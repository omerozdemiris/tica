@extends('admin.layouts.app')

@section('title', 'Duyurular')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Duyurular</h1>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-black">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold" data-form-title>Yeni Duyuru</h2>
                <button type="button" class="text-xs text-gray-500 hover:text-gray-800" data-reset-form>Yeni</button>
            </div>
            <form id="announcement-form" class="space-y-3" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id">
                <div>
                    <label class="text-sm">Başlık</label>
                    <input type="text" name="title"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"
                        required>
                </div>
                <div>
                    <label class="text-sm block">Görsel</label>
                    <label
                        class="mt-1 flex items-center justify-between px-3 py-2 rounded-md border border-dashed border-gray-300 dark:border-gray-700 cursor-pointer bg-white dark:bg-black">
                        <span class="text-sm text-gray-500" data-file-label data-default-text="Dosya seçin">Dosya
                            seçin</span>
                        <input type="file" name="image" class="hidden" data-file-input accept="image/*">
                        <span class="text-xs text-gray-400">Resim yükle</span>
                    </label>
                    <div class="mt-2 hidden" data-image-preview-wrapper>
                        <div class="flex items-center gap-3">
                            <img src="" alt="Duyuru görseli"
                                class="w-12 h-12 rounded-md object-cover border border-gray-200 dark:border-gray-800"
                                data-image-preview>
                            <span class="text-xs text-gray-500">Mevcut görsel</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="text-sm">Link (opsiyonel)</label>
                    <input type="text" name="link"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                </div>
                <label class="flex items-center gap-3">
                    <span>Aktif</span>
                    <input type="checkbox" name="is_active" value="1" class="toggle">
                </label>
                <div class="flex items-center justify-end">
                    <button
                        class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black"
                        data-submit-label>Kaydet</button>
                </div>
            </form>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-black">
            <table class="min-w-full text-sm" data-datatable>
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="text-left px-3 py-2">Başlık</th>
                        <th class="text-left px-3 py-2">Durum</th>
                        <th class="text-right px-3 py-2">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($announcements as $item)
                        <tr class="border-t border-gray-100 dark:border-gray-900">
                            <td class="px-3 py-2">{{ $item->title }}</td>
                            <td class="px-3 py-2">
                                <label class="inline-flex items-center gap-2">
                                    <span>{{ $item->is_active ? 'Aktif' : 'Pasif' }}</span>
                                    <input type="checkbox" class="toggle" data-toggle-active data-id="{{ $item->id }}"
                                        @if ($item->is_active) checked @endif>
                                </label>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <button class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800"
                                    data-edit='@json($item)'
                                    data-image-url="{{ $item->image ? asset($item->image) : '' }}"
                                    data-image-name="{{ $item->image ? basename($item->image) : '' }}">Düzenle</button>
                                <button class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800" data-delete
                                    data-url="{{ route('admin.announcements.destroy', $item->id) }}">Sil</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <template id="announcement-edit-template">
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
            <div class="w-full max-w-xl bg-white dark:bg-black rounded-lg border border-gray-200 dark:border-gray-800 p-4">
                <h3 class="font-semibold mb-3">Duyuru Düzenle</h3>
                <form class="space-y-3" data-edit-form>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id">
                    <div>
                        <label class="text-sm">Başlık</label>
                        <input type="text" name="title"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div>
                        <label class="text-sm">Görsel URL</label>
                        <input type="text" name="image"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div>
                        <label class="text-sm">Link</label>
                        <input type="text" name="link"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <label class="flex items-center gap-3">
                        <span>Aktif</span>
                        <input type="checkbox" name="is_active" value="1" class="toggle">
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
            const STORE_URL = "{{ route('admin.announcements.store') }}";
            const UPDATE_BASE_URL = "{{ url('/admin/announcements') }}";

            const $form = $('#announcement-form');
            const $formTitle = $('[data-form-title]');
            const $submitBtn = $form.find('[data-submit-label]');
            const $fileLabel = $form.find('[data-file-label]');
            const $fileInput = $form.find('input[type="file"][name="image"]');
            const $imagePreviewWrapper = $form.find('[data-image-preview-wrapper]');
            const $imagePreview = $form.find('[data-image-preview]');
            const $methodField = $form.find('[data-method-field]');

            function setFileLabel(text) {
                if ($fileLabel.length) {
                    $fileLabel.text(text || $fileLabel.data('default-text') || 'Dosya seçin');
                }
            }

            function hidePreview() {
                $imagePreviewWrapper.addClass('hidden');
                $imagePreview.attr('src', '');
            }

            function showPreview(url) {
                if (!url) {
                    hidePreview();
                    return;
                }
                $imagePreview.attr('src', url);
                $imagePreviewWrapper.removeClass('hidden');
            }

            function resetForm() {
                $form.trigger('reset');
                $form.attr('action', STORE_URL);
                $form.find('[name=id]').val('');
                $methodField.val('');
                $submitBtn.text('Ekle');
                $formTitle.text('Yeni Duyuru');
                setFileLabel();
                $fileInput.val('');
                hidePreview();
            }

            $('[data-reset-form]').on('click', resetForm);
            resetForm();

            $fileInput.on('change', function() {
                const file = this.files && this.files[0];
                if (!file) {
                    setFileLabel();
                    hidePreview();
                    return;
                }
                setFileLabel(file.name);
                const reader = new FileReader();
                reader.onload = function(event) {
                    showPreview(event.target?.result || '');
                };
                reader.readAsDataURL(file);
            });

            $form.on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                if (!$methodField.val()) {
                    formData.delete('_method');
                }
                $.ajax({
                    url: $form.attr('action'),
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        showSuccess(res?.msg);
                        setTimeout(function() {
                            location.reload();
                        }, 600);
                    },
                    error: function(xhr) {
                        showError(xhr.responseJSON?.msg || 'Hata');
                    }
                });
            });

            $(document).on('change', '[data-toggle-active]', function() {
                const id = $(this).data('id');
                const active = this.checked ? 1 : 0;
                const payload = {
                    _method: 'PUT',
                    title: '',
                    image: '',
                    link: '',
                    is_active: active
                };
                const row = $(this).closest('tr');
                const editBtn = row.find('[data-edit]');
                if (editBtn.length) {
                    try {
                        const data = editBtn.data('edit') || {};
                        payload.title = data.title || '';
                        payload.image = data.image || '';
                        payload.link = data.link || '';
                    } catch (_) {}
                }
                $.ajax({
                    url: UPDATE_BASE_URL + '/' + id,
                    method: 'POST',
                    data: payload,
                    success: function(res) {
                        showSuccess(res?.msg);
                        setTimeout(function() {
                            location.reload();
                        }, 400);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.msg || 'Hata';
                        showError(msg || 'Diğer duyuruyu pasif hale getiriniz.');
                        $(row).find('[data-toggle-active]').prop('checked', !active);
                    }
                });
            });

            $(document).on('click', '[data-edit]', function() {
                const $btn = $(this);
                const data = $btn.data('edit') || {};
                $form.attr('action', UPDATE_BASE_URL + '/' + data.id);
                $form.find('[name=id]').val(data.id);
                $form.find('[name=title]').val(data.title || '');
                $form.find('[name=link]').val(data.link || '');
                $form.find('[name=is_active]').prop('checked', !!data.is_active);
                $methodField.val('PUT');
                $submitBtn.text('Güncelle');
                $formTitle.text('Duyuru Düzenle');
                $fileInput.val('');

                const filename = $btn.data('image-name') || (data.image ? data.image.split('/').pop() : '');
                setFileLabel(filename);
                const previewUrl = $btn.data('image-url') || data.image_url || data.image || '';
                showPreview(previewUrl);

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        </script>
    @endpush
@endsection
