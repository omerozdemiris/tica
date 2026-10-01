@php
    $product = $data->product;
    $variants = $product->variants ?? collect();
    $canPurchase =
        (optional($store)->sell_enabled ?? true) && (!(optional($store)->auth_required ?? false) || auth()->check());
    $primaryCategory = $product->categories->first();
    $breadcrumbs = $data->breadcrumbs ?? [];
@endphp
@extends('frontend.layouts.app')
@section('title', 'Ürün - ' . $product->title)
@section('breadcrumb_title', $product->title)
@section('og_title', $product->meta_title ?? '')
@section('og_description', $product->meta_description ?? Str::limit(strip_tags($product->description), 250))
@section('content')
    @include('frontend.parts.breadcrumb')
    <section class="py-3 md:py-3">
        <div class="max-w-[1680px] mx-auto px-4 md:px-0 grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-10">
            <div class="lg:hidden col-span-1">
                <h1 class="text-lg font-semibold text-gray-900">{{ $product->title }}</h1>
            </div>
            <div class="lg:col-span-7 space-y-2">
                @php
                    $allImages = collect();
                    if ($product->photo) {
                        $allImages->push(
                            (object) [
                                'id' => 'main',
                                'url' => asset($product->photo),
                                'type' => 'main',
                            ],
                        );
                    }
                    if ($product->gallery && $product->gallery->isNotEmpty()) {
                        foreach ($product->gallery as $galleryItem) {
                            $allImages->push(
                                (object) [
                                    'id' => $galleryItem->id,

                                    'url' => asset('upload/productgallery/' . $galleryItem->name),

                                    'type' => 'gallery',
                                ],
                            );
                        }
                    }
                @endphp
                @if ($allImages->isNotEmpty())
                    <div class="flex flex-col gap-[1px] md:gap-2 bg-gray-100">
                        @foreach ($allImages as $image)
                            <a href="{{ $image->url }}" data-fancybox="product-gallery" data-caption="{{ $product->title }}"
                                data-zoom-src="{{ $image->url }}"
                                class="product-zoom-item relative block bg-white cursor-zoom-in">
                                <span class="relative block overflow-hidden">
                                    <img src="{{ $image->url }}" alt="{{ $product->title }}"
                                        class="w-full h-full object-cover aspect-[4/5]">
                                    <span
                                        class="product-zoom-lens pointer-events-none hidden lg:block absolute z-20 border border-white/80 shadow-md bg-white/20 backdrop-blur-[1px]"></span>
                                </span>
                                <span
                                    class="product-zoom-preview pointer-events-none hidden absolute z-30 left-full ml-6 top-0 w-[380px] aspect-[4/5] border border-gray-200 shadow-xl bg-white bg-no-repeat"></span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div
                        class="aspect-[4/5] bg-white border border-gray-200 overflow-hidden flex items-center justify-center">
                        <div class="text-gray-300 text-5xl">
                            <i class="ri-image-line"></i>
                        </div>
                    </div>
                @endif
            </div>
            <div class="lg:col-span-4 lg:sticky lg:top-24 h-max self-start pt-8">
                <div>
                    <div class="hidden lg:flex items-start justify-between gap-4">
                        <h1 class="text-lg md:text-lg font-semibold text-gray-900 max-w-sm">{{ $product->title }}</h1>
                        @if ($store->show_price === 1)
                            <div
                                class="text-right text-md font-semibold product-price-display {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                @if (!is_null($product->price))
                                    {{ number_format((float) $product->price, 2, ',', '.') }} ₺
                                @else
                                    <span
                                        class="text-sm {{ $theme->color ? 'text-' . $theme->color . '/60' : 'text-gray-500' }}">Fiyat
                                        bilgisi için iletişime geçin</span>
                                @endif
                            </div>

                        @endif
                    </div>
                    <p class="text-[10px] uppercase  text-gray-500 mt-1">STK: {{ $product->id }}</p>
                    <div class="flex items-center gap-2 mt-3">
                        @foreach ($product->categories as $category)
                            <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                                class="inline-flex items-center text-[10px] {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }}">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="py-3 bg-white" data-base-price="{{ (float) $product->price }}">
                    <p class="text-[11px] uppercase text-gray-500 lg:hidden">Fiyat</p>
                    <div
                        class="mt-1 text-2xl font-semibold product-price-display lg:hidden {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                        @if (!is_null($product->price))
                            {{ number_format((float) $product->price, 2, ',', '.') }} ₺
                        @else
                            <span
                                class="text-sm {{ $theme->color ? 'text-' . $theme->color . '/60' : 'text-gray-500' }}">Fiyat
                                bilgisi için iletişime geçin</span>
                        @endif
                    </div>

                    <form action="{{ route('cart.store') }}" method="POST" class="mt-5 space-y-4" data-cart-add-form
                        id="add-to-cart-form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        @if (!(optional($store)->sell_enabled ?? true))
                            <div class="py-3 {{ $theme->color ? 'text-' . $theme->color : 'text-amber-700' }} text-xs">
                                Satın alma işlemi şu anda kapalıdır.
                            </div>
                        @elseif (!is_null($product->price) || $variants->isNotEmpty())
                            @if ($variants->isNotEmpty())
                                <div class="mt-5 space-y-4">
                                    @foreach ($variants->groupBy('attribute_id') as $attributeId => $attrVariants)
                                        @php
                                            $attribute = $attrVariants->first()->attribute;
                                            $isColorAttribute = $attrVariants->every(function ($variant) {
                                                $termValue = $variant->term->value ?? null;
                                                return is_string($termValue) && str_starts_with($termValue, '#');
                                            });
                                        @endphp
                                        <div>
                                            <label class="text-[11px] font-semibold uppercase r text-gray-700">
                                                {{ $attribute->name ?? 'Özellik' }} Seçin
                                            </label>
                                            @if ($isColorAttribute)
                                                <div class="mt-2 flex flex-wrap items-center gap-2 md:gap-2.5"
                                                    data-color-variant-group>
                                                    @foreach ($attrVariants as $variant)
                                                        @php
                                                            $term = $variant->term;
                                                            $displayName = $term->name ?? '';
                                                            $colorMatch = $term->value ?? '#d1d5db';
                                                        @endphp
                                                        <button type="button"
                                                            class="group relative w-8 h-8 md:w-9 md:h-9 border border-gray-300 shadow-sm transition-all duration-300 color-variant-option"
                                                            data-value="{{ $variant->id }}"
                                                            data-name="{{ $displayName }}"
                                                            data-color="{{ $colorMatch }}"
                                                            data-price="{{ $variant->price ?? '' }}">
                                                            <span class="block w-full h-full"
                                                                style="background-color: {{ $colorMatch }}"></span>
                                                            <span
                                                                class="absolute inset-0 hidden items-center justify-center text-white bg-black/25 color-variant-check">
                                                                <i class="ri-check-line text-xs"></i>
                                                            </span>
                                                            <span
                                                                class="information-box pointer-events-none absolute left-1/2 -translate-x-1/2 -top-8 hidden group-hover:block whitespace-nowrap px-1.5 py-1 bg-gray-900 text-white text-xs leading-none">
                                                                {{ $displayName }}
                                                            </span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                                <input type="hidden" name="variant_ids[]" class="variant-hidden-input"
                                                    form="add-to-cart-form" required>
                                            @else
                                                <div class="relative mt-2 custom-select-container">
                                                    <button type="button"
                                                        class="w-full flex items-center justify-between px-4 py-3 bg-white border border-gray-200 hover:bg-gray-50 focus:outline-none transition-all custom-select-button">
                                                        <div class="flex items-center gap-3">
                                                            <span
                                                                class="w-5 h-5 rounded-full border border-gray-200 shadow-inner hidden selected-color-preview"></span>
                                                            <span
                                                                class="text-sm font-semibold text-gray-900 selected-text">Seçiniz</span>
                                                        </div>
                                                        <i class="ri-arrow-down-s-line text-gray-400"></i>
                                                    </button>

                                                    <input type="hidden" name="variant_ids[]" class="variant-hidden-input"
                                                        form="add-to-cart-form" required>

                                                    <div
                                                        class="absolute z-50 w-full mt-2 bg-white border border-gray-200 shadow-xl hidden custom-select-menu max-h-60 overflow-y-auto">
                                                        @foreach ($attrVariants as $variant)
                                                            @php
                                                                $term = $variant->term;
                                                                $displayName = $term->name ?? '';
                                                                $colorMatch =
                                                                    isset($term->value) &&
                                                                    str_starts_with($term->value, '#')
                                                                        ? $term->value
                                                                        : null;
                                                            @endphp
                                                            <div class="flex items-center justify-between px-3 py-2 hover:bg-gray-50 cursor-pointer transition-colors custom-select-option"
                                                                data-value="{{ $variant->id }}"
                                                                data-name="{{ $displayName }}"
                                                                data-color="{{ $colorMatch }}"
                                                                data-price="{{ $variant->price ?? '' }}">
                                                                <div class="flex items-center gap-2">
                                                                    @if ($colorMatch)
                                                                        <span
                                                                            class="w-5 h-5 rounded-full border border-gray-200 shadow-inner"
                                                                            style="background-color: {{ $colorMatch }}"></span>
                                                                    @endif
                                                                    <span
                                                                        class="text-sm font-semibold text-gray-900">{{ $displayName }}</span>
                                                                </div>
                                                                @if (!is_null($variant->price) && $variant->price > 0)
                                                                    <span
                                                                        class="text-xs font-bold text-gray-400">{{ number_format((float) $variant->price, 2, ',', '.') }}
                                                                        ₺</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <div>
                                <label class="text-[11px] font-semibold uppercase  text-gray-700">Adet</label>
                                <div class="mt-2 flex items-center border border-gray-200 overflow-hidden w-36">
                                    <button type="button"
                                        class="px-2 py-1 text-md font-medium {{ $theme->color ? 'text-' . $theme->color . '/60' : 'text-gray-600' }} decrement">
                                        -
                                    </button>
                                    <input type="number" name="quantity" value="1" min="1"
                                        class="w-full text-center text-sm font-semibold py-2 outline-none">
                                    <button type="button" class="px-2 py-1 text-md font-medium text-gray-600 increment">
                                        +
                                    </button>
                                </div>
                            </div>
                            <button type="submit"
                                class="hover:bg-white hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-700' }} border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-blue-600' }} w-full px-4 py-3 flex items-center justify-center gap-4 {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-xs uppercase  font-semibold hover:{{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-700' }} transition-all duration-300">
                                <span><i class="ri-shopping-bag-2-line font-light text-xl"></i></span>
                                Sepete Ekle
                            </button>
                        @else
                            <div
                                class="px-4 py-3 {{ $theme->color ? 'bg-' . $theme->color . '/5' : 'bg-gray-100' }} {{ $theme->color ? 'text-' . $theme->color . '/60' : 'text-gray-600' }} text-xs">
                                Bu ürün için fiyat bilgisi henüz eklenmedi. Lütfen bizimle iletişime geçin.
                            </div>
                        @endif
                    </form>
                </div>
                <div class="prose prose-sm max-w-none text-sm text-gray-700 text-xs">
                    {!! $product->description !!}
                </div>
            </div>
        </div>
    </section>
    @if (optional($store)->comments_enabled ?? false)
        @if (isset($data->canComment) && $data->canComment)
            <section class="pb-12 pt-8 bg-white">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="max-w-4xl mx-auto">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">
                            Ürünü Değerlendir <i class="ri-chat-quote-fill"></i>
                        </h2>
                        <form action="{{ route('user.comments.store', $product->id) }}" method="POST"
                            class="space-y-6 bg-gray-50 p-6 shadow-sm">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Puanınız
                                </label>
                                <div class="flex flex-row-reverse justify-end gap-1">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <input type="radio" name="rating" id="star{{ $i }}"
                                            value="{{ $i }}" class="peer hidden" required />
                                        <label for="star{{ $i }}"
                                            class="cursor-pointer text-gray-300 text-3xl peer-checked:text-yellow-400 peer-hover:text-yellow-400 hover:text-yellow-400 transition">
                                            ★
                                        </label>
                                    @endfor
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    1 (kötü) - 5 (mükemmel)
                                </p>
                            </div>
                            <div>
                                <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                                    Yorumunuz
                                </label>
                                <textarea name="comment" id="comment" rows="4" placeholder="Ürün hakkındaki deneyiminizi paylaşın..."
                                    class="w-full border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-300' }} px-4 py-3 text-sm text-gray-900 focus:border-{{ $theme->color ? $theme->color : 'blue-500' }} focus:ring-2 focus:ring-{{ $theme->color ? $theme->color . '/20' : 'blue-200' }} transition resize-none"
                                    required></textarea>
                            </div>
                            <button type="submit"
                                class="w-[max-content] mx-auto {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} px-6 py-3 text-white font-semibold text-sm hover:opacity-90 focus:outline-none focus:ring-4 focus:ring-{{ $theme->color ? $theme->color . '/30' : 'blue-300' }} transition">
                                Değerlendirmeyi Gönder
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        @endif

        @php
            $approvedComments = ($data->comments ?? collect())->where('status', 1);
        @endphp

        @if ($approvedComments->isNotEmpty())
            <section
                class="py-12 bg-white border-t {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }}">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-2xl font-bold mb-8 text-gray-900">Değerlendirmeler</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($approvedComments as $comment)
                            @include('frontend.parts.components.product.comments', [
                                'comment' => $comment,
                                'product' => $product,
                            ])
                        @endforeach
                    </div>
                </div>
            </section>
        @else
            <section class="pb-12 pt-8 bg-white">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <p class="text-gray-500 text-sm">Henüz değerlendirme yapılmadı.</p>
                </div>
            </section>
        @endif
    @endif

    @if (($data->related ?? collect())->isNotEmpty())
        <section class="py-12 border-t border-gray-200">
            <div class="max-w-[1680px] mx-auto px-4 md:px-8">
                <h2 class="text-base md:text-lg font-semibold text-gray-900 mb-6 uppercase ">
                    Benzer Ürünler
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-[1px] md:gap-3">
                    @foreach ($data->related->take(4) as $related)
                        @php
                            $slug = \Illuminate\Support\Str::slug($related->title ?? 'urun');
                        @endphp
                        @include('frontend.parts.components.product.card', [
                            'product' => $related,
                            'slug' => $slug,
                        ])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const colorVariantGroups = document.querySelectorAll('[data-color-variant-group]');

            colorVariantGroups.forEach((group) => {
                const options = group.querySelectorAll('.color-variant-option');
                const hiddenInput = group.nextElementSibling;

                if (!options.length || !hiddenInput) {
                    return;
                }

                options.forEach((option) => {
                    option.addEventListener('click', function() {
                        options.forEach((item) => {
                            const isSelected = item === option;
                            const check = item.querySelector(
                                '.color-variant-check');

                            item.classList.toggle('ring-2', isSelected);
                            item.classList.toggle('ring-offset-1', isSelected);
                            item.classList.toggle('ring-gray-700', isSelected);
                            item.classList.toggle('opacity-40', !isSelected);
                            item.classList.toggle('saturate-0', !isSelected);
                            item.classList.toggle('scale-95', !isSelected);

                            if (check) {
                                check.classList.toggle('hidden', !isSelected);
                                check.classList.toggle('flex', isSelected);
                            }
                        });

                        hiddenInput.value = option.dataset.value || '';
                        hiddenInput.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    });
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canUseDesktopZoom = window.matchMedia('(min-width: 1024px)').matches;
            if (!canUseDesktopZoom) {
                return;
            }

            const zoomItems = document.querySelectorAll('.product-zoom-item');
            zoomItems.forEach((item) => {
                const image = item.querySelector('img');
                const lens = item.querySelector('.product-zoom-lens');
                const preview = item.querySelector('.product-zoom-preview');
                const zoomSrc = item.getAttribute('data-zoom-src') || image?.getAttribute('src');

                if (!image || !lens || !preview || !zoomSrc) {
                    return;
                }

                const lensWidth = 220;
                const lensHeight = 220;
                const zoomScale = 2.4;

                preview.style.backgroundImage = `url("${zoomSrc}")`;
                preview.style.backgroundSize = `${zoomScale * 100}%`;
                lens.style.width = `${lensWidth}px`;
                lens.style.height = `${lensHeight}px`;

                const showZoom = () => {
                    lens.classList.remove('hidden');
                    preview.classList.remove('hidden');
                };

                const hideZoom = () => {
                    lens.classList.add('hidden');
                    preview.classList.add('hidden');
                };

                item.addEventListener('mouseenter', showZoom);
                item.addEventListener('mouseleave', hideZoom);

                item.addEventListener('mousemove', (event) => {
                    const rect = image.getBoundingClientRect();
                    const x = event.clientX - rect.left;
                    const y = event.clientY - rect.top;

                    if (x < 0 || y < 0 || x > rect.width || y > rect.height) {
                        hideZoom();
                        return;
                    }

                    showZoom();

                    const halfLensW = lensWidth / 2;
                    const halfLensH = lensHeight / 2;

                    const lensX = Math.max(0, Math.min(x - halfLensW, rect.width - lensWidth));
                    const lensY = Math.max(0, Math.min(y - halfLensH, rect.height - lensHeight));

                    lens.style.left = `${lensX}px`;
                    lens.style.top = `${lensY}px`;

                    const xPercent = rect.width > 0 ? (x / rect.width) * 100 : 50;
                    const yPercent = rect.height > 0 ? (y / rect.height) * 100 : 50;
                    preview.style.backgroundPosition = `${xPercent}% ${yPercent}%`;
                });
            });
        });
    </script>
@endsection
