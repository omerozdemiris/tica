@extends('frontend.layouts.app')
@section('title', 'Siparişlerim')
@section('breadcrumb_title', 'Siparişlerim')
@section('content')
    @php
        $orders = $data->orders ?? collect();
    @endphp

    @include('frontend.parts.breadcrumb')
    <div class="bg-white border-b border-black/10">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-sm text-gray-500">Tüm sipariş geçmişinizi bu sayfadan inceleyebilirsiniz.</p>
        </div>
    </div>

    <section class="py-6">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-black/10 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50/50 border-b border-black/10">
                            <tr class="text-left text-[11px] uppercase tracking-wider text-gray-500">
                                <th class="px-4 py-3 font-semibold">Sipariş No</th>
                                <th class="px-4 py-3 font-semibold">Tarih</th>
                                <th class="px-4 py-3 font-semibold">Durum</th>
                                <th class="px-4 py-3 text-right font-semibold">Tutar</th>
                                <th class="px-4 py-3 text-right font-semibold"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5">
                            @forelse ($orders as $order)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-4 py-3 font-semibold text-gray-900">
                                        #{{ $order->order_number }}
                                        @if ($order->returns?->isNotEmpty())
                                            <span
                                                class="ml-2 text-[11px] inline-flex items-center gap-1 px-2 py-0.5 bg-yellow-50 text-yellow-700">
                                                <i class="ri-information-line"></i>
                                                İade talebi var
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-[11px] text-gray-500">{{ $order->created_at?->format('d.m.Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-[11px] font-medium
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
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                        {{ number_format((float) $order->total, 2, ',', '.') }} ₺
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('user.orders.show', $order->id) }}"
                                            class="text-[11px] font-semibold inline-block {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white px-3 py-1.5 hover:opacity-90 transition-opacity">Detay</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-xs text-gray-500">
                                        Henüz siparişiniz bulunmuyor.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-black/10">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection
