<?php

namespace App\Services\Erp;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    /**
     * ERP tarafında kargo ücreti ürünü (gizli tutulacak).
     */
    public const SHIPPING_PRODUCT_ID = 2925651;
    public function __construct(
        protected ErpApiClient $client
    ) {
    }

    /**
     * ERP'den sayfalı ürün listesi çeker.
     *
     * @param  array{page?:int,per_page?:int,search?:string,sort?:string,direction?:string,filters?:array}  $params
     */
    public function paginate(array $params = []): LengthAwarePaginator
    {
        $page = (int) ($params['page'] ?? request()->integer('page', 1));
        $perPage = (int) ($params['per_page'] ?? config('erp.defaults.per_page', 15));

        $query = [
            'page' => $page,
            'per_page' => $perPage,
        ];

        if (!empty($params['search'])) {
            $query['search'] = $params['search'];
        }

        if (!empty($params['sort'])) {
            $query['sort'] = $params['sort'];
            $query['direction'] = $params['direction'] ?? 'asc';
        }

        if (!empty($params['filters']) && is_array($params['filters'])) {
            foreach ($params['filters'] as $key => $value) {
                $query["filters[{$key}]"] = $value;
            }
        }

        $cacheKey = 'erp.products.paginate.' . md5(json_encode($query));

        $response = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($query) {
            return $this->client->get('/api/company/products', $query);
        });

        $items = collect(Arr::get($response, 'data.data'));
        if ($items->isEmpty() && is_array(Arr::get($response, 'data'))) {
            $items = collect(Arr::get($response, 'data'));
        }
        if ($items->isEmpty() && is_array(Arr::get($response, 'items'))) {
            $items = collect(Arr::get($response, 'items'));
        }

        $mapped = $items
            ->filter(function ($item) {
                return is_array($item);
            })
            ->map(fn(array $item) => $this->mapProduct($item))
            // Kargo ücreti ürünü (SHIPPING_PRODUCT_ID) hiçbir listede gösterilmemeli.
            ->filter(function ($product) {
                return (int) ($product->id ?? 0) !== self::SHIPPING_PRODUCT_ID;
            })
            ->values();

        $total = (int) (
            Arr::get($response, 'data.total') ??
            Arr::get($response, 'meta.total') ??
            Arr::get($response, 'total') ??
            $mapped->count()
        );

        $lastPage = (int) (
            Arr::get($response, 'data.last_page') ??
            Arr::get($response, 'meta.last_page') ??
            ceil($total / max(1, $perPage))
        );

        return new LengthAwarePaginator(
            $mapped,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    /**
     * Tek ürün detayı.
     */
    public function find(int $id): ?object
    {
        $cacheKey = "erp.products.find.{$id}";

        $response = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($id) {
            return $this->client->get("/api/company/products/{$id}");
        });

        $raw = Arr::get($response, 'data', $response);
        if (!is_array($raw)) {
            return null;
        }

        return $this->mapProduct($raw);
    }

    /**
     * Arama parametresi ile ilk eşleşen ürünü döndürür.
     */
    public function findBySearch(string $search): ?object
    {
        if ($search === '') {
            return null;
        }

        $query = [
            'search' => $search,
            'per_page' => 1,
            'page' => 1,
        ];

        $cacheKey = 'erp.products.search.' . md5(json_encode($query));

        $response = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($query) {
            return $this->client->get('/api/company/products', $query);
        });

        $items = collect(Arr::get($response, 'data.data'))
            ->whenEmpty(fn() => collect(Arr::get($response, 'data')))
            ->whenEmpty(fn() => collect(Arr::get($response, 'items')))
            ->whenEmpty(fn() => collect($response));

        $first = $items->first();
        if (!is_array($first)) {
            return null;
        }

        return $this->mapProduct($first);
    }

    /**
     * Sepet/checkout için ID listesiyle toplu ürün çekme.
     *
     * @param  int[]  $ids
     * @return Collection<int,object>
     */
    public function findManyByIds(array $ids): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        $filters = [
            'id' => '[' . implode(',', array_unique($ids)) . ']',
        ];

        $query = [
            'filters[id]' => $filters['id'],
            'per_page' => count($ids),
        ];

        $cacheKey = 'erp.products.many.' . md5(json_encode($query));

        $response = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($query) {
            return $this->client->get('/api/company/products', $query);
        });

        $items = collect(Arr::get($response, 'data'));
        if ($items->isEmpty() && is_array(Arr::get($response, 'items'))) {
            $items = collect(Arr::get($response, 'items'));
        }

        return $items
            ->filter(function ($item) {
                return is_array($item);
            })
            ->map(fn(array $item) => $this->mapProduct($item))
            ->keyBy('id');
    }

    /**
     * ERP ürün payload'unu Blade tarafının kullanabileceği ortak bir yapıya map eder.
     */
    protected function mapProduct(array $item): object
    {
        $id = (int) ($item['id'] ?? $item['product_id'] ?? 0);
        $categories = collect(Arr::get($item, 'categories', []))
            ->map(function ($cat) {
                if (!is_array($cat)) {
                    return null;
                }

                $catId = (int) ($cat['id'] ?? 0);
                $name = (string) ($cat['name'] ?? '');

                return (object) [
                    'id' => $catId,
                    'name' => $name,
                    'slug' => \Illuminate\Support\Str::slug($cat['slug'] ?? $name ?: 'kategori'),
                ];
            })
            ->filter()
            ->values();

        if ($categories->isEmpty()) {
            $categoryIds = Arr::get($item, 'category_ids', []);
            if (is_array($categoryIds) && !empty($categoryIds)) {
                static $categoryIndex = null;
                if ($categoryIndex === null) {
                    /** @var \App\Services\Erp\CategoryService $catService */
                    $catService = app(\App\Services\Erp\CategoryService::class);
                    $categoryIndex = $catService->all()->keyBy('id');
                }

                $categories = collect($categoryIds)
                    ->map(function ($catId) use ($categoryIndex) {
                        $catId = (int) $catId;
                        $cat = $categoryIndex->get($catId);
                        if ($cat) {
                            return (object) [
                                'id' => $cat->id,
                                'name' => $cat->name,
                                'slug' => $cat->slug,
                            ];
                        }
                        return null;
                    })
                    ->filter()
                    ->values();
            }
        }

        $rawPrice = null;
        if (isset($item['retail_selling_price']) && $item['retail_selling_price'] > 0) {
            $rawPrice = (float) $item['retail_selling_price'] + ($item['retail_selling_price'] * ($item['retail_tax_rate']['rate'] / 100));
        } else {
            $rawPrice = null;
        }

        $stock = null;
        if (isset($item['stock_quantity'])) {
            $stock = (float) $item['stock_quantity'];
        } elseif (isset($item['stock'])) {
            $stock = (float) $item['stock'];
        }

        $accountCurrency = Arr::get($item, 'account_currency.symbol');
        $retailCurrency = Arr::get($item, 'retail_currency.symbol');
        $currencySymbol = $accountCurrency ?? $retailCurrency ?? '₺';

        $appProductId = null;
        $sku = $item['sku'] ?? null;
        if ($sku) {
            $localProduct = \App\Models\Product::query()
                ->where('code', $sku)
                ->first();
            if ($localProduct) {
                $appProductId = $localProduct->id;
            }
        }

        return (object) [
            'id' => $id,
            'title' => $item['name'] ?? $item['title'] ?? null,
            'description' => $item['description'] ?? null,
            'sku' => $item['sku'] ?? null,
            'barcode' => $item['barcode'] ?? null,
            'photo' => $item['image'] ?? $item['photo'] ?? null,
            'price' => $rawPrice,
            'discount_price' => isset($item['discount_price']) ? (float) $item['discount_price'] : null,
            'stock' => $stock,
            // Blade tarafında Eloquent ilişki koleksiyonu ile aynı şekilde kullanılabilmesi için
            // kategoriler Illuminate\Support\Collection olarak döndürülür.
            'categories' => $categories,
            'currency_symbol' => $currencySymbol,
            'app_product_id' => $appProductId,
            'raw' => $item,
        ];
    }
}
