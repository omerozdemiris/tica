@extends('admin.layouts.app')

@section('title', 'Yeni Bölüm Oluştur')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Yeni Bölüm Oluştur</h1>
        <a href="{{ route('admin.home-sections.index') }}"
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

    <form id="section-form" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        <div class="lg:col-span-1 space-y-6">
            <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-black/20">
                <h2 class="font-semibold mb-4 text-sm uppercase tracking-wider text-gray-500">Bölüm Bilgileri</h2>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium">Bölüm Adı (Sistem Adı)</label>
                        <input type="text" name="name" required placeholder="örn: vitrin_urunleri"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all">
                        <p class="text-[10px] text-gray-500 mt-1">Sadece küçük harf, rakam ve alt çizgi kullanın.</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Görünen Başlık</label>
                        <input type="text" name="title" required placeholder="örn: Vitrin Ürünleri"
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all">
                    </div>

                    <div>
                        <label class="text-sm font-medium">Açıklama</label>
                        <input name="description" rows="3" placeholder="Bölüm hakkında kısa açıklama..."
                            class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all">
                    </div>

                    <div class="flex items-center gap-3 py-2">
                        <input type="checkbox" name="is_active" value="1" class="toggle" checked>
                        <span class="text-sm font-medium">Bölüm Aktif</span>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-900">
                        <button type="submit"
                            class="w-full px-4 py-2.5 rounded-lg bg-black text-white dark:bg-white dark:text-black font-semibold hover:opacity-90 transition-all flex items-center justify-center gap-2">
                            <i class="ri-save-line"></i><span>Kaydet</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-black/20">
                <h2 class="font-semibold mb-4 text-sm uppercase tracking-wider text-gray-500">İçerik Tipi</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <label
                        class="flex items-center gap-3 p-3 rounded-md border border-gray-100 dark:border-gray-900 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <input type="radio" name="type" value="products" checked
                            class="text-black dark:text-white focus:ring-0">
                        <div>
                            <p class="text-sm font-medium">Seçmeli Ürünler</p>
                            <p class="text-[10px] text-gray-500">Havuzdan ürün seçin.</p>
                        </div>
                    </label>
                    <label
                        class="flex items-center gap-3 p-3 rounded-md border border-gray-100 dark:border-gray-900 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <input type="radio" name="type" value="categories"
                            class="text-black dark:text-white focus:ring-0">
                        <div>
                            <p class="text-sm font-medium">Seçmeli Kategoriler</p>
                            <p class="text-[10px] text-gray-500">Havuzdan kategori seçin.</p>
                        </div>
                    </label>
                    <label
                        class="flex items-center gap-3 p-3 rounded-md border border-gray-100 dark:border-gray-900 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <input type="radio" name="type" value="showcase"
                            class="text-black dark:text-white focus:ring-0">
                        <div>
                            <p class="text-sm font-medium">Vitrin (İndirimli)</p>
                            <p class="text-[10px] text-gray-500">İndirimli ürünler listeler.</p>
                        </div>
                    </label>
                    <label
                        class="flex items-center gap-3 p-3 rounded-md border border-gray-100 dark:border-gray-900 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <input type="radio" name="type" value="blogs" class="text-black dark:text-white focus:ring-0">
                        <div>
                            <p class="text-sm font-medium">Seçmeli Blog</p>
                            <p class="text-[10px] text-gray-500">Havuzdan blog seçin.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div
                class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/20 h-full flex flex-col">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h2 class="font-semibold text-sm uppercase tracking-wider text-gray-500">İçerik Yönetimi</h2>
                    <div class="relative">
                        <input type="text" id="pool-search" placeholder="Ara..."
                            class="pl-8 pr-3 py-1.5 text-xs rounded-full border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 focus:ring-0 w-48">
                        <i class="ri-search-2-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div class="flex-1 grid grid-cols-2 divide-x divide-gray-200 dark:divide-gray-800 min-h-[500px]">
                    <!-- Pool -->
                    <div class="flex flex-col">
                        <div
                            class="p-3 bg-gray-50 dark:bg-gray-900/50 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-200 dark:border-gray-800">
                            Havuz
                        </div>
                        <div id="item-pool"
                            class="flex-1 p-2 overflow-y-auto space-y-1 max-h-[600px] relative pool-scrollbar">
                            @foreach ($products as $product)
                                <div class="item-card group bg-white dark:bg-gray-900 p-2 rounded border border-gray-100 dark:border-gray-800 flex items-center gap-3 cursor-move hover:border-black dark:hover:border-white transition-all"
                                    data-id="{{ $product->id }}" data-type="products"
                                    data-search="{{ strtolower($product->title) }}">
                                    @if ($product->photo)
                                        <img src="{{ asset($product->photo) }}" class="w-8 h-8 rounded object-cover">
                                    @else
                                        <div
                                            class="w-8 h-8 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                            <i class="ri-image-line"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium truncate">{{ $product->title }}</p>
                                        <p class="text-[10px] text-gray-500">#{{ $product->id }}</p>
                                    </div>
                                </div>
                            @endforeach

                            @foreach ($categories as $category)
                                <div class="item-card group bg-white dark:bg-gray-900 p-2 rounded border border-gray-100 dark:border-gray-800 flex items-center gap-3 cursor-move hover:border-black dark:hover:border-white transition-all hidden"
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

                            @foreach ($blogs as $blog)
                                <div class="item-card group bg-white dark:bg-gray-900 p-2 rounded border border-gray-100 dark:border-gray-800 flex items-center gap-3 cursor-move hover:border-black dark:hover:border-white transition-all hidden"
                                    data-id="{{ $blog->id }}" data-type="blogs"
                                    data-search="{{ strtolower($blog->title) }}">
                                    @if ($blog->image)
                                        <img src="{{ asset($blog->image) }}" class="w-8 h-8 rounded object-cover">
                                    @else
                                        <div
                                            class="w-8 h-8 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                            <i class="ri-edit-2-line"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium truncate">{{ $blog->title }}</p>
                                        <p class="text-[10px] text-gray-500">#{{ $blog->id }}</p>
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

                    <!-- Selection -->
                    <div class="flex flex-col bg-gray-50/50 dark:bg-black/10">
                        <div
                            class="p-3 bg-gray-50 dark:bg-gray-900/50 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-200 dark:border-gray-800">
                            Seçili Öğeler (Sürükleyerek Sıralayın)
                        </div>
                        <div id="item-selection"
                            class="flex-1 p-2 overflow-y-auto space-y-1 max-h-[600px] pool-scrollbar">
                            <div class="empty-state p-8 text-center space-y-2 opacity-50">
                                <i class="ri-arrow-right-s-line text-3xl"></i>
                                <p class="text-xs">Havuzdan öğeleri buraya sürükleyin.</p>
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

            if (poolEl && selectionEl) {
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
            }

            function checkEmpty() {
                if (!selectionEl) return;
                const count = $(selectionEl).find('.item-card').length;
                $(selectionEl).find('.empty-state').toggle(count === 0);
            }

            function getSelectedIds() {
                if (!selectionEl) return [];
                return $(selectionEl).find('.item-card').map(function() {
                    return $(this).data('id');
                }).get();
            }

            function loadItems(reset = false) {
                if (isLoading || (!hasMore && !reset)) return;
                if (!poolEl) return;

                const type = $('input[name="type"]:checked').val();
                isLoading = true;
                $('#pool-loader').removeClass('hidden').appendTo(poolEl);

                if (reset) {
                    skipCount = 0;
                    hasMore = true;
                    $(poolEl).find('.item-card').remove();
                }

                const query = $('#pool-search').val();
                const exclude = getSelectedIds();

                $.get("{{ route('admin.home-sections.get-items') }}", {
                    type: type,
                    q: query,
                    skip: skipCount,
                    exclude: exclude
                }, function(items) {
                    if (items.length < 20) {
                        hasMore = false;
                    }

                    items.forEach(item => {
                        const html = `
                            <div class="item-card group bg-white dark:bg-gray-900 p-2 rounded border border-gray-100 dark:border-gray-800 flex items-center gap-3 cursor-move hover:border-black dark:hover:border-white transition-all"
                                data-id="${item.id}" data-type="${item.type}"
                                data-search="${item.title.toLowerCase()}">
                                ${item.photo ? `<img src="${item.photo}" class="w-8 h-8 rounded object-cover">` : 
                                  `<div class="w-8 h-8 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                                                              <i class="lni ${item.type === 'categories' ? 'lni-tag' : (item.type === 'blogs' ? 'lni-write' : 'lni-image')}"></i>
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

            if (poolEl) {
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
            }

            $('input[name="type"]').on('change', function() {
                loadItems(true);
                $(selectionEl).find('.item-card').remove();
                checkEmpty();
            });

            $('#section-form').on('submit', function(e) {
                e.preventDefault();
                const type = $('input[name="type"]:checked').val();
                const itemIds = [];

                $(selectionEl).find('.item-card').each(function() {
                    itemIds.push($(this).data('id'));
                });

                const data = $(this).serializeArray();
                itemIds.forEach(id => data.push({
                    name: 'item_ids[]',
                    value: id
                }));

                $.ajax({
                    url: "{{ route('admin.home-sections.store') }}",
                    type: 'POST',
                    data: data,
                    success: function(res) {
                        showSuccess(res.msg);
                        if (res.redirect) {
                            setTimeout(() => {
                                window.location.href = res.redirect;
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        showError(xhr.responseJSON?.msg || 'Bir hata oluştu.');
                    }
                });
            });
        </script>
    @endpush
@endsection
