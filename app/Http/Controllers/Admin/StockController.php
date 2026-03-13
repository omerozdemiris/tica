<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductAttributeTerm;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StockController extends Controller
{
    public function low()
    {
        // Varyantı olan ürünlerin ID'lerini al
        $productsWithVariants = ProductAttributeTerm::query()
            ->whereNotNull('product_id')
            ->distinct()
            ->pluck('product_id')
            ->toArray();

        // Basit ürünleri çek (varyantı olmayan, stok 1-10 arası)
        $products = Product::query()
            ->select(['id', 'title', 'photo', 'stock', 'stock_type'])
            ->with(['categories:id,name'])
            ->whereNotIn('id', $productsWithVariants)
            ->whereNotNull('stock')
            ->whereBetween('stock', [1, 10])
            ->get();

        // Varyantları çek (stok 1-10 arası)
        $variants = ProductAttributeTerm::query()
            ->with([
                'product' => function ($q) {
                    $q->select('id', 'title', 'photo')->with(['categories:id,name']);
                },
                'attribute:id,name',
                'term:id,name',
            ])
            ->whereNotNull('stock')
            ->whereBetween('stock', [1, 10])
            ->whereHas('product')
            ->get()
            ->filter(fn(ProductAttributeTerm $variant) => $variant->product !== null);

        // Birleştir ve formatla
        $items = collect();

        foreach ($products as $product) {
            $items->push((object) [
                'id' => 'product_' . $product->id,
                'product_id' => $product->id,
                'product_title' => $product->title,
                'variant_label' => 'Basit Ürün',
                'stock_label' => $this->getStockLabel($product->stock, $product->stock_type),
                'stock_value' => $this->getStockValue($product->stock, $product->stock_type),
                'photo' => $product->photo ? asset($product->photo) : null,
                'categories' => $product->categories->pluck('name')->implode(', '),
            ]);
        }

        foreach ($variants as $variant) {
            $attributeName = $variant->attribute->name ?? null;
            $termName = $variant->term->name ?? null;
            $label = trim(($attributeName ? $attributeName . ': ' : '') . ($termName ?? 'Varyant'));

            $items->push((object) [
                'id' => 'variant_' . $variant->id,
                'product_id' => $variant->product->id,
                'product_title' => $variant->product->title,
                'variant_label' => $label !== '' ? $label : 'Varyant',
                'stock_label' => $this->getStockLabel($variant->stock, $variant->stock_type),
                'stock_value' => $this->getStockValue($variant->stock, $variant->stock_type),
                'photo' => $variant->product->photo ? asset($variant->product->photo) : null,
                'categories' => $variant->product->categories->pluck('name')->implode(', '),
            ]);
        }

        // Sırala
        $items = $items->sortBy([
            ['stock_value', 'asc'],
            ['product_title', 'asc'],
        ])->values();

        // Paginate
        $items = $this->paginateCollection($items, 10);

        return view('admin.pages.stock.low', compact('items'));
    }

    public function out()
    {
        // Varyantı olan ürünlerin ID'lerini al
        $productsWithVariants = ProductAttributeTerm::query()
            ->whereNotNull('product_id')
            ->distinct()
            ->pluck('product_id')
            ->toArray();

        // Basit ürünleri çek (varyantı olmayan, stok 0 veya null)
        $products = Product::query()
            ->select(['id', 'title', 'photo', 'stock', 'stock_type'])
            ->with(['categories:id,name'])
            ->whereNotIn('id', $productsWithVariants)
            ->where(function ($q) {
                $q->where('stock', '<=', 0)
                    ->orWhere(function ($q2) {
                        $q2->whereNull('stock')
                            ->where(function ($q3) {
                                $q3->whereNull('stock_type')->orWhere('stock_type', 0);
                            });
                    });
            })
            ->get();

        // Varyantları çek (stok 0 veya null)
        $variants = ProductAttributeTerm::query()
            ->with([
                'product' => function ($q) {
                    $q->select('id', 'title', 'photo')->with(['categories:id,name']);
                },
                'attribute:id,name',
                'term:id,name',
            ])
            ->where(function ($q) {
                $q->where('stock', '<=', 0)
                    ->orWhere(function ($q2) {
                        $q2->whereNull('stock')
                            ->where(function ($q3) {
                                $q3->whereNull('stock_type')->orWhere('stock_type', 0);
                            });
                    });
            })
            ->whereHas('product')
            ->get()
            ->filter(fn(ProductAttributeTerm $variant) => $variant->product !== null);

        // Birleştir ve formatla
        $items = collect();

        foreach ($products as $product) {
            $items->push((object) [
                'id' => 'product_' . $product->id,
                'product_id' => $product->id,
                'product_title' => $product->title,
                'variant_label' => 'Basit Ürün',
                'stock_label' => $this->getStockLabel($product->stock, $product->stock_type),
                'stock_value' => $this->getStockValue($product->stock, $product->stock_type),
                'photo' => $product->photo ? asset($product->photo) : null,
                'categories' => $product->categories->pluck('name')->implode(', '),
            ]);
        }

        foreach ($variants as $variant) {
            $attributeName = $variant->attribute->name ?? null;
            $termName = $variant->term->name ?? null;
            $label = trim(($attributeName ? $attributeName . ': ' : '') . ($termName ?? 'Varyant'));

            $items->push((object) [
                'id' => 'variant_' . $variant->id,
                'product_id' => $variant->product->id,
                'product_title' => $variant->product->title,
                'variant_label' => $label !== '' ? $label : 'Varyant',
                'stock_label' => $this->getStockLabel($variant->stock, $variant->stock_type),
                'stock_value' => $this->getStockValue($variant->stock, $variant->stock_type),
                'photo' => $variant->product->photo ? asset($variant->product->photo) : null,
                'categories' => $variant->product->categories->pluck('name')->implode(', '),
            ]);
        }

        // Sırala
        $items = $items->sortBy([
            ['product_title', 'asc'],
            ['variant_label', 'asc'],
        ])->values();

        // Paginate
        $items = $this->paginateCollection($items, 10);

        return view('admin.pages.stock.out', compact('items'));
    }

    protected function getStockLabel($stock, $stockType): string
    {
        $isUnlimited = ($stockType ?? null) == 1 && $stock === null;
        if ($isUnlimited) {
            return 'Sınırsız';
        }
        return $stock === null ? '0' : (string) $stock;
    }

    protected function getStockValue($stock, $stockType): int
    {
        $isUnlimited = ($stockType ?? null) == 1 && $stock === null;
        if ($isUnlimited) {
            return 999999;
        }
        return (int) ($stock ?? 0);
    }

    protected function paginateCollection(Collection $items, int $perPage): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $total = $items->count();
        $results = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
}
