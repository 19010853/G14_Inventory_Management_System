<?php

namespace App\AI;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Transfer;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\ProductCategory;

class AIDataResolver
{
    /**
     * Resolve context data for intent - ONLY query what's needed
     */
    public static function resolve($user, string $intent): array
    {
        return match ($intent) {
            'product' => [
                'total_products' => Product::count(),
            ],
            'purchase' => [
                'total_purchases' => Purchase::count(),
                'today_purchases' => Purchase::whereDate('created_at', today())->count(),
            ],
            'sale' => [
                'total_sales' => Sale::count(),
                'today_sales' => Sale::whereDate('created_at', today())->count(),
            ],
            'transfer' => [
                'today_transfers' => Transfer::whereDate('created_at', today())->count(),
            ],
            'warehouse' => [
                'total_warehouses' => Warehouse::count(),
            ],
            'brand' => [
                'total_brands' => Brand::count(),
            ],
            'supplier' => [
                'total_suppliers' => Supplier::count(),
            ],
            'customer' => [
                'total_customers' => Customer::count(),
            ],
            'category' => [
                'total_categories' => ProductCategory::count(),
            ],
            default => [], // General intent - no data needed
        };
    }
}
