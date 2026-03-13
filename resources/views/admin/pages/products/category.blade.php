@extends('admin.layouts.app')
@section('title', 'Kategori Ürünleri')
@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Kategori: {{ $category->name }}</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.products.create') }}"
                class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black inline-flex items-center gap-2"><i
                    class="ri-add-line"></i><span>Yeni Ürün</span></a>
            <button type="button"
                class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 inline-flex items-center gap-2"
                data-delete data-url="{{ route('admin.categories.products.clear', $category->id) }}"
                data-confirm="Bu kategorideki TÜM ürünler silinecek. Emin misiniz?">
                <i class="ri-delete-bin-line"></i><span>Ürünleri Temizle</span>
            </button>
        </div>
    </div>
    <div class="rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
        <table id="products-table" class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="text-left px-3 py-2">Ürün</th>
                    <th class="text-left px-3 py-2">Kategoriler</th>
                    <th class="text-right px-3 py-2">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products ?? [] as $product)
                    @php
                        $productPhoto = $product->photo ? asset($product->photo) : null;
                    @endphp
                    <tr class="border-t border-gray-100 dark:border-gray-900">
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-3">
                                @if ($productPhoto)
                                    <img src="{{ $productPhoto }}" alt="{{ $product->title }}"
                                        class="w-12 h-12 rounded-md object-cover border border-gray-200 dark:border-gray-800">
                                @else
                                    <div
                                        class="w-12 h-12 rounded-md border border-dashed border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-400 dark:text-gray-500 flex items-center justify-center">
                                        <i class="ri-image-line text-lg"></i>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium">{{ $product->title }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">#{{ $product->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2">
                            {{ $product->categories->pluck('name')->join(', ') }}
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="{{ route('admin.products.edit', $product->id) }}"
                                    class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 inline-flex items-center gap-1"><i
                                        class="ri-pencil-line"></i><span>Düzenle</span></a>
                                <button data-delete
                                    data-url="{{ route('admin.categories.products.destroy', [$category->id, $product->id]) }}"
                                    data-confirm="Bu ürünü bu kategoriden çıkarmak istediğinize emin misiniz?"
                                    class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 inline-flex items-center gap-1"><i
                                        class="ri-subtract-line"></i><span>Kategoriden Çıkar</span></button>
                                <button data-delete data-id="{{ $product->id }}"
                                    data-confirm="Ürünü silmek istediğinize emin misiniz?"
                                    class="px-2 py-1 rounded border border-gray-200 dark-border-gray-800 bg-white dark:bg-black inline-flex items-center gap-1"><i
                                        class="ri-delete-bin-line"></i><span>Ürünü Sil</span></button>
                            </div>
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
    @push('scripts')
        <script>
            window.AdminTables && window.AdminTables.products && window.AdminTables.products();
        </script>
    @endpush
@endsection
