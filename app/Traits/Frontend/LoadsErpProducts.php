<?php

namespace App\Traits\Frontend;

use App\Services\Erp\ProductService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait LoadsErpProducts
{
    protected function erpFrontendEnabled(): bool
    {
        return (bool) config('erp.enabled') && (bool) config('erp.frontend_enabled');
    }

    /**
     * Ürün listeleme sayfası için ERP'den sayfalı ürün listesi çeker.
     */
    protected function loadErpProductIndex(Request $request, array $filters = []): LengthAwarePaginator
    {
        /** @var ProductService $service */
        $service = app(ProductService::class);

        $params = [
            'page' => $request->integer('page', 1),
            'per_page' => 15,
        ];

        if (!empty($filters['q'])) {
            $params['search'] = $filters['q'];
        }

        // Kategori filtresi varsa ERP filters[categories][]=ID formatında gönder.
        if (!empty($filters['category'])) {
            $params['filters'] = [
                'categories' => [(int) $filters['category']],
            ];
        }

        return $service->paginate($params);
    }

    /**
     * Ana sayfa için ERP'den son ürünleri getirir.
     */
    protected function loadErpHomeLatestProducts(int $limit = 15)
    {
        /** @var ProductService $service */
        $service = app(ProductService::class);

        $paginator = $service->paginate([
            'page' => 1,
            'per_page' => $limit,
            'sort' => 'created_at',
            'direction' => 'desc',
        ]);
        return $paginator->getCollection();
    }
}
