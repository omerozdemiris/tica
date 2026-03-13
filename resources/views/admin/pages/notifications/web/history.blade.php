@extends('admin.layouts.app')
@section('title', 'Bildirim Geçmişi')
@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Bildirim Geçmişi</h1>
        <div class="flex items-center gap-2">
            <button type="button" id="delete-selected" style="display: none;"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition-colors">
                <i class="ri-delete-bin-line"></i>
                <span>Seçilenleri Sil</span>
            </button>
            <button type="button" id="delete-all"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-red-200 text-red-600 text-sm font-medium hover:bg-red-50 transition-colors">
                <i class="ri-delete-bin-3-line"></i>
                <span>Tümünü Sil</span>
            </button>
            <a href="{{ route('admin.notifications.web.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-800 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-900">
                <i class="ri-arrow-left-line"></i>
                <span>Geri Dön</span>
            </a>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-black">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left w-10">
                        <input type="checkbox" id="select-all" class="rounded border-gray-300">
                    </th>
                    <th class="text-left px-4 py-3">Müşteri</th>
                    <th class="text-left px-4 py-3">Başlık</th>
                    <th class="text-center px-4 py-3">Tür</th>
                    <th class="text-center px-4 py-3">Durum</th>
                    <th class="text-right px-4 py-3">Tarih</th>
                </tr>
            </thead>
            <tbody class="cursor-pointer">
                @forelse($notifications as $notification)
                    <tr
                        class="border-t border-gray-100 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="px-4 py-3 text-left">
                            <input type="checkbox" name="ids[]" value="{{ $notification->id }}"
                                class="notification-checkbox rounded border-gray-300">
                        </td>
                        <td class="px-4 py-3">{{ $notification->user->name ?? 'Bilinmiyor' }}</td>
                        <td class="px-4 py-3 font-medium">{{ $notification->title }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($notification->type === 'event')
                                <span
                                    class="px-2 py-1 rounded-full text-[10px] uppercase font-bold bg-amber-100 text-amber-700">Sistem</span>
                            @else
                                <span
                                    class="px-2 py-1 rounded-full text-[10px] uppercase font-bold bg-blue-100 text-blue-700">Özel</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($notification->read_at)
                                <span class="text-xs text-emerald-600">Okundu</span>
                            @else
                                <span class="text-xs text-gray-400">Bekliyor</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">
                            {{ $notification->created_at->translatedFormat('d F Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Henüz bildirim kaydı yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            const $selectAll = $('#select-all');
            const $checkboxes = $('.notification-checkbox');
            const $deleteSelectedBtn = $('#delete-selected');
            const $deleteAllBtn = $('#delete-all');

            function toggleDeleteBtn() {
                const checkedCount = $('.notification-checkbox:checked').length;
                if (checkedCount > 0) {
                    $deleteSelectedBtn.fadeIn();
                } else {
                    $deleteSelectedBtn.fadeOut();
                }
            }

            $selectAll.on('change', function() {
                $checkboxes.prop('checked', this.checked);
                toggleDeleteBtn();
            });

            $checkboxes.on('change', function() {
                const allChecked = $checkboxes.length === $('.notification-checkbox:checked').length;
                $selectAll.prop('checked', allChecked);
                toggleDeleteBtn();

                $(this).closest('tr').toggleClass('bg-blue-50/50 dark:bg-blue-900/20', this.checked);
            });

            $(document).on('click', 'tbody tr', function(e) {
                if ($(e.target).closest('input[type="checkbox"], a, button').length) return;

                const $checkbox = $(this).find('.notification-checkbox');
                if ($checkbox.length) {
                    $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
                }
            });

            $deleteSelectedBtn.on('click', function() {
                const ids = $('.notification-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (ids.length === 0) return;

                window.showConfirmModal('Seçilen bildirimleri silmek istediğinize emin misiniz?',
                    function() {
                        $.ajax({
                            url: "{{ route('admin.notifications.web.delete-selected') }}",
                            type: 'POST',
                            data: {
                                ids: ids
                            },
                            success: function(res) {
                                window.showSuccess(res.msg);
                                setTimeout(() => window.location.reload(), 600);
                            },
                            error: function(xhr) {
                                window.showError(xhr.responseJSON?.msg ||
                                    'Bir hata oluştu');
                            }
                        });
                    });
            });

            $deleteAllBtn.on('click', function() {
                window.showConfirmModal(
                    'Tüm bildirim geçmişini temizlemek istediğinize emin misiniz? Bu işlem geri alınamaz.',
                    function() {
                        $.ajax({
                            url: "{{ route('admin.notifications.web.clear-all') }}",
                            type: 'POST',
                            success: function(res) {
                                window.showSuccess(res.msg);
                                setTimeout(() => window.location.reload(), 600);
                            },
                            error: function(xhr) {
                                window.showError(xhr.responseJSON?.msg ||
                                    'Bir hata oluştu');
                            }
                        });
                    });
            });
        });
    </script>
@endpush
