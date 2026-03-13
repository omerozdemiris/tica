@extends('admin.layouts.app')
@section('title', 'Trafik Yönetimi')
@section('content')
    <div class="p-5 pt-0 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Trafik Yönetimi ve Analiz</h1>
        </div>

        <!-- 1. Üst Özet Widget Grubu -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 relative group">
                <div class="absolute top-2 right-2 text-gray-300 hover:text-gray-500 transition-colors cursor-help"
                    data-tooltip-target="tooltip-total-visit" data-tooltip-placement="top">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-total-visit" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-150 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Sitenize gelen toplam tekil ziyaretçi sayısı.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                    <i class="ri-group-line ri-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium leading-none">Toplam Ziyaret</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $dailyTraffic->sum('count') }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 relative group">
                <div class="absolute top-2 right-2 text-gray-300 hover:text-gray-500 transition-colors cursor-help"
                    data-tooltip-target="tooltip-sms-return" data-tooltip-placement="top">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-sms-return" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-150 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    SMS yoluyla gelen linklere tıklayarak siteye ulaşan kullanici sayısı.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600">
                    <i class="ri-chat-smile-2-line ri-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium leading-none">SMS Dönüşü</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $sourceData->where('source', 'sms')->first()?->count ?? 0 }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 relative group">
                <div class="absolute top-2 right-2 text-gray-300 hover:text-gray-500 transition-colors cursor-help"
                    data-tooltip-target="tooltip-email-return" data-tooltip-placement="top">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-email-return" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-150 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    E-posta bültenleri veya bildirimleri üzerinden gelen kullanici sayısı.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">
                    <i class="ri-mail-send-line ri-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium leading-none">Email Dönüşü</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">
                        {{ $sourceData->where('source', 'email')->first()?->count ?? 0 }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex items-center gap-4 relative group">
                <div class="absolute top-2 right-2 text-gray-300 hover:text-gray-500 transition-colors cursor-help"
                    data-tooltip-target="tooltip-other-source" data-tooltip-placement="top">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-other-source" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-150 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Direkt giriş, arama motorlari veya diğer kaynaklardan gelen trafik.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center text-gray-600">
                    <i class="ri-global-line ri-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium leading-none">Diğer</p>
                    <p class="text-3xl font-bold text-gray-600 mt-2">
                        {{ $sourceData->whereNotIn('source', ['sms', 'email'])->sum('count') }}</p>
                </div>
            </div>
        </div>

        <!-- 2. Orta Analiz Widget Grubu -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- 5 İl Analizi -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 relative group">
                <div class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition-colors cursor-help"
                    data-tooltip-target="tooltip-top-cities" data-tooltip-placement="top">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-top-cities" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-150 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    En çok etkileşim alan ilk 5 şehir (Müşteri adres verileri baz alinmiştir).
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2 uppercase tracking-wider">
                    <i class="ri-map-pin-2-line text-red-500"></i>
                    Yoğun Ziyaret Alan İller
                </h3>
                <div class="space-y-3">
                    @foreach ($topCities as $city)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ $city->city }}</span>
                            <span
                                class="text-xs font-bold bg-red-50 text-red-600 px-2 py-0.5 rounded-full">{{ $city->count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Tekrar Ziyaret Edilen Ürünler -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 relative group">
                <div class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition-colors cursor-help"
                    data-tooltip-target="tooltip-repeat-products" data-tooltip-placement="top">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-repeat-products" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-150 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    En çok tekrar tekrar ziyaret edilen ve ilgi gören ilk 5 ürün.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2 uppercase tracking-wider">
                    <i class="ri-refresh-line text-blue-500"></i>
                    En Çok İlgi Gören Ürünler
                </h3>
                <div class="space-y-3">
                    @foreach ($topRepeatProducts as $prod)
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ $prod['url'] }}" target="_blank"
                                class="text-xs text-gray-600 truncate hover:text-blue-600">{{ $prod['title'] }}</a>
                            <span
                                class="text-xs font-bold bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full shrink-0">{{ $prod['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- SMS/Email Dönüş Ürünleri -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 relative group">
                <div class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition-colors cursor-help"
                    data-tooltip-target="tooltip-conversion-products" data-tooltip-placement="top">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-conversion-products" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-150 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    SMS veya E-posta kampanyalari üzerinden en çok tıklanan ve dönüş sağlayan ürünler.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2 uppercase tracking-wider">
                    <i class="ri-links-line text-green-500"></i>
                    Kampanya Dönüş Hedefleri
                </h3>
                <div class="space-y-3">
                    @foreach ($conversionTargets->take(5) as $conv)
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex flex-col min-w-0">
                                <a href="{{ $conv['url'] }}" target="_blank"
                                    class="text-[11px] text-gray-600 truncate hover:text-blue-600">{{ $conv['title'] }}</a>
                                <span class="text-[9px] uppercase font-bold text-gray-400">{{ $conv['source'] }}</span>
                            </div>
                            <span
                                class="text-xs font-bold bg-green-50 text-green-600 px-2 py-0.5 rounded-full shrink-0">{{ $conv['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 3. Grafikler Alt Kısma -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 relative group">
                <div class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition-colors cursor-help"
                    data-tooltip-target="tooltip-traffic-chart" data-tooltip-placement="top">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-traffic-chart" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-150 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Son 15 gün içindeki günlük ziyaretçi trafiği değişimi.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="ri-line-chart-line text-blue-500"></i>
                    Son 15 Günlük Trafik
                </h3>
                <div id="trafficChart" style="height: 400px;"></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 relative group">
                <div class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition-colors cursor-help"
                    data-tooltip-target="tooltip-source-chart" data-tooltip-placement="top">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-source-chart" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-150 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Ziyaretçilerin siteye hangi kanallar (Direct, SMS, E-posta vb.) üzerinden geldiğinin dağılımı.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="ri-pie-chart-2-line text-purple-500"></i>
                    Kaynak Dağılımı
                </h3>
                <div id="sourceChart" style="height: 400px;"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 relative group">
                <div class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition-colors cursor-help"
                    data-tooltip-target="tooltip-device-chart" data-tooltip-placement="top">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-device-chart" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-150 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Kullanicilarin siteye hangi cihaz tipleri (Mobil, Masaüstü) ile girdiğinin oranı.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="ri-device-line text-orange-500"></i>
                    Cihaz Dağılımı
                </h3>
                <div id="deviceChart" style="height: 300px;"></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 relative group">
                <div class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition-colors cursor-help"
                    data-tooltip-target="tooltip-platform-chart" data-tooltip-placement="top">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-platform-chart" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-150 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Kullanicilarin işletim sistemi (Windows, iOS, Android vb.) dağılımı.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="ri-window-line text-indigo-500"></i>
                    Platform Dağılımı
                </h3>
                <div id="platformChart" style="height: 300px;"></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 relative group">
                <div class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition-colors cursor-help"
                    data-tooltip-target="tooltip-top-targets" data-tooltip-placement="top">
                    <i class="ri-information-line"></i>
                </div>
                <div id="tooltip-top-targets" role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-xs font-medium text-white transition-opacity duration-150 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    Site genelinde en çok ziyaret edilen sayfalar, ürünler ve kategoriler.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="ri-fire-line text-red-500"></i>
                    En çok gösterilenler
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-sm text-gray-500 border-b border-gray-50">
                                <th class="pb-3">Hedef</th>
                                <th class="pb-3 text-right">Ziyaret</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @foreach ($topTargets as $target)
                                <tr class="border-b border-gray-50">
                                    <td class="py-3">
                                        @php
                                            $targetLabel = match ($target->target_type) {
                                                'product' => 'Ürün',
                                                'category' => 'Kategori',
                                                'home' => 'Anasayfa',
                                                default => $target->target_type,
                                            };
                                        @endphp
                                        <div class="flex items-center">
                                            <span
                                                class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-[10px] font-bold uppercase mr-3 shrink-0">{{ $targetLabel }}</span>
                                            <a href="{{ $target->url }}" target="_blank"
                                                class="text-gray-700 hover:text-blue-600 hover:underline flex items-center gap-1 group transition-colors">
                                                <span class="truncate max-w-[250px]">{{ $target->title }}</span>
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3.5 w-3.5 opacity-0 group-hover:opacity-100 transition-opacity text-blue-500"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="py-3 text-right font-semibold text-gray-900">{{ $target->count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var trafficChart = echarts.init(document.getElementById('trafficChart'));
            trafficChart.setOption({
                tooltip: {
                    trigger: 'axis'
                },
                xAxis: {
                    type: 'category',
                    data: {!! json_encode($dailyTraffic->pluck('date')) !!}
                },
                yAxis: {
                    type: 'value'
                },
                series: [{
                    data: {!! json_encode($dailyTraffic->pluck('count')) !!},
                    type: 'line',
                    smooth: true,
                    areaStyle: {
                        color: 'rgba(37, 99, 235, 0.1)'
                    },
                    lineStyle: {
                        color: '#2563eb'
                    },
                    itemStyle: {
                        color: '#2563eb'
                    }
                }]
            });

            // Source Chart
            var sourceChart = echarts.init(document.getElementById('sourceChart'));
            sourceChart.setOption({
                tooltip: {
                    trigger: 'item'
                },
                legend: {
                    bottom: '0',
                    left: 'center'
                },
                series: [{
                    type: 'pie',
                    radius: ['40%', '70%'],
                    avoidLabelOverlap: false,
                    itemStyle: {
                        borderRadius: 10,
                        borderColor: '#fff',
                        borderWidth: 2
                    },
                    label: {
                        show: false
                    },
                    data: {!! json_encode(
                        $sourceData->groupBy(function ($item) {
                                return match ($item->source) {
                                    'sms' => 'SMS',
                                    'email' => 'E-POSTA',
                                    'direct' => 'DİREKT',
                                    default => 'DİĞER',
                                };
                            })->map(function ($items, $label) {
                                return ['value' => $items->sum('count'), 'name' => $label];
                            })->values(),
                    ) !!}
                }]
            });

            // Device Chart
            var deviceChart = echarts.init(document.getElementById('deviceChart'));
            deviceChart.setOption({
                tooltip: {
                    trigger: 'item'
                },
                legend: {
                    bottom: '0',
                    left: 'center'
                },
                series: [{
                    type: 'pie',
                    radius: '70%',
                    data: {!! json_encode(
                        $deviceData->map(function ($item) {
                            $label = match ($item->device) {
                                'mobile' => 'MOBİL',
                                'desktop' => 'MASAÜSTÜ',
                                default => 'DİĞER',
                            };
                            return ['value' => $item->count, 'name' => $label];
                        }),
                    ) !!},
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            });

            // Platform Chart
            var platformChart = echarts.init(document.getElementById('platformChart'));
            platformChart.setOption({
                tooltip: {
                    trigger: 'item'
                },
                legend: {
                    bottom: '0',
                    left: 'center'
                },
                series: [{
                    type: 'pie',
                    radius: '70%',
                    data: {!! json_encode(
                        $platformData->map(function ($item) {
                            $label = match ($item->platform) {
                                'windows' => 'WINDOWS',
                                'android' => 'ANDROID',
                                'ios' => 'IOS',
                                'linux' => 'LINUX',
                                'macos' => 'MACOS',
                                default => 'DİĞER',
                            };
                            return ['value' => $item->count, 'name' => $label];
                        }),
                    ) !!},
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
                trafficChart.resize();
                sourceChart.resize();
                deviceChart.resize();
                platformChart.resize();
            });
        });
    </script>
@endpush
