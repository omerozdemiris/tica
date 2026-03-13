@extends($template . '.layouts.app')
@section('title', 'Havale / EFT Hatası')
@section('breadcrumb_title', 'Havale Hatası')
@section('content')
    @include($template . '.parts.breadcrumb')
    <section class="py-16 bg-slate-950 text-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="border border-white/10 rounded-3xl p-10 text-center space-y-6 bg-gradient-to-b from-slate-900 to-black">
                <div class="w-20 h-20 mx-auto rounded-full bg-white/5 flex items-center justify-center text-3xl">
                    ⚠️
                </div>
                <h1 class="text-3xl font-semibold">İşlem tamamlanamadı</h1>
                <p class="text-sm text-white/70 leading-relaxed">
                    Seçtiğiniz ürünlerden biri stokta kalmadı veya havale talimatı oluşturulurken beklenmeyen bir hata
                    oluştu.
                    Sepetinizi kontrol ederek tekrar deneyebilir ya da farklı bir ödeme yöntemi seçebilirsiniz.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('cart.index') }}"
                        class="px-6 py-3 rounded-full bg-white text-black text-sm font-semibold hover:bg-gray-100 transition">
                        Sepete geri dön
                    </a>
                    <a href="{{ route('home') }}"
                        class="px-6 py-3 rounded-full border border-white/30 text-sm font-semibold hover:bg-white/10 transition">
                        Alışverişe devam et
                    </a>
                </div>
                <p class="text-xs text-white/50">Yardım için bizimle iletişime geçebilirsiniz.</p>
            </div>
        </div>
    </section>
@endsection
