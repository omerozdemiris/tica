<?php

namespace App\Http\Controllers\Admin;

use App\Models\Promotion;
use App\Models\Product;
use App\Models\Category;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::latest()->get();
        return view('admin.pages.promotions.index', compact('promotions'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->take(20)->get();
        $categories = Category::take(20)->get();
        return view('admin.pages.promotions.create', compact('products', 'categories'));
    }

    public function getItems(Request $request)
    {
        $type = $request->get('type', 'products');
        $search = $request->get('q');
        $skip = $request->get('skip', 0);
        $excludeIds = $request->get('exclude', []);

        if ($type === 'products') {
            $query = Product::where('is_active', true);
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
                    'type' => 'products'
                ];
            });
        } else {
            $query = Category::query();
            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }
            if (!empty($excludeIds)) {
                $query->whereNotIn('id', $excludeIds);
            }
            // Kategorilerde skip optimizasyonu istenmediği için hepsini veya arananı getiriyoruz
            $items = $query->get(['id', 'name']);
            $items = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->name,
                    'photo' => null,
                    'type' => 'categories'
                ];
            });
        }

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:promotions,code',
            'discount_rate' => 'required|integer|min:1|max:100',
            'condition_type' => 'required|in:1,2',
            'usage_limit' => 'required_if:condition_type,1|nullable|integer|min:1',
            'start_date' => 'required_if:condition_type,2|nullable|date',
            'end_date' => 'required_if:condition_type,2|nullable|date|after:start_date',
            'type' => 'required|in:products,categories,cart_total',
            'item_ids' => 'required_unless:type,cart_total|array',
            'min_total' => 'required_if:type,cart_total|nullable|numeric|min:0',
        ]);

        $promotion = Promotion::create([
            'code' => $validated['code'],
            'discount_rate' => $validated['discount_rate'],
            'condition_type' => $validated['condition_type'],
            'usage_limit' => $validated['usage_limit'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'min_total' => $validated['min_total'] ?? null,
            'is_active' => $request->has('is_active'),
            'data' => [
                'type' => $validated['type'],
                'item_ids' => $validated['item_ids'] ?? [],
            ],
            'public' => $request->has('public'),
        ]);

        return response()->json([
            'msg' => 'Promosyon başarıyla oluşturuldu.',
            'redirect' => route('admin.promotions.index')
        ]);
    }

    public function edit($id)
    {
        $promotion = Promotion::findOrFail($id);

        $data = $promotion->data ?? [];
        $currentType = $data['type'] ?? 'products';
        $itemIds = $data['item_ids'] ?? [];

        $products = [];
        $categories = [];

        if ($currentType === 'products') {
            $products = Product::where('is_active', true)->whereNotIn('id', $itemIds)->take(20)->get();
            $categories = Category::take(20)->get();
        } else if ($currentType === 'categories') {
            $products = Product::where('is_active', true)->take(20)->get();
            $categories = Category::whereNotIn('id', $itemIds)->get();
        } else {
            $products = Product::where('is_active', true)->take(20)->get();
            $categories = Category::get();
        }

        $selectedItems = [];
        if ($currentType === 'products') {
            $selectedItems = Product::whereIn('id', $itemIds)->get();
        } else if ($currentType === 'categories') {
            $selectedItems = Category::whereIn('id', $itemIds)->get();
        }

        return view('admin.pages.promotions.edit', compact('promotion', 'products', 'categories', 'selectedItems'));
    }

    public function update(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);
        $before = $promotion->toArray();

        $validated = $request->validate([
            'code' => 'required|string|unique:promotions,code,' . $id,
            'discount_rate' => 'required|integer|min:1|max:100',
            'condition_type' => 'required|in:1,2',
            'usage_limit' => 'required_if:condition_type,1|nullable|integer|min:1',
            'start_date' => 'required_if:condition_type,2|nullable|date',
            'end_date' => 'required_if:condition_type,2|nullable|date|after:start_date',
            'type' => 'required|in:products,categories,cart_total',
            'item_ids' => 'required_unless:type,cart_total|array',
            'min_total' => 'required_if:type,cart_total|nullable|numeric|min:0',
        ]);

        $promotion->update([
            'code' => $validated['code'],
            'discount_rate' => $validated['discount_rate'],
            'condition_type' => $validated['condition_type'],
            'usage_limit' => $validated['usage_limit'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'min_total' => $validated['min_total'] ?? null,
            'is_active' => $request->has('is_active'),
            'data' => [
                'type' => $validated['type'],
                'item_ids' => $validated['item_ids'] ?? [],
            ],
            'public' => $request->has('public'),
        ]);

        app(AdminLogService::class)->log('Promosyon Güncellendi', $before, $promotion->fresh()->toArray());

        return response()->json([
            'msg' => 'Promosyon başarıyla güncellendi.',
            'redirect' => route('admin.promotions.index')
        ]);
    }

    public function destroy($id)
    {
        $promotion = Promotion::findOrFail($id);
        $before = $promotion->toArray();
        $promotion->delete();

        app(AdminLogService::class)->log('Promosyon Silindi', $before, null);

        return response()->json(['msg' => 'Promosyon silindi.']);
    }

    public function toggleStatus($id)
    {
        $promotion = Promotion::findOrFail($id);
        $before = $promotion->toArray();
        $promotion->is_active = !$promotion->is_active;
        $promotion->save();

        app(AdminLogService::class)->log('Promosyon Durumu Güncellendi', $before, $promotion->fresh()->toArray());

        return response()->json(['msg' => $promotion->is_active ? 'Aktif yapıldı.' : 'Pasif yapıldı.']);
    }

    public function togglePublic($id)
    {
        $promotion = Promotion::findOrFail($id);
        $before = $promotion->toArray();
        $promotion->public = !$promotion->public;
        $promotion->save();

        app(AdminLogService::class)->log('Promosyon Erişilebilirliği Güncellendi', $before, $promotion->fresh()->toArray());

        return response()->json(['msg' => $promotion->public ? 'Herkese açık yapıldı.' : 'Herkese kapalı yapıldı.']);
    }
}
