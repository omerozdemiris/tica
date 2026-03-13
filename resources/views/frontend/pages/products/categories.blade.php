@extends('frontend.layouts.app')

@section('title', 'Kategoriler')

@section('breadcrumb_title', 'Kategoriler')

@section('content')

    @include('frontend.parts.breadcrumb')

    @php

        $categories = $data->categories ?? collect();

    @endphp

    <section class="py-12">

        <div class="container mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                @foreach ($categories as $category)
                    @php
                        $categoryId = $category->app_category_id ?? $category->id;
                        $productsCount = null;
                        if (is_callable([$category, 'products_count'])) {
                            $productsCount = $category->products_count();
                        } elseif (isset($category->products_count)) {
                            $productsCount = $category->products_count;
                        }

                        if (!empty($categoryId)) {
                            $url = route('categories.show', [$categoryId, $category->slug]);
                        } else {
                            // Local kategori yoksa, ERP kategorisine tıklayınca ürün arama sayfasına yönlendir.
                            $url = route('products.index', ['q' => $category->name]);
                        }
                    @endphp

                    <a href="{{ $url }}"
                        class="block {{ $theme->color ? 'bg-' . $theme->color . '/5' : 'bg-white' }} border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-2xl p-6 shadow-sm hover:shadow-lg transition">

                        <div class="flex items-center justify-between">

                            <h2
                                class="text-lg font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">
                                {{ $category->name }}</h2>

                            @if ($productsCount)
                                <span class="text-xs px-3 py-1 bg-blue-50 text-blue-600 rounded-full">
                                    {{ $productsCount ?? '0' }} ürün
                                </span>
                            @endif

                        </div>

                        @if ($category->description)
                            <p
                                class="text-sm {{ $theme->color ? 'text-' . $theme->color : 'text-gray-500' }} mt-3 line-clamp-3">

                                {{ Str::limit(strip_tags($category->description), 140) }}

                            </p>
                        @endif

                    </a>
                @endforeach

            </div>

        </div>

    </section>

@endsection
