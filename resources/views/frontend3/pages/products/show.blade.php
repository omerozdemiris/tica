@php
    $product = $data->product;
    $variants = $product->variants ?? collect();
    $canPurchase =
        (optional($store)->sell_enabled ?? true) && (!(optional($store)->auth_required ?? false) || auth()->check());
    $primaryCategory = $product->categories->first();
    $breadcrumbs = $data->breadcrumbs ?? [];
    $price = (float) $product->price;
    $discountPrice = (float) $product->discount_price;
    $hasDiscount =
        !is_null($product->discount_price) &&
        $product->discount_price > 0 &&
        $product->discount_price < $product->price;
@endphp
@extends($template . '.layouts.app')
@section('title', 'Ürün - ' . $product->title)
@section('breadcrumb_title', $product->title)
@section('content')
@section('og_title', $product->meta_title ?? '')
@section('og_description', $product->meta_description ?? Str::limit(strip_tags($product->description), 250))
@include($template . '.parts.breadcrumb')
<section class="py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <div class="lg:col-span-6 space-y-4">
                @php
                    $allImages = collect();
                    if ($product->photo) {
                        $allImages->push((object) ['id' => 'main', 'url' => asset($product->photo), 'type' => 'main']);
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
                    <div
                        class="border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }} bg-white overflow-hidden">
                        <div class="swiper productMainSwiper aspect-[4/5]">
                            <div class="swiper-wrapper">
                                @foreach ($allImages as $index => $image)
                                    <div class="swiper-slide">
                                        <a href="{{ $image->url }}" data-fancybox="product-gallery"
                                            class="block w-full h-full">
                                            <img src="{{ $image->url }}" alt="{{ $product->title }}"
                                                class="w-full h-full object-cover">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @if ($allImages->count() > 1)
                        <div class="grid grid-cols-4 md:grid-cols-6 gap-2">
                            @foreach ($allImages as $index => $image)
                                <div class="cursor-pointer border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }} overflow-hidden aspect-square thumb-item"
                                    data-index="{{ $index }}">
                                    <img src="{{ $image->url }}"
                                        class="w-full h-full object-cover opacity-50 hover:opacity-100 transition-opacity">
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div
                        class="aspect-[4/5] bg-gray-50 border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }} flex items-center justify-center text-gray-200">
                        <i class="ri-image-line text-6xl"></i>
                    </div>
                @endif
            </div>
            <div class="lg:col-span-6">
                <div class="sticky top-32 space-y-8">
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            @foreach ($product->categories as $category)
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                                    {{ $category->name }}
                                </span>
                                @if (!$loop->last)
                                    <span class="text-gray-200">/</span>
                                @endif
                            @endforeach
                        </div>
                        <h1
                            class="text-lg md:text-2xl font-bold uppercase tracking-tighter leading-tight mb-4 text-gray-900">
                            {{ $product->title }}
                        </h1>
                        <div class="mb-6">
                            <div
                                class="prose prose-sm text-sm max-w-none {{ $theme->color ? 'prose-' . $theme->color : 'prose-blue' }} text-gray-500 leading-relaxed">
                                {!! $product->description !!}
                            </div>
                        </div>
                        <div class="flex items-baseline gap-4 mt-6"
                            data-base-price="{{ (float) ($hasDiscount ? $discountPrice : $product->price) }}">
                            @if ($hasDiscount)
                                <span
                                    class="text-2xl font-black product-price-display {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                    {{ number_format($discountPrice, 2, ',', '.') }} ₺
                                </span>
                                <span class="text-lg font-bold text-gray-300 line-through">
                                    {{ number_format($price, 2, ',', '.') }} ₺
                                </span>
                            @elseif ($price > 0)
                                <span
                                    class="text-2xl font-black product-price-display {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                    {{ number_format($price, 2, ',', '.') }} ₺
                                </span>
                            @else
                                <span class="text-lg font-black uppercase tracking-widest text-gray-400">Fiyat bilgisi
                                    için iletişime geçin</span>
                            @endif
                        </div>
                    </div>
                    <div
                        class="border-t {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }} pt-8 space-y-6">
                        @if ($variants->isNotEmpty())
                            <div class="space-y-8">
                                @foreach ($variants->groupBy('attribute_id') as $attributeId => $attrVariants)
                                    @php $attribute = $attrVariants->first()->attribute; @endphp
                                    <div>
                                        <label
                                            class="text-[11px] font-black uppercase tracking-widest mb-4 block text-gray-400">
                                            {{ $attribute->name ?? 'Seçenek' }} Seçin
                                        </label>

                                        <div class="relative custom-select-container">
                                            <button type="button"
                                                class="w-full flex items-center justify-between bg-white border border-gray-200 px-6 py-4 text-xs font-black uppercase tracking-widest outline-none appearance-none cursor-pointer text-gray-700 hover:bg-gray-50 transition-all custom-select-button">
                                                <div class="flex items-center gap-4">
                                                    <span
                                                        class="w-4 h-4 rounded-full border border-gray-100 hidden selected-color-preview"></span>
                                                    <span class="selected-text">Seçiniz</span>
                                                </div>
                                                <i class="ri-arrow-down-s-line text-gray-400"></i>
                                            </button>

                                            <input type="hidden" name="variant_ids[]" class="variant-hidden-input"
                                                form="add-to-cart-form" required>

                                            <div
                                                class="absolute z-50 w-full mt-1 bg-white border border-gray-200 shadow-2xl hidden custom-select-menu max-h-60 overflow-y-auto">
                                                @foreach ($attrVariants as $variant)
                                                    @php
                                                        $term = $variant->term;
                                                        $displayName = $term->name ?? '';
                                                        $colorMatch =
                                                            isset($term->value) && str_starts_with($term->value, '#')
                                                                ? $term->value
                                                                : null;
                                                    @endphp
                                                    <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 cursor-pointer transition-colors custom-select-option"
                                                        data-value="{{ $variant->id }}"
                                                        data-name="{{ $displayName }}"
                                                        data-color="{{ $colorMatch }}"
                                                        data-price="{{ $variant->price ?? '' }}">
                                                        <div class="flex items-center gap-4">
                                                            @if ($colorMatch)
                                                                <span
                                                                    class="w-4 h-4 rounded-full border border-gray-100"
                                                                    style="background-color: {{ $colorMatch }}"></span>
                                                            @endif
                                                            <span
                                                                class="text-xs font-black uppercase tracking-tighter text-gray-900">{{ $displayName }}</span>
                                                        </div>
                                                        @if ($variant->price)
                                                            <span
                                                                class="text-[10px] font-black opacity-40">{{ number_format($variant->price, 2, ',', '.') }}
                                                                ₺</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <form action="{{ route('cart.store') }}" method="POST" data-cart-add-form class="space-y-6"
                            id="add-to-cart-form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <div class="flex items-stretch gap-4">
                                <div
                                    class="w-32 flex items-stretch border rounded-full border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }}">
                                    <button type="button"
                                        class="w-10 flex rounded-full items-center justify-center text-xl font-bold hover:bg-gray-50 decrement text-gray-600">-</button>
                                    <input type="number" name="quantity" value="1" min="1"
                                        class="flex-1 w-full text-center font-black outline-none bg-transparent text-gray-900">
                                    <button type="button"
                                        class="w-10 flex rounded-full items-center justify-center text-xl font-bold hover:bg-gray-50 increment text-gray-600">+</button>
                                </div>
                                @if (!(optional($store)->sell_enabled ?? true))
                                    <div
                                        class="flex-1 bg-gray-100 text-gray-400 px-8 py-4 text-xs font-black uppercase tracking-widest flex items-center justify-center">
                                        Satış Kapalı
                                    </div>
                                @elseif (!is_null($product->price) || $variants->isNotEmpty())
                                    <button type="submit"
                                        class="flex-1 {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} rounded-full text-white px-8 py-4 text-xs font-black uppercase tracking-widest hover:bg-opacity-90 transition flex items-center justify-center gap-3 shadow-xl">
                                        <i class="ri-shopping-bag-line text-lg"></i>
                                        Sepete Ekle
                                    </button>
                                @else
                                    <div
                                        class="flex-1 bg-gray-100 text-gray-400 px-8 py-4 text-xs font-black uppercase tracking-widest flex items-center justify-center">
                                        Stokta Yok
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if ($data->canComment)
        <section class="pb-20 pt-10 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto">

                    <h2 class="text-2xl font-bold text-gray-900 mb-6">
                        Ürünü Değerlendir <i class="ri-chat-quote-fill"></i>
                    </h2>
                    <form action="{{ route('user.comments.store', $product->id) }}" method="POST"
                        class="space-y-6 bg-gray-50 p-6 rounded-2xl shadow-sm">
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
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition resize-none"
                                required></textarea>
                        </div>
                        <button type="submit"
                            class="w-[max-content] mx-auto rounded-xl bg-indigo-600 px-6 py-3 text-white font-semibold text-sm hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300 transition">
                            Değerlendirmeyi Gönder
                        </button>
                    </form>
                </div>
            </div>
        </section>
    @endif
    {{-- Değerlendirmeler --}}
    @if ($data->comments->where('status', 1)->isNotEmpty())
        <section
            class="py-24 bg-white border-t {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }}">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold mb-8 text-gray-900">Değerlendirmeler</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($data->comments->where('status', 1) as $comment)
                        @include($template . '.parts.components.product.comments', [
                            'comment' => $comment,
                            'product' => $product,
                        ])
                    @endforeach
                </div>
            </div>
        </section>
    @else
        <section class="pb-20 pt-10 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-gray-500 text-sm">Henüz değerlendirme yapılmadı.</p>
            </div>
        </section>
    @endif
</section>
@if (($data->related ?? collect())->isNotEmpty())
    <section
        class="py-24 bg-white border-t {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }}">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-8 text-gray-900">Bunları da sevebilirsiniz
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-4">
                @foreach ($data->related->take(4) as $related)
                    @php $slug = \Illuminate\Support\Str::slug($related->title ?? 'urun'); @endphp
                    @include($template . '.parts.components.product.card', [
                        'product' => $related,
                        'slug' => $slug,
                    ])
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection
