@extends('admin.layouts.app')
@section('title', 'Promosyonlar')
@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Promosyonlar / İndirim Kuponları</h1>
        <a href="{{ route('admin.promotions.create') }}"
            class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black flex items-center gap-2">
            <i class="ri-add-line"></i><span>Yeni Promosyon</span>
        </a>
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-black">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="text-left px-3 py-2">Kupon Kodu</th>
                    <th class="text-left px-3 py-2">İndirim Oranı</th>
                    <th class="text-left px-3 py-2">Koşul</th>
                    <th class="text-left px-3 py-2">Kullanım</th>
                    <th class="text-left px-3 py-2">Herkese Açık</th>
                    <th class="text-center px-3 py-2">Aktif</th>
                    <th class="text-right px-3 py-2">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($promotions as $promotion)
                    <tr class="border-t border-gray-100 dark:border-gray-900 bg-white dark:bg-black/20">
                        <td class="px-3 py-2 font-bold">{{ $promotion->code }}</td>
                        <td class="px-3 py-2"><span
                                class="bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-100 px-2 py-0.5 rounded text-xs">%{{ $promotion->discount_rate }}</span>
                        </td>
                        <td class="px-3 py-2">
                            @if ($promotion->condition_type == 1)
                                <span class="text-gray-500">Kota Sınırlı (Limit: {{ $promotion->usage_limit }})</span>
                            @elseif ($promotion->condition_type == 2)
                                <span class="text-gray-500">{{ $promotion->start_date?->format('d.m.Y H:i') }} -
                                    {{ $promotion->end_date?->format('d.m.Y H:i') }}</span>
                            @else
                                <span class="text-gray-500">Min. Tutar: {{ number_format($promotion->min_total, 2, ',', '.') }} ₺</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">{{ $promotion->usage_count }} kez kullanıldı</td>
                        <td class="px-3 py-2 text-center">
                            <input type="checkbox" class="toggle promotion-public" data-id="{{ $promotion->id }}"
                                @checked($promotion->public)>
                        </td>
                        <td class="px-3 py-2 text-center">
                            <input type="checkbox" class="toggle promotion-status" data-id="{{ $promotion->id }}"
                                @checked($promotion->is_active)>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="{{ route('admin.promotions.edit', $promotion->id) }}"
                                    class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 inline-flex items-center gap-1">
                                    <i class="ri-pencil-line"></i><span>Düzenle</span>
                                </a>
                                <button data-delete data-url="{{ route('admin.promotions.destroy', $promotion->id) }}"
                                    class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-black inline-flex items-center gap-1">
                                    <i class="ri-delete-bin-line"></i><span>Sil</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @push('scripts')
        <script>
            $(document).on('change', '.promotion-status', function() {
                const id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/promotions') }}/" + id + "/status",
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
            $(document).on('change', '.promotion-public', function() {
                const id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/promotions') }}/" + id + "/public",
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
