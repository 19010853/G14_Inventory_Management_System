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

    // Store Purchase Methods
    public function StorePurchase(Request $request){

        $request->validate([
            'date' => 'required|date',
            'status' => 'required',
            'supplier_id' => 'required',
        ]);

        try {

            DB::beginTransaction();

            // Purchase Create
            $purchase = Purchase::create([
                'date' => $request->date,
                'warehouse_id' => $request->warehouse_id,
                'supplier_id' => $request->supplier_id,
                'discount' => $request->discount,
                'shipping' => $request->shipping,
                'status' => $request->status,
                'note' => $request->note,
                'grand_total' => 0,
            ]);

            // Store Purchase Items and Update Product Stock
            foreach ($request->products as $productData){
                $product = Product::findOrFail($productData['id']);
                $netUnitCost = $productData['net_unit_cost'] ?? $product->price;

                if ($netUnitCost === null) {
                    throw new \Exception("Net Unit cost is missing ofr the product id" . $productData['id']);
                }

                $subtotal = ($netUnitCost * $productData['quantity']) - ($productData['discount'] ?? 0);
                $grandTotal += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $productData['id'],
                    'net_unit_cost' => $netUnitCost,
                    'stock' => $product->product_qty + $productData['quantity'],
                    'quantity' => $productData['quantity'],
                    'discount' => $productData['discount'] ?? 0,
                    'subtotal' => $subtotal, 
                ]);
                $product->increment('product_qty', $productData['quantity']);
            }

            $purchase->update(['grand_total' => $grandTotal + $request->shipping - $request->discount]);

            DB::commit();

            $notification = array(
                'message' => 'Purchase created successfully',
                'alert-type' => 'success',
            );

            return redirect()->route('all.purchase')->with($notification);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // End Methods
}
