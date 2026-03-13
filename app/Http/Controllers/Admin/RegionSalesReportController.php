<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegionSalesReportController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('user_addresses', 'orders.shipping_address_id', '=', 'user_addresses.id')
            ->select('order_items.*', 'orders.created_at as order_date', 'user_addresses.city_id', 'user_addresses.state_id', 'user_addresses.city as city_name', 'user_addresses.state as state_name');

        if ($request->filled('start_date')) {
            $query->whereDate('orders.created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('orders.created_at', '<=', $request->end_date);
        }
        if ($request->filled('product_id')) {
            $query->where('order_items.product_id', $request->product_id);
        }
        if ($request->filled('category_id')) {
            $query->whereExists(function ($q) use ($request) {
                $q->select(DB::raw(1))
                    ->from('category_product')
                    ->whereColumn('category_product.product_id', 'order_items.product_id')
                    ->where('category_product.category_id', $request->category_id);
            });
        }
        if ($request->filled('min_price')) {
            $query->where('order_items.price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('order_items.price', '<=', $request->max_price);
        }

        // Region Filters
        if ($request->filled('city_id')) {
            $query->where('user_addresses.city_id', $request->city_id);
        }

        $totals = (object) [
            'total_amount' => (clone $query)->sum('order_items.total'),
            'total_qty' => (clone $query)->sum('order_items.quantity'),
            'total_orders' => (clone $query)->distinct('order_items.order_id')->count('order_items.order_id'),
        ];

        // Şehir bazlı veriler (Koordinatlı)
        $cityCoordinates = $this->getCityCoordinates();
        $salesByCity = (clone $query)
            ->join('cities', 'user_addresses.city_id', '=', 'cities.id')
            ->select('cities.plate_code', 'cities.name', DB::raw('SUM(order_items.total) as total'), DB::raw('COUNT(DISTINCT orders.id) as order_count'))
            ->whereNotNull('user_addresses.city')
            ->groupBy('cities.plate_code', 'cities.name')
            ->get()
            ->map(function ($city) use ($cityCoordinates) {
                $plate = str_pad($city->plate_code, 2, '0', STR_PAD_LEFT);
                $city->lat = $cityCoordinates[$plate]['lat'] ?? 39.0;
                $city->lng = $cityCoordinates[$plate]['lng'] ?? 35.0;
                return $city;
            });

        $products = Product::select('id', 'title')->orderBy('title')->get();
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $cities = City::orderBy('name')->get();

        $unfilteredSalesByCity = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('user_addresses', 'orders.shipping_address_id', '=', 'user_addresses.id')
            ->join('cities', 'user_addresses.city_id', '=', 'cities.id')
            ->select('cities.plate_code', 'cities.name', DB::raw('SUM(order_items.total) as total'), DB::raw('COUNT(DISTINCT orders.id) as order_count'))
            ->whereNotNull('user_addresses.city')
            ->groupBy('cities.plate_code', 'cities.name')
            ->get();

        $mostOrderedCity = $unfilteredSalesByCity->sortByDesc('order_count')->first();
        $leastOrderedCity = $unfilteredSalesByCity->where('order_count', '>', 0)->sortBy('order_count')->first();

        $regionMapping = $this->getRegionMapping();
        $unfilteredSalesByRegion = $unfilteredSalesByCity->groupBy(function ($city) use ($regionMapping) {
            $plate = str_pad($city->plate_code, 2, '0', STR_PAD_LEFT);
            return $regionMapping[$plate] ?? 'Bilinmeyen Bölge';
        })->map(function ($cities, $region) {
            return [
                'name' => $region,
                'total' => $cities->sum('total'),
                'order_count' => $cities->sum('order_count')
            ];
        })->sortByDesc('order_count');

        $mostActiveRegion = $unfilteredSalesByRegion->first();

        // 1. Premium Müşteriler (En yüksek tekil sipariş tutarları)
        $premiumCustomers = Order::query()
            ->join('user_addresses', 'orders.shipping_address_id', '=', 'user_addresses.id')
            ->select('user_addresses.fullname as customer_name', 'user_addresses.city', 'orders.total', 'orders.created_at')
            ->orderByDesc('orders.total')
            ->limit(3)
            ->get();

        // 2. Tekrar Sipariş Oranı % (Müşteri bazlı)
        $customerStats = DB::table('orders')
            ->join('user_addresses', 'orders.shipping_address_id', '=', 'user_addresses.id')
            ->select('user_addresses.email', DB::raw('COUNT(orders.id) as order_count'))
            ->whereNotNull('user_addresses.email')
            ->groupBy('user_addresses.email')
            ->get();
        $totalCustomers = $customerStats->count();
        $repeatCustomers = $customerStats->where('order_count', '>', 1)->count();
        $repeatRate = $totalCustomers > 0 ? ($repeatCustomers / $totalCustomers) * 100 : 0;

        // 3. En Yoğun Gün / Saat
        $dayMapping = [
            'Monday' => 'Pazartesi',
            'Tuesday' => 'Salı',
            'Wednesday' => 'Çarşamba',
            'Thursday' => 'Perşembe',
            'Friday' => 'Cuma',
            'Saturday' => 'Cumartesi',
            'Sunday' => 'Pazar'
        ];
        $busyDayRaw = Order::select(DB::raw('DAYNAME(created_at) as day'), DB::raw('COUNT(id) as count'))
            ->groupBy('day')
            ->orderByDesc('count')
            ->first();
        $busyDay = $busyDayRaw ? ($dayMapping[$busyDayRaw->day] ?? $busyDayRaw->day) : '-';

        $busyHourRaw = Order::select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(id) as count'))
            ->groupBy('hour')
            ->orderByDesc('count')
            ->first();
        $busyHour = $busyHourRaw ? sprintf('%02d:00 - %02d:00', $busyHourRaw->hour, $busyHourRaw->hour + 1) : '-';

        // 4. Bölgesel Sadakat Oranı (Şehir bazlı tekrar sipariş oranı)
        $cityLoyalty = DB::table('orders')
            ->join('user_addresses', 'orders.shipping_address_id', '=', 'user_addresses.id')
            ->select('user_addresses.city', 'user_addresses.email', DB::raw('COUNT(orders.id) as order_count'))
            ->whereNotNull('user_addresses.city')
            ->whereNotNull('user_addresses.email')
            ->groupBy('user_addresses.city', 'user_addresses.email')
            ->get()
            ->groupBy('city')
            ->map(function ($items, $cityName) {
                $totalInCity = $items->count();
                $repeatsInCity = $items->where('order_count', '>', 1)->count();
                return [
                    'name' => $cityName,
                    'rate' => $totalInCity > 0 ? ($repeatsInCity / $totalInCity) * 100 : 0
                ];
            })
            ->sortByDesc('rate')
            ->take(5);

        // 5. En Son Sipariş Gelen İl
        $lastOrder = Order::query()
            ->join('user_addresses', 'orders.shipping_address_id', '=', 'user_addresses.id')
            ->select('user_addresses.city', 'orders.created_at')
            ->orderByDesc('orders.created_at')
            ->first();

        return view('admin.pages.sales-reports.regions', compact(
            'totals',
            'salesByCity',
            'products',
            'categories',
            'cities',
            'mostOrderedCity',
            'leastOrderedCity',
            'mostActiveRegion',
            'premiumCustomers',
            'repeatRate',
            'busyDay',
            'busyHour',
            'cityLoyalty',
            'lastOrder'
        ));
    }

    private function getRegionMapping()
    {
        return [
            '01' => 'Akdeniz',
            '02' => 'Güneydoğu Anadolu',
            '03' => 'Ege',
            '04' => 'Doğu Anadolu',
            '05' => 'Karadeniz',
            '06' => 'İç Anadolu',
            '07' => 'Akdeniz',
            '08' => 'Karadeniz',
            '09' => 'Ege',
            '10' => 'Marmara',
            '11' => 'Marmara',
            '12' => 'Doğu Anadolu',
            '13' => 'Doğu Anadolu',
            '14' => 'Karadeniz',
            '15' => 'Akdeniz',
            '16' => 'Marmara',
            '17' => 'Marmara',
            '18' => 'İç Anadolu',
            '19' => 'Karadeniz',
            '20' => 'Ege',
            '21' => 'Güneydoğu Anadolu',
            '22' => 'Marmara',
            '23' => 'Doğu Anadolu',
            '24' => 'Doğu Anadolu',
            '25' => 'Doğu Anadolu',
            '26' => 'İç Anadolu',
            '27' => 'Güneydoğu Anadolu',
            '28' => 'Karadeniz',
            '29' => 'Karadeniz',
            '30' => 'Doğu Anadolu',
            '31' => 'Akdeniz',
            '32' => 'Akdeniz',
            '33' => 'Akdeniz',
            '34' => 'Marmara',
            '35' => 'Ege',
            '36' => 'Doğu Anadolu',
            '37' => 'Karadeniz',
            '38' => 'İç Anadolu',
            '39' => 'Marmara',
            '40' => 'İç Anadolu',
            '41' => 'Marmara',
            '42' => 'İç Anadolu',
            '43' => 'Ege',
            '44' => 'Doğu Anadolu',
            '45' => 'Ege',
            '46' => 'Akdeniz',
            '47' => 'Güneydoğu Anadolu',
            '48' => 'Ege',
            '49' => 'Doğu Anadolu',
            '50' => 'İç Anadolu',
            '51' => 'İç Anadolu',
            '52' => 'Karadeniz',
            '53' => 'Karadeniz',
            '54' => 'Marmara',
            '55' => 'Karadeniz',
            '56' => 'Güneydoğu Anadolu',
            '57' => 'Karadeniz',
            '58' => 'İç Anadolu',
            '59' => 'Marmara',
            '60' => 'Karadeniz',
            '61' => 'Karadeniz',
            '62' => 'Doğu Anadolu',
            '63' => 'Güneydoğu Anadolu',
            '64' => 'Ege',
            '65' => 'Doğu Anadolu',
            '66' => 'İç Anadolu',
            '67' => 'Karadeniz',
            '68' => 'İç Anadolu',
            '69' => 'Karadeniz',
            '70' => 'İç Anadolu',
            '71' => 'İç Anadolu',
            '72' => 'Güneydoğu Anadolu',
            '73' => 'Güneydoğu Anadolu',
            '74' => 'Karadeniz',
            '75' => 'Doğu Anadolu',
            '76' => 'Doğu Anadolu',
            '77' => 'Marmara',
            '78' => 'Karadeniz',
            '79' => 'Güneydoğu Anadolu',
            '80' => 'Akdeniz',
            '81' => 'Karadeniz',
        ];
    }

    private function getCityCoordinates()
    {
        return [
            '01' => ['lat' => 37.0000, 'lng' => 35.3213],
            '02' => ['lat' => 37.7648, 'lng' => 38.2786],
            '03' => ['lat' => 38.7507, 'lng' => 30.5567],
            '04' => ['lat' => 39.7191, 'lng' => 43.0503],
            '05' => ['lat' => 40.6499, 'lng' => 35.8353],
            '06' => ['lat' => 39.9334, 'lng' => 32.8597],
            '07' => ['lat' => 36.8969, 'lng' => 30.7133],
            '08' => ['lat' => 41.1828, 'lng' => 41.8183],
            '09' => ['lat' => 37.8444, 'lng' => 27.8458],
            '10' => ['lat' => 39.6484, 'lng' => 27.8826],
            '11' => ['lat' => 40.1419, 'lng' => 29.9793],
            '12' => ['lat' => 38.8847, 'lng' => 40.4939],
            '13' => ['lat' => 38.4006, 'lng' => 42.1095],
            '14' => ['lat' => 40.7350, 'lng' => 31.6061],
            '15' => ['lat' => 37.7203, 'lng' => 30.2908],
            '16' => ['lat' => 40.1826, 'lng' => 29.0660],
            '17' => ['lat' => 40.1467, 'lng' => 26.4086],
            '18' => ['lat' => 40.6013, 'lng' => 33.6134],
            '19' => ['lat' => 40.5506, 'lng' => 34.9556],
            '20' => ['lat' => 37.7765, 'lng' => 29.0864],
            '21' => ['lat' => 37.9144, 'lng' => 40.2110],
            '22' => ['lat' => 41.6818, 'lng' => 26.5623],
            '23' => ['lat' => 38.6810, 'lng' => 39.2264],
            '24' => ['lat' => 39.7500, 'lng' => 39.5000],
            '25' => ['lat' => 39.9000, 'lng' => 41.2700],
            '26' => ['lat' => 39.7767, 'lng' => 30.5206],
            '27' => ['lat' => 37.0662, 'lng' => 37.3833],
            '28' => ['lat' => 40.9128, 'lng' => 38.3895],
            '29' => ['lat' => 40.4608, 'lng' => 39.4814],
            '30' => ['lat' => 37.5744, 'lng' => 43.7408],
            '31' => ['lat' => 36.2000, 'lng' => 36.1667],
            '32' => ['lat' => 37.7644, 'lng' => 30.5522],
            '33' => ['lat' => 36.8121, 'lng' => 34.6415],
            '34' => ['lat' => 41.0082, 'lng' => 28.9784],
            '35' => ['lat' => 38.4192, 'lng' => 27.1287],
            '36' => ['lat' => 40.6167, 'lng' => 43.1000],
            '37' => ['lat' => 41.3887, 'lng' => 33.7827],
            '38' => ['lat' => 38.7312, 'lng' => 35.4787],
            '39' => ['lat' => 41.7333, 'lng' => 27.2167],
            '40' => ['lat' => 39.1458, 'lng' => 34.1639],
            '41' => ['lat' => 40.8533, 'lng' => 29.8815],
            '42' => ['lat' => 37.8714, 'lng' => 32.4846],
            '43' => ['lat' => 39.4167, 'lng' => 29.9833],
            '44' => ['lat' => 38.3552, 'lng' => 38.3095],
            '45' => ['lat' => 38.6191, 'lng' => 27.4289],
            '46' => ['lat' => 37.5858, 'lng' => 36.9371],
            '47' => ['lat' => 37.3129, 'lng' => 40.7339],
            '48' => ['lat' => 37.2153, 'lng' => 28.3636],
            '49' => ['lat' => 38.7317, 'lng' => 41.4911],
            '50' => ['lat' => 38.6244, 'lng' => 34.7144],
            '51' => ['lat' => 37.9667, 'lng' => 34.6833],
            '52' => ['lat' => 40.9839, 'lng' => 37.8764],
            '53' => ['lat' => 41.0201, 'lng' => 40.5234],
            '54' => ['lat' => 40.7569, 'lng' => 30.3783],
            '55' => ['lat' => 41.2928, 'lng' => 36.3313],
            '56' => ['lat' => 37.9333, 'lng' => 41.9500],
            '57' => ['lat' => 42.0231, 'lng' => 35.1531],
            '58' => ['lat' => 39.7477, 'lng' => 37.0179],
            '59' => ['lat' => 40.9833, 'lng' => 27.5167],
            '60' => ['lat' => 40.3167, 'lng' => 36.5500],
            '61' => ['lat' => 41.0027, 'lng' => 39.7168],
            '62' => ['lat' => 39.1079, 'lng' => 39.5401],
            '63' => ['lat' => 37.1591, 'lng' => 38.7969],
            '64' => ['lat' => 38.6823, 'lng' => 29.4082],
            '65' => ['lat' => 38.4891, 'lng' => 43.4089],
            '66' => ['lat' => 39.8181, 'lng' => 34.8147],
            '67' => ['lat' => 41.4511, 'lng' => 31.7944],
            '68' => ['lat' => 38.3687, 'lng' => 34.0327],
            '69' => ['lat' => 40.2552, 'lng' => 40.2249],
            '70' => ['lat' => 37.1759, 'lng' => 33.2214],
            '71' => ['lat' => 39.8468, 'lng' => 33.5153],
            '72' => ['lat' => 37.8812, 'lng' => 41.1351],
            '73' => ['lat' => 37.5164, 'lng' => 42.4611],
            '74' => ['lat' => 41.6344, 'lng' => 32.3375],
            '75' => ['lat' => 41.1105, 'lng' => 42.7022],
            '76' => ['lat' => 39.9237, 'lng' => 44.0450],
            '77' => ['lat' => 40.6551, 'lng' => 29.2769],
            '78' => ['lat' => 41.2061, 'lng' => 32.6204],
            '79' => ['lat' => 36.7184, 'lng' => 37.1212],
            '80' => ['lat' => 37.0742, 'lng' => 36.2473],
            '81' => ['lat' => 40.8438, 'lng' => 31.1565],
        ];
    }
}
