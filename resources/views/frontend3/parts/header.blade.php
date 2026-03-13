@php
    $guestCartId = session('guest_cart_id');
    if (!$guestCartId && !auth()->check()) {
        $guestCartId = session()->getId();
        session(['guest_cart_id' => $guestCartId]);
    }
    $headerCartSummary = \App\Models\Cart::headerSummary(auth()->id(), $guestCartId);
@endphp

<header class="bg-white sticky top-0 z-40 border-b border-{{ $theme->color ? $theme->color : 'border-gray-200' }}">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between py-2 md:py-4 gap-8">
            {{-- Logo --}}
            <div class="flex-shrink-0">
                <a href="{{ route('home') }}" class="block">
                    @if (!empty($settings?->logo))
                        <img src="{{ $settings->logo }}" alt="{{ $settings->title ?? config('app.name') }}"
                            class="md:h-12 h-12 w-auto object-contain">
                    @else
                        <span
                            class="text-2xl font-semibold uppercase tracking-tighter text-gray-900">{{ $settings->title ?? config('app.name') }}</span>
                    @endif
                </a>
            </div>
            <div class="flex-1 max-w-2xl hidden md:block">
                <form action="{{ route('products.index') }}" method="GET" class="relative">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                            <i class="ri-search-line text-gray-400 text-lg"></i>
                        </div>
                        <input type="text" name="q" placeholder="Arama" value="{{ request('q') }}"
                            class="w-full bg-white border border-gray-200 pl-12 pr-4 py-3.5 text-md rounded-full focus:border-{{ $theme->color ? $theme->color : 'border-blue-600' }} transition-all outline-none font-medium text-gray-900 shadow-sm">
                    </div>
                </form>
            </div>
            <div class="hidden md:flex items-center gap-3">
                @guest
                    <a href="{{ route('login') }}"
                        class="w-12 h-12 flex items-center justify-center bg-gray-50 rounded-full text-gray-900 hover:bg-gray-100 transition-all border border-gray-100">
                        <i class="ri-user-line text-xl"></i>
                    </a>
                @else
                    <!-- Notifications -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open"
                            class="w-12 h-12 flex items-center justify-center relative bg-gray-200 rounded-full text-gray-900 hover:bg-gray-100 transition-all border border-gray-100">
                            <i
                                class="ri-notification-3-line text-xl {{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }}"></i>
                            @if (auth()->user()->unreadNotifications()->count() > 0)
                                <span
                                    class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full animate-pulse">
                                    {{ auth()->user()->unreadNotifications()->count() }}
                                </span>
                            @endif
                        </button>

                        <div x-show="open" x-transition x-cloak
                            class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden">
                            <div class="p-3 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                                <span class="text-sm font-semibold">Bildirimler</span>
                                <a href="{{ route('user.notifications.index') }}"
                                    class="text-xs {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} hover:underline">Tümünü
                                    Gör</a>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                @forelse(auth()->user()->notifications()->limit(5)->get() as $notif)
                                    <a href="{{ route('user.notifications.show', $notif->id) }}"
                                        class="block p-3 border-b border-gray-50 hover:bg-gray-50 transition-colors {{ !$notif->read_at ? 'bg-blue-50/30' : '' }}">
                                        <div class="flex gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                <i class="ri-notification-line text-blue-600"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $notif->title }}
                                                </p>
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
                                <div class="p-2 border-t border-gray-50 grid grid-cols-2 gap-2">
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
                        class="px-4 py-2 flex items-center justify-center bg-{{ $theme->color ? $theme->color : 'bg-blue-600' }} rounded-full text-white hover:opacity-90 transition-all">
                        @if (auth()->user()->name)
                            <span class="text-sm font-semibold">
                                {{ auth()->user()->name }}
                            </span>
                        @endif
                        <i class="ri-user-line text-xl ml-2"></i>
                    </a>
                    <form action="{{ route('logout') }}" method="POST"
                        class="hidden md:flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-900
                        hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }} transition-all rounded-full">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 text-sm">
                            Çıkış<i class="ri-logout-box-line text-xl"></i>
                        </button>
                    </form>
                @endguest
                <a href="{{ route('cart.index') }}"
                    class="h-12 px-6 flex items-center gap-3 bg-{{ $theme->color ? $theme->color . '/10' : 'bg-blue-600' }} rounded-full text-white hover:opacity-90 transition-all relative">
                    <i
                        class="ri-shopping-cart-2-line text-xl {{ $theme->color ? 'text-' . $theme->color : 'text-white' }}"></i>
                    @if (($headerCartSummary->count ?? 0) > 0)
                        <span
                            class="absolute -top-1 -right-1 bg-black text-white text-[10px] font-semibold w-5 h-5 flex items-center justify-center rounded-full">
                            {{ $headerCartSummary->count }}
                        </span>
                    @endif
                </a>
            </div>
            <button type="button" data-mobile-menu-trigger
                class="md:hidden w-11 h-11 flex items-center justify-center bg-gray-50 rounded-full text-gray-900 border border-gray-100">
                <i class="ri-menu-line text-xl"></i>
            </button>
        </div>
        <div class="hidden md:flex items-center py-2">
            <nav class="flex items-center gap-8">
                {{-- Tüm Ürünler --}}
                <div class="relative group">
                    <button
                        class="flex items-center gap-2 text-md font-semibold text-gray-900 hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }} transition-colors py-4">
                        Tüm Ürünler
                        <i class="ri-arrow-down-s-line transition-transform group-hover:rotate-180"></i>
                    </button>
                    {{-- Mega Menu --}}
                    <div
                        class="absolute top-full left-0 w-[800px] bg-white border border-gray-100 shadow-2xl rounded-2xl opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all transform translate-y-2 group-hover:translate-y-0 z-50 p-8">
                        <div class="grid grid-cols-4 gap-8">
                            @foreach ($categories->take(8) as $category)
                                <div class="space-y-4">
                                    <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                                        class="block text-md font-semibold text-gray-900 hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }}">
                                        {{ $category->name }}
                                    </a>
                                    <ul class="space-y-2">
                                        {{-- Alt Kategoriler --}}
                                        @foreach ($category->children->take(4) as $child)
                                            <li>
                                                <a href="{{ route('categories.show', [$child->id, $child->slug]) }}"
                                                    class="text-xs text-gray-500 hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }}">
                                                    {{ $child->name }}
                                                </a>
                                            </li>
                                        @endforeach

                                        {{-- Öne Çıkan Ürünler --}}
                                        @if (isset($category->previewProducts) && $category->previewProducts->isNotEmpty())
                                            @foreach ($category->previewProducts->take(4) as $product)
                                                @php $productSlug = Str::slug($product->title); @endphp
                                                <li>
                                                    <a href="{{ route('products.show', [$product->id, $productSlug]) }}"
                                                        class="text-xs text-gray-800 hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }}">
                                                        {{ $product->title }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Dinamik Kategoriler (Maks 8) --}}
                @foreach ($rootCategories->take(8) as $category)
                    <a href="{{ route('categories.show', [$category->id, $category->slug]) }}"
                        class="text-md font-semibold text-gray-900 hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }} transition-colors py-4">
                        {{ $category->name }}
                    </a>
                @endforeach

                {{-- Statik Linkler --}}
                <a href="{{ route('pages.about') }}"
                    class="text-md font-semibold text-gray-900 hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }} transition-colors py-4">Hakkımızda</a>
                <a href="{{ route('blog.index') }}"
                    class="text-md font-semibold text-gray-900 hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }} transition-colors py-4">Blog</a>
                <a href="{{ route('returns.lookup') }}"
                    class="text-md font-semibold text-gray-900 hover:text-{{ $theme->color ? $theme->color : 'text-blue-600' }} transition-colors py-4">
                    <i class="ri-loop-left-line"></i>
                    Sipariş Sorgula
                </a>
            </nav>
        </div>
    </div>
</header>
