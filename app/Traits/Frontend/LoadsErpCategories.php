<?php

namespace App\Traits\Frontend;

use App\Models\Category as LocalCategory;
use App\Services\Erp\CategoryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait LoadsErpCategories
{
    /**
     * ERP'den kategori listesini çekip, mümkünse mevcut Category modeli ile eşleştirir.
     *
     * @return Collection<int,object>
     */
    protected function loadErpCategoriesForListing(): Collection
    {
        if (!config('erp.enabled') || !config('erp.frontend_enabled')) {
            return collect();
        }

        return Cache::remember('erp_categories_listing', now()->addMinutes(1), function () {
            /** @var CategoryService $service */
            $service = app(CategoryService::class);
            $categories = $service->all();

            return $categories
                ->map(function ($cat) {
                    $local = LocalCategory::query()
                        ->where('name', $cat->name)
                        ->orWhere('slug', $cat->slug)
                        ->first();

                    $cat->app_category_id = $local?->id;
                    if ($local) {
                        $cat->description = $local->description;
                    }

                    return $cat;
                })
                ->values();
        });
    }
}

