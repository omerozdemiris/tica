@foreach ($sections as $section)
    @if ($section->name === 'slider')
        @include('frontend.parts.home.slider')
    @elseif ($section->name === 'new_products')
        @include('frontend.parts.home.products.new_products')
    @elseif ($section->name === 'all_categories')
        @include('frontend.parts.home.products.all_categories')
    @elseif ($section->name === 'video')
        @include('frontend.parts.home.video')
    @else
        @php
            $type = $section->data['type'] ?? 'products';
            $items = $section->items ?? collect();
        @endphp

        @if ($items->count())
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
                    @if ($type === 'products' || $type === 'showcase')
                        <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-5 gap-2 md:gap-4">
                            @foreach ($items as $product)
                                @php $slug = Str::slug($product->title); @endphp
                                @include('frontend.parts.components.product.card', [
                                    'product' => $product,
                                    'slug' => $slug,
                                ])
                            @endforeach
                        </div>
                    @elseif ($type === 'categories')
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                            @foreach ($items as $category)
                                <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                                    class="group block p-4 rounded-2xl border border-gray-100 transition-all bg-white text-center">
                                    <div
                                        class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 transition-all">
                                        <i class="ri-instance-line text-2xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-sm font-semibold truncate">{{ $category->name }}</h3>
                                </a>
                            @endforeach
                        </div>
                    @elseif ($type === 'blogs')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @foreach ($items as $blog)
                                <a href="{{ route('blog.show', [$blog->id, $blog->slug]) }}" class="group block">
                                    <div class="aspect-[4/3] rounded-xl overflow-hidden bg-gray-100 mb-4">
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
                                        <h3 class="text-lg font-bold text-gray-900 leading-tight group-hover:text-gray-600 transition-colors">
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
                    @endif
                </div>
            </section>
        @endif
    @endif
@endforeach
