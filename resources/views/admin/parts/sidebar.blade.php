@php
    $current = request()->route()?->getName();
    $isActive = function (array $names) use ($current) {
        return $current && in_array($current, $names, true);
    };

    // Initial dropdown state based on active route
    $initialDropdown = 'null';
    if (
        $isActive([
            'admin.site-settings.index',
            'admin.store-settings.index',
            'admin.shipping-companies.index',
            'admin.shipping-companies.orders',
        ])
    ) {
        $initialDropdown = "'settings'";
    } elseif (
        $isActive([
            'admin.products.index',
            'admin.categories.index',
            'admin.attributes.index',
            'admin.terms.index',
            'admin.products.bulk-excel',
        ])
    ) {
        $initialDropdown = "'products'";
    } elseif (
        $isActive([
            'admin.product-comments.index',
            'admin.product-comments.pending',
            'admin.product-comments.approved',
            'admin.product-comments.rejected',
            'admin.product-comments.show',
        ])
    ) {
        $initialDropdown = "'comments'";
    } elseif (
        $isActive([
            'admin.orders.index',
            'admin.orders.new',
            'admin.orders.pending',
            'admin.orders.canceled',
            'admin.orders.completed',
        ])
    ) {
        $initialDropdown = "'orders'";
    } elseif (
        $isActive([
            'admin.slider.index',
            'admin.home-sections.index',
            'admin.home-sections.create',
            'admin.home-sections.edit',
            'admin.blog.index',
            'admin.announcements.index',
            'admin.campaigns.index',
            'admin.notifications.web.index',
            'admin.notifications.web.history',
        ])
    ) {
        $initialDropdown = "'contents'";
    } elseif ($isActive(['admin.traffic.index', 'admin.sales-reports.index', 'admin.region-reports.index'])) {
        $initialDropdown = "'reports'";
    } elseif ($isActive(['admin.stock.low', 'admin.stock.out'])) {
        $initialDropdown = "'stock'";
    } elseif ($isActive(['admin.theme.selection', 'admin.theme.index'])) {
        $initialDropdown = "'theme'";
    } elseif ($isActive(['admin.logs.admin', 'admin.logs.customer'])) {
        $initialDropdown = "'logs'";
    }

    $activeClass = 'bg-gray-200 dark:bg-gray-800';
    $subActiveClass = 'bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300';
    $adminUser = $adminUser ?? request()->attributes->get('adminUser');
    $can = function (string $group) use ($adminUser) {
        if (!$adminUser) {
            return false;
        }
        if ($adminUser->isSuperAdmin()) {
            return true;
        }
        return $adminUser->hasRouteAccess('admin.' . $group . '.index');
    };
@endphp
<div x-show="mobileSidebarOpen" class="fixed inset-0 z-[60] md:hidden bg-black/50 backdrop-blur-sm transition-opacity"
    @click="mobileSidebarOpen = false" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" x-cloak></div>

