@extends('frontend.layouts.app')
@section('title', 'Siparişlerim')
@section('breadcrumb_title', 'Siparişlerim')
@section('content')
    @php
        $orders = $data->orders ?? collect();
    @endphp

    @include('frontend.parts.breadcrumb')
    <div class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-sm text-gray-500">Tüm sipariş geçmişinizi bu sayfadan inceleyebilirsiniz.</p>
        </div>
    </div>

    <section class="py-10">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 bg-gray-50 border border-gray-200 rounded-2xl shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="px-6 py-3">Sipariş No</th>
                            <th class="px-6 py-3">Tarih</th>
                            <th class="px-6 py-3">Durum</th>
                            <th class="px-6 py-3 text-right">Tutar</th>
                            <th class="px-6 py-3 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    #{{ $order->order_number }}
                                    @if ($order->returns?->isNotEmpty())
                                        <span
                                            class="ml-2 text-[11px] inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-700">
                                            <i class="ri-information-line"></i>
                                            İade talebi var
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $order->created_at?->format('d.m.Y H:i') }}</td>
                                <td class="px-6 py-4">
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
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                    {{ number_format((float) $order->total, 2, ',', '.') }} ₺
                                </td>
                                <td class="px-6 py-4 text-right flex flex-col items-end gap-2">
                                    <a href="{{ route('user.orders.show', $order->id) }}"
                                        class="text-sm font-semibold {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white px-4 py-2 rounded-full hover:{{ $theme->color ? 'bg-' . $theme->color . '/30' : 'bg-blue-700' }} hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-700' }} transition-colors">Detay</a>
                                    {{-- <a href="{{ route('returns.order', $order->id) }}"
                                        class="text-xs font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} hover:{{ $theme->color ? 'text-' . $theme->color : 'text-blue-700' }} transition-colors">İade
                                        Talebi</a> --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                    Henüz siparişiniz bulunmuyor.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $orders->links() }}
            </div>
        </div>
    </section>
@endsection
