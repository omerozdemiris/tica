@php
    $slug = $slug ?? Str::slug($product->title);
    $price = (float) $product->price;
    $discountPrice = (float) $product->discount_price;
    $hasDiscount =
        !is_null($product->discount_price) &&
        $product->discount_price > 0 &&
        $product->discount_price < $product->price;
    $discountPercentage = $hasDiscount ? round((($price - $discountPrice) / $price) * 100) : 0;
    $isNew = $product->isNew();

    $gallery = $product->gallery;
    $images = collect();
    if ($product->photo) {
        $images->push(asset($product->photo));
    }
    foreach ($gallery as $item) {
        $images->push(asset('upload/productgallery/' . $item->name));
    }
    if ($images->isEmpty()) {
        $images->push(asset('assets/img/resim-yok.svg'));
    }

    $cardId = 'product-card-' . $product->id . '-' . Str::random(4);
@endphp

<div id="{{ $cardId }}"
    class="group relative bg-white border border-gray-100 flex flex-col h-full overflow-hidden transition-all duration-300 hover:shadow-md product-card rounded-[2rem]">

    <div class="relative aspect-[4/4] overflow-hidden bg-white rounded-t-[2rem] cursor-pointer"
        onclick="window.location.href = '{{ route('products.show', [$product->id, $slug]) }}'">
        <div class="absolute top-3 left-3 z-30 flex flex-col gap-1.5">
            @if ($hasDiscount)
                <div
                    class="bg-{{ $theme->color ? $theme->color : 'bg-blue-600' }} text-white text-[10px] font-bold px-4 py-3 rounded-full leading-none">
                    - %{{ $discountPercentage }}
                </div>
            @endif
            @if ($isNew)
                <div
                    class="bg-{{ $theme->color ? $theme->color : 'bg-blue-600' }} text-white text-[10px] font-bold px-4 py-3 rounded-full uppercase leading-none">
                    YENİ
                </div>
            @endif
        </div>
        <div
            class="image-slider w-full h-full flex overflow-x-auto snap-x snap-mandatory no-scrollbar pointer-events-auto md:pointer-events-none">
            @foreach ($images as $index => $image)
                <div class="flex-none w-full h-full snap-center bg-[#f7f7f7] flex items-center justify-center p-4">
                    <img src="{{ asset('assets/img/resim-yok.svg') }}" data-src="{{ $image }}"
                        alt="{{ $product->title }}"
                        class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105 lazy"
                        data-index="{{ $index }}" onerror="this.src='{{ asset('assets/img/resim-yok.svg') }}'">
                </div>
            @endforeach
        </div>
        <div class="hidden md:flex absolute inset-0 z-20">
            @foreach ($images as $index => $image)
                <div class="flex-1 h-full cursor-pointer hover-trigger"
                    onmouseover="updateProductCardImage('{{ $cardId }}', {{ $index }})">
                </div>
            @endforeach
        </div>
        <div
            class="absolute inset-0 z-30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-2 bg-white/5 pointer-events-none">
            @if ($images->count() > 1)
                <div class="absolute inset-y-0 left-2 flex items-center">
                    <i class="ri-arrow-left-s-line text-2xl text-gray-400"></i>
                </div>
                <div class="absolute inset-y-0 right-2 flex items-center">
                    <i class="ri-arrow-right-s-line text-2xl text-gray-400"></i>
                </div>
            @endif

            <div class="flex gap-2 pointer-events-auto absolute bottom-5">
                <a href="{{ route('products.show', [$product->id, $slug]) }}"
                    class="px-4 gap-4 h-10 bg-{{ $theme->color ? $theme->color : 'bg-blue-600' }} rounded-full flex items-center justify-center text-white hover:bg-black transition-colors shadow-lg"
                    title="İncele">
                    <i class="ri-shopping-bag-line"></i>
                    İncele
                </a>
            </div>
        </div>
        @if ($images->count() > 1)
            <div class="absolute bottom-3 left-0 right-0 z-30 flex justify-center gap-1 px-6 pointer-events-none">
                @foreach ($images as $index => $image)
                    <div class="h-0.5 flex-1 max-w-[24px] bg-gray-200 transition-colors duration-300 pagination-dash"
                        data-index="{{ $index }}"
                        style="{{ $index === 0 ? 'background-color: ' . ($theme->color ? $theme->color : 'bg-blue-600') . ';' : '' }}">
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    <div class="p-4 flex flex-col flex-1 bg-white">
        <a href="{{ route('products.show', [$product->id, $slug]) }}" class="block mb-2">
            <h4
                class="text-sm font-semibold text-gray-900 line-clamp-1 group-hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }} transition-colors">
                {{ $isMobile ? Str::limit($product->title, 22) : Str::limit($product->title, 55) }}
            </h4>
        </a>
        <div class="flex items-center gap-2 mb-3">
            @if ($hasDiscount)
                <span class="text-xs text-gray-400 line-through">
                    {{ number_format($price, 2, ',', '.') }} ₺
                </span>
                <span class="text-base font-bold text-gray-900">
                    {{ number_format($discountPrice, 2, ',', '.') }} ₺
                </span>
            @else
                <span class="text-base font-bold text-gray-900">
                    {{ number_format($price, 2, ',', '.') }} ₺
                </span>
            @endif
        </div>
        <div class="mt-auto pt-3 border-t border-dashed border-gray-200">
            <p class="text-[11px] text-gray-500 line-clamp-2 leading-relaxed min-h-[32px]">
                {{ $product->description ? strip_tags($product->description) : 'Ürün açıklaması henüz eklenmemiş.' }}
            </p>
        </div>
    </div>
</div>

@once
    <script>
        function updateProductCardImage(cardId, index) {
            const card = document.getElementById(cardId);
            if (!card) return;

            const slider = card.querySelector('.image-slider');
            const width = slider.offsetWidth;

            slider.scrollTo({
                left: width * index,
                behavior: 'smooth'
            });

            const dashes = card.querySelectorAll('.pagination-dash');
            dashes.forEach((dash, i) => {
                dash.style.backgroundColor = (i === index) ?
                    '{{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }}' : '#e5e7eb';
            });
        }
        document.addEventListener('scroll', (e) => {
            if (e.target.classList && e.target.classList.contains('image-slider')) {
                const slider = e.target;
                const index = Math.round(slider.scrollLeft / slider.offsetWidth);
                const card = slider.closest('.product-card');
                const dashes = card.querySelectorAll('.pagination-dash');
                dashes.forEach((dash, i) => {
                    dash.style.backgroundColor = (i === index) ?
                        '{{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }}' : '#e5e7eb';
                });
            }
        }, true);
    </script>
@endonce
