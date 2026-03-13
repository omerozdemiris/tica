<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Category::query();
            if ($request->filled('keyword')) {
                $query->where('name', 'like', '%' . $request->keyword . '%');
            }
            return response()->json($query->orderBy('name')->get());
        }

        $currentCategory = null;
        $breadcrumbs = [];

        if ($request->has('category') && $request->category) {
            $currentCategory = Category::with(['parent.parent.parent'])->withCount('products')->findOrFail($request->category);

            $breadcrumbs = [];
            $parent = $currentCategory->parent;
            while ($parent) {
                array_unshift($breadcrumbs, $parent);
                $parent = $parent->parent;
            }
            $breadcrumbs[] = $currentCategory;

            $categories = $currentCategory->children()
                ->withCount(['products', 'children'])
                ->orderBy('name')
                ->get();
        } else {
            $categories = Category::whereNull('category_id')
                ->withCount(['products', 'children'])
                ->orderBy('name')
                ->get();
        }

        return view('admin.pages.categories.index', compact('categories', 'currentCategory', 'breadcrumbs'));
    }

    public function create()
    {
        $parents = Category::orderBy('name')->get();
        return view('admin.pages.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'special_characters'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255', 'special_characters'],
            'meta_description' => ['nullable', 'string'],
            'photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        $data['slug'] = Str::slug($data['name'] ?? '');

        if ($request->hasFile('photo_file')) {
            $dir = public_path('upload/categories');
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $file = $request->file('photo_file');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $name = uniqid('cat_') . '.' . $ext;
            $file->move($dir, $name);
            $data['photo'] = '/upload/categories/' . $name;
        }

        $category = Category::create($data);
        app(AdminLogService::class)->log('Kategori Oluşturuldu', null, $category->toArray());
        return $this->jsonSuccess('Kategori oluşturuldu');
    }

    public function edit(int $id)
    {
        $category = Category::findOrFail($id);
        $parents = Category::where('id', '<>', $id)->orderBy('name')->get();
        return view('admin.pages.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, int $id)
    {
        $category = Category::findOrFail($id);
        $before = $category->toArray();
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'special_characters'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255', 'special_characters'],
            'meta_description' => ['nullable', 'string', 'special_characters'],
            'remove_photo' => ['nullable', 'boolean'],
            'photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        $data['slug'] = Str::slug($data['name'] ?? $category->name);

        if ($request->hasFile('photo_file')) {
            $dir = public_path('upload/categories');
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            // Eski fotoğrafı sil
            if ($category->photo && is_file(public_path(ltrim($category->photo, '/')))) {
                @unlink(public_path(ltrim($category->photo, '/')));
            }

            $file = $request->file('photo_file');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $name = uniqid('cat_') . '.' . $ext;
            $file->move($dir, $name);
            $data['photo'] = '/upload/categories/' . $name;
        }

        $category->update($data);

        if ($request->boolean('remove_photo') && $category->photo) {
            $path = public_path(ltrim($category->photo, '/'));
            if (is_file($path)) {
                @unlink($path);
            }
            $category->photo = null;
            $category->save();
        }

        app(AdminLogService::class)->log('Kategori Güncellendi', $before, $category->fresh()->toArray());

        return $this->jsonSuccess('Kategori güncellendi');
    }

    public function destroy(int $id)
    {
        $category = Category::findOrFail($id);
        $before = $category->toArray();
        $category->products()->detach();
        $category->delete();

        app(AdminLogService::class)->log('Kategori Silindi', $before, null);

        return $this->jsonSuccess('Kategori silindi');
    }

    public function removeProduct(int $categoryId, int $productId)
    {
        $category = Category::findOrFail($categoryId);
        $category->products()->detach($productId);

        app(AdminLogService::class)->log('Ürün Kategoriden Çıkarıldı', ['category_id' => $categoryId, 'product_id' => $productId], null);

        return $this->jsonSuccess('Ürün kategoriden çıkarıldı');
    }

    public function destroyProducts(int $id)
    {
        $category = Category::findOrFail($id);
        $productIds = $category->products()->pluck('products.id')->all();
        if (!empty($productIds)) {
            $products = \App\Models\Product::whereIn('id', $productIds)->get();
            foreach ($products as $product) {
                // İlişkileri ve varyasyonları temizle, ardından ürünü sil
                $product->categories()->detach();
                if (method_exists($product, 'variants')) {
                    $product->variants()->delete();
                }
                $product->delete();
            }
            app(AdminLogService::class)->log('Kategorideki Tüm Ürünler Silindi', ['category_id' => $id, 'product_count' => count($productIds)], null);
        }
        return $this->jsonSuccess('Kategoriye bağlı tüm ürünler silindi');
    }
}
