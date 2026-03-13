@extends('admin.layouts.app')
@section('title', 'Değerlendirme Detayı')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Değerlendirme Detayı</h1>
        <div class="flex items-center gap-2">
            <button type="button" onclick="deleteComment({{ $comment->id }})"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-red-50 text-red-600 border border-red-200 text-sm font-medium hover:bg-red-100 transition-colors">
                <i class="ri-delete-bin-line"></i>
                <span>Sil</span>
            </button>
            <a href="{{ route('admin.product-comments.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-800 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-900">
                <i class="ri-arrow-left-line"></i>
                <span>Geri Dön</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-lg p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg">
                            {{ $comment->user->initials ?? '?' }}
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">{{ $comment->user->name ?? 'Anonim' }}</h3>
                            <p class="text-sm text-gray-500">{{ $comment->user->email ?? '' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="flex text-yellow-400 mb-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= $comment->rating ? 'ri-star-fill' : 'ri-star-line' }} text-xl"></i>
                            @endfor
                        </div>
                        <span class="text-xs text-gray-400">{{ $comment->created_at->translatedFormat('d F Y H:i') }}</span>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Yorum</h4>
                    <p
                        class="text-gray-700 dark:text-gray-300 leading-relaxed bg-gray-50 dark:bg-gray-900 p-4 rounded-lg italic">
                        "{{ $comment->comment }}"
                    </p>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Durum Değiştir</h4>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="updateCommentStatus({{ $comment->id }}, 1)"
                            class="px-6 py-2 rounded-full {{ $comment->status == 1 ? 'bg-green-600 text-white' : 'bg-green-50 text-green-600 border border-green-200 hover:bg-green-100' }} font-semibold transition-all">
                            Onayla
                        </button>
                        <button type="button" onclick="updateCommentStatus({{ $comment->id }}, 2)"
                            class="px-6 py-2 rounded-full {{ $comment->status == 2 ? 'bg-red-600 text-white' : 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-100' }} font-semibold transition-all">
                            Reddet
                        </button>
                        <button type="button" onclick="updateCommentStatus({{ $comment->id }}, 0)"
                            class="px-6 py-2 rounded-full {{ $comment->status == 0 ? 'bg-yellow-500 text-white' : 'bg-yellow-50 text-yellow-600 border border-yellow-200 hover:bg-yellow-100' }} font-semibold transition-all">
                            Beklemeye Al
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-lg p-6">
                <h4 class="font-bold mb-4">Ürün Bilgisi</h4>
                @if ($comment->product)
                    <div class="flex items-center gap-4">
                        <img src="{{ asset($comment->product->photo) }}" alt="{{ $comment->product->title }}"
                            class="w-16 h-16 object-cover rounded-lg border">
                        <div>
                            <a href="{{ route('products.show', [$comment->product->id, $comment->product->slug]) }}"
                                target="_blank"
                                class="font-semibold text-sm line-clamp-2 text-blue-600 hover:underline flex items-center gap-1">
                                {{ $comment->product->title }}
                                <i class="ri-external-link-line text-xs"></i>
                            </a>
                            <p class="text-xs text-blue-600 font-bold mt-1">
                                {{ number_format($comment->product->price, 2, ',', '.') }} TL</p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-col gap-2">
                        <a href="{{ route('admin.products.edit', $comment->product->id) }}"
                            class="text-center text-xs font-medium text-gray-500 hover:text-black dark:hover:text-white transition-colors underline">
                            Admin'de Düzenle
                        </a>
                    </div>
                @else
                    <p class="text-sm text-gray-500 italic">Ürün bulunamadı.</p>
                @endif
            </div>

            <div class="bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-lg p-6">
                <h4 class="font-bold mb-4">Aynı Üründeki Diğer Yorumlar</h4>
                <div class="space-y-4">
                    @forelse($other_comments_product as $other)
                        <div class="border-b border-gray-50 dark:border-gray-900 pb-3 last:border-0 last:pb-0">
                            <div class="flex justify-between items-start mb-1">
                                <a href="{{ route('admin.product-comments.show', $other->id) }}" class="text-xs font-bold hover:text-blue-600">{{ $other->user->name ?? 'Anonim' }}</a>
                                <div class="flex text-yellow-400 text-[10px]">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="{{ $i <= $other->rating ? 'ri-star-fill' : 'ri-star-line' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 line-clamp-2 italic">"{{ $other->comment }}"</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">Başka yorum yok.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function updateCommentStatus(id, status) {
                let message = 'Bu yorumun durumunu değiştirmek istediğinize emin misiniz?';
                if(status === 1) message = 'Bu yorumu onaylamak istediğinize emin misiniz?';
                if(status === 2) message = 'Bu yorumu reddetmek istediğinize emin misiniz?';
                if(status === 0) message = 'Bu yorumu beklemeye almak istediğinize emin misiniz?';

                showConfirmModal(message, function() {
                    $.ajax({
                        url: `/admin/product-comments/${id}`,
                        type: 'POST',
                        data: {
                            _method: 'PUT',
                            status: status
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
                            const msg = xhr.responseJSON?.msg || 'İşlem sırasında bir hata oluştu.';
                            showError(msg);
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
                                setTimeout(() => location.href = "{{ route('admin.product-comments.index') }}", 1000);
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
