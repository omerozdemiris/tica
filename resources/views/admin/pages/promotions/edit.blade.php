@extends('admin.layouts.app')

@section('title', 'Promosyon Düzenle')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Promosyon Düzenle: {{ $promotion->code }}</h1>
        <a href="{{ route('admin.promotions.index') }}"
            class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-white text-black dark:bg-black dark:text-white flex items-center gap-2">
            <i class="ri-arrow-left-line"></i><span>Geri Dön</span>
        </a>
    </div>

    <style>
        .pool-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .pool-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .pool-scrollbar::-webkit-scrollbar-thumb {
            background: #1a1a1a;
            border-radius: 10px;
        }

        .dark .pool-scrollbar::-webkit-scrollbar-thumb {
            background: #444;
        }

        .pool-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #000;
        }

        .dark .pool-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

    <form id="promotion-form" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        @method('PUT')
        <div class="lg:col-span-1 space-y-6">
            <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-black/20">
                <h2 class="font-semibold mb-4 text-sm uppercase tracking-wider text-gray-500">Promosyon Bilgileri</h2>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium">Kupon Kodu</label>
                        <input type="text" name="code" required value="{{ $promotion->code }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black uppercase">
                    </div>

                    <div>
                        <label class="text-sm font-medium">İndirim Oranı (%)</label>
                        <input type="number" name="discount_rate" required min="1" max="100"
                            value="{{ $promotion->discount_rate }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>

                    <div>
                        <label class="text-sm font-medium">Koşul Tipi</label>
                        <select name="condition_type" id="condition_type"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                            <option value="">Seçiniz</option>
                            <option value="1" @selected($promotion->condition_type == 1)>Kota Sınırlı</option>
                            <option value="2" @selected($promotion->condition_type == 2)>Tarih Aralıklı</option>
                            <option value="3" @selected($promotion->condition_type == 3)>Minimum Sepet Tutarı</option>
                        </select>
                    </div>

                    <div id="usage_limit_wrap" class="{{ $promotion->condition_type == 1 ? '' : 'hidden' }}">
                        <label class="text-sm font-medium">Kullanım Kotası (Adet)</label>
                        <input type="number" name="usage_limit" min="1" value="{{ $promotion->usage_limit }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>

                    <div id="min_total_wrap" class="{{ $promotion->condition_type == 3 ? '' : 'hidden' }}">
                        <label class="text-sm font-medium">Minimum Sepet Tutarı (₺)</label>
                        <input type="number" name="min_total" min="0" step="0.01"
                            value="{{ $promotion->min_total }}"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                    </div>

                    <div id="date_range_wrap" class="{{ $promotion->condition_type == 2 ? '' : 'hidden' }} space-y-4">
                        <div>
                            <label class="text-sm font-medium">Başlangıç Tarihi</label>
                            <input type="datetime-local" name="start_date"
                                value="{{ $promotion->start_date?->format('Y-m-d\TH:i') }}"
                                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                        </div>
                        <div>
                            <label class="text-sm font-medium">Bitiş Tarihi</label>
                            <input type="datetime-local" name="end_date"
                                value="{{ $promotion->end_date?->format('Y-m-d\TH:i') }}"
                                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 py-2">
                        <input type="checkbox" name="is_active" value="1" class="toggle" @checked($promotion->is_active)>
                        <span class="text-sm font-medium">Promosyon Aktif</span>
                    </div>

                    <div class="flex items-center gap-3 py-2">
                        <input type="checkbox" name="public" value="1" class="toggle" @checked($promotion->public)>
                        <span class="text-sm font-medium">Herkese Açık</span>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-900">
                        <button type="submit"
                            class="w-full px-4 py-2.5 rounded-lg bg-black text-white dark:bg-white dark:text-black font-semibold hover:opacity-90 transition-all flex items-center justify-center gap-2">
                            <i class="ri-save-line"></i><span>Güncelle</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-black/20">
                <h2 class="font-semibold mb-4 text-sm uppercase tracking-wider text-gray-500">Uygulama Alanı</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @php
                        $currentType = $promotion->data['type'] ?? 'products';
                        $selectedIds = $promotion->data['item_ids'] ?? [];
                    @endphp
                    <label
                        class="flex items-center gap-3 p-3 rounded-md border border-gray-100 dark:border-gray-900 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <input type="radio" name="type" value="products" @checked($currentType === 'products')
                            class="text-black dark:text-white focus:ring-0">
                        <div>
                            <p class="text-sm font-medium">Seçmeli Ürünler</p>
                            <p class="text-[10px] text-gray-500">Belirli ürünlerde geçerli.</p>
                        </div>
                    </label>
                    <label
                        class="flex items-center gap-3 p-3 rounded-md border border-gray-100 dark:border-gray-900 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <input type="radio" name="type" value="categories" @checked($currentType === 'categories')
                            class="text-black dark:text-white focus:ring-0">
                        <div>
                            <p class="text-sm font-medium">Seçmeli Kategoriler</p>
                            <p class="text-[10px] text-gray-500">Belirli kategorilerde geçerli.</p>
                        </div>
                    </label>
                    <label
                        class="flex items-center gap-3 p-3 rounded-md border border-gray-100 dark:border-gray-900 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <input type="radio" name="type" value="cart_total" @checked($currentType === 'cart_total')
                            class="text-black dark:text-white focus:ring-0">
                        <div>
                            <p class="text-sm font-medium font-bold text-gray-900">Sepet Tutarı</p>
                            <p class="text-[10px] text-gray-600">Tüm alışverişlerde geçerli.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div id="pool-wrapper" class="{{ $currentType === 'cart_total' ? 'hidden' : '' }} h-full flex flex-col">
                <div
                    class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/20 h-full flex flex-col">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                        <h2 class="font-semibold text-sm uppercase tracking-wider text-gray-500">Havuz Yönetimi</h2>
                        <div class="relative">
                            <input type="text" id="pool-search" placeholder="Ara..."
                                class="pl-8 pr-3 py-1.5 text-xs rounded-full border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 focus:ring-0 w-48">
                            <i class="ri-search-2-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>

                    <div class="flex-1 grid grid-cols-2 divide-x divide-gray-200 dark:divide-gray-800 min-h-[500px]">
                        <div class="flex flex-col">
                            <div
                                class="p-3 bg-gray-50 dark:bg-gray-900/50 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-200 dark:border-gray-800">
                                Havuz
                            </div>
                            <div id="item-pool"
                                class="flex-1 p-2 overflow-y-auto space-y-1 max-h-[600px] relative pool-scrollbar">
                                @foreach ($products as $product)
                                    <div class="item-card group bg-white dark:bg-gray-900 p-2 rounded border border-gray-100 dark:border-gray-800 flex items-center gap-3 cursor-move hover:border-black dark:hover:border-white transition-all {{ $currentType !== 'products' ? 'hidden' : '' }}"
                                        data-id="{{ $product->id }}" data-type="products"
                                        data-search="{{ strtolower($product->title) }}">
                                        @if ($product->photo)
                                            <img src="{{ asset($product->photo) }}" class="w-8 h-8 rounded object-cover">
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium truncate">{{ $product->title }}</p>
                                            <p class="text-[10px] text-gray-500">#{{ $product->id }}</p>
                                        </div>
                                    </div>
                                @endforeach
                                @foreach ($categories as $category)
                                    <div class="item-card group bg-white dark:bg-gray-900 p-2 rounded border border-gray-100 dark:border-gray-800 flex items-center gap-3 cursor-move hover:border-black dark:hover:border-white transition-all {{ $currentType !== 'categories' ? 'hidden' : '' }}"
                                        data-id="{{ $category->id }}" data-type="categories"
                                        data-search="{{ strtolower($category->name) }}">
                                        <div
                                            class="w-8 h-8 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                            <i class="ri-tag-line"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium truncate">{{ $category->name }}</p>
                                            <p class="text-[10px] text-gray-500">#{{ $category->id }}</p>
                                        </div>
                                    </div>
                                @endforeach
                                <div id="pool-loader"
                                    class="hidden py-4 flex items-center justify-center text-gray-500 gap-2">
                                    <i class="ri-loader-4-line animate-spin text-xl"></i>
                                    <span class="text-xs font-medium">Yükleniyor...</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col bg-gray-50/50 dark:bg-black/10">
                            <div
                                class="p-3 bg-gray-50 dark:bg-gray-900/50 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-200 dark:border-gray-800">
                                Geçerli Alanlar
                            </div>
                            <div id="item-selection"
                                class="flex-1 p-2 overflow-y-auto space-y-1 max-h-[600px] pool-scrollbar">
                                @foreach ($selectedItems as $item)
                                    <div class="item-card group bg-white dark:bg-gray-900 p-2 rounded border border-gray-100 dark:border-gray-800 flex items-center gap-3 cursor-move hover:border-black dark:hover:border-white transition-all"
                                        data-id="{{ $item->id }}" data-type="{{ $currentType }}"
                                        data-search="{{ strtolower($item->title ?? $item->name) }}">
                                        @if ($currentType === 'products')
                                            @if ($item->photo)
                                                <img src="{{ asset($item->photo) }}"
                                                    class="w-8 h-8 rounded object-cover">
                                            @endif
                                        @else
                                            <div
                                                class="w-8 h-8 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                                <i class="ri-tag-line"></i>
                                            </div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium truncate">{{ $item->title ?? $item->name }}</p>
                                            <p class="text-[10px] text-gray-500">#{{ $item->id }}</p>
                                        </div>
                                    </div>
                                @endforeach
                                <div
                                    class="empty-state p-8 text-center space-y-2 opacity-50 {{ count($selectedItems) > 0 ? 'hidden' : '' }}">
                                    <i class="ri-arrow-right-s-line text-3xl"></i>
                                    <p class="text-xs">Havuzdan öğeleri buraya sürükleyin.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            const poolEl = document.getElementById('item-pool');
            const selectionEl = document.getElementById('item-selection');
            let skipCount = 20;
            let isLoading = false;
            let hasMore = true;
            let searchTimeout = null;

            new Sortable(poolEl, {
                group: 'shared',
                animation: 150,
                sort: false
            });
            new Sortable(selectionEl, {
                group: 'shared',
                animation: 150,
                onAdd: checkEmpty,
                onRemove: checkEmpty
            });

            function checkEmpty() {
                const count = $(selectionEl).find('.item-card').length;
                $(selectionEl).find('.empty-state').toggle(count === 0);
            }

            function getSelectedIds() {
                return $(selectionEl).find('.item-card').map(function() {
                    return $(this).data('id');
                }).get();
            }

            function loadItems(reset = false) {
                if (isLoading || (!hasMore && !reset)) return;

                const type = $('input[name="type"]:checked').val();
                if (type === 'cart_total') return;

                isLoading = true;
                $('#pool-loader').removeClass('hidden').appendTo(poolEl);

                if (reset) {
                    skipCount = 0;
                    hasMore = true;
                    $(poolEl).find('.item-card').remove();
                }

                const query = $('#pool-search').val();
                const exclude = getSelectedIds();

                $.get("{{ route('admin.promotions.get-items') }}", {
                    type: type,
                    q: query,
                    skip: skipCount,
                    exclude: exclude
                }, function(items) {
                    if (items.length < 20 || type === 'categories') {
                        hasMore = false;
                    }

                    items.forEach(item => {
                        const html = `
                            <div class="item-card group bg-white dark:bg-gray-900 p-2 rounded border border-gray-100 dark:border-gray-800 flex items-center gap-3 cursor-move hover:border-black dark:hover:border-white transition-all"
                                data-id="${item.id}" data-type="${item.type}"
                                data-search="${item.title.toLowerCase()}">
                                ${item.photo ? `<img src="${item.photo}" class="w-8 h-8 rounded object-cover">` : 
                                  `<div class="w-8 h-8 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                                              <i class="ri-tag-line"></i>
                                                           </div>`}
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium truncate">${item.title}</p>
                                    <p class="text-[10px] text-gray-500">#${item.id}</p>
                                </div>
                            </div>`;
                        $(html).insertBefore('#pool-loader');
                    });

                    skipCount += items.length;
                    isLoading = false;
                    $('#pool-loader').addClass('hidden');
                }).fail(function() {
                    isLoading = false;
                    $('#pool-loader').addClass('hidden');
                });
            }

            // Infinite Scroll
            $(poolEl).on('scroll', function() {
                if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight - 50) {
                    loadItems();
                }
            });

            // Search with debounce
            $('#pool-search').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadItems(true);
                }, 500);
            });

            $('#condition_type').on('change', function() {
                const val = $(this).val();
                $('#usage_limit_wrap').toggleClass('hidden', val != '1');
                $('#date_range_wrap').toggleClass('hidden', val != '2');
            });

            $('input[name="type"]').on('change', function() {
                const type = $(this).val();

                if (type === 'cart_total') {
                    $('#pool-wrapper').addClass('hidden');
                    $('#min_total_wrap').removeClass('hidden');
                } else {
                    $('#pool-wrapper').removeClass('hidden');
                    $('#min_total_wrap').addClass('hidden');
                    loadItems(true);
                }

                $(selectionEl).find('.item-card').remove();
                checkEmpty();
            });

            $('#promotion-form').on('submit', function(e) {
                e.preventDefault();
                const type = $('input[name="type"]:checked').val();
                const itemIds = [];

                if (type !== 'cart_total') {
                    $(selectionEl).find('.item-card').each(function() {
                        itemIds.push($(this).data('id'));
                    });
                    if (itemIds.length === 0) return showError('Lütfen en az bir öğe seçin.');
                }

                const data = $(this).serializeArray();
                if (type !== 'cart_total') {
                    itemIds.forEach(id => data.push({
                        name: 'item_ids[]',
                        value: id
                    }));
                }

                $.ajax({
                    url: "{{ route('admin.promotions.update', $promotion->id) }}",
                    method: "POST",
                    data: $.param(data),
                    success: function(res) {
                        showSuccess(res?.msg);
                        if (res.redirect) setTimeout(() => window.location.href = res.redirect, 600);
                    },
                    error: function(xhr) {
                        showError(xhr.responseJSON?.msg || 'Hata oluştu.');
                    }
                });
            });
        </script>
    @endpush
@endsection
