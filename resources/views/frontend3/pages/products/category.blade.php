@extends($template . '.layouts.app')

@php
    $category = $data->category;
    $products = $data->products ?? collect();
    $rootCategories = $data->rootCategories ?? collect();
    $breadcrumbs = $data->breadcrumbs ?? [];
    $activeCategoryPathIds = $data->activeCategoryPathIds ?? [$category->id];
    $descriptionPreview = $category->description
        ? \Illuminate\Support\Str::limit(strip_tags($category->description), 200)
        : null;
@endphp
@section('title', $category->name)
@section('breadcrumb_title', $category->name)
@section('content')
@section('og_title', $category->meta_title ?? '')
@section('og_description', $category->meta_description ?? Str::limit(strip_tags($category->description), 250))
@include($template . '.parts.breadcrumb')
<section class="py-16">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-12">
            <aside class="lg:w-72 hidden lg:block flex-shrink-0">
                <div
                    class="border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }} sticky top-32 bg-white">
                    <div
                        class="p-3 border-b {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }} {{ $theme->color ? 'bg-' . $theme->color . '/5' : 'bg-gray-50' }}">
                        <h2 class="text-lg font-bold  {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                            Tüm Kategoriler</h2>
                    </div>
                    <nav class="p-6">
                        <ul class="space-y-4">
                            @foreach ($rootCategories as $rootCategory)
                                <li>
                                    <a href="{{ route('categories.show', [$rootCategory->id, $rootCategory->slug]) }}"
                                        class="text-[11px] font-black uppercase tracking-widest hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} transition-colors {{ in_array($rootCategory->id, $activeCategoryPathIds) ? ($theme->color ? 'text-' . $theme->color : 'text-blue-600') : 'text-gray-400' }}">
                                        {{ $rootCategory->name }}
                                    </a>
                                    @if (in_array($rootCategory->id, $activeCategoryPathIds) && $rootCategory->children->isNotEmpty())
                                        <ul
                                            class="mt-4 ml-4 space-y-3 border-l {{ $theme->color ? 'border-' . $theme->color . '/20' : 'border-gray-100' }} pl-4">
                                            @foreach ($rootCategory->children as $child)
                                                <li>
                                                    <a href="{{ route('categories.show', [$child->id, $child->slug]) }}"
                                                        class="text-[10px] font-bold uppercase tracking-widest hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} transition-colors {{ $child->id == $category->id ? ($theme->color ? 'text-' . $theme->color : 'text-blue-600') : 'text-gray-400' }}">
                                                        {{ $child->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                </div>
            </aside>
            <div class="flex-1">
                @if ($products->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-x-4 gap-y-12">
                        @foreach ($products as $product)
                            @php
                                $slug = \Illuminate\Support\Str::slug($product->title ?? 'urun');
                            @endphp
                            @include($template . '.parts.components.product.card', [
                                'product' => $product,
                                'slug' => $slug,
                            ])
                        @endforeach
                    </div>
                    <div class="mt-16">
                        {{ $products->links($template . '.parts.components.pagination') }}
                    </div>
                @else
                    <div
                        class="bg-white border {{ $theme->color ? 'border-' . $theme->color . '/20' : 'border-gray-200' }} p-20 text-center">
                        <i class="ri-search-2-line text-4xl text-gray-200 mb-4 block"></i>
                        <p class="text-sm font-black uppercase tracking-widest text-gray-400">
                            BU KOLEKSİYONDA HENÜZ ÜRÜN BULUNMUYOR.
                        </p>
                        <a href="{{ route('products.index') }}"
                            class="inline-block mt-6 px-8 py-3 {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-xs font-black uppercase tracking-widest hover:bg-opacity-90 transition">
                            TÜM ÜRÜNLERİ GÖR
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
