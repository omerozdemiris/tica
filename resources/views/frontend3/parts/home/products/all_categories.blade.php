<section class="py-8 bg-white" data-category-tabs>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-12">
            {{-- Tab List - Rounded Pills Style --}}
            <div class="flex flex-wrap items-center gap-3" role="tablist">
                @foreach ($categories->take(4) as $category)
                    @php $isActive = $loop->first; @endphp
                    <button type="button"
                        class="category-tab px-10 py-3.5 text-[14px] font-bold rounded-full border-2 transition-all outline-none"
                        role="tab" aria-selected="{{ $isActive ? 'true' : 'false' }}" data-category-tab-button
                        data-tab-target="category-tab-{{ $category->id }}"
                        data-active-class="bg-{{ $theme->color ? $theme->color : 'bg-blue-600' }} border-{{ $theme->color ? $theme->color : 'bg-blue-600' }} text-white"
                        data-inactive-class="bg-white border-gray-100 text-black hover:border-gray-300">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>

            {{-- Tab Panels --}}
            <div class="mt-8">
                @foreach ($categories->take(4) as $category)
                    @php
                        $isActive = $loop->first;
                        $previewProducts = ($category->previewProducts ?? collect())->take(4);
                    @endphp
                    <div id="category-tab-{{ $category->id }}"
                        class="category-tab-panel transition-all {{ $isActive ? '' : 'hidden' }} animate-fade"
                        role="tabpanel" data-category-tab-panel>

                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-6">
                            @forelse ($previewProducts as $product)
                                @php $slug = \Illuminate\Support\Str::slug($product->title ?? 'urun'); @endphp
                                @include(
                                    $template . '.parts.components.product.card',
                                    compact('product', 'slug'))
                            @empty
                                <div
                                    class="col-span-full py-20 border-2 border-dashed border-gray-100 rounded-3xl text-center font-bold text-gray-300">
                                    BU KOLEKSİYONDA ÜRÜN BULUNMUYOR.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
