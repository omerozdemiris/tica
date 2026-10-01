@php
    $breadcrumbTitle = trim($__env->yieldContent('breadcrumb_title'));
    $breadcrumbTitle = html_entity_decode($breadcrumbTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $breadcrumbs = $breadcrumbs ?? [];
    $currentCategory = $currentCategory ?? null;
    $childCategories = $childCategories ?? collect();
    $hideDeepBreadcrumbOnMobile = request()->routeIs('products.show') || request()->routeIs('categories.show');
@endphp
@if ($breadcrumbTitle)
    <div class="bg-white">
        <div class="max-w-[1680px] mx-auto px-3 md:px-1 py-1 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 text-[10px] uppercase  text-gray-500">
                <button type="button" onclick="history.back()" class="inline-flex items-center gap-1 hover:text-gray-900">
                    <i class="ri-arrow-left-line text-base"></i>
                    Geri
                </button>
                <span class="text-gray-300">/</span>
                <a href="{{ route('home') }}" class="hover:text-gray-900">Anasayfa</a>
                @foreach ($breadcrumbs as $crumb)
                    @php
                        $crumbLabel = html_entity_decode($crumb['label'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    @endphp
                    <span class="text-gray-300 {{ $hideDeepBreadcrumbOnMobile ? 'hidden md:inline' : '' }}">/</span>
                    <a href="{{ $crumb['url'] }}" class="hover:text-gray-900 {{ $hideDeepBreadcrumbOnMobile ? 'hidden md:inline' : '' }}">{{ $crumbLabel }}</a>
                @endforeach
                <span class="text-gray-300 {{ $hideDeepBreadcrumbOnMobile ? 'hidden md:inline' : '' }}">/</span>
                <span class="text-gray-900 {{ $hideDeepBreadcrumbOnMobile ? 'hidden md:inline' : '' }}">{{ $breadcrumbTitle }}</span>
            </div>
            <p class="hidden md:block text-[10px] uppercase  text-gray-500">
                {{ request()->routeIs('categories.show') && isset($data->products) ? $data->products->total() . ' ürün' : '' }}
            </p>
        </div>
    </div>
@endif

