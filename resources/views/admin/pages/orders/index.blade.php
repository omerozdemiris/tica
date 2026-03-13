@extends('admin.layouts.app')
@section('title', 'Tüm Siparişler')
@section('content')
    @php($filters = $filters ?? [])

    <form method="GET" action="{{ route('admin.orders.index') }}"
        class="mb-6 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black p-4">
        <div class="grid grid-cols-1 lg:grid-cols-6 gap-4">
            <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Durum</label>
                <select name="status"
                    class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm">
                    <option value="">Tümü</option>
                    @foreach (['new' => 'Yeni', 'pending' => 'Beklemede', 'completed' => 'Tamamlandı', 'canceled' => 'İptal'] as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Sipariş No</label>
                <input type="text" name="order_number" value="{{ $filters['order_number'] ?? '' }}"
                    class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm"
                    placeholder="ORD-000123">
            </div>
            <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Müşteri</label>
                <input type="text" name="customer" value="{{ $filters['customer'] ?? '' }}"
                    class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm"
                    placeholder="Ad, e-posta veya kullanıcı adı">
            </div>
            <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Başlangıç Tarihi</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}"
                    class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Bitiş Tarihi</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}"
                    class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Min. Tutar</label>
                    <input type="number" name="total_min" step="0.01" min="0"
                        value="{{ $filters['total_min'] ?? '' }}"
                        class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm"
                        placeholder="0.00">
                </div>
                <div>
                    <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Maks. Tutar</label>
                    <input type="number" name="total_max" step="0.01" min="0"
                        value="{{ $filters['total_max'] ?? '' }}"
                        class="mt-1 w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm"
                        placeholder="0.00">
                </div>
            </div>
        </div>
        <div class="mt-4 flex items-center gap-2">
            <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black text-sm font-medium">
                <i class="ri-search-2-line"></i>
                <span>Filtrele</span>
            </button>
            <a href="{{ route('admin.orders.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-800 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-900">
                <i class="ri-refresh-line"></i>
                <span>Filtreyi Sıfırla</span>
            </a>
        </div>
    </form>

    @include('admin.pages.orders.partials.table')
@endsection
