@extends('admin.layouts.app')
@section('title', 'Müşteri Detayı')
@section('content')

    <div class="mb-6">

        <a href="{{ route('admin.customers.index') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">

            <i class="ri-arrow-left-line"></i>

            <span>Geri Dön</span>

        </a>

    </div>



    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        <div class="lg:col-span-1">

            <div class="rounded-xl border border-gray-200 dark:border-gray-800 shadow-lg p-6 mb-6">

                <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-800">

                    <div
                        class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white text-2xl font-semibold flex-shrink-0">

                        {{ $customer->initials }}

                    </div>

                    <div>

                        <h3 class="text-lg font-semibold">{{ $customer->name }}</h3>

                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->email }}</p>

                        <div class="mt-3 flex flex-wrap items-center gap-2">

                            @if ($customer->email_verified_at)
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-200">

                                    Onaylandı

                                </span>

                                <span class="text-xs text-gray-500 dark:text-gray-400">

                                    {{ $customer->email_verified_at->format('d.m.Y H:i') }}

                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200">

                                    Beklemede

                                </span>

                                <button type="button" id="resend-verification"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-medium border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                    data-url="{{ route('admin.customers.verification.send', $customer->id) }}">

                                    <i class="ri-mail-send-line"></i>

                                    <span>Doğrulama Mailini Yeniden Gönder</span>

                                </button>
                            @endif

                        </div>

                    </div>

                </div>

                <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">

                    <i class="ri-user-line"></i>

                    <span>Müşteri Bilgileri</span>

                </h2>

                <form id="customer-update" class="space-y-4">

                    @csrf

                    @method('PUT')

                    <div>

                        <label class="text-sm text-gray-600 dark:text-gray-400">Ad Soyad</label>

                        <input type="text" name="name" value="{{ $customer->name }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">

                    </div>

                    <div>

                        <label class="text-sm text-gray-600 dark:text-gray-400">E-posta</label>

                        <input type="email" name="email" value="{{ $customer->email }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">

                    </div>

                    <div>

                        <label class="text-sm text-gray-600 dark:text-gray-400">Telefon</label>

                        <input type="text" name="phone" value="{{ $customer->phone }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">

                    </div>


                    <div>

                        <label class="text-sm text-gray-600 dark:text-gray-400">Katılma Tarihi</label>

                        <p class="text-sm mt-1 text-gray-700 dark:text-gray-300">

                            {{ $customer->created_at?->format('d.m.Y H:i') }}
                        </p>

                    </div>

                    <div class="flex items-center justify-end pt-2">

                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black hover:opacity-90 transition-opacity">

                            <i class="ri-checkbox-circle-line"></i>

                            <span>Güncelle</span>

                        </button>

                    </div>

                </form>

            </div>



            <div class="rounded-xl border border-gray-200 dark:border-gray-800 shadow-lg p-6">

                <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">

                    <i class="ri-shopping-cart-line"></i>

                    <span>Sepet Durumu</span>

                </h2>

                @if ($hasCartItems && $cart)

                    <div
                        class="flex items-center justify-between bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/40 dark:to-indigo-900/40 rounded-xl p-4 border border-blue-100 dark:border-blue-900 mb-4">

                        <div>

                            <p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Aktif Sepet</p>

                            <p class="text-xs text-gray-500 dark:text-gray-400">

                                Son güncelleme: {{ $cart->updated_at?->format('d.m.Y H:i') ?? '—' }}

                            </p>

                        </div>

                        <div class="text-right">

                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $cartItemCount }} ürün</p>

                            <p class="text-xs text-gray-500 dark:text-gray-400">

                                Toplam: {{ number_format((float) $cartTotal, 2, ',', '.') }} ₺

                            </p>

                        </div>

                    </div>

                    <div class="border border-gray-100 dark:border-gray-900 rounded-lg overflow-hidden">

                        <table class="min-w-full text-xs">

                            <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-300">

                                <tr>

                                    <th class="text-left px-3 py-2 font-semibold">Ürün</th>

                                    <th class="text-left px-3 py-2 font-semibold">Varyant</th>

                                    <th class="text-right px-3 py-2 font-semibold">Adet</th>

                                    <th class="text-right px-3 py-2 font-semibold">Birim</th>

                                    <th class="text-right px-3 py-2 font-semibold">Toplam</th>

                                </tr>

                            </thead>

                            <tbody class="divide-y divide-gray-100 dark:divide-gray-900">

                                @foreach ($cart->items as $item)
                                    <tr>

                                        <td class="px-3 py-2">

                                            @if ($item->product)
                                                <a href="{{ route('admin.products.edit', $item->product_id) }}"
                                                    class="text-xs font-semibold text-indigo-600 dark:text-indigo-300 hover:underline">

                                                    {{ $item->product->title }}

                                                </a>
                                            @else
                                                <span class="text-xs text-gray-500">Silinmiş ürün</span>
                                            @endif

                                        </td>

                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">

                                            @if ($item->variant)
                                                {{ ($item->variant->attribute->name ?? 'Varyant') . ': ' . ($item->variant->term->name ?? '') }}
                                            @else
                                                —
                                            @endif

                                        </td>

                                        <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-200 font-semibold">

                                            {{ $item->quantity }}

                                        </td>

                                        <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-300">

                                            {{ number_format((float) $item->price, 2, ',', '.') }} ₺

                                        </td>

                                        <td class="px-3 py-2 text-right text-gray-800 dark:text-gray-100 font-semibold">

                                            {{ number_format((float) $item->subtotal, 2, ',', '.') }} ₺

                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>

                    </div>
                @else
                    <div class="text-center py-8">

                        <div
                            class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-3">

                            <i class="ri-shopping-bag-3-line text-2xl text-gray-400 font-light opacity-50"></i>

                        </div>

                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Sepet Boş</p>

                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Bu müşterinin aktif sepeti bulunmuyor.</p>

                    </div>

                @endif

            </div>

        </div>



        <div class="lg:col-span-2">

            <div class="rounded-xl border border-gray-200 dark:border-gray-800 shadow-lg p-6 mb-6">

                <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">

                    <i class="ri-bar-chart-line"></i>

                    <span>Sipariş Satın Alma Grafiği</span>

                </h2>

                <div id="chart-purchases" class="h-64"></div>

            </div>



            <div class="rounded-xl border border-gray-200 dark:border-gray-800 shadow-lg p-6">

                <div class="flex items-center justify-between mb-4">

                    <h2 class="text-lg font-semibold flex items-center gap-2">

                        <i class="ri-list-check"></i>

                        <span>Geçmiş Siparişler</span>

                    </h2>

                    <a href="{{ route('admin.orders.customer', $customer->id) }}"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">

                        <i class="ri-eye-line"></i>

                        <span>Tümünü Gör</span>

                    </a>

                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full text-sm">

                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">

                            <tr>

                                <th class="text-left px-4 py-3 font-semibold">Sipariş No</th>

                                <th class="text-left px-4 py-3 font-semibold">Tarih</th>

                                <th class="text-left px-4 py-3 font-semibold">Durum</th>

                                <th class="text-right px-4 py-3 font-semibold">Tutar</th>

                                <th class="text-right px-4 py-3 font-semibold">İşlemler</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($orders as $order)
                                <tr
                                    class="border-t border-gray-100 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">

                                    <td class="px-4 py-3 font-medium">{{ $order->order_number }}</td>

                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">

                                        {{ $order->created_at->format('d.m.Y H:i') }}
                                    </td>

                                    <td class="px-4 py-3">

                                        @if ($order->status === 'new')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Yeni</span>
                                        @elseif($order->status === 'pending')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Beklemede</span>
                                        @elseif($order->status === 'completed')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Tamamlandı</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">İptal</span>
                                        @endif

                                    </td>

                                    <td class="px-4 py-3 text-right font-semibold">

                                        {{ number_format($order->total, 2, ',', '.') }} ₺
                                    </td>

                                    <td class="px-4 py-3 text-right">

                                        <a href="{{ route('admin.orders.show', $order->id) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">

                                            <i class="ri-eye-line"></i>

                                            <span>Detay</span>

                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">Sipariş

                                        bulunmamaktadır.

                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>



    @push('head')
        <script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>
    @endpush

    @push('scripts')
        <script>
            (function() {

                const ec = window.echarts;

                const chartEl = document.getElementById('chart-purchases');



                if (chartEl && ec) {

                    const chart = ec.init(chartEl, null, {

                        renderer: 'svg'

                    });

                    const data = @json($purchaseChartData);



                    chart.setOption({

                        title: {

                            text: 'Son 6 Ay Sipariş Grafiği',

                            left: 'center',

                            textStyle: {

                                fontSize: 14,

                                fontWeight: 'bold'

                            }

                        },

                        tooltip: {

                            trigger: 'axis',

                            axisPointer: {

                                type: 'shadow'

                            }

                        },

                        grid: {

                            left: '3%',

                            right: '4%',

                            bottom: '3%',

                            containLabel: true

                        },

                        xAxis: {

                            type: 'category',

                            data: data.labels,

                            axisLine: {

                                lineStyle: {

                                    color: '#e0e0e0'

                                }

                            }

                        },

                        yAxis: {

                            type: 'value',

                            axisLine: {

                                lineStyle: {

                                    color: '#e0e0e0'

                                }

                            }

                        },

                        series: [{

                            name: 'Sipariş Sayısı',

                            type: 'bar',

                            data: data.values,

                            itemStyle: {

                                color: new ec.graphic.LinearGradient(0, 0, 0, 1, [{

                                    offset: 0,

                                    color: '#8b5cf6'

                                }, {

                                    offset: 1,

                                    color: '#3b82f6'

                                }]),

                                borderRadius: [4, 4, 0, 0]

                            },

                            emphasis: {

                                itemStyle: {

                                    color: new ec.graphic.LinearGradient(0, 0, 0, 1, [{

                                        offset: 0,

                                        color: '#7c3aed'

                                    }, {

                                        offset: 1,

                                        color: '#2563eb'

                                    }])

                                }

                            }

                        }]

                    });

                }



                const resendBtn = $('#resend-verification');

                if (resendBtn.length) {

                    resendBtn.on('click', function(e) {

                        e.preventDefault();

                        const $btn = $(this);

                        const url = $btn.data('url');

                        if (!url) return;

                        $btn.prop('disabled', true).addClass('opacity-60');

                        $.ajax({

                            url: url,

                            method: "POST",

                            data: {

                                _token: "{{ csrf_token() }}"

                            },

                            success: function(res) {

                                showSuccess(res?.msg || 'Doğrulama e-postası gönderildi');

                            },

                            error: function(xhr) {

                                const msg = xhr.responseJSON?.msg || 'Hata';

                                showError(msg);

                            },

                            complete: function() {

                                $btn.prop('disabled', false).removeClass('opacity-60');

                            }

                        });

                    });

                }



                $('#customer-update').on('submit', function(e) {

                    e.preventDefault();

                    $.ajax({

                        url: "{{ route('admin.customers.update', $customer->id) }}",

                        method: "POST",

                        data: $(this).serialize(),

                        success: function(res) {

                            showSuccess(res?.msg || 'Güncellendi');

                        },

                        error: function(xhr) {

                            showError(xhr.responseJSON?.msg || 'Hata');

                        }

                    });

                });

            })();
        </script>
    @endpush

@endsection
