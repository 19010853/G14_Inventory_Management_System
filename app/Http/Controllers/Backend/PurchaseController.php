<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

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

    // Edit Purchase Methods
    public function EditPurchase($id){
        $editData = Purchase::with('purchaseItems.product')->findOrFail($id);
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        return view('admin.backend.purchase.edit_purchase',compact('editData','suppliers','warehouses'));
    }
    // End Methods

    // Update Purchase Methods
    public function UpdatePurchase(Request $request, $id){

        $request->validate([
            'date' => 'required|date',
            'status' => 'required',
        ]);

        DB::beginTransaction();

        try {
            
            $purchase = Purchase::findOrFail($id);

            $purchase->update([
                'date' => $request->date,
                'warehouse_id' => $request->warehouse_id,
                'supplier_id' => $request->supplier_id,
                'discount' => $request->discount ?? 0,
                'shipping' => $request->shipping ?? 0,
                'status' => $request->status,
                'note' => $request->note,
                'grand_total' => $request->grand_total, 
            ]);

            // Get Old Purchase Items
            $oldPurchaseItems = PurchaseItem::where('purchase_id', $purchase->id)->get();

            // Loop through old purchase items and decrement product stock
            foreach ($oldPurchaseItems as $oldItems) {
                $product = Product::find($oldItems->product_id);
                if ($product) {
                    $product->decrement('product_qty', $oldItems->quantity);
                    // Decrement old quantity from purchase items table
                }
            }

            // Delete old purchase items
            PurchaseItem::where('purchase_id', $purchase->id)->delete();

            // Loop through new purchase items and increment product stock
            foreach($request->products as $product_id => $productData){
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product_id,
                    'net_unit_cost' => $productData['net_unit_cost'],
                    'stock' => $productData['stock'],
                    'quantity' => $productData['quantity'],
                    'discount' => $productData['discount'] ?? 0,
                    'subtotal' => $productData['subtotal'],  
                ]);
        
                /// Update product stock by incremeting new quantity 
                $product = Product::find($product_id);
                if ($product) {
                    $product->increment('product_qty',$productData['quantity']);
                    // Increment new quantity
                 } 
               }

               DB::commit();

               $notification = array(
                'message' => 'Purchase Updated Successfully',
                'alert-type' => 'success'
             ); 
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
          }   
    }
    // End Methods

    // Show Purchase Details Methods
    public function DetailsPurchase($id){
        $purchase = Purchase::with(['supplier', 'purchaseItems.product'])->find($id);
        return view('admin.backend.purchase.purchase_details',compact('purchase'));
    }
    // End Methods

    // Generate PDF Invoice Methods
    public function InvoicePurchase($id){
        $purchase = Purchase::with(['supplier', 'warehouse', 'purchaseItems.product'])->find($id);

        $pdf = Pdf::loadView('admin.backend.purchase.invoice_pdf',compact('purchase'));
        return $pdf->download('purchase_'.$id.'.pdf');
    }
    // End Methods
}
