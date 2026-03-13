@foreach ($sections as $section)

    @if ($section->name === 'slider')

        @include($template . '.parts.home.slider')
    @elseif ($section->name === 'new_products')
        @include($template . '.parts.home.products.new_products')
    @elseif ($section->name === 'all_categories')
        @include($template . '.parts.home.products.all_categories')
    @else
        @php

            $type = $section->data['type'] ?? 'products';

            $items = $section->items ?? collect();

        @endphp



        @if ($items->count())

            <section class="py-10">

                <div class="container mx-auto px-4 sm:px-6 lg:px-8">

                    <div class="flex items-center justify-between mb-10">

                        <div>

                            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">

                                {{ $section->title }}

                            </h2>

                            @if ($section->description)
                                <p class="md:text-sm text-xs text-gray-500 mt-2">

                                    {{ $section->description }}

                                </p>
                            @endif

                        </div>

                    </div>

                    @if ($type === 'products' || $type === 'showcase')
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-2 md:gap-4">

                            @foreach ($items as $product)
                                @php $slug = Str::slug($product->title); @endphp

                                @include($template . '.parts.components.product.card', [
                                    'product' => $product,
                                
                                    'slug' => $slug,
                                ])
                            @endforeach

                        </div>
                    @elseif ($type === 'categories')
                        @php

                            $allHavePhotos = $items->every(fn($item) => !empty($item->photo));

                            $count = $items->count();

                            $colSpan = match ($count) {
                                2 => 'lg:col-span-6 md:col-span-6',

                                3 => 'lg:col-span-4 md:col-span-4',

                                4 => 'lg:col-span-3 md:col-span-4',

                                default => 'lg:col-span-3 md:col-span-4',
                            };

                        @endphp



                        @if ($allHavePhotos)
                            <div class="hidden md:grid grid-cols-12 gap-8">

                                @foreach ($items as $category)
                                    <div
                                        class="group relative aspect-[2/1] overflow-hidden bg-white rounded-[2rem] shadow-sm {{ $colSpan }}">

                                        @if ($category->photo)
                                            <img src="{{ asset($category->photo) }}" alt="{{ $category->name }}"
                                                class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105">
                                        @endif

                                        <div
                                            class="absolute inset-0 flex flex-col justify-center p-12 bg-gradient-to-r from-white/20 to-transparent">

                                            <div class="max-w-[60%]">

                                                <h3
                                                    class="text-lg md:text-2xl font-bold text-gray-50 mb-4 leading-tight">

                                                    {{ $category->name }}

                                                </h3>

                                                @if ($category->description)
                                                    <p
                                                        class="text-base md:text-sm text-gray-200 mb-8 font-medium leading-relaxed">

                                                        {{ Str::limit(strip_tags($category->description), 250) }}

                                                    </p>
                                                @endif



                                                <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                                                    class="hover:bg-{{ $theme->color ? $theme->color : 'bg-blue-600' }} hover:text-white inline-block bg-white text-gray-900 px-10 py-4 rounded-full text-[13px] font-bold shadow-xl transition-all uppercase tracking-wide">

                                                    Alışverişe Başla

                                                </a>

                                            </div>

                                        </div>

                                    </div>
                                @endforeach

                            </div>

                            <div class="md:hidden swiper categorySwiper">

                                <div class="swiper-wrapper">

                                    @foreach ($items as $category)
                                        <div class="swiper-slide px-4">

                                            <div
                                                class="group relative md:aspect-[2/5] aspect-[4/5] overflow-hidden bg-white rounded-[2rem] shadow-sm">

                                                @if ($category->photo)
                                                    <img src="{{ asset($category->photo) }}"
                                                        alt="{{ $category->name }}" class="w-full h-full object-cover">
                                                @endif

                                                <div
                                                    class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent flex flex-col justify-end p-8">

                                                    <h3
                                                        class="text-lg font-bold text-gray-50 mb-3 leading-tight absolute top-12">

                                                        {{ $category->name }}

                                                    </h3>

                                                    @if ($category->description)
                                                        <p
                                                            class="text-sm text-gray-200 mb-6 font-medium line-clamp-2 absolute top-24">

                                                            {{ strip_tags($category->description) }}

                                                        </p>
                                                    @endif

                                                    <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                                                        class="inline-block bg-white text-gray-900 px-8 py-3.5 rounded-full text-xs font-bold text-center shadow-lg border border-gray-100">

                                                        Alışverişe Başla

                                                    </a>

                                                </div>

                                            </div>

                                        </div>
                                    @endforeach

                                </div>

                            </div>
                        @else
                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">

                                @foreach ($items as $category)
                                    <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                                        class="group block p-4 border border-gray-100 transition-all bg-white text-center">

                                        <div
                                            class="w-16 h-16 mx-auto mb-3 bg-gray-50 flex items-center justify-center text-gray-400 transition-all">

                                            <i class="ri-instance-line text-2xl text-gray-400"></i>

                                        </div>

                                        <h3 class="text-sm font-semibold truncate">{{ $category->name }}</h3>

                                    </a>
                                @endforeach

                            </div>
                        @endif
                    @elseif ($type === 'blogs')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">

                            @foreach ($items as $blog)
                                <a href="{{ route('blog.show', [$blog->id, $blog->slug]) }}" class="group block">

                                    <div
                                        class="aspect-[4/3] rounded-[2rem] overflow-hidden bg-gray-100 dark:bg-gray-900 mb-6">

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
                                            <p
                                                class="text-sm text-gray-500 dark:text-gray-900 line-clamp-2 leading-relaxed">

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



@push('scripts')
    <script>
        $(document).ready(function() {

            if ($('.categorySwiper').length > 0) {

                new Swiper('.categorySwiper', {

                    slidesPerView: 1.2,

                    spaceBetween: 10,

                    centeredSlides: false,

                    loop: false,

                });

            }

        });
    </script>
@endpush
