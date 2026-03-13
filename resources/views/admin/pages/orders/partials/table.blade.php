@php

    $statusMeta = [
        'new' => [
            'label' => 'Yeni',

            'badgeClass' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200',
        ],

        'pending' => [
            'label' => 'Beklemede',

            'badgeClass' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200',
        ],

        'completed' => [
            'label' => 'Tamamlandı',

            'badgeClass' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-200',
        ],

        'canceled' => [
            'label' => 'İptal Edildi',

            'badgeClass' => 'bg-rose-100 text-rose-700 dark:bg-rose-900 dark:text-rose-200',
        ],
    ];

    $badgeBaseClass = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium';

    $paymentStatusMeta = [
        1 => [
            'label' => 'Başarılı',
            'badgeClass' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-200',
        ],
        0 => [
            'label' => 'Bekleniyor',
            'badgeClass' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200',
        ],
    ];
    $paymentStatuClass = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium';

    $paymentMethods = [
        'card' => 'Kart',
        'wire' => 'Havale',
    ];

@endphp



<div class="flex items-center justify-between mb-4">

    <h1 class="text-lg font-semibold">{{ $title ?? 'Siparişler' }}</h1>

</div>



<div class="rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm bg-white dark:bg-black">

    <table class="min-w-full text-sm">

        <thead class="bg-gray-50 dark:bg-gray-900">

            <tr>

                <th class="text-left px-4 py-3 font-semibold">Sipariş No</th>

                <th class="text-left px-4 py-3 font-semibold">Müşteri</th>

                <th class="text-left px-4 py-3 font-semibold">Durum</th>
                <th class="text-left px-4 py-3 font-semibold">Ödeme</th>

                <th class="text-right px-4 py-3 font-semibold">Tutar</th>

                <th class="text-right px-4 py-3 font-semibold">Tarih</th>

                <th class="text-right px-4 py-3 font-semibold">İşlemler</th>

            </tr>

        </thead>

        <tbody>

            @forelse ($orders as $order)

                @php

                    $meta = $statusMeta[$order->status] ?? $statusMeta['pending'];

                    $customerName = $order->user->name ?? 'Müşteri';

                    $customerEmail = $order->user->email ?? '—';

                    $customerPhone = $order->user->phone ?? '—';
                    
                    $customerTc = $order->shippingAddress?->tc ?? $order->billingAddress?->tc;

                @endphp

                <tr
                    class="border-t border-gray-100 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">

                    <td class="px-4 py-3 font-semibold">

                        <div>{{ $order->order_number }}</div>

                        <div class="text-xs text-gray-500">#{{ $order->id }}</div>

                    </td>

                    <td class="px-4 py-3">

                        <div class="font-medium">{{ $customerName }}</div>

                        <div class="text-xs text-gray-500">{{ $customerEmail }}</div>

                        @if ($customerPhone && $customerPhone !== '—')
                            <div class="text-xs text-gray-500">{{ $customerPhone }}</div>
                        @endif

                        @if ($customerTc)
                            <div class="text-xs text-gray-500">TC: {{ $customerTc }}</div>
                        @endif

                    </td>

                    <td class="px-4 py-3">

                        <div class="flex flex-col gap-2">

                            <span class="{{ $badgeBaseClass }} {{ $meta['badgeClass'] }}"
                                data-status-badge>{{ $meta['label'] }}</span>

                            <select
                                class="text-xs px-2 py-1 rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-black order-status-select"
                                data-order-status-select
                                data-status-url="{{ route('admin.orders.status', $order->id) }}"
                                data-order-id="{{ $order->id }}" data-order-number="{{ $order->order_number }}"
                                data-method="{{ $order->method }}" data-is-paid="{{ $order->is_paid }}"
                                data-prev="{{ $order->status }}">

                                @foreach ($statusMeta as $key => $definition)
                                    <option value="{{ $key }}" @selected($order->status === $key)>
                                        {{ $definition['label'] }}</option>
                                @endforeach

                            </select>

                        </div>

                    </td>
                    <td class="px-4 py-3">
                        <span class="{{ $badgeBaseClass }} {{ $paymentStatusMeta[$order->is_paid]['badgeClass'] }}"
                            data-payment-status-badge>{{ $paymentStatusMeta[$order->is_paid]['label'] }}
                            ({{ $paymentMethods[$order->method] }})
                        </span>
                    </td>

                    <td class="px-4 py-3 text-right font-semibold">{{ number_format($order->total, 2, ',', '.') }} ₺
                    </td>

                    <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400">
                        {{ $order->created_at->format('d.m.Y H:i') }}</td>

                    <td class="px-4 py-3">

                        <div class="flex flex-wrap justify-end gap-2">

                            <a href="{{ route('admin.orders.show', $order->id) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 text-xs">

                                <i class="ri-eye-line"></i>

                                <span>Detay</span>

                            </a>

                            <a href="{{ route('admin.orders.edit', $order->id) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900 text-xs">

                                <i class="ri-edit-line"></i>

                                <span>Düzenle</span>

                            </a>

                            <button type="button" data-delete
                                data-url="{{ route('admin.orders.destroy', $order->id) }}"
                                data-confirm="Bu siparişi silmek istediğinize emin misiniz?"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border border-red-200 text-red-600 dark:border-red-900 hover:bg-red-50 dark:hover:bg-red-900/40 text-xs">

                                <i class="ri-delete-bin-line"></i>

                                <span>Sil</span>

                            </button>

                        </div>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">

                        Listelenecek sipariş bulunamadı.

                    </td>

                </tr>
            @endforelse

        </tbody>

    </table>



    @if ($orders instanceof \Illuminate\Contracts\Pagination\Paginator && $orders->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-900 bg-gray-50 dark:bg-gray-900">

            {{ $orders->links() }}

        </div>
    @endif

</div>



@once

    @push('scripts')
        <script>
            window.ShippingCompanies = @json($shippingCompanies ?? []);

            window.OrderStatusMeta = @json($statusMeta);

            (function() {

                if (window.__orderStatusHandlersInitialized) return;

                window.__orderStatusHandlersInitialized = true;



                const badgeBaseClass = @json($badgeBaseClass);



                function getMeta(status) {

                    const meta = window.OrderStatusMeta || {};

                    return meta[status] || meta['pending'];

                }



                function setBadge($row, status) {

                    const meta = getMeta(status);

                    const $badge = $row.find('[data-status-badge]').first();

                    if ($badge.length) {

                        $badge.attr('class', badgeBaseClass + ' ' + (meta.badgeClass || 'bg-gray-200'));

                        $badge.text(meta.label || status);

                    }

                }



                function revertSelect($select) {

                    const prev = $select.data('prev');

                    if (prev) {

                        $select.val(prev);

                        setBadge($select.closest('tr'), prev);

                    }

                }



                function postStatus($select, payload) {

                    const url = $select.data('statusUrl');

                    if (!url) return;

                    const $row = $select.closest('tr');

                    $.ajax({

                        url,

                        type: 'POST',

                        data: payload,

                        success: function(res) {

                            const message = res?.msg || 'Sipariş güncellendi';

                            showSuccess(message);

                            const status = payload.status;

                            setBadge($row, status);

                            $select.data('prev', status);

                            $select.val(status);

                            setTimeout(function() {

                                window.location.reload();

                            }, 1000);

                        },

                        error: function(xhr) {

                            const msg = xhr.responseJSON?.msg || 'Hata oluştu';

                            showError(msg);

                            revertSelect($select);

                        },

                    });

                }



                function openCompletedModal($select) {

                    const companies = window.ShippingCompanies || [];

                    if (!companies.length) {

                        showError(
                            'Aktif kargo firması bulunamadı. Önce Teslimat Ayarları üzerinden kargo firması ekleyiniz.');

                        revertSelect($select);

                        return;

                    }

                    const $overlay = $(
                        '<div class="fixed inset-0 z-50 bg-black/50 backdrop-blur flex items-center justify-center p-4"></div>'
                    );

                    const $box = $(
                        '<div class="max-w-lg w-full rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black p-5"></div>'
                    );

                    $box.append('<h3 class="text-base font-semibold mb-4">Siparişi Tamamla</h3>');

                    const companySelect = [

                        '<label class="text-xs uppercase tracking-wide text-gray-500">Kargo Firması</label>',

                        '<select class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black" data-completed-company>'

                    ];

                    companySelect.push('<option value="">-- Seçiniz --</option>');

                    companies.forEach(function(company) {

                        companySelect.push('<option value="' + company.id + '">' + company.name + '</option>');

                    });

                    companySelect.push('</select>');

                    $box.append(companySelect.join(''));

                    $box.append(
                        '<label class="text-xs uppercase tracking-wide text-gray-500 block mt-4">Takip Numarası</label>'
                    );

                    $box.append(
                        '<input type="text" data-completed-tracking class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black" placeholder="Kargo takip numarası">'
                    );

                    const $actions = $('<div class="flex items-center justify-end gap-2 mt-6"></div>');

                    const $cancel = $(
                        '<button type="button" class="px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800">Vazgeç</button>'
                    );

                    const $continue = $(
                        '<button type="button" class="px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">Tamamla</button>'
                    );

                    $actions.append($cancel, $continue);

                    $box.append($actions);

                    $overlay.append($box);

                    $('body').append($overlay);



                    $cancel.on('click', function() {

                        $overlay.remove();

                        revertSelect($select);

                    });



                    $continue.on('click', function() {
                        const companyId = $box.find('[data-completed-company]').val();
                        const trackingNo = $box.find('[data-completed-tracking]').val().trim();

                        if (!companyId) {
                            showError('Lütfen kargo firması seçiniz.');
                            return;
                        }

                        if (!trackingNo) {
                            showError('Lütfen kargo takip numarası giriniz.');
                            return;
                        }

                        const meta = getMeta('completed');
                        let confirmMessage = 'Sipariş durumunu "' + (meta.label || 'Tamamlandı') +
                            '" olarak değiştirmek istediğinize emin misiniz?';
                        let updatePayment = false;
                        const method = $select.data('method');
                        const isPaid = $select.data('isPaid');

                        if (method === 'wire' && isPaid == 0) {
                            confirmMessage = 'Ödeme durumu bekleniyor, yine de devam etmek istiyor musunuz?';
                            updatePayment = true;
                        }

                        showConfirmModal(
                            confirmMessage,
                            function() {
                                postStatus($select, {
                                    status: 'completed',
                                    shipping_company_id: companyId,
                                    tracking_no: trackingNo,
                                    update_payment: updatePayment ? 1 : 0
                                });
                            },
                            function() {
                                revertSelect($select);
                            }
                        );

                        $overlay.remove();
                    });

                }



                function openCanceledModal($select) {

                    const $overlay = $(
                        '<div class="fixed inset-0 z-50 bg-black/50 backdrop-blur flex items-center justify-center p-4"></div>'
                    );

                    const $box = $(
                        '<div class="max-w-lg w-full rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black p-5"></div>'
                    );

                    $box.append('<h3 class="text-base font-semibold mb-4">Siparişi İptal Et</h3>');

                    $box.append('<label class="text-xs uppercase tracking-wide text-gray-500">İptal Sebebi</label>');

                    $box.append(
                        '<textarea data-cancel-reason rows="4" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black" placeholder="İptal sebebini yazınız..."></textarea>'
                    );

                    const $actions = $('<div class="flex items-center justify-end gap-2 mt-6"></div>');

                    const $cancel = $(
                        '<button type="button" class="px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800">Vazgeç</button>'
                    );

                    const $continue = $(
                        '<button type="button" class="px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">İptal Et</button>'
                    );

                    $actions.append($cancel, $continue);

                    $box.append($actions);

                    $overlay.append($box);

                    $('body').append($overlay);



                    $cancel.on('click', function() {

                        $overlay.remove();

                        revertSelect($select);

                    });



                    $continue.on('click', function() {

                        const reason = ($box.find('[data-cancel-reason]').val() || '').trim();

                        if (!reason) {

                            showError('Lütfen iptal sebebini yazınız.');

                            return;

                        }

                        const meta = getMeta('canceled');

                        showConfirmModal(

                            'Sipariş durumunu "' + (meta.label || 'İptal') +
                            '" olarak değiştirmek istediğinize emin misiniz?',

                            function() {

                                postStatus($select, {

                                    status: 'canceled',

                                    cancel_reason: reason,

                                });

                            },

                            function() {

                                revertSelect($select);

                            }

                        );

                        $overlay.remove();

                    });

                }



                $(document).on('focus', '[data-order-status-select]', function() {

                    $(this).data('prev', $(this).val());

                });



                $(document).on('change', '[data-order-status-select]', function() {

                    const $select = $(this);

                    const newStatus = $select.val();

                    const prevStatus = $select.data('prev');

                    if (newStatus === prevStatus) return;



                    if (newStatus === 'completed') {

                        openCompletedModal($select);

                        return;

                    }

                    if (newStatus === 'canceled') {

                        openCanceledModal($select);

                        return;

                    }



                    const meta = getMeta(newStatus);

                    showConfirmModal(

                        'Sipariş durumunu "' + (meta.label || newStatus) +
                        '" olarak değiştirmek istediğinize emin misiniz?',

                        function() {

                            postStatus($select, {
                                status: newStatus
                            });

                        },

                        function() {

                            revertSelect($select);

                        }

                    );

                });

            })
            ();
        </script>
    @endpush
@endonce
