@extends('admin.layouts.app')
@section('title', 'Yönetim Paneli')
@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <!-- 1. Widget: Satış Performansı -->
        <div
            class="bg-white dark:bg-black/20 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-md relative group">
            <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                data-tooltip-target="tooltip-perf">
                <i class="ri-information-line"></i>
            </div>
            <div id="tooltip-perf" role="tooltip"
                class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                Seçilen dönemdeki toplam ciro, sipariş adedi ve ortalama değerlerin özeti.
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
            <div class="flex items-center justify-between mb-3 pr-6">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    Satış Performansı
                </h2>
                <div class="relative group" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center gap-1 text-xs font-medium bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 px-3 py-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <span id="label-performance-period">Bu Yıl</span>
                        <i class="ri-arrow-down-s-line text-gray-400"></i>
                    </button>
                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-2 w-32 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-xl z-10 py-1"
                        style="display: none;">
                        <button onclick="updatePerformance('day', 'Bugün')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Bugün</button>
                        <button onclick="updatePerformance('week', 'Bu Hafta')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Bu
                            Hafta</button>
                        <button onclick="updatePerformance('month', 'Bu Ay')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Bu
                            Ay</button>
                        <button onclick="updatePerformance('year', 'Bu Yıl')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Bu
                            Yıl</button>
                        <button onclick="updatePerformance('all', 'Tüm Zamanlar')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Tüm
                            Zamanlar</button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div
                    class="p-4 rounded-lg bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/20">
                    <div id="v-perf-sales" class="text-xl font-bold text-blue-600 dark:text-blue-400">0.00 TL</div>
                    <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wider mt-1">Toplam Satış</div>
                </div>
                <div
                    class="p-4 rounded-lg bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-900/20">
                    <div id="v-perf-orders" class="text-xl font-bold text-indigo-600 dark:text-indigo-400">0</div>
                    <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wider mt-1">Sipariş</div>
                </div>
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800">
                    <div id="v-perf-avg-sale" class="text-xl font-bold text-gray-700 dark:text-gray-300">0.00 TL</div>
                    <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wider mt-1">Ortalama Satış</div>
                </div>
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800">
                    <div id="v-perf-avg-items" class="text-xl font-bold text-gray-700 dark:text-gray-300">0</div>
                    <div class="text-[10px] text-gray-500 uppercase font-semibold tracking-wider mt-1">Ortalama Adet</div>
                </div>
            </div>
        </div>

        <!-- 2. Widget: Sepet Özeti -->
        <div
            class="bg-white dark:bg-black/20 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-md relative group">
            <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                data-tooltip-target="tooltip-basket">
                <i class="ri-information-line"></i>
            </div>
            <div id="tooltip-basket" role="tooltip"
                class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                Mağazadaki sepetlerin ortalama tutar, ürün çeşidi ve miktar bazlı analizi.
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
            <div class="flex items-center justify-between mb-3 pr-6">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    Sepet Özeti
                </h2>
                <div class="relative group" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center gap-1 text-xs font-medium bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 px-3 py-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <span id="label-basket-period">Bu Yıl</span>
                        <i class="ri-arrow-down-s-line text-gray-400"></i>
                    </button>
                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-2 w-32 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-xl z-10 py-1"
                        style="display: none;">
                        <button onclick="updateBasket('day', 'Bugün')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Bugün</button>
                        <button onclick="updateBasket('week', 'Bu Hafta')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Bu
                            Hafta</button>
                        <button onclick="updateBasket('month', 'Bu Ay')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Bu
                            Ay</button>
                        <button onclick="updateBasket('year', 'Bu Yıl')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Bu
                            Yıl</button>
                        <button onclick="updateBasket('all', 'Tüm Zamanlar')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Tüm
                            Zamanlar</button>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500 mb-1 animate-pulse"></span>
                            <div class="text-[10px] text-gray-400 uppercase font-semibold tracking-wider mb-1">Aktif Sepet Sayısı
                            </div>
                        </div>
                        <div id="v-basket-avg" class="text-lg font-bold">0</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-semibold tracking-wider mb-1">Ort. Sepet Tutarı
                        </div>
                        <div id="v-basket-price-avg" class="text-lg font-bold">0.00 TL</div>
                    </div>
                </div>
                <div id="chart-basket-trend" class="h-32 w-full mt-2"></div>
            </div>
        </div>

        <!-- 3. Widget: Aktif Ziyaretçiler -->
        <div
            class="bg-white dark:bg-black/20 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-md relative group">
            <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                data-tooltip-target="tooltip-visitors">
                <i class="ri-information-line"></i>
            </div>
            <div id="tooltip-visitors" role="tooltip"
                class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                Belirlenen son zaman dilimi içinde siteyi ziyaret eden gerçek kullanıcılar.
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
            <div class="flex items-center justify-between mb-3 pr-6">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    Aktif Ziyaretçiler
                </h2>
                <div class="relative group" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center gap-1 text-xs font-medium bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 px-3 py-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <span id="label-visitor-period">Son 10 Dakika</span>
                        <i class="ri-arrow-down-s-line text-gray-400"></i>
                    </button>
                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-xl z-10 py-1"
                        style="display: none;">
                        <button onclick="updateVisitors('10min', 'Son 10 Dakika')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Son 10
                            Dakika</button>
                        <button onclick="updateVisitors('30min', 'Son 30 Dakika')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Son 30
                            Dakika</button>
                        <button onclick="updateVisitors('1hour', 'Son 1 Saat')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Son 1
                            Saat</button>
                        <button onclick="updateVisitors('today', 'Bugün')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Bugün</button>
                        <button onclick="updateVisitors('all', 'Tüm Zamanlar')"
                            class="w-full text-left px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">Tüm
                            Zamanlar</button>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-8 py-2">
                <div
                    class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-gray-900 rounded-xl min-w-[100px]">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></span>
                        <span id="v-visitor-total" class="text-2xl font-bold">0</span>
                    </div>
                    <span class="text-xs text-gray-500 font-medium">Kullanıcı</span>
                </div>
                <div class="flex-1 space-y-4 text-sm font-medium">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <i class="ri-computer-line"></i> Masaüstü
                        </div>
                        <span id="v-visitor-desktop">%0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <i class="ri-smartphone-line"></i> Mobil
                        </div>
                        <span id="v-visitor-mobile">%0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mevcut Grafikler -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div
            class="p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-md bg-white dark:bg-black/20 relative group">
            <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                data-tooltip-target="tooltip-chart-rev">
                <i class="ri-information-line"></i>
            </div>
            <div id="tooltip-chart-rev" role="tooltip"
                class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                Son 12 ayın başarılı siparişlerinden elde edilen brüt gelir dağılımı.
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
            <div id="chart-revenue" class="h-80"></div>
        </div>
        <div
            class="p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-md bg-white dark:bg-black/20 relative group">
            <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                data-tooltip-target="tooltip-chart-ord">
                <i class="ri-information-line"></i>
            </div>
            <div id="tooltip-chart-ord" role="tooltip"
                class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                Son 7 gün içindeki siparişlerin durumlarına göre günlük değişim grafiği.
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
            <div id="chart-orders" class="h-80"></div>
        </div>
    </div>

    <!-- Eski Widgetlar (Alt Sıra) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 my-6">
        <a href="{{ route('admin.orders.index') }}"
            class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm bg-white dark:bg-black/20 flex items-center justify-between hover:shadow-md transition-shadow relative group">
            <div class="absolute top-1 right-1 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                data-tooltip-target="tooltip-w-orders">
                <i class="ri-information-line text-[10px]"></i>
            </div>
            <div id="tooltip-w-orders" role="tooltip"
                class="absolute z-10 invisible inline-block px-3 py-2 text-[10px] font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                Sistemdeki toplam sipariş sayısı.
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
            <div class="flex items-center gap-3">
                <i class="ri-shopping-basket-line text-xl text-gray-400"></i>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Siparişler</div>
            </div>
            <span id="w-ordersTotal"
                class="px-2 py-0.5 text-xs font-bold rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">0</span>
        </a>
        <a href="{{ route('admin.stock.out') }}"
            class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm bg-white dark:bg-black/20 flex items-center justify-between hover:shadow-md transition-shadow relative group">
            <div class="absolute top-1 right-1 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                data-tooltip-target="tooltip-w-stock">
                <i class="ri-information-line text-[10px]"></i>
            </div>
            <div id="tooltip-w-stock" role="tooltip"
                class="absolute z-10 invisible inline-block px-3 py-2 text-[10px] font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                Stok miktarı kritik seviyenin altına düşen veya biten ürün sayısı.
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
            <div class="flex items-center gap-3">
                <i class="ri-error-warning-line text-xl text-red-400"></i>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tükenen</div>
            </div>
            <span id="w-outStock"
                class="px-2 py-0.5 text-xs font-bold rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">0</span>
        </a>
        <a href="{{ route('admin.products.index') }}"
            class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm bg-white dark:bg-black/20 flex items-center justify-between hover:shadow-md transition-shadow relative group">
            <div class="absolute top-1 right-1 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                data-tooltip-target="tooltip-w-products">
                <i class="ri-information-line text-[10px]"></i>
            </div>
            <div id="tooltip-w-products" role="tooltip"
                class="absolute z-10 invisible inline-block px-3 py-2 text-[10px] font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                Mağazanızda tanımlı toplam aktif ürün sayısı.
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
            <div class="flex items-center gap-3">
                <i class="ri-box-3-line text-xl text-gray-400"></i>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Ürünler</div>
            </div>
            <span id="w-productsCount"
                class="px-2 py-0.5 text-xs font-bold rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">0</span>
        </a>
        <a href="{{ route('admin.categories.index') }}"
            class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm bg-white dark:bg-black/20 flex items-center justify-between hover:shadow-md transition-shadow relative group">
            <div class="absolute top-1 right-1 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                data-tooltip-target="tooltip-w-categories">
                <i class="ri-information-line text-[10px]"></i>
            </div>
            <div id="tooltip-w-categories" role="tooltip"
                class="absolute z-10 invisible inline-block px-3 py-2 text-[10px] font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                Ürünlerinize ait toplam kategori sayısı.
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
            <div class="flex items-center gap-3">
                <i class="ri-grid-line text-xl text-gray-400"></i>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategoriler</div>
            </div>
            <span id="w-categoriesCount"
                class="px-2 py-0.5 text-xs font-bold rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">0</span>
        </a>
        <a href="{{ route('admin.customers.index') }}"
            class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm bg-white dark:bg-black/20 flex items-center justify-between hover:shadow-md transition-shadow relative group">
            <div class="absolute top-1 right-1 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                data-tooltip-target="tooltip-w-customers">
                <i class="ri-information-line text-[10px]"></i>
            </div>
            <div id="tooltip-w-customers" role="tooltip"
                class="absolute z-10 invisible inline-block px-3 py-2 text-[10px] font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                Sisteme kayıtlı toplam üye müşteri sayısı.
                <div class="tooltip-arrow" data-popper-arrow></div>
            </div>
            <div class="flex items-center gap-3">
                <i class="ri-user-heart-line text-xl text-gray-400"></i>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Müşteriler</div>
            </div>
            <span id="w-customersCount"
                class="px-2 py-0.5 text-xs font-bold rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">0</span>
        </a>
    </div>

    @push('head')
        <script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
        <script>
            let currentPerfPeriod = 'year';
            let currentBasketPeriod = 'year';
            let currentVisitorPeriod = '10min';

            function updatePerformance(period, label) {
                currentPerfPeriod = period;
                document.getElementById('label-performance-period').innerText = label;
                fetchWidgetData('performance', period);
            }

            function updateBasket(period, label) {
                currentBasketPeriod = period;
                document.getElementById('label-basket-period').innerText = label;
                fetchWidgetData('basket', period);
            }

            function updateVisitors(period, label) {
                currentVisitorPeriod = period;
                document.getElementById('label-visitor-period').innerText = label;
                fetchWidgetData('visitor', period);
            }

            function fetchWidgetData(type, period) {
                const url = `{{ route('admin.dashboard.metrics') }}?${type}_period=${period}`;
                fetch(url).then(r => r.json()).then(resp => {
                    if (resp.code === 1) {
                        const data = resp.data;
                        if (type === 'performance') renderPerformance(data.performanceStats);
                        if (type === 'basket') renderBasket(data.basketStats);
                        if (type === 'visitor') renderVisitors(data.visitorStats);
                    }
                });
            }

            function renderPerformance(p) {
                const currency = (val) => new Intl.NumberFormat('tr-TR', {
                    style: 'currency',
                    currency: 'TRY'
                }).format(val || 0);
                document.getElementById('v-perf-sales').innerText = currency(p.total_sales);
                document.getElementById('v-perf-orders').innerText = p.order_count || 0;
                document.getElementById('v-perf-avg-sale').innerText = currency(p.avg_sale);
                document.getElementById('v-perf-avg-items').innerText = (p.avg_order_items || 0).toFixed(2);
            }

            function renderBasket(b) {
                const currency = (val) => new Intl.NumberFormat('tr-TR', {
                    style: 'currency',
                    currency: 'TRY'
                }).format(val || 0);
                document.getElementById('v-basket-avg').innerText = b.basket_count || 0;
                document.getElementById('v-basket-price-avg').innerText = currency(b.price_avg);

                // Trend Grafiği
                const chartDom = document.getElementById('chart-basket-trend');
                if (!chartDom) return;
                const myChart = echarts.getInstanceByDom(chartDom) || echarts.init(chartDom);
                const option = {
                    grid: {
                        top: 10,
                        bottom: 20,
                        left: 0,
                        right: 0,
                        containLabel: false
                    },
                    tooltip: {
                        trigger: 'axis',
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        textStyle: {
                            color: '#000',
                            fontSize: 10
                        },
                        formatter: function(params) {
                            let res = `<div style="font-weight:bold;margin-bottom:3px">${params[0].name}</div>`;
                            params.forEach(item => {
                                const val = item.seriesName === 'Ort. Tutar' ? currency(item.value) : item
                                    .value;
                                res += `<div style="display:flex;justify-content:space-between;gap:10px">
                                    <span>${item.seriesName}:</span>
                                    <span style="font-weight:bold">${val}</span>
                                </div>`;
                            });
                            return res;
                        }
                    },
                    xAxis: {
                        type: 'category',
                        data: b.trend.map(i => i.date),
                        show: false
                    },
                    yAxis: [{
                        type: 'value',
                        show: false
                    }, {
                        type: 'value',
                        show: false
                    }],
                    series: [{
                            name: 'Sepet Sayısı',
                            data: b.trend.map(i => i.count),
                            type: 'bar',
                            itemStyle: {
                                color: '#e5e7eb',
                                borderRadius: [2, 2, 0, 0]
                            }
                        },
                        {
                            name: 'Ort. Tutar',
                            data: b.trend.map(i => i.avg),
                            type: 'line',
                            yAxisIndex: 1,
                            smooth: true,
                            showSymbol: false,
                            lineStyle: {
                                width: 2,
                                color: '#7c3aed'
                            }
                        }
                    ]
                };
                myChart.setOption(option);
            }

            function renderVisitors(v) {
                document.getElementById('v-visitor-total').innerText = v.total || 0;
                document.getElementById('v-visitor-desktop').innerText = `%${v.desktop_percent || 0}`;
                document.getElementById('v-visitor-mobile').innerText = `%${v.mobile_percent || 0}`;
            }

            function refreshDashboard() {
                const url =
                    `{{ route('admin.dashboard.metrics') }}?performance_period=${currentPerfPeriod}&basket_period=${currentBasketPeriod}&visitor_period=${currentVisitorPeriod}`;
                fetch(url).then(r => r.json()).then(resp => {
                    if (resp.code === 1) {
                        const data = resp.data;
                        renderPerformance(data.performanceStats);
                        renderBasket(data.basketStats);
                        renderVisitors(data.visitorStats);
                        renderCharts(data);

                        // Alt sayaçlar
                        document.getElementById('w-ordersTotal').innerText = (data.orders?.new || 0) + (data.orders
                                ?.pending || 0) +
                            (data.orders?.canceled || 0) + (data.orders?.completed || 0);
                        document.getElementById('w-outStock').innerText = data.outStock || 0;
                        document.getElementById('w-productsCount').innerText = data.productsCount || 0;
                        document.getElementById('w-categoriesCount').innerText = data.categoriesCount || 0;
                        document.getElementById('w-customersCount').innerText = data.customerCount || 0;
                    }
                });
            }

            function renderCharts(data) {
                const ec = window.echarts;
                const revenueEl = document.getElementById('chart-revenue');
                const ordersEl = document.getElementById('chart-orders');

                if (revenueEl && ec) {
                    const c = ec.getInstanceByDom(revenueEl) || ec.init(revenueEl, null, {
                        renderer: 'svg'
                    });
                    const revenueData = data.monthlyRevenue || [];
                    c.setOption({
                        title: {
                            text: 'Aylık Gelir',
                            left: 'center',
                            textStyle: {
                                fontSize: 14
                            }
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'shadow'
                            }
                        },
                        grid: {
                            bottom: '10%',
                            top: '20%',
                            containLabel: true
                        },
                        xAxis: {
                            type: 'category',
                            data: revenueData.map(item => item.month)
                        },
                        yAxis: {
                            type: 'value'
                        },
                        series: [{
                            name: 'Gelir',
                            type: 'bar',
                            data: revenueData.map(item => item.revenue),
                            itemStyle: {
                                color: '#10b981',
                                borderRadius: [4, 4, 0, 0]
                            }
                        }]
                    });
                }

                if (ordersEl && ec) {
                    const c = ec.getInstanceByDom(ordersEl) || ec.init(ordersEl, null, {
                        renderer: 'svg'
                    });
                    const dailyData = data.dailyOrders || [];
                    c.setOption({
                        title: {
                            text: 'Sipariş Durumları (Son 7 Gün)',
                            left: 'center',
                            textStyle: {
                                fontSize: 14
                            }
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross',
                                label: {
                                    backgroundColor: '#6a7985'
                                }
                            }
                        },
                        legend: {
                            data: ['Yeni', 'Bekleyen', 'Tamamlanan', 'İptal'],
                            bottom: 0,
                            icon: 'circle'
                        },
                        grid: {
                            left: '3%',
                            right: '4%',
                            bottom: '10%',
                            top: '20%',
                            containLabel: true
                        },
                        xAxis: [{
                            type: 'category',
                            boundaryGap: false,
                            data: dailyData.map(item => item.day)
                        }],
                        yAxis: [{
                            type: 'value'
                        }],
                        series: [{
                                name: 'Yeni',
                                type: 'line',
                                stack: 'Total',
                                areaStyle: {},
                                emphasis: {
                                    focus: 'series'
                                },
                                data: dailyData.map(item => item.new),
                                itemStyle: {
                                    color: '#3b82f6'
                                }
                            },
                            {
                                name: 'Bekleyen',
                                type: 'line',
                                stack: 'Total',
                                areaStyle: {},
                                emphasis: {
                                    focus: 'series'
                                },
                                data: dailyData.map(item => item.pending),
                                itemStyle: {
                                    color: '#f59e0b'
                                }
                            },
                            {
                                name: 'Tamamlanan',
                                type: 'line',
                                stack: 'Total',
                                areaStyle: {},
                                emphasis: {
                                    focus: 'series'
                                },
                                data: dailyData.map(item => item.completed),
                                itemStyle: {
                                    color: '#10b981'
                                }
                            },
                            {
                                name: 'İptal',
                                type: 'line',
                                stack: 'Total',
                                areaStyle: {},
                                emphasis: {
                                    focus: 'series'
                                },
                                data: dailyData.map(item => item.canceled),
                                itemStyle: {
                                    color: '#ef4444'
                                }
                            }
                        ]
                    });
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                refreshDashboard();
            });
        </script>
    @endpush
@endsection
