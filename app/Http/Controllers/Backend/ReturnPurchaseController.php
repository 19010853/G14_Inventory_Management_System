<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnPurchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ReturnPurchaseItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;


class ReturnPurchaseController extends Controller
{
    //
    // All Return Purchase Methods
    public function AllReturnPurchase(){
        $allData = ReturnPurchase::orderBy('id', 'desc')->get();
        return view('admin.backend.return_purchase.all_return_purchase',compact('allData'));
    }
    // End Methods

    // Add Return Purchase Methods
    public function AddReturnPurchase(){
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        return view('admin.backend.return-purchase.add_return_purchase',compact('suppliers','warehouses'));
    }
    // End Methods

    // Store Return Purchase Methods
    public function StoreReturnPurchase(Request $request){

        $request->validate([
            'date' => 'required|date',
            'status' => 'required',
            'supplier_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $grandTotal = 0;

            $purchase = ReturnPurchase::create([
                'date' => $request->date,
                'warehouse_id' => $request->warehouse_id,
                'supplier_id' => $request->supplier_id,
                'discount' => $request->discount ?? 0,
                'shipping' => $request->shipping ?? 0,
                'status' => $request->status,
                'note' => $request->note,
                'grand_total' => 0,
            ]);

            // Store Return Purchase Items then update stock
            foreach($request->products as $productData){
                $product = Product::findOrFail($productData['id']);
                $netUnitCost = $productData['net_unit_cost'] ?? $product->price;

                if ($netUnitCost === null) {
                    throw new \Exception("Net Unit cost is missing ofr the product id" . $productData['id']);
                }

                $subtotal = ($netUnitCost * $productData['quantity']) - ($productData['discount'] ?? 0);
                $grandTotal += $subtotal;

                ReturnPurchaseItem::create([
                    'return_purchase_id' => $purchase->id,
                    'product_id' => $productData['id'],
                    'net_unit_cost' => $netUnitCost,
                    'stock' => $product->product_qty + $productData['quantity'],
                    'quantity' => $productData['quantity'],
                    'discount' => $productData['discount'] ?? 0,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('product_qty', $productData['quantity']);
            }

            $purchase->update(['grand_total' => $grandTotal + $request->shipping - $request->discount]);

            DB::commit();

            $notification = array(
                'message' => 'Return Purchase created successfully',
                'alert-type' => 'success',
            );

            return redirect()->route('all.return.purchase')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
          }
    }
    // End Method

    // Show Details Return Purchase Methods
    public function DetailsReturnPurchase($id) {
        $purchase = ReturnPurchase::with(['supplier', 'purchaseItems.product'])->find($id);
        return view('admin.backend.return-purchase.return_purchase_details',compact('purchase'));
    }
    // End Method

    // Generate PDF Invoice Methods
    public function InvoiceReturnPurchase($id) {
        $purchase = ReturnPurchase::with(['supplier', 'purchaseItems.product'])->find($id);

        $pdf = Pdf::loadView('admin.backend.return-purchase.invoice_pdf',compact('purchase'));
        return $pdf->download('purchase_'.$id.'.pdf');
    }
    // End Method

    // Edit Return Purchase Methods
    public function EditReturnPurchase($id) {
        $editData = ReturnPurchase::with(['purchaseItems.product'])->findOrFail($id);
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        return view('admin.backend.return-purchase.edit_return_purchase',compact('editData','suppliers','warehouses'));
    }
    // End Method

    // Update Return Purchase Methods
    public function UpdateReturnPurchase(Request $request, $id) {
        $request->validate([
            'date' => 'required|date',
            'status' => 'required',
        ]);

        DB::beginTransaction();

        try {
            $purchase = ReturnPurchase::findOrFail($id);

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
            $oldPurchaseItems = ReturnPurchaseItem::where('return_purchase_id', $purchase->id)->get();

            // Loop through old purchase items and decrement product stock
            foreach($oldPurchaseItems as $oldItems) {
                $product = Product::find($oldItems->product_id);
                if ($product) {
                    $product->increment('product_qty', $oldItems->quantity);
                }
            }

            // Delete old purchase items
            ReturnPurchaseItem::where('return_purchase_id', $purchase->id)->delete();

            // Loop through new purchase items and insert new purchase items
            foreach($request->products as $product_id => $productData){
                ReturnPurchaseItem::create([
                    'return_purchase_id' => $purchase->id,
                    'product_id' => $product_id,
                    'net_unit_cost' => $productData['net_unit_cost'],
                    'stock' => $productData['stock'],
                    'quantity' => $productData['quantity'],
                    'discount' => $productData['discount'] ?? 0,
                    'subtotal' => $productData['subtotal'],
                ]);

                // Update product stock by incrementing new quantity
                $product = Product::find($product_id);
                if ($product) {
                    $product->decrement('product_qty', $productData['quantity']);
                }
            }

            DB::commit();

            $notification = array(
                'message' => 'Return Purchase Updated Successfully',
                'alert-type' => 'success'
             );
             return redirect()->route('all.return.purchase')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
          }
    }
    // End Method

    // Delete Return Purchase Methods
    public function DeleteReturnPurchase($id) {
        try {
            DB::beginTransaction();

            $purchase = ReturnPurchase::findOrFail($id);
            $purchaseItems = ReturnPurchaseItem::where('return_purchase_id', $purchase->id)->get();

            foreach($purchaseItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('product_qty', $item->quantity);
                }
            }
            ReturnPurchaseItem::where('return_purchase_id', $id)->delete();
            $purchase->delete();
            DB::commit();

            $notification = array(
                'message' => 'Return Purchase Deleted Successfully',
                'alert-type' => 'success'
             );
             return redirect()->route('all.return.purchase')->with($notification);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()], 500);
              }
    }
    // End Method
}
