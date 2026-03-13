@extends('admin.layouts.app')

@section('title', 'İade Talepleri')

@php

    $filters = $filters ?? [];

    $statusLabels = [
        'pending' => 'Bekleyen İadeler',

        'processed' => 'İade Edilenler',

        null => 'Tüm İadeler',
    ];

@endphp

@section('content')

    <div class="mb-6 flex flex-wrap items-center gap-3">

        <a href="{{ route('admin.returns.index') }}"
            class="px-3 py-2 rounded-lg text-sm font-semibold {{ $activeStatus === null ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">

            Tüm İadeler

        </a>

        <a href="{{ route('admin.returns.pending') }}"
            class="px-3 py-2 rounded-lg text-sm font-semibold {{ $activeStatus === 'pending' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">

            Bekleyen İadeler

        </a>

        <a href="{{ route('admin.returns.processed') }}"
            class="px-3 py-2 rounded-lg text-sm font-semibold {{ $activeStatus === 'processed' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">

            İade Edilenler

        </a>

    </div>



    <form method="GET" action="{{ url()->current() }}"
        class="mb-6 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black p-4">

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

            <div>

                <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Sipariş No</label>

                <input type="text" name="order_number" value="{{ $filters['order_number'] ?? '' }}"
                    class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm"
                    placeholder="ORD-0001">

            </div>

            <div>

                <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Müşteri</label>

                <input type="text" name="customer" value="{{ $filters['customer'] ?? '' }}"
                    class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm"
                    placeholder="Ad Soyad">

            </div>

            <div>

                <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">E-posta</label>

                <input type="email" name="email" value="{{ $filters['email'] ?? '' }}"
                    class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm"
                    placeholder="ornek@mail.com">

            </div>

            <div>

                <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Başlangıç</label>

                <input type="date" name="date_start" value="{{ optional($filters['date_start'])->format('Y-m-d') }}"
                    class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm">

            </div>

            <div>

                <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Bitiş</label>

                <input type="date" name="date_end" value="{{ optional($filters['date_end'])->format('Y-m-d') }}"
                    class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm">

            </div>

        </div>

        <div class="mt-4 flex items-center gap-2">

            <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-900 bg-black text-white text-sm font-semibold hover:bg-gray-900 transition">

                <i class="ri-search-line text-base"></i>

                <span>Filtrele</span>

            </button>

            <a href="{{ url()->current() }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-800 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-900 transition">

                <i class="ri-refresh-line text-base"></i>

                <span>Temizle</span>

            </a>

        </div>

    </form>



    <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black overflow-hidden">

        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800 text-sm">

            <thead class="bg-gray-50 dark:bg-gray-900/40 text-xs uppercase tracking-wide text-gray-500">

                <tr>

                    <th class="px-4 py-3 text-left">Sipariş</th>

                    <th class="px-4 py-3 text-left">Müşteri</th>

                    <th class="px-4 py-3 text-left">Durum</th>

                    <th class="px-4 py-3 text-left">Ürün Adedi</th>

                    <th class="px-4 py-3 text-left">Tarih</th>

                    <th class="px-4 py-3 text-right"></th>

                </tr>

            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                @forelse ($returns as $return)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition">

                        <td class="px-4 py-3">

                            <p class="font-semibold text-gray-900 dark:text-gray-100">#{{ $return->order_number }}</p>

                            <p class="text-xs text-gray-500">{{ $return->order?->created_at?->format('d.m.Y H:i') }}</p>

                        </td>

                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200">

                            <p class="font-semibold">{{ $return->customer_name }}</p>

                            <p class="text-xs text-gray-500">{{ $return->customer_email }}</p>

                        </td>

                        <td class="px-4 py-3">

                            @php

                                $statusClasses = [
                                    'pending' => 'bg-yellow-100 text-yellow-700',

                                    'processed' => 'bg-green-100 text-green-700',

                                    'rejected' => 'bg-red-100 text-red-700',
                                ];

                                $statusNames = [
                                    'pending' => 'Beklemede',

                                    'processed' => 'İade Edildi',

                                    'rejected' => 'Reddedildi',
                                ];

                            @endphp

                            <div class="flex flex-col gap-2">

                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses[$return->status] ?? 'bg-gray-100 text-gray-700' }}">

                                    {{ $statusNames[$return->status] ?? ucfirst($return->status) }}

                                </span>

                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-700 uppercase">

                                    İade

                                </span>

                            </div>

                        </td>
                        <td class="px-4 py-3">
                            <select
                                class="status-selector w-full min-w-[130px] px-2 py-1 text-xs rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-1 focus:ring-black dark:focus:ring-white outline-none transition-all"
                                data-id="{{ $return->id }}" data-current="{{ $return->status }}">
                                <option value="pending" {{ $return->status === 'pending' ? 'selected' : '' }}>Beklemede
                                </option>
                                <option value="processed" {{ $return->status === 'processed' ? 'selected' : '' }}>İade
                                    Edildi</option>
                                <option value="rejected" {{ $return->status === 'rejected' ? 'selected' : '' }}>Reddedildi
                                </option>
                            </select>
                        </td>

                        <td class="px-4 py-3 text-gray-900 dark:text-gray-200">

                            {{ $return->items->count() }}

                        </td>

                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">

                            {{ $return->created_at?->format('d.m.Y H:i') }}

                        </td>

                        <td class="px-4 py-3 text-right">

                            <a href="{{ route('admin.returns.show', $return) }}"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md bg-black text-white text-xs font-semibold hover:bg-gray-900 transition">

                                <i class="ri-eye-line text-base"></i>

                                <span>Detay</span>

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">

                            Kayıt bulunamadı.

                        </td>

                    </tr>
                @endforelse

            </tbody>

        </table>

        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800">

            {{ $returns->links() }}

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
