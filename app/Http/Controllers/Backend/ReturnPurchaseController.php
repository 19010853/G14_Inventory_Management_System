<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnPurchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ReturnPurchaseItem;
use App\Models\User;
use App\Notifications\NewReturnPurchaseNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;


class ReturnPurchaseController extends Controller
{
    //
    // All Return Purchase Methods
    public function AllReturnPurchase(){
        $user = auth()->user();
        if (!$user->hasPermissionTo('all.return.purchase') && !$user->hasPermissionTo('return.purchase.menu')) {
            abort(403, 'Unauthorized Action');
        }
        $allData = ReturnPurchase::with('warehouse', 'supplier')->orderBy('id', 'desc')->get();
        return view('admin.backend.return-purchase.all_return_purchase',compact('allData'));
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

            // Store Return Purchase Items then update stock (only if status = Return)
            foreach($request->products as $productData){
                $product = Product::findOrFail($productData['id']);
                $netUnitCost = $productData['net_unit_cost'] ?? $product->price;

                if ($netUnitCost === null) {
                    throw new \Exception("Net Unit cost is missing ofr the product id" . $productData['id']);
                }

                $subtotal = ($netUnitCost * $productData['quantity']) - ($productData['discount'] ?? 0);
                $grandTotal += $subtotal;

                // Calculate stock: if status is Return, subtract quantity; otherwise use current stock
                $stockAfter = $request->status === 'Return' 
                    ? $product->product_qty - $productData['quantity'] 
                    : $product->product_qty;

                ReturnPurchaseItem::create([
                    'return_purchase_id' => $purchase->id,
                    'product_id' => $productData['id'],
                    'net_unit_cost' => $netUnitCost,
                    'stock' => $stockAfter,
                    'quantity' => $productData['quantity'],
                    'discount' => $productData['discount'] ?? 0,
                    'subtotal' => $subtotal,
                ]);

                // Only update product quantity if status is Return
                if ($request->status === 'Return') {
                    $product->decrement('product_qty', $productData['quantity']);
                }
            }

            $purchase->update(['grand_total' => $grandTotal + $request->shipping - $request->discount]);

            DB::commit();

            // Notify admins about new purchase return
            $admin = User::role('Super Admin')->get();
            Notification::send($admin, new NewReturnPurchaseNotification($purchase));

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
        try {
            $purchase = ReturnPurchase::with(['supplier', 'warehouse', 'purchaseItems.product'])->find($id);
            
            if (!$purchase) {
                abort(404, 'Return Purchase not found');
            }
            
            return view('admin.backend.return-purchase.return_purchase_details', compact('purchase'));
        } catch (\Exception $e) {
            \Log::error('Return Purchase details error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            abort(500, 'Failed to load return purchase details: ' . $e->getMessage());
        }
    }
    // End Method

    // Generate PDF Invoice Methods
    public function InvoiceReturnPurchase($id) {
        try {
            $purchase = ReturnPurchase::with(['supplier', 'warehouse', 'purchaseItems.product'])->find($id);

            if (!$purchase) {
                abort(404, 'Return Purchase not found');
            }

            // Check if required relationships exist
            if (!$purchase->supplier) {
                \Log::error('Return Purchase invoice error: Supplier not found for return purchase ID: ' . $id);
                abort(500, 'Supplier information is missing');
            }

            if (!$purchase->warehouse) {
                \Log::error('Return Purchase invoice error: Warehouse not found for return purchase ID: ' . $id);
                abort(500, 'Warehouse information is missing');
            }

            $pdf = Pdf::loadView('admin.backend.return-purchase.invoice_purchase', compact('purchase'));
            return $pdf->download('return_purchase_'.$id.'.pdf');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Return Purchase invoice error: Model not found - ' . $e->getMessage());
            abort(404, 'Return Purchase not found');
        } catch (\Exception $e) {
            \Log::error('Return Purchase invoice generation error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            abort(500, 'Failed to generate invoice: ' . $e->getMessage());
        }
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
            $oldStatus = $purchase->status;

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

            // If old status was Return, revert the quantity changes
            if ($oldStatus === 'Return') {
                foreach($oldPurchaseItems as $oldItems) {
                    $product = Product::find($oldItems->product_id);
                    if ($product) {
                        $product->increment('product_qty', $oldItems->quantity);
                    }
                }
            }

            // Delete old purchase items
            ReturnPurchaseItem::where('return_purchase_id', $purchase->id)->delete();

            // Loop through new purchase items
            foreach($request->products as $product_id => $productData){
                $product = Product::find($product_id);
                if (!$product) {
                    continue;
                }

                // Calculate stock: if new status is Return, subtract quantity; otherwise use current stock
                $stockAfter = $request->status === 'Return' 
                    ? $product->product_qty - $productData['quantity'] 
                    : $product->product_qty;

                ReturnPurchaseItem::create([
                    'return_purchase_id' => $purchase->id,
                    'product_id' => $product_id,
                    'net_unit_cost' => $productData['net_unit_cost'],
                    'stock' => $stockAfter,
                    'quantity' => $productData['quantity'],
                    'discount' => $productData['discount'] ?? 0,
                    'subtotal' => $productData['subtotal'],
                ]);

                // Only update product quantity if new status is Return
                if ($request->status === 'Return') {
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

            // Only revert quantity if status was Return
            if ($purchase->status === 'Return') {
                foreach($purchaseItems as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('product_qty', $item->quantity);
                    }
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
