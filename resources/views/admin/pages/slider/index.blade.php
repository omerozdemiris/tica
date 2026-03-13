@extends('admin.layouts.app')

@section('title', 'Slider Yönetimi')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Slider Yönetimi</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-black">
            <h2 class="font-semibold mb-3" data-form-title>Yeni Slider</h2>
            <form id="slider-form" class="space-y-3" enctype="multipart/form-data" data-mode="create">
                @csrf
                <input type="hidden" name="id">
                <input type="hidden" name="_method" value="POST" data-method>
                
                <!-- Desktop Image -->
                <div>
                    <label class="text-sm">Görsel (Masaüstü)</label>
                    <label
                        class="mt-1 flex items-center justify-between gap-3 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900">
                        <div class="flex items-center gap-3">
                            <i class="ri-image-line text-xl text-gray-500"></i>
                            <span class="text-sm text-gray-600 dark:text-gray-300" data-file-label data-type="desktop" data-default-text="Dosya seçin...">Dosya seçin...</span>
                        </div>
                        <span class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-700 rounded-md">Gözat</span>
                        <input type="file" name="image_file" accept="image/*" class="sr-only" data-file-input data-type="desktop">
                    </label>
                    <div class="mt-2" data-image-preview-wrapper data-type="desktop" style="display:none;">
                        <img data-image-preview data-type="desktop" src=""
                            class="max-h-32 rounded border border-gray-200 dark:border-gray-800">
                    </div>
                </div>

                <!-- Mobile Image -->
                <div>
                    <label class="text-sm">Görsel (Mobil)</label>
                    <label
                        class="mt-1 flex items-center justify-between gap-3 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900">
                        <div class="flex items-center gap-3">
                            <i class="ri-smartphone-line text-xl text-gray-500"></i>
                            <span class="text-sm text-gray-600 dark:text-gray-300" data-file-label data-type="mobile" data-default-text="Dosya seçin...">Dosya seçin...</span>
                        </div>
                        <span class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-700 rounded-md">Gözat</span>
                        <input type="file" name="mobile_image_file" accept="image/*" class="sr-only" data-file-input data-type="mobile">
                    </label>    
                    <div class="mt-2" data-image-preview-wrapper data-type="mobile" style="display:none;">
                        <img data-image-preview data-type="mobile" src=""
                            class="max-h-32 rounded border border-gray-200 dark:border-gray-800">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm">Başlık</label>
                        <input type="text" name="title"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div>
                        <label class="text-sm">Buton Yazısı</label>
                        <input type="text" name="button_text"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm">Buton Linki</label>
                        <input type="text" name="button_link"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div>
                        <label class="text-sm">Sıra</label>
                        <input type="number" min="0" name="sort_order"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                </div>
                <div>
                    <label class="text-sm">Açıklama</label>
                    <input type="text" name="description"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                </div>
                <label class="flex items-center gap-3">
                    <span>Aktif</span>
                    <input type="checkbox" name="is_active" value="1" class="toggle">
                </label>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" data-cancel-edit
                        class="hidden px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800">İptal</button>
                    <button data-submit-btn
                        class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">Ekle</button>
                </div>
            </form>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-black">
            <table class="min-w-full text-sm" data-datatable>
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="text-left px-3 py-2">Sıra</th>
                        <th class="text-left px-3 py-2">Başlık</th>
                        <th class="text-left px-3 py-2">Aktif</th>
                        <th class="text-right px-3 py-2">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sliders as $slider)
                        @php
                            $sliderPayload = [
                                'id' => $slider->id,
                                'title' => $slider->title,
                                'description' => $slider->description,
                                'description_html' => $slider->description,
                                'button_text' => $slider->button_text,
                                'button_link' => $slider->button_link,
                                'sort_order' => $slider->sort_order,
                                'is_active' => $slider->is_active,
                                'image' => $slider->image ? asset($slider->image) : null,
                                'mobile_image' => $slider->mobile_image ? asset($slider->mobile_image) : null,
                            ];
                        @endphp
                        <tr class="border-t border-gray-100 dark:border-gray-900">
                            <td class="px-3 py-2">{{ $slider->sort_order }}</td>
                            <td class="px-3 py-2">{{ $slider->title ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $slider->is_active ? 'Evet' : 'Hayır' }}</td>
                            <td class="px-3 py-2 text-right">
                                <button
                                    class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 flex items-center gap-1 inline-flex"
                                    data-edit='@json($sliderPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)'><i
                                        class="ri-pencil-line"></i><span>Düzenle</span></button>
                                <button
                                    class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 flex items-center gap-1 inline-flex"
                                    data-delete data-url="{{ route('admin.slider.destroy', $slider->id) }}">Sil</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


    @push('scripts')
        <script>
            // Preview Image Logic (Desktop ve Mobile için ayrıldı)
            $(document).on('change', 'input[data-file-input]', function() {
                const type = $(this).data('type'); // desktop or mobile
                const file = this.files[0];
                const $label = $(`span[data-file-label][data-type="${type}"]`);
                const $previewWrapper = $(`div[data-image-preview-wrapper][data-type="${type}"]`);
                const $previewImg = $(`img[data-image-preview][data-type="${type}"]`);

                if (file) {
                    $label.text(file.name);
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $previewImg.attr('src', e.target.result);
                        $previewWrapper.show();
                    }
                    reader.readAsDataURL(file);
                } else {
                    $label.text($label.data('default-text'));
                    $previewWrapper.hide();
                    $previewImg.attr('src', '');
                }
            });

            // Create/Update
            $('#slider-form').on('submit', function(e) {
                e.preventDefault();
                const fd = new FormData(this);
                const mode = $(this).data('mode') || 'create';
                let url = "{{ route('admin.slider.store') }}";
                if (mode === 'edit') {
                    const id = $(this).find('[name=id]').val();
                    url = "{{ url('/admin/slider') }}/" + id;
                    fd.set('_method', 'PUT');
                }
                $.ajax({
                    url: url,
                    method: "POST",
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        // showSuccess fonksiyonunuz varsa kullanın, yoksa alert
                        if(typeof showSuccess === 'function') {
                            showSuccess(res?.msg || 'İşlem Başarılı');
                        } else {
                            alert(res?.msg || 'İşlem Başarılı');
                        }
                        setTimeout(function() {
                            location.reload();
                        }, 600);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.msg || 'Hata';
                        if(typeof showError === 'function') {
                            showError(msg);
                        } else {
                            alert(msg);
                        }
                    }
                });
            });

            // Edit Mode
            $(document).on('click', '[data-edit]', function() {
                const data = $(this).data('edit');
                const $form = $('#slider-form');
                $form.attr('data-mode', 'edit');
                $('[data-form-title]').text('Slider Düzenle');
                $('[data-submit-btn]').text('Güncelle');
                $('[data-cancel-edit]').removeClass('hidden');
                $form.find('[name=_method][data-method]').val('PUT');
                $form.find('[name=id]').val(data.id || '');
                $form.find('[name=title]').val(data.title || '');
                $form.find('[name=button_text]').val(data.button_text || '');
                $form.find('[name=button_link]').val(data.button_link || '');
                $form.find('[name=sort_order]').val(data.sort_order || 0);
                $form.find('[name=description]').val(data.description_html || data.description || '');
                $form.find('[name=is_active]').prop('checked', !!data.is_active);
                
                // Desktop Preview
                if (data.image) {
                    $('[data-image-preview][data-type="desktop"]').attr('src', data.image);
                    $('[data-image-preview-wrapper][data-type="desktop"]').show();
                } else {
                    $('[data-image-preview][data-type="desktop"]').attr('src', '');
                    $('[data-image-preview-wrapper][data-type="desktop"]').hide();
                }

                // Mobile Preview
                if (data.mobile_image) {
                    $('[data-image-preview][data-type="mobile"]').attr('src', data.mobile_image);
                    $('[data-image-preview-wrapper][data-type="mobile"]').show();
                } else {
                    $('[data-image-preview][data-type="mobile"]').attr('src', '');
                    $('[data-image-preview-wrapper][data-type="mobile"]').hide();
                }
            });

            // Cancel edit
            $(document).on('click', '[data-cancel-edit]', function() {
                const $form = $('#slider-form');
                $form.attr('data-mode', 'create')[0].reset();
                $('[data-form-title]').text('Yeni Slider');
                $('[data-submit-btn]').text('Ekle');
                $(this).addClass('hidden');
                $form.find('[name=_method][data-method]').val('POST');
                $form.find('[name=id]').val('');
                
                // Reset Previews
                $('[data-image-preview]').attr('src', '');
                $('[data-image-preview-wrapper]').hide();
                
                // Reset File Labels
                $('[data-file-label]').each(function() {
                    $(this).text($(this).data('default-text'));
                });
            });
        </script>
    @endpush
@endsection
