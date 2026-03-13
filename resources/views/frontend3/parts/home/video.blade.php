<section class="w-full py-10 px-6 md:px-12 lg:px-24 bg-white overflow-hidden">
    <div class="max-w-screen-2xl mx-auto flex flex-col md:flex-row items-center justify-between gap-12 md:gap-20">
        <div class="w-full md:w-1/2 lg:w-4/12">
            <h2 class="text-6xl md:text-5xl font-bold text-gray-900 leading-[1.1] mb-6 tracking-tight">
                Doğal<br>Zeytinyağı
            </h2>
            <p class="text-gray-500 text-xl md:text-xl mb-12 max-w-sm leading-snug">
                Lezzet dolu uluslararası ödüllü yeni hasat zeytinyağı
            </p>
            <a href="/categories/40/zeytin-yaglari"
                class="inline-block bg-{{ $theme->color ? $theme->color : 'bg-blue-600' }} text-white px-12 py-4 rounded-full text-sm font-bold hover:bg-zinc-800 transition-colors duration-300">
                Ürünleri Keşfet
            </a>
        </div>
        <div class="w-full md:w-1/2 lg:w-8/12">
            <div class="relative w-full aspect-[5/3] overflow-hidden rounded-3xl">
                <video autoplay loop muted playsinline class="w-full h-full object-cover">
                    <source src="{{ asset('assets/video.mp4') }}" type="video/mp4">
                </video>
            </div>
        </div>
    </div>
</section>
