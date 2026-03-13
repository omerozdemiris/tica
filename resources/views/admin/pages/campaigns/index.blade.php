@extends('admin.layouts.app')

@section('title', 'Bildirim Çubuğu')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Bildirim Çubuğu</h1>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-black">
            <h2 class="font-semibold mb-3">Yeni Bildiirim</h2>
            <form id="campaign-create" class="space-y-3">
                @csrf
                <div>
                    <label class="text-sm">Başlık</label>
                    <input type="text" name="title"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                </div>
                <div>
                    <label class="text-sm">Link (opsiyonel)</label>
                    <input type="text" name="link"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                </div>
                <div>
                    <label class="text-sm">Bölüm</label>
                    <select name="section" id="campaign-section-create"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                        <option value="header">Header</option>
                        <option value="footer">Footer</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm">Arkaplan Rengi</label>
                    <select name="background_color" id="campaign-color-create"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                        @foreach ($colors as $name => $code)
                            <option value="{{ $code }}" data-color="{{ $code }}">{{ $name }}
                            </option>
                        @endforeach
                    </select>
                    <div id="campaign-color-preview-create"
                        class="mt-2 h-8 w-full rounded border border-gray-200 dark:border-gray-800"></div>
                </div>
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
                        <th class="text-left px-3 py-2">Başlık</th>
                        <th class="text-left px-3 py-2">Bölüm</th>
                        <th class="text-left px-3 py-2">Renk</th>
                        <th class="text-right px-3 py-2">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($campaigns as $item)
                        @php
                            $colorName = array_search($item->background_color, $colors) ?: 'Renk Yok';
                        @endphp
                        <tr class="border-t border-gray-100 dark:border-gray-900">
                            <td class="px-3 py-2">{{ Str::limit($item->title, 50) }}</td>
                            <td class="px-3 py-2 capitalize">{{ $item->section }}</td>
                            <td class="px-3 py-2">
                                <div class="inline-flex items-center gap-2">
                                    <span class="inline-block w-4 h-4 rounded border border-gray-300 dark:border-gray-700"
                                        style="background: {{ $item->background_color }}"></span>
                                    <span class="text-xs">{{ $colorName }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <button class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800"
                                    data-edit='@json($item)'>Düzenle</button>
                                <button class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800" data-delete
                                    data-url="{{ route('admin.campaigns.destroy', $item->id) }}">Sil</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <template id="campaign-edit-template">
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
            <div class="w-full max-w-xl bg-white dark:bg-black rounded-lg border border-gray-200 dark:border-gray-800 p-4">
                <h3 class="font-semibold mb-3">Bildiirim Düzenle</h3>
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
                        <label class="text-sm">Link</label>
                        <input type="text" name="link"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>
                    <div>
                        <label class="text-sm">Bölüm</label>
                        <select name="section" id="campaign-section-edit"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                            <option value="header">Header</option>
                            <option value="footer">Footer</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm">Arkaplan Rengi</label>
                        <select name="background_color" id="campaign-color-edit"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                            @foreach ($colors as $name => $code)
                                <option value="{{ $code }}" data-color="{{ $code }}">{{ $name }}
                                </option>
                            @endforeach
                        </select>
                        <div id="campaign-color-preview-edit"
                            class="mt-2 h-8 w-full rounded border border-gray-200 dark:border-gray-800"></div>
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
            const campaignColors = @json($colors);

            function setColorPreview(elSelect, elPreview) {
                try {
                    const val = $(elSelect).val();
                    $(elPreview).css('background', val || 'transparent');
                } catch (_) {}
            }

            function applyDarkModeToTomSelect(tsInstance) {
                if (tsInstance && tsInstance.control) {
                    const $control = $(tsInstance.control);
                    const isDark = document.documentElement.classList.contains('dark');
                    if (isDark) {
                        $control.addClass('dark:bg-black dark:border-gray-800 dark:text-white');
                        $control.css({
                            'background-color': '#000000',
                            'border-color': '#1f2937',
                            'color': '#ffffff'
                        });
                    } else {
                        $control.css({
                            'background-color': '#ffffff',
                            'border-color': '#e5e7eb',
                            'color': '#000000'
                        });
                    }
                }
            }

            function initCampaignColorSelect(selectId) {
                const $select = $(selectId);
                if ($select.length && !$select[0].tomselect) {
                    const ts = new TomSelect(selectId, {
                        placeholder: 'Renk seçin...',
                        allowEmptyOption: false,
                        copyClassesToDropdown: true,
                        render: {
                            option: function(data, escape) {
                                const colorCode = data.value;
                                const colorName = data.text;
                                return '<div class="flex items-center gap-2 p-2">' +
                                    '<span class="inline-block w-5 h-5 rounded border border-gray-300 dark:border-gray-700" style="background: ' +
                                    escape(colorCode) + '"></span>' +
                                    '<span>' + escape(colorName) + '</span>' +
                                    '</div>';
                            },
                            item: function(data, escape) {
                                const colorCode = data.value;
                                const colorName = data.text;
                                return '<div class="flex items-center gap-2">' +
                                    '<span class="inline-block w-4 h-4 rounded border border-gray-300 dark:border-gray-700" style="background: ' +
                                    escape(colorCode) + '"></span>' +
                                    '<span>' + escape(colorName) + '</span>' +
                                    '</div>';
                            }
                        },
                        onInitialize: function() {
                            applyDarkModeToTomSelect(this);
                        }
                    });
                    setTimeout(() => applyDarkModeToTomSelect(ts), 50);
                    $select.on('change', function() {
                        setColorPreview(this, '#campaign-color-preview-create');
                    });
                    setColorPreview(selectId, '#campaign-color-preview-create');
                }
            }

            function initCampaignSectionSelect(selectId) {
                const $select = $(selectId);
                if ($select.length && !$select[0].tomselect) {
                    const ts = new TomSelect(selectId, {
                        placeholder: 'Bölüm seçin...',
                        allowEmptyOption: false,
                        copyClassesToDropdown: true,
                        render: {
                            option: function(data, escape) {
                                return '<div class="text-sm px-2 py-1">' + escape(data.text) + '</div>';
                            }
                        },
                        onInitialize: function() {
                            applyDarkModeToTomSelect(this);
                        }
                    });
                    setTimeout(() => applyDarkModeToTomSelect(ts), 50);
                }
            }

            initCampaignColorSelect('#campaign-color-create');
            initCampaignSectionSelect('#campaign-section-create');

            $('#campaign-create').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('admin.campaigns.store') }}",
                    method: "POST",
                    data: $(this).serialize(),
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
                const tpl = $($('#campaign-edit-template').html());
                const form = tpl.find('[data-edit-form]');
                form.attr('action', "{{ url('/admin/campaigns') }}/" + data.id);
                form.find('[name=id]').val(data.id);
                form.find('[name=title]').val(data.title || '');
                form.find('[name=link]').val(data.link || '');
                form.find('[name=section]').val(data.section || 'header');
                form.find('[name=background_color]').val(data.background_color || '');
                $('body').append(tpl);

                setTimeout(function() {
                    const $editColorSelect = tpl.find('#campaign-color-edit');
                    const $editSectionSelect = tpl.find('#campaign-section-edit');

                    if ($editColorSelect.length && !$editColorSelect[0].tomselect) {
                        const ts = new TomSelect('#campaign-color-edit', {
                            placeholder: 'Renk seçin...',
                            allowEmptyOption: false,
                            copyClassesToDropdown: true,
                            render: {
                                option: function(data, escape) {
                                    const colorCode = data.value;
                                    const colorName = data.text;
                                    return '<div class="flex items-center gap-2 p-2">' +
                                        '<span class="inline-block w-5 h-5 rounded border border-gray-300 dark:border-gray-700" style="background: ' +
                                        escape(colorCode) + '"></span>' +
                                        '<span>' + escape(colorName) + '</span>' +
                                        '</div>';
                                },
                                item: function(data, escape) {
                                    const colorCode = data.value;
                                    const colorName = data.text;
                                    return '<div class="flex items-center gap-2">' +
                                        '<span class="inline-block w-4 h-4 rounded border border-gray-300 dark:border-gray-700" style="background: ' +
                                        escape(colorCode) + '"></span>' +
                                        '<span>' + escape(colorName) + '</span>' +
                                        '</div>';
                                }
                            },
                            onInitialize: function() {
                                applyDarkModeToTomSelect(this);
                            }
                        });
                        setTimeout(() => {
                            applyDarkModeToTomSelect(ts);
                            ts.setValue(data.background_color || '');
                        }, 50);
                        $editColorSelect.on('change', function() {
                            setColorPreview(this, tpl.find('#campaign-color-preview-edit'));
                        });
                    }

                    if ($editSectionSelect.length && !$editSectionSelect[0].tomselect) {
                        const tsSection = new TomSelect('#campaign-section-edit', {
                            placeholder: 'Bölüm seçin...',
                            allowEmptyOption: false,
                            copyClassesToDropdown: true,
                            render: {
                                option: function(data, escape) {
                                    return '<div class="text-sm px-2 py-1">' + escape(data.text) +
                                        '</div>';
                                }
                            },
                            onInitialize: function() {
                                applyDarkModeToTomSelect(this);
                            }
                        });
                        setTimeout(() => applyDarkModeToTomSelect(tsSection), 50);
                    }

                    setColorPreview(tpl.find('#campaign-color-edit'), tpl.find('#campaign-color-preview-edit'));
                }, 100);
            });

            $(document).on('click', '[data-close]', function() {
                $(this).closest('.fixed.inset-0').remove();
            });

            $(document).on('submit', '[data-edit-form]', function(e) {
                e.preventDefault();
                const id = $(this).find('[name=id]').val();
                const data = $(this).serialize();
                $.ajax({
                    url: "{{ url('/admin/campaigns') }}/" + id,
                    method: "POST",
                    data: data,
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
        </script>
    @endpush
@endsection
