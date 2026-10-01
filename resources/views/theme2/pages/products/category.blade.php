@extends('frontend.layouts.app')
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
@section('og_title', $category->meta_title ?? '')
@section('og_description', $category->meta_description ?? Str::limit(strip_tags($category->description), 250))
@section('content')
    @include('frontend.parts.breadcrumb', [
        'currentCategory' => $category,
        'childCategories' => $data->childCategories ?? collect(),
    ])
    @if ($descriptionPreview)
        <div class="bg-white border-b border-gray-100">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-sm text-gray-500">{{ $descriptionPreview }}</p>
            </div>
        </div>
    @endif
    <section class="py-4 md:py-4">
        <div class="max-w-[1680px] mx-auto">
            <div class="flex-1">
                @if ($products->count() > 0)
                    <div class="grid grid-cols-2 lg:grid-cols-12 gap-[1px] md:gap-8">
                        @foreach ($products as $productIndex => $product)
                            @php
                                $slug = \Illuminate\Support\Str::slug($product->title ?? 'urun');
                            @endphp
                            <div class="col-span-1 lg:col-span-4">
                                @include('frontend.parts.components.product.card', [
                                    'product' => $product,
                                    'isHero' => false,
                                ])
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-8 md:mt-10">
                        {{ $products->links('frontend.parts.components.pagination') }}
                    </div>
                @else
                    <div class="bg-white text-center text-gray-400 text-sm">
                        Bu kategoride henüz ürün bulunmuyor.
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
