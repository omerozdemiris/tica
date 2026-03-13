@extends('admin.layouts.app')

@section('title', 'Ürünler')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Ürünler</h1>
        <a href="{{ route('admin.products.create') }}"
            class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black flex items-center gap-2"><i
                class="ri-add-line"></i><span>Yeni Ürün</span></a>
    </div>
    @php
        $filters = $filters ?? ['keyword' => request('keyword', ''), 'category_id' => request('category_id')];
    @endphp
    <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/30 p-4">
        <form method="GET" action="{{ route('admin.products.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 ">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Ürün Adı</label>
                    <input type="text" name="keyword" value="{{ $filters['keyword'] }}"
                        class="mt-2 w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-sm"
                        placeholder="Ürün adı veya açıklama">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-black text-white text-sm font-semibold dark:bg-white dark:text-black hover:bg-gray-900 transition w-full md:w-auto">
                        Ara
                    </button>
                    <a href="{{ route('admin.products.index') }}"
                        class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-200">
                        Sıfırla
                    </a>
                </div>
            </div>
        </form>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
        <table id="products-table" class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="text-left px-3 py-2">Ürün</th>
                    <th class="text-left px-3 py-2">Kategoriler</th>
                    <th class="text-center px-3 py-2">Stok</th>
                    <th class="text-center px-3 py-2">Durum</th>
                    <th class="text-center px-3 py-2">Satış</th>
                    <th class="text-center px-3 py-2">Sepet</th>
                    <th class="text-center px-3 py-2">Gösterim</th>
                    <th class="text-right px-3 py-2">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products ?? [] as $product)
                    @php
                        $productPhoto = $product->photo ? asset($product->photo) : null;
                        $stockLabel = $product->stock_label;
                    @endphp
                    <tr class="border-t border-gray-100 dark:border-gray-900">
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-3">
                                @if ($productPhoto)
                                    <img src="{{ $productPhoto }}" alt="{{ $product->title }}"
                                        class="w-12 h-14 rounded-md object-cover border border-gray-200 dark:border-gray-800">
                                @else
                                    <div
                                        class="w-12 h-12 rounded-md border border-dashed border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-400 dark:text-gray-500 flex items-center justify-center">
                                        <i class="ri-image-line text-lg"></i>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-xs line-clamp-1 max-w-[300px]">{{ $product->title }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">#{{ $product->code ?? $product->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2">
                            <span
                                class="text-[10px] bg-orange-50 px-1 py-0.5 rounded-lg text-orange-700 dark:text-gray-400 line-clamp-2 max-w-[170px]">
                                {{ $product->categories_label ?? '-' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center">
                            @if ($stockLabel === 'Sınırsız')
                                <span
                                    class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full uppercase">Sınırsız</span>
                            @else
                                <span
                                    class="text-sm font-semibold bg-indigo-50 px-2 py-0.5 rounded-xl {{ (int) $stockLabel <= 5 ? 'text-red-600' : 'text-indigo-700 dark:text-gray-300' }}">
                                    {{ $stockLabel }}
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-center">
                            @if ($product->is_active)
                                <span
                                    class="inline-flex items-center gap-1 text-[10px] font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full uppercase">
                                    <i class="ri-checkbox-circle-fill"></i> Aktif
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full uppercase">
                                    <i class="ri-close-circle-fill"></i> Pasif
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span
                                class="bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-100 rounded-full text-center px-2 py-1 text-xs">
                                {{ $product->sold_count ?? 0 }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span
                                class="bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-100 rounded-full text-center px-2 py-1 text-xs">
                                {{ $product->in_cart_count ?? 0 }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span
                                class="bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-100 rounded-full text-center px-2 py-1 text-xs">
                                {{ $product->click_count ?? 0 }}
                            </span>
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2 justify-end">
                                <button type="button"
                                    class="px-2 py-1 rounded border border-gray-300 dark:border-gray-800 inline-flex items-center gap-1"
                                    title="Galeri" data-gallery data-kind="productgallery" data-id="{{ $product->id }}">
                                    <span class="px-1">Medya</span><i class="ri-image-line"></i>
                                </button>
                                <a href="{{ route('admin.products.edit', $product->id) }}"
                                    class="px-2 py-1 rounded border border-gray-300 dark:border-gray-800 inline-flex items-center gap-1"><i
                                        class="ri-pencil-line"></i><span>Düzenle</span></a>
                                <button data-delete data-id="{{ $product->id }}"
                                    data-confirm="Bu ürünü silmek istediğinize emin misiniz?"
                                    class="px-2 py-1 rounded border border-gray-300 dark:border-gray-800 bg-white dark:bg-black inline-flex items-center gap-1"><i
                                        class="ri-delete-bin-line"></i><span>Sil</span></button>
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
        @if (method_exists($products, 'hasPages') && $products->hasPages())
            <div class="px-4 py-4 border-t border-gray-100 dark:border-gray-900 bg-gray-50 dark:bg-gray-900">
                {{ $products->links() }}
            </div>
        @endif
    </div>
    @push('scripts')
        <script>
            window.AdminTables && window.AdminTables.products && window.AdminTables.products();
        </script>
    @endpush
    @include('admin.pages.dropzone.modal')
@endsection
