@extends('frontend.layouts.app')

@section('title', 'Ürünler')

@section('breadcrumb_title', 'Ürünler')

@section('content')

@include('frontend.parts.breadcrumb')

@php

$products = $data->products ?? collect();

$filters = $data->filters ?? (object) [];

@endphp



<div class="">

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <form action="{{ route('products.index') }}" method="GET"

            class="flex flex-col md:flex-row md:items-center gap-3">

            <div class="flex items-center border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-full overflow-hidden w-full md:w-auto">

                <input type="text" name="q" value="{{ $filters->q ?? '' }}" placeholder="Ürün ara..."

                    class="px-4 py-2 outline-none text-sm w-full md:w-64">

                <button type="submit" class="px-4 py-2 {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-sm hover:{{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-700' }} transition"><i

                        class="ri-search-line"></i></button>

            </div>

            <select name="category"

                class="px-4 py-2 text-sm border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-full bg-white focus:ring-0 w-full md:w-auto md:min-w-[200px]">

                <option value="">Tüm Kategoriler</option>

                @foreach ($data->categories ?? collect() as $category)

                <option value="{{ $category->id }}" @selected($filters->category == $category->id)>

                    {{ $category->name }}
                </option>

                @endforeach

            </select>

        </form>

    </div>

</div>

<section class="py-12">

    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        @if ($products->count() > 0)

        <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-5 gap-6">

            @foreach ($products as $product)

            @php

            $slug = \Illuminate\Support\Str::slug($product->title ?? 'urun');

            @endphp

            @include('frontend.parts.components.product.card', compact('product', 'slug'))

            @endforeach

        </div>

        <div class="mt-8">

            {{ $products->links('frontend.parts.components.pagination') }}

        </div>

        @else

        <div class="bg-white border border-gray-200 rounded-2xl p-10 text-center text-gray-500">

            Aradığınız kriterlere uygun ürün bulunamadı.

        </div>

        @endif

    </div>

</section>

@endsection