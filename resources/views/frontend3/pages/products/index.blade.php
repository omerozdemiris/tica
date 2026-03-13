@extends($template . '.layouts.app')

@section('title', 'Ürünler')

@section('breadcrumb_title', 'Ürünler')

@section('content')

    @include($template . '.parts.breadcrumb')

    @php
        $products = $data->products ?? collect();
        $filters = $data->filters ?? (object) [];
    @endphp

    <div class="border-b {{ $theme->color ? 'border-' . $theme->color . '/10' : 'border-gray-100' }} bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <form action="{{ route('products.index') }}" method="GET"
                class="flex flex-col md:flex-row md:items-stretch gap-4">
                <div
                    class="flex-1 flex items-stretch border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }} overflow-hidden rounded-full bg-white">
                    <input type="text" name="q" value="{{ $filters->q ?? '' }}" placeholder="Ürün ara..."
                        class="flex-1 px-6 py-3 outline-none text-xs font-bold tracking-widest rounded-full text-gray-900">
                    <button type="submit"
                        class="px-8 {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white hover:bg-opacity-90 transition flex items-center justify-center">
                        <i class="ri-search-line text-lg"></i>
                    </button>
                </div>

                <div
                    class="w-full md:w-72 border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }} rounded-full bg-white">
                    <select name="category" onchange="this.form.submit()"
                        class="w-full h-full px-6 py-3 text-xs font-bold tracking-widest rounded-full bg-transparent outline-none cursor-pointer appearance-none text-gray-700">
                        <option value="">Tüm Koleksiyonlar</option>
                        @foreach ($data->categories ?? collect() as $category)
                            <option value="{{ $category->id }}" @selected($filters->category == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <section class="py-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            @if ($products->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-x-4 gap-y-12">
                    @foreach ($products as $product)
                        @php
                            $slug = \Illuminate\Support\Str::slug($product->title ?? 'urun');
                        @endphp
                        @include($template . '.parts.components.product.card', compact('product', 'slug'))
                    @endforeach
                </div>

                <div class="mt-16">
                    {{ $products->links($template . '.parts.components.pagination') }}
                </div>
            @else
                <div
                    class="bg-white border {{ $theme->color ? 'border-' . $theme->color . '/20' : 'border-gray-200' }} p-20 text-center">
                    <i class="ri-search-2-line text-4xl text-gray-200 mb-4 block"></i>
                    <p class="text-sm font-bold tracking-widest text-gray-400">
                        ARADIĞINIZ KRİTERLERE UYGUN ÜRÜN BULUNAMADI.
                    </p>
                    <a href="{{ route('products.index') }}"
                        class="inline-block mt-6 px-8 py-3 {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-xs font-black uppercase tracking-widest hover:bg-opacity-90 transition">
                        TÜM ÜRÜNLERİ GÖR
                    </a>
                </div>
            @endif
        </div>
    </section>

@endsection
