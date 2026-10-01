@php
    $guestCartId = session('guest_cart_id');
    if (!$guestCartId && !auth()->check()) {
        $guestCartId = session()->getId();
        session(['guest_cart_id' => $guestCartId]);
    }
    $headerCartSummary = \App\Models\Cart::headerSummary(auth()->id(), $guestCartId);
@endphp
<header class="bg-white sticky top-0 z-40 border-b border-gray-200" x-data="{ searchOpen: false }">
    <div class="bg-white">
        <div
            class="max-w-[1680px] mx-auto px-1 md:px-2 md:py-0.5 py-0.5 grid grid-cols-[1fr_auto_1fr] items-start text-[10px] uppercase  text-gray-700">
            <div class="flex items-start gap-4">
                <button type="button" data-mobile-menu-trigger
                    class="inline-flex md:hidden items-center justify-center w-9 h-9 border border-gray-200 text-gray-600 hover:text-gray-900 hover:border-gray-300">
                    <i class="ri-menu-line text-base"></i>
                </button>
                <div class="hidden md:flex items-center gap-3 pt-1 text-sm text-gray-500">
                    <a href="{{ $settings->instagram }}" class="hover:text-gray-900 transition" aria-label="Instagram">
                        <i class="ri-instagram-line"></i>
                    </a>
                    <a href="{{ $settings->facebook }}" class="hover:text-gray-900 transition" aria-label="Facebook">
                        <i class="ri-facebook-line"></i>
                    </a>
                    <a href="{{ $settings->twitter }}" class="hover:text-gray-900 transition" aria-label="Twitter">
                        <i class="ri-twitter-x-line"></i>
                    </a>
                    <a href="{{ $settings->youtube }}" class="hover:text-gray-900 transition" aria-label="YouTube">
                        <i class="ri-youtube-line"></i>
                    </a>
                    <a href="{{ $settings->linkedin }}" class="hover:text-gray-900 transition" aria-label="LinkedIn">
                        <i class="ri-linkedin-line"></i>
                    </a>
                </div>
            </div>
            <div class="flex justify-center">
                <a href="{{ route('home') }}" class="inline-flex items-start md:hidden">
                    @if (!empty($settings?->logo))
                        <img src="{{ $settings->logo }}" alt="{{ $settings->title ?? config('app.name') }}"
                            class="h-9 w-auto object-contain">
                    @else
                        <span class="text-lg font-bold">{{ $settings->title ?? config('app.name') }}</span>
                    @endif
                </a>
            </div>
            <div class="relative flex items-center justify-end gap-4">
                <button type="button" @click="searchOpen = !searchOpen"
                    class="relative hidden md:inline-flex items-center gap-1 hover:text-black transition text-xs after:content-[''] after:absolute after:left-0 after:bottom-0 after:h-px after:w-full after:bg-current after:origin-right after:scale-x-0 after:transition-transform after:duration-200 hover:after:origin-left hover:after:scale-x-100 hover:after:duration-300">
                    <i class="ri-search-line"></i> Ara
                </button>
                <a href="{{ route('returns.lookup') }}"
                    class="relative hidden md:inline-flex items-center gap-1 hover:text-black transition text-xs after:content-[''] after:absolute after:left-0 after:bottom-0 after:h-px after:w-full after:bg-current after:origin-right after:scale-x-0 after:transition-transform after:duration-200 hover:after:origin-left hover:after:scale-x-100 hover:after:duration-300">
                    <i class="ri-question-line text-sm"></i> Yardım
                </a>
                @guest
                    <a href="{{ route('login') }}"
                        class="relative hidden md:inline-flex items-center gap-1 hover:text-black transition text-xs after:content-[''] after:absolute after:left-0 after:bottom-0 after:h-px after:w-full after:bg-current after:origin-right after:scale-x-0 after:transition-transform after:duration-200 hover:after:origin-left hover:after:scale-x-100 hover:after:duration-300">
                        <i class="ri-user-line text-sm"></i> Hesabım
                    </a>
                @else
                    <a href="{{ route('user.dashboard') }}"
                        class="relative hidden md:inline-flex items-center gap-1 hover:text-black transition text-xs after:content-[''] after:absolute after:left-0 after:bottom-0 after:h-px after:w-full after:bg-current after:origin-right after:scale-x-0 after:transition-transform after:duration-200 hover:after:origin-left hover:after:scale-x-100 hover:after:duration-300">
                        <i class="ri-user-line text-sm"></i> Hesabım
                    </a>
                @endguest
                <a href="{{ route('cart.index') }}"
                    class="relative hidden md:inline-flex items-center gap-1 hover:text-black transition text-xs after:content-[''] after:absolute after:left-0 after:bottom-0 after:h-px after:w-full after:bg-current after:origin-right after:scale-x-0 after:transition-transform after:duration-200 hover:after:origin-left hover:after:scale-x-100 hover:after:duration-300">
                    <i class="ri-shopping-bag-line text-sm"></i>
                    <span>{{ $headerCartSummary->count ?? 0 }}</span>
                </a>
                <div x-show="searchOpen" x-transition x-cloak
                    class="absolute right-0 top-full mt-2 w-full max-w-[320px]">
                    <form action="{{ route('products.index') }}" method="GET"
                        class="flex items-stretch border border-gray-200 w-full bg-white shadow-sm">
                        <input type="text" name="q" placeholder="Ürün ara..." value="{{ request('q') }}"
                            class="flex-1 px-3 py-1 text-xs border-0 focus:ring-0 focus:outline-none">
                        <button type="submit"
                            class="px-4 py-2 text-xs font-medium {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white transition flex items-center justify-center">
                            <i class="ri-search-line"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="max-w-[1680px] mx-auto">
        <div class="w-full py-0.5 md:py-0.5">
            @php
                $categoryItems = collect($menuCategories)->values();
                $categorySplitIndex = (int) ceil($categoryItems->count() / 3);
                $leftCategories = $categoryItems->slice(0, $categorySplitIndex);
                $rightCategories = $categoryItems->slice($categorySplitIndex);
            @endphp
            <div class="hidden md:grid grid-cols-[1fr_auto_1fr] items-end gap-4 pt-1">
                <nav class="flex items-end justify-end flex-wrap gap-1 text-[11px] font-medium uppercase ">
                    <div class="relative group px-0.5 py-0.5">
                        <a href="{{ route('home') }}"
                            class="relative inline-flex items-end gap-1 px-2.5 py-1.5 transition after:content-[''] after:absolute after:left-0 after:bottom-1 after:h-px after:w-full after:bg-current after:origin-right after:scale-x-0 after:transition-transform after:duration-200 hover:after:origin-left hover:after:scale-x-100 hover:after:duration-300">
                            <span>Anasayfa</span>
                        </a>
                    </div>
                    @foreach ($leftCategories as $category)
                        <div class="relative group px-0.5 py-0.5">
                            <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                                class="relative inline-flex items-end gap-1 px-2.5 py-1.5 transition after:content-[''] after:absolute after:left-0 after:bottom-1 after:h-px after:w-full after:bg-current after:origin-right after:scale-x-0 after:transition-transform after:duration-200 hover:after:origin-left hover:after:scale-x-100 hover:after:duration-300">
                                <span>{{ $category->name }}</span>
                                @if ($category->children->isNotEmpty())
                                    <i class="ri-arrow-down-s-line text-xs"></i>
                                @endif
                            </a>
                            @if ($category->children->isNotEmpty())
                                <div
                                    class="absolute left-0 z-40 top-full w-56 bg-white border {{ $theme->color ? 'border-' . $theme->color . '/20' : 'border-gray-100/20' }} shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition">
                                    <div class="py-2">
                                        @foreach ($category->children as $child)
                                            <div class="relative group/child">
                                                <a href="{{ route('categories.show', [$child->id, $child->slug]) }}"
                                                    class="flex items-center justify-between px-2.5 py-1.5 text-[11px] text-gray-600 hover:bg-gray-50 hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} transition">
                                                    <span>{{ $child->name }}</span>
                                                    @if ($child->children && $child->children->isNotEmpty())
                                                        <i class="ri-arrow-right-s-line text-xs"></i>
                                                    @endif
                                                </a>
                                                @if ($child->children && $child->children->isNotEmpty())
                                                    <div
                                                        class="absolute top-0 left-full w-52 bg-white shadow-lg border {{ $theme->color ? 'border-' . $theme->color . '/20' : 'border-gray-100/20' }} opacity-0 pointer-events-none group-hover/child:opacity-100 group-hover/child:pointer-events-auto transition z-40">
                                                        <div class="py-2 text-[11px] text-gray-700">
                                                            @foreach ($child->children as $grandchild)
                                                                <a href="{{ route('categories.show', [$grandchild->id, $grandchild->slug]) }}"
                                                                    class="block px-2.5 py-1.5 hover:bg-gray-50 hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} transition">
                                                                    {{ $grandchild->name }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </nav>
                <div class="flex items-end justify-center -mt-9 relative z-10">
                    <a href="{{ route('home') }}" class="inline-flex items-end px-3">
                        @if (!empty($settings?->logo))
                            <img src="{{ $settings->logo }}" alt="{{ $settings->title ?? config('app.name') }}"
                                class="h-16 w-auto object-contain">
                        @else
                            <span class="text-lg font-bold">{{ $settings->title ?? config('app.name') }}</span>
                        @endif
                    </a>
                </div>
                <nav class="flex items-end justify-start flex-wrap gap-1 text-[11px] font-medium uppercase ">
                    @foreach ($rightCategories as $category)
                        <div class="relative group px-0.5 py-0.5">
                            <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                                class="relative inline-flex items-end gap-1 px-2.5 py-1.5 transition after:content-[''] after:absolute after:left-0 after:bottom-0 after:h-px after:w-full after:bg-current after:origin-right after:scale-x-0 after:transition-transform after:duration-200 hover:after:origin-left hover:after:scale-x-100 hover:after:duration-300">
                                <span>{{ $category->name }}</span>
                                @if ($category->children->isNotEmpty())
                                    <i class="ri-arrow-down-s-line text-xs"></i>
                                @endif
                            </a>
                            @if ($category->children->isNotEmpty())
                                <div
                                    class="absolute right-0 z-40 top-full w-56 bg-white border {{ $theme->color ? 'border-' . $theme->color . '/20' : 'border-gray-100/20' }} shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition">
                                    <div class="py-2">
                                        @foreach ($category->children as $child)
                                            <div class="relative group/child">
                                                <a href="{{ route('categories.show', [$child->id, $child->slug]) }}"
                                                    class="flex items-center justify-between px-2.5 py-1.5 text-[11px] text-gray-600 hover:bg-gray-50 hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} transition">
                                                    <span>{{ $child->name }}</span>
                                                    @if ($child->children && $child->children->isNotEmpty())
                                                        <i class="ri-arrow-left-s-line text-xs"></i>
                                                    @endif
                                                </a>
                                                @if ($child->children && $child->children->isNotEmpty())
                                                    <div
                                                        class="absolute top-0 right-full w-52 bg-white shadow-lg border {{ $theme->color ? 'border-' . $theme->color . '/20' : 'border-gray-100/20' }} opacity-0 pointer-events-none group-hover/child:opacity-100 group-hover/child:pointer-events-auto transition z-40">
                                                        <div class="py-2 text-[11px] text-gray-700">
                                                            @foreach ($child->children as $grandchild)
                                                                <a href="{{ route('categories.show', [$grandchild->id, $grandchild->slug]) }}"
                                                                    class="block px-2.5 py-1.5 hover:bg-gray-50 hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} transition">
                                                                    {{ $grandchild->name }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </nav>
            </div>
        </div>
    </div>
</header>