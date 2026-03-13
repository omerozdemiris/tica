@extends('admin.layouts.app')

@section('title', 'Ana Sayfa Bölümleri')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Ana Sayfa Bölümleri</h1>
        <a href="{{ route('admin.home-sections.create') }}"
            class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black flex items-center gap-2">
            <i class="ri-add-line"></i><span>Yeni Bölüm</span>
        </a>
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="w-10 px-3 py-2 text-center"></th>
                    <th class="text-left px-3 py-2">Bölüm Adı</th>
                    <th class="text-left px-3 py-2">Başlık</th>
                    <th class="text-center px-3 py-2">Aktif</th>
                    <th class="text-right px-3 py-2">İşlemler</th>
                </tr>
            </thead>
            <tbody id="sortable-sections">
                @foreach ($sections as $section)
                    <tr class="border-t border-gray-100 dark:border-gray-900 bg-white dark:bg-black/20"
                        data-id="{{ $section->id }}">
                        <td class="px-3 py-2 text-center cursor-move text-gray-400">
                            <i class="ri-grid-line-alt"></i>
                        </td>
                        <td class="px-3 py-2 font-medium">
                            {{ $section->name }}
                            @if ($section->is_fixed)
                                <span
                                    class="ml-2 text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 uppercase tracking-wider">Sistem</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $section->title }}</td>
                        <td class="px-3 py-2 text-center">
                            <input type="checkbox" class="toggle section-status" data-id="{{ $section->id }}"
                                @checked($section->is_active)>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <div class="flex items-center gap-2 justify-end">
                                @if (!$section->is_fixed)
                                    <a href="{{ route('admin.home-sections.edit', $section->id) }}"
                                        class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 inline-flex items-center gap-1 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                        <i class="ri-pencil-line"></i><span>Düzenle</span>
                                    </a>
                                    <button data-delete data-url="{{ route('admin.home-sections.destroy', $section->id) }}"
                                        class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-black inline-flex items-center gap-1 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 transition-colors">
                                        <i class="ri-delete-bin-line"></i><span>Sil</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            new Sortable(document.getElementById('sortable-sections'), {
                animation: 150,
                handle: '.cursor-move',
                onEnd: function() {
                    const order = [];
                    $('#sortable-sections tr').each(function() {
                        order.push($(this).data('id'));
                    });

                    $.ajax({
                        url: "{{ route('admin.home-sections.sort') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            order: order
                        },
                        success: function(res) {
                            showSuccess(res?.msg);
                        }
                    });
                }
            });

            $(document).on('change', '.section-status', function() {
                const id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/home-sections') }}/" + id + "/status",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        _method: "PATCH"
                    },
                    success: function(res) {
                        showSuccess(res?.msg);
                    }
                });
            });
        </script>
    @endpush
@endsection
