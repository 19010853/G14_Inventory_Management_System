<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalSales = Sale::whereDate('created_at', Carbon::today())->sum('grand_total');
        $totalPurchases = Purchase::whereDate('created_at', Carbon::today())->sum('grand_total');
        $totalUsers = User::count();

        $salesData = Sale::select(
            \DB::raw('DATE(created_at) as date'),
            \DB::raw('sum(grand_total) as total_sales')
        )->groupByRaw('DATE(created_at)')->get();

        $bestSellingProducts = \DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select('products.name', \DB::raw('SUM(sale_items.quantity) as total_quantity'))
            ->groupBy('products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();


        return view('admin.index', compact('totalProducts', 'totalSales', 'totalPurchases', 'totalUsers', 'salesData', 'bestSellingProducts'));
    }
}
