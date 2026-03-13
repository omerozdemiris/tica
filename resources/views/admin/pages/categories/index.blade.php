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
        <a href="{{ route('admin.categories.create') }}"
            class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black inline-flex items-center gap-2"><i
                class="ri-add-line"></i><span>Yeni Kategori</span></a>
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
            <div id="categories-list" class="divide-y divide-gray-100 dark:divide-gray-900">
                @foreach ($categories as $category)
                    <div class="category-item group hover:bg-gray-50 dark:hover:bg-gray-900 transition"
                        data-name="{{ strtolower($category->name) }}">
                        <div class="flex items-center justify-between px-4 py-3">
                            <div class="flex items-center gap-3 flex-1">
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
            const categoryItems = document.querySelectorAll('.category-item');

            if (searchInput && categoriesList) {
                searchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase().trim();
                    let visibleCount = 0;

                    categoryItems.forEach(function(item) {
                        const categoryName = item.getAttribute('data-name');
                        if (categoryName.includes(searchTerm)) {
                            item.style.display = '';
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    if (visibleCount === 0 && searchTerm !== '') {
                        categoriesList.style.display = 'none';
                        noResults.classList.remove('hidden');
                    } else {
                        categoriesList.style.display = '';
                        noResults.classList.add('hidden');
                    }
                });
            }
        });
    </script>
@endpush

@include('admin.pages.dropzone.modal')
