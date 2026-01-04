<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

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
        try {
            $query = $request->input('query');
            $warehouse_id = $request->input('warehouse_id');

            if (empty($query) || strlen($query) < 2) {
                return response()->json([]);
            }

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

            // Return products with product_qty
            $products = $products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'price' => $product->price,
                    'product_qty' => $product->product_qty ?? 0,
                ];
            });

            return response()->json($products);
        } catch (\Exception $e) {
            \Log::error('PurchaseProductSearch error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search products'], 500);
        }
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

            $grandTotal = 0;

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
        try {
            $editData = Purchase::with(['supplier', 'warehouse', 'purchaseItems.product'])->findOrFail($id);
            $suppliers = Supplier::all();
            $warehouses = Warehouse::all();
            return view('admin.backend.purchase.edit_purchase', compact('editData', 'suppliers', 'warehouses'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Purchase not found');
        } catch (\Exception $e) {
            \Log::error('Edit purchase error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            abort(500, 'Failed to load purchase for editing: ' . $e->getMessage());
        }
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
        try {
            $purchase = Purchase::with(['supplier', 'warehouse', 'purchaseItems.product'])->find($id);
            
            if (!$purchase) {
                abort(404, 'Purchase not found');
            }
            
            return view('admin.backend.purchase.purchase_details', compact('purchase'));
        } catch (\Exception $e) {
            \Log::error('Purchase details error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            abort(500, 'Failed to load purchase details: ' . $e->getMessage());
        }
    }
    // End Methods

    // Generate PDF Invoice Methods
    public function InvoicePurchase($id){
        try {
            $purchase = Purchase::with(['supplier', 'warehouse', 'purchaseItems.product'])->find($id);

            if (!$purchase) {
                abort(404, 'Purchase not found');
            }

            // Check if required relationships exist
            if (!$purchase->supplier) {
                \Log::error('Purchase invoice error: Supplier not found for purchase ID: ' . $id);
                abort(500, 'Supplier information is missing');
            }

            if (!$purchase->warehouse) {
                \Log::error('Purchase invoice error: Warehouse not found for purchase ID: ' . $id);
                abort(500, 'Warehouse information is missing');
            }

            $pdf = Pdf::loadView('admin.backend.purchase.invoice_pdf', compact('purchase'));
            return $pdf->download('purchase_'.$id.'.pdf');
        } catch (\Exception $e) {
            \Log::error('Purchase invoice generation error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            abort(500, 'Failed to generate invoice: ' . $e->getMessage());
        }
    }
    // End Methods

    // Delete Purchase Methods
    public function DeletePurchase($id){
        try {
            DB::beginTransaction();

            $purchase = Purchase::findOrFail($id);
            $purchaseItems = PurchaseItem::where('purchase_id', $id)->get();

            foreach ($purchaseItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->decrement('product_qty', $item->quantity);
                }
            }
            PurchaseItem::where('purchase_id', $id)->delete();
            $purchase->delete();
            DB::commit();

            $notification = array(
                'message' => 'Purchase Deleted Successfully',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
          }
    }
}
