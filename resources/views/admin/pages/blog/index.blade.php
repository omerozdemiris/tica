@extends('admin.layouts.app')

@section('title', 'Blog Yönetimi')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Blog Yönetimi</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-black">
            <h2 class="font-semibold mb-3">Yeni Yazı</h2>
            <form id="blog-create" class="space-y-3" enctype="multipart/form-data">
                @csrf
                <div>
                    <label class="text-sm">Fotoğraf</label>
                    <label
                        class="mt-1 flex items-center justify-between gap-3 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900">
                        <div class="flex items-center gap-3">
                            <i class="ri-image-line text-xl text-gray-500"></i>
                            <span class="text-sm text-gray-600 dark:text-gray-300" data-file-label
                                data-default-text="Dosya seçin...">Dosya seçin...</span>
                        </div>
                        <span class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-700 rounded-md">Gözat</span>
                        <input type="file" name="photo" accept="image/*" class="sr-only" data-file-input>
                    </label>
                    <div class="mt-2 hidden" data-image-preview-wrapper>
                        <img data-image-preview src=""
                            class="max-h-32 rounded border border-gray-200 dark:border-gray-800">
                    </div>
                </div>
                <div>
                    <label class="text-sm">Başlık</label>
                    <input type="text" name="title"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                </div>

                <div>
                    <label class="text-sm">Özet</label>
                    <textarea name="excerpt" rows="3"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"></textarea>
                </div>
                <div>
                    <label class="text-sm">İçerik</label>
                    <textarea name="content" rows="6"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"></textarea>
                </div>
                <label class="flex items-center gap-3">
                    <span>Yayınla</span>
                    <input type="checkbox" name="is_published" value="1" class="toggle">
                </label>
                <div class="flex items-center justify-end">
                    <button
                        class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">Ekle</button>
                </div>
            </form>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-black">
            <table class="min-w-full text-sm" data-datatable>
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="text-left px-3 py-2">Görsel</th>
                        <th class="text-left px-3 py-2">Başlık</th>
                        <th class="text-left px-3 py-2">Durum</th>
                        <th class="text-right px-3 py-2">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $post)
                        <tr class="border-t border-gray-100 dark:border-gray-900">
                            <td class="px-3 py-2">
                                @if ($post->photo)
                                    <img src="{{ asset($post->photo) }}" class="w-10 h-10 rounded object-cover">
                                @else
                                    <div
                                        class="w-10 h-10 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                        <i class="ri-image-line"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $post->title }}</td>
                            <td class="px-3 py-2">{{ $post->is_published ? 'Yayında' : 'Taslak' }}</td>
                            <td class="px-3 py-2 text-right">
                                <button
                                    class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 inline-flex items-center gap-1"
                                    title="Galeri" data-gallery data-kind="bloggallery" data-id="{{ $post->id }}"><i
                                        class="ri-image-line"></i><span>Galeri</span></button>
                                <button
                                    class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 inline-flex items-center gap-1"
                                    data-edit='@json($post)'><i
                                        class="ri-pencil-line"></i><span>Düzenle</span></button>
                                <button
                                    class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 inline-flex items-center gap-1"
                                    data-delete data-url="{{ route('admin.blog.destroy', $post->id) }}"><i
                                        class="ri-delete-bin-line"></i><span>Sil</span></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <template id="blog-edit-template">
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
            <div class="w-full max-w-2xl bg-white dark:bg-black rounded-lg border border-gray-200 dark:border-gray-800 p-4">
                <h3 class="font-semibold mb-3">Yazı Düzenle</h3>
                <form class="space-y-3" data-edit-form enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id">
                    <div>
                        <label class="text-sm">Fotoğraf</label>
                        <label
                            class="mt-1 flex items-center justify-between gap-3 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900">
                            <div class="flex items-center gap-3">
                                <i class="ri-image-line text-xl text-gray-500"></i>
                                <span class="text-sm text-gray-600 dark:text-gray-300" data-file-label
                                    data-default-text="Dosya seçin...">Dosya seçin...</span>
                            </div>
                            <span
                                class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-700 rounded-md">Gözat</span>
                            <input type="file" name="photo" accept="image/*" class="sr-only" data-file-input>
                        </label>
                        <div class="mt-2 hidden" data-image-preview-wrapper>
                            <img data-image-preview src=""
                                class="max-h-32 rounded border border-gray-200 dark:border-gray-800">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm">Başlık</label>
                            <input type="text" name="title"
                                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                        </div>

                    </div>
                    <div>
                        <label class="text-sm">Özet</label>
                        <textarea name="excerpt" rows="3"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"></textarea>
                    </div>
                    <div>
                        <label class="text-sm">İçerik</label>
                        <textarea name="content" rows="6"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"></textarea>
                    </div>
                    <label class="flex items-center gap-3">
                        <span>Yayınla</span>
                        <input type="checkbox" name="is_published" value="1" class="toggle">
                    </label>
                    <div class="flex items-center justify-end gap-2">
                        <button type="button" data-close
                            class="px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800">Kapat</button>
                        <button
                            class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    @push('scripts')
        <script>
            // Preview Image Logic
            $(document).on('change', 'input[data-file-input]', function() {
                const file = this.files[0];
                const $parent = $(this).closest('div');
                const $label = $parent.find('span[data-file-label]');
                const $previewWrapper = $parent.find('div[data-image-preview-wrapper]');
                const $previewImg = $parent.find('img[data-image-preview]');

                if (file) {
                    $label.text(file.name);
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $previewImg.attr('src', e.target.result);
                        $previewWrapper.removeClass('hidden');
                    }
                    reader.readAsDataURL(file);
                } else {
                    $label.text($label.data('default-text'));
                    $previewWrapper.addClass('hidden');
                    $previewImg.attr('src', '');
                }
            });

            // Create
            $('#blog-create').on('submit', function(e) {
                e.preventDefault();
                const fd = new FormData(this);
                $.ajax({
                    url: "{{ route('admin.blog.store') }}",
                    method: "POST",
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        showSuccess(res?.msg);
                        setTimeout(function() {
                            location.reload();
                        }, 600);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.msg || 'Hata';
                        showError(msg);
                    }
                });
            });

            // Edit modal
            $(document).on('click', '[data-edit]', function() {
                const data = $(this).data('edit');
                const tpl = $($('#blog-edit-template').html());
                const form = tpl.find('[data-edit-form]');
                form.attr('action', "{{ url('/admin/blog') }}/" + data.id);
                form.find('[name=id]').val(data.id);
                form.find('[name=title]').val(data.title || '');
                form.find('[name=slug]').val(data.slug || '');
                form.find('[name=excerpt]').val(data.excerpt || '');
                form.find('[name=content]').val(data.content || '');
                form.find('[name=is_published]').prop('checked', !!data.is_published);

                if (data.photo) {
                    form.find('img[data-image-preview]').attr('src', data.photo);
                    form.find('div[data-image-preview-wrapper]').removeClass('hidden');
                }

                $('body').append(tpl);
            });

            // Close modal
            $(document).on('click', '[data-close]', function() {
                $(this).closest('.fixed.inset-0').remove();
            });

            // Update
            $(document).on('submit', '[data-edit-form]', function(e) {
                e.preventDefault();
                const id = $(this).find('[name=id]').val();
                const fd = new FormData(this);
                $.ajax({
                    url: "{{ url('/admin/blog') }}/" + id,
                    method: "POST",
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        showSuccess(res?.msg);
                        setTimeout(function() {
                            location.reload();
                        }, 600);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.msg || 'Hata';
                        showError(msg);
                    }
                });
            });
        </script>
    @endpush
@endsection
@include('admin.pages.dropzone.modal')
