@extends('admin.layouts.app')

@section('title', 'Yönetici Log Geçmişi')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Yönetici Log Geçmişi</h1>
            <button onclick="exportToExcel()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <i class="ri-file-excel-2-line"></i>
                Excel'e Aktar
            </button>
        </div>
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <form action="{{ route('admin.logs.admin') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Yönetici</label>
                    <select name="user_id"
                        class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2">
                        <option value="">Tümü</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">İşlem Tipi</label>
                    <input type="text" name="type" value="{{ request('type') }}" placeholder="örn: Güncellendi"
                        class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Başlangıç Tarihi</label>
                    <input type="date" name="date_start" value="{{ request('date_start') }}"
                        class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Bitiş Tarihi</label>
                    <input type="date" name="date_end" value="{{ request('date_end') }}"
                        class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2">
                </div>
                <div class="md:col-span-4 flex justify-end gap-2">
                    <a href="{{ route('admin.logs.admin') }}"
                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Temizle</a>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">Filtrele</button>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div
            class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="logsTable" class="w-full text-left border-collapse">
                    <thead
                        class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 text-xs uppercase font-semibold text-gray-500">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Yönetici</th>
                            <th class="px-6 py-4">İşlem</th>
                            <th class="px-6 py-4">IP Adresi</th>
                            <th class="px-6 py-4">Tarih</th>
                            <th class="px-6 py-4 text-right">Detay</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 font-medium">#{{ $log->id }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center font-bold">
                                            {{ $log->user ? substr($log->user->name, 0, 1) : '?' }}
                                        </div>
                                        <span>{{ $log->user ? $log->user->name : 'Silinmiş Kullanıcı' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-medium 
                                {{ str_contains($log->type, 'Silindi')
                                    ? 'bg-red-100 text-red-700 dark:bg-red-900/30'
                                    : (str_contains($log->type, 'Oluşturuldu')
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30'
                                        : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30') }}">
                                        {{ $log->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500">{{ $log->ip_address }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button onclick="showLogDetail({{ $log->id }})"
                                        class="text-blue-600 hover:text-blue-700 font-medium">İncele</button>
                                    <div id="log-detail-{{ $log->id }}" class="hidden">
                                        <div class="before-data">@json($log->before)</div>
                                        <div class="after-data">@json($log->after)</div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">Kayıt bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Log Detail Modal -->
    <div id="logDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeLogDetail()"></div>
            <div
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-4xl relative z-10 max-h-[90vh] flex flex-col">
                <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-xl font-bold">İşlem Detayı</h3>
                    <button onclick="closeLogDetail()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase font-semibold text-gray-500">
                            <tr>
                                <th class="px-4 py-3 border border-gray-200 dark:border-gray-700">Alan</th>
                                <th class="px-4 py-3 border border-gray-200 dark:border-gray-700">Eski Veri</th>
                                <th class="px-4 py-3 border border-gray-200 dark:border-gray-700">Yeni Veri</th>
                            </tr>
                        </thead>
                        <tbody id="logComparisonBody" class="text-sm divide-y divide-gray-200 dark:divide-gray-800">
                        </tbody>
                    </table>
                    <div id="noDataMessage" class="hidden py-10 text-center text-gray-500">
                        Karşılaştırılacak veri bulunamadı.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        function showLogDetail(id) {
            const container = document.getElementById(`log-detail-${id}`);
            const beforeData = JSON.parse(container.querySelector('.before-data').textContent) || {};
            const afterData = JSON.parse(container.querySelector('.after-data').textContent) || {};

            const tbody = document.getElementById('logComparisonBody');
            tbody.innerHTML = '';

            // Tüm unique anahtarları topla
            const allKeys = [...new Set([...Object.keys(beforeData), ...Object.keys(afterData)])];

            // Gereksiz alanları filtrele
            const excludedKeys = ['created_at', 'updated_at', 'id', 'user_id', 'password', 'remember_token'];
            const keys = allKeys.filter(key => !excludedKeys.includes(key));

            if (keys.length === 0) {
                document.getElementById('noDataMessage').classList.remove('hidden');
            } else {
                document.getElementById('noDataMessage').classList.add('hidden');
                keys.forEach(key => {
                    const beforeVal = formatValue(beforeData[key]);
                    const afterVal = formatValue(afterData[key]);
                    const isChanged = beforeVal !== afterVal;

                    const tr = document.createElement('tr');
                    if (isChanged) tr.className = 'bg-yellow-50 dark:bg-yellow-900/10';

                    tr.innerHTML = `
                    <td class="px-4 py-3 font-semibold border border-gray-200 dark:border-gray-700 w-1/4">${key}</td>
                    <td class="px-4 py-3 border border-gray-200 dark:border-gray-700 break-all">${beforeVal}</td>
                    <td class="px-4 py-3 border border-gray-200 dark:border-gray-700 break-all ${isChanged ? 'text-blue-600 dark:text-blue-400 font-medium' : ''}">${afterVal}</td>
                `;
                    tbody.appendChild(tr);
                });
            }

            document.getElementById('logDetailModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function formatValue(val) {
            if (val === null || val === undefined) return '<span class="text-gray-400 italic">boş</span>';
            if (typeof val === 'object') return `<pre class="text-[10px]">${JSON.stringify(val, null, 2)}</pre>`;
            if (typeof val === 'boolean') return val ? 'Evet' : 'Hayır';
            return String(val);
        }

        function closeLogDetail() {
            document.getElementById('logDetailModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function exportToExcel() {
            const table = document.getElementById('logsTable');
            const rows = [];

            // Header
            const headers = [];
            table.querySelectorAll('thead th').forEach((th, index) => {
                if (index < 5) headers.push(th.innerText.trim());
            });
            rows.push(headers);

            // Data
            table.querySelectorAll('tbody tr').forEach(tr => {
                const row = [];
                tr.querySelectorAll('td').forEach((td, index) => {
                    if (index < 5) row.push(td.innerText.trim());
                });
                if (row.length > 0) rows.push(row);
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(rows);
            XLSX.utils.book_append_sheet(wb, ws, "Admin Logs");
            XLSX.writeFile(wb, "yönetici-loglari.xlsx");
        }
    </script>
@endpush
