<?php

namespace App\Http\Controllers\Admin;

use App\Models\Visitor;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrafficController extends Controller
{
    public function index()
    {
        // Source data for pie chart
        $sourceData = Visitor::select('source', DB::raw('count(*) as count'))
            ->groupBy('source')
            ->get();

        // Device data for pie chart
        $deviceData = Visitor::select('device', DB::raw('count(*) as count'))
            ->groupBy('device')
            ->get();

        // Platform data for pie chart
        $platformData = Visitor::select('platform', DB::raw('count(*) as count'))
            ->groupBy('platform')
            ->get();

        // Traffic over time (last 15 days)
        $dailyTraffic = Visitor::select(DB::raw('DATE(visited_at) as date'), DB::raw('count(*) as count'))
            ->where('visited_at', '>=', now()->subDays(15))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topTargets = Visitor::select('target', 'target_type', DB::raw('count(*) as count'))
            ->groupBy('target', 'target_type')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get();

        // Map targets to names/titles
        $topTargets = $topTargets->map(function ($item) {
            $item->title = 'Bilinmeyen';
            $item->url = '#';

            if ($item->target_type === 'home') {
                $item->title = 'Anasayfa';
                $item->url = route('home');
            } elseif ($item->target_type === 'product') {
                $product = Product::find($item->target);
                if ($product) {
                    $item->title = $product->title;
                    $item->url = route('products.show', [$product->id, $product->slug]);
                }
            } elseif ($item->target_type === 'category') {
                $category = Category::where('slug', $item->target)->orWhere('id', $item->target)->first();
                if ($category) {
                    $item->title = $category->name;
                    $item->url = route('categories.show', [$category->id, $category->slug]);
                }
            }
            return $item;
        });

        $topCities = DB::table('user_addresses')
            ->select('city', DB::raw('count(*) as count'))
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        $topRepeatProducts = Visitor::where('target_type', 'product')
            ->select('target', DB::raw('count(*) as count'))
            ->groupBy('target')
            ->orderByDesc('count')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $product = Product::find($item->target);
                return [
                    'title' => $product ? $product->title : 'Bilinmeyen Ürün',
                    'count' => $item->count,
                    'url' => $product ? route('products.show', [$product->id, $product->slug]) : '#'
                ];
            });

        $conversionTargets = Visitor::whereIn('source', ['sms', 'email'])
            ->where('target_type', 'product')
            ->select('target', 'source', DB::raw('count(*) as count'))
            ->groupBy('target', 'source')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                $product = Product::find($item->target);
                return [
                    'title' => $product ? $product->title : 'Bilinmeyen Ürün',
                    'source' => $item->source,
                    'count' => $item->count,
                    'url' => $product ? route('products.show', [$product->id, $product->slug]) : '#'
                ];
            });

        return view('admin.pages.traffic.index', compact(
            'sourceData',
            'deviceData',
            'platformData',
            'dailyTraffic',
            'topTargets',
            'topCities',
            'topRepeatProducts',
            'conversionTargets'
        ));
    }
}
