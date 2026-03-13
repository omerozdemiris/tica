<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Cart;
use App\Models\CheckoutSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductAttributeTerm;
use App\Mail\NotifyAdminOrderMail;
use App\Mail\OrderStatusMail;
use App\Models\Setting;
use App\Models\Store;
use App\Models\UserAddress;
use App\Models\Theme;
use App\Services\CartService;
use App\Services\Logs\CustomerLogService;
use App\Services\Payments\PaytrService;
// use App\Services\Payments\ZiraatService;
use App\Services\Payments\ZiraatIframeService as ZiraatService;
use App\Services\Payments\IyzicoService;
use App\Services\PricingService;
use App\Services\SmsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected PaytrService $paytrService,
        protected ZiraatService $ziraatService,
        protected PricingService $pricingService,
        protected SmsService $smsService
    ) {
        parent::__construct();
    }

    public function index(Request $request): View|RedirectResponse
    {
        $theme = Theme::first();
        $cart = $this->cartService->getCart(false);

        if (!$cart || !$cart->items || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => 'Sepetinizde ürün bulunmuyor.',
            ]);
        }

        $store = Store::first();

        if ($store && $store->auth_required && !auth()->check()) {
            return redirect()->route('cart.index')->withErrors([
                'auth' => 'Siparişi tamamlamak için üye girişi yapmanız gerekmektedir.',
            ]);
        }

        $banks = Bank::active()->orderBy('bank_name')->get();
        $meta = (object) [
            'title' => ($store->meta_title ?? config('app.name')) . ' | Siparişi Tamamla',
            'description' => $store->meta_description ?? null,
        ];

        $addresses = auth()->check()
            ? auth()->user()->addresses()->orderByDesc('is_default')->orderBy('created_at', 'desc')->get()
            : collect();

        $guestAddress = $request->session()->get('checkout.guest_address');

        $data = (object) [
            'cart' => $cart,
            'addresses' => $addresses,
            'guestAddress' => $guestAddress,
            'meta' => $meta,
        ];

        $pricing = $this->pricingService->summarizeCart($cart);

        return view($theme->thene . '.pages.cart.checkout', [
            'data' => $data,
            'meta' => $meta,
            'banks' => $banks,
            'pricing' => $pricing,
        ]);
    }

    public function start(Request $request): View|RedirectResponse
    {
        $cart = $this->cartService->getCart(false);
        if (!$cart || !$cart->items || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => 'Sepetinizde ürün bulunmuyor.',
            ]);
        }

        $store = Store::first();

        if ($store && $store->auth_required && !auth()->check()) {
            return redirect()->route('cart.index')->withErrors([
                'auth' => 'Siparişi tamamlamak için üye girişi yapmanız gerekmektedir.',
            ]);
        }

        $request->validate([
            'payment_method' => ['required', 'in:card,wire'],
        ]);

        $cart->loadMissing('items.product', 'items.variant.term');
        $this->assertCartStock($cart);

        [$shippingAddress, $billingAddress] = $this->resolveAddresses($request);

        $paymentMethod = $request->string('payment_method')->toString() ?: 'card';
        $wireEnabled = (bool) ($this->store?->allow_wire_payments);
        $wireBanksAvailable = Bank::active()->exists();

        if ($paymentMethod === 'wire' && (!$wireEnabled || !$wireBanksAvailable)) {
            return back()
                ->withErrors(['payment_method' => 'Havale/EFT seçeneği şu anda aktif değil.'])
                ->withInput();
        }

        if ($paymentMethod === 'wire') {
            try {
                $order = $this->placeWireOrder($cart, $shippingAddress, $billingAddress, $request);
            } catch (RuntimeException $e) {
                Log::warning('Havale/EFT siparişi oluşturulamadı', [
                    'message' => $e->getMessage(),
                ]);

                return redirect()
                    ->route('cart.checkout.wire.error')
                    ->withErrors(['payment' => $e->getMessage()]);
            }

            return redirect()->route('cart.checkout.wire.success', $order->order_number);
        }

        $checkoutSession = $this->createCheckoutSession($request, $cart, $shippingAddress, $billingAddress);

        try {
            /** @var IyzicoService $iyzico */
            $iyzico = app(IyzicoService::class);

            $customer = [
                'id' => $checkoutSession->user_id ?? $checkoutSession->guest_id ?? $checkoutSession->merchant_oid,
                'name' => $checkoutSession->customer_data['name'] ?? $shippingAddress->fullname,
                'surname' => $shippingAddress->fullname,
                'email' => $checkoutSession->customer_data['email'] ?? $shippingAddress->email,
                'gsmNumber' => $checkoutSession->customer_data['phone'] ?? $shippingAddress->phone,
                'address' => $checkoutSession->customer_data['address'] ?? $this->formatFullAddress($shippingAddress),
                'city' => $shippingAddress->city,
                'country' => 'Türkiye',
            ];

            $items = collect($checkoutSession->cart_snapshot['items'] ?? []);

            $basketItems = $items
                ->map(function ($item) {
                    $lineTotal = (float) ($item['subtotal'] ?? ($item['price'] ?? 0));

                    return [
                        'name' => $item['name'] ?? 'Ürün',
                        'price' => $lineTotal,
                        'sku' => $item['product_id'] ?? null,
                        'category1' => 'Genel',
                        'category2' => 'Genel',
                    ];
                })
                ->values()
                ->all();

            $basketTotal = $items->sum(function ($item) {
                return (float) ($item['subtotal'] ?? ($item['price'] ?? 0));
            });

            $result = $iyzico->initializeCheckoutForm([
                'conversation_id' => $checkoutSession->merchant_oid,
                'price' => $basketTotal,
                'paid_price' => $basketTotal,
                'currency' => $checkoutSession->currency ?? 'TRY',
                'callback_url' => route('cart.checkout.iyzico.callback'),
                'customer' => $customer,
                'basket_items' => $basketItems,
            ]);

            $checkoutSession->update([
                'payment_service_token' => $result['token'] ?? null,
            ]);

            $theme = Theme::first();

            return view($theme->thene . '.pages.cart.iyzico_iframe', [
                'meta' => (object) [
                    'title' => ($this->store?->meta_title ?? config('app.name')) . ' | Ödeme',
                    'description' => $this->store?->meta_description ?? null,
                ],
                'checkout' => $checkoutSession,
                'checkoutFormContent' => $result['checkoutFormContent'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Iyzico ile ödeme başlatılırken kritik hata', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()
                ->withErrors(['payment' => 'Ödeme başlatılamadı: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function ziraatCallback(Request $request)
    {
        $response = $request->all();

        $merchantOid = $request->input('Oid') ?? $request->input('oid') ?? $request->input('OrderId') ?? $request->input('TransactionId');

        $status = $request->input('Response') ?? $request->input('ResultCode') ?? $request->input('ProcReturnCode') ?? $request->input('Rc');

        if (!$merchantOid) {
            Log::error('Ziraat Callback Hatası: Sipariş numarası (OID) bulunamadı.', $response);
            $errMsg = $request->input('ErrMsg') ?? 'Ödeme yanıtı işlenemedi (Sipariş No Bulunamadı).';
            return redirect()->route('cart.checkout.fail', ['message' => $errMsg]);
        }

        $session = CheckoutSession::where('merchant_oid', $merchantOid)->first();

        if (!$session) {
            return redirect()->route('cart.index')->withErrors(['payment' => 'Ödeme oturumu bulunamadı.']);
        }

        if ($session->user_id && !auth()->check()) {
            auth()->loginUsingId($session->user_id);
        }

        if ($session->guest_id && !request()->session()->has('guest_id')) {
            request()->session()->put('guest_id', $session->guest_id);
        }

        if ($this->ziraatService->verifyCallback($response)) {
            try {
                $this->finalizeOrder($session);
                $session->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                return redirect()->route('cart.checkout.success', ['merchant_oid' => $merchantOid]);
            } catch (RuntimeException $e) {
                Log::error('Ziraat ödeme sonrası hata', [
                    'merchant_oid' => $merchantOid,
                    'message' => $e->getMessage(),
                ]);
                return redirect()->route('cart.checkout.fail');
            }
        }
        dd($response);

        $session->update(['status' => 'failed']);
        return redirect()->route('cart.checkout.fail')->withErrors(['payment' => $request->input('ErrMsg') ?? 'Ödeme başarısız.']);
    }

    public function iyzicoCallback(Request $request)
    {
        $token = $request->input('token');

        if (!$token) {
            return redirect()->route('cart.index')->withErrors([
                'payment' => 'Ödeme yanıtı alınamadı.',
            ]);
        }

        /** @var IyzicoService $iyzico */
        $iyzico = app(IyzicoService::class);

        try {
            $result = $iyzico->retrievePaymentResult($token);
        } catch (\Throwable $e) {
            Log::error('Iyzico ödeme sonucu alınamadı', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('cart.checkout.fail')->withErrors([
                'payment' => 'Ödeme doğrulanamadı.',
            ]);
        }

        $merchantOid = $result['basketId'] ?? $result['conversationId'] ?? null;

        if (!$merchantOid) {
            return redirect()->route('cart.checkout.fail')->withErrors([
                'payment' => 'Sipariş numarası bulunamadı.',
            ]);
        }

        $session = CheckoutSession::where('merchant_oid', $merchantOid)->first();

        if (!$session) {
            return redirect()->route('cart.index')->withErrors([
                'payment' => 'Ödeme oturumu bulunamadı.',
            ]);
        }

        if (($result['paymentStatus'] ?? null) === 'SUCCESS') {
            try {
                $this->finalizeOrder($session);
                $session->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                return redirect()->route('cart.checkout.success', ['merchant_oid' => $merchantOid]);
            } catch (RuntimeException $e) {
                Log::error('Iyzico ödeme sonrası hata', [
                    'merchant_oid' => $merchantOid,
                    'message' => $e->getMessage(),
                ]);
                return redirect()->route('cart.checkout.fail')->withErrors([
                    'payment' => 'Sipariş oluşturulamadı.',
                ]);
            }
        }

        $session->update(['status' => 'failed']);

        return redirect()->route('cart.checkout.fail')->withErrors([
            'payment' => 'Ödeme başarısız.',
        ]);
    }

    public function payment(string $merchantOid): View|RedirectResponse
    {
        $theme = Theme::first();
        $session = $this->findSessionForVisitor($merchantOid);

        if (!$session || !$session->payment_service_token || $session->status !== 'pending') {
            return redirect()->route('cart.index')->withErrors([
                'payment' => 'Ödeme oturumu bulunamadı veya süresi doldu.',
            ]);
        }

        $store = Store::first();
        $meta = (object) [
            'title' => ($store->meta_title ?? config('app.name')) . ' | Ödeme',
            'description' => $store->meta_description ?? null,
        ];

        return view($theme->thene . '.pages.cart.payment', [
            'data' => (object) [
                'checkout' => $session,
                'cart' => $session->cart_snapshot,
            ],
            'meta' => $meta,
        ]);
    }

    public function callback(Request $request)
    {
        $merchantOid = $request->input('merchant_oid');
        $status = $request->input('status');
        $totalAmount = $request->input('total_amount');
        $hash = $request->input('hash');

        if (!$merchantOid || !$hash) {
            return response('PAYTR notification failed', 400);
        }

        if (!$this->paytrService->verifyCallback($hash, $merchantOid, $status, $totalAmount)) {
            Log::warning('PayTR callback doğrulaması başarısız', [
                'merchant_oid' => $merchantOid,
            ]);
            return response('PAYTR notification failed', 400);
        }

        $session = CheckoutSession::where('merchant_oid', $merchantOid)->first();

        if (!$session) {
            Log::warning('Checkout oturumu bulunamadı', ['merchant_oid' => $merchantOid]);
            return response('PAYTR notification failed', 404);
        }

        if ($session->status === 'paid') {
            return response('OK', 200);
        }

        if ($status === 'success') {
            try {
                $this->finalizeOrder($session);
                $session->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            } catch (RuntimeException $e) {
                Log::error('Ödeme sonrası stok güncellenemedi', [
                    'merchant_oid' => $merchantOid,
                    'message' => $e->getMessage(),
                ]);

                $session->update([
                    'status' => 'failed',
                ]);
            }
        } else {
            $session->update([
                'status' => 'failed',
            ]);
        }

        return response('OK', 200);
    }

    public function success(Request $request): View|RedirectResponse
    {
        $theme = Theme::first();
        $merchantOid = $request->query('merchant_oid');
        if (!$merchantOid) {
            return redirect()->route('cart.index');
        }

        $session = CheckoutSession::where('merchant_oid', $merchantOid)->first();

        if (!$session || $session->status !== 'paid' || !$session->order_id) {
            return redirect()->route('cart.index')->withErrors([
                'payment' => 'Siparişiniz doğrulanamadı.',
            ]);
        }

        $order = $session->order()->with(['items.product'])->first();

        $store = Store::first();
        $meta = (object) [
            'title' => ($store->meta_title ?? config('app.name')) . ' | Sipariş Onayı',
            'description' => $store->meta_description ?? null,
        ];

        return view($theme->thene . '.pages.checkout.card.success', [
            'data' => (object) [
                'order' => $order,
            ],
            'meta' => $meta,
        ]);
    }

    public function fail(Request $request): View
    {
        $theme = Theme::first();
        $store = Store::first();
        $meta = (object) [
            'title' => ($store->meta_title ?? config('app.name')) . ' | Ödeme Hatası',
            'description' => $store->meta_description ?? null,
        ];

        return view($theme->thene . '.pages.checkout.card.error', [
            'meta' => $meta,
            'message' => $request->query('message')
        ]);
    }

    public function wireSuccess(string $orderNumber): View|RedirectResponse
    {
        $theme = Theme::first();
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product'])
            ->first();

        if (!$order || $order->method !== 'wire') {
            return redirect()->route('cart.checkout.wire.error')->withErrors([
                'payment' => 'Sipariş doğrulanamadı.',
            ]);
        }

        $store = Store::first();
        $banks = Bank::active()->orderBy('bank_name')->get();
        $meta = (object) [
            'title' => ($store->meta_title ?? config('app.name')) . ' | Havale Talimatı',
            'description' => $store->meta_description ?? null,
        ];

        return view($theme->thene . '.pages.checkout.wire.success', [
            'data' => (object) [
                'order' => $order,
            ],
            'meta' => $meta,
            'banks' => $banks,
        ]);
    }

    public function wireError(): View
    {
        $theme = Theme::first();
        $store = Store::first();
        $meta = (object) [
            'title' => ($store->meta_title ?? config('app.name')) . ' | Havale Hatası',
            'description' => $store->meta_description ?? null,
        ];

        return view($theme->thene . '.pages.checkout.wire.error', [
            'meta' => $meta,
        ]);
    }

    /**
     * @return array{0:UserAddress,1:UserAddress}
     */
    protected function resolveAddresses(Request $request): array
    {
        $store = $this->store ?? Store::first();
        $tcRequired = $store?->tc_required ?? false;

        if (auth()->check()) {
            $validated = $request->validate([
                'address_id' => ['required', 'exists:user_addresses,id'],
                'notes' => ['nullable', 'string', 'max:500'],
            ], [
                'address_id.required' => 'Lütfen kayıtlı adreslerinizden birini seçin.',
                'address_id.exists' => 'Seçtiğiniz adres bulunamadı.',
                'notes.max' => 'Sipariş notu en fazla 500 karakter olabilir.',
            ]);

            $address = UserAddress::where('user_id', auth()->id())->findOrFail($validated['address_id']);

            if ($tcRequired && empty($address->tc)) {
                throw ValidationException::withMessages([
                    'tc' => 'TC Kimlik Numarası zorunludur. Lütfen adres bilgilerinizi güncelleyin.',
                ]);
            }

            $request->session()->forget('checkout.guest_address');

            return [$address, $address];
        }

        $tcRules = $tcRequired
            ? ['required', 'string', 'size:11', 'regex:/^[0-9]+$/']
            : ['nullable', 'string', 'size:11', 'regex:/^[0-9]+$/'];

        $tcMessages = $tcRequired
            ? ['tc.required' => 'TC Kimlik Numarası zorunludur.', 'tc.size' => 'TC Kimlik Numarası 11 haneli olmalıdır.', 'tc.regex' => 'TC Kimlik Numarası sadece rakamlardan oluşmalıdır.']
            : ['tc.size' => 'TC Kimlik Numarası 11 haneli olmalıdır.', 'tc.regex' => 'TC Kimlik Numarası sadece rakamlardan oluşmalıdır.'];

        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'tc' => $tcRules,
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
        ], array_merge([
            'fullname.required' => 'Ad ve soyad alanı zorunludur.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Lütfen geçerli bir e-posta adresi girin.',
            'phone.required' => 'Telefon numarası zorunludur.',
            'city.required' => 'Şehir bilgisi zorunludur.',
            'state.required' => 'İlçe/semt bilgisi zorunludur.',
            'address.required' => 'Adres alanı boş bırakılamaz.',
            'fullname.max' => 'Ad soyad en fazla 120 karakter olabilir.',
            'email.max' => 'E-posta en fazla 150 karakter olabilir.',
            'phone.max' => 'Telefon en fazla 20 karakter olabilir.',
            'city.max' => 'Şehir adı en fazla 100 karakter olabilir.',
            'state.max' => 'İlçe adı en fazla 100 karakter olabilir.',
            'zip.max' => 'Posta kodu en fazla 20 karakter olabilir.',
            'address.max' => 'Adres en fazla 500 karakter olabilir.',
        ], $tcMessages));

        $guestId = $this->cartService->currentGuestId(true);
        $address = UserAddress::create([
            'guest_id' => $guestId,
            'type' => 'shipping',
            'title' => 'Misafir Adresi',
            'fullname' => $validated['fullname'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'tc' => $validated['tc'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'],
            'zip' => $validated['zip'] ?? null,
            'country' => 'Türkiye',
            'address' => $validated['address'],
            'is_default' => false,
        ]);

        request()->session()->put('checkout.guest_address', $address->only([
            'title',
            'fullname',
            'email',
            'phone',
            'tc',
            'city',
            'state',
            'zip',
            'address',
        ]));

        return [$address, $address];
    }

    protected function createCheckoutSession(Request $request, Cart $cart, UserAddress $shipping, UserAddress $billing): CheckoutSession
    {
        $identifiers = [
            'user_id' => $cart->user_id,
            'guest_id' => $cart->guest_id ?? $this->cartService->currentGuestId(),
        ];

        $merchantOid = 'C' . now()->format('YmdHis') . Str::upper(Str::random(6));

        $subtotal = (float) $cart->total_price;
        $taxAmount = 0;
        if ($this->store && $this->store->tax_enabled && $this->store->tax_rate > 0) {
            $taxAmount = $subtotal * ($this->store->tax_rate / 100);
        }

        $baseWithTax = $subtotal + $taxAmount;
        $shippingCost = 0;

        if ($this->store && $this->store->shipping_price_limit > 0 && $baseWithTax < $this->store->shipping_price_limit) {
            $shippingCost = (float) ($this->store->shipping_price ?? 0);
        }

        $finalAmount = $baseWithTax + $shippingCost;

        $snapshot = [
            'items' => $cart->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'variant_id' => $item->product_attribute_term_id,
                    'variant_ids' => $item->variant_ids,
                    'name' => $item->product?->title ?? 'Ürün',
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'subtotal' => (float) $item->subtotal,
                ];
            })->values()->all(),
            'total' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_cost' => $shippingCost,
            'final_amount' => $finalAmount,
        ];

        $customerData = [
            'name' => $shipping->fullname,
            'email' => $shipping->email,
            'phone' => $shipping->phone,
            'address' => $this->formatFullAddress($shipping),
            'notes' => $request->input('notes'),
        ];

        return CheckoutSession::create([
            'merchant_oid' => $merchantOid,
            'cart_id' => $cart->id,
            'user_id' => $identifiers['user_id'],
            'guest_id' => $identifiers['guest_id'],
            'shipping_address_id' => $shipping->id,
            'billing_address_id' => $billing->id,
            'amount' => $finalAmount,
            'currency' => 'TRY',
            'cart_snapshot' => $snapshot,
            'customer_data' => $customerData,
            'status' => 'pending',
        ]);
    }

    protected function finalizeOrder(CheckoutSession $session): void
    {
        if ($session->order_id) {
            return;
        }

        DB::transaction(function () use ($session) {
            $this->reserveStockForItems(
                collect($session->cart_snapshot['items'] ?? [])
                    ->map(fn($item) => [
                        'product_id' => $item['product_id'],
                        'variant_id' => $item['variant_id'],
                        'variant_ids' => $item['variant_ids'] ?? [],
                        'quantity' => $item['quantity'],
                    ])
                    ->all()
            );

            $order = Order::create([
                'user_id' => $session->user_id,
                'guest_id' => $session->guest_id,
                'order_number' => $this->generateOrderNumber(),
                'status' => 'pending',
                'method' => 'card',
                'is_paid' => 1,
                'total' => $session->amount,
                'shipping_address' => $session->customer_data['address'] ?? null,
                'billing_address' => $session->customer_data['address'] ?? null,
                'shipping_address_id' => $session->shipping_address_id,
                'billing_address_id' => $session->billing_address_id,
                'notes' => Arr::get($session->customer_data, 'notes'),
            ]);

            foreach ($session->cart_snapshot['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_attribute_term_id' => $item['variant_id'],
                    'variant_ids' => $item['variant_ids'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['subtotal'],
                ]);
            }

            $session->update([
                'order_id' => $order->id,
            ]);

            if ($session->cart_id) {
                Cart::where('id', $session->cart_id)->delete();
            }

            app(CustomerLogService::class)->log('Sipariş Oluşturuldu (Kart)', null, $order->toArray());

            $this->sendAdminNotification($order);
            $this->sendCustomerNotification($order);
            $this->sendCustomerSms($order, 'created');
        });
    }

    protected function placeWireOrder(Cart $cart, UserAddress $shipping, UserAddress $billing, Request $request): Order
    {
        $cart->loadMissing('items.product', 'items.variant.term');

        $userId = $cart->user_id ?? auth()->id();
        $guestId = $cart->guest_id ?? $this->cartService->currentGuestId();

        $subtotal = (float) ($cart->total_price ?? 0);
        $taxAmount = 0;
        if ($this->store && $this->store->tax_enabled && $this->store->tax_rate > 0) {
            $taxAmount = $subtotal * ($this->store->tax_rate / 100);
        }

        $baseWithTax = $subtotal + $taxAmount;
        $shippingCost = 0;
        if ($this->store && $this->store->shipping_price_limit > 0 && $baseWithTax < $this->store->shipping_price_limit) {
            $shippingCost = (float) ($this->store->shipping_price ?? 0);
        }

        $finalAmount = $baseWithTax + $shippingCost;

        return DB::transaction(function () use ($cart, $shipping, $billing, $request, $userId, $guestId, $finalAmount) {
            $this->reserveStockForItems(
                $cart->items->map(fn($item) => [
                    'product_id' => $item->product_id,
                    'variant_id' => $item->product_attribute_term_id,
                    'variant_ids' => $item->variant_ids,
                    'quantity' => $item->quantity,
                ])->all()
            );

            $order = Order::create([
                'user_id' => $userId,
                'guest_id' => $guestId,
                'order_number' => $this->generateOrderNumber(),
                'status' => 'pending',
                'method' => 'wire',
                'is_paid' => 0,
                'total' => $finalAmount,
                'shipping_address' => $this->formatFullAddress($shipping),
                'billing_address' => $this->formatFullAddress($billing),
                'shipping_address_id' => $shipping->id,
                'billing_address_id' => $billing->id,
                'notes' => $request->input('notes'),
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_attribute_term_id' => $item->product_attribute_term_id,
                    'variant_ids' => $item->variant_ids,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'total' => (float) $item->subtotal,
                ]);
            }

            $order->refresh();
            $order->load(['user', 'shippingAddress']);

            $this->sendAdminNotification($order);
            $this->sendCustomerNotification($order);

            $cart->items()->delete();
            $cart->delete();

            app(CustomerLogService::class)->log('Sipariş Oluşturuldu (Havale)', null, $order->toArray());

            $this->sendCustomerSms($order, 'created');

            return $order;
        });
    }

    protected function sendCustomerNotification(Order $order): void
    {
        $email = $order->customer_email;
        if (!$email) {
            return;
        }

        try {
            Mail::to($email)->send(new OrderStatusMail($order, $order->status));
        } catch (\Exception $e) {
            Log::error('Müşteri sipariş bildirimi gönderilemedi', [
                'order_id' => $order->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    protected function assertCartStock(Cart $cart): void
    {
        if (!$this->store?->auto_stock) {
            return;
        }

        $cart->items->each(function ($item) {
            $product = $item->product;
            if (!$product) {
                throw ValidationException::withMessages([
                    'stock' => 'Sepetteki bir ürün bulunamadı veya satışta değil.',
                ]);
            }

            $variantIds = $item->variant_ids ?? [];
            if (empty($variantIds) && !empty($item->product_attribute_term_id)) {
                $variantIds = [$item->product_attribute_term_id];
            }

            if (!empty($variantIds)) {
                $variants = ProductAttributeTerm::whereIn('id', $variantIds)->get();
                foreach ($variants as $variant) {
                    $available = $this->availableStockValue($product, $variant);
                    if ($available !== null && $item->quantity > $available) {
                        throw ValidationException::withMessages([
                            'stock' => $this->stockErrorMessage($product, $variant, $available),
                        ]);
                    }
                }
            } else {
                $available = $this->availableStockValue($product, null);
                if ($available !== null && $item->quantity > $available) {
                    throw ValidationException::withMessages([
                        'stock' => $this->stockErrorMessage($product, null, $available),
                    ]);
                }
            }
        });
    }

    protected function reserveStockForItems(array $items): void
    {
        if (!$this->store?->auto_stock) {
            return;
        }

        foreach ($items as $item) {
            $product = Product::query()->lockForUpdate()->find($item['product_id']);
            if (!$product) {
                throw new RuntimeException('Ürün bulunamadı.');
            }

            $variantIds = $item['variant_ids'] ?? [];
            if (empty($variantIds) && !empty($item['variant_id'])) {
                $variantIds = [$item['variant_id']];
            }

            if (!empty($variantIds)) {
                $variants = ProductAttributeTerm::query()->whereIn('id', $variantIds)->lockForUpdate()->get();
                foreach ($variants as $variant) {
                    $available = $this->availableStockValue($product, $variant);
                    if ($available !== null && $item['quantity'] > $available) {
                        throw new RuntimeException($this->stockErrorMessage($product, $variant, $available));
                    }
                    if ($available !== null) {
                        $variant->stock = max(0, (int) ($variant->stock ?? 0) - (int) $item['quantity']);
                        $variant->save();
                    }
                }
            } else {
                $available = $this->availableStockValue($product, null);
                if ($available !== null && $item['quantity'] > $available) {
                    throw new RuntimeException($this->stockErrorMessage($product, null, $available));
                }
                if ($available !== null) {
                    $product->stock = max(0, (int) ($product->stock ?? 0) - (int) $item['quantity']);
                    $product->save();
                }
            }
        }
    }

    protected function availableStockValue(Product $product, ?ProductAttributeTerm $variant): ?int
    {
        if (!$this->store?->auto_stock) {
            return null;
        }

        if ($variant) {
            if ($this->isUnlimitedStock($variant->stock_type, $variant->stock)) {
                return null;
            }

            return max(0, (int) ($variant->stock ?? 0));
        }

        if ($this->isUnlimitedStock($product->stock_type, $product->stock)) {
            return null;
        }

        return max(0, (int) ($product->stock ?? 0));
    }

    protected function isUnlimitedStock(?int $stockType, ?int $stock): bool
    {
        return (int) ($stockType ?? 0) === 1 || $stock === null;
    }

    protected function stockErrorMessage(Product $product, ?ProductAttributeTerm $variant, int $available): string
    {
        $name = $product->title ?? 'Ürün';

        if ($variant) {
            $variant->loadMissing('term');
            if ($variant->term?->name) {
                $name .= ' - ' . $variant->term->name;
            }
        }

        if ($available <= 0) {
            return "{$name} stokta bulunmuyor.";
        }

        return "{$name} için en fazla {$available} adet seçebilirsiniz.";
    }

    protected function findSessionForVisitor(string $merchantOid): ?CheckoutSession
    {
        $guestId = $this->cartService->currentGuestId(false);
        $userId = auth()->id();

        $query = CheckoutSession::where('merchant_oid', $merchantOid);

        if ($userId) {
            $query->where(function ($q) use ($userId, $guestId) {
                $q->where('user_id', $userId);
                if ($guestId) {
                    $q->orWhere('guest_id', $guestId);
                }
            });
        } elseif ($guestId) {
            $query->where('guest_id', $guestId);
        }

        return $query->first();
    }

    protected function formatFullAddress(UserAddress $address): string
    {
        return trim(implode(' ', array_filter([
            $address->address,
            $address->state,
            $address->city,
            $address->zip,
        ])));
    }

    protected function generateOrderNumber(): string
    {
        return 'ORD' . now()->format('Ymd') . Str::upper(Str::random(4));
    }

    protected function sendAdminNotification(Order $order): void
    {
        $settings = Setting::first();
        $notifyMail = $settings?->notify_mail;

        if (!$notifyMail) {
            return;
        }

        try {
            Mail::to($notifyMail)->send(new NotifyAdminOrderMail($order));
        } catch (\Exception $e) {
            Log::warning('Admin sipariş bildirimi gönderilemedi', [
                'order_id' => $order->id,
                'notify_mail' => $notifyMail,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendCustomerSms(Order $order, string $type): void
    {
        $phone = $order->customer_phone;
        if (!$phone) {
            return;
        }

        $message = match ($type) {
            'created' => $order->method === 'wire'
                ? "Sayın {$order->customer_name}, siparişiniz (#{$order->order_number}) alınmıştır. Havale/EFT ödeme talimatları e-posta adresinize gönderilmiştir."
                : "Sayın {$order->customer_name}, siparişiniz (#{$order->order_number}) başarıyla oluşturulmuştur. Ödemeniz alınmıştır.",
            default => null,
        };

        if (!$message) {
            return;
        }

        try {
            $this->smsService->sendSms($phone, $message);
        } catch (\Exception $e) {
            Log::warning('Müşteri SMS bildirimi gönderilemedi', [
                'order_id' => $order->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
