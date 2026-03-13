@php
    $order = $data->order;
    $items = $order->items ?? collect();
    $statusLabels = [
        'new' => 'Yeni',
        'pending' => 'Beklemede',
        'completed' => 'Tamamlandı',
        'canceled' => 'İptal Edildi',
    ];
@endphp
@extends('frontend.layouts.app')
@section('title', 'Sipariş - ' . $order->order_number)
@section('breadcrumb_title', 'Sipariş #' . $order->order_number)
@section('breadcrumb_actions')
    <a href="{{ route('user.orders') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">
        Siparişlere geri dön
    </a>
@endsection
@section('content')
    @include('frontend.parts.breadcrumb')
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-sm text-gray-500">Veriliş tarihi {{ $order->created_at?->format('d.m.Y H:i') }}</p>
        </div>
    </div>
    <section class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div
                    class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900">Sipariş Bilgileri</h2>
                    <a href="{{ route('returns.lookup') }}?order_number={{ $order->order_number }}&email={{ $order->customer_email }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        <i class="ri-loop-left-line text-base"></i>
                        İade Talebi Oluştur
                    </a>
                </div>
                <div class="divide-y divide-gray-100 text-sm text-gray-600">
                    <div class="px-6 py-4 flex justify-between">
                        <span>Durum</span>
                        <span
                            class="font-semibold {{ $order->status === 'completed' ? 'text-green-600' : 'text-gray-900' }}">{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</span>
                    </div>
                    @if ($order->shipping)
                        <div class="px-6 py-4 flex justify-between">
                            <span>Kargo Takip Numarası</span>
                            <span class="font-semibold text-green-800">{{ $order->shipping->tracking_no }}</span>
                        </div>
                        <div class="px-6 py-4 flex justify-between">
                            <span>Kargo Firması</span>
                            <span class="font-semibold text-gray-800">{{ $order->shipping->shippingCompany->name }}</span>
                        </div>
                    @endif
                    @php
                        $subtotal = $items->sum('total');
                        $taxAmount = 0;
                        if ($store->tax_enabled && $store->tax_rate > 0) {
                            $taxAmount = $subtotal * ($store->tax_rate / 100);
                        }
                        $baseWithTax = $subtotal + $taxAmount;

                        $displayTotal = (float) $order->total;
                        $shippingCost = max(0, $displayTotal - $baseWithTax);
                    @endphp

                    <div class="px-6 py-4 space-y-3">
                        <div class="flex justify-between">
                            <span>Ara Toplam</span>
                            <span class="font-semibold text-gray-900">{{ number_format($subtotal, 2, ',', '.') }} ₺</span>
                        </div>

                        @if ($taxAmount > 0)
                            <div class="flex justify-between text-gray-500">
                                <span>KDV (%{{ (int) $store->tax_rate }})</span>
                                <span class="font-semibold">{{ number_format($taxAmount, 2, ',', '.') }} ₺</span>
                            </div>
                        @endif

                        @if ($shippingCost > 0)
                            <div class="flex justify-between">
                                <span>Kargo Ücreti</span>
                                <span class="font-semibold text-gray-900">{{ number_format($shippingCost, 2, ',', '.') }}
                                    ₺</span>
                            </div>
                        @endif

                        <div class="flex justify-between pt-3 border-t border-gray-100 text-base">
                            <span class="font-bold text-gray-900">Genel Toplam</span>
                            <span class="font-bold text-gray-900">{{ number_format($displayTotal, 2, ',', '.') }} ₺</span>
                        </div>
                    </div>
                    <div class="px-6 py-4">
                        <span class="text-gray-500">Notlar</span>
                        <p class="mt-2 text-gray-700">{{ $order->notes ?? 'Ek not bulunmuyor.' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Ürünler</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach ($items as $item)
                        <div class="px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <a href="{{ $item->product ? route('products.show', [$item->product->id, \Illuminate\Support\Str::slug($item->product->title ?? 'urun')]) : '#' }}"
                                    class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 md:w-12 md:h-12 rounded-lg border border-gray-200 overflow-hidden bg-gray-50">
                                        @if ($item->product?->photo)
                                            <img src="{{ asset($item->product->photo) }}"
                                                alt="{{ $item->product?->title }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                <i class="ri-image-line text-xl"></i>
                                            </div>
                                        @endif
                                    </div>
                                </a>
                                <div>
                                    <a href="{{ $item->product ? route('products.show', [$item->product->id, \Illuminate\Support\Str::slug($item->product->title ?? 'urun')]) : '#' }}"
                                        class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition">
                                        {{ $item->product?->title ?? 'Ürün' }}
                                    </a>
                                    @if ($item->product?->categories?->isNotEmpty())
                                        <div class="mt-1 flex flex-wrap gap-2">
                                            @foreach ($item->product->categories as $category)
                                                <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                                                    class="text-[11px] px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition">
                                                    {{ $category->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($item->variant)
                                        <p class="text-xs text-gray-500">
                                            {{ ($item->variant->attribute->name ?? 'Varyant') . ': ' . ($item->variant->term->name ?? '') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-6 text-sm text-gray-600">
                                <span>Adet: <strong>{{ $item->quantity }}</strong></span>
                                <span>Birim: <strong>{{ number_format((float) $item->price, 2, ',', '.') }}
                                        ₺</strong></span>
                                <span>Toplam: <strong>{{ number_format((float) $item->total, 2, ',', '.') }}
                                        ₺</strong></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Teslimat Adresi</h3>
                    <p class="text-sm text-gray-600 mt-2 whitespace-pre-line">
                        {{ $order->shippingAddress?->fullname }}<br>
                        {{ $order->shippingAddress?->address ?? $order->shipping_address }}<br>
                        {{ $order->shippingAddress?->city }} {{ $order->shippingAddress?->state }}
                        {{ $order->shippingAddress?->zip }}<br>
                        Tel: {{ $order->shippingAddress?->phone ?? $order->customer_phone }}
                    </p>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Fatura Adresi</h3>
                    <p class="text-sm text-gray-600 mt-2 whitespace-pre-line">
                        {{ $order->billingAddress?->fullname }}<br>
                        {{ $order->billingAddress?->address ?? $order->billing_address }}<br>
                        {{ $order->billingAddress?->city }} {{ $order->billingAddress?->state }}
                        {{ $order->billingAddress?->zip }}<br>
                        Tel: {{ $order->billingAddress?->phone ?? $order->customer_phone }}
                    </p>
                </div>
            </div>

            @if ($order->returns?->isNotEmpty())
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">İade Talepleri</h3>
                    <div class="mt-4 space-y-3">
                        @foreach ($order->returns as $return)
                            <div class="p-4 border border-gray-100 rounded-2xl flex flex-col gap-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-gray-900">
                                        {{ $return->created_at?->format('d.m.Y H:i') }}
                                    </span>
                                    <span
                                        class="text-xs font-semibold px-3 py-1 rounded-full
                                        @if ($return->status === 'pending') bg-yellow-100 text-yellow-700
                                        @elseif($return->status === 'processed') bg-green-100 text-green-700
                                        @else bg-red-100 text-red-700 @endif">
                                        @php
                                            $returnStatus = [
                                                'pending' => 'Beklemede',
                                                'processed' => 'İade Edildi',
                                                'rejected' => 'Reddedildi',
                                            ];
                                        @endphp
                                        {{ $returnStatus[$return->status] ?? ucfirst($return->status) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600">{{ $return->reason ?? 'İade sebebi belirtilmedi.' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
