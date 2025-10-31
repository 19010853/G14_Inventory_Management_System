<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    //All Purchase Methods
    public function AllPurchase(){
        $allData = Purchase::orderBy('id', 'desc')->get();
        return view('admin.backend.purchase.all_purchase',compact('allData'));
    }

    //Add Purchase Methods
    public function AddPurchase(){
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        return view('admin.backend.purchase.add_purchase',compact('suppliers','warehouses'));
    }
    // End Methods

    // Purchase Product Search Methods
    public function PurchaseProductSearch(Request $request){
        $query = $request->input('query');
        $warehouse_id = $request->input('warehouse_id');

        $products = Product::where(function($q) use ($query){
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('code', 'LIKE', "%{$query}%");
        })
        ->when($warehouse_id, function($q) use ($warehouse_id) {
            $q->where('warehouse_id', $warehouse_id);
        })
        ->select('id', 'name', 'code', 'price', 'product_qty')
        ->limit(10)
        ->get();

        return response()->json($products);
    }
    // End Methods
}
