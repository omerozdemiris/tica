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

use App\Services\Payments\PaytrService;

use App\Services\Payments\ZiraatService;

use App\Services\PricingService;

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

        protected PricingService $pricingService

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



        if ($paymentMethod === 'card') {

            $request->validate([

                'card_number' => ['required', 'string'],

                'expiry_month' => ['required', 'string', 'size:2'],

                'expiry_year' => ['required', 'string', 'size:2'],

                'cvv' => ['required', 'string', 'min:3', 'max:4'],

            ], [

                'card_number.required' => 'Kart numarası gereklidir.',

                'expiry_month.required' => 'Son kullanma ayı gereklidir.',

                'expiry_year.required' => 'Son kullanma yılı gereklidir.',

                'cvv.required' => 'CVV gereklidir.',

            ]);
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

            $paymentData = $this->ziraatService->preparePaymentData([

                'merchant_oid' => $checkoutSession->merchant_oid,

                'amount' => $checkoutSession->amount,

                'card_number' => $request->input('card_number'),

                'expiry_month' => $request->input('expiry_month'),

                'expiry_year' => $request->input('expiry_year'),

                'cvv' => $request->input('cvv'),

                'success_url' => route('cart.checkout.ziraat.callback'),

                'fail_url' => route('cart.checkout.ziraat.callback'),

            ]);



            return view(Theme::first()->thene . '.pages.cart.ziraat_redirect', [

                'paymentData' => $paymentData

            ]);
        } catch (\Throwable $e) {

            Log::error('Ödeme başlatılırken kritik hata', [

                'message' => $e->getMessage(),

                'file' => $e->getFile(),

                'line' => $e->getLine(),

                'trace' => $e->getTraceAsString(),

            ]);



            return back()

                ->withErrors(['payment' => 'Ödeme başlatılamadı: ' . $e->getMessage()])

                ->withInput();
        }
    }



    public function ziraatCallback(Request $request)

    {

        $response = $request->all();



        $merchantOid = $request->input('Oid') ?? $request->input('oid') ?? $request->input('OrderId');



        $status = $request->input('Response') ?? $request->input('ResultCode') ?? $request->input('ProcReturnCode');



        if (!$merchantOid) {

            Log::error('Ziraat Callback Hatası: Sipariş numarası (OID) bulunamadı.', $response);

            $errMsg = $request->input('ErrMsg') ?? 'Ödeme yanıtı işlenemedi (Sipariş No Bulunamadı).';

            return redirect()->route('cart.checkout.fail', ['message' => $errMsg]);
        }



        $session = CheckoutSession::where('merchant_oid', $merchantOid)->first();



        if (!$session) {

            return redirect()->route('cart.index')->withErrors(['payment' => 'Ödeme oturumu bulunamadı.']);
        }



        // OTURUM KURTARMA: Eğer oturum sıfırlandıysa (HTTP testi nedeniyle), 

        // session'daki verileri CheckoutSession'dan geri yükleyelim.

        if ($session->user_id && !auth()->check()) {

            auth()->loginUsingId($session->user_id);
        }



        if ($session->guest_id && !request()->session()->has('guest_id')) {

            request()->session()->put('guest_id', $session->guest_id);
        }



        if ($this->ziraatService->verifyCallback($response) && $status === 'Approved') {

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

            $request->session()->forget('checkout.guest_address');



            return [$address, $address];
        }



        $validated = $request->validate([

            'fullname' => ['required', 'string', 'max:120'],

            'email' => ['required', 'email', 'max:150'],

            'phone' => ['required', 'string', 'max:20'],

            'city' => ['required', 'string', 'max:100'],

            'state' => ['required', 'string', 'max:100'],

            'zip' => ['nullable', 'string', 'max:20'],

            'address' => ['required', 'string', 'max:500'],

        ], [

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

        ]);



        $guestId = $this->cartService->currentGuestId(true);

        $address = UserAddress::create([

            'guest_id' => $guestId,

            'type' => 'shipping',

            'title' => 'Misafir Adresi',

            'fullname' => $validated['fullname'],

            'email' => $validated['email'],

            'phone' => $validated['phone'],

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



        $snapshot = [

            'items' => $cart->items->map(function ($item) {

                return [

                    'product_id' => $item->product_id,

                    'variant_id' => $item->product_attribute_term_id,

                    'name' => $item->product?->title ?? 'Ürün',

                    'quantity' => $item->quantity,

                    'price' => (float) $item->price,

                    'subtotal' => (float) $item->subtotal,

                ];
            })->values()->all(),

            'total' => (float) $cart->total_price,

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

            'amount' => $snapshot['total'],

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

                        'quantity' => $item['quantity'],

                    ])

                    ->all()

            );



            $order = Order::create([

                'user_id' => $session->user_id,

                'guest_id' => $session->guest_id,

                'order_number' => $this->generateOrderNumber(),

                'status' => 'completed',

                'method' => 'card',

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



            $this->sendAdminNotification($order);

            $this->sendCustomerNotification($order);
        });
    }



    protected function placeWireOrder(Cart $cart, UserAddress $shipping, UserAddress $billing, Request $request): Order

    {

        $cart->loadMissing('items.product', 'items.variant.term');



        return DB::transaction(function () use ($cart, $shipping, $billing, $request) {

            $this->reserveStockForItems(

                $cart->items->map(fn($item) => [

                    'product_id' => $item->product_id,

                    'variant_id' => $item->product_attribute_term_id,

                    'quantity' => $item->quantity,

                ])->all()

            );



            $order = Order::create([

                'user_id' => $cart->user_id,

                'guest_id' => $cart->guest_id,

                'order_number' => $this->generateOrderNumber(),

                'status' => 'pending',

                'method' => 'wire',

                'total' => (float) ($cart->total_price ?? 0),

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

                    'quantity' => $item->quantity,

                    'price' => (float) $item->price,

                    'total' => (float) $item->subtotal,

                ]);
            }



            $cart->items()->delete();

            $cart->delete();



            $this->sendAdminNotification($order);

            $this->sendCustomerNotification($order);



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



            $variant = $item->variant;

            $available = $this->availableStockValue($product, $variant);

            if ($available !== null && $item->quantity > $available) {

                throw ValidationException::withMessages([

                    'stock' => $this->stockErrorMessage($product, $variant, $available),

                ]);
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



            $variant = null;

            if (!empty($item['variant_id'])) {

                $variant = ProductAttributeTerm::query()->lockForUpdate()->find($item['variant_id']);

                if (!$variant) {

                    throw new RuntimeException('Ürün varyasyonu bulunamadı.');
                }
            }



            $available = $this->availableStockValue($product, $variant);

            if ($available !== null && $item['quantity'] > $available) {

                throw new RuntimeException($this->stockErrorMessage($product, $variant, $available));
            }



            if ($available !== null) {

                if ($variant) {

                    $variant->stock = max(0, (int) ($variant->stock ?? 0) - (int) $item['quantity']);

                    $variant->save();
                } else {

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
}
