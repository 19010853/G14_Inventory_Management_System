<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
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

class TransferController extends Controller
{
    // Show All Transfer Method
    public function AllTransfer(){
        $allData = Transfer::with(['transferItems.product'])->orderBy('id','desc')->get();
        return view('admin.backend.transfer.all_transfer',compact('allData'));
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

    try {

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

        /// Store Sales Items & Update Stock
    foreach($request->products as $productData){
        $product = Product::findOrFail($productData['id']);
        $netUnitCost = $product->price;
        $quantity = $productData['quantity'];
        $discount = $productData['discount'];
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

        /// Decrement stock form 'from_warehouse'
        Product::where('id',$productData['id'])
            ->where('warehouse_id', $request->from_warehouse_id)
            ->decrement('product_qty',$quantity);

        // Check if the product exists in to_warehouse

        $existingProduct = Product::where('name',$product->name)
            ->where('brand_id', $product->brand_id)
            ->where('warehouse_id', $request->to_warehouse_id)
            ->first();

        if ($existingProduct) {
            $existingProduct->increment('product_qty',$quantity);
        } else {
            // if not exists then create new product without code
            Product::create([
                'name' => $product->name,
                'brand_id' => $product->brand_id,
                'warehouse_id' => $request->to_warehouse_id,
                'price' => $product->price,
                'product_qty' => $quantity,
                'status' => 1, // Assuing active status
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

    }

    DB::commit();

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

         // Restore previous stock
         $oldTransferItems = TransferItem::where('transfer_id', $transfer->id)->get();

         foreach($oldTransferItems as $oldItem){
            Product::where('id',$oldItem->product_id)
                ->where('warehouse_id',$transfer->from_warehouse_id)
                ->increment('product_qty',$oldItem->quantity);

            Product::where('id',$oldItem->product_id)
            ->where('warehouse_id',$transfer->to_warehouse_id)
            ->decrement('product_qty',$oldItem->quantity);

            /// Delete odl transfer items to prevent duplicate entries

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

            Product::where('id',$productId)
            ->where('warehouse_id',$transfer->from_warehouse_id)
            ->decrement('product_qty',$productData['quantity']);
            /// Sending warehouse  quantity

            Product::where('warehouse_id',$transfer->to_warehouse_id)
            ->increment('product_qty',$productData['quantity']);
                /// receiving warehouse  quantity
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

          foreach($transferItems as $item){
            Product::where('id',$item->product_id)
            ->where('warehouse_id',$transfer->from_warehouse_id)
            ->increment('product_qty',$item->quantity);
            /// Sending warehouse  quantity

            Product::where('warehouse_id',$transfer->to_warehouse_id)
            ->decrement('product_qty',$item->quantity);
                /// receiving warehouse  quantity

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
    $transfer = Transfer::with(['transferItems.product'])->findOrFail($id);
    $product = Product::find($transfer->product_id);
    $fromWarehouse = Warehouse::find($transfer->from_warehouse_id);
    $toWarehouse = Warehouse::find($transfer->to_warehouse_id);
    return view('admin.backend.transfer.details_transfer',compact('transfer','product','fromWarehouse','toWarehouse'));

   }
    // End Method
}
