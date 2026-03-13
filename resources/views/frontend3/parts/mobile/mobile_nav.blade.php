@php
    $currentRoute = request()->route()?->getName();
    $isActive = function ($routePattern) use ($currentRoute) {
        if (is_array($routePattern)) {
            return in_array($currentRoute, $routePattern);
        }
        return $currentRoute === $routePattern || Str::startsWith($currentRoute, $routePattern);
    };

    $navItems = [
        [
            'name' => 'Anasayfa',
            'icon' => 'ri-home-line',
            'active_icon' => 'ri-home-fill',
            'route' => 'home',
            'pattern' => 'home',
        ],
        [
            'name' => 'Ürünler',
            'icon' => 'ri-apps-line',
            'active_icon' => 'ri-apps-fill',
            'route' => 'products.index',
            'pattern' => ['products.index', 'categories.show'],
        ],
        [
            'name' => 'Sepet',
            'icon' => 'ri-shopping-cart-line',
            'active_icon' => 'ri-shopping-cart-fill',
            'route' => 'cart.index',
            'pattern' => 'cart.index',
            'badge' => true,
        ],
        [
            'name' => 'Sorgula',
            'icon' => 'ri-search-eye-line',
            'active_icon' => 'ri-search-eye-fill',
            'route' => 'returns.lookup',
            'pattern' => 'returns.lookup',
        ],
        [
            'name' => 'Hesabım',
            'icon' => 'ri-user-line',
            'active_icon' => 'ri-user-fill',
            'route' => auth()->check() ? 'user.dashboard' : 'login',
            'pattern' => ['user.dashboard', 'login', 'register', 'password.request'],
        ],
    ];

    $guestCartId = session('guest_cart_id');
    if (!$guestCartId && !auth()->check()) {
        $guestCartId = session()->getId();
        session(['guest_cart_id' => $guestCartId]);
    }
    $cartSummary = \App\Models\Cart::headerSummary(auth()->id(), $guestCartId);
@endphp

<div class="md:hidden fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 z-30 pb-safe">
    <div class="grid grid-cols-5 h-14">
        @foreach ($navItems as $item)
            @php
                $active = $isActive($item['pattern']);
                $colorClass = $theme->color ? 'text-' . $theme->color : 'text-blue-600';
            @endphp
            <a href="{{ route($item['route']) }}"
                class="flex flex-col items-center justify-center gap-1/2 w-full h-full {{ $active ? $colorClass : 'text-gray-500 hover:text-gray-900' }}">
                <div class="relative">
                    <i class="{{ $active ? $item['active_icon'] : $item['icon'] }} text-xl"></i>
                    @if (isset($item['badge']) && $item['badge'] && ($cartSummary->count ?? 0) > 0)
                        <span data-cart-count
                            class="absolute -top-1.5 -right-1.5 {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full ring-2 ring-white">
                            {{ $cartSummary->count }}
                        </span>
                    @endif
                </div>
                <span class="text-[10px] font-medium truncate max-w-full px-1">{{ $item['name'] }}</span>
            </a>
        @endforeach
    </div>
</div>
<div class="md:hidden h-16 pb-safe"></div>
