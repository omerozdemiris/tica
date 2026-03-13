<?php

namespace App\Http\Controllers\Admin;

use App\Models\HomeSection;
use App\Models\Category;
use App\Models\Product;
use App\Models\BlogPost;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeSectionController extends Controller
{
    public function index()
    {
        $sections = HomeSection::orderBy('sort_order')->get();
        return view('admin.pages.home_sections.index', compact('sections'));
    }

    public function create()
    {
        $categories = Category::take(20)->get();
        $products = Product::where('is_active', true)->take(20)->get();
        $blogs = BlogPost::where('is_published', true)->take(20)->get();
        return view('admin.pages.home_sections.create', compact('categories', 'products', 'blogs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $name = Str::slug($validated['name'], '_');

        if (HomeSection::where('name', $name)->exists()) {
            return response()->json([
                'msg' => 'Bu isimde bir section zaten mevcut.',
                'code' => 2
            ], 422);
        }

        $section = HomeSection::create([
            'name' => $name,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'is_active' => $request->has('is_active'),
            'sort_order' => HomeSection::max('sort_order') + 1,
            'data' => [
                'type' => $request->input('type', 'products'),
                'item_ids' => $request->input('item_ids', []),
                'category_id' => $request->input('category_id'),
            ]
        ]);

        app(AdminLogService::class)->log('Alan (HomeSection) Oluşturuldu', null, $section->toArray());

        return response()->json([
            'msg' => 'Section başarıyla oluşturuldu.',
            'redirect' => route('admin.home-sections.index')
        ]);
    }

    public function getItems(Request $request)
    {
        $type = $request->get('type', 'products');
        $search = $request->get('q');
        $skip = $request->get('skip', 0);
        $excludeIds = $request->get('exclude', []);

        if ($type === 'products' || $type === 'showcase') {
            $query = Product::where('is_active', true);
            if ($type === 'showcase') {
                $query->where('discount_price', '>', 0);
            }
            if ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            }
            if (!empty($excludeIds)) {
                $query->whereNotIn('id', $excludeIds);
            }
            $items = $query->skip($skip)->take(20)->get(['id', 'title', 'photo']);
            $items = $items->map(function ($item) use ($type) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'photo' => $item->photo ? asset($item->photo) : null,
                    'type' => $type
                ];
            });
        } elseif ($type === 'categories') {
            $query = Category::query();
            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }
            if (!empty($excludeIds)) {
                $query->whereNotIn('id', $excludeIds);
            }
            $items = $query->skip($skip)->take(20)->get(['id', 'name']);
            $items = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->name,
                    'photo' => null,
                    'type' => 'categories'
                ];
            });
        } elseif ($type === 'blogs') {
            $query = BlogPost::where('is_published', true);
            if ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            }
            if (!empty($excludeIds)) {
                $query->whereNotIn('id', $excludeIds);
            }
            $items = $query->skip($skip)->take(20)->get(['id', 'title', 'photo']);
            $items = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'photo' => $item->photo ? asset($item->photo) : null,
                    'type' => 'blogs'
                ];
            });
        } else {
            $items = collect();
        }

        return response()->json($items);
    }

    public function edit($id)
    {
        $section = HomeSection::findOrFail($id);

        $data = $section->data ?? [];
        $currentType = $data['type'] ?? 'products';
        $itemIds = $data['item_ids'] ?? [];

        $products = [];
        $categories = [];
        $blogs = [];

        if ($currentType === 'products' || $currentType === 'showcase') {
            $query = Product::where('is_active', true)->whereNotIn('id', $itemIds);
            if ($currentType === 'showcase') {
                $query->where('discount_price', '>', 0);
            }
            $products = $query->take(20)->get();
            $categories = Category::take(20)->get();
            $blogs = BlogPost::where('is_published', true)->take(20)->get();
        } elseif ($currentType === 'categories') {
            $products = Product::where('is_active', true)->take(20)->get();
            $categories = Category::whereNotIn('id', $itemIds)->take(20)->get();
            $blogs = BlogPost::where('is_published', true)->take(20)->get();
        } elseif ($currentType === 'blogs') {
            $products = Product::where('is_active', true)->take(20)->get();
            $categories = Category::take(20)->get();
            $blogs = BlogPost::where('is_published', true)->whereNotIn('id', $itemIds)->take(20)->get();
        }

        $selectedItems = [];
        if ($currentType === 'products' || $currentType === 'showcase') {
            $selectedItems = Product::whereIn('id', $itemIds)
                ->get()
                ->sortBy(function ($product) use ($itemIds) {
                    return array_search($product->id, $itemIds);
                });
        } elseif ($currentType === 'categories') {
            $selectedItems = Category::whereIn('id', $itemIds)
                ->get()
                ->sortBy(function ($category) use ($itemIds) {
                    return array_search($category->id, $itemIds);
                });
        } elseif ($currentType === 'blogs') {
            $selectedItems = BlogPost::whereIn('id', $itemIds)
                ->get()
                ->sortBy(function ($blog) use ($itemIds) {
                    return array_search($blog->id, $itemIds);
                });
        }

        return view('admin.pages.home_sections.edit', compact('section', 'categories', 'products', 'blogs', 'selectedItems'));
    }

    public function update(Request $request, $id)
    {
        $section = HomeSection::findOrFail($id);
        $before = $section->toArray();

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];

        if (!$section->is_fixed) {
            $rules['name'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'is_active' => $request->has('is_active'),
        ];

        if (!$section->is_fixed) {
            $updateData['name'] = Str::slug($validated['name'], '_');
        }

        // Only update data for non-fixed sections (fixed ones use their own logic)
        if (!$section->is_fixed) {
            $updateData['data'] = [
                'type' => $request->input('type', 'products'),
                'item_ids' => $request->input('item_ids', []),
                'category_id' => $request->input('category_id'),
            ];
        }

        $section->update($updateData);

        app(AdminLogService::class)->log('Alan (HomeSection) Güncellendi', $before, $section->fresh()->toArray());

        return response()->json([
            'msg' => 'Section başarıyla güncellendi.',
            'redirect' => route('admin.home-sections.index')
        ]);
    }

    public function destroy($id)
    {
        $section = HomeSection::findOrFail($id);
        $before = $section->toArray();

        if ($section->is_fixed) {
            return response()->json(['msg' => 'Sistem bölümleri silinemez.', 'code' => 2], 422);
        }

        $section->delete();

        app(AdminLogService::class)->log('Alan (HomeSection) Silindi', $before, null);

        return response()->json(['msg' => 'Section başarıyla silindi.']);
    }

    public function sort(Request $request)
    {
        $order = $request->input('order');
        foreach ($order as $index => $id) {
            HomeSection::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        app(AdminLogService::class)->log('Alan (HomeSection) Sıralaması Güncellendi', null, ['order' => $order]);

        return response()->json(['msg' => 'Sıralama güncellendi.']);
    }

    public function toggleStatus($id)
    {
        $section = HomeSection::findOrFail($id);
        $before = $section->toArray();
        $section->is_active = !$section->is_active;
        $section->save();

        app(AdminLogService::class)->log('Alan (HomeSection) Durumu Güncellendi', $before, $section->fresh()->toArray());

        return response()->json(['msg' => 'Durum güncellendi.']);
    }
}
