@php
    $slug = $slug ?? \Illuminate\Support\Str::slug($product->title ?? 'urun');
    $isHero = $isHero ?? false;
    $galleryPreview = collect($product->gallery ?? [])->values();
    $primaryImage = $product->photo ? asset($product->photo) : asset('assets/img/resim-yok.svg');
    $hoverImage = $galleryPreview->first()
        ? asset('upload/productgallery/' . $galleryPreview->first()->name)
        : $primaryImage;
@endphp
<article class="group bg-white border border-gray-100/80 hover:border-gray-400 h-full transition-all duration-300">
    <a href="{{ route('products.show', [$product->id, $slug]) }}" class="block">
        <div
            class="relative overflow-hidden {{ $isHero ? 'aspect-[4/5] md:aspect-[3/4]' : 'aspect-[3/4]' }} bg-gray-100">
            <img src="{{ $primaryImage }}" alt="{{ $product->title }}"
                class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0">
            <img src="{{ $hoverImage }}" alt="{{ $product->title }}"
                class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-300 group-hover:opacity-100">
        </div>
    </a>
    <div class="pt-2 px-2 pb-3">
        <a href="{{ route('products.show', [$product->id, $slug]) }}"
            class="group flex items-start justify-between gap-3 transition-all duration-300">
            <h4
                class="max-w-[max-content] relative py-0.5 inline-block flex-1 min-w-0 md:text-[10px] text-[10px] md:text-xs text-gray-900 transition after:content-[''] after:absolute after:left-0 after:bottom-0 after:h-px after:w-full after:bg-current after:origin-right after:scale-x-0 after:transition-transform after:duration-200 group-hover:after:origin-left group-hover:after:scale-x-100 group-hover:after:duration-300">
                {{ Str::limit($product->title, 28) }}
            </h4>
            @if ($store->show_price === 1)
                <p class="shrink-0 text-xs md:text-xs text-gray-900 text-right leading-none">
                    @if (!is_null($product->discount_price) && $product->discount_price > 0)
                        <span class="text-[10px] md:text-[10px] text-gray-400 line-through mr-1.5">
                            {{ number_format((float) $product->price, 0, ',', '.') }} ₺
                        </span>
                        {{ number_format((float) $product->discount_price, 0, ',', '.') }} ₺
                    @elseif (!is_null($product->price))
                        {{ number_format((float) $product->price, 0, ',', '.') }} ₺
                    @else
                        <span class="text-[11px] text-gray-500">-</span>
                    @endif
                </p>

            @endif
        </a>
    </div>
</article>
