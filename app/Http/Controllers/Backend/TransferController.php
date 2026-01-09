<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Customer;
use App\Models\Warehouse;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\User;
use App\Notifications\NewTransferNotification;
use Illuminate\Support\Facades\Notification;

class TransferController extends Controller
{
    // Show All Transfer Method
    public function AllTransfer(){
        if (!auth()->user()->hasPermissionTo('all.transfers')) {
            abort(403, 'Unauthorized Action');
        }
        try {
            $allData = Transfer::with(['fromWarehouse', 'toWarehouse', 'transferItems.product'])->orderBy('id','desc')->get();
            return view('admin.backend.transfer.all_transfer', compact('allData'));
        } catch (\Exception $e) {
            \Log::error('All Transfer error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->withErrors(['error' => 'Failed to load transfers: ' . $e->getMessage()]);
        }
    }
    // End Method

    // Add New Transfer Method
    public function AddTransfer(){
        try {
            $warehouses = Warehouse::all();
            return view('admin.backend.transfer.add_transfer', compact('warehouses'));
        } catch (\Exception $e) {
            \Log::error('Add Transfer error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->withErrors(['error' => 'Failed to load transfer form: ' . $e->getMessage()]);
        }
    }
    // End Method

    // Store Transfer Method
    public function StoreTransfer(Request $request){
        try {
            $request->validate([
                'date' => 'required|date',
                'status' => 'required|in:Transfer,Pending,Ordered',
                'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
                'to_warehouse_id' => 'required|exists:warehouses,id',
                'products' => 'required|array|min:1',
                'products.*.id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|numeric|min:1',
                'products.*.discount' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'shipping' => 'nullable|numeric|min:0',
                'note' => 'nullable|string|max:1000',
            ], [
                'date.required' => 'The date field is required.',
                'date.date' => 'The date must be a valid date.',
                'status.required' => 'The status field is required.',
                'status.in' => 'The status must be one of: Transfer, Pending, or Ordered.',
                'from_warehouse_id.required' => 'Please select the source warehouse.',
                'from_warehouse_id.exists' => 'The selected source warehouse is invalid.',
                'from_warehouse_id.different' => 'From Warehouse and To Warehouse must be different.',
                'to_warehouse_id.required' => 'Please select the destination warehouse.',
                'to_warehouse_id.exists' => 'The selected destination warehouse is invalid.',
                'products.required' => 'Please add at least one product to transfer.',
                'products.min' => 'Please add at least one product to transfer.',
                'products.*.id.required' => 'Product ID is required.',
                'products.*.id.exists' => 'One or more selected products are invalid.',
                'products.*.quantity.required' => 'Product quantity is required.',
                'products.*.quantity.numeric' => 'Product quantity must be a number.',
                'products.*.quantity.min' => 'Product quantity must be at least 1.',
                'products.*.discount.numeric' => 'Product discount must be a number.',
                'discount.numeric' => 'Discount must be a number.',
                'shipping.numeric' => 'Shipping must be a number.',
            ]);

        DB::beginTransaction();

        $transfer = Transfer::create([
            'date' => $request->date,
            'from_warehouse_id' => $request->from_warehouse_id,
            'to_warehouse_id' => $request->to_warehouse_id,
            'discount' => $request->discount ?? 0,
            'shipping' => $request->shipping ?? 0,
            'status' => $request->status,
            'note' => $request->note,
            'grand_total' => 0,

        ]);

        /// Store Transfer Items & Update Stock (only if status = Transfer)
    foreach($request->products as $productData){
        $product = Product::with('images')->findOrFail($productData['id']);
        $netUnitCost = $product->price;
        $quantity = $productData['quantity'];
        $discount = $productData['discount'] ?? 0;
        $subtotal = ($netUnitCost * $quantity) - $discount;

        TransferItem::create([
            'transfer_id' => $transfer->id,
            'product_id' => $productData['id'],
            'net_unit_cost' => $netUnitCost,
            'stock' => $product->product_qty,
            'quantity' => $quantity,
            'discount' => $discount,
            'subtotal' => $subtotal,
        ]);

        // Only update product quantity if status is Transfer
        if ($request->status === 'Transfer') {
            /// Decrement stock form 'from_warehouse'
            Product::where('id',$productData['id'])
                ->where('warehouse_id', $request->from_warehouse_id)
                ->decrement('product_qty',$quantity);

            // Check if the product exists in to_warehouse
            $existingProduct = Product::with('images')->where('name',$product->name)
                ->where('brand_id', $product->brand_id)
                ->where('warehouse_id', $request->to_warehouse_id)
                ->first();

            if ($existingProduct) {
                $existingProduct->increment('product_qty',$quantity);
                
                // Copy images from source product if destination product has no images
                if ($existingProduct->images->isEmpty() && $product->images->isNotEmpty()) {
                    foreach ($product->images as $sourceImage) {
                        ProductImage::create([
                            'product_id' => $existingProduct->id,
                            'image' => $sourceImage->image,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            } else {
                // if not exists then create new product with all details from source product
                $newProduct = Product::create([
                    'name' => $product->name,
                    'code' => $product->code ?? null,
                    'category_id' => $product->category_id ?? null,
                    'brand_id' => $product->brand_id,
                    'supplier_id' => $product->supplier_id ?? null,
                    'warehouse_id' => $request->to_warehouse_id,
                    'price' => $product->price,
                    'stock_alert' => $product->stock_alert ?? 0,
                    'product_qty' => $quantity,
                    'status' => $product->status ?? 1,
                    'note' => $product->note ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Copy all images from source product to new product
                if ($product->images->isNotEmpty()) {
                    foreach ($product->images as $sourceImage) {
                        ProductImage::create([
                            'product_id' => $newProduct->id,
                            'image' => $sourceImage->image,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

    }

    DB::commit();

    // Notify admins about new transfer
    $admin = User::role('Super Admin')->get();
    Notification::send($admin, new NewTransferNotification($transfer));

    $notification = array(
        'message' => 'Transfer Complete Successfully',
        'alert-type' => 'success'
     );
     return redirect()->route('all.transfer')->with($notification);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return redirect()->back()->withInput()->withErrors($e->errors());
      } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Store Transfer error: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return redirect()->back()->withInput()->withErrors(['error' => 'Failed to store transfer: ' . $e->getMessage()]);
      }
    }
    // End Method

    // Edit Transfer Method
    public function EditTransfer($id){
        $editData = Transfer::with(['fromWarehouse','toWarehouse','transferItems.product'])->findOrFail($id);
        $warehouses = Warehouse::all();
        return view('admin.backend.transfer.edit_transfer',compact('warehouses','editData'));

    }
    // End Method

    // Update Transfer Method
    public function UpdateTransfer(Request $request, $id){

        try {

         DB::beginTransaction();

         $transfer = Transfer::findOrFail($id);
         $oldStatus = $transfer->status;

         // Restore previous stock only if old status was Transfer
         if ($oldStatus === 'Transfer') {
             $oldTransferItems = TransferItem::where('transfer_id', $transfer->id)->get();

             foreach($oldTransferItems as $oldItem){
                Product::where('id',$oldItem->product_id)
                    ->where('warehouse_id',$transfer->from_warehouse_id)
                    ->increment('product_qty',$oldItem->quantity);

                Product::where('id',$oldItem->product_id)
                    ->where('warehouse_id',$transfer->to_warehouse_id)
                    ->decrement('product_qty',$oldItem->quantity);
             }
         }

         // Delete old transfer items to prevent duplicate entries
         TransferItem::where('transfer_id',$transfer->id)->delete();

         // update the transfer record
         $transfer->update([
            'date' => $request->date,
            'discount' => $request->discount ?? 0,
            'shipping' => $request->shipping ?? 0,
            'status' => $request->status,
            'note' => $request->note,
            'grand_total' => $request->grand_total,
         ]);

         /// add new transfer items
         foreach($request->products as $productId => $productData){
            $product = Product::find($productId);
            if (!$product) {
                throw new \Exception("Product id not found");
            }
            
            // Create new Transfer item in transfer item table
            $transferItem = TransferItem::create([
                'transfer_id' => $transfer->id,
                'product_id' => $productId,
                'net_unit_cost' => $product->price ?? 0,
                'stock' => $product->product_qty,
                'quantity' => $productData['quantity'],
                'discount' => $productData['discount'] ?? 0,
                'subtotal' => $productData['subtotal'] ?? 0,
            ]);

            // Only update product quantity if new status is Transfer
            if ($request->status === 'Transfer') {
                Product::where('id',$productId)
                    ->where('warehouse_id',$transfer->from_warehouse_id)
                    ->decrement('product_qty',$productData['quantity']);
                /// Sending warehouse quantity

                Product::where('warehouse_id',$transfer->to_warehouse_id)
                    ->increment('product_qty',$productData['quantity']);
                /// receiving warehouse quantity
            }
          }

          DB::commit();

            $notification = array(
                'message' => 'Transfer Updated Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('all.transfer')->with($notification);
         }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
          }

    }
     // End Method

     // Delete Transfer Method
     public function DeleteTransfer($id){

        try {
          DB::beginTransaction();
          $transfer = Transfer::findOrFail($id);
          $transferItems = TransferItem::where('transfer_id',$transfer->id)->get();

          // Only revert quantity if status was Transfer
          if ($transfer->status === 'Transfer') {
              foreach($transferItems as $item){
                Product::where('id',$item->product_id)
                    ->where('warehouse_id',$transfer->from_warehouse_id)
                    ->increment('product_qty',$item->quantity);
                /// Sending warehouse quantity

                Product::where('warehouse_id',$transfer->to_warehouse_id)
                    ->decrement('product_qty',$item->quantity);
                /// receiving warehouse quantity
              }
          }
          TransferItem::where('transfer_id',$transfer->id)->delete();
          $transfer->delete();
          DB::commit();

          $notification = array(
            'message' => 'Transfer Deleted Successfully',
            'alert-type' => 'success'
         );
         return redirect()->route('all.transfer')->with($notification);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
          }
    }
    // End Method

    // Show Transfer Details Method
    public function DetailsTransfer($id){
        try {
            $transfer = Transfer::with(['fromWarehouse', 'toWarehouse', 'transferItems.product'])->findOrFail($id);
            
            if (!$transfer) {
                abort(404, 'Transfer not found');
            }
            
            return view('admin.backend.transfer.detail_transfer', compact('transfer'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Transfer details error: Model not found - ' . $e->getMessage());
            abort(404, 'Transfer not found');
        } catch (\Exception $e) {
            \Log::error('Transfer details error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            abort(500, 'Failed to load transfer details: ' . $e->getMessage());
        }
    }
    // End Method
}
