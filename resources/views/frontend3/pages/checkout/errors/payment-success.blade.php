@extends($template . '.layouts.app')
@php
    $order = $data->order;
    $items = $order?->items ?? collect();
@endphp
@section('title', 'Sipariş Onayı')
@section('breadcrumb_title', 'Sipariş Onayı')
@section('content')
    @include($template . '.parts.breadcrumb')
    <section class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-8 text-center space-y-4">
                <div
                    class="w-16 h-16 rounded-full bg-green-100 text-green-600 flex items-center justify-center mx-auto text-2xl">
                    <i class="ri-check-line"></i>
                </div>
                <h1 class="text-2xl font-semibold text-gray-900">Siparişiniz alındı!</h1>
                <p class="text-gray-500">Sipariş numaranız
                    <span class="font-semibold text-gray-900">{{ $order->order_number }}</span>. Detaylar aşağıda
                    listelendi.
                </p>
                <a href="{{ route('home') }}"
                    class="inline-flex items-center gap-2 px-5 py-3 rounded-full bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition">
                    Alışverişe devam et
                </a>
            </div>

            <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-[0.3em]">Sipariş Özeti</p>
                        <h2 class="text-xl font-semibold text-gray-900 mt-1">Toplam:
                            {{ number_format((float) $order->total, 2, ',', '.') }} ₺</h2>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Durum</p>
                        <p class="text-sm font-semibold text-green-600">Tamamlandı</p>
                    </div>
                </div>

                <ul class="divide-y divide-gray-100">
                    @foreach ($items as $item)
                        <li class="py-4 flex items-center justify-between text-sm text-gray-600">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $item->product?->title ?? 'Ürün' }}</p>
                                <p class="text-xs text-gray-500">Adet: {{ $item->quantity }}</p>
                            </div>
                            <span class="font-semibold text-gray-900">
                                {{ number_format((float) $item->total, 2, ',', '.') }} ₺
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
@endsection
