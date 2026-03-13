@extends('admin.layouts.app')
@section('title', 'Sipariş Detayı')
@php
    $statusMeta = [
        'new' => [
            'label' => 'Yeni',
            'badgeClass' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200',
        ],
        'pending' => [
            'label' => 'Beklemede',
            'badgeClass' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200',
        ],
        'completed' => [
            'label' => 'Tamamlandı',
            'badgeClass' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-200',
        ],
        'canceled' => [
            'label' => 'İptal Edildi',
            'badgeClass' => 'bg-rose-100 text-rose-700 dark:bg-rose-900 dark:text-rose-200',
        ],
    ];
    $badgeBaseClass = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium';
@endphp
@section('content')
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ url()->previous() }}"
            class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
            <i class="ri-arrow-left-line"></i>
            <span>Geri Dön</span>
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.orders.edit', $order->id) }}"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 text-sm">
                <i class="ri-edit-line"></i>
                <span>Siparişi Düzenle</span>
            </a>
            <button type="button" id="print-invoice-btn" data-order-id="{{ $order->id }}"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 text-sm">
                <i class="ri-file-pdf-line"></i>
                <span>Fiş Yazdır</span>
            </button>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2">
            <div class="rounded-xl bg-white border border-gray-200 dark:border-gray-800 shadow-lg p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <i class="ri-shopping-basket-line"></i>
                    <span>Sipariş Bilgileri</span>
                </h2>
                @php
                    $methodMeta = [
                        'card' => [
                            'label' => 'Kredi / Banka Kartı',
                            'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                            'icon' => 'ri-bank-card-2-line',
                        ],
                        'wire' => [
                            'label' => 'Havale / EFT',
                            'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
                            'icon' => 'ri-exchange-dollar-line',
                        ],
                    ][$order->method ?? 'card'] ?? [
                        'label' => ucfirst($order->method ?? 'Bilinmiyor'),
                        'class' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                        'icon' => 'ri-question-line',
                    ];
                    $paymentStatusMeta = [
                        1 => [
                            'label' => 'Başarılı',
                            'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
                            'icon' => 'ri-check-line',
                        ],
                        0 => [
                            'label' => 'Bekleniyor',
                            'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
                            'icon' => 'ri-time-line',
                        ],
                    ];
                @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-500 dark:text-gray-400">Sipariş No</label>
                        <p class="text-sm font-semibold">{{ $order->order_number }}</p>
                    </div>
                    <div class="w-[max-content]">
                        <label class="text-xs text-gray-500 dark:text-gray-400">Durum</label>
                        <div class="mt-1 flex flex-col gap-2">
                            @php $meta = $statusMeta[$order->status] ?? $statusMeta['new']; @endphp
                            <span class="{{ $badgeBaseClass }} {{ $meta['badgeClass'] }}"
                                data-status-badge>{{ $meta['label'] }}</span>
                            <select
                                class="text-xs px-2 py-1 rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-black order-status-select"
                                data-order-status-select data-status-url="{{ route('admin.orders.status', $order->id) }}"
                                data-order-id="{{ $order->id }}" data-order-number="{{ $order->order_number }}"
                                data-method="{{ $order->method }}" data-is-paid="{{ $order->is_paid }}"
                                data-prev="{{ $order->status }}">
                                @foreach ($statusMeta as $key => $definition)
                                    <option value="{{ $key }}" @selected($order->status === $key)>
                                        {{ $definition['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 dark:text-gray-400">Tarih</label>
                        <p class="text-sm">{{ $order->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 dark:text-gray-400">Toplam Tutar</label>
                        <p class="text-sm font-semibold text-green-600 dark:text-green-400">
                            {{ number_format($order->total, 2, ',', '.') }} ₺
                        </p>
                    </div>
                    <div class="col-span-1">
                        <label class="text-xs text-gray-500 dark:text-gray-400">Ödeme Yöntemi</label>
                        <p class="mt-1">
                            <span
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium {{ $methodMeta['class'] }}">
                                <i class="{{ $methodMeta['icon'] }} text-sm"></i>
                                {{ $methodMeta['label'] }}
                            </span>
                        </p>
                    </div>
                    <div class="col-span-1">
                        <label class="text-xs text-gray-500 dark:text-gray-400">Ödeme Durumu</label>
                        <p class="mt-1">
                            <span id="payment-status-badge"
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium {{ $paymentStatusMeta[$order->is_paid]['class'] }}">
                                {{ $paymentStatusMeta[$order->is_paid]['label'] }} <i
                                    class="{{ $paymentStatusMeta[$order->is_paid]['icon'] }} text-sm"></i>
                            </span>
                        </p>
                        @if ($order->method === 'wire')
                            <div class="mt-2">
                                <select onchange="handleWirePaymentChange(this.value)"
                                    class="block bg-gray-100 px-2 py-1 rounded-2xl text-xs border-gray-200 dark:border-gray-800 rounded-md text-gray-900 dark:text-gray-100 focus:ring-black">
                                    <option value="0" {{ $order->is_paid == 0 ? 'selected' : '' }}>Bekleniyor</option>
                                    <option value="1" {{ $order->is_paid == 1 ? 'selected' : '' }}>Ödeme Başarılı
                                    </option>
                                </select>
                            </div>
                            <script>
                                function handleWirePaymentChange(newStatus) {
                                    const orderId = "{{ $order->id }}";
                                    const message = "Ödeme durumunu değiştirmek istediğinize emin misiniz?";
                                    showConfirmModal(message, function() {
                                        fetch(`/admin/orders/${orderId}/payment-status-wire?status=${newStatus}`, {
                                                method: 'GET',
                                                headers: {
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                }
                                            })
                                            .then(data => {
                                                if (data.status === 'success' || data.success) {
                                                    showSuccess(data.msg || "İşlem başarıyla tamamlandı.");
                                                    setTimeout(() => location.reload(), 1000);
                                                } else {
                                                    showSuccess(data.msg || "İşlem güncellendi.");
                                                    setTimeout(() => location.reload(), 1500);
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Hata:', error);
                                                showSuccess("İşlem tamamlandı (Sayfa yenileniyor...)");
                                                setTimeout(() => location.reload(), 1500);
                                            });
                                    }, function() {
                                        location.reload();
                                    });
                                }
                            </script>
                        @endif
                    </div>
                </div>
                @if ($order->notes)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                        <label class="text-xs text-gray-500 dark:text-gray-400">Notlar</label>
                        <p class="text-sm mt-1">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>
            <div class="rounded-xl bg-white border border-gray-200 dark:border-gray-800 shadow-lg p-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <i class="ri-box-3-line"></i>
                    <span>Sipariş Ürünleri</span>
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="text-left px-4 py-3 font-semibold">Ürün</th>
                                <th class="text-left px-4 py-3 font-semibold">Fiyat</th>
                                <th class="text-center px-4 py-3 font-semibold">Adet</th>
                                <th class="text-right px-4 py-3 font-semibold">Toplam</th>
                                <th class="text-right px-4 py-3 font-semibold">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                @php
                                    $product = $item->product;
                                    $productPhoto = $product?->photo ? asset($product->photo) : null;
                                @endphp
                                <tr
                                    class="border-t border-gray-100 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            @if ($productPhoto)
                                                <img src="{{ $productPhoto }}" alt="{{ $product->title }}"
                                                    class="w-12 h-12 object-cover rounded-md border border-gray-200 dark:border-gray-800">
                                            @else
                                                <div
                                                    class="w-12 h-12 bg-gray-100 dark:bg-gray-900 text-gray-400 dark:text-gray-500 rounded-md flex items-center justify-center border border-dashed border-gray-300 dark:border-gray-700">
                                                    <i class="ri-image-line text-lg"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-medium">{{ $product?->title ?? 'Silinmiş Ürün' }}</p>
                                                @if ($item->variant_ids)
                                                    <div class="mt-1 flex flex-wrap gap-1">
                                                        @foreach ($item->variants() as $variant)
                                                            @php
                                                                $term = $variant->term;
                                                                $colorMatch =
                                                                    isset($term->value) &&
                                                                    str_starts_with($term->value, '#')
                                                                        ? $term->value
                                                                        : null;
                                                            @endphp
                                                            <span
                                                                class="inline-flex items-center gap-1.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                                                {{ $variant->attribute?->name }}:
                                                                @if ($colorMatch)
                                                                    <span
                                                                        class="w-2.5 h-2.5 rounded-full border border-gray-300"
                                                                        style="background-color: {{ $colorMatch }}"></span>
                                                                @endif
                                                                {{ $term?->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @elseif ($item->variant)
                                                    @php
                                                        $term = $item->variant->term;
                                                        $colorMatch =
                                                            isset($term->value) && str_starts_with($term->value, '#')
                                                                ? $term->value
                                                                : null;
                                                    @endphp
                                                    <p class="text-[10px] text-gray-500 flex items-center gap-1.5">
                                                        <span>{{ $item->variant->attribute?->name }}:</span>
                                                        @if ($colorMatch)
                                                            <span class="w-2.5 h-2.5 rounded-full border border-gray-300"
                                                                style="background-color: {{ $colorMatch }}"></span>
                                                        @endif
                                                        <span>{{ $term?->name }}</span>
                                                    </p>
                                                @endif
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    #{{ $product?->id ?? '—' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">{{ number_format($item->price, 2, ',', '.') }} ₺</td>
                                    <td class="px-4 py-3 text-center">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">
                                        {{ number_format($item->total, 2, ',', '.') }} ₺
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if ($product)
                                            <a href="{{ route('admin.products.edit', $product->id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                                <i class="ri-eye-line"></i>
                                                <span>Ürün</span>
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400">Ürün silinmiş</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-900">
                            @php
                                $subtotal = $order->items->sum('total');
                                $taxEnabled = $store->tax_enabled ?? false;
                                $taxRate = $store->tax_rate ?? 0;
                                $taxAmount = $taxEnabled ? ($subtotal * $taxRate) / 100 : 0;
                                $shippingLimit = $store->shipping_price_limit ?? 0;
                                $shippingPrice = $store->shipping_price ?? 0;
                                $hasShipping = $subtotal < $shippingLimit;
                                $shippingAmount = $hasShipping ? $shippingPrice : 0;
                            @endphp
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right font-semibold">Ara Toplam:</td>
                                <td class="px-4 py-3 text-right">
                                    {{ number_format($subtotal, 2, ',', '.') }} ₺
                                </td>
                                <td></td>
                            </tr>
                            @if ($taxEnabled)
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-semibold">
                                        KDV (%{{ $taxRate }}):
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        {{ number_format($taxAmount, 2, ',', '.') }} ₺
                                    </td>
                                    <td></td>
                                </tr>
                            @endif
                            @if ($hasShipping)
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-semibold">Kargo Ücreti:</td>
                                    <td class="px-4 py-3 text-right">
                                        {{ number_format($shippingAmount, 2, ',', '.') }} ₺
                                    </td>
                                    <td></td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right font-semibold">Genel Toplam:</td>
                                <td class="px-4 py-3 text-right font-bold text-lg text-green-600 dark:text-green-400">
                                    {{ number_format($order->total, 2, ',', '.') }} ₺
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div>
            <div class="rounded-xl bg-white border border-gray-200 dark:border-gray-800 shadow-lg p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <i class="ri-user-line"></i>
                    <span>Müşteri Bilgileri</span>
                </h2>
                @php
                    $customerName =
                        $order->user?->name ??
                        ($order->shippingAddress?->fullname ??
                            ($order->billingAddress?->fullname ?? 'Misafir Müşteri'));
                    $customerEmail =
                        $order->user?->email ??
                        ($order->shippingAddress?->email ?? ($order->billingAddress?->email ?? '—'));
                    $customerPhone =
                        $order->user?->phone ?? ($order->shippingAddress?->phone ?? $order->billingAddress?->phone);
                    $customerTc = $order->shippingAddress?->tc ?? $order->billingAddress?->tc;
                @endphp
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-200 dark:border-gray-800">
                    @php
                        $name = trim($customerName);
                        $words = preg_split('/\s+/', $name);
                        $initials = '';
                        if (!empty($words[0])) {
                            $initials .= mb_strtoupper(mb_substr($words[0], 0, 1, 'UTF-8'), 'UTF-8');
                        }
                        if (count($words) > 1 && !empty($words[count($words) - 1])) {
                            $initials .= mb_strtoupper(mb_substr($words[count($words) - 1], 0, 1, 'UTF-8'), 'UTF-8');
                        }
                    @endphp
                    <div
                        class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white text-lg font-semibold flex-shrink-0">
                        {{ $initials ?: '?' }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold">{{ $customerName }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $customerEmail }}</p>
                        @if ($customerPhone)
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $customerPhone }}</p>
                        @endif
                        @if ($customerTc)
                            <p class="text-xs text-gray-500 dark:text-gray-400">TC: {{ $customerTc }}</p>
                        @endif
                    </div>
                </div>
                @if ($order->user_id)
                    <div class="space-y-3">
                        <div class="pt-3">
                            <a href="{{ route('admin.customers.show', $order->user_id) }}"
                                class="inline-flex items-center gap-2 w-full justify-center px-4 py-2 rounded-md text-sm font-medium border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <i class="ri-user-line"></i>
                                <span>Müşteri Detayı</span>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="space-y-1">
                        <div class="pt-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Misafir Müşteri</span>
                        </div>
                    </div>
                @endif
            </div>
            @if ($order->shipping)
                <div class="rounded-xl bg-white border border-gray-200 dark:border-gray-800 shadow-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <i class="ri-truck-line"></i>
                        <span>Kargo Bilgileri</span>
                    </h2>
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Kargo Firması</span>
                            <p class="font-semibold">{{ $order->shipping->shippingCompany?->name ?? 'Belirtilmedi' }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Takip Numarası</span>
                            <p class="font-semibold">{{ $order->shipping->tracking_no }}</p>
                        </div>
                        @if ($order->shipping->tracking_link)
                            <div>
                                <a href="{{ $order->shipping->tracking_link }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs">
                                    <i class="ri-external-link-line"></i>
                                    <span>Kargo Takip Sayfası</span>
                                </a>
                            </div>
                        @endif
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Gönderim Tarihi</span>
                            <p class="font-semibold">
                                {{ optional($order->shipping->delivered_at)->format('d.m.Y H:i') ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
            @if ($order->shippingAddress || $order->shipping_address || $order->billingAddress || $order->billing_address)
                <div class="rounded-xl bg-white border border-gray-200 dark:border-gray-800 shadow-lg p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <i class="ri-map-pin-line"></i>
                        <span>Adres Bilgileri</span>
                    </h2>
                    <div class="space-y-4">
                        @if ($order->shippingAddress || $order->shipping_address)
                            <div>
                                <label class="text-xs text-gray-500 dark:text-gray-400">Teslimat Adresi</label>
                                <p class="text-sm mt-1 whitespace-pre-line">
                                    {{ $order->shippingAddress?->address ?? $order->shipping_address }}
                                    @if ($order->shippingAddress?->city || $order->shippingAddress?->state || $order->shippingAddress?->zip)
                                        <br>
                                        {{ $order->shippingAddress?->state }} / {{ $order->shippingAddress?->city }}
                                        {{ $order->shippingAddress?->zip }}
                                    @endif
                                </p>
                                @php

                                    $shippingPhone =
                                        $order->shippingAddress?->phone ??
                                        ($order->shipping?->customer_phone ?? $order->customer_phone);

                                    $shippingTc = $order->shippingAddress?->tc;
                                @endphp
                                @if ($shippingPhone)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Telefon: {{ $shippingPhone }}

                                    </p>
                                @endif
                                @if ($shippingTc)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">TC Kimlik No:
                                        {{ $shippingTc }}
                                    </p>
                                @endif
                            </div>
                        @endif
                        @if ($order->billingAddress || $order->billing_address)
                            <div>
                                <label class="text-xs text-gray-500 dark:text-gray-400">Fatura Adresi</label>
                                <p class="text-sm mt-1 whitespace-pre-line">
                                    {{ $order->billingAddress?->address ?? $order->billing_address }}
                                    @if ($order->billingAddress?->city || $order->billingAddress?->state || $order->billingAddress?->zip)
                                        <br>
                                        {{ $order->billingAddress?->state }} / {{ $order->billingAddress?->city }}
                                        {{ $order->billingAddress?->zip }}
                                    @endif
                                </p>
                                @php
                                    $billingPhone = $order->billingAddress?->phone ?? $order->customer_phone;
                                    $billingTc = $order->billingAddress?->tc;
                                @endphp
                                @if ($billingPhone)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Telefon: {{ $billingPhone }}
                                    </p>
                                @endif
                                @if ($billingTc)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">TC Kimlik No:
                                        {{ $billingTc }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
    @once
        @push('scripts')
            <script>
                window.ShippingCompanies = @json($shippingCompanies ?? []);
                window.OrderStatusMeta = @json($statusMeta);

                (function() {
                    if (window.__orderStatusHandlersInitialized) return;
                    window.__orderStatusHandlersInitialized = true;

                    const badgeBaseClass = @json($badgeBaseClass);

                    function getMeta(status) {
                        const meta = window.OrderStatusMeta || {};
                        return meta[status] || meta['pending'];
                    }

                    function setBadge(status) {
                        const meta = getMeta(status);
                        const $badge = $('[data-status-badge]');
                        if ($badge.length) {
                            $badge.attr('class', badgeBaseClass + ' ' + (meta.badgeClass || 'bg-gray-200'));
                            $badge.text(meta.label || status);
                        }
                    }

                    function revertSelect($select) {
                        const prev = $select.data('prev');
                        if (prev) {
                            $select.val(prev);
                            setBadge(prev);
                        }
                    }

                    function postStatus($select, payload) {
                        const url = $select.data('statusUrl');
                        if (!url) return;

                        $.ajax({
                            url,
                            type: 'POST',
                            data: payload,
                            success: function(res) {
                                showSuccess(res?.msg || 'Sipariş güncellendi');
                                const status = payload.status;
                                setBadge(status);
                                $select.data('prev', status);
                                $select.val(status);
                                setTimeout(() => location.reload(), 1000);
                            },
                            error: function(xhr) {
                                showError(xhr.responseJSON?.msg || 'Hata oluştu');
                                revertSelect($select);
                            },
                        });
                    }

                    function openCompletedModal($select) {
                        const companies = window.ShippingCompanies || [];
                        if (!companies.length) {
                            showError(
                                'Aktif kargo firması bulunamadı. Önce Teslimat Ayarları üzerinden kargo firması ekleyiniz.');
                            revertSelect($select);
                            return;
                        }

                        const $overlay = $(
                            '<div class="fixed inset-0 z-50 bg-black/50 backdrop-blur flex items-center justify-center p-4"></div>'
                        );
                        const $box = $(
                            '<div class="max-w-lg w-full rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black p-5"></div>'
                        );
                        $box.append('<h3 class="text-base font-semibold mb-4">Siparişi Tamamla</h3>');

                        let companySelect = [
                            '<label class="text-xs uppercase tracking-wide text-gray-500">Kargo Firması</label>',
                            '<select class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black" data-completed-company>'
                        ];
                        companySelect.push('<option value="">-- Seçiniz --</option>');
                        companies.forEach(c => companySelect.push(`<option value="${c.id}">${c.name}</option>`));
                        companySelect.push('</select>');

                        $box.append(companySelect.join(''));
                        $box.append(
                            '<label class="text-xs uppercase tracking-wide text-gray-500 block mt-4">Takip Numarası</label>'
                        );
                        $box.append(
                            '<input type="text" data-completed-tracking class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black" placeholder="Kargo takip numarası">'
                        );

                        const $actions = $('<div class="flex items-center justify-end gap-2 mt-6"></div>');
                        const $cancel = $(
                            '<button type="button" class="px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800">Vazgeç</button>'
                        );
                        const $continue = $(
                            '<button type="button" class="px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">Tamamla</button>'
                        );

                        $actions.append($cancel, $continue);
                        $box.append($actions);
                        $overlay.append($box);
                        $('body').append($overlay);

                        $cancel.on('click', () => {
                            $overlay.remove();
                            revertSelect($select);
                        });
                        $continue.on('click', () => {
                            const companyId = $box.find('[data-completed-company]').val();
                            const trackingNo = $box.find('[data-completed-tracking]').val().trim();
                            if (!companyId || !trackingNo) {
                                showError('Lütfen kargo bilgilerini eksiksiz giriniz.');
                                return;
                            }

                            let confirmMessage = 'Sipariş durumunu "Tamamlandı" yapmak istediğinize emin misiniz?';
                            let updatePayment = false;
                            const method = $select.data('method');
                            const isPaid = $select.data('isPaid');

                            if (method === 'wire' && isPaid == 0) {
                                confirmMessage = 'Ödeme durumu bekleniyor, yine de devam etmek istiyor musunuz?';
                                updatePayment = true;
                            }

                            showConfirmModal(confirmMessage, () => {
                                postStatus($select, {
                                    status: 'completed',
                                    shipping_company_id: companyId,
                                    tracking_no: trackingNo,
                                    update_payment: updatePayment ? 1 : 0
                                });
                            }, () => revertSelect($select));
                            $overlay.remove();
                        });
                    }

                    function openCanceledModal($select) {
                        const $overlay = $(
                            '<div class="fixed inset-0 z-50 bg-black/50 backdrop-blur flex items-center justify-center p-4"></div>'
                        );
                        const $box = $(
                            '<div class="max-w-lg w-full rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black p-5"></div>'
                        );
                        $box.append('<h3 class="text-base font-semibold mb-4">Siparişi İptal Et</h3>');
                        $box.append('<label class="text-xs uppercase tracking-wide text-gray-500">İptal Sebebi</label>');
                        $box.append(
                            '<textarea data-cancel-reason rows="4" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black" placeholder="İptal sebebini yazınız..."></textarea>'
                        );

                        const $actions = $('<div class="flex items-center justify-end gap-2 mt-6"></div>');
                        const $cancel = $(
                            '<button type="button" class="px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800">Vazgeç</button>'
                        );
                        const $continue = $(
                            '<button type="button" class="px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">İptal Et</button>'
                        );

                        $actions.append($cancel, $continue);
                        $box.append($actions);
                        $overlay.append($box);
                        $('body').append($overlay);

                        $cancel.on('click', () => {
                            $overlay.remove();
                            revertSelect($select);
                        });
                        $continue.on('click', () => {
                            const reason = $box.find('[data-cancel-reason]').val().trim();
                            if (!reason) {
                                showError('Lütfen iptal sebebini yazınız.');
                                return;
                            }

                            showConfirmModal('Sipariş durumunu "İptal Edildi" yapmak istediğinize emin misiniz?',
                                () => {
                                    postStatus($select, {
                                        status: 'canceled',
                                        cancel_reason: reason
                                    });
                                }, () => revertSelect($select));
                            $overlay.remove();
                        });
                    }

                    $(document).on('focus', '[data-order-status-select]', function() {
                        $(this).data('prev', $(this).val());
                    });

                    $(document).on('change', '[data-order-status-select]', function() {
                        const $select = $(this);
                        const newStatus = $select.val();
                        const prevStatus = $select.data('prev');
                        if (newStatus === prevStatus) return;

                        if (newStatus === 'completed') {
                            openCompletedModal($select);
                            return;
                        }
                        if (newStatus === 'canceled') {
                            openCanceledModal($select);
                            return;
                        }

                        const meta = getMeta(newStatus);
                        showConfirmModal('Sipariş durumunu "' + meta.label + '" yapmak istediğinize emin misiniz?',
                            function() {
                                postStatus($select, {
                                    status: newStatus
                                });
                            }, () => revertSelect($select));
                    });
                })
                ();

                // PDF Fiş Yazdır
                $(document).on('click', '#print-invoice-btn', function() {
                    const orderId = $(this).data('order-id');
                    const url = `/admin/orders/${orderId}/report-pdf`;

                    // UIBlock başlat
                    $.blockUI({
                        message: '<div class="flex flex-col items-center gap-3"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-black dark:border-white"></div><p class="text-sm font-medium">PDF oluşturuluyor...</p></div>',
                        css: {
                            border: 'none',
                            padding: '20px',
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            color: '#000',
                            '-webkit-border-radius': '10px',
                            '-moz-border-radius': '10px',
                            borderRadius: '10px',
                        },
                        overlayCSS: {
                            backgroundColor: 'rgba(0, 0, 0, 0.5)',
                        }
                    });

                    // AJAX ile sipariş verisini al
                    $.ajax({
                        url: url,
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        success: function(response) {
                            if (response.code === 1 && response.data) {
                                // PDF oluşturma sayfasını aç ve data'yı aktar
                                const reportUrl = `/admin/orders/${orderId}/report-pdf?order_id=${orderId}`;
                                const reportWindow = window.open(reportUrl, '_blank');

                                // Data'yı window'a aktar (alternatif yöntem)
                                setTimeout(function() {
                                    if (reportWindow && !reportWindow.closed) {
                                        try {
                                            reportWindow.reportData = response.data;
                                        } catch (e) {
                                            console.log('Cross-origin erişim kısıtlaması');
                                        }
                                    }
                                }, 100);

                                setTimeout(function() {
                                    $.unblockUI();
                                    showSuccess('PDF başarıyla oluşturuldu ve indirildi.');
                                }, 2000);
                            } else {
                                $.unblockUI();
                                showError(response.msg || 'PDF oluşturulurken bir hata oluştu.');
                            }
                        },
                        error: function(xhr) {
                            $.unblockUI();
                            showError(xhr.responseJSON?.msg || 'Sipariş verisi alınırken bir hata oluştu.');
                        }
                    });
                });
            </script>
        @endpush
    @endonce

@endsection
