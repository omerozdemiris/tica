<?php

namespace App\Services\Erp;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    public function __construct(
        protected ErpApiClient $client
    ) {
    }

    /**
     * ERP'den tüm kategorileri düz liste olarak çeker.
     *
     * @return Collection<int,object>
     */
    public function all(): Collection
    {
        // Kategorileri 30 dakika boyunca cache'te tut.
        return Cache::remember('erp.categories.all', now()->addMinutes(30), function () {
            $response = $this->client->get('/api/company/product-categories');

            $items = collect(Arr::get($response, 'data'))
                ->whenEmpty(fn() => collect(Arr::get($response, 'items')))
                ->whenEmpty(fn() => collect($response));

            return $items
                ->filter(function ($item) {
                    return is_array($item);
                })
                ->map(fn(array $item) => $this->mapCategory($item))
                ->values();
        });
    }

    /**
     * Tek kategori detayı.
     */
    public function find(int $id): ?object
    {
        $response = $this->client->get("/api/company/product-categories/{$id}");

        $raw = Arr::get($response, 'data', $response);
        if (!is_array($raw)) {
            return null;
        }

        return $this->mapCategory($raw);
    }

    /**
     * Kategori listesini parent/child ilişkisine göre ağaç haline getirir.
     *
     * @return Collection<int,object>
     */
    public function tree(): Collection
    {
        // Ağaç yapısını da 30 dakika cache'le.
        return Cache::remember('erp.categories.tree', now()->addMinutes(30), function () {
            $all = $this->all();

            /** @var array<int,object> $byId */
            $byId = [];
            foreach ($all as $cat) {
                $cat->children = [];
                $byId[$cat->id] = $cat;
            }

            $roots = [];

            foreach ($byId as $cat) {
                $parentId = $cat->parent_id;
                if ($parentId && isset($byId[$parentId])) {
                    $byId[$parentId]->children[] = $cat;
                } else {
                    $roots[] = $cat;
                }
            }

            return collect($roots);
        });
    }

    /**
     * ERP kategori payload'unu ortak yapıya map eder.
     */
    protected function mapCategory(array $item): object
    {
        $id = (int) ($item['id'] ?? 0);
        $name = (string) ($item['name'] ?? '');
        $slug = (string) ($item['slug'] ?? \Illuminate\Support\Str::slug($name ?: 'kategori'));

        return (object) [
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
            'parent_id' => isset($item['parent_id']) ? (int) $item['parent_id'] : null,
            'products_count' => isset($item['products_count']) ? (int) $item['products_count'] : null,
            'raw' => $item,
        ];
    }
}
