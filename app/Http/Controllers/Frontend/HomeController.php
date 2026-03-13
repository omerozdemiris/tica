<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Theme;
use App\Models\Template;
use App\Models\Product;
use App\Models\Store;
use App\Models\Slider;
use App\Models\HomeSection;
use App\Models\BlogPost;
use Illuminate\Contracts\View\View;
use App\Services\TrafficLogger;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request): View
    {

        TrafficLogger::logVisit('home');
        $store = Store::first();
        if ($store && $store->maintenance == 1) {
            return view('frontend.pages.maintenance');
        }

        $sections = HomeSection::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($section) {
                $data = $section->data;
                if ($section->is_fixed) return $section;

                if (($data['type'] ?? '') === 'products' && !empty($data['item_ids'])) {
                    $section->items = Product::whereIn('id', $data['item_ids'])
                        ->where('is_active', true)
                        ->get()
                        ->sortBy(function ($product) use ($data) {
                            return array_search($product->id, $data['item_ids']);
                        });
                } elseif (($data['type'] ?? '') === 'categories' && !empty($data['item_ids'])) {
                    $section->items = Category::whereIn('id', $data['item_ids'])
                        ->get()
                        ->sortBy(function ($category) use ($data) {
                            return array_search($category->id, $data['item_ids']);
                        });
                } elseif (($data['type'] ?? '') === 'showcase') {
                    if (!empty($data['item_ids'])) {
                        $section->items = Product::whereIn('id', $data['item_ids'])
                            ->where('is_active', true)
                            ->get()
                            ->sortBy(function ($product) use ($data) {
                                return array_search($product->id, $data['item_ids']);
                            });
                    } else {
                        $section->items = Product::where('is_active', true)
                            ->whereNotNull('discount_price')
                            ->where('discount_price', '>', 0)
                            ->latest()
                            ->take(10)
                            ->get();
                    }
                } elseif (($data['type'] ?? '') === 'blogs' && !empty($data['item_ids'])) {
                    $section->items = BlogPost::whereIn('id', $data['item_ids'])
                        ->where('is_published', true)
                        ->get()
                        ->sortBy(function ($blog) use ($data) {
                            return array_search($blog->id, $data['item_ids']);
                        });
                }
                return $section;
            });

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

        $latestProducts = Product::query()
            ->where('is_active', true)
            ->latest()
            ->take(15)
            ->get();

        $meta = (object) [
            'title' => $store->meta_title ?? config('app.name'),
            'description' => $store->meta_description ?? null,
        ];

        $data = (object) [
            'categories' => $categoriesWithProducts,
            'latestProducts' => $latestProducts,
            'meta' => $meta,
        ];

        $latestProducts = $data->latestProducts ?? collect();

        $sliders = Slider::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $activeAnnouncement = Announcement::query()
            ->where('is_active', true)
            ->latest()
            ->first();


        $theme = Theme::first();
        return view($theme->thene . '.pages.home', [
            'data' => $data,
            'sliders' => $sliders,
            'latestProducts' => $latestProducts,
            'meta' => $meta,
            'activeAnnouncement' => $activeAnnouncement,
            'sections' => $sections,
        ]);
    }
}
