@extends('admin.layouts.app')

@section('title', 'Teslimat Siparişleri')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-lg font-semibold">{{ $company->name }} - Tamamlanan Siparişler</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400">Bu kargo firması ile teslim edilmiş siparişlerin listesi.</p>
        </div>
        <a href="{{ route('admin.shipping-companies.index') }}"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 text-sm">
            <i class="ri-arrow-left-line"></i> Teslimat Ayarlarına Dön
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold">Sipariş No</th>
                    <th class="text-left px-4 py-3 font-semibold">Müşteri</th>
                    <th class="text-left px-4 py-3 font-semibold">Teslimat</th>
                    <th class="text-left px-4 py-3 font-semibold">Takip</th>
                    <th class="text-right px-4 py-3 font-semibold">Tutar</th>
                    <th class="text-right px-4 py-3 font-semibold">Tarih</th>
                    <th class="text-right px-4 py-3 font-semibold">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-t border-gray-100 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-900">
                        <td class="px-4 py-3 font-semibold">{{ $order->order_number }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <span>{{ $order->user->name }}</span>
                                <span class="text-xs text-gray-500">{{ $order->user->email }}</span>
                                @if ($order->user->phone)
                                    <span class="text-xs text-gray-500">{{ $order->user->phone }}</span>
                                @endif
                                @php
                                    $orderTc = $order->shippingAddress?->tc ?? $order->billingAddress?->tc;
                                @endphp
                                @if ($orderTc)
                                    <span class="text-xs text-gray-500">TC: {{ $orderTc }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $order->shipping->shipping_address ?? '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if ($order->shipping && $order->shipping->tracking_no)
                                <div class="flex flex-col text-xs">
                                    <span class="text-gray-600 dark:text-gray-300">No:
                                        {{ $order->shipping->tracking_no }}</span>
                                    @if ($order->shipping->tracking_link)
                                        <a href="{{ $order->shipping->tracking_link }}" target="_blank" rel="noopener"
                                            class="text-blue-500 hover:text-blue-600">Takip bağlantısı</a>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400">Bilgi yok</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">
                            {{ number_format($order->total, 2, ',', '.') }} ₺
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400">
                            {{ $order->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.orders.show', $order->id) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 text-xs">
                                <i class="ri-eye-line"></i> Detay
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            Bu kargo firmasıyla tamamlanan sipariş bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
