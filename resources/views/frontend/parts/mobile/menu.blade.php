<div class="flex-1 px-3 md:hidden justify-center bg-white border-b border-gray-100 py-2">
    <form action="{{ route('products.index') }}" method="GET"
        class="flex items-stretch border border-gray-200 rounded-full w-full max-w-xl bg-white">
        <input type="text" name="q" placeholder="Ürün ara..." value="{{ request('q') }}"
            class="flex-1 px-4 py-2 text-sm rounded-l-full border-0 focus:ring-0 focus:outline-none">
        <button type="submit"
            class="px-4 py-2 text-sm font-medium {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white rounded-r-full hover:bg-gray-700 transition flex items-center justify-center">
            <i class="ri-search-line"></i>
        </button>
    </form>
</div>
<div class="md:hidden bg-white border-b border-gray-100 sticky top-0 z-50">
    <div class="flex items-center">
        <div class="flex-1 overflow-x-auto no-scrollbar flex items-center gap-2 px-3 py-4 whitespace-nowrap">
            @foreach ($rootCategories as $category)
                @php
                    $categoryUrl = route('categories.show', [$category->id, $category->slug]);
                @endphp
                <a href="{{ $categoryUrl }}"
                    class="text-sm font-medium rounded-2xl px-2 py-1 text-gray-600 hover:{{ $theme->color ? 'bg-' . $theme->color . '/30 hover:text-' . $theme->color : 'bg-gray-100 hover:text-gray-900' }} {{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} {{ $theme->color ? 'border border-' . $theme->color . '/30' : 'border-gray-100' }} {{ $theme->color ? 'hover:border-' . $theme->color . '/50' : 'hover:border-gray-200' }} transition-colors block">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </div>
</div>
<div data-mobile-menu-overlay
    class="fixed inset-0 bg-black/50 z-50 hidden transition-opacity duration-300 opacity-0 backdrop-blur-sm"></div>
<div data-mobile-menu-sidebar
    class="fixed top-0 left-0 h-full w-[85%] md:w-96 md:max-w-96 max-w-xs bg-white z-50 transform -translate-x-full transition-transform duration-300 shadow-2xl overflow-y-auto flex flex-col">
    <div class="p-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
        <a href="{{ route('home') }}">
            @if (!empty($settings?->logo))
                <img src="{{ $settings->logo }}" alt="{{ $settings->title ?? config('app.name') }}"
                    class="h-8 md:h-10 w-auto object-contain">
            @else
                <span class="text-xl font-bold">{{ $settings->title ?? config('app.name') }}</span>
            @endif
        </a>
        <button type="button" data-mobile-menu-close class="text-gray-400 hover:text-gray-600">
            <i class="ri-close-line text-2xl"></i>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto relative">
        <div id="mobile-menu-root" class="absolute inset-0 flex flex-col bg-white transition-transform duration-300">
            @if ($isMobile)
                @if ($header_campaign)
                    <div class="border-b border-gray-100">
                        @include('frontend.parts.campaings.header')
                    </div>
                @endif
            @endif
            <nav class="p-4 space-y-1">
                <div class="border-b- border-gray-100 last:border-0">
                    <a href="{{ route('home') }}"
                        class="block p-2 text-sm text-gray-600 font-bold hover:text-gray-900">
                        Ana Sayfa
                    </a>
                </div>
                @foreach ($rootCategories as $category)
                    @php
                        $hasChildren = $category->children && $category->children->isNotEmpty();
                        $panelId = 'root-' . $category->id;
                    @endphp
                    <div class="border-b border-gray-100 last:border-0">
                        @if ($hasChildren)
                            <button type="button" data-open-panel="{{ $panelId }}"
                                class="flex items-center justify-between w-full p-2 text-left group">
                                <span
                                    class="text-gray-700 font-medium text-sm group-hover:text-gray-900">{{ $category->name }}</span>
                                <i class="ri-arrow-right-s-line text-gray-400 text-lg"></i>
                            </button>
                        @else
                            @php
                                $categoryUrl = route('categories.show', [$category->id, $category->slug]);
                            @endphp
                            <a href="{{ $categoryUrl }}" class="block p-2 text-sm text-gray-600 hover:text-gray-900">
                                {{ $category->name }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </nav>
        </div>
        @foreach ($rootCategories as $category)
            @if ($category->children && $category->children->isNotEmpty())
                @include('frontend.parts.mobile.category-panel', [
                    'categories' => $category->children,
                    'panelId' => 'root-' . $category->id,
                    'title' => $category->name,
                    'parentCategory' => $category,
                ])
            @endif
        @endforeach
    </div>
    <div class="p-4 border-t border-gray-100 bg-gray-50 mt-auto">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Bizi Takip Edin</p>
        <div class="flex items-center gap-2 flex-wrap">
            @if (!empty($settings->instagram))
                <a href="{{ $settings->instagram }}" target="_blank" rel="noopener"
                    class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-[#E1306C] hover:border-[#E1306C] transition-colors shadow-sm">
                    <i class="ri-instagram-line text-xl"></i>
                </a>
            @endif
            @if (!empty($settings->twitter))
                <a href="{{ $settings->twitter }}" target="_blank" rel="noopener"
                    class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-[#1DA1F2] hover:border-[#1DA1F2] transition-colors shadow-sm">
                    <i class="ri-twitter-x-line text-xl"></i>
                </a>
            @endif
            @if (!empty($settings->facebook))
                <a href="{{ $settings->facebook }}" target="_blank" rel="noopener"
                    class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-[#1877F2] hover:border-[#1877F2] transition-colors shadow-sm">
                    <i class="ri-facebook-fill text-xl"></i>
                </a>
            @endif
            @if (!empty($settings->youtube))
                <a href="{{ $settings->youtube }}" target="_blank" rel="noopener"
                    class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-[#FF0000] hover:border-[#FF0000] transition-colors shadow-sm">
                    <i class="ri-youtube-fill text-xl"></i>
                </a>
            @endif
            @if (!empty($settings->linkedin))
                <a href="{{ $settings->linkedin }}" target="_blank" rel="noopener"
                    class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-[#0A66C2] hover:border-[#0A66C2] transition-colors shadow-sm">
                    <i class="ri-linkedin-fill text-xl"></i>
                </a>
            @endif
            @if (!empty($settings->whatsapp))
                <a href="https://wa.me/{{ $settings->whatsapp }}" target="_blank" rel="noopener"
                    class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-[#25D366] hover:border-[#25D366] transition-colors shadow-sm">
                    <i class="ri-whatsapp-line text-xl"></i>
                </a>
            @endif
        </div>
    </div>
</div>
