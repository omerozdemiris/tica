@extends('admin.layouts.app')
@section('title', 'Satış Raporları')
@section('content')
    <div class="p-4 pt-0 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Satış Raporları ve Analiz</h1>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <form action="{{ route('admin.sales-reports.index') }}" method="GET"
                class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
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
                        <i class="ri-box-3-line"></i> Ürün Filtrele
                    </label>
                    <select name="product_id" id="product_select"
                        class="w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tümü</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}"
                                {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->title }}</option>
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
                                {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1 flex items-center gap-1">
                        <i class="ri-bank-card-line"></i> Ödeme Yöntemi
                    </label>
                    <select name="payment_method" id="payment_select"
                        class="w-full rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tümü</option>
                        @foreach ($paymentMethods as $method)
                            @php
                                $methodLabel = match (strtoupper($method)) {
                                    'WIRE' => 'Havale/EFT',
                                    'CARD', 'CREDIT_CARD' => 'Kredi/Banka Kartı',
                                    'CASH' => 'Kapıda Ödeme',
                                    default => strtoupper($method),
                                };
                            @endphp
                            <option value="{{ $method }}"
                                {{ request('payment_method') == $method ? 'selected' : '' }}>{{ $methodLabel }}
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
                <div class="flex items-end gap-2 lg:col-span-6">
                    <button type="submit"
                        class="bg-blue-600 text-white rounded-lg px-6 py-2.5 text-sm font-medium hover:bg-blue-700 transition-colors flex items-center gap-2 shadow-sm">
                        <i class="ri-filter-3-line"></i> Filtrele
                    </button>
                    <a href="{{ route('admin.sales-reports.index') }}"
                        class="bg-gray-100 text-gray-600 rounded-lg px-6 py-2.5 text-sm font-medium hover:bg-gray-200 transition-colors flex items-center gap-2">
                        <i class="ri-refresh-line"></i> Sıfırla
                    </a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                    <i class="ri-money-dollar-circle-line ri-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Toplam Satış Tutarı</p>
                    <p class="text-3xl font-bold text-blue-600 mt-1">
                        {{ number_format($totals->total_amount, 2, ',', '.') }}
                        TL</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600">
                    <i class="ri-shopping-bag-line ri-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Satılan Ürün Adeti</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">
                        {{ number_format($totals->total_qty, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">
                    <i class="ri-bill-line ri-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Toplam Sipariş</p>
                    <p class="text-3xl font-bold text-purple-600 mt-1">{{ $totals->total_orders }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-orange-600">
                    <i class="ri-line-chart-line ri-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Ortalama Sipariş Tutarı</p>
                    <p class="text-3xl font-bold text-orange-600 mt-1">
                        {{ number_format($totals->total_orders > 0 ? $totals->total_amount / $totals->total_orders : 0, 2, ',', '.') }}
                        TL
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-gray-600">
                <i class="ri-pulse-line ri-lg"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-800 uppercase tracking-tight">Dönüşüm Hunisi ve Analizi</h2>
                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-widest">Kullanıcı alışkanlıkları ve
                    dönüşüm metrikleri</p>
            </div>
        </div>

        {{-- Dönüşüm Oranları --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative group">
                <div class="absolute top-0 right-0 p-3">
                    <div class="group/info relative">
                        <i class="ri-information-line text-gray-400 cursor-help text-xs"></i>
                        <div
                            class="absolute bottom-full right-0 z-[100] mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded shadow-xl opacity-0 group-hover/info:opacity-100 transition-opacity pointer-events-none">
                            Siteyi ziyaret eden tekil kullanıcıların yüzde kaçının sepetine ürün eklediğini
                            gösterir.
                            <div class="absolute top-full right-2 border-8 border-transparent border-t-gray-900">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div
                        class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 flex-shrink-0">
                        <i class="ri-user-follow-line ri-xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-sm text-gray-500 font-medium">Ziyaretçi → Sepet</p>
                            <div class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 leading-none">
                            %{{ number_format($conversions->visitor_to_cart, 2) }}
                        </p>
                        <div class="mt-4 flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-blue-500 h-full rounded-full transition-all duration-1000"
                                    style="width: {{ min(100, $conversions->visitor_to_cart) }}%"></div>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400">{{ $conversions->counts['visitors'] }}
                                Ziyaretçi</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative group">
                <div class="absolute top-0 right-0 p-3">
                    <div class="group/info relative">
                        <i class="ri-information-line text-gray-400 cursor-help text-xs"></i>
                        <div
                            class="absolute bottom-full right-0 z-[100] mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded shadow-xl opacity-0 group-hover/info:opacity-100 transition-opacity pointer-events-none">
                            Oluşturulan sepetlerin yüzde kaçının başarıyla siparişe dönüştüğünü gösterir.
                            <div class="absolute top-full right-2 border-8 border-transparent border-t-gray-900">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div
                        class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600 flex-shrink-0">
                        <i class="ri-shopping-cart-2-line ri-xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-sm text-gray-500 font-medium">Sepet → Sipariş</p>
                            <div class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 leading-none">
                            %{{ number_format($conversions->cart_to_order, 2) }}
                        </p>
                        <div class="mt-4 flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-green-500 h-full rounded-full transition-all duration-1000"
                                    style="width: {{ min(100, $conversions->cart_to_order) }}%"></div>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400">{{ $conversions->counts['carts'] }}
                                Sepet</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative group">
                <div class="absolute top-0 right-0 p-3">
                    <div class="group/info relative">
                        <i class="ri-information-line text-gray-400 cursor-help text-xs"></i>
                        <div
                            class="absolute bottom-full right-0 z-[100] mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded shadow-xl opacity-0 group-hover/info:opacity-100 transition-opacity pointer-events-none">
                            Ziyaretçilerin doğrudan siparişe dönüşme oranını gösterir. (Sipariş / Ziyaretçi)
                            <div class="absolute top-full right-2 border-8 border-transparent border-t-gray-900">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div
                        class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600 flex-shrink-0">
                        <i class="ri-flashlight-line ri-xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-sm text-gray-500 font-medium">Genel Dönüşüm Oranı</p>
                            <div class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-purple-500"></span>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 leading-none">
                            %{{ number_format($conversions->visitor_to_order, 2) }}
                        </p>
                        <div class="mt-4 flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-purple-500 h-full rounded-full transition-all duration-1000"
                                    style="width: {{ min(100, $conversions->visitor_to_order) }}%"></div>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400">{{ $conversions->counts['orders'] }}
                                Sipariş</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="ri-bar-chart-line text-blue-500"></i>
                    Günlük Satış Trendi
                </h3>
                <div id="salesOverTimeChart" style="height: 400px;"></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="ri-pie-chart-2-line text-purple-500"></i>
                    Kategori Bazlı Dağılım
                </h3>
                <div id="categoryChart" style="height: 400px;"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 lg:col-span-2">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="ri-list-check text-blue-500"></i>
                    Son Satışlar
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-xs text-gray-500 border-b border-gray-50 uppercase tracking-wider">
                                <th class="pb-3 font-medium">Sipariş</th>
                                <th class="pb-3 font-medium">Ürün</th>
                                <th class="pb-3 font-medium">Müşteri</th>
                                <th class="pb-3 font-medium text-center">Adet</th>
                                <th class="pb-3 font-medium text-right">Tutar</th>
                                <th class="pb-3 font-medium text-right">Tarih</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @foreach ($sales as $sale)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4">
                                        <a href="{{ route('admin.orders.show', $sale->order_id) }}"
                                            class="font-medium text-blue-600 hover:underline">
                                            #{{ $sale->order->order_number ?? 'SİPARİŞ YOK' }}
                                        </a>
                                    </td>
                                    <td class="py-4">
                                        <div class="flex flex-col">
                                            <a href="{{ route('products.show', [$sale->product_id, Str::slug($sale->product->title ?? 'urun')]) }}"
                                                target="_blank"
                                                class="font-medium text-gray-900 hover:text-blue-600 hover:underline transition-colors flex items-center gap-1">
                                                {{ $sale->product->title ?? 'Bilinmeyen Ürün' }}
                                                <i class="ri-external-link-line text-xs opacity-50"></i>
                                            </a>
                                            <span
                                                class="text-xs text-gray-500">{{ $sale->product?->categories?->first()?->name ?? 'Kategorisiz' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 text-gray-600">
                                        {{ $sale->order->customer_name ?? 'Bilinmeyen Müşteri' }}</td>
                                    <td class="py-4 text-center">{{ $sale->quantity }}</td>
                                    <td class="py-4 text-right font-semibold text-gray-900">
                                        {{ number_format($sale->total, 2, ',', '.') }} TL</td>
                                    <td class="py-4 text-right text-gray-500">{{ $sale->created_at->format('d.m.Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $sales->links() }}
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="ri-fire-line text-red-500"></i>
                    En Çok Satan 10 Ürün
                </h3>
                <div class="space-y-4">
                    @foreach ($topProducts as $top)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <div class="flex flex-col">
                                <a href="{{ route('products.show', [$top->id, Str::slug($top->title)]) }}"
                                    target="_blank"
                                    class="text-sm font-semibold text-gray-900 truncate max-w-[250px] hover:underline hover:text-blue-600 transition-colors flex items-center gap-1">
                                    {{ $top->title }}
                                    <i class="ri-external-link-line text-xs opacity-50"></i>
                                </a>
                                <span class="text-xs text-gray-500">{{ $top->total_qty }} Adet Satıldı</span>
                            </div>
                            <span
                                class="text-sm font-bold text-blue-600">{{ number_format($top->total_amount, 2, ',', '.') }}
                                TL</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof TomSelect !== 'undefined') {
                new TomSelect('#product_select', {
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
                new TomSelect('#payment_select', {
                    create: false,
                    sortField: {
                        field: 'text',
                        direction: 'asc'
                    }
                });
            }

            // Trend Chart
            var trendChart = echarts.init(document.getElementById('salesOverTimeChart'));

            const formatDate = (dateStr) => {
                const date = new Date(dateStr);
                return date.toLocaleDateString('tr-TR', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
            };

            const formatCurrency = (value) => {
                return new Intl.NumberFormat('tr-TR', {
                    style: 'currency',
                    currency: 'TRY'
                }).format(value);
            };

            trendChart.setOption({
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    textStyle: {
                        color: '#1f2937'
                    },
                    formatter: function(params) {
                        let res =
                            `<div style="font-weight: 700; margin-bottom: 4px; color: #374151;">${formatDate(params[0].name)}</div>`;
                        params.forEach(item => {
                            res += `<div style="display: flex; align-items: center; justify-content: space-between; min-width: 150px;">
                                <span style="display: flex; align-items: center; gap: 6px; font-size: 13px;">
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background-color:${item.color};"></span>
                                    Satış Tutarı:
                                </span>
                                <span style="font-weight: 700; font-size: 13px; margin-left: 12px;">${formatCurrency(item.value)}</span>
                            </div>`;
                        });
                        return res;
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '10%',
                    top: '5%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: {!! json_encode($salesOverTime->pluck('date')) !!},
                    axisLabel: {
                        formatter: (val) => {
                            const date = new Date(val);
                            return date.toLocaleDateString('tr-TR', {
                                day: 'numeric',
                                month: 'short'
                            });
                        },
                        color: '#6b7280',
                        fontSize: 11
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#e5e7eb'
                        }
                    }
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: (val) => val.toLocaleString('tr-TR') + ' TL',
                        color: '#6b7280',
                        fontSize: 11
                    },
                    splitLine: {
                        lineStyle: {
                            type: 'dashed',
                            color: '#f3f4f6'
                        }
                    }
                },
                series: [{
                    name: 'Satış Tutarı',
                    data: {!! json_encode($salesOverTime->pluck('total')) !!},
                    type: 'line',
                    smooth: true,
                    showSymbol: true,
                    symbolSize: 6,
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                offset: 0,
                                color: 'rgba(37, 99, 235, 0.2)'
                            },
                            {
                                offset: 1,
                                color: 'rgba(37, 99, 235, 0)'
                            }
                        ])
                    },
                    lineStyle: {
                        color: '#2563eb',
                        width: 3
                    },
                    itemStyle: {
                        color: '#2563eb'
                    }
                }]
            });

            // Category Chart
            var categoryChart = echarts.init(document.getElementById('categoryChart'));
            categoryChart.setOption({
                tooltip: {
                    trigger: 'item',
                    formatter: (params) => {
                        return `<div style="font-weight: 700;">${params.name}</div>
                                <div style="margin-top: 4px;">${formatCurrency(params.value)} <span style="color: #6b7280; font-size: 11px;">(${params.percent}%)</span></div>`;
                    }
                },
                legend: {
                    bottom: '0',
                    left: 'center',
                    icon: 'circle',
                    textStyle: {
                        fontSize: 11
                    }
                },
                series: [{
                    type: 'pie',
                    radius: ['45%', '70%'],
                    avoidLabelOverlap: false,
                    itemStyle: {
                        borderRadius: 10,
                        borderColor: '#fff',
                        borderWidth: 2
                    },
                    label: {
                        show: false
                    },
                    data: {!! json_encode($salesByCategory->map(fn($item) => ['value' => $item->total, 'name' => $item->name])) !!},
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            });

            window.addEventListener('resize', function() {
                trendChart.resize();
                categoryChart.resize();
            });
        });
    </script>
@endsection
