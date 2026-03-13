<?php

namespace App\Http\Controllers\Admin;

use App\Mail\OrderCanceledMail;
use App\Mail\OrderCompletedMail;
use App\Mail\OrderStatusMail;
use App\Mail\PaymentStatusWireMail;
use App\Models\Order;
use App\Models\ProductAttributeTerm;
use App\Models\Setting;
use App\Models\Shipping;
use App\Models\ShippingCompany;
use App\Models\Store;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\Logs\AdminLogService;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    protected function renderList($title, $view, $query, array $extra = [])
    {
        $orders = $query->with(['user', 'returns', 'shipping.shippingCompany', 'shippingAddress', 'billingAddress'])->latest()->paginate(10)->withQueryString();
        $shippingCompanies = ShippingCompany::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'tracking_link']);
        return view($view, array_merge(compact('orders', 'shippingCompanies', 'title'), $extra));
    }

    public function index(Request $request)
    {
        $query = Order::query();

        $filters = [
            'status' => $request->get('status'),
            'order_number' => trim($request->get('order_number', '')),
            'customer' => trim($request->get('customer', '')),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'total_min' => $request->get('total_min'),
            'total_max' => $request->get('total_max'),
        ];

        if (!in_array($filters['status'], ['new', 'pending', 'completed', 'canceled'], true)) {
            $filters['status'] = null;
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['order_number'] !== '') {
            $query->where('order_number', 'like', '%' . $filters['order_number'] . '%');
        }

        if ($filters['customer'] !== '') {
            $term = $filters['customer'];
            $query->where(function ($q) use ($term) {
                $q->whereHas('user', function ($userQuery) use ($term) {
                    $userQuery->where('name', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%')
                        ->orWhere('username', 'like', '%' . $term . '%');
                })->orWhereHas('shippingAddress', function ($addressQuery) use ($term) {
                    $addressQuery->where('fullname', 'like', '%' . $term . '%');
                })->orWhereHas('billingAddress', function ($addressQuery) use ($term) {
                    $addressQuery->where('fullname', 'like', '%' . $term . '%');
                });
            });
        }

        if ($filters['start_date'] && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        } else {
            $filters['start_date'] = null;
        }

        if ($filters['end_date'] && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        } else {
            $filters['end_date'] = null;
        }

        if ($request->filled('total_min') && is_numeric($filters['total_min'])) {
            $query->where('total', '>=', (float) $filters['total_min']);
        } else {
            $filters['total_min'] = null;
        }

        if ($request->filled('total_max') && is_numeric($filters['total_max'])) {
            $query->where('total', '<=', (float) $filters['total_max']);
        } else {
            $filters['total_max'] = null;
        }

        return $this->renderList('Tüm Siparişler', 'admin.pages.orders.index', $query, [
            'filters' => $filters,
        ]);
    }

    public function show(int $id)
    {
        $order = Order::with(['user', 'items.product', 'returns', 'shipping.shippingCompany', 'shippingAddress', 'billingAddress'])->findOrFail($id);
        $store = Store::first();
        $shippingCompanies = ShippingCompany::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'tracking_link']);
        return view('admin.pages.orders.show', compact('order', 'store', 'shippingCompanies'));
    }

    public function new()
    {
        $query = Order::where('status', 'new')->where('created_at', '>=', Carbon::now()->subDays(7));
        return $this->renderList('Yeni Gelen Siparişler (Son 7 Gün)', 'admin.pages.orders.new', $query);
    }

    public function pending()
    {
        $query = Order::whereIn('status', ['pending', 'new']);
        return $this->renderList('Bekleyen Siparişler', 'admin.pages.orders.pending', $query);
    }

    public function canceled()
    {
        $query = Order::where('status', 'canceled');
        return $this->renderList('İptal Edilen Siparişler', 'admin.pages.orders.canceled', $query);
    }

    public function completed()
    {
        $query = Order::where('status', 'completed');
        return $this->renderList('Tamamlanan Siparişler', 'admin.pages.orders.completed', $query);
    }

    public function edit(int $id)
    {
        $order = Order::with(['user', 'shippingAddress', 'billingAddress'])->findOrFail($id);
        return view('admin.pages.orders.edit', compact('order'));
    }

    public function update(Request $request, int $id)
    {
        $order = Order::with(['user', 'shippingAddress', 'billingAddress'])->findOrFail($id);
        $before = $order->toArray();

        $validator = Validator::make($request->all(), [
            'shipping_fullname' => ['nullable', 'string', 'max:255', 'special_characters'],
            'shipping_phone' => ['nullable', 'string', 'max:50'],
            'shipping_tc' => ['nullable', 'string', 'max:11'],
            'shipping_city' => ['nullable', 'string', 'max:100'],
            'shipping_state' => ['nullable', 'string', 'max:100'],
            'shipping_zip' => ['nullable', 'string', 'max:20'],
            'shipping_address' => ['nullable', 'string', 'special_characters'],
            'billing_fullname' => ['nullable', 'string', 'max:255', 'special_characters'],
            'billing_phone' => ['nullable', 'string', 'max:50'],
            'billing_tc' => ['nullable', 'string', 'max:11'],
            'billing_city' => ['nullable', 'string', 'max:100'],
            'billing_state' => ['nullable', 'string', 'max:100'],
            'billing_zip' => ['nullable', 'string', 'max:20'],
            'billing_address' => ['nullable', 'string', 'special_characters'],
        ], [], [
            'shipping_fullname' => 'Teslimat ad soyad',
            'shipping_phone' => 'Teslimat telefon',
            'shipping_tc' => 'Teslimat TC kimlik no',
            'shipping_city' => 'Teslimat şehri',
            'shipping_state' => 'Teslimat ilçesi',
            'shipping_zip' => 'Teslimat posta kodu',
            'shipping_address' => 'Teslimat adresi',
            'billing_fullname' => 'Fatura ad soyad',
            'billing_phone' => 'Fatura telefon',
            'billing_tc' => 'Fatura TC kimlik no',
            'billing_city' => 'Fatura şehri',
            'billing_state' => 'Fatura ilçesi',
            'billing_zip' => 'Fatura posta kodu',
            'billing_address' => 'Fatura adresi',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }

        $data = $validator->validated();
        $shippingAddressText = trim($data['shipping_address'] ?? '');
        if ($shippingAddressText !== '') {
            $shippingAddress = $order->shippingAddress ?: new UserAddress([
                'user_id' => $order->user_id,
                'type' => 'shipping',
            ]);
            $shippingAddress->title = $shippingAddress->title ?: 'Teslimat Adresi';
            $shippingAddress->fullname = $data['shipping_fullname'] ?? $order->user?->name;
            $shippingAddress->phone = $data['shipping_phone'] ?? $order->customer_phone;
            $shippingAddress->tc = $data['shipping_tc'] ?? null;
            $shippingAddress->city = $data['shipping_city'] ?? null;
            $shippingAddress->state = $data['shipping_state'] ?? null;
            $shippingAddress->zip = $data['shipping_zip'] ?? null;
            $shippingAddress->address = $shippingAddressText;
            $shippingAddress->save();

            $order->shipping_address_id = $shippingAddress->id;
            $order->shipping_address = trim(implode(' ', array_filter([
                $shippingAddressText,
                $shippingAddress->state,
                $shippingAddress->city,
                $shippingAddress->zip,
            ])));
        } else {
            $order->shipping_address_id = null;
            $order->shipping_address = null;
        }

        $billingAddressText = trim($data['billing_address'] ?? '');
        if ($billingAddressText !== '') {
            $billingAddress = $order->billingAddress ?: new UserAddress([
                'user_id' => $order->user_id,
                'type' => 'billing',
            ]);
            $billingAddress->title = $billingAddress->title ?: 'Fatura Adresi';
            $billingAddress->fullname = $data['billing_fullname'] ?? $order->user?->name;
            $billingAddress->phone = $data['billing_phone'] ?? $order->customer_phone;
            $billingAddress->tc = $data['billing_tc'] ?? null;
            $billingAddress->city = $data['billing_city'] ?? null;
            $billingAddress->state = $data['billing_state'] ?? null;
            $billingAddress->zip = $data['billing_zip'] ?? null;
            $billingAddress->address = $billingAddressText;
            $billingAddress->save();

            $order->billing_address_id = $billingAddress->id;
            $order->billing_address = trim(implode(' ', array_filter([
                $billingAddressText,
                $billingAddress->state,
                $billingAddress->city,
                $billingAddress->zip,
            ])));
        } else {
            $order->billing_address_id = null;
            $order->billing_address = null;
        }

        $order->save();

        app(AdminLogService::class)->log('Sipariş Güncellendi (Adres Değişikliği)', $before, $order->fresh(['user', 'shippingAddress', 'billingAddress'])->toArray());

        return $this->jsonSuccess('Sipariş adresleri güncellendi');
    }

    public function updateStatus(Request $request, int $id)
    {
        $order = Order::with(['items.product', 'shipping', 'user', 'shippingAddress', 'billingAddress'])->findOrFail($id);
        $before = $order->toArray();

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['new', 'pending', 'completed', 'canceled'])],
            'shipping_company_id' => ['nullable', 'integer', 'exists:shipping_companies,id'],
            'tracking_no' => ['nullable', 'string', 'max:255'],
            'cancel_reason' => ['nullable', 'string', 'max:500'],
        ], [], [
            'status' => 'Durum',
            'shipping_company_id' => 'Kargo firması',
            'tracking_no' => 'Takip numarası',
            'cancel_reason' => 'İptal sebebi',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }

        $data = $validator->validated();
        $newStatus = $data['status'];
        $trackingNo = isset($data['tracking_no']) ? trim($data['tracking_no']) : null;
        $cancelReason = isset($data['cancel_reason']) ? trim($data['cancel_reason']) : null;

        if ($newStatus === 'completed') {
            if (empty($data['shipping_company_id'])) {
                return $this->jsonError('Lütfen kargo firması seçiniz.', 422);
            }
            if (empty($trackingNo)) {
                return $this->jsonError('Lütfen kargo takip numarası giriniz.', 422);
            }
        }

        if ($newStatus === 'canceled' && empty($cancelReason)) {
            return $this->jsonError('Lütfen iptal sebebini belirtiniz.', 422);
        }

        $order->status = $newStatus;
        $order->save();
        $successMsg = 'Sipariş durumu güncellendi';

        // Havale ödemesi bekleyen bir sipariş tamamlanıyorsa ve ödeme güncelleme onayı geldiyse
        if ($request->input('update_payment') == 1 && $order->method === 'wire' && $order->is_paid == 0) {
            $order->is_paid = 1;
            $order->save();

            $paymentNotification = $this->sendPaymentWireNotification($order, 'completed', [
                'message' => 'Ödemeniz başarıyla tamamlandı.',
            ]);

            if ($paymentNotification) {
                $successMsg .= ' ve ' . $paymentNotification;
            }
        }

        if ($newStatus === 'completed') {
            $shippingCompany = ShippingCompany::find($data['shipping_company_id']);
            if (!$shippingCompany) {
                return $this->jsonError('Seçilen kargo firması bulunamadı.', 404);
            }
            $shipping = $order->shipping;
            if (!$shipping) {
                $shipping = new Shipping();
                $shipping->order_id = $order->id;
            }
            $shipping->shipping_company_id = $shippingCompany->id;
            $shipping->shipping_address = $order->shipping_address ?? $order->shippingAddress?->address ?? null;
            $shipping->tracking_no = $trackingNo;
            $shipping->tracking_link = $shippingCompany->tracking_link;
            $shipping->delivered_at = Carbon::now();
            $shipping->customer_name = $order->shippingAddress?->fullname
                ?? $order->billingAddress?->fullname
                ?? $order->user?->name;
            $shipping->customer_phone = $order->shippingAddress?->phone
                ?? $order->billingAddress?->phone;
            $shipping->customer_email = $order->customer_email;
            $shipping->save();

            $successMsg = 'Sipariş tamamlandı.';
        } elseif ($newStatus === 'canceled') {
            $reason = $cancelReason ?? '';
            $order->notes = trim($order->notes ? $order->notes . "\nİptal sebebi: " . $reason : 'İptal sebebi: ' . $reason);
            $order->save();

            if ($order->shipping) {
                $order->shipping()->delete();
            }

            $successMsg = 'Sipariş iptal edildi.';
        } else {
            // For other statuses remove shipping association
            if ($order->shipping && $newStatus !== 'completed') {
                $order->shipping()->delete();
            }
        }

        $messageForStatus = match ($newStatus) {
            'new' => 'Siparişiniz başarıyla alındı ve sistemimize kaydedildi.',
            'pending' => 'Siparişiniz hazırlanıyor, en kısa sürede kargoya verilecektir.',
            default => null,
        };

        $notificationMessage = $this->sendOrderStatusNotification($order, $newStatus, [
            'cancel_reason' => $cancelReason,
            'message' => $messageForStatus,
        ]);

        if ($notificationMessage) {
            $successMsg .= ' ' . $notificationMessage;
        }

        $this->sendOrderStatusSms($order, $newStatus, [
            'cancel_reason' => $cancelReason,
            'tracking_no' => $trackingNo,
            'shipping_company' => $newStatus === 'completed' ? $shippingCompany?->name : null,
        ]);

        app(AdminLogService::class)->log('Sipariş Durumu Güncellendi', $before, $order->fresh(['items.product', 'shipping', 'user', 'shippingAddress', 'billingAddress'])->toArray());

        return $this->jsonSuccess($successMsg);
    }

    public function paymentStatusWire(Request $request, int $id)
    {
        $order = Order::findOrFail($id);
        $before = $order->toArray();
        $status = $request->input('status', 1);

        $order->is_paid = $status;
        $order->save();

        $successMsg = 'Ödeme durumu güncellendi.';

        if ($status == 1) {
            $notificationMessage = $this->sendPaymentWireNotification($order, 'completed', [
                'message' => 'Ödemeniz başarıyla tamamlandı.',
            ]);

            if ($notificationMessage) {
                $successMsg .= ' ' . $notificationMessage;
            }

            $this->sendPaymentWireSms($order);
        }

        app(AdminLogService::class)->log('Sipariş Ödeme Durumu Güncellendi', $before, $order->fresh()->toArray());

        return $this->jsonSuccess($successMsg);
    }

    public function destroy(int $id)
    {
        $order = Order::with('items', 'shipping')->findOrFail($id);
        $before = $order->toArray();
        if ($order->shipping) {
            $order->shipping()->delete();
        }
        if ($order->items()->exists()) {
            $order->items()->delete();
        }
        $order->delete();

        app(AdminLogService::class)->log('Sipariş Silindi', $before, null);

        return $this->jsonSuccess('Sipariş silindi');
    }

    public function customerOrders(int $customerId)
    {
        $customer = User::where('role', 0)->findOrFail($customerId);
        $query = Order::where('user_id', $customerId);
        $orders = $query->with(['user', 'shipping.shippingCompany', 'shippingAddress', 'billingAddress'])->latest()->paginate(10)->withQueryString();
        $shippingCompanies = ShippingCompany::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'tracking_link']);
        $title = $customer->name . ' - Tüm Siparişler';
        return view('admin.pages.orders.customer', compact('orders', 'shippingCompanies', 'title', 'customer'));
    }

    protected function shouldNotifyCustomers(): bool
    {
        $store = $this->store ?? Store::first();
        return (bool) ($store?->notify_order_complete ?? true);
    }

    protected function resolveOrderEmail(Order $order): ?string
    {
        if ($order->customer_email) {
            return $order->customer_email;
        }

        if ($order->user && $order->user->email) {
            return $order->user->email;
        }

        if ($order->billingAddress?->email) {
            return $order->billingAddress->email;
        }

        if ($order->shippingAddress?->email) {
            return $order->shippingAddress->email;
        }

        if ($order->guest_id) {
            $guestAddress = UserAddress::where('guest_id', $order->guest_id)
                ->orderByDesc('created_at')
                ->first();
            if ($guestAddress && $guestAddress->email) {
                return $guestAddress->email;
            }
        }

        return null;
    }

    protected function sendOrderStatusNotification(Order $order, string $status, array $context = []): ?string
    {
        if (!$this->shouldNotifyCustomers()) {
            return null;
        }

        $freshOrder = $order->fresh(['shipping.shippingCompany', 'items.product', 'user', 'shippingAddress', 'billingAddress']);
        $recipient = $this->resolveOrderEmail($freshOrder);

        if (!$recipient) {
            return 'Müşteri e-posta adresi bulunamadı.';
        }

        if ($status === 'completed') {
            Mail::to($recipient)->send(new OrderCompletedMail($freshOrder));
        } elseif ($status === 'canceled') {
            $reason = $context['cancel_reason'] ?? '';
            Mail::to($recipient)->send(new OrderCanceledMail($freshOrder, $reason));
        } else {
            $message = $context['message'] ?? null;
            Mail::to($recipient)->send(new OrderStatusMail($freshOrder, $status, $message));
        }

        return 'Bilgilendirme e-postası ' . $recipient . ' adresine gönderildi.';
    }
    protected function sendPaymentWireNotification(Order $order, string $status, array $context = []): ?string
    {
        if (!$this->shouldNotifyCustomers()) {
            return null;
        }
        $recipient = $this->resolveOrderEmail($order);
        if (!$recipient) {
            return 'Müşteri e-posta adresi bulunamadı.';
        }
        Mail::to($recipient)->send(new PaymentStatusWireMail($order, $status, $context));
        return 'Bilgilendirme e-postası ' . $recipient . ' adresine gönderildi.';
    }

    protected function sendOrderStatusSms(Order $order, string $status, array $context = []): void
    {
        $phone = $order->customer_phone;
        if (!$phone) {
            return;
        }

        $message = match ($status) {
            'new' => "Sayın {$order->customer_name}, siparişiniz (#{$order->order_number}) başarıyla alınmıştır. Detaylar için e-postanızı kontrol ediniz.",
            'pending' => "Sayın {$order->customer_name}, siparişiniz (#{$order->order_number}) hazırlanıyor. En kısa sürede kargoya verilecektir.",
            'completed' => isset($context['tracking_no'])
                ? "Sayın {$order->customer_name}, siparişiniz (#{$order->order_number}) kargoya verilmiştir. Takip No: {$context['tracking_no']}"
                : "Sayın {$order->customer_name}, siparişiniz (#{$order->order_number}) tamamlanmıştır.",
            'canceled' => isset($context['cancel_reason'])
                ? "Sayın {$order->customer_name}, siparişiniz (#{$order->order_number}) iptal edilmiştir. Sebep: {$context['cancel_reason']}"
                : "Sayın {$order->customer_name}, siparişiniz (#{$order->order_number}) iptal edilmiştir.",
            default => null,
        };

        if (!$message) {
            return;
        }

        try {
            app(SmsService::class)->sendSms($phone, $message);
        } catch (\Exception $e) {
            Log::warning('Müşteri SMS bildirimi gönderilemedi', [
                'order_id' => $order->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendPaymentWireSms(Order $order): void
    {
        $phone = $order->customer_phone;
        if (!$phone) {
            return;
        }

        $message = "Sayın {$order->customer_name}, siparişiniz (#{$order->order_number}) için ödemeniz başarıyla tamamlanmıştır.";

        try {
            app(SmsService::class)->sendSms($phone, $message);
        } catch (\Exception $e) {
            Log::warning('Müşteri SMS bildirimi gönderilemedi', [
                'order_id' => $order->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function reportOrderToPdf(Request $request, $id)
    {
        try {
            $order = Order::with(['items.product', 'items.variant.attribute', 'items.variant.term', 'shippingAddress', 'billingAddress', 'user'])
                ->findOrFail($id);

            $settings = Setting::first();
            $store = Store::first();

            $customerName = $order->user?->name
                ?? $order->shippingAddress?->fullname
                ?? $order->billingAddress?->fullname
                ?? 'Misafir Müşteri';

            $customerAddress = $order->shippingAddress?->address
                ?? $order->billingAddress?->address
                ?? $order->shipping_address
                ?? 'Adres belirtilmemiş';

            $customerPhone = $order->customer_phone
                ?? $order->shippingAddress?->phone
                ?? $order->billingAddress?->phone
                ?? 'Telefon belirtilmemiş';

            $items = $order->items->map(function ($item) {
                $variants = collect();
                if (!empty($item->variant_ids) && is_array($item->variant_ids)) {
                    $variants = ProductAttributeTerm::with(['attribute', 'term'])
                        ->whereIn('id', $item->variant_ids)
                        ->get();
                } elseif ($item->variant) {
                    $variants = collect([$item->variant]);
                }

                return [
                    'product_title' => $item->product?->title ?? 'Silinmiş Ürün',
                    'variants' => $variants->map(function ($v) {
                        $term = $v->term;
                        $colorMatch = (isset($term->value) && str_starts_with($term->value, '#')) ? $term->value : null;
                        return [
                            'attribute_name' => $v->attribute?->name ?? 'Seçenek',
                            'term_name' => $term->name ?? 'N/A',
                            'color_match' => $colorMatch,
                        ];
                    })->toArray(),
                    'price' => (float) $item->price,
                    'quantity' => (int) $item->quantity,
                    'total' => (float) $item->total,
                ];
            });

            $subtotal = $order->items->sum('total');
            $taxEnabled = $store->tax_enabled ?? false;
            $taxRate = (float) ($store->tax_rate ?? 0);
            $taxAmount = $taxEnabled ? ($subtotal * $taxRate) / 100 : 0;
            $shippingLimit = (float) ($store->shipping_price_limit ?? 0);
            $shippingPrice = (float) ($store->shipping_price ?? 0);
            $hasShipping = $subtotal < $shippingLimit;
            $shippingAmount = $hasShipping ? $shippingPrice : 0;

            $data = [
                'order' => [
                    'order_number' => $order->order_number,
                    'created_at' => $order->created_at->format('d.m.Y H:i'),
                    'total' => (float) $order->total,
                    'notes' => $order->notes,
                ],
                'settings' => [
                    'title' => $settings->title ?? '',
                    'address' => $settings->address ?? '',
                ],
                'customer' => [
                    'name' => $customerName,
                    'address' => $customerAddress,
                    'phone' => $customerPhone,
                ],
                'items' => $items,
                'calculations' => [
                    'subtotal' => (float) $subtotal,
                    'tax_enabled' => $taxEnabled,
                    'tax_rate' => $taxRate,
                    'tax_amount' => (float) $taxAmount,
                    'has_shipping' => $hasShipping,
                    'shipping_amount' => (float) $shippingAmount,
                    'grand_total' => (float) $order->total,
                ],
            ];

            // AJAX isteği ise JSON döndür
            if ($request->wantsJson() || $request->ajax()) {
                return $this->jsonSuccess('Sipariş verisi başarıyla alındı.', $data);
            }

            // Normal istek ise view döndür
            return view('admin.pages.orders.report', ['data' => $data]);
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return $this->jsonError('Sipariş verisi alınırken bir hata oluştu: ' . $e->getMessage(), 500);
            }
            abort(500, 'Sipariş verisi alınırken bir hata oluştu.');
        }
    }
}
