@extends('admin.layouts.app')
@section('title', 'Web Site Bildirimleri')
@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Web Site Bildirimleri</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.notifications.web.history') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-800 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-900">
                <i class="ri-history-line"></i>
                <span>Geçmiş Bildirimler</span>
            </a>
            <button type="button" data-notification-modal-open
                class="hidden inline-flex items-center gap-2 px-4 py-2 rounded-md bg-blue-600 text-white text-sm font-medium"
                id="bulk-notification-btn">
                <i class="ri-notification-3-line"></i>
                <span>Seçili Müşterilere Gönder</span>
            </button>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.notifications.web.index') }}"
        class="mb-6 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black p-4">
        <div class="flex gap-4">
            <input type="text" name="keyword" value="{{ request('keyword') }}"
                class="flex-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"
                placeholder="Müşteri adı veya e-posta ile ara...">
            <button type="submit"
                class="px-4 py-2 rounded-md bg-black text-white dark:bg-white dark:text-black text-sm font-medium">
                Filtrele
            </button>
        </div>
    </form>

    <div class="rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-black">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 w-16 text-center">
                        <input type="checkbox" id="select-all-customers" class="w-5 h-5 rounded border-gray-300">
                    </th>
                    <th class="text-left px-4 py-3">Müşteri Bilgileri</th>
                    <th class="text-left px-4 py-3">E-posta</th>
                    <th class="text-center px-4 py-3">Bildirim İzni</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr class="customer-row border-t border-gray-100 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors cursor-pointer"
                        data-id="{{ $customer->id }}">
                        <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                            <input type="checkbox" name="customer_ids[]" value="{{ $customer->id }}"
                                class="customer-checkbox w-5 h-5 rounded border-gray-300">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                    {{ $customer->initials }}
                                </div>
                                <span class="font-medium">{{ $customer->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $customer->email }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($customer->hasNotificationPermission('web'))
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-700">Kapalı</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">Kayıt bulunamadı.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $customers->links() }}
    </div>

    @include('admin.pages.notifications.web.modal')

    @push('scripts')
        <script>
            $(function() {
                const $modal = $('#notification-modal');
                const $bulkBtn = $('#bulk-notification-btn');
                const $selectAll = $('#select-all-customers');
                const $checkboxes = $('.customer-checkbox');
                const $contextSelector = $('#context-selector');
                const $contextSearch = $('#context-search');
                const $searchResults = $('#search-results');
                const $contextType = $('#context_type');

                let selectedContextIds = [];

                function toggleBulkBtn() {
                    const checked = $('.customer-checkbox:checked').length;
                    $bulkBtn.toggleClass('hidden', checked === 0);
                }

                $('.customer-row').on('click', function() {
                    const $cb = $(this).find('.customer-checkbox');
                    $cb.prop('checked', !$cb.is(':checked'));
                    toggleBulkBtn();
                });

                $selectAll.on('change', function() {
                    $checkboxes.prop('checked', $(this).is(':checked'));
                    toggleBulkBtn();
                });

                $checkboxes.on('change', function(e) {
                    e.stopPropagation();
                    toggleBulkBtn();
                });

                $('[data-notification-modal-open]').on('click', function() {
                    $modal.removeClass('hidden');
                });

                $('[data-notification-modal-close]').on('click', function() {
                    $modal.addClass('hidden');
                });

                let searchTimeout;
                $contextSearch.on('input', function() {
                    clearTimeout(searchTimeout);
                    const query = $(this).val();
                    if (query.length < 2) {
                        $searchResults.addClass('hidden');
                        return;
                    }

                    searchTimeout = setTimeout(() => {
                        const type = $contextType.val();
                        const url = type === 'product' ? "{{ route('admin.products.index') }}" :
                            "{{ route('admin.categories.index') }}";

                        $.get(url, {
                            keyword: query,
                            ajax: 1
                        }, function(data) {
                            let html = '';
                            const items = data.data || data;
                            if (items.length === 0) {
                                html =
                                    '<div class="p-2 text-sm text-gray-500">Sonuç bulunamadı</div>';
                            } else {
                                items.forEach(item => {
                                    html +=
                                        `<div class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer text-sm" data-id="${item.id}" data-name="${item.title || item.name}">${item.title || item.name}</div>`;
                                });
                            }
                            $searchResults.html(html).removeClass('hidden');
                        });
                    }, 300);
                });

                $(document).on('click', '#search-results div', function() {
                    const id = $(this).data('id');
                    const name = $(this).data('name');
                    if (!selectedContextIds.includes(id)) {
                        selectedContextIds.push(id);
                        $contextSelector.append(`
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200 rounded text-xs">
                            ${name}
                            <i class="ri-close-line cursor-pointer remove-context" data-id="${id}"></i>
                            <input type="hidden" name="context_ids[]" value="${id}">
                        </span>
                    `);
                    }
                    $searchResults.addClass('hidden');
                    $contextSearch.val('');
                });

                $(document).on('click', '.remove-context', function() {
                    const id = $(this).data('id');
                    selectedContextIds = selectedContextIds.filter(x => x !== id);
                    $(this).parent().remove();
                });

                $('#notification-form').on('submit', function(e) {
                    e.preventDefault();
                    const customerIds = $('.customer-checkbox:checked').map(function() {
                        return $(this).val();
                    }).get();
                    const formData = $(this).serializeArray();
                    customerIds.forEach(id => formData.push({
                        name: 'user_ids[]',
                        value: id
                    }));

                    $.ajax({
                        url: "{{ route('admin.notifications.web.send') }}",
                        method: 'POST',
                        data: $.param(formData),
                        success: function(res) {
                            showSuccess(res.msg);
                            $modal.addClass('hidden');
                            window.location.reload();
                        },
                        error: function(xhr) {
                            showError(xhr.responseJSON?.msg || 'Hata oluştu');
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
