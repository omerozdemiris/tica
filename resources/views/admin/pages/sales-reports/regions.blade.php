@extends('admin.layouts.app')
@section('title', 'Bölgesel Satış Raporları')
@section('content')
    <div class="p-4 pt-0 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Bölgesel Satış ve Sipariş Analizi</h1>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <form action="{{ route('admin.region-reports.index') }}" method="GET"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-4">
                <div class="relative">
                    <label class="block text-xs font-medium text-gray-500 mb-1 flex items-center gap-1">
                        <i class="ri-calendar-line"></i> Başlangıç Tarihi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <i class="ri-calendar-event-line text-gray-400 text-sm"></i>
                        </div>
                        <input datepicker datepicker-autohide datepicker-format="yyyy-mm-dd" type="text"
                            name="start_date" value="{{ request('start_date') }}"
                            class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5"
                            placeholder="Başlangıç Seçin">
                    </div>
                </div>
                <div class="relative">
                    <label class="block text-xs font-medium text-gray-500 mb-1 flex items-center gap-1">
                        <i class="ri-calendar-check-line"></i> Bitiş Tarihi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <i class="ri-calendar-check-line text-gray-400 text-sm"></i>
                        </div>
                        <input datepicker datepicker-autohide datepicker-format="yyyy-mm-dd" type="text" name="end_date"
                            value="{{ request('end_date') }}"
                            class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5"
                            placeholder="Bitiş Seçin">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1 flex items-center gap-1">
                        <i class="ri-map-pin-line"></i> Şehir
                    </label>
                    <select name="city_id" id="city_select" onchange="this.form.submit()"
                        class="w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tümü</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1 flex items-center gap-1">
                        <i class="ri-folder-line"></i> Kategori Filtrele
                    </label>
                    <select name="category_id" id="category_select"
                        class="w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tümü</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1 flex items-center gap-1">
                        <i class="ri-box-3-line"></i> Ürün Filtrele
                    </label>
                    <select name="product_id" id="product_select"
                        class="w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tümü</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}"
                                {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1 flex items-center gap-1">
                        <i class="ri-money-dollar-circle-line"></i> Fiyat Aralığı (TL)
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="relative">
                            <i class="ri-money-dollar-circle-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                            <input type="number" name="min_price" step="0.01" min="0"
                                value="{{ request('min_price') }}"
                                class="pl-8 w-full px-2 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 text-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Min">
                        </div>
                        <div class="relative">
                            <i class="ri-money-dollar-circle-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                            <input type="number" name="max_price" step="0.01" min="0"
                                value="{{ request('max_price') }}"
                                class="pl-8 w-full px-2 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 text-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Max">
                        </div>
                    </div>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="bg-gray-900 text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-blue-700 transition-colors flex items-center gap-2 shadow-sm whitespace-nowrap">
                        <i class="ri-filter-3-line"></i> Filtrele
                    </button>
                    <a href="{{ route('admin.region-reports.index') }}"
                        class="bg-gray-100 text-gray-600 rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-gray-200 transition-colors flex items-center gap-2 whitespace-nowrap">
                        <i class="ri-refresh-line"></i> Sıfırla
                    </a>
                </div>
            </form>
        </div>
        <!-- Üst Özet Kartları -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 flex items-center gap-3 relative group">
                <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                    data-tooltip-target="tooltip-total-sales">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-total-sales" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Seçili filtreler dahilindeki brüt satış tutarı.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                    <i class="ri-map-pin-2-line ri-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider leading-none">Toplam Satış</p>
                    <p class="text-sm font-bold text-blue-600 mt-1">
                        {{ number_format($totals->total_amount, 2, ',', '.') }} TL</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 flex items-center gap-3 relative group">
                <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                    data-tooltip-target="tooltip-total-orders">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-total-orders" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Filtreler dahilinde oluşturulan benzersiz sipariş sayısı.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center text-green-600">
                    <i class="ri-shopping-basket-line ri-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider leading-none">Toplam Sipariş
                    </p>
                    <p class="text-sm font-bold text-green-600 mt-1">
                        {{ number_format($totals->total_orders, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 flex items-center gap-3 relative group">
                <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                    data-tooltip-target="tooltip-avg-basket">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-avg-basket" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Toplam cironun toplam sipariş sayısına bölünmesiyle hesaplanan ortalama tutar.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">
                    <i class="ri-hand-coin-line ri-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider leading-none">Ortalama Sepet
                    </p>
                    <p class="text-sm font-bold text-purple-600 mt-1">
                        {{ number_format($totals->total_orders > 0 ? $totals->total_amount / $totals->total_orders : 0, 2, ',', '.') }}
                        TL
                    </p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 flex items-center gap-3 relative group">
                <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                    data-tooltip-target="tooltip-most-ordered">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-most-ordered" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Tüm zamanlar genelinde en fazla siparişin verildiği şehir.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center text-orange-600">
                    <i class="ri-medal-line ri-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider leading-none">En Çok Sipariş
                    </p>
                    <p class="text-sm font-bold text-orange-600 mt-1">
                        {{ $mostOrderedCity->name ?? '-' }} ({{ $mostOrderedCity->order_count ?? 0 }})
                    </p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 flex items-center gap-3 relative group">
                <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                    data-tooltip-target="tooltip-most-active-region">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-most-active-region" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Bölgeler arasında en yüksek sipariş adedine sahip bölge.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                    <i class="ri-global-line ri-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider leading-none">En Aktif Bölge
                    </p>
                    <p class="text-sm font-bold text-indigo-600 mt-1">
                        {{ $mostActiveRegion['name'] ?? '-' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Alt Analiz Widgetları -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Premium Müşteriler -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 flex flex-col gap-3 relative group">
                <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                    data-tooltip-target="tooltip-premium-customers">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-premium-customers" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Sipariş tutarı en yüksek olan ilk 3 üye müşteri.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center text-amber-600">
                        <i class="ri-vip-crown-line"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-700 uppercase tracking-tight">Premium Müşteriler</p>
                </div>
                <div class="space-y-2">
                    @foreach ($premiumCustomers as $customer)
                        <div class="flex justify-between items-center text-[11px] border-b border-gray-50 pb-1">
                            <div class="flex flex-col truncate max-w-[90px]">
                                <span class="text-gray-900 font-bold truncate"
                                    title="{{ $customer->customer_name }}">{{ $customer->customer_name }}</span>
                                <span class="text-[9px] text-blue-500">{{ $customer->city }}</span>
                            </div>
                            <span class="font-bold text-gray-900">{{ number_format($customer->total, 0, ',', '.') }}
                                TL</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Tekrar Sipariş Oranı -->
            <div
                class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 flex flex-col justify-center items-center text-center relative group">
                <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                    data-tooltip-target="tooltip-repeat-rate">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-repeat-rate" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Birden fazla kez alışveriş yapan müşteri yüzdesi.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div class="w-10 h-10 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-600 mb-2">
                    <i class="ri-refresh-line ri-lg"></i>
                </div>
                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider leading-none">Tekrar Sipariş Oranı
                </p>
                <p class="text-xl font-black text-emerald-600 mt-2">%{{ number_format($repeatRate, 1) }}</p>
                <p class="text-[9px] text-gray-400 mt-1">Sadık Müşteri Kitlesi</p>
            </div>

            <!-- En Yoğun Gün / Saat -->
            <div
                class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 flex flex-col justify-center relative group">
                <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                    data-tooltip-target="tooltip-busy-time">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-busy-time" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Siparişlerin en sık geldiği gün ve saat aralığı.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-rose-50 rounded-lg flex items-center justify-center text-rose-600">
                        <i class="ri-time-line"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-700 uppercase tracking-tight">En Yoğun Zaman</p>
                </div>
                <div class="space-y-1">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] text-gray-500 uppercase">GÜN:</span>
                        <span class="text-[11px] font-bold text-rose-600">{{ $busyDay }}</span>
                    </div>
                    <div class="flex justify-between items-center border-t border-gray-50 pt-1">
                        <span class="text-[10px] text-gray-500 uppercase">SAAT:</span>
                        <span class="text-[11px] font-bold text-rose-600">{{ $busyHour }}</span>
                    </div>
                </div>
            </div>

            <!-- Bölgesel Sadakat Oranı -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 flex flex-col gap-2 relative group">
                <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                    data-tooltip-target="tooltip-regional-loyalty">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-regional-loyalty" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    İllerdeki müşterilerin sizi tekrar tercih etme oranı.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider mb-1 leading-none">Bölgesel
                    Sadakat</p>
                <div class="space-y-2 mt-1">
                    @foreach ($cityLoyalty as $city)
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-gray-700 w-12 truncate">{{ $city['name'] }}</span>
                            <div class="flex-1 h-1 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $city['rate'] }}%"></div>
                            </div>
                            <span
                                class="text-[10px] font-bold text-indigo-600">%{{ number_format($city['rate'], 0) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Son Sipariş Gelen İl -->
            <div
                class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 flex flex-col justify-center items-center text-center relative group">
                <div class="absolute top-2 right-2 transition-opacity cursor-help text-gray-300 hover:text-gray-500"
                    data-tooltip-target="tooltip-last-order">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-last-order" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Mağazadan gelen en son başarılı siparişin konumu.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div class="w-10 h-10 bg-sky-50 rounded-xl flex items-center justify-center text-sky-600 mb-2">
                    <i class="ri-map-pin-user-line ri-lg"></i>
                </div>
                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider leading-none">Son Sipariş</p>
                <p class="text-sm font-bold text-gray-900 mt-2">{{ $lastOrder->city ?? '-' }}</p>
                <p class="text-[10px] text-sky-600 font-medium mt-1 uppercase">
                    {{ $lastOrder ? $lastOrder->created_at->diffForHumans() : '-' }}
                </p>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 lg:col-span-2 min-h-[500px]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="ri-map-2-line text-gray-600"></i>
                        Satış Haritası
                    </h3>
                    <span class="text-xs text-gray-400">Balonlar satış hacmini temsil eder</span>
                </div>
                <div id="turkeyMap" style="height: 500px; width: 100%;border-radius: 1rem;"></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="ri-list-ordered text-orange-500"></i>
                    En Çok Satış Yapan İller
                </h3>
                <div class="space-y-3 overflow-y-auto max-h-[480px] pr-2 custom-scrollbar">
                    @if ($salesByCity && $salesByCity->count() > 0)
                        @foreach ($salesByCity->sortByDesc('total') as $item)
                            <div
                                class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-semibold text-gray-900">{{ $item->name ?? 'Belirtilmemiş' }}</span>
                                    <span class="text-xs text-gray-500">{{ $item->order_count }} Sipariş</span>
                                </div>
                                <span
                                    class="text-sm font-bold text-blue-600">{{ number_format($item->total, 2, ',', '.') }}
                                    TL</span>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-10 text-gray-400 text-sm">
                            Henüz veri bulunmuyor.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof TomSelect !== 'undefined') {
                    new TomSelect('#city_select', {
                        create: false,
                        sortField: {
                            field: 'text',
                            direction: 'asc'
                        }
                    });
                    new TomSelect('#category_select', {
                        create: false,
                        sortField: {
                            field: 'text',
                            direction: 'asc'
                        }
                    });
                    new TomSelect('#product_select', {
                        create: false,
                        sortField: {
                            field: 'text',
                            direction: 'asc'
                        }
                    });
                }

                const mapContainer = document.getElementById('turkeyMap');
                if (!mapContainer) return;

                const map = L.map('turkeyMap', {
                    center: [39.0, 35.0],
                    zoom: 6,
                    minZoom: 5,
                    maxZoom: 12,
                    scrollWheelZoom: false,
                    attributionControl: false
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(map);

                const salesData = {!! json_encode($salesByCity) !!};
                const maxTotal = Math.max(...salesData.map(d => d.total), 1);

                salesData.forEach(city => {
                    if (city.lat && city.lng && city.total > 0) {
                        const radius = Math.sqrt(city.total / maxTotal) * 40000;

                        const circle = L.circle([city.lat, city.lng], {
                            color: '#1e40af',
                            fillColor: '#3b82f6',
                            fillOpacity: 0.6,
                            radius: radius
                        }).addTo(map);

                        const tooltipContent = `
                            <div class="p-1 font-sans">
                                <b class="text-sm">${city.name}</b><br/>
                                <span class="text-blue-600 font-bold">${new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(city.total)}</span><br/>
                                <span class="text-xs text-gray-500">${city.order_count} Sipariş</span>
                            </div>
                        `;

                        circle.bindTooltip(tooltipContent, {
                            sticky: true
                        });
                    }
                });

            });
        </script>
        <style>
            .custom-scrollbar::-webkit-scrollbar {
                width: 4px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #e5e7eb;
                border-radius: 10px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #d1d5db;
            }
        </style>
    @endpush
@endsection
