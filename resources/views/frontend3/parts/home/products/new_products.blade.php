<section class="py-10 bg-white border-b border-gray-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <h2 class="text-xl md:text-2xl font-semibold leading-none">
                    YENİ EKLENEN ÜRÜNLERİMİZ
                </h2>
            </div>

            <a href="{{ route('products.index') }}"
                class="inline-flex items-center rounded-full gap-4 px-10 py-4 {{ $theme->color ? 'bg-' . $theme->color : 'bg-black' }} text-white text-[11px] font-black uppercase tracking-widest hover:opacity-90 transition shadow-xl">
                <span>TÜMÜNÜ KEŞFET</span>
                <i class="ri-arrow-right-line text-lg"></i>
            </a>
        </div>

        <div class="relative">
            <div class="swiper p-4 newProductsSwiper">
                <div class="swiper-wrapper">
                    @foreach ($latestProducts as $product)
                        @php $slug = \Illuminate\Support\Str::slug($product->title ?? 'urun'); @endphp
                        <div class="swiper-slide">
                            @include($template . '.parts.components.product.card', [
                                'product' => $product,
                                'slug' => $slug,
                            ])
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Navigation Buttons --}}
            <button
                class="new-products-prev absolute -left-4 md:-left-12 top-1/2 -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 bg-white rounded-full flex items-center justify-center text-gray-400 hover:text-black shadow-xl border border-gray-100 transition-all z-20">
                <i class="ri-arrow-left-s-line text-2xl"></i>
            </button>
            <button
                class="new-products-next absolute -right-4 md:-right-12 top-1/2 -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 bg-white rounded-full flex items-center justify-center text-gray-400 hover:text-black shadow-xl border border-gray-100 transition-all z-20">
                <i class="ri-arrow-right-s-line text-2xl"></i>
            </button>
        </div>
    </div>
</section>

@push('scripts')
    <script>
        $(document).ready(function() {
            if ($('.newProductsSwiper').length > 0) {
                new Swiper('.newProductsSwiper', {
                    slidesPerView: 2,
                    spaceBetween: 16,
                    navigation: {
                        nextEl: '.new-products-next',
                        prevEl: '.new-products-prev',
                    },
                    breakpoints: {
                        640: {
                            slidesPerView: 2,
                        },
                        1024: {
                            slidesPerView: 4,
                            spaceBetween: 24,
                        }
                    }
                });
            }
        });
    </script>
@endpush
