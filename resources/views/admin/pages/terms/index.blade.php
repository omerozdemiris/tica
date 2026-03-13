@extends('admin.layouts.app')

@section('title', 'Terimler')

@section('content')
    <style>
        /* Scrollable but scrollbar hidden (cross-browser) */
        .terms-no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .terms-no-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Terimler</h1>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-black h-[30rem]">
            <h2 class="font-semibold mb-3">Yeni Terim</h2>
            <form id="term-create" class="space-y-3" enctype="multipart/form-data">
                @csrf
                <div>
                    <label class="text-sm">Nitelik</label>
                    <select name="attribute_id" class="js-select w-full mt-1">
                        @foreach ($attributes as $attr)
                            <option value="{{ $attr->id }}">{{ $attr->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm">Başlık</label>
                    <input type="text" name="name"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                </div>
                <div>
                    <label class="text-sm">Değer Türü</label>
                    <div class="mt-2 grid grid-cols-2 gap-2" data-term-type-toggle>
                        <label class="cursor-pointer">
                            <input type="radio" name="term_type" value="value" class="sr-only" checked>
                            <div
                                class="term-type-btn flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                                <div class="flex flex-col items-start leading-tight">
                                    <div class="flex items-center gap-2">
                                        <i class="ri-input-method-line text-gray-400"></i>
                                        <span class="text-sm font-medium">Değer</span>
                                    </div>
                                    <span class="text-[10px] font-semibold text-gray-400 mt-0.5">Metinsel ya da renk kodu
                                        gibi sade içerik</span>
                                </div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="term_type" value="file" class="sr-only">
                            <div
                                class="term-type-btn flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                                <div class="flex flex-col items-start leading-tight">
                                    <div class="flex items-center gap-2">
                                        <i class="ri-image-line text-gray-400"></i>
                                        <span class="text-sm font-medium">Görsel</span>
                                    </div>
                                    <span class="text-[10px] font-semibold text-gray-400 mt-0.5">İkon, doku ya da renk
                                        görseli</span>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="mt-3" data-term-type-panel="value">
                        <label class="text-sm">Metin</label>
                        <input type="text" name="value"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                        <p class="text-xs text-gray-500 mt-1">Örn: renk kodu, kısa açıklama vb.</p>
                    </div>
                    <div class="mt-3 hidden" data-term-type-panel="file">
                        <label class="text-sm">Görsel (max 200KB)</label>
                        <label
                            class="mt-2 flex items-center justify-between gap-3 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                            <div class="flex items-center gap-3">
                                <i class="ri-upload-line text-xl text-gray-400"></i>
                                <span class="text-sm text-gray-600 dark:text-gray-300" data-file-label
                                    data-default-text="Görsel seçin...">Görsel seçin...</span>
                            </div>
                            <span
                                class="text-[10px] px-2 py-1 bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-lg shadow-sm">Gözat</span>
                            <input type="file" name="file" accept="image/*" class="sr-only" data-file-input disabled>
                        </label>
                        <p class="text-xs text-gray-500 mt-1">Her terim için isteğe bağlı görsel yükleyebilirsiniz.</p>
                    </div>
                </div>
                <div class="flex items-center justify-end">
                    <button
                        class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">Ekle</button>
                </div>
            </form>
        </div>
        <div class="space-y-4">
            @php
                $grouped = $terms->groupBy('attribute_id');
            @endphp
            @forelse ($grouped as $attributeId => $group)
                @php
                    $attr = $group->first()?->attribute;
                    $attrName = $attr?->name ?? 'Nitelik Yok';
                @endphp
                <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black overflow-hidden">
                    <div
                        class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-900 bg-gray-50 dark:bg-gray-900/40">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                {{ $attrName }}
                            </span>
                            <span
                                class="text-[11px] px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-300">
                                {{ $group->count() }} terim
                            </span>
                        </div>
                    </div>
                    <div class="max-h-[16rem] overflow-y-auto terms-no-scrollbar">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0 z-10">
                                <tr>
                                    <th class="text-left px-3 py-2">ID</th>
                                    <th class="text-left px-3 py-2">Başlık</th>
                                    <th class="text-left px-3 py-2">Değer</th>
                                    <th class="text-left px-3 py-2">Görsel</th>
                                    <th class="text-right px-3 py-2">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($group as $term)
                                    <tr class="border-t border-gray-100 dark:border-gray-900">
                                        <td class="px-3 py-2">{{ $term->id }}</td>
                                        <td class="px-3 py-2">{{ $term->name }}</td>
                                        <td class="px-3 py-2">
                                            @if (str_starts_with($term->value, '#'))
                                                <span class="group relative inline-block">
                                                    <span class="block w-7 h-7 rounded-full cursor-pointer"
                                                        style="background-color: {{ $term->value }}"></span>
                                                    <span class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap text-[10px] text-white bg-black/80 rounded-lg px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity duration-150 z-10">{{ $term->value }}</span>
                                                </span>
                                            @else
                                                {{ $term->value }}
                                            @endif
                                        </td>
                                        <td class="px-3 py-2">
                                            @if ($term->file)
                                                <img src="{{ asset($term->file) }}" alt="{{ $term->name }}"
                                                    class="h-6 max-w-[80px] object-contain rounded bg-white border border-gray-100">
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800"
                                                data-edit='@json($term)'>Düzenle</button>
                                            <button class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800"
                                                data-delete
                                                data-url="{{ route('admin.terms.destroy', $term->id) }}">Sil</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div
                    class="rounded-lg border border-dashed border-gray-200 dark:border-gray-800 bg-white dark:bg-black/40 px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    Henüz eklenmiş terim bulunmuyor.
                </div>
            @endforelse
        </div>
    </div>

    <template id="term-edit-template">
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
            <div class="w-full max-w-xl bg-white dark:bg-black rounded-lg border border-gray-200 dark:border-gray-800 p-4">
                <h3 class="font-semibold mb-3">Terim Düzenle</h3>
                <form class="space-y-3" data-edit-form enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id">
                    <div>
                        <label class="text-sm">Nitelik</label>
                        <select name="attribute_id" class="js-select w-full mt-1">
                            @foreach ($attributes as $attr)
                                <option value="{{ $attr->id }}">{{ $attr->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm">Başlık</label>
                        <input type="text" name="name"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div>
                        <label class="text-sm">Değer</label>
                        <input type="text" name="value"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div data-edit-term-type>
                        <label class="text-sm">Terim Türü</label>
                        <div class="mt-2 grid grid-cols-2 gap-2" data-term-type-toggle>
                            <label class="cursor-pointer">
                                <input type="radio" name="term_type" value="value" class="sr-only">
                                <div
                                    class="term-type-btn flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                                    <div class="flex flex-col items-start leading-tight">
                                        <div class="flex items-center gap-2">
                                            <i class="ri-input-method-line text-gray-400"></i>
                                            <span class="text-sm font-medium">Değer</span>
                                        </div>
                                        <span class="text-[10px] font-semibold text-gray-400 mt-0.5">Metinsel ya da renk
                                            kodu gibi sade içerik</span>
                                    </div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="term_type" value="file" class="sr-only">
                                <div
                                    class="term-type-btn flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                                    <div class="flex flex-col items-start leading-tight">
                                        <div class="flex items-center gap-2">
                                            <i class="ri-image-line text-gray-400"></i>
                                            <span class="text-sm font-medium">Görsel</span>
                                        </div>
                                        <span class="text-[10px] font-semibold text-gray-400 mt-0.5">İkon, doku ya da renk
                                            görseli</span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="mt-3" data-term-type-panel="value">
                            <label class="text-sm">Değer</label>
                            <input type="text" name="value"
                                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                        </div>

                        <div class="mt-3 hidden" data-term-type-panel="file">
                            <label class="text-sm">Mevcut Görsel</label>
                            <div class="mt-2 flex items-center gap-3">
                                <img data-file-preview src="" alt=""
                                    class="h-8 max-w-[120px] object-contain rounded bg-white border border-gray-100 hidden">
                                <span data-file-empty class="text-xs text-gray-400">Görsel yok</span>
                                <label
                                    class="ml-auto inline-flex items-center gap-2 text-xs cursor-pointer px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800">
                                    <input type="checkbox" name="remove_file" value="1" class="mr-1">
                                    Görseli kaldır
                                </label>
                            </div>
                            <div class="mt-3">
                                <label class="text-sm">Yeni Görsel (max 200KB)</label>
                                <label
                                    class="mt-2 flex items-center justify-between gap-3 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <i class="ri-upload-line text-xl text-gray-400"></i>
                                        <span class="text-sm text-gray-600 dark:text-gray-300" data-file-label
                                            data-default-text="Görsel seçin...">Görsel seçin...</span>
                                    </div>
                                    <span
                                        class="text-[10px] px-2 py-1 bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-lg shadow-sm">Gözat</span>
                                    <input type="file" name="file" accept="image/*" class="sr-only"
                                        data-file-input disabled>
                                </label>
                            </div>
                        </div>
                    </div>
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
            $('#term-create').on('submit', function(e) {
                e.preventDefault();
                const formEl = this;
                const fd = new FormData(formEl);
                $.ajax({
                    url: "{{ route('admin.terms.store') }}",
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
                        showError(xhr.responseJSON?.msg || 'Hata');
                    }
                });
            });

            $(document).on('click', '[data-edit]', function() {
                const data = $(this).data('edit');
                const tpl = $($('#term-edit-template').html());
                const form = tpl.find('[data-edit-form]');
                form.attr('action', "{{ url('/admin/terms') }}/" + data.id);
                form.find('[name=id]').val(data.id);
                form.find('[name=attribute_id]').val(data.attribute_id || '');
                form.find('[name=name]').val(data.name || '');
                form.find('[name=value]').val(data.value || '');
                form.find('[name=remove_file]').prop('checked', false);

                const preview = tpl.find('[data-file-preview]');
                const empty = tpl.find('[data-file-empty]');
                if (data.file) {
                    preview.attr('src', "{{ asset('') }}" + data.file).removeClass('hidden');
                    empty.addClass('hidden');
                } else {
                    preview.addClass('hidden').attr('src', '');
                    empty.removeClass('hidden');
                }

                // term_type defaults: if file exists -> file, else value
                const type = data.file ? 'file' : 'value';
                tpl.find('input[name="term_type"][value="' + type + '"]').prop('checked', true).trigger('change');

                $('body').append(tpl);
            });

            $(document).on('click', '[data-close]', function() {
                $(this).closest('.fixed.inset-0').remove();
            });

            $(document).on('submit', '[data-edit-form]', function(e) {
                e.preventDefault();
                const id = $(this).find('[name=id]').val();
                const formEl = this;
                const fd = new FormData(formEl);
                $.ajax({
                    url: "{{ url('/admin/terms') }}/" + id,
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
                        showError(xhr.responseJSON?.msg || 'Hata');
                    }
                });
            });

            $(document).on('change', '[data-file-input]', function() {
                const file = this.files && this.files[0] ? this.files[0] : null;
                const wrap = $(this).closest('label');
                const label = wrap.find('[data-file-label]');
                const defaultText = label.data('default-text') || 'Dosya seçin...';
                label.text(file ? file.name : defaultText);
            });

            function syncTermTypeUI(scope) {
                const $scope = scope ? $(scope) : $(document);
                $scope.find('[data-term-type-toggle]').each(function() {
                    const $toggle = $(this);
                    const $container = $toggle.closest('div');
                    const val = $toggle.find('input[type="radio"][name="term_type"]:checked').val() || 'value';
                    const $root = $toggle.closest('div').parent();

                    // button styles
                    $toggle.find('.term-type-btn').removeClass(
                        'ring-2 ring-black dark:ring-white bg-gray-50 dark:bg-gray-900');
                    $toggle.find('input[type="radio"][name="term_type"]:checked').closest('label').find(
                            '.term-type-btn')
                        .addClass('ring-2 ring-black dark:ring-white bg-gray-50 dark:bg-gray-900');

                    // panels
                    const $valuePanel = $root.find('[data-term-type-panel="value"]').first();
                    const $filePanel = $root.find('[data-term-type-panel="file"]').first();
                    const $valueInput = $valuePanel.find('input[name="value"]').first();
                    const $fileInput = $filePanel.find('input[type="file"][name="file"]').first();

                    if (val === 'file') {
                        $valuePanel.addClass('hidden');
                        $filePanel.removeClass('hidden');
                        $valueInput.prop('disabled', true);
                        $fileInput.prop('disabled', false);
                    } else {
                        $filePanel.addClass('hidden');
                        $valuePanel.removeClass('hidden');
                        $fileInput.prop('disabled', true);
                        $valueInput.prop('disabled', false);
                    }
                });
            }

            $(document).on('change', 'input[type="radio"][name="term_type"]', function() {
                syncTermTypeUI($(this).closest('form'));
            });

            // initial state for create form
            syncTermTypeUI($('#term-create'));
        </script>
    @endpush
@endsection
