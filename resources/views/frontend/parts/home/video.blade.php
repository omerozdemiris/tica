<section class="w-full py-12 px-4 md:px-6 lg:px-8 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-8 md:gap-12">
        <div class="w-full md:w-1/2">
            <h2 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight mb-6">
                Doğal<br>Zeytinyağı
            </h2>
            <p class="text-gray-500 text-lg md:text-xl mb-8 max-w-md leading-relaxed">
                Lezzet dolu uluslararası ödüllü yeni hasat zeytinyağı
            </p>
            <a href="/categories/40/zeytin-yaglari"
                class="inline-block {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white px-8 py-3 rounded-full text-sm font-semibold hover:opacity-90 transition-colors">
                Ürünleri Keşfet
            </a>
        </div>
        <div class="w-full md:w-1/2">
            <div class="relative w-full aspect-video overflow-hidden rounded-xl">
                <video autoplay loop muted playsinline class="w-full h-full object-cover">
                    <source src="{{ asset('assets/video.mp4') }}" type="video/mp4">
                </video>
            </div>
        </div>
    </div>
</section>
