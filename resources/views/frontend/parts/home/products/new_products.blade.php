<section class="py-16">
    <div class="max-w-[1680px] mx-auto px-4 md:px-0">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="md:text-2xl text-md font-semibold text-gray-900">
                    Yeni Eklenen Ürünler</h2>
                <p class="md:text-sm md:text-xs text-[10px] text-gray-500 mt-1">
                    Mağazamıza
                    eklenen en yeni ürünleri keşfedin.</p>
            </div>
            <a href="{{ route('products.index') }}"
                class="md:text-md text-xs font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} hover:text-white inline-flex items-center gap-1 border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-blue-600' }} hover:{{ $theme->color ? 'bg-' . $theme->color : 'bg-black' }} transition-all duration-300 md:px-4 px-2 py-2">
                <i class="ri-arrow-right-line"></i>
                <span>Tümünü Gör</span>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-4 gap-2 md:gap-8">
            @foreach ($latestProducts as $product)
                @php
                    $slug = \Illuminate\Support\Str::slug($product->title ?? 'urun');
                @endphp
                @include('frontend.parts.components.product.card', ['product' => $product, 'slug' => $slug])
            @endforeach
        </div>
    </div>
</section>