<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Visitor;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderItem::query()
            ->whereHas('order', function ($q) {
            $q->where('status', 'completed');
            })
            ->with(['order', 'product.categories']);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if ($request->filled('start_date')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->start_date);
            });
        }
        if ($request->filled('end_date')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->end_date);
            });
        }
        if ($request->filled('product_id')) {
            $query->where('order_items.product_id', $request->product_id);
        }
        if ($request->filled('category_id')) {
            $query->whereHas('product.categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }
        if ($request->filled('payment_method')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->where('method', $request->payment_method);
            });
        }
        if ($request->filled('min_price')) {
            $query->where('order_items.price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('order_items.price', '<=', $request->max_price);
        }

        $totals = (object) [
            'total_amount' => (clone $query)->sum('order_items.total'),
            'total_qty' => (clone $query)->sum('order_items.quantity'),
            'total_orders' => (clone $query)->distinct('order_id')->count('order_id'),
        ];

        $visitorQuery = Visitor::query();
        $cartQueryCount = Cart::query();
        $orderQueryCount = Order::query();

        if ($request->filled('start_date')) {
            $visitorQuery->whereDate('created_at', '>=', $request->start_date);
            $cartQueryCount->whereDate('created_at', '>=', $request->start_date);
            $orderQueryCount->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $visitorQuery->whereDate('created_at', '<=', $request->end_date);
            $cartQueryCount->whereDate('created_at', '<=', $request->end_date);
            $orderQueryCount->whereDate('created_at', '<=', $request->end_date);
        }

        $totalVisitors = $visitorQuery->distinct('ip_address')->count('ip_address');
        $totalCarts = $cartQueryCount->count();
        $totalOrdersSiteWide = $orderQueryCount->count();

        $conversions = (object) [
            'visitor_to_cart' => $totalVisitors > 0 ? ($totalCarts / $totalVisitors) * 100 : 0,
            'cart_to_order' => $totalCarts > 0 ? ($totalOrdersSiteWide / $totalCarts) * 100 : 0,
            'visitor_to_order' => $totalVisitors > 0 ? ($totalOrdersSiteWide / $totalVisitors) * 100 : 0,
            'counts' => [
                'visitors' => $totalVisitors,
                'carts' => $totalCarts,
                'orders' => $totalOrdersSiteWide
            ]
        ];

        $chartQuery = clone $query;

        $salesOverTime = (clone $chartQuery)
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select(DB::raw('DATE(orders.created_at) as date'), DB::raw('SUM(order_items.total) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $salesByCategory = (clone $chartQuery)
            ->join(DB::raw('(SELECT product_id, MIN(category_id) as first_category_id FROM category_product GROUP BY product_id) as primary_cats'), 'order_items.product_id', '=', 'primary_cats.product_id')
            ->join('categories', 'primary_cats.first_category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(order_items.total) as total'))
            ->groupBy('categories.name')
            ->get();

        $topProducts = (clone $chartQuery)
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.title', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.total) as total_amount'))
            ->groupBy('products.id', 'products.title')
            ->orderBy('total_amount', 'desc')
            ->take(10)
            ->get();

        $sales = $query->latest()->paginate(20)->withQueryString();
        $products = Product::select('id', 'title')->orderBy('title')->get();
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $paymentMethods = Order::select('method')->distinct()->whereNotNull('method')->pluck('method');

        return view('admin.pages.sales-reports.index', compact(
            'sales',
            'products',
            'categories',
            'paymentMethods',
            'salesOverTime',
            'salesByCategory',
            'topProducts',
            'totals',
            'conversions'
        ));
    }
}
