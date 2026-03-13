<section class="py-16">

    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-center justify-between mb-8">

            <div>

                <h2 class="md:text-2xl text-md font-semibold text-gray-900">

                    Yeni Eklenen Ürünler</h2>

                <p class="md:text-sm text-xs text-gray-500 mt-1">

                    Mağazamıza

                    eklenen en yeni ürünleri keşfedin.</p>

            </div>

            <a href="{{ route('products.index') }}"
                class="md:text-md text-[10px] font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} hover:text-white inline-flex items-center gap-1 {{ $theme->color ? 'bg-' . $theme->color . '/5' : 'bg-gray-100' }} border {{ $theme->color ? 'border-' . $theme->color : 'border-blue-600' }} hover:{{ $theme->color ? 'bg-' . $theme->color : 'bg-black' }} transition-colors px-4 py-2 rounded-full">

                <i class="ri-arrow-right-line"></i>

                <span>Tümünü Gör</span>

            </a>

        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-5 gap-2 md:gap-4">

            @foreach ($latestProducts as $product)
                @php

                    $slug = \Illuminate\Support\Str::slug($product->title ?? 'urun');

                @endphp

                @include('frontend.parts.components.product.card', ['product' => $product, 'slug' => $slug])
            @endforeach

        </div>

    </div>

</section>
