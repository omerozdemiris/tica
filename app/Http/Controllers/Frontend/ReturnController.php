<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnItem;
use App\Models\ReturnRequest;
use App\Models\Theme;
use App\Models\Setting;
use App\Services\Logs\CustomerLogService;
use App\Services\SmsService;
use App\Mail\NotifyAdminReturnMail;
use App\Mail\ReturnStatusMail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ReturnController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function lookup(Request $request): View
    {
        $theme = Theme::first();
        
        $order = null;
        $orders = null;
        $lookupData = null;

        if ($request->filled('order_number') || $request->filled('email')) {
            $lookupData = $request->validate([
                'order_number' => ['required', 'string', 'max:64'],
                'email' => ['required', 'email', 'max:255'],
            ], [
                'order_number.required' => 'Sipariş numarası zorunludur.',
                'email.required' => 'E-posta adresi zorunludur.',
                'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            ]);

            $query = Order::query()->with(['shippingAddress', 'returns']);

            if (!empty($lookupData['order_number'])) {
                $query->where('order_number', $lookupData['order_number']);
            }

            if (!empty($lookupData['email'])) {
                $query->where(function ($q) use ($lookupData) {
                    $q->whereHas('shippingAddress', function ($address) use ($lookupData) {
                        $address->where('email', $lookupData['email']);
                    })->orWhereHas('user', function ($user) use ($lookupData) {
                        $user->where('email', $lookupData['email']);
                    });
                });
            }

            $results = $query->latest()->get();

            if ($results->count() === 1) {
                $order = $results->first();
                $order->load(['items.product', 'returns.items', 'shippingAddress', 'billingAddress']);
            } else {
                $orders = $results;
            }
        }

        $store = $this->store ?? Setting::first();
        $meta = (object) [
            'title' => 'Sipariş Sorgulama & İade | ' . ($store->meta_title ?? config('app.name')),
            'description' => 'Sipariş numaranız veya e-posta adresiniz ile siparişinizi bulun.',
        ];

        return view($theme->thene . '.pages.returns.lookup', [
            'data' => (object) [
                'order' => $order,
                'orders' => $orders,
                'lookup' => $lookupData,
            ],
            'meta' => $meta,
        ]);
    }

    public function createFromOrder(Order $order): View|RedirectResponse
    {
        if (!Auth::check() || $order->user_id !== Auth::id()) {
            return redirect()->route('returns.lookup')->withErrors([
                'order' => 'Sipariş bulunamadı.',
            ]);
        }

        $request = request();
        $request->merge([
            'order_number' => $order->order_number,
        ]);

        return $this->lookup($request);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'order_number' => ['required_without:order_id', 'string', 'max:64'],
            'email' => ['required_without:order_id', 'nullable', 'email', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['integer'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'order_number' => 'Sipariş numarası',
            'email' => 'E-posta',
            'items' => 'Ürünler',
        ]);

        $order = $this->resolveOrderForCreation(
            $validated['order_id'] ?? null,
            $validated['order_number'] ?? null,
            $validated['email'] ?? null
        );

        if (!$order) {
            return back()->withErrors([
                'order_number' => 'Sipariş bulunamadı.',
            ])->withInput();
        }

        $order->loadMissing(['items.product', 'shippingAddress']);

        $orderItemIds = collect($validated['items'])->unique()->all();
        $orderItems = $order->items()->whereIn('id', $orderItemIds)->get();

        if ($orderItems->isEmpty()) {
            return back()->withErrors([
                'items' => 'Geçerli ürün seçmediniz.',
            ])->withInput();
        }

        $existingReturnItemIds = ReturnItem::whereHas('returnRequest', function ($query) use ($order) {
            $query->where('order_id', $order->id);
        })->pluck('order_item_id')->all();

        $duplicateSelection = $orderItems->pluck('id')->filter(function ($id) use ($existingReturnItemIds) {
            return in_array($id, $existingReturnItemIds, true);
        })->all();

        if (!empty($duplicateSelection)) {
            return back()->withErrors([
                'items' => 'Seçtiğiniz ürünlerden bazıları için zaten iade talebi bulunuyor.',
            ])->withInput();
        }

        $shipping = $order->shippingAddress;

        $returnRequest = ReturnRequest::create([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => $order->user_id,
            'guest_id' => $order->guest_id,
            'customer_name' => $shipping?->fullname ?? Auth::user()?->name ?? 'Müşteri',
            'customer_email' => $shipping?->email ?? Auth::user()?->email ?? 'unknown@example.com',
            'customer_phone' => $shipping?->phone,
            'status' => 'pending',
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($orderItems as $item) {
            ReturnItem::create([
                'return_id' => $returnRequest->id,
                'order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'reason' => null,
            ]);
        }

        $this->sendAdminNotification($returnRequest, 'pending');
        $this->sendCustomerNotification($returnRequest, 'pending');
        $this->sendReturnRequestSms($returnRequest);

        app(CustomerLogService::class)->log('İade Talebi Oluşturuldu', null, $returnRequest->toArray());

        return redirect()
            ->route('returns.lookup', [
                'order_number' => $order->order_number,
                'email' => $validated['email'] ?? $shipping?->email ?? Auth::user()?->email,
            ])
            ->with('status', 'İade talebiniz alınmıştır.');
    }

    protected function resolveOrderForLookup(string $orderNumber, ?string $email): ?Order
    {
        $query = Order::query()->where('order_number', $orderNumber);

        if (Auth::check()) {
            $query->where(function ($q) use ($email) {
                $q->where('user_id', Auth::id());
                if ($email) {
                    $q->orWhereHas('shippingAddress', fn($address) => $address->where('email', $email));
                }
            });
        } else {
            $query->where(function ($q) use ($email) {
                $q->whereHas('shippingAddress', function ($address) use ($email) {
                    $address->where('email', $email);
                });
            });
        }

        return $query->with(['shippingAddress', 'returns'])->first();
    }

    protected function resolveOrderForCreation(?int $orderId, ?string $orderNumber, ?string $email): ?Order
    {
        if ($orderId && Auth::check()) {
            return Order::where('id', $orderId)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$orderNumber || !$email) {
            return null;
        }

        return $this->resolveOrderForLookup($orderNumber, $email);
    }

    protected function sendAdminNotification(ReturnRequest $return, string $status): void
    {
        $settings = Setting::first();
        $notifyMail = $settings?->notify_mail;

        if (!$notifyMail) {
            return;
        }

        try {
            Mail::to($notifyMail)->send(new NotifyAdminReturnMail($return, $status));
        } catch (\Exception $e) {
            Log::warning('Admin iade bildirimi gönderilemedi', [
                'return_id' => $return->id,
                'notify_mail' => $notifyMail,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendCustomerNotification(ReturnRequest $return, string $status): void
    {
        $email = $return->customer_email;
        if (!$email) {
            return;
        }
        try {
            Mail::to($email)->send(new ReturnStatusMail($return, $status));
        } catch (\Exception $e) {
            Log::error('Müşteri iade bildirimi gönderilemedi', [
                'return_id' => $return->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendReturnRequestSms(ReturnRequest $return): void
    {
        $phone = $return->customer_phone;
        if (!$phone) {
            return;
        }

        $message = "Sayın {$return->customer_name}, iade talebiniz (#{$return->order_number}) alınmıştır. Talebiniz incelendikten sonra size bilgi verilecektir.";

        try {
            app(SmsService::class)->sendSms($phone, $message);
        } catch (\Exception $e) {
            Log::error('Müşteri SMS bildirimi gönderilemedi', [
                'return_id' => $return->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
