@extends($template . '.layouts.app')
@php
    $checkout = $data->checkout;
    $cartSnapshot = $checkout->cart_snapshot ?? [];
    $items = collect($cartSnapshot['items'] ?? []);
@endphp
@section('title', 'Ödeme')
@section('breadcrumb_title', 'Ödeme')
@section('content')
    @include($template . '.parts.breadcrumb')
    <section class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white border border-gray-200 rounded-3xl shadow-sm">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Adım 2/2</p>
                        <h1 class="text-2xl font-semibold text-gray-900 mt-1">Ödeme Ekranı</h1>
                        <p class="text-sm text-gray-500 mt-1">Kart bilgileriniz PayTR tarafından güvenle işlenecektir.</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400">Sipariş Tutarı</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ number_format((float) ($checkout->amount ?? 0), 2, ',', '.') }} ₺
                        </p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="aspect-[4/3] bg-gray-50 border border-gray-100 rounded-2xl overflow-hidden shadow-inner">
                        <iframe id="paytriframe"
                            src="https://www.paytr.com/odeme/guvenli/{{ $checkout->payment_service_token }}" frameborder="0"
                            scrolling="no" style="width: 1px; min-width: 100%; min-height: 100%;"
                            allow="payment *"></iframe>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Sepet Özeti</h2>
                    <a href="{{ route('cart.index') }}" class="text-sm text-blue-600 hover:text-blue-700">Sepete geri
                        dön</a>
                </div>
                <div class="space-y-3 text-sm text-gray-600">
                    @foreach ($items as $item)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $item['name'] }}</p>
                                <p class="text-xs text-gray-500">Adet: {{ $item['quantity'] }}</p>
                            </div>
                            <span class="font-semibold text-gray-900">
                                {{ number_format((float) $item['subtotal'], 2, ',', '.') }} ₺
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://www.paytr.com/js/iframeResizer.min.js?v2"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof iFrameResize === 'function') {
                iFrameResize({
                    checkOrigin: false
                }, '#paytriframe');
            }
        });
    </script>
@endpush
