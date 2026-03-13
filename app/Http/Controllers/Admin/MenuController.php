<?php

namespace App\Http\Controllers\Admin;

use App\Models\Menu;
use App\Models\Category;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('category')->orderBy('sort_order')->get();
        $categories = Category::orderBy('name')->get();
        return view('admin.pages.menu.index', compact('menus', 'categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'special_characters'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'show_in_footer' => ['nullable', 'boolean'],
            'show_in_menu' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        foreach (['show_in_footer', 'show_in_menu', 'is_active'] as $flag) {
            $data[$flag] = (bool)($data[$flag] ?? false);
        }
        $menu = Menu::create($data);

        app(AdminLogService::class)->log('Menü Oluşturuldu', null, $menu->toArray());

        return $this->jsonSuccess('Menü oluşturuldu', ['id' => $menu->id]);
    }

    public function update(Request $request, int $id)
    {
        $menu = Menu::findOrFail($id);
        $before = $menu->toArray();
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'special_characters'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'show_in_footer' => ['nullable', 'boolean'],
            'show_in_menu' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        foreach (['show_in_footer', 'show_in_menu', 'is_active'] as $flag) {
            $data[$flag] = (bool)($data[$flag] ?? false);
        }
        $menu->update($data);

        app(AdminLogService::class)->log('Menü Güncellendi', $before, $menu->fresh()->toArray());

        return $this->jsonSuccess('Menü güncellendi');
    }

    public function destroy(int $id)
    {
        $menu = Menu::findOrFail($id);
        $before = $menu->toArray();
        $menu->delete();

        app(AdminLogService::class)->log('Menü Silindi', $before, null);

        return $this->jsonSuccess('Menü silindi');
    }
}
