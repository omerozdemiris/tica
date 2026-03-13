@php
    $guestCartId = session('guest_cart_id');
    if (!$guestCartId && !auth()->check()) {
        $guestCartId = session()->getId();
        session(['guest_cart_id' => $guestCartId]);
    }
    $headerCartSummary = \App\Models\Cart::headerSummary(auth()->id(), $guestCartId);
@endphp
<header class="bg-white shadow-sm sticky top-0 z-40">
    <div
        class="bg-white hidden md:block border-b {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-200' }}">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-10 text-xs text-gray-600">
            <div class="flex items-center gap-4">
                @if (!empty($settings?->phone))
                    <span class="inline-flex items-center gap-1 text-black/90">
                        <i class="ri-phone-line"></i>
                        <a href="tel:{{ $settings->phone }}" class="hover:text-gray-900">{{ $settings->phone }}</a>
                    </span>
                @endif
                @if (!empty($settings?->email))
                    <span class="inline-flex items-center gap-1 text-black/90">
                        <i class="ri-mail-line"></i>
                        <a href="mailto:{{ $settings->email }}" class="hover:text-gray-900">{{ $settings->email }}</a>
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('returns.lookup') }}"
                    class="inline-flex items-center gap-1 text-black/90 hover:text-gray-900 font-semibold">
                    <i class="ri-loop-left-line"></i>
                    Sipariş Sorgula
                </a>
                @auth
                    <p class="hover:text-gray-900 font-bold text-black/90">
                        <span><i class="ri-user-line mx-1"></i></span>
                        {{ auth()->user()->name }}
                    </p>
                    <form action="{{ route('logout') }}" method="POST" class="hidden md:block">
                        @csrf
                        <button type="submit" class="hover:text-gray-900 text-black/90">
                            Çıkış Yap
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="w-full py-2 md:py-2">
            <div class="flex items-center justify-between gap-3 md:gap-6 w-full">
                <div class="flex {{ $isMobile ? 'justify-between' : '' }} items-center gap-3 flex-shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 flex-shrink-0">
                        @if (!empty($settings?->logo))
                            <img src="{{ $settings->logo }}" alt="{{ $settings->title ?? config('app.name') }}"
                                class="md:h-10 h-8 w-auto object-contain">
                        @else
                            <span class="text-xl font-bold">{{ $settings->title ?? config('app.name') }}</span>
                        @endif
                    </a>
                </div>
                <button type="button" data-mobile-menu-trigger
                    class="inline-flex md:hidden items-center justify-center w-10 h-10 rounded-full border border-gray-200 text-gray-600 hover:text-gray-900 hover:border-gray-300">
                    <i class="ri-menu-line text-xl"></i>
                </button>
                <div class="flex-1 mx-3 hidden md:flex justify-center">
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
                <div class="hidden md:flex items-center gap-3 flex-shrink-0">
                    @guest
                        <a href="{{ route('login') }}" class="text-sm text-black/90 hover:text-gray-900">
                            Giriş Yap
                        </a>
                        <a href="{{ route('register') }}" class="text-sm text-black/90 hover:text-gray-900">
                            Üye Ol
                        </a>
                    @else
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open"
                                class="inline-flex relative items-center justify-center w-10 h-10 rounded-full {{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} border border-gray-200 hover:{{ $theme->color ? 'bg-' . $theme->color . '/10' : 'bg-gray-100' }} hover:{{ $theme->color ? 'border-' . $theme->color . '/50' : 'border-gray-200' }} transition-colors">
                                <i class="ri-notification-3-line text-lg"></i>
                                @if (auth()->user()->unreadNotifications()->count() > 0)
                                    <span
                                        class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full animate-pulse">
                                        {{ auth()->user()->unreadNotifications()->count() }}
                                    </span>
                                @endif
                            </button>
                            <div x-show="open" x-transition x-cloak
                                class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
                                <div class="p-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                                    <span class="text-sm font-semibold text-gray-900">Bildirimler</span>
                                    <a href="{{ route('user.notifications.index') }}"
                                        class="text-xs {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} hover:underline">Tümünü Gör</a>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    @forelse(auth()->user()->notifications()->limit(5)->get() as $notif)
                                        <a href="{{ route('user.notifications.show', $notif->id) }}"
                                            class="block p-3 border-b border-gray-50 hover:bg-gray-50 transition-colors {{ !$notif->read_at ? 'bg-blue-50/30' : '' }}">
                                            <div class="flex gap-3">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                                    <i class="ri-notification-line text-gray-600"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $notif->title }}</p>
                                                    <p class="text-xs text-gray-500 line-clamp-2 mt-1">{{ $notif->text }}</p>
                                                    <p class="text-[10px] text-gray-400 mt-1">
                                                        {{ $notif->created_at->translatedFormat('d F Y H:i') }}</p>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="p-8 text-center">
                                            <i class="ri-notification-off-line text-2xl text-gray-200"></i>
                                            <p class="text-xs text-gray-400 mt-2">Henüz bildiriminiz yok.</p>
                                        </div>
                                    @endforelse
                                </div>
                                @if (auth()->user()->notifications()->count() > 0)
                                    <div class="p-2 border-t border-gray-100 grid grid-cols-2 gap-2">
                                        <button data-mark-notifications-read data-url="{{ route('user.notifications.read-all') }}"
                                            class="text-[11px] text-gray-500 hover:text-gray-900 flex items-center justify-center gap-1 py-1 px-2 hover:bg-gray-50 rounded-lg transition-colors">
                                            <i class="ri-check-double-line"></i> Okundu Yap
                                        </button>
                                        <button data-clear-notifications data-url="{{ route('user.notifications.clear-all') }}"
                                            class="text-[11px] text-red-500 hover:text-red-700 flex items-center justify-center gap-1 py-1 px-2 hover:bg-red-50 rounded-lg transition-colors">
                                            <i class="ri-delete-bin-line"></i> Temizle
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('user.dashboard') }}"
                            class="inline-flex relative items-center gap-2 px-4 py-2 rounded-full {{ $theme->color ? 'text-' . $theme->color : '' }} border border-gray-200 hover:{{ $theme->color ? 'bg-' . $theme->color . '/10' : 'bg-gray-100' }} hover:{{ $theme->color ? 'border-' . $theme->color . '/50' : 'border-gray-200' }} transition-colors">
                            <i class="ri-user-line"></i>
                            Hesabım
                        </a>
                    @endguest
                    <a href="{{ route('cart.index') }}"
                        class="inline-flex relative items-center gap-2 px-4 py-2 rounded-full {{ $theme->color ? 'text-' . $theme->color : '' }} border border-gray-200 hover:{{ $theme->color ? 'bg-' . $theme->color . '/10' : 'bg-gray-100' }} hover:{{ $theme->color ? 'border-' . $theme->color . '/50' : 'border-gray-200' }} transition-colors">
                        <i class="ri-shopping-cart-line text-lg"></i>
                        <span class="text-sm font-medium">Sepet</span>
                        <span data-cart-count
                            class="absolute -top-2 -right-2 {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-xs w-6 h-6 flex items-center justify-center rounded-full">
                            {{ $headerCartSummary->count ?? 0 }}
                        </span>
                    </a>
                </div>
            </div>
            <div class="hidden md:flex items-stretch justify-between mt-6">
                <div class="relative group">
                    <button
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-gray-200 bg-gray-50 text-sm font-medium hover:bg-gray-100 hover:border-gray-300 transition">

                        <i class="ri-menu-fill text-base"></i>

                        <span>Tüm Kategoriler</span>

                        <i class="ri-arrow-down-s-line text-xs"></i>

                    </button>

                    <div
                        class="absolute left-0 bg-white rounded-xl shadow-lg border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-100' }} opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition z-40 w-auto">

                        <div class="py-2 text-sm w-auto min-w-[220px]">

                            @foreach ($rootCategories as $category)
                                <div class="relative group/category">

                                    @php
                                        $categoryUrl = route('categories.show', [$category->id, $category->slug]);
                                    @endphp

                                    <a href="{{ $categoryUrl ?? '#' }}"
                                        class="flex items-center justify-between px-4 py-2 text-gray-700 hover:bg-gray-50 hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} transition">

                                        <span>{{ $category->name }}</span>

                                        @if ($category->children->isNotEmpty())
                                            <i class="ri-arrow-right-s-line text-xs"></i>
                                        @endif

                                    </a>

                                    @if ($category->children->isNotEmpty())
                                        <div
                                            class="absolute top-0 left-full mt-0 w-56 bg-white rounded-xl shadow-lg border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-100' }} opacity-0 pointer-events-none group-hover/category:opacity-100 group-hover/category:pointer-events-auto transition z-40">

                                            <div class="py-2 text-xs text-gray-700">

                                                @foreach ($category->children as $child)
                                                    <div class="relative group/subcategory">

                                                        <a href="{{ route('categories.show', [$child->id, $child->slug]) }}"
                                                            class="flex items-center justify-between px-4 py-2 hover:bg-gray-50 hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} transition">

                                                            <span>{{ $child->name }}</span>

                                                            @if ($child->children && $child->children->isNotEmpty())
                                                                <i class="ri-arrow-right-s-line text-[10px]"></i>
                                                            @endif

                                                        </a>

                                                        @if ($child->children && $child->children->isNotEmpty())
                                                            <div
                                                                class="absolute top-0 left-full ml-1 w-52 bg-white rounded-xl shadow-lg border {{ $theme->color ? 'border-' . $theme->color . '/30' : 'border-gray-100' }} opacity-0 pointer-events-none group-hover/subcategory:opacity-100 group-hover/subcategory:pointer-events-auto transition z-40">

                                                                <div class="py-2 text-[11px] text-gray-700">

                                                                    @foreach ($child->children as $grandchild)
                                                                        <a href="{{ route('categories.show', [$grandchild->id, $grandchild->slug]) }}"
                                                                            class="block px-4 py-1.5 hover:bg-gray-50 hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} transition">
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

                        </div>

                    </div>

                </div>

                {{-- SAĞ: KATEGORİ NAV --}}
                <nav class="flex items-center gap-2 text-sm font-medium">

                    <a href="{{ route('home') }}"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-full hover:bg-gray-100 hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} transition">
                        Anasayfa
                    </a>

                    <a href="{{ route('products.index') }}"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-full hover:bg-gray-100 hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} transition">
                        Tüm Ürünler
                    </a>

                    @foreach ($rootCategories as $category)
                        <div class="relative group px-1 py-1">

                            @php
                                $categoryUrl = route('categories.show', [$category->id, $category->slug]);
                            @endphp

                            <a href="{{ $categoryUrl ?? '#' }}"
                                class="inline-flex items-center gap-1 px-3 py-2 rounded-full hover:bg-gray-100 hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} transition">

                                <span>{{ $category->name }}</span>

                                @if ($category->children->isNotEmpty())
                                    <i class="ri-arrow-down-s-line text-xs"></i>
                                @endif

                            </a>

                            @if ($category->children->isNotEmpty())
                                <div
                                    class="absolute left-0 z-40 top-full w-56 bg-white rounded-xl border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-100' }} shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition">

                                    <div class="py-2">

                                        @foreach ($category->children as $child)
                                            <div class="relative group/child">

                                                <a href="{{ route('categories.show', [$child->id, $child->slug]) }}"
                                                    class="flex items-center justify-between px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} transition">

                                                    <span>{{ $child->name }}</span>

                                                    @if ($child->children && $child->children->isNotEmpty())
                                                        <i class="ri-arrow-right-s-line text-[10px]"></i>
                                                    @endif

                                                </a>

                                                @if ($child->children && $child->children->isNotEmpty())
                                                    <div
                                                        class="absolute top-0 left-full ml-1 w-52 bg-white rounded-xl shadow-lg border {{ $theme->color ? 'border-' . $theme->color . '/40' : 'border-gray-100' }} opacity-0 pointer-events-none group-hover/child:opacity-100 group-hover/child:pointer-events-auto transition z-40">

                                                        <div class="py-2 text-[11px] text-gray-700">

                                                            @foreach ($child->children as $grandchild)
                                                                <a href="{{ route('categories.show', [$grandchild->id, $grandchild->slug]) }}"
                                                                    class="block px-3 py-1.5 hover:bg-gray-50 hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} transition">
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
