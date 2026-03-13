@extends($template . '.layouts.app')
@php
    $order = $data->order;
    $items = $order?->items ?? collect();
    $wireBanks = collect($banks ?? []);
@endphp
@section('title', 'Havale / EFT Talimatı')
@section('breadcrumb_title', 'Havale / EFT')
@section('content')
    @include($template . '.parts.breadcrumb')
    <section class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-8 space-y-4 text-center">
                <div
                    class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto text-2xl">
                    <i class="ri-exchange-dollar-line"></i>
                </div>
                <h1 class="text-2xl font-semibold text-gray-900">Siparişiniz Başarıyla Oluşturuldu!</h1>
                <p class="text-gray-500">
                    Sipariş numaranız
                    <span class="font-semibold text-gray-900">{{ $order->order_number }}</span>.
                    Lütfen aşağıdaki banka bilgilerini kullanarak ödemenizi tamamlayın.
                </p>
            </div>

            <div class="bg-emerald-50 border border-emerald-100 rounded-3xl p-6 space-y-4">
                <h2 class="text-sm font-semibold text-emerald-900 uppercase tracking-wide">Banka Bilgileri</h2>
                @if ($wireBanks->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($wireBanks as $bank)
                            <div class="bg-white rounded-2xl border border-emerald-100 p-4 shadow-sm space-y-2">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-emerald-600">Banka</p>
                                        <p class="text-base font-semibold text-gray-900">{{ $bank->bank_name }}</p>
                                    </div>
                                    <span
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                        <i class="ri-check-line"></i>
                                        Aktif
                                    </span>
                                </div>
                                <div class="text-sm text-gray-700">
                                    <p class="font-semibold">Alıcı: <span
                                            class="font-normal">{{ $bank->bank_receiver }}</span></p>
                                    <p class="mt-1 text-xs font-mono tracking-wider text-gray-600">{{ $bank->bank_iban }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div
                        class="rounded-2xl border border-yellow-100 bg-yellow-50 text-yellow-900 text-sm font-medium px-4 py-3">
                        Banka bilgileri kısa süre içinde paylaşılacaktır. Lütfen müşteri temsilcimizle iletişime geçin.
                    </div>
                @endif
                <div class="text-xs text-emerald-800">
                    Havale/EFT dekontunu oluştururken açıklama alanına mutlaka <strong>{{ $order->order_number }}</strong>
                    yazmayı unutmayın. Ödemeniz ulaştığında siparişiniz onaylanacaktır.
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-3xl p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-[0.3em]">Sipariş Özeti</p>
                        <h2 class="text-xl font-semibold text-gray-900 mt-1">
                            Tutar: {{ number_format((float) $order->total, 2, ',', '.') }} ₺
                        </h2>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Durum</p>
                        <span
                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                            <i class="ri-time-line text-base"></i> Ödeme Bekleniyor
                        </span>
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
                <div class="text-sm text-gray-500">
                    Havale/EFT işleminizi tamamladıktan sonra dekontunuzu bizimle paylaşabilirsiniz.
                </div>
            </div>
        </div>
    </section>
@endsection
