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

    @include('frontend.parts.breadcrumb')

    @if ($descriptionPreview)
        <div class="bg-white border-b border-gray-100">

            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

                <p class="text-sm text-gray-500">{{ $descriptionPreview }}</p>

            </div>

        </div>
    @endif



    <section class="py-8">

        <div class="container mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col lg:flex-row gap-8">

                <aside class="lg:w-64 md:block hidden flex-shrink-0">

                    <div
                        class="bg-white rounded-xl border {{ $theme->color ? 'border-' . $theme->color . '/20' : 'border-gray-200' }} shadow-sm sticky top-24">

                        <div
                            class="p-4 border-b {{ $theme->color ? 'border-' . $theme->color . '/20' : 'border-gray-200' }}">

                            <h2 class="text-sm font-semibold text-gray-900">Kategoriler</h2>

                        </div>

                        <nav class="p-4">

                            <ul class="category-tree">

                                @foreach ($rootCategories as $rootCategory)
                                    @include('frontend.parts.category-tree-item', [
                                        'category' => $rootCategory,
                                    
                                        'currentCategoryId' => $category->id,
                                    
                                        'activeCategoryPathIds' => $activeCategoryPathIds,
                                    
                                        'level' => 0,
                                    ])
                                @endforeach

                            </ul>

                        </nav>

                    </div>

                </aside>



                <div class="flex-1">

                    @if ($products->count() > 0)

                        <div class="mb-6">

                            <h1 class="text-2xl font-bold text-gray-900">

                                {{ $category->name }}</h1>

                            <p class="text-sm {{ $theme->color ? 'text-' . $theme->color : 'text-gray-500' }} mt-1">

                                {{ $products->total() }} ürün bulundu</p>

                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                            @foreach ($products as $product)
                                @php
                                    $slug = \Illuminate\Support\Str::slug($product->title ?? 'urun');

                                @endphp
                                @include('frontend.parts.components.product.card', ['product' => $product])
                            @endforeach
                        </div>
                        <div class="mt-8">
                            {{ $products->links() }}
                        </div>
                    @else
                        <div class="bg-white border border-gray-200 rounded-2xl p-10 text-center text-gray-500">

                            Bu kategoride henüz ürün bulunmuyor.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
