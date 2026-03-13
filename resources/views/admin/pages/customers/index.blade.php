@extends('admin.layouts.app')
@section('title', 'Müşteriler')
@section('content')
    @php($filters = $filters ?? [])
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Müşteriler</h1>
    </div>
    <form method="GET" action="{{ route('admin.customers.index') }}"
        class="mb-6 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Ad Soyad</label>
                <input type="text" name="name" value="{{ $filters['name'] ?? '' }}"
                    class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"
                    placeholder="Ad Soyad">
            </div>
            <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">E-posta</label>
                <input type="text" name="email" value="{{ $filters['email'] ?? '' }}"
                    class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"
                    placeholder="E-posta">
            </div>
            <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Telefon</label>
                <input type="text" name="phone" value="{{ $filters['phone'] ?? '' }}"
                    class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"
                    placeholder="Telefon">
            </div>
            <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Durum</label>
                <select name="status"
                    class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm">
                    <option value="">Tümü</option>
                    <option value="verified" @selected(($filters['status'] ?? '') === 'verified')>Onaylandı</option>
                    <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Beklemede</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black text-sm font-medium">
                    <i class="ri-search-2-line"></i>
                    <span>Filtrele</span>
                </button>
                <a href="{{ route('admin.customers.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-800 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-900">
                    <i class="ri-refresh-line"></i>
                    <span>Filtreyi Sıfırla</span>
                </a>
            </div>
        </div>
    </form>

    <div class="rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="text-left px-3 py-2">ID</th>
                    <th class="text-left px-3 py-2">Ad Soyad</th>
                    <th class="text-left px-3 py-2">E-posta</th>
                    <th class="text-left px-3 py-2">Durum</th>
                    <th class="text-left px-3 py-2">Katılma Tarihi</th>
                    <th class="text-right px-3 py-2">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr
                        class="border-t border-gray-100 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="px-3 py-2">{{ $customer->id }}</td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                                    {{ $customer->initials }}
                                </div>
                                <span>{{ $customer->name }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-2">{{ $customer->email }}</td>
                        <td class="px-3 py-2">
                            @if ($customer->email_verified_at)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-200">
                                    Onaylandı
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200">
                                    Beklemede
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2">{{ $customer->created_at?->format('d.m.Y H:i') }}</td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('admin.customers.show', $customer->id) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <i class="ri-eye-line"></i>
                                <span>Detay</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-gray-500">Kayıt bulunamadı.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $customers->links() }}
    </div>
@endsection
