<?php

namespace App\Traits\Frontend;

use App\Services\Erp\ProductService;
use Illuminate\Support\Collection;

trait UsesErpCartProducts
{
    /**
     * Sepetteki ürünler için ERP'den sku (code) üzerinden ürün bilgilerini çeker.
     *
     * @param  object|null  $cart
     * @return Collection<string,object>  sku -> ErpProduct
     */
    protected function loadErpProductsForCart(?object $cart): Collection
    {
        if (
            !config('erp.enabled') ||
            !config('erp.frontend_enabled') ||
            !$cart ||
            !$cart->items ||
            $cart->items->isEmpty()
        ) {
            return collect();
        }

        $skus = $cart->items
            ->map(fn($item) => $item->product?->code)
            ->filter()
            ->unique()
            ->values();

        if ($skus->isEmpty()) {
            return collect();
        }

        /** @var ProductService $service */
        $service = app(ProductService::class);

        $result = collect();

        foreach ($skus as $sku) {
            $erpProduct = $service->findBySearch($sku);
            if ($erpProduct) {
                $result->put($sku, $erpProduct);
            }
        }

        return $result;
    }
}

