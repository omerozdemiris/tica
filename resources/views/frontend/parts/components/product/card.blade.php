<div
    class="flex flex-col h-full rounded-xl border {{ $theme->color ? 'border-' . $theme->color . '/25' : 'border-gray-200' }} shadow-sm overflow-hidden bg-white">
    <a href="{{ route('products.show', [$product->id, $slug]) }}" class="block">
        <div
            class="aspect-[5/3] {{ $theme->color ? 'bg-' . $theme->color . '/5' : 'bg-gray-50' }} flex items-center justify-center">

            @if ($product->photo)
                <img src="{{ asset('assets/img/resim-yok.jpg') }}" data-src="{{ asset($product->photo) }}"
                    alt="{{ $product->title }}" class="w-full h-full object-cover lazy"
                    onerror="this.src='{{ asset('assets/img/resim-yok.jpg') }}'">
            @else
                <div class="{{ $theme->color ? 'text-' . $theme->color : 'text-gray-300' }} text-3xl">
                    <img src="{{ asset('assets/img/resim-yok.jpg') }}" class="w-full h-full object-cover">
                </div>
            @endif
        </div>
    </a>
    <div class="flex flex-col p-4">
        <div class="flex flex-col">
            <a href="{{ route('products.show', [$product->id, $slug]) }}" class="block">
                <h4 class="text-xs md:text-sm font-semibold text-gray-900 line-clamp-2 w-full">
                    {{ $isMobile ? Str::limit($product->title, 22) : Str::limit($product->title, 55) }}
                </h4>
            </a>
            <div class="grid grid-cols-2 gap-[1px] md:gap-[3px]">
                @foreach ($product->categories->take(2) as $category)
                    <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                        class="my-2 py-0.5 px-0.5 md:py-1 md:px-2 rounded-full text-[7px] md:text-[10px] font-medium col-span-1 inline-flex items-center {{ $theme->color ? 'bg-' . $theme->color . '/5 text-' . $theme->color : 'bg-gray-100 text-gray-600' }} line-clamp-2">
                        {{ Str::limit(strip_tags($category->name), 15) }}
                    </a>
                @endforeach
            </div>
        </div>
        <div class="mt-0">
            <p class="text-sm md:text-md font-bold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                @if (!is_null($product->discount_price) && $product->discount_price > 0)
                    <span class="text-xs md:text-xs line-through text-gray-400 mr-1">
                        {{ number_format((float) $product->price, 2, ',', '.') }} ₺
                    </span>
                    {{ number_format((float) $product->discount_price, 2, ',', '.') }} ₺
                @elseif (!is_null($product->price))
                    {{ number_format((float) $product->price, 2, ',', '.') }} ₺
                @else
                    <span class="text-sm {{ $theme->color ? 'text-' . $theme->color : 'text-gray-500' }}">Fiyat
                        bilgisi için iletişime geçin</span>
                @endif
            </p>
        </div>
    </div>
</div>
