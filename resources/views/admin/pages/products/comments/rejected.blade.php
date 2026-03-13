@extends('admin.layouts.app')
@section('title', 'Reddedilen Değerlendirmeler')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Reddedilen Değerlendirmeler</h1>
    </div>

    <div class="mb-6 border-b border-gray-200 dark:border-gray-800">
        <nav class="flex gap-4">
            <a href="{{ route('admin.product-comments.index') }}"
                class="pb-2 text-sm font-medium border-b-2 {{ request()->routeIs('admin.product-comments.index') ? 'border-black dark:border-white text-black dark:text-white' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Tümü
            </a>
            <a href="{{ route('admin.product-comments.pending') }}"
                class="pb-2 text-sm font-medium border-b-2 {{ request()->routeIs('admin.product-comments.pending') ? 'border-black dark:border-white text-black dark:text-white' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Bekleyenler
            </a>
            <a href="{{ route('admin.product-comments.approved') }}"
                class="pb-2 text-sm font-medium border-b-2 {{ request()->routeIs('admin.product-comments.approved') ? 'border-black dark:border-white text-black dark:text-white' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Onaylananlar
            </a>
            <a href="{{ route('admin.product-comments.rejected') }}"
                class="pb-2 text-sm font-medium border-b-2 {{ request()->routeIs('admin.product-comments.rejected') ? 'border-black dark:border-white text-black dark:text-white' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Reddedilenler
            </a>
        </nav>
    </div>

    <div id="bulk-actions" class="hidden mb-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg flex items-center gap-4">
        <span class="text-sm font-medium text-gray-500"><span id="selected-count">0</span> öğe seçildi</span>
        <div class="h-4 w-px bg-gray-200 dark:bg-gray-800"></div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="bulkAction('status_1')" class="px-3 py-1.5 text-xs font-semibold bg-green-600 text-white rounded-md hover:bg-green-700">Onayla</button>
            <button type="button" onclick="bulkAction('status_0')" class="px-3 py-1.5 text-xs font-semibold bg-yellow-500 text-white rounded-md hover:bg-yellow-600">Beklemeye Al</button>
            <button type="button" onclick="bulkAction('delete')" class="px-3 py-1.5 text-xs font-semibold bg-black text-white dark:bg-white dark:text-black rounded-md hover:opacity-80">Sil</button>
        </div>
    </div>

    <div class="bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" id="select-all" class="rounded border-gray-300 dark:border-gray-700 text-black focus:ring-0">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yorum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($comments as $comment)
                    <tr>
                        <td class="px-4 py-4">
                            <input type="checkbox" class="comment-checkbox rounded border-gray-300 dark:border-gray-700 text-black focus:ring-0" value="{{ $comment->id }}">
                        </td>
                        <td class="px-6 py-4">
                            @if ($comment->product)
                                <a href="{{ route('products.show', [$comment->product->id, $comment->product->slug]) }}"
                                    target="_blank"
                                    class="text-sm font-medium text-blue-600 hover:underline flex items-center gap-1">
                                    {{ $comment->product->title }}
                                    <i class="ri-external-link-line text-xs"></i>
                                </a>
                            @else
                                <div class="text-sm font-medium text-gray-400">Silinmiş Ürün</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">{{ $comment->user->name ?? 'Anonim' }}</div>
                            <div class="text-xs text-gray-500">{{ $comment->user->email ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.product-comments.show', $comment->id) }}"
                                class="text-sm text-gray-900 dark:text-gray-300 max-w-xs truncate block hover:text-blue-600 hover:underline"
                                title="{{ $comment->comment }}">
                                {{ $comment->comment }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex text-yellow-400">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="{{ $i <= $comment->rating ? 'ri-star-fill' : 'ri-star-line' }}"></i>
                                @endfor
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end items-center gap-2">
                                <a href="{{ route('admin.product-comments.show', $comment->id) }}" class="p-1 hover:text-blue-600" title="Detay"><i class="ri-eye-line"></i></a>
                                <button type="button" onclick="deleteComment({{ $comment->id }})" class="p-1 hover:text-red-600" title="Sil"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">Reddedilmiş değerlendirme bulunmuyor.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $comments->links() }}
    </div>

    @push('scripts')
        <script>
            $('#select-all').on('change', function() {
                $('.comment-checkbox').prop('checked', $(this).is(':checked'));
                updateBulkActionsVisibility();
            });

            $(document).on('change', '.comment-checkbox', function() {
                updateBulkActionsVisibility();
            });

            function updateBulkActionsVisibility() {
                const checkedCount = $('.comment-checkbox:checked').length;
                $('#selected-count').text(checkedCount);
                if (checkedCount > 0) {
                    $('#bulk-actions').removeClass('hidden').addClass('flex');
                } else {
                    $('#bulk-actions').addClass('hidden').removeClass('flex');
                }
            }

            function bulkAction(action) {
                const ids = $('.comment-checkbox:checked').map(function() { return $(this).val(); }).get();
                if (ids.length === 0) return;

                let message = 'Seçilen öğeler üzerinde bu işlemi yapmak istediğinize emin misiniz?';
                if (action === 'delete') message = 'Seçilen öğeleri silmek istediğinize emin misiniz?';

                showConfirmModal(message, function() {
                    $.ajax({
                        url: "{{ route('admin.product-comments.bulk-action') }}",
                        type: 'POST',
                        data: {
                            ids: ids,
                            action: action
                        },
                        success: function(res) {
                            if (res.code === 1) {
                                showSuccess(res.msg);
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                showError(res.msg);
                            }
                        },
                        error: function(xhr) {
                            showError(xhr.responseJSON?.msg || 'Bir hata oluştu.');
                        }
                    });
                });
            }

            function deleteComment(id) {
                showConfirmModal('Bu değerlendirmeyi silmek istediğinize emin misiniz?', function() {
                    $.ajax({
                        url: `/admin/product-comments/${id}`,
                        type: 'POST',
                        data: {
                            _method: 'DELETE'
                        },
                        success: function(res) {
                            if (res.code === 1) {
                                showSuccess(res.msg);
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                showError(res.msg);
                            }
                        },
                        error: function(xhr) {
                            showError(xhr.responseJSON?.msg || 'Bir hata oluştu.');
                        }
                    });
                });
            }
        </script>
    @endpush
@endsection