<aside x-data="{
    collapsed: JSON.parse(localStorage.getItem('admin-sidebar-collapsed') || 'false'),
    openDropdown: {!! $initialDropdown !!},
    get isExpanded() { return !this.collapsed || mobileSidebarOpen }
}"
    class="shadow-md border-r border-gray-200 dark:border-gray-800 bg-white dark:bg-black backdrop-blur fixed inset-y-0 left-0 z-[70] transition-all duration-300 flex flex-col md:sticky md:top-0 md:h-screen md:z-10"
    :class="{
        'w-[16rem] translate-x-0': mobileSidebarOpen,
        '-translate-x-full md:translate-x-0': !mobileSidebarOpen,
        'md:w-20': collapsed,
        'md:w-[14rem]': !collapsed
    }">
    <div class="px-4 py-[1.05rem] border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="ri-store-line text-2xl"></i>
            <span class="font-bold text-sm" x-show="isExpanded" x-transition.opacity>Mağaza Yönetim Paneli</span>
        </div>
        <button type="button" class="md:hidden text-gray-500" @click="mobileSidebarOpen = false">
            <i class="ri-close-line text-2xl"></i>
        </button>
    </div>
    <button type="button"
        class="hidden md:inline-flex items-center justify-center w-8 h-8 rounded-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition absolute -right-4 top-24 z-50"
        @click="collapsed = !collapsed; localStorage.setItem('admin-sidebar-collapsed', JSON.stringify(collapsed))">
        <i class="ri-arrow-left-s-line text-xl" :class="collapsed ? 'rotate-180' : ''"></i>
    </button>
    <nav class="p-2 text-md px-4 h-[calc(100vh-6rem)] relative overflow-y-auto sidebar-scrollbar">
        <style>
            .sidebar-scrollbar::-webkit-scrollbar {
                width: 4px;
            }

            .sidebar-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .sidebar-scrollbar::-webkit-scrollbar-thumb {
                background: #1a1a1a;
                border-radius: 10px;
            }

            .dark .sidebar-scrollbar::-webkit-scrollbar-thumb {
                background: #444;
            }

            .sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #000;
            }

            .dark .sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
        </style>
        @if ($can('dashboard'))
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-2 px-2 rounded text-sm hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors {{ $isActive(['admin.dashboard']) ? $activeClass : '' }}"
                :class="collapsed ? 'py-1.5' : 'py-1.5'">
                <i class="ri-home-4-line" :class="collapsed ? 'text-xl' : 'text-base'"></i>
                <span x-show="isExpanded" x-transition.opacity>Anasayfa</span>
            </a>
        @endif

        @php
            $settingsRoutes = [
                'admin.site-settings.index',
                'admin.store-settings.index',
                'admin.shipping-companies.index',
                'admin.shipping-companies.orders',
            ];
            $canSettings = $can('site-settings') || $can('store-settings') || $can('shipping-companies');
        @endphp

        @if ($canSettings)
            <div class="mt-2">
                <button type="button" @click="openDropdown = (openDropdown === 'settings' ? null : 'settings')"
                    class="w-full flex items-center justify-between px-2 rounded text-sm hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors {{ $isActive($settingsRoutes) ? $activeClass : '' }}"
                    :class="collapsed ? 'py-1.5' : 'py-1.5'">
                    <span class="flex items-center gap-2">
                        <i class="ri-settings-5-line" :class="collapsed ? 'text-xl' : 'text-base'"></i>
                        <span x-show="isExpanded" x-transition.opacity>Ayarlar</span>
                    </span>
                    <i class="ri-arrow-down-s-line text-xs transition-transform duration-200"
                        :class="openDropdown === 'settings' ? 'rotate-180' : ''" x-show="isExpanded"></i>
                </button>
                <div x-show="openDropdown === 'settings' && isExpanded" x-collapse.duration.300ms>
                    <div class="space-y-1 mt-1 ml-2 pb-1">
                        @if ($can('site-settings'))
                            <a href="{{ route('admin.site-settings.index') }}"
                                class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.site-settings.index']) ? $subActiveClass : '' }}">
                                <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Site Ayarları
                            </a>
                        @endif
                        @if ($can('store-settings'))
                            <a href="{{ route('admin.store-settings.index') }}"
                                class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.store-settings.index']) ? $subActiveClass : '' }}">
                                <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Mağaza Ayarları
                            </a>
                        @endif
                        @if ($can('shipping-companies'))
                            <a href="{{ route('admin.shipping-companies.index') }}"
                                class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.shipping-companies.index', 'admin.shipping-companies.orders']) ? $subActiveClass : '' }}">
                                <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Teslimat Ayarları
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($can('products'))
            <div class="mt-2">
                @php
                    $productRoutes = [
                        'admin.products.index',
                        'admin.categories.index',
                        'admin.attributes.index',
                        'admin.terms.index',
                        'admin.products.bulk-excel',
                    ];
                @endphp
                <button type="button" @click="openDropdown = (openDropdown === 'products' ? null : 'products')"
                    class="w-full flex items-center justify-between px-2 rounded text-sm hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors {{ $isActive($productRoutes) ? $activeClass : '' }}"
                    :class="collapsed ? 'py-1.5' : 'py-1.5'">
                    <span class="flex items-center gap-2">
                        <i class="ri-box-3-line" :class="collapsed ? 'text-xl' : 'text-base'"></i>
                        <span x-show="isExpanded" x-transition.opacity>Ürünler</span>
                    </span>
                    <i class="ri-arrow-down-s-line text-xs transition-transform duration-200"
                        :class="openDropdown === 'products' ? 'rotate-180' : ''" x-show="isExpanded"></i>
                </button>
                <div x-show="openDropdown === 'products' && isExpanded" x-collapse.duration.300ms>
                    <div class="space-y-1 mt-1 ml-2 pb-1">
                        <a href="{{ route('admin.products.index') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.products.index']) ? $subActiveClass : '' }}"><span><i
                                    class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Ürünler</a>
                        <a href="{{ route('admin.categories.index') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.categories.index']) ? $subActiveClass : '' }}"><span><i
                                    class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Kategoriler</a>
                        <a href="{{ route('admin.attributes.index') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.attributes.index']) ? $subActiveClass : '' }}"><span><i
                                    class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Nitelikler</a>
                        <a href="{{ route('admin.terms.index') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.terms.index']) ? $subActiveClass : '' }}"><span><i
                                    class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Terimler</a>
                        <a href="{{ route('admin.products.bulk-excel') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.products.bulk-excel']) ? $subActiveClass : '' }}">
                            <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>
                            Toplu İşlemler
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-2">
            @php
                $commentRoutes = [
                    'admin.product-comments.index',
                    'admin.product-comments.pending',
                    'admin.product-comments.approved',
                    'admin.product-comments.rejected',
                    'admin.product-comments.show',
                ];
            @endphp
            <button type="button" @click="openDropdown = (openDropdown === 'comments' ? null : 'comments')"
                class="w-full flex items-center justify-between px-2 rounded text-sm hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors {{ $isActive($commentRoutes) ? $activeClass : '' }}"
                :class="collapsed ? 'py-1.5' : 'py-1.5'">
                <span class="flex items-center gap-2">
                    <i class="ri-chat-1-line" :class="collapsed ? 'text-xl' : 'text-base'"></i>
                    <span x-show="isExpanded" x-transition.opacity>Yorumlar</span>
                </span>
                <i class="ri-arrow-down-s-line text-xs transition-transform duration-200"
                    :class="openDropdown === 'comments' ? 'rotate-180' : ''" x-show="isExpanded"></i>
            </button>
            <div x-show="openDropdown === 'comments' && isExpanded" x-collapse.duration.300ms>
                <div class="space-y-1 mt-1 ml-2 pb-1">
                    <a href="{{ route('admin.product-comments.index') }}"
                        class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.product-comments.index']) ? $subActiveClass : '' }}">
                        <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Tüm Değerlendirmeler
                    </a>
                    <a href="{{ route('admin.product-comments.pending') }}"
                        class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.product-comments.pending']) ? $subActiveClass : '' }}">
                        <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Bekleyenler
                    </a>
                    <a href="{{ route('admin.product-comments.approved') }}"
                        class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.product-comments.approved']) ? $subActiveClass : '' }}">
                        <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Onaylananlar
                    </a>
                    <a href="{{ route('admin.product-comments.rejected') }}"
                        class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.product-comments.rejected']) ? $subActiveClass : '' }}">
                        <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Reddedilenler
                    </a>
                </div>
            </div>
        </div>
        @if ($can('orders'))
            <div class="mt-2">
                @php
                    $orderRoutes = [
                        'admin.orders.index',
                        'admin.orders.new',
                        'admin.orders.pending',
                        'admin.orders.canceled',
                        'admin.orders.completed',
                    ];
                @endphp
                <button type="button" @click="openDropdown = (openDropdown === 'orders' ? null : 'orders')"
                    class="w-full flex items-center justify-between px-2 rounded text-sm hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors {{ $isActive($orderRoutes) ? $activeClass : '' }}"
                    :class="collapsed ? 'py-1.5' : 'py-1.5'">
                    <span class="flex items-center gap-2">
                        <i class="ri-shopping-basket-line" :class="collapsed ? 'text-xl' : 'text-base'"></i>
                        <span x-show="isExpanded" x-transition.opacity>Siparişler</span>
                    </span>
                    <i class="ri-arrow-down-s-line text-xs transition-transform duration-200"
                        :class="openDropdown === 'orders' ? 'rotate-180' : ''" x-show="isExpanded"></i>
                </button>
                <div x-show="openDropdown === 'orders' && isExpanded" x-collapse.duration.300ms>
                    <div class="space-y-1 mt-1 ml-2 pb-1">
                        <a href="{{ route('admin.orders.index') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.orders.index']) ? $subActiveClass : '' }}">
                            <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Tüm
                            Siparişler</a>
                        <a href="{{ route('admin.orders.new') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.orders.new']) ? $subActiveClass : '' }}">
                            <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Yeni
                            Gelen (7 Gün)</a>
                        <a href="{{ route('admin.orders.pending') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.orders.pending']) ? $subActiveClass : '' }}">
                            <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Tüm
                            Bekleyenler</a>
                        <a href="{{ route('admin.orders.canceled') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.orders.canceled']) ? $subActiveClass : '' }}">
                            <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>İptal
                            Edilenler</a>
                        <a href="{{ route('admin.orders.completed') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.orders.completed']) ? $subActiveClass : '' }}">
                            <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Tamamlananlar</a>
                    </div>
                </div>
            </div>
        @endif

        @php
            $contentRoutes = [
                'admin.slider.index',
                'admin.home-sections.index',
                'admin.home-sections.create',
                'admin.home-sections.edit',
                'admin.blog.index',
                'admin.announcements.index',
                'admin.campaigns.index',
                'admin.notifications.web.index',
                'admin.notifications.web.history',
            ];
            $canContent =
                $can('slider') ||
                $can('home-sections') ||
                $can('blog') ||
                $can('announcements') ||
                $can('campaigns') ||
                $can('notifications');
        @endphp

        @if ($canContent)
            <div class="mt-2">
                <button type="button" @click="openDropdown = (openDropdown === 'contents' ? null : 'contents')"
                    class="w-full flex items-center justify-between px-2 rounded text-sm hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors {{ $isActive($contentRoutes) ? $activeClass : '' }}"
                    :class="collapsed ? 'py-1.5' : 'py-1.5'">
                    <span class="flex items-center gap-2">
                        <i class="ri-article-line" :class="collapsed ? 'text-xl' : 'text-base'"></i>
                        <span x-show="isExpanded" x-transition.opacity>İçerikler</span>
                    </span>
                    <i class="ri-arrow-down-s-line text-xs transition-transform duration-200"
                        :class="openDropdown === 'contents' ? 'rotate-180' : ''" x-show="isExpanded"></i>
                </button>
                <div x-show="openDropdown === 'contents' && isExpanded" x-collapse.duration.300ms>
                    <div class="space-y-1 mt-1 ml-2 pb-1">
                        @if ($can('slider'))
                            <a href="{{ route('admin.slider.index') }}"
                                class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.slider.index']) ? $subActiveClass : '' }}">
                                <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Slider Yönetimi
                            </a>
                        @endif
                        @if ($can('home-sections'))
                            <a href="{{ route('admin.home-sections.index') }}"
                                class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.home-sections.index', 'admin.home-sections.create', 'admin.home-sections.edit']) ? $subActiveClass : '' }}">
                                <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Alan Yönetimi
                            </a>
                        @endif
                        @if ($can('blog'))
                            <a href="{{ route('admin.blog.index') }}"
                                class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.blog.index']) ? $subActiveClass : '' }}">
                                <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Blog Yönetimi
                            </a>
                        @endif
                        @if ($can('announcements'))
                            <a href="{{ route('admin.announcements.index') }}"
                                class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.announcements.index']) ? $subActiveClass : '' }}">
                                <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Duyurular
                            </a>
                        @endif
                        @if ($can('campaigns'))
                            <a href="{{ route('admin.campaigns.index') }}"
                                class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.campaigns.index']) ? $subActiveClass : '' }}">
                                <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Duyuru Çubuğu
                            </a>
                        @endif
                        @if ($can('notifications'))
                            <a href="{{ route('admin.notifications.web.index') }}"
                                class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.notifications.web.index', 'admin.notifications.web.history']) ? $activeClass : '' }}">
                                <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Bildirimler
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($can('promotions'))
            <a href="{{ route('admin.promotions.index') }}"
                class="flex items-center gap-2 px-2 rounded text-sm hover:bg-gray-50 dark:hover:bg-gray-900 mt-1 {{ $isActive(['admin.promotions.index', 'admin.promotions.create', 'admin.promotions.edit']) ? $activeClass : '' }}"
                :class="collapsed ? 'py-1.5' : 'py-1.5'">
                <i class="ri-discount-percent-line" :class="collapsed ? 'text-xl' : 'text-base'"></i>
                <span x-show="isExpanded" x-transition.opacity>Promosyonlar</span>
            </a>
        @endif

        @if ($can('reports'))
            <div class="mt-2">
                @php
                    $reportRoutes = [
                        'admin.traffic.index',
                        'admin.live-visitors.index',
                        'admin.sales-reports.index',
                        'admin.region-reports.index',
                    ];
                @endphp
                <button type="button" @click="openDropdown = (openDropdown === 'reports' ? null : 'reports')"
                    class="w-full flex items-center justify-between px-2 rounded text-sm hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors {{ $isActive($reportRoutes) ? $subActiveClass : '' }}"
                    :class="collapsed ? 'py-1.5' : 'py-1.5'">
                    <span class="flex items-center gap-2">
                        <i class="ri-bar-chart-2-line" :class="collapsed ? 'text-xl' : 'text-base'"></i>
                        <span x-show="isExpanded" x-transition.opacity>Raporlar</span>
                    </span>
                    <i class="ri-arrow-down-s-line text-xs transition-transform duration-200"
                        :class="openDropdown === 'reports' ? 'rotate-180' : ''" x-show="isExpanded"></i>
                </button>
                <div x-show="openDropdown === 'reports' && isExpanded" x-collapse.duration.300ms>
                    <div class="space-y-1 mt-1 ml-2 pb-1">
                        <a href="{{ route('admin.traffic.index') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.traffic.index']) ? $subActiveClass : '' }}">
                            <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Site Trafiği
                        </a>
                        <a href="{{ route('admin.sales-reports.index') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.sales-reports.index']) ? $subActiveClass : '' }}">
                            <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Satış Raporları
                        </a>
                        <a href="{{ route('admin.region-reports.index') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.region-reports.index']) ? $subActiveClass : '' }}">
                            <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Bölgeler
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if ($can('customers'))
            <a href="{{ route('admin.customers.index') }}"
                class="flex items-center gap-2 px-2 text-sm rounded hover:bg-gray-50 dark:hover:bg-gray-900 mt-1 {{ $isActive(['admin.customers.index', 'admin.customers.show']) ? $activeClass : '' }}"
                :class="collapsed ? 'py-1.5' : 'py-1.5'">
                <i class="ri-group-line" :class="collapsed ? 'text-xl' : 'text-base'"></i>
                <span x-show="isExpanded" x-transition.opacity>Müşteriler</span>
            </a>
        @endif
        @if ($store && $store->auto_stock && $can('stock'))
            <div class="mt-2">
                @php
                    $stockRoutes = ['admin.stock.low', 'admin.stock.out'];
                @endphp
                <button type="button" @click="openDropdown = (openDropdown === 'stock' ? null : 'stock')"
                    class="w-full flex items-center justify-between px-2 text-sm rounded hover:bg-gray-50 dark:hover:bg-gray-900 {{ $isActive($stockRoutes) ? $activeClass : '' }}"
                    :class="collapsed ? 'py-1.5' : 'py-1.5'">
                    <span class="flex items-center gap-2">
                        <i class="ri-database-2-line" :class="collapsed ? 'text-xl' : 'text-base'"></i>
                        <span x-show="isExpanded" x-transition.opacity>Stok Yönetimi</span>
                    </span>
                    <i class="ri-arrow-down-s-line text-xs transition-transform duration-200"
                        :class="openDropdown === 'stock' ? 'rotate-180' : ''" x-show="isExpanded"></i>
                </button>
                <div x-show="openDropdown === 'stock' && isExpanded" x-collapse.duration.300ms>
                    <div class="space-y-1 ml-8 mt-1 pb-1">
                        <a href="{{ route('admin.stock.low') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.stock.low']) ? $subActiveClass : '' }}">Azalan
                            Ürünler</a>
                        <a href="{{ route('admin.stock.out') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.stock.out']) ? $subActiveClass : '' }}">Tükenmiş
                            Ürünler</a>
                    </div>
                </div>
            </div>
        @endif
        @if ($can('returns'))
            <div class="mt-2">
                <button type="button" onclick="window.location.href='{{ route('admin.returns.index') }}'"
                    class="w-full flex items-center justify-between px-2 text-sm rounded hover:bg-gray-50 dark:hover:bg-gray-900"
                    :class="collapsed ? 'py-1.5' : 'py-1.5'">
                    <span class="flex items-center gap-2">
                        <i class="ri-loop-left-line" :class="collapsed ? 'text-xl' : 'text-base'"></i>
                        <span x-show="isExpanded" x-transition.opacity>İade Yönetimi</span>
                    </span>
                </button>
            </div>
        @endif

        @if ($adminUser && $adminUser->isSuperAdmin())
            <div class="mt-4 border-t border-gray-200 dark:border-gray-800 pt-3">
                <div class="text-xs px-4 uppercase text-sm tracking-wide text-gray-500 dark:text-gray-400 mb-2"
                    x-show="isExpanded" x-transition.opacity>
                    Kullanıcı Yönetimi
                </div>
                @if ($can('users'))
                    <a href="{{ route('admin.users.index') }}"
                        class="flex items-center gap-2 px-3 text-sm rounded hover:bg-gray-50 dark:hover:bg-gray-900 {{ $isActive(['admin.users.index']) ? $activeClass : '' }}"
                        :class="collapsed ? 'py-1.5' : 'py-1.5'">
                        <i class="ri-user-line" :class="collapsed ? 'text-xl' : 'text-base'"></i>
                        <span x-show="isExpanded" x-transition.opacity>Kullanıcılar</span>
                    </a>
                @endif
            </div>
        @endif

        @if ($adminUser && $adminUser->isSuperAdmin())
            @if ($can('theme'))
                <div class="mt-2">
                    @php
                        $themeRoutes = ['admin.theme.selection', 'admin.theme.index'];
                    @endphp
                    <button type="button" @click="openDropdown = (openDropdown === 'theme' ? null : 'theme')"
                        class="w-full flex text-sm items-center justify-between px-3 rounded hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors {{ $isActive($themeRoutes) ? $activeClass : '' }}"
                        :class="collapsed ? 'py-1.5' : 'py-1.5'">
                        <span class="flex items-center gap-2">
                            <i class="ri-palette-line" :class="collapsed ? 'text-xl' : 'text-base'"></i>
                            <span x-show="isExpanded" x-transition.opacity>Tema Ayarları</span>
                        </span>
                        <i class="ri-arrow-down-s-line text-xs transition-transform duration-200"
                            :class="openDropdown === 'theme' ? 'rotate-180' : ''" x-show="isExpanded"></i>
                    </button>
                    <div x-show="openDropdown === 'theme' && isExpanded" x-collapse.duration.300ms>
                        <div class="space-y-1 mt-1 ml-2 pb-1">
                            <a href="{{ route('admin.theme.selection') }}"
                                class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.theme.selection']) ? $subActiveClass : '' }}">
                                <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Tema Seçimi
                            </a>
                            <a href="{{ route('admin.theme.index') }}"
                                class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.theme.index']) ? $subActiveClass : '' }}">
                                <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Renk Ayarları
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        @endif
        @if ($adminUser && $adminUser->isSuperAdmin())
            <div class="mt-2">
                @php
                    $logRoutes = ['admin.logs.admin', 'admin.logs.customer'];
                @endphp
                <button type="button" @click="openDropdown = (openDropdown === 'logs' ? null : 'logs')"
                    class="w-full flex text-sm items-center justify-between px-3 rounded hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors {{ $isActive($logRoutes) ? $activeClass : '' }}"
                    :class="collapsed ? 'py-1.5' : 'py-1.5'">
                    <span class="flex items-center gap-2">
                        <i class="ri-history-line" :class="collapsed ? 'text-xl' : 'text-base'"></i>
                        <span x-show="isExpanded" x-transition.opacity>Log Geçmişi</span>
                    </span>
                    <i class="ri-arrow-down-s-line text-xs transition-transform duration-200"
                        :class="openDropdown === 'logs' ? 'rotate-180' : ''" x-show="isExpanded"></i>
                </button>
                <div x-show="openDropdown === 'logs' && isExpanded" x-collapse.duration.300ms>
                    <div class="space-y-1 mt-1 ml-2 pb-1">
                        <a href="{{ route('admin.logs.admin') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.logs.admin']) ? $subActiveClass : '' }}">
                            <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Yönetici Logları
                        </a>
                        <a href="{{ route('admin.logs.customer') }}"
                            class="block px-2 py-1 rounded hover:bg-gray-50 text-xs dark:hover:bg-gray-900 {{ $isActive(['admin.logs.customer']) ? $subActiveClass : '' }}">
                            <span><i class="ri-circle-fill opacity-50 text-xs mr-3"></i></span>Müşteri Logları
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </nav>
</aside>
