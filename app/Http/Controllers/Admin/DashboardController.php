<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\ProductAttributeTerm;
use App\Models\Campaign;
use App\Models\Visitor;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingOrders = Order::where('status', 'pending')
            ->with(['user', 'items.product'])
            ->latest()
            ->take(10)
            ->get();
        return view('admin.pages.dashboard.index', compact('pendingOrders'));
    }

    public function metrics(Request $request)
    {
        $categoryClicks = Category::orderByDesc('click_count')->limit(10)->get(['name', 'click_count']);
        $productClicks = Product::orderByDesc('click_count')->limit(10)->get(['title', 'click_count']);
        $visitorCount = optional(Setting::first())->visitor_count ?? 0;
        $categoriesCount = Category::count();
        $productsCount = Product::count();
        $customerCount = User::where('role', 0)->count();
        $campaignCount = Campaign::count();

        $orders = [
            'new' => Order::where('status', 'new')->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'canceled' => Order::where('status', 'canceled')->count(),
            'completed' => Order::where('status', 'completed')->count(),
        ];

        $dailyOrders = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dailyOrders[] = [
                'day' => $date->format('d M'),
                'new' => Order::where('status', 'new')->whereDate('created_at', $date)->count(),
                'pending' => Order::where('status', 'pending')->whereDate('created_at', $date)->count(),
                'completed' => Order::whereIn('status', ['completed', 'shipped', 'delivered'])->whereDate('created_at', $date)->count(),
                'canceled' => Order::where('status', 'canceled')->whereDate('created_at', $date)->count(),
            ];
        }

        $productsWithVariants = ProductAttributeTerm::query()
            ->whereNotNull('product_id')
            ->distinct()
            ->pluck('product_id')
            ->toArray();

        $lowStockSimple = Product::query()
            ->whereNotIn('id', $productsWithVariants)
            ->whereNotNull('stock')
            ->whereBetween('stock', [1, 10])
            ->count();

        $lowStockVariants = ProductAttributeTerm::query()
            ->whereNotNull('stock')
            ->whereBetween('stock', [1, 10])
            ->whereHas('product')
            ->count();

        $outStockSimple = Product::query()
            ->whereNotIn('id', $productsWithVariants)
            ->where(function ($q) {
                $q->where('stock', '<=', 0)
                    ->orWhere(function ($q2) {
                        $q2->whereNull('stock')
                            ->where(function ($q3) {
                                $q3->whereNull('stock_type')->orWhere('stock_type', 0);
                            });
                    });
            })
            ->count();

        $outStockVariants = ProductAttributeTerm::query()
            ->where(function ($q) {
                $q->where('stock', '<=', 0)
                    ->orWhere(function ($q2) {
                        $q2->whereNull('stock')
                            ->where(function ($q3) {
                                $q3->whereNull('stock_type')->orWhere('stock_type', 0);
                            });
                    });
            })
            ->whereHas('product')
            ->count();

        $lowStock = $lowStockSimple + $lowStockVariants;
        $outStock = $outStockSimple + $outStockVariants;

        $monthlyRevenue = [];
        $currentMonthRevenue = 0;
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthRevenue = (float)DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereYear('orders.created_at', $date->year)
                ->whereMonth('orders.created_at', $date->month)
                ->sum('order_items.total');
            $monthlyRevenue[] = [
                'month' => $date->format('M'),
                'revenue' => $monthRevenue
            ];
            if ($i === 0) {
                $currentMonthRevenue = $monthRevenue;
            }
        }

        $totalRevenue = (float)DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->sum('order_items.total');

        $performancePeriod = $request->get('performance_period', 'year');
        $performanceStats = $this->getPerformanceStats($performancePeriod);

        $basketPeriod = $request->get('basket_period', 'year');
        $basketStats = $this->getBasketStats($basketPeriod);

        $visitorPeriod = $request->get('visitor_period', '10min');
        $visitorStats = $this->getVisitorStats($visitorPeriod);

        return response()->json([
            'code' => 1,
            'msg' => 'ok',
            'data' => [
                'categoryClicks' => $categoryClicks,
                'productClicks' => $productClicks,
                'visitorCount' => (int)$visitorCount,
                'categoriesCount' => (int)$categoriesCount,
                'productsCount' => (int)$productsCount,
                'orders' => $orders,
                'lowStock' => $lowStock,
                'outStock' => $outStock,
                'customerCount' => $customerCount,
                'campaignsCount' => $campaignCount,
                'monthlyRevenue' => $monthlyRevenue,
                'currentMonthRevenue' => $currentMonthRevenue,
                'totalRevenue' => $totalRevenue,
                'performanceStats' => $performanceStats,
                'basketStats' => $basketStats,
                'visitorStats' => $visitorStats,
                'dailyOrders' => $dailyOrders,
            ]
        ]);
    }

    private function getPerformanceStats($period)
    {
        $query = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id');

        $this->applyPeriodFilter($query, $period, 'orders.created_at');

        $totalSales = (float)$query->sum('order_items.total');
        $itemCount = (float)$query->sum('order_items.quantity');
        $orderCount = (int)$query->distinct('order_items.order_id')->count('order_items.order_id');

        $avgSale = $orderCount > 0 ? $totalSales / $orderCount : 0;
        $avgOrderItems = $orderCount > 0 ? $itemCount / $orderCount : 0;

        return [
            'total_sales' => (float)$totalSales,
            'order_count' => (int)$orderCount,
            'avg_sale' => (float)$avgSale,
            'avg_order_items' => (float)$avgOrderItems,
        ];
    }

    private function getBasketStats($period)
    {
        $query = Cart::query();

        $this->applyPeriodFilter($query, $period, 'updated_at');

        $totalSales = (float)$query->sum('total_price');
        $orderCount = (int)$query->count();
        $basketAvgPrice = $orderCount > 0 ? $totalSales / $orderCount : 0;

        $trendQuery = Cart::query()
            ->select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('COUNT(id) as count'),
                DB::raw('SUM(total_price) as total')
            )
            ->whereNotNull('updated_at');

        $this->applyPeriodFilter($trendQuery, $period, 'updated_at');

        $trendData = $trendQuery->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => date('d M', strtotime($item->date)),
                    'count' => (int)$item->count,
                    'avg' => $item->count > 0 ? (float)($item->total / $item->count) : 0
                ];
            });

        return [
            'basket_count' => (int)$orderCount,
            'price_avg' => (float)$basketAvgPrice,
            'trend' => $trendData
        ];
    }

    private function getVisitorStats($period)
    {
        $query = Visitor::query();
        if ($period === '10min') {
            $query->where('visited_at', '>=', now()->subMinutes(10));
        } elseif ($period === '30min') {
            $query->where('visited_at', '>=', now()->subMinutes(30));
        } elseif ($period === '1hour') {
            $query->where('visited_at', '>=', now()->subHour());
        } elseif ($period === 'today') {
            $query->whereDate('visited_at', today());
        } elseif ($period === 'all') {
        }

        $total = $query->count();
        $devices = (clone $query)->select('device', DB::raw('count(*) as count'))->groupBy('device')->get();

        $desktopCount = 0;
        $mobileCount = 0;
        foreach ($devices as $d) {
            if (stripos($d->device, 'mobile') !== false) {
                $mobileCount += $d->count;
            } else {
                $desktopCount += $d->count;
            }
        }

        $desktopPercent = $total > 0 ? round(($desktopCount / $total) * 100) : 0;
        $mobilePercent = $total > 0 ? round(($mobileCount / $total) * 100) : 0;

        return [
            'total' => $total,
            'desktop_percent' => $desktopPercent,
            'mobile_percent' => $mobilePercent,
        ];
    }

    private function applyPeriodFilter($query, $period, $column = 'created_at')
    {
        if ($period === 'day') {
            $query->whereDate($column, today());
        } elseif ($period === 'week') {
            $query->whereBetween($column, [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereMonth($column, now()->month)->whereYear($column, now()->year);
        } elseif ($period === 'year') {
            $query->whereYear($column, now()->year);
        } elseif ($period === 'all') {
            // No filter
        }
    }
}
