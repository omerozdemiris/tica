@extends('admin.layouts.app')

@section('title', 'İade Talebi #' . $return->id)

@php

$statusOptions = [

'pending' => 'Beklemede',

'processed' => 'İade Edildi',

'rejected' => 'Reddedildi',

];

@endphp

@section('content')

<div class="space-y-6">

    <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black p-6">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>

                <p class="text-xs text-gray-500 uppercase tracking-[0.3em]">Sipariş</p>

                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">#{{ $return->order_number }}</h1>

                <p class="text-sm text-gray-500 mt-1">

                    {{ $return->created_at?->format('d.m.Y H:i') }}

                </p>

            </div>

            <div class="flex items-center gap-3">
                <select
                    class="status-selector px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm outline-none focus:ring-1 focus:ring-black dark:focus:ring-white transition-all"
                    data-id="{{ $return->id }}" data-current="{{ $return->status }}">
                    @foreach ($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected($return->status === $key)>{{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>

        </div>

    </div>



    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black p-6 space-y-4">

            <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Müşteri Bilgileri

            </h2>

            <div class="text-sm text-gray-600 dark:text-gray-300 space-y-2">

                <p><span class="text-gray-500">Ad Soyad:</span> {{ $return->customer_name }}</p>

                <p><span class="text-gray-500">E-posta:</span> {{ $return->customer_email }}</p>

                <p><span class="text-gray-500">Telefon:</span> {{ $return->customer_phone ?? '—' }}</p>

            </div>

        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black p-6 space-y-4">

            <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">İade Bilgileri</h2>

            <div class="text-sm text-gray-600 dark:text-gray-300 space-y-2">

                <p><span class="text-gray-500">Sebep:</span> {{ $return->reason ?? 'Belirtilmemiş' }}</p>

                <p><span class="text-gray-500">Not:</span> {{ $return->notes ?? '—' }}</p>

            </div>

        </div>

    </div>



    <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">

        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800">

            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">İade Edilen Ürünler

            </h3>

        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-800">

            @foreach ($return->items as $item)

            @php

            $orderItem = $item->orderItem;

            @endphp

            <div class="px-4 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                <div class="flex items-center gap-4">

                    <div

                        class="w-12 h-12 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 overflow-hidden">

                        @if ($orderItem?->product?->photo)

                        <img src="{{ asset($orderItem->product->photo) }}" class="w-full h-full object-cover"

                            alt="{{ $orderItem->product?->title }}">

                        @else

                        <div class="w-full h-full flex items-center justify-center text-gray-300">

                            <i class="ri-image-line text-lg"></i>

                        </div>

                        @endif

                    </div>

                    <div>

                        <p class="font-semibold text-gray-900 dark:text-white">

                            {{ $orderItem?->product?->title ?? 'Ürün #' . $orderItem?->id }}

                        </p>

                        <p class="text-xs text-gray-500">Adet: {{ $item->quantity }}</p>

                    </div>

                </div>

                <p class="text-sm text-gray-600 dark:text-gray-300">

                    {{ number_format((float) $orderItem?->total, 2, ',', '.') }} ₺

                </p>

            </div>

            @endforeach

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
    $(document).on('change', '.status-selector', function() {
        const $select = $(this);
        const id = $select.data('id');
        const newStatus = $select.val();
        const currentStatus = $select.data('current');
        const statusText = $select.find('option:selected').text();

        if (newStatus === currentStatus) return;

        const resetSelect = () => $select.val(currentStatus);

        const performUpdate = (notes = '') => {
            let url = "{{ route('admin.returns.status', ['status' => ':status']) }}";
            url = url.replace(':status', newStatus);
            window.location.href = url + '?return_id=' + id + '&notes=' + encodeURIComponent(notes);
        };

        showConfirmModal(`${statusText} durumuna geçirmek istediğinize emin misiniz?`, function() {
            if (newStatus === 'rejected') {
                showRejectionModal(function(notes) {
                    performUpdate(notes);
                }, resetSelect);
            } else {
                performUpdate();
            }
        }, resetSelect);
    });

    function showRejectionModal(onConfirm, onCancel) {
        const $overlay = $(
            '<div class="fixed inset-0 bg-black/60 z-[9999] flex items-center justify-center p-4 backdrop-blur-sm"></div>'
        );
        const $box = $(
            '<div class="max-w-md w-full rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black p-6 shadow-2xl"></div>'
        );

        $box.append('<div class="text-lg font-bold mb-2">Red Nedeni</div>');
        $box.append('<p class="text-sm text-gray-500 mb-4">Lütfen bu iade talebinin reddedilme nedenini belirtin.</p>');
        const $textarea = $(
            '<textarea class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm mb-4 focus:ring-2 focus:ring-black dark:focus:ring-white outline-none" rows="4" placeholder="Reddetme nedenini yazınız..."></textarea>'
        );
        $box.append($textarea);

        const $actions = $('<div class="flex items-center justify-end gap-3"></div>');
        const $cancel = $(
            '<button type="button" class="px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-800 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-900 transition">Vazgeç</button>'
        );
        const $ok = $(
            '<button type="button" class="px-4 py-2 rounded-lg text-sm font-bold bg-black text-white dark:bg-white dark:text-black hover:opacity-80 transition">Reddet</button>'
        );

        $actions.append($cancel, $ok);
        $box.append($actions);
        $overlay.append($box);
        $('body').append($overlay);

        $textarea.focus();

        $cancel.on('click', function() {
            $overlay.remove();
            if (typeof onCancel === 'function') onCancel();
        });

        $ok.on('click', function() {
            const notes = $textarea.val().trim();
            if (!notes) {
                if (typeof showError === 'function') showError('Lütfen bir red nedeni giriniz.');
                else alert('Lütfen bir red nedeni giriniz.');
                return;
            }
            $overlay.remove();
            if (typeof onConfirm === 'function') onConfirm(notes);
        });
    }
</script>
@endpush
