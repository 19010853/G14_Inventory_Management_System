<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalSales = Sale::whereDate('created_at', Carbon::today())->sum('grand_total');
        $totalPurchases = Purchase::whereDate('created_at', Carbon::today())->sum('grand_total');
        $totalUsers = User::count();

        // Daily Sales data for main chart
        $salesData = Sale::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('sum(grand_total) as total_sales')
        )->groupByRaw('DATE(created_at)')->get();

        // Best Selling Products
        $bestSellingProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select('products.name', \DB::raw('SUM(sale_items.quantity) as total_quantity'))
            ->groupBy('products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        // Mini Charts Data - Last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        // Generate array of last 30 days
        $dates = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        // Products created per day (last 30 days)
        $productsData = Product::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', $startDate)
        ->groupByRaw('DATE(created_at)')
        ->pluck('count', 'date')
        ->toArray();

        // Sales per day (last 30 days)
        $salesPerDay = Sale::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(grand_total) as total')
        )
        ->where('created_at', '>=', $startDate)
        ->groupByRaw('DATE(created_at)')
        ->pluck('total', 'date')
        ->toArray();

        // Purchases per day (last 30 days)
        $purchasesPerDay = Purchase::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(grand_total) as total')
        )
        ->where('created_at', '>=', $startDate)
        ->groupByRaw('DATE(created_at)')
        ->pluck('total', 'date')
        ->toArray();

        // Users created per day (last 30 days)
        $usersData = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', $startDate)
        ->groupByRaw('DATE(created_at)')
        ->pluck('count', 'date')
        ->toArray();

        // Fill in missing dates with 0
        $productsChartData = array_map(function($date) use ($productsData) {
            return isset($productsData[$date]) ? (int)$productsData[$date] : 0;
        }, $dates);

        $salesChartData = array_map(function($date) use ($salesPerDay) {
            return isset($salesPerDay[$date]) ? (float)$salesPerDay[$date] : 0;
        }, $dates);

        $purchasesChartData = array_map(function($date) use ($purchasesPerDay) {
            return isset($purchasesPerDay[$date]) ? (float)$purchasesPerDay[$date] : 0;
        }, $dates);

        $usersChartData = array_map(function($date) use ($usersData) {
            return isset($usersData[$date]) ? (int)$usersData[$date] : 0;
        }, $dates);

        return view('admin.index', compact(
            'totalProducts', 
            'totalSales', 
            'totalPurchases', 
            'totalUsers', 
            'salesData', 
            'bestSellingProducts',
            'dates',
            'productsChartData',
            'salesChartData',
            'purchasesChartData',
            'usersChartData'
        ));
    }
}
