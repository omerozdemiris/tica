@extends('frontend.layouts.app')
@section('title', 'Ödeme Hatası')
@section('breadcrumb_title', 'Ödeme Hatası')
@section('content')
    @include('frontend.parts.breadcrumb')
    <section class="py-16 bg-black text-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border border-white/10 rounded-3xl p-10 text-center space-y-6 bg-gradient-to-b from-black to-zinc-900">
                <div class="w-20 h-20 mx-auto rounded-full bg-white/5 flex items-center justify-center text-3xl">
                    ☹️
                </div>
                <h1 class="text-3xl font-semibold">Ödeme tamamlanamadı</h1>
                <p class="text-sm text-white/70 leading-relaxed">
                    Banka veya ödeme sağlayıcısından beklenmeyen bir hata döndü. Kart limitiniz, 3D güvenlik doğrulaması
                    veya bağlantı problemi sebep olmuş olabilir.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('cart.index') }}"
                        class="px-6 py-3 rounded-full bg-white text-black text-sm font-semibold hover:bg-gray-100 transition">
                        Sepete geri dön
                    </a>
                    <a href="{{ route('cart.checkout') }}"
                        class="px-6 py-3 rounded-full border border-white/30 text-sm font-semibold hover:bg-white/10 transition">
                        Ödemeyi yeniden dene
                    </a>
                </div>
                <p class="text-xs text-white/50">Sorun devam ederse lütfen bizimle iletişime geçin.</p>
            </div>
        </div>
    </section>
@endsection

