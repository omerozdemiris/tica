@php
    /**
     * Blade içinde:
     * - Her kategori için aktif ürün sayısını hesapla
     * - En çok ürünü olanı başa al
     * - $loop->first ile aktif tab yap
     */
    $categories = $categories
        ->map(function ($category) {
            $category->active_count = $category->products
                ->where('is_active', true)
                ->count();

            return $category;
        })
        ->sortByDesc('active_count')
        ->values(); // index reset (çok önemli)
@endphp

<section class="container mx-auto px-4 sm:px-6 lg:px-8 py-16" data-category-tabs>
    <div
        class="bg-white rounded-3xl shadow-sm border {{ $theme->color ? 'border-' . $theme->color . '/10' : 'border-gray-100' }} p-6 lg:p-10">
        <div class="flex flex-col gap-10">

            {{-- TABS --}}
            <div class="flex flex-wrap gap-3" role="tablist">
                @foreach ($categories as $category)
                    @php
                        $tabId = 'category-tab-' . $category->id;
                        $isActive = $loop->first; // 🔥 en çok ürünlü kategori
                    @endphp

                    <button type="button"
                        class="category-tab inline-flex items-center gap-2 rounded-full border {{ $theme->color ? 'border-' . $theme->color . '/10' : 'border-gray-100' }} px-5 py-2.5 text-xs md:text-sm font-semibold transition-all focus:outline-none focus-visible:ring focus-visible:ring-offset-2"
                        role="tab"
                        aria-selected="{{ $isActive ? 'true' : 'false' }}"
                        data-category-tab-button
                        data-tab-target="{{ $tabId }}"
                        data-active-class="{{ $theme->color ? 'bg-' . $theme->color . '/5' : 'bg-gray-900' }} {{ $theme->color ? 'text-' . $theme->color : 'text-white' }} {{ $theme->color ? 'border-' . $theme->color . '/50' : 'border-gray-900' }} transition-all"
                        data-inactive-class="bg-white text-gray-700 {{ $theme->color ? 'border-' . $theme->color . '/10' : 'border-gray-200' }} transition-all">

                        <span>{{ $category->name }}</span>

                        <span
                            class="inline-flex items-center justify-center rounded-full {{ $theme->color ? 'bg-' . $theme->color . '/90' : 'bg-gray-100' }} px-2.5 py-0.5 text-xs font-bold text-white transition-all">
                            {{ $category->active_count }}
                        </span>
                    </button>
                @endforeach
            </div>

            {{-- TAB CONTENT --}}
            <div>
                @foreach ($categories as $category)
                    @php
                        $tabId = 'category-tab-' . $category->id;
                        $isActive = $loop->first;
                        $previewProducts = ($category->previewProducts ?? collect())->take(5);
                    @endphp

                    <div id="{{ $tabId }}"
                        class="category-tab-panel transition-all {{ $isActive ? '' : 'hidden' }} animate-fade"
                        role="tabpanel"
                        data-category-tab-panel>

                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 class="text-2xl font-semibold text-gray-900">
                                    {{ $category->name }}
                                </h3>

                                @if ($category->description)
                                    <p
                                        class="text-sm {{ $theme->color ? 'text-' . $theme->color . '/60' : 'text-gray-500' }} mt-2">
                                        {{ Str::limit(strip_tags($category->description), 140) }}
                                    </p>
                                @endif
                            </div>

                            <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                                class="inline-flex items-center justify-center text-center gap-2 rounded-full border hover:{{ $theme->color ? 'bg-' . $theme->color . '/90' : 'bg-gray-100' }} hover:text-white {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} px-5 py-2 text-sm font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }} transition-all">
                                <span>Tümünü gör</span>
                            </a>
                        </div>

                        <div class="mt-8 grid grid-cols-2 gap-2 md:gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                            @forelse ($previewProducts as $product)
                                @php
                                    $slug = \Illuminate\Support\Str::slug($product->title ?? 'urun');
                                @endphp

                                @include(
                                    'frontend.parts.components.product.card',
                                    compact('product', 'slug')
                                )
                            @empty
                                <div class="col-span-full text-sm text-gray-500">
                                    Bu kategoride henüz ürün bulunmuyor.
                                </div>
                            @endforelse
                        </div>

                    </div>
                @endforeach
            </div>

        </div>
    </div>
</section>
