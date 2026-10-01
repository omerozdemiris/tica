@extends('frontend.layouts.app')
@section('title', 'Ürünler')
@section('breadcrumb_title', 'Ürünler')
@section('content')
    @include('frontend.parts.breadcrumb')
    @php
        $products = $data->products ?? collect();
    @endphp
    <section class="py-4">
        <div class="max-w-[1680px] mx-auto">
            @if ($products->count() > 0)
                <div class="grid grid-cols-2 lg:grid-cols-12 gap-[1px] md:gap-8">
                    @foreach ($products as $productIndex => $product)
                        @php
                            $slug = \Illuminate\Support\Str::slug($product->title ?? 'urun');
                        @endphp
                        <div class="col-span-1 lg:col-span-4">
                            @include('frontend.parts.components.product.card', [
                                'product' => $product,
                                'slug' => $slug,
                                'isHero' => false,
                            ])
                            </div>
                    @endforeach
                    </div>
                <div class="mt-8">
                    {{ $products->links('frontend.parts.components.pagination') }}
                </div>
            @else
                <div class="bg-white text-center text-gray-400">
                    Aradığınız kriterlere uygun ürün bulunamadı.
                    </div>
            @endif
            </div>
        </section>
@endsection
