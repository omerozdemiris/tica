@extends('admin.layouts.app')

@section('title', 'Kategoriler')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-lg font-semibold">Kategoriler</h1>
            @if ($currentCategory)
                <nav class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                    <a href="{{ route('admin.categories.index') }}" class="hover:text-gray-700">Kategoriler</a>
                    @foreach ($breadcrumbs as $crumb)
                        <span>/</span>
                        @if ($crumb->id === $currentCategory->id)
                            <span class="text-gray-700">{{ $crumb->name }}</span>
                        @else
                            <a href="{{ route('admin.categories.index', ['category' => $crumb->id]) }}"
                                class="hover:text-gray-700">{{ $crumb->name }}</a>
                        @endif
                    @endforeach
                </nav>
            @endif
        </div>
        <div class="flex items-center gap-2">
            @if (!$categories->isEmpty())
                <button type="button" id="sorting-toggle"
                    class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 inline-flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-900 transition"
                    aria-pressed="false" title="Sıralamayı aç/kapat">
                    <i class="ri-drag-move-line"></i>
                    <span>Sıralamayı Aç</span>
                </button>
            @endif
            <a href="{{ route('admin.categories.create') }}"
                class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black inline-flex items-center gap-2"><i
                    class="ri-add-line"></i><span>Yeni Kategori</span></a>
        </div>
    </div>

    @if (!$categories->isEmpty())
        <div class="mb-4">
            <div class="relative">
                <input type="text" id="category-search" placeholder="Kategori ara..."
                    class="w-full px-4 py-2 pl-10 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>
    @endif

    @if ($categories->isEmpty())
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-8 text-center">
            <p class="text-sm text-gray-500">Henüz kategori bulunmuyor.</p>
        </div>
    @else
        <div class="rounded-lg bg-white border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div id="categories-list" class="divide-y divide-gray-100 dark:divide-gray-900"
                data-sort-url="{{ route('admin.categories.sort') }}" data-parent-id="{{ $currentCategory?->id }}">
                @foreach ($categories as $category)
                    <div class="category-item group hover:bg-gray-50 dark:hover:bg-gray-900 transition"
                        data-name="{{ strtolower($category->name) }}" data-category-id="{{ $category->id }}"
                        draggable="false">
                        <div class="flex items-center justify-between px-4 py-3">
                            <div class="flex items-center gap-3 flex-1">
                                <button type="button"
                                    class="drag-handle hidden text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-move"
                                    title="Sürükleyerek sırala">
                                    <i class="ri-draggable text-lg"></i>
                                </button>
                                <i class="ri-folder-line text-lg text-gray-400"></i>
                                <div class="flex-1">
                                    @php
                                        $hasChildren = ($category->children_count ?? 0) > 0;
                                        $categoryUrl = $hasChildren
                                            ? route('admin.categories.index', ['category' => $category->id])
                                            : route('admin.categories.edit', $category->id);
                                    @endphp
                                    <a href="{{ $categoryUrl }}"
                                        class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 transition">
                                        {{ $category->name }}
                                    </a>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-xs text-gray-500">
                                            {{ $category->products_count ?? 0 }} ürün
                                        </span>
                                        @if ($hasChildren)
                                            <span class="text-xs text-blue-600 dark:text-blue-400">
                                                {{ $category->children_count ?? 0 }} alt kategori
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.categories.products', $category->id) }}"
                                    class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 inline-flex items-center gap-1 text-xs hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                    title="Ürünleri Görüntüle">
                                    <i class="ri-box-3-line"></i>
                                    <span>{{ $category->products_count ?? 0 }}</span>
                                </a>
                                <a href="{{ route('admin.categories.edit', $category->id) }}"
                                    class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 inline-flex items-center gap-1 text-md hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                    title="Düzenle">
                                    <i class="ri-pencil-line"></i>
                                </a>
                                <button data-delete data-url="{{ route('admin.categories.destroy', $category->id) }}"
                                    data-confirm="Bu kategoriyi silmek istediğinize emin misiniz? Bağlı ürünler kategoriden çıkarılacak."
                                    class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 inline-flex items-center gap-1 text-md hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                    title="Sil">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div id="no-results" class="hidden p-8 text-center">
                <p class="text-sm text-gray-500">Arama sonucu bulunamadı.</p>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('category-search');
            const categoriesList = document.getElementById('categories-list');
            const noResults = document.getElementById('no-results');
            const sortingToggle = document.getElementById('sorting-toggle');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            let sortingEnabled = false;
            let draggedItem = null;
            let isSavingSort = false;

            const getCategoryItems = () => Array.from(categoriesList?.querySelectorAll('.category-item') || []);

            function updateNoResultsVisibility(searchTerm) {
                const visibleCount = getCategoryItems().filter((item) => item.style.display !== 'none').length;
                if (visibleCount === 0 && searchTerm !== '') {
                    categoriesList.style.display = 'none';
                    noResults.classList.remove('hidden');
                } else {
                    categoriesList.style.display = '';
                    noResults.classList.add('hidden');
                }
            }

            function setSortingMode(enabled) {
                sortingEnabled = enabled;
                getCategoryItems().forEach((item) => {
                    item.draggable = enabled;
                    item.classList.toggle('cursor-move', enabled);
                    item.classList.toggle('bg-blue-50', false);
                    item.querySelectorAll('.drag-handle').forEach((handle) => {
                        handle.classList.toggle('hidden', !enabled);
                    });
                });

                if (!sortingToggle) {
                    return;
                }

                sortingToggle.setAttribute('aria-pressed', enabled ? 'true' : 'false');
                sortingToggle.classList.toggle('bg-blue-600', enabled);
                sortingToggle.classList.toggle('text-white', enabled);
                sortingToggle.classList.toggle('border-blue-600', enabled);
                const label = sortingToggle.querySelector('span');
                if (label) {
                    label.textContent = enabled ? 'Sıralamayı Kapat' : 'Sıralamayı Aç';
                }
            }

            function getDragAfterElement(container, clientY) {
                const draggableElements = getCategoryItems().filter((item) => item !== draggedItem && item.style.display !== 'none');
                let closest = { offset: Number.NEGATIVE_INFINITY, element: null };

                draggableElements.forEach((element) => {
                    const box = element.getBoundingClientRect();
                    const offset = clientY - box.top - box.height / 2;
                    if (offset < 0 && offset > closest.offset) {
                        closest = { offset, element };
                    }
                });

                return closest.element;
            }

            async function persistSortOrder() {
                if (!categoriesList || isSavingSort) {
                    return;
                }

                isSavingSort = true;
                const parentId = categoriesList.dataset.parentId || null;
                const categoryIds = getCategoryItems().map((item) => Number(item.dataset.categoryId)).filter(Boolean);

                try {
                    const response = await fetch(categoriesList.dataset.sortUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            parent_id: parentId ? Number(parentId) : null,
                            category_ids: categoryIds,
                        }),
                    });

                    if (!response.ok) {
                        throw new Error('Sıralama kaydedilemedi.');
                    }
                } catch (error) {
                    setSortingMode(false);
                    window.alert(error.message || 'Sıralama kaydedilirken hata oluştu.');
                } finally {
                    isSavingSort = false;
                }
            }

            if (searchInput && categoriesList) {
                searchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase().trim();
                    getCategoryItems().forEach(function(item) {
                        const categoryName = item.getAttribute('data-name');
                        if (categoryName.includes(searchTerm)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                    updateNoResultsVisibility(searchTerm);
                });
            }

            if (sortingToggle && categoriesList) {
                sortingToggle.addEventListener('click', function() {
                    if (isSavingSort) {
                        return;
                    }
                    setSortingMode(!sortingEnabled);
                });

                categoriesList.addEventListener('dragstart', function(event) {
                    const item = event.target.closest('.category-item');
                    if (!sortingEnabled || !item) {
                        event.preventDefault();
                        return;
                    }
                    draggedItem = item;
                    item.classList.add('opacity-60');
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', item.dataset.categoryId || '');
                });

                categoriesList.addEventListener('dragover', function(event) {
                    if (!sortingEnabled || !draggedItem) {
                        return;
                    }
                    event.preventDefault();
                    const afterElement = getDragAfterElement(categoriesList, event.clientY);
                    if (!afterElement) {
                        categoriesList.appendChild(draggedItem);
                    } else {
                        categoriesList.insertBefore(draggedItem, afterElement);
                    }
                });

                categoriesList.addEventListener('dragend', function(event) {
                    const item = event.target.closest('.category-item');
                    if (!sortingEnabled || !item) {
                        return;
                    }
                    item.classList.remove('opacity-60');
                    draggedItem = null;
                    persistSortOrder();
                });
            }
        });
    </script>
@endpush

@include('admin.pages.dropzone.modal')
