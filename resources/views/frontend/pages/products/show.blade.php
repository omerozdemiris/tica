@php
    $product = $data->product;
    $variants = $product->variants ?? collect();
    $canPurchase =
        (optional($store)->sell_enabled ?? true) && (!(optional($store)->auth_required ?? false) || auth()->check());
    $primaryCategory = $product->categories->first();
    $breadcrumbs = $data->breadcrumbs ?? [];

    if ($variants->isNotEmpty()) {
        $inStock = $variants->contains(fn($v) => $v->stock_type == 1 || ($v->stock !== null && $v->stock > 0));
    } else {
        $inStock = $product->stock_type == 1 || ($product->stock !== null && $product->stock > 0);
    }
@endphp
@extends('frontend.layouts.app')
@section('title', 'Ürün - ' . $product->title)
@section('breadcrumb_title', $product->title)
@section('og_title', $product->meta_title ?? '')
@section('og_description', $product->meta_description ?? Str::limit(strip_tags($product->description), 250))
@section('content')
    @include('frontend.parts.breadcrumb')
    <section class="py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-12">
            <div class="space-y-4 lg:col-span-7">
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
                    <div class="product-gallery-main">
                        <div class="swiper productMainSwiper bg-white border border-gray-200 rounded-3xl overflow-hidden">
                            <div class="swiper-wrapper">
                                @foreach ($allImages as $index => $image)
                                    <div class="swiper-slide">
                                        <a href="{{ $image->url }}" data-fancybox="product-gallery"
                                            data-caption="{{ $product->title }}" class="block w-full h-[30rem]">
                                            <img src="{{ $image->url }}" alt="{{ $product->title }}"
                                                class="w-full h-[30rem] object-cover cursor-pointer">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @if ($allImages->count() > 1)
                        <div class="product-gallery-thumbs">
                            <div class="swiper productThumbsSwiper py-2 px-1">
                                <div class="swiper-wrapper ">
                                    @foreach ($allImages as $index => $image)
                                        <div class="swiper-slide cursor-pointer " data-index="{{ $index }}">
                                            <div
                                                class="aspect-square bg-white border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl overflow-hidden {{ $index === 0 ? 'ring-2 ring-' . $theme->color : '' }}">
                                                <img src="{{ $image->url }}" alt="{{ $product->title }}"
                                                    class="w-full h-full object-cover">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div
                        class="aspect-square bg-white border border-gray-200 rounded-3xl overflow-hidden flex items-center justify-center">
                        <div class="text-gray-300 text-5xl">
                            <i class="ri-image-line"></i>
                        </div>
                    </div>
                @endif
            </div>
            <div class="space-y-6 lg:col-span-5">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $product->title }}</h1>
                    @if (!empty($product->sku))
                        <p class="text-xs font-semibold text-gray-500 mt-1">
                            SKU: {{ $product->sku }}
                        </p>
                    @endif
                    <div class="flex items-center gap-2 mt-2">
                        @foreach ($product->categories as $category)
                            <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                                class="inline-flex items-center text-xs px-3 py-1 {{ $theme->color ? 'bg-' . $theme->color . '/5' : 'bg-blue-50' }} {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} rounded-full">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="border border-gray-200 rounded-2xl p-6 bg-white shadow-sm"
                    data-base-price="{{ (float) $product->price }}">
                    <p class="text-sm text-gray-600">Fiyat</p>
                    <div
                        class="mt-2 text-3xl font-semibold product-price-display {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                        @if (!is_null($product->price))
                            {{ number_format((float) $product->price, 2, ',', '.') }} ₺
                        @else
                            <span
                                class="text-sm {{ $theme->color ? 'text-' . $theme->color . '/60' : 'text-gray-500' }}">Fiyat
                                bilgisi için iletişime geçin</span>
                        @endif
                    </div>
                    @if ($variants->isNotEmpty())
                        <div class="mt-6 space-y-6">
                            @foreach ($variants->groupBy('attribute_id')->sortByDesc(function ($attrVariants) {
            return $attrVariants->contains(function ($v) {
                return !empty($v->term?->file) || (isset($v->term?->value) && str_starts_with($v->term->value, '#'));
            })
                ? 1
                : 0;
        }) as $attributeId => $attrVariants)
                                @php $attribute = $attrVariants->first()->attribute; @endphp
                                <div>
                                    <label
                                        class="text-sm font-bold uppercase tracking-wider {{ $theme->color ? 'text-' . $theme->color . '/60' : 'text-gray-700' }}">
                                        {{ $attribute->name ?? 'Özellik' }}
                                    </label>
                                    @php
                                        $hasVisualTerms = $attrVariants->contains(function ($variant) {
                                            return !empty($variant->term?->file) ||
                                                (isset($variant->term?->value) &&
                                                    str_starts_with($variant->term->value, '#'));
                                        });
                                    @endphp

                                    @if ($hasVisualTerms)
                                        <input type="hidden" name="variant_ids[]" class="variant-hidden-input"
                                            form="add-to-cart-form" required>
                                        <div class="mt-3 grid grid-cols-6 gap-1" data-term-grid>
                                            @foreach ($attrVariants as $variant)
                                                @php
                                                    $term = $variant->term;
                                                    $displayName = $term->name ?? '';
                                                    $file = $term->file ?? null;
                                                    $colorMatch =
                                                        isset($term->value) && str_starts_with($term->value, '#')
                                                            ? $term->value
                                                            : null;
                                                @endphp
                                                <button type="button"
                                                    class="relative term-grid-option flex items-center justify-center rounded-lg border border-gray-300 bg-white hover:bg-gray-50 transition p-0.5 h-10 overflow-hidden"
                                                    data-value="{{ $variant->id }}" data-name="{{ $displayName }}"
                                                    data-color="{{ $colorMatch }}" title="{{ $displayName }}">
                                                    @if ($file)
                                                        <img src="{{ asset($file) }}" alt="{{ $displayName }}"
                                                            class="w-full h-full rounded-md object-cover">
                                                    @elseif ($colorMatch)
                                                        <span class="w-full h-full rounded-md"
                                                            style="background-color: {{ $colorMatch }}"></span>
                                                    @else
                                                        <span
                                                            class="text-[11px] font-semibold text-gray-700 truncate max-w-[70px]">
                                                            {{ $displayName }}
                                                        </span>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="relative mt-2 custom-select-container">
                                            <button type="button"
                                                class="w-full flex items-center justify-between px-4 py-3 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-gray-50 focus:outline-none transition-all custom-select-button">
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
                                                class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-xl hidden custom-select-menu max-h-60 overflow-y-auto">
                                                @foreach ($attrVariants as $variant)
                                                    @php
                                                        $term = $variant->term;
                                                        $displayName = $term->name ?? '';
                                                        $colorMatch =
                                                            isset($term->value) && str_starts_with($term->value, '#')
                                                                ? $term->value
                                                                : null;
                                                    @endphp
                                                    <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors custom-select-option"
                                                        data-value="{{ $variant->id }}" data-name="{{ $displayName }}"
                                                        data-color="{{ $colorMatch }}"
                                                        data-price="{{ $variant->price ?? '' }}">
                                                        <div class="flex items-center gap-3">
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
                    <form action="{{ route('cart.store') }}" method="POST" class="mt-6 space-y-4" data-cart-add-form
                        id="add-to-cart-form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        @if (!(optional($store)->sell_enabled ?? true))
                            <div
                                class="px-4 py-3 rounded-xl {{ $theme->color ? 'bg-' . $theme->color . '/5' : 'bg-amber-100' }} {{ $theme->color ? 'text-' . $theme->color : 'text-amber-700' }} text-sm">
                                Satın alma işlemi şu anda kapalıdır.
                            </div>
                        @elseif (!$inStock)
                            <div
                                class="px-4 py-3 rounded-xl bg-red-50 text-red-600 text-sm font-semibold border border-red-200 flex items-center gap-2">
                                <i class="ri-error-warning-line text-lg"></i>
                                Bu ürün şu anda stokta bulunmuyor.
                            </div>
                        @elseif (!is_null($product->price) || $variants->isNotEmpty())
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Adet</label>
                                <div class="mt-2 flex items-center border border-gray-200 rounded-xl overflow-hidden w-36">
                                    <button type="button"
                                        class="px-4 py-2 text-lg font-medium {{ $theme->color ? 'text-' . $theme->color . '/60' : 'text-gray-600' }} decrement">
                                        -
                                    </button>
                                    <input type="number" name="quantity" value="1" min="1"
                                        class="w-full text-center text-sm font-semibold py-2 outline-none">
                                    <button type="button" class="px-4 py-2 text-lg font-medium text-gray-600 increment">
                                        +
                                    </button>
                                </div>
                            </div>
                            <button type="submit"
                                class="hover:{{ $theme->color ? 'bg-' . $theme->color . '/30' : 'bg-blue-100' }} hover:{{ $theme->color ? 'text-' . $theme->color . '/70' : 'text-blue-700' }} w-full px-4 py-3 flex items-center justify-center gap-4 rounded-xl {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white font-semibold hover:{{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-700' }} transition">
                                <span><i class="ri-shopping-bag-2-line font-light text-xl"></i></span>
                                Sepete Ekle
                            </button>
                        @else
                            <div
                                class="px-4 py-3 rounded-xl {{ $theme->color ? 'bg-' . $theme->color . '/5' : 'bg-gray-100' }} {{ $theme->color ? 'text-' . $theme->color . '/60' : 'text-gray-600' }} text-sm">
                                Bu ürün için fiyat bilgisi henüz eklenmedi. Lütfen bizimle iletişime geçin.
                            </div>
                        @endif
                    </form>
                </div>
            </div>
            <div
                class="md:col-span-4 prose prose-sm {{ $theme->color ? 'prose-' . $theme->color : 'prose-blue' }} max-w-none">
                {!! $product->description !!}
            </div>
        </div>
    </section>
    @push('scripts')
        <script>
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.term-grid-option');
                if (!btn) return;

                const block = btn.closest('[data-term-grid]')?.parentElement;
                const hidden = block.querySelector('input.variant-hidden-input');
                if (!hidden) return;

                block.querySelectorAll('.term-grid-option.is-selected').forEach(el => {
                    el.classList.remove('is-selected');
                    el.style.borderColor = '';
                    el.style.boxShadow = '';
                    const old = el.querySelector('.term-check-overlay');
                    if (old) old.remove();
                });

                btn.classList.add('is-selected');
                btn.style.borderColor = '{{ $themeColors['primary'] ?? '#2563eb' }}';
                btn.style.boxShadow = '0 0 0 2px {{ $themeColors['primary_light'] ?? 'rgba(37,99,235,0.25)' }}';

                if (!btn.querySelector('.term-check-overlay')) {
                    const overlay = document.createElement('div');
                    overlay.className =
                        'term-check-overlay absolute z-10 inset-0 flex items-center justify-center bg-black/30 rounded-md pointer-events-none';
                    overlay.innerHTML = '<i class="ri-check-line text-white text-2xl drop-shadow"></i>';
                    btn.appendChild(overlay);
                }

                hidden.value = btn.dataset.value || '';
                hidden.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            });

            const cartForm = document.getElementById('add-to-cart-form');
            if (cartForm) {
                cartForm.addEventListener('submit', function(e) {
                    const hiddens = cartForm.querySelectorAll('.variant-hidden-input');
                    if (!hiddens.length) return;

                    const missing = [];
                    hiddens.forEach(function(input) {
                        if (!input.value || input.value === '') {
                            const group = input.closest('div');
                            const label = group ? group.querySelector('label') : null;
                            const name = label ? label.textContent.trim() : 'Özellik';
                            missing.push(name);
                            if (group) group.classList.add('ring-2', 'ring-red-400', 'rounded-2xl');
                            setTimeout(function() {
                                if (group) group.classList.remove('ring-2', 'ring-red-400',
                                    'rounded-2xl');
                            }, 3000);
                        }
                    });

                    if (missing.length > 0) {
                        e.preventDefault();
                        const existing = cartForm.querySelector('.variant-error-msg');
                        if (existing) existing.remove();

                        const msg = document.createElement('div');
                        msg.className =
                            'variant-error-msg px-4 py-3 rounded-xl bg-red-50 text-red-600 text-sm font-semibold border border-red-200 animate-pulse';
                        msg.textContent = 'Lütfen seçim yapın: ' + missing.join(', ');
                        cartForm.prepend(msg);
                        setTimeout(function() {
                            msg.remove();
                        }, 4000);
                    }
                });
            }
        </script>
    @endpush
    @if (isset($data->canComment) && $data->canComment)
        <section class="pb-12 pt-8 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-4xl mx-auto">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">
                        Ürünü Değerlendir <i class="ri-chat-quote-fill"></i>
                    </h2>
                    <form action="{{ route('user.comments.store', $product->id) }}" method="POST"
                        class="space-y-6 bg-gray-50 p-6 rounded-xl shadow-sm">
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
                                1 (kötü) – 5 (mükemmel)
                            </p>
                        </div>
                        <div>
                            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                                Yorumunuz
                            </label>
                            <textarea name="comment" id="comment" rows="4" placeholder="Ürün hakkındaki deneyiminizi paylaşın..."
                                class="w-full rounded-xl border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-300' }} px-4 py-3 text-sm text-gray-900 focus:border-{{ $theme->color ? $theme->color : 'blue-500' }} focus:ring-2 focus:ring-{{ $theme->color ? $theme->color . '/20' : 'blue-200' }} transition resize-none"
                                required></textarea>
                        </div>
                        <button type="submit"
                            class="w-[max-content] mx-auto rounded-xl {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} px-6 py-3 text-white font-semibold text-sm hover:opacity-90 focus:outline-none focus:ring-4 focus:ring-{{ $theme->color ? $theme->color . '/30' : 'blue-300' }} transition">
                            Değerlendirmeyi Gönder
                        </button>
                    </form>
                </div>
            </div>
        </section>
    @endif
    @if (isset($data->comments) && $data->comments->where('status', 1)->isNotEmpty())
        <section
            class="py-12 bg-white border-t {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }}">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold mb-8 text-gray-900">Değerlendirmeler</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($data->comments->where('status', 1) as $comment)
                        @include('frontend.parts.components.product.comments', [
                            'comment' => $comment,
                            'product' => $product,
                        ])
                    @endforeach
                </div>
            </div>
        </section>
    @elseif (isset($data->comments))
        <section class="pb-12 pt-8 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-gray-500 text-sm">Henüz değerlendirme yapılmadı.</p>
            </div>
        </section>
    @endif
    @if (($data->related ?? collect())->isNotEmpty())
        <section class=" py-12 border-t border-gray-200">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">
                    Benzer Ürünler</h2>
                <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-5 gap-6">
                    @foreach ($data->related->take(5) as $related)
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
@endsection
