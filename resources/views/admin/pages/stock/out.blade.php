@extends('admin.layouts.app')

@section('title', 'Tükenmiş Ürünler')

@section('content')
    <h1 class="text-lg font-semibold mb-4">Tükenmiş Ürünler</h1>
    <div class="rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="text-left px-3 py-2">Ürün</th>
                    <th class="text-left px-3 py-2">Varyant</th>
                    <th class="text-left px-3 py-2">Kategori</th>
                    <th class="text-left px-3 py-2">Stok</th>
                    <th class="text-right px-3 py-2">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr
                        class="border-t border-gray-100 dark:border-gray-900 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-3">
                                @if (!empty($item->photo))
                                    <img src="{{ $item->photo }}" alt="{{ $item->product_title }}"
                                        class="w-12 h-12 rounded-md object-cover border border-gray-200 dark:border-gray-800">
                                @else
                                    <div
                                        class="w-12 h-12 rounded-md border border-dashed border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-400 dark:text-gray-500 flex items-center justify-center">
                                        <i class="ri-image-line text-lg"></i>
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.products.edit', $item->product_id) }}"
                                        class="font-medium hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $item->product_title }}
                                    </a>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        #{{ $item->product_id }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2">{{ $item->variant_label }}</td>
                        <td class="px-3 py-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item->categories ?: '—' }}</span>
                        </td>
                        <td class="px-3 py-2">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-700 dark:bg-rose-900 dark:text-rose-200">
                                {{ $item->stock_label }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('admin.products.edit', $item->product_id) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <i class="ri-share-line"></i>
                                <span>Ürüne Git</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-3 py-6 text-center text-gray-500" colspan="5">Tükenen stok bulunmamaktadır.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $items->links() }}
    </div>
@endsection
