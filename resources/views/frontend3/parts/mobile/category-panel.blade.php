<div id="mobile-panel-{{ $panelId }}"
    class="mobile-menu-panel fixed inset-0 bg-white z-[60] transform translate-x-full transition-transform duration-300 flex flex-col h-full hidden">

    <div class="flex items-center gap-3 p-4 border-b border-gray-100 bg-gray-50">
        <button type="button" data-close-panel="{{ $panelId }}" class="p-1 -ml-1 text-gray-500 hover:text-gray-900">
            <i class="ri-arrow-left-line text-xl"></i>
        </button>
        <span class="font-semibold text-gray-900 truncate">{{ $title }}</span>
    </div>

    <div class="flex-1 overflow-y-auto bg-white">
        @if (isset($parentCategory) && $parentCategory)
            <a href="{{ route('categories.show', [$parentCategory->id, $parentCategory->slug]) }}"
                class="flex items-center justify-between px-4 py-3 border-b border-gray-50 text-sm font-bold {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }}">
                <span>{{ $parentCategory->name }} - Tümünü Gör</span>
                <i class="ri-arrow-right-line"></i>
            </a>
        @endif

        @foreach ($categories as $category)
            @php
                $hasChildren = $category->children && $category->children->isNotEmpty();
                $childPanelId = $panelId . '-' . $category->id;
            @endphp

            <div class="border-b border-gray-50 last:border-0">
                @if ($hasChildren)
                    <button type="button" data-open-panel="{{ $childPanelId }}"
                        class="flex items-center justify-between w-full px-4 py-3 text-left group hover:bg-gray-50 transition-colors">
                        <span
                            class="text-gray-700 font-medium text-sm group-hover:text-gray-900">{{ $category->name }}</span>
                        <div class="flex items-center gap-2">
                            @if ($category->products_count ?? false)
                                <span
                                    class="text-[10px] px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded-full">{{ $category->products_count }}</span>
                            @endif
                            <i class="ri-arrow-right-s-line text-gray-400 text-xl group-hover:text-gray-600"></i>
                        </div>
                    </button>
                @else
                    <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                        class="flex items-center justify-between w-full px-4 py-3 text-left group hover:bg-gray-50 transition-colors">
                        <span
                            class="text-gray-600 font-medium text-sm group-hover:text-gray-900">{{ $category->name }}</span>
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>

@foreach ($categories as $category)
    @if ($category->children && $category->children->isNotEmpty())
        @include('frontend2.parts.mobile.category-panel', [
            'categories' => $category->children,
            'panelId' => $panelId . '-' . $category->id,
            'title' => $category->name,
            'parentCategory' => $category,
        ])
    @endif
@endforeach
