<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductComment;
use App\Models\Store;
use App\Models\Theme;
use App\Services\TrafficLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $theme = Theme::first();
        $query = Product::query()
            ->with(['categories', 'gallery'])
            ->where('is_active', true)
            ->latest();

        $filters = [
            'q' => $request->string('q')->toString(),
            'category' => $request->integer('category'),
        ];

        if ($filters['q']) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['q'].'%')
                    ->orWhere('description', 'like', '%'.$filters['q'].'%');
            });
        }

        if ($filters['category']) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('categories.id', $filters['category']);
            });
        }

        $products = $query->paginate(18)->withQueryString();
        $filterCategories = Category::query()
            ->whereNull('category_id')
            ->orderBy('name')
            ->get();

        $store = Store::first();
        $meta = (object) [
            'title' => ($store->meta_title ?? 'Mağaza').' | Ürünler',
            'description' => $store->meta_description ?? null,
        ];

        $data = (object) [
            'products' => $products,
            'filters' => (object) $filters,
            'meta' => $meta,
            'categories' => $filterCategories,
        ];

        return view($theme->thene.'.pages.products.index', [
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    public function show(Product $product, ?string $slug = null): View|RedirectResponse
    {
        TrafficLogger::logVisit('product', $product->id);
        $theme = Theme::first();
        $expectedSlug = Str::slug($product->title ?? 'urun');
        if ($slug !== $expectedSlug) {
            return redirect()->route('products.show', [$product->id, $expectedSlug]);
        }

        $product->load([
            'categories.parent.parent', // Load parents for breadcrumb
            'variants.attribute',
            'variants.term',
            'gallery',
        ]);

        $related = Product::query()
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->with(['categories', 'gallery'])
            ->latest()
            ->take(8)
            ->get();

        $store = Store::first();

        $meta = (object) [
            'title' => $product->meta_title ?? $product->title ?? $store->meta_title ?? config('app.name'),
            'description' => $product->meta_description ?? $store->meta_description ?? null,
        ];

        $mainCategory = $product->categories->first();
        $breadcrumbs = $this->generateBreadcrumbs($mainCategory);
        if ($mainCategory) {
            $breadcrumbs[] = [
                'label' => $mainCategory->name,
                'url' => route('categories.show', [$mainCategory->id, $mainCategory->slug]),
            ];
        }
        $comments = ProductComment::where('product_id', $product->id)->take(20)->get();
        $canComment = auth()->check() &&
            $comments->where('user_id', auth()->id())->isEmpty() &&
            auth()->user()->orders()->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })->exists();

        $data = (object) [
            'product' => $product,
            'related' => $related,
            'meta' => $meta,
            'breadcrumbs' => $breadcrumbs,
            'comments' => $comments,
            'canComment' => $canComment,
        ];

        $product->click_count = $product->click_count + 1;
        $product->save();

        return view($theme->thene.'.pages.products.show', [
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    public function categories(): View
    {
        $theme = Theme::first();
        $categories = Category::query()
            ->withCount('products')
            ->whereNull('category_id')
            ->orderBy('name')
            ->get();

        $store = Store::first();

        $meta = (object) [
            'title' => ($store->meta_title ?? 'Mağaza').' | Kategoriler',
            'description' => $store->meta_description ?? null,
        ];

        $data = (object) [
            'categories' => $categories,
            'meta' => $meta,
        ];

        return view($theme->thene.'.pages.products.categories', [
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    public function byCategory(Category $category, ?string $slug = null): View|RedirectResponse
    {
        TrafficLogger::logVisit('category', $category->slug);
        $theme = Theme::first();
        if ($slug !== $category->slug) {
            return redirect()->route('categories.show', [$category->id, $category->slug]);
        }

        $category->load(['parent.parent.parent', 'children']);

        $breadcrumbs = $this->generateBreadcrumbs($category);
        $activeCategoryPathIds = array_column($breadcrumbs, 'id'); // We need IDs for sidebar
        // Add current category ID
        $activeCategoryPathIds[] = $category->id;

        // Fix activeCategoryPathIds because generateBreadcrumbs now returns arrays, not models.
        // We need to re-implement logic to get IDs efficiently or just traverse parents again.
        // Actually let's keep generateBreadcrumbs simple and traverse for IDs here.

        $activeCategoryPathIds = [$category->id];
        $parent = $category->parent;
        while ($parent) {
            $activeCategoryPathIds[] = $parent->id;
            $parent = $parent->parent;
        }

        $allCategoryIds = $this->getAllDescendantCategoryIds($category);
        $allCategoryIds[] = $category->id;

        $products = Product::query()
            ->where('is_active', true)
            ->whereHas('categories', function ($query) use ($allCategoryIds) {
                $query->whereIn('categories.id', $allCategoryIds);
            })
            ->with(['categories', 'gallery'])
            ->latest()
            ->paginate(18)
            ->withQueryString();

        $rootCategories = Category::query()
            ->whereNull('category_id')
            ->with(['children.children.children'])
            ->orderBy('name')
            ->get();

        $store = Store::first();

        $meta = (object) [
            'title' => $category->name.' | '.($store->meta_title ?? config('app.name')),
            'description' => $category->description ?? $store->meta_description ?? null,
        ];

        $data = (object) [
            'category' => $category,
            'products' => $products,
            'breadcrumbs' => $breadcrumbs,
            'activeCategoryPathIds' => $activeCategoryPathIds,
            'childCategories' => $category->children,
            'meta' => $meta,
            'rootCategories' => $rootCategories,
        ];

        return view($theme->thene.'.pages.products.category', [
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    private function getAllDescendantCategoryIds(Category $category): array
    {
        $ids = [];
        $children = $category->children()->get();

        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getAllDescendantCategoryIds($child));
        }

        return $ids;
    }

    private function generateBreadcrumbs(?Category $category): array
    {
        $breadcrumbs = [];
        if (! $category) {
            return $breadcrumbs;
        }

        $parent = $category->parent;
        while ($parent) {
            array_unshift($breadcrumbs, [
                'label' => $parent->name,
                'url' => route('categories.show', [$parent->id, $parent->slug]),
                'id' => $parent->id,
            ]);
            $parent = $parent->parent;
        }

        return $breadcrumbs;
    }
}
