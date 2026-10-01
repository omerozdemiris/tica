@php
    $sectionList = $sections->values();
    $sectionCount = $sectionList->count();
@endphp

@for ($i = 0; $i < $sectionCount; $i++)
    @php
        $section = $sectionList[$i];
    @endphp

    @if ($section->name === 'slider')
        @include('frontend.parts.home.slider')
        @continue
    @elseif ($section->name === 'new_products')
        @include('frontend.parts.home.products.new_products')
        @continue
    @elseif ($section->name === 'all_categories')
        @include('frontend.parts.home.products.all_categories')
        @continue
    @elseif ($section->name === 'video')
        @include('frontend.parts.home.video')
        @continue
    @endif

    @php
        $type = $section->data['type'] ?? 'products';
        $items = $section->items ?? collect();
    @endphp

    @if (!$items->count())
        @continue
    @endif

    @if ($type === 'categories')
        @php
            $categoryGroup = collect([$section]);
            $cursor = $i + 1;
            while ($cursor < $sectionCount) {
                $nextSection = $sectionList[$cursor];
                $nextType = $nextSection->data['type'] ?? null;
                $nextItems = $nextSection->items ?? collect();
                if (
                    $nextType !== 'categories' ||
                    !$nextItems->count() ||
                    in_array($nextSection->name, ['slider', 'new_products', 'all_categories', 'video'], true)
                ) {
                    break;
                }
                $categoryGroup->push($nextSection);
                $cursor++;
            }
            $groupCount = $categoryGroup->count();
            $gridClass =
                $groupCount === 1 ? 'md:grid-cols-1' : ($groupCount === 2 ? 'md:grid-cols-2' : 'md:grid-cols-3');
            $i = $cursor - 1;
        @endphp
        <section class="max-w-[1680px] mx-auto px-4 md:px-0 md:py-20 py-6">
            <div class="grid grid-cols-1 {{ $gridClass }} gap-8">
                @foreach ($categoryGroup as $categorySection)
                    @php
                        $categoryItem = ($categorySection->items ?? collect())->first();
                        $bannerImage = $categoryItem?->banner_photo
                            ? asset($categoryItem->banner_photo)
                            : ($categoryItem?->photo
                                ? asset($categoryItem->photo)
                                : asset('assets/img/resim-yok.svg'));
                        $previewProducts = collect($categoryItem?->banner_preview_products ?? [])->take(5);
                        $sectionTitle = $categorySection->title ?: $categoryItem->name ?? 'Kategori';
                        $sectionDescription = $categorySection->description;
                    @endphp
                    <div class="group relative block overflow-hidden bg-gray-100 min-h-screen">
                        @if ($categoryItem)
                            <a href="{{ route('categories.show', [$categoryItem->id, $categoryItem->slug]) }}"
                                class="absolute inset-0 z-10" aria-label="{{ $sectionTitle }}"></a>
                        @endif
                        <img src="{{ $bannerImage }}" alt="{{ $sectionTitle }}"
                            class="absolute inset-0 w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/5"></div>
                        <div class="relative z-20 p-4 md:p-6 pointer-events-none">
                            <h3 class="text-xs md:text-sm uppercase  font-medium text-gray-900">
                                {{ $sectionTitle }}
                            </h3>
                            @if ($sectionDescription)
                                <p class="text-[11px] text-gray-700 mt-1 line-clamp-2">
                                    {{ $sectionDescription }}
                                </p>
                            @endif
                        </div>
                        @if ($previewProducts->isNotEmpty())
                            <div
                                class="absolute left-3 bottom-3 z-30 flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-white/95 border border-gray-300/50 px-1 py-1">
                                @foreach ($previewProducts as $previewProduct)
                                    @php
                                        $previewSlug = \Illuminate\Support\Str::slug($previewProduct->title ?? 'urun');
                                        $previewImage = $previewProduct->photo
                                            ? asset($previewProduct->photo)
                                            : asset('assets/img/resim-yok.svg');
                                    @endphp
                                    <a href="{{ route('products.show', [$previewProduct->id, $previewSlug]) }}"
                                        class="block w-16 h-24 border border-gray-300/60 bg-white overflow-hidden"
                                        title="{{ $previewProduct->title }}">
                                        <img src="{{ $previewImage }}" alt="{{ $previewProduct->title }}"
                                            class="w-full h-full object-cover">
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @elseif ($type === 'products' || $type === 'showcase')
        <section class="py-16">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="md:text-2xl text-md font-semibold text-gray-900">
                            {{ $section->title }}
                        </h2>
                        @if ($section->description)
                            <p class="md:text-sm text-xs text-gray-500 mt-1">
                                {{ $section->description }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-5 gap-2 md:gap-4">
                    @foreach ($items as $product)
                        @php $slug = Str::slug($product->title); @endphp
                        @include('frontend.parts.components.product.card', [
                            'product' => $product,
                            'slug' => $slug,
                        ])
                    @endforeach
                </div>
            </div>
        </section>
    @elseif ($type === 'blogs')
        <section class="py-16">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="md:text-2xl text-md font-semibold text-gray-900">
                            {{ $section->title }}
                        </h2>
                        @if ($section->description)
                            <p class="md:text-sm text-xs text-gray-500 mt-1">
                                {{ $section->description }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($items as $blog)
                        <a href="{{ route('blog.show', [$blog->id, $blog->slug]) }}" class="group block">
                            <div class="aspect-[4/3] overflow-hidden bg-gray-100 mb-4">
                                @if ($blog->photo)
                                    <img src="{{ asset($blog->photo) }}" alt="{{ $blog->title }}"
                                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                                        <i class="ri-article-line text-5xl"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="space-y-2">
                                <h3
                                    class="text-lg font-bold text-gray-900 leading-tight group-hover:text-gray-600 transition-colors">
                                    {{ $blog->title }}
                                </h3>
                                @if ($blog->excerpt)
                                    <p class="text-sm text-gray-500 line-clamp-2 leading-relaxed">
                                        {{ $blog->excerpt }}
                                    </p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endfor
