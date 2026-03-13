<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Setting;
use App\Models\Store;
use App\Models\Campaign;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Theme;
use App\Models\Product;
use App\Services\CartService;
use App\Support\TailwindColor;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected ?Setting $settings = null;
    protected ?Store $store = null;
    protected ?Theme $theme = null;
    public function __construct()
    {
        $this->settings = Setting::first();
        $this->settings->visitor_count = $this->settings->visitor_count + 1;
        $this->settings->save();
        $this->store = Store::first();
        $this->theme = Theme::first();
        $menus = Menu::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        $rootCategories = Category::query()
            ->with('children')
            ->whereNull('category_id')
            ->orderBy('name')
            ->get();
        $featuredProducts = Product::query()
            ->where('is_active', true)
            ->latest()
            ->take(12)
            ->get();


        $headerCampaign = Campaign::query()->where('section', 'header')->first();
        $footerCampaign = Campaign::query()->where('section', 'footer')->first();

        $cartSummaryData = app(CartService::class)->getSummary();
        $cartSummary = (object) [
            'count' => $cartSummaryData['count'] ?? 0,
            'total' => $cartSummaryData['total'] ?? 0,
        ];

        $topCategories = Category::query()
            ->with('children')
            ->whereNull('category_id')
            ->whereHas('products', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('name')
            ->get();

        $categoriesWithProducts = $topCategories->map(function (Category $category) {
            $category->setRelation('previewProducts', $category->products()
                ->where('is_active', true)
                ->latest()
                ->take(10)
                ->get());
            return $category;
        });

        $data = (object) [
            'categories' => $categoriesWithProducts,
        ];

        $categories = $data->categories ?? collect();


        $themeColors = TailwindColor::palette($this->theme?->color ?? null);

        $template = Theme::first()->thene;

        view()->share([
            'settings' => $this->settings,
            'store' => $this->store,
            'menus' => $menus,
            'header_campaign' => $headerCampaign,
            'footer_campaign' => $footerCampaign,
            'rootCategories' => $rootCategories,
            'featuredProducts' => $featuredProducts,
            'cartSummary' => $cartSummary,
            'theme' => $this->theme,
            'themeColors' => $themeColors,
            'template' => $template,
            'categories' => $categories,
        ]);
    }
}
