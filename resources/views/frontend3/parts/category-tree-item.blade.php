@php

    $isActive = $category->id === $currentCategoryId;

    $isInActivePath = in_array($category->id, $activeCategoryPathIds ?? []);

    $hasChildren = $category->children->isNotEmpty();

    $shouldShowChildren = $isInActivePath && $hasChildren;

@endphp

<li class="category-tree__item md:block hidden" style="--category-level: {{ $level }}">

    <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
        class="category-tree__link border border-{{ $theme->color ? $theme->color . '/30' : 'gray-200' }} hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }} transition {{ $isActive ? 'is-active' : ($isInActivePath ? 'is-path' : '') }} {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">

        <span class="category-tree__icon {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">

            <i class="{{ $hasChildren ? 'ri-grid-line' : 'ri-price-tag-3-line' }}"></i>

        </span>

        <span class="category-tree__label">

            <span
                class="category-tree__name text-gray-900">{{ $category->name }}</span>

            @if ($category->products_count() ?? false)
                <span
                    class="category-tree__badge text-gray-900">{{ $category->products_count() ?? '0' }}</span>
            @endif

        </span>

        @if ($hasChildren)
            <span
                class="category-tree__chevron {{ $shouldShowChildren ? 'is-open' : '' }} {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">

                <i class="ri-arrow-right-up-line"></i>

            </span>
        @endif

    </a>

    @if ($hasChildren)

        <ul
            class="category-tree__children {{ $shouldShowChildren ? 'is-visible' : '' }} text-gray-900">

            @foreach ($category->children as $child)
                @include('frontend.parts.category-tree-item', [
                    'category' => $child,
                
                    'currentCategoryId' => $currentCategoryId,
                
                    'activeCategoryPathIds' => $activeCategoryPathIds,
                
                    'level' => $level + 1,
                ])
            @endforeach

        </ul>

    @endif

</li>
