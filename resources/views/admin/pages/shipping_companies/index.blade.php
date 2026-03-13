@extends('admin.layouts.app')

@section('title', 'Teslimat Ayarları')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold">Teslimat Ayarları</h1>
    </div>

    <div class="rounded-xl bg-white border border-gray-200 dark:border-gray-800 p-5 mb-8">
        <h2 class="text-sm font-semibold mb-4">Yeni Kargo Firması Ekle</h2>
        <form id="shipping-company-create" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end"
            data-url="{{ route('admin.shipping-companies.store') }}">
            @csrf
            <div>
                <label class="text-xs uppercase tracking-wide text-gray-500">Kargo Firma Adı</label>
                <input type="text" name="name"
                    class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"
                    placeholder="Firma adı" required>
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide text-gray-500">Takip Bağlantısı</label>
                <input type="text" name="tracking_link"
                    class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"
                    placeholder="https://...">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="toggle" checked>
                <span class="text-sm">Aktif</span>
            </div>
            <div class="flex items-center gap-2 md:justify-end">
                <button type="submit"
                    class="px-4 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">
                    Ekle
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($companies as $company)
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm bg-white dark:bg-black">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">{{ $company->name }}</h3>
                            <p class="text-xs text-gray-500">{{ $company->tracking_link ?: 'Takip bağlantısı girilmedi' }}
                            </p>
                        </div>
                        <span
                            class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full {{ $company->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-200' }}">
                            {{ $company->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                    </div>

                    <form class="space-y-3 shipping-company-update" data-id="{{ $company->id }}"
                        data-url="{{ route('admin.shipping-companies.update', $company->id) }}">
                        @csrf
                        <div>
                            <label class="text-xs uppercase tracking-wide text-gray-500">Kargo Firma Adı</label>
                            <input type="text" name="name" value="{{ $company->name }}"
                                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                        </div>
                        <div>
                            <label class="text-xs uppercase tracking-wide text-gray-500">Takip Bağlantısı</label>
                            <input type="text" name="tracking_link" value="{{ $company->tracking_link }}"
                                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"
                                placeholder="https://...">
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="is_active" value="1" class="toggle"
                                @if ($company->is_active) checked @endif>
                            <span>Aktif</span>
                        </label>
                        <div class="flex items-center justify-between gap-2 pt-2">
                            <button type="submit"
                                class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900">
                                Güncelle
                            </button>
                            <a href="{{ route('admin.shipping-companies.orders', $company->id) }}"
                                class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900">
                                Siparişleri Gör
                            </a>
                            <button type="button" data-delete
                                data-url="{{ route('admin.shipping-companies.destroy', $company->id) }}"
                                data-confirm="Bu kargo firmasını silmek istediğinize emin misiniz?"
                                class="px-3 py-2 rounded-md text-sm border border-red-200 text-red-600 dark:border-red-900 hover:bg-red-50 dark:hover:bg-red-900/40">
                                Sil
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="col-span-full">
                    <div
                        class="rounded-xl border border-dashed border-gray-200 dark:border-gray-800 p-8 text-center text-sm text-gray-500">
                        Henüz kargo firması eklenmemiş.
                        <p class="text-gray-500 text-xs mt-2">Siparişlerinizi yola çıkarmak ve müşterilerinizi
                            bilgilendirmek için lütfen kargo firması ekleyin.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    @push('scripts')
        <script>
            $('#shipping-company-create').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                $.ajax({
                    url: $form.data('url'),
                    method: 'POST',
                    data: $form.serialize(),
                    success: function(res) {
                        showSuccess(res?.msg);
                        setTimeout(function() {
                            location.reload();
                        }, 800);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.msg || 'Hata oluştu';
                        showError(msg);
                    }
                });
            });

            $(document).on('submit', '.shipping-company-update', function(e) {
                e.preventDefault();
                const $form = $(this);
                const url = $form.data('url');
                const data = $form.serializeArray();
                data.push({
                    name: '_method',
                    value: 'PUT'
                });
                $.ajax({
                    url,
                    method: 'POST',
                    data: $.param(data),
                    success: function(res) {
                        showSuccess(res?.msg);
                        setTimeout(function() {
                            location.reload();
                        }, 600);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.msg || 'Hata oluştu';
                        showError(msg);
                    }
                });
            });
        </script>
    @endpush

@endsection
