<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Store;
use App\Http\Controllers\Controller as BaseController;

abstract class Controller extends BaseController
{
    public function __construct()
    {
        
        view()->share([
            'settings' => Setting::first(),
            'order_count' => Order::count(),
            'order_count_pending' => Order::where('status', 'pending')->count(),
            'order_count_canceled' => Order::where('status', 'canceled')->count(),
            'order_count_completed' => Order::where('status', 'completed')->count(),
            'product_count' => Product::count(),
            'category_count' => Category::count(),
            'store' => Store::first(),
        ]);
    }

    protected function jsonSuccess(string $msg = 'Başarılı', array $data = [], int $status = 200)
    {
        return response()->json([
            'code' => 1,
            'msg' => $msg,
            'data' => $data,
        ], $status);
    }

    protected function jsonValidationError(array $errors, string $msg = 'Doğrulama hatası')
    {
        return response()->json([
            'code' => 2,
            'msg' => $msg,
            'errors' => $errors,
        ], 422);
    }

    protected function jsonError(string $msg = 'Hata', int $status = 400, array $errors = [])
    {
        return response()->json([
            'code' => 0,
            'msg' => $msg,
            'errors' => $errors,
        ], $status);
    }
}


