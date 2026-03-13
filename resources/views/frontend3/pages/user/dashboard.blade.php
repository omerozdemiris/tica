@extends($template . '.layouts.app')
@section('title', 'Hesabım')
@section('breadcrumb_title', 'Hesabım')
@section('content')
    @php
        $statistics = $data->statistics ?? (object) [];
        $orders = $data->orders ?? collect();
        $quickActions = [
            [
                'label' => 'Siparişlerim',
                'description' => 'Tüm sipariş geçmişinizi görüntüleyin.',
                'icon' => 'ri-shopping-bag-3-line',
                'url' => route('user.orders'),
            ],
            [
                'label' => 'Adres Defteri',
                'description' => 'Teslimat ve fatura adreslerinizi yönetin.',
                'icon' => 'ri-map-pin-2-line',
                'url' => route('user.addresses'),
            ],
            [
                'label' => 'Profil Bilgileri',
                'description' => 'Kişisel bilgilerinizi ve şifrenizi güncelleyin.',
                'icon' => 'ri-user-settings-line',
                'url' => route('user.profile'),
            ],
            [
                'label' => 'İade / Değişim',
                'description' => 'İade taleplerinizi başlatın ve takip edin.',
                'icon' => 'ri-refund-2-line',
                'url' => route('returns.lookup'),
            ],
        ];
    @endphp
    @include($template . '.parts.breadcrumb')
    <div class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-sm text-gray-500">Merhaba {{ auth()->user()->name }}, hesap özetinizi ve son siparişlerinizi
                buradan takip edebilirsiniz.</p>
        </div>
    </div>

    <section class="py-10">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($quickActions as $action)
                    <a href="{{ $action['url'] }}"
                        class="group flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div
                            class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl text-white {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }}">
                            <i class="{{ $action['icon'] }}"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-base font-semibold text-gray-900">{{ $action['label'] }}</p>
                            <p class="text-sm text-gray-500">{{ $action['description'] }}</p>
                        </div>
                        <i
                            class="ri-arrow-right-line text-lg text-gray-400 {{ $theme->color ? 'group-hover:text-' . $theme->color : 'group-hover:text-blue-600' }}"></i>
                    </a>
                @endforeach
            </div>

            <div class="md:hidden">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 rounded-2xl border {{ $theme->color ? 'border-' . $theme->color : 'border-red-200' }} {{ $theme->color ? 'bg-' . $theme->color . '/5' : 'bg-red-50' }} p-4 {{ $theme->color ? 'text-' . $theme->color : 'text-red-600' }} transition hover:bg-{{ $theme->color ? 'bg-' . $theme->color . '/20' : 'bg-red-100' }} font-semibold shadow-sm">
                        <i
                            class="ri-logout-box-line text-lg {{ $theme->color ? 'text-' . $theme->color : 'text-red-600' }}"></i>
                        <span>Çıkış Yap</span>
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <p class="text-xs text-gray-500 uppercase">Toplam Sipariş</p>
                    <p class="mt-2 text-xl font-bold text-gray-900">
                        {{ number_format($statistics->total_orders ?? 0) }}
                    </p>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <p class="text-xs text-gray-500 uppercase">Tamamlanan Sipariş</p>
                    <p class="mt-2 text-xl font-bold text-gray-900">
                        {{ number_format($statistics->completed_orders ?? 0) }}
                    </p>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <p class="text-xs text-gray-500 uppercase">Toplam Harcama</p>
                    <p class="mt-2 text-xl font-bold text-gray-900">
                        {{ number_format((float) ($statistics->total_spent ?? 0), 2, ',', '.') }} ₺
                    </p>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Son Siparişler</h2>
                    <a href="{{ route('user.orders') }}"
                        class="text-sm font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-700' }} transition-colors">
                        Tüm Siparişlerim
                    </a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">#{{ $order->order_number }}</p>
                                <p class="text-xs text-gray-500">{{ $order->created_at?->format('d.m.Y H:i') }}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ number_format((float) $order->total, 2, ',', '.') }} ₺
                                </span>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                         @if ($order->status === 'completed') bg-green-100 text-green-700
                                         @elseif($order->status === 'pending') bg-yellow-100 text-yellow-700
                                         @elseif($order->status === 'canceled') bg-red-100 text-red-700
                                         @else bg-blue-100 text-blue-700 @endif">
                                    @php
                                        $statusLabels = [
                                            'new' => 'Yeni',
                                            'pending' => 'Beklemede',
                                            'completed' => 'Tamamlandı',
                                            'canceled' => 'İptal Edildi',
                                        ];
                                    @endphp
                                    {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                                </span>
                                <a href="{{ route('user.orders.show', $order->id) }}"
                                    class="text-sm font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-700' }} transition-colors">Detay</a>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm text-gray-500">
                            Henüz siparişiniz bulunmuyor.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection
