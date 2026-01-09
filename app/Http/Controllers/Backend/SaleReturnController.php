<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Warehouse;
use App\Models\User;
use App\Notifications\NewSaleReturnNotification;
use App\Notifications\NewSaleReturnDueNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleReturnController extends Controller
{
    // Show All Sale Returns
     public function AllSalesReturn(){
        $user = auth()->user();
        if (!$user->hasPermissionTo('all.return.sale') && !$user->hasPermissionTo('return.sale.menu')) {
            abort(403, 'Unauthorized Action');
        }
        $allData = SaleReturn::orderBy('id','desc')->get();
        return view('admin.backend.return-sale.all_return_sales',compact('allData'));
    }
    // End Method

    // Add Sale Return
    public function AddSalesReturn(){
        $customers = Customer::all();
        $warehouses = Warehouse::all();
        return view('admin.backend.return-sale.add_return_sales',compact('customers','warehouses'));
    }
     // End Method

     // Store Sale Return
    public function StoreSalesReturn(Request $request){
        try {
            $request->validate([
                'date' => 'required|date',
                'status' => 'required|in:Return,Pending,Ordered',
                'warehouse_id' => 'required|exists:warehouses,id',
                'customer_id' => 'required|exists:customers,id',
                'products' => 'required|array|min:1',
                'products.*.id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|numeric|min:1',
                'products.*.net_unit_cost' => 'nullable|numeric|min:0',
                'products.*.discount' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'shipping' => 'nullable|numeric|min:0',
                'paid_amount' => 'nullable|numeric|min:0',
                'due_amount' => 'nullable|numeric|min:0',
                'note' => 'nullable|string|max:1000',
            ], [
                'date.required' => 'The date field is required.',
                'date.date' => 'The date must be a valid date.',
                'status.required' => 'The status field is required.',
                'status.in' => 'The status must be one of: Return, Pending, or Ordered.',
                'warehouse_id.required' => 'Please select a warehouse.',
                'warehouse_id.exists' => 'The selected warehouse is invalid.',
                'customer_id.required' => 'Please select a customer.',
                'customer_id.exists' => 'The selected customer is invalid.',
                'products.required' => 'Please add at least one product to return.',
                'products.min' => 'Please add at least one product to return.',
                'products.*.id.required' => 'Product ID is required.',
                'products.*.id.exists' => 'One or more selected products are invalid.',
                'products.*.quantity.required' => 'Product quantity is required.',
                'products.*.quantity.numeric' => 'Product quantity must be a number.',
                'products.*.quantity.min' => 'Product quantity must be at least 1.',
                'products.*.net_unit_cost.numeric' => 'Net unit cost must be a number.',
                'products.*.discount.numeric' => 'Product discount must be a number.',
                'discount.numeric' => 'Discount must be a number.',
                'shipping.numeric' => 'Shipping must be a number.',
                'paid_amount.numeric' => 'Paid amount must be a number.',
                'due_amount.numeric' => 'Due amount must be a number.',
            ]);

            DB::beginTransaction();

            $grandTotal = 0;

            $sales = SaleReturn::create([
                'date' => $request->date,
                'warehouse_id' => $request->warehouse_id,
                'customer_id' => $request->customer_id,
                'discount' => $request->discount ?? 0,
                'shipping' => $request->shipping ?? 0,
                'status' => $request->status,
                'note' => $request->note ?? null,
                'grand_total' => 0,
                'paid_amount' => $request->paid_amount ?? 0,
                'due_amount' => $request->due_amount ?? 0,
            ]);

            /// Store Sales Items & Update Stock (only if status = Return)
            foreach($request->products as $productData){
                $product = Product::findOrFail($productData['id']);
                $netUnitCost = $productData['net_unit_cost'] ?? $product->price;

                if ($netUnitCost === null) {
                    throw new \Exception("Net Unit cost is missing for the product id " . $productData['id']);
                }

                $subtotal = ($netUnitCost * $productData['quantity']) - ($productData['discount'] ?? 0);
                $grandTotal += $subtotal;

                // Calculate stock: if status is Return, add quantity; otherwise use current stock
                $stockAfter = $request->status === 'Return' 
                    ? $product->product_qty + $productData['quantity'] 
                    : $product->product_qty;

                SaleReturnItem::create([
                    'sale_return_id' => $sales->id,
                    'product_id' => $productData['id'],
                    'net_unit_cost' => $netUnitCost,
                    'stock' => $stockAfter,
                    'quantity' => $productData['quantity'],
                    'discount' => $productData['discount'] ?? 0,
                    'subtotal' => $subtotal,
                ]);

                // Only update product quantity if status is Return
                if ($request->status === 'Return') {
                    $product->increment('product_qty', $productData['quantity']);
                }
            }

            $sales->update(['grand_total' => $grandTotal + ($request->shipping ?? 0) - ($request->discount ?? 0)]);

            DB::commit();

            // Notify admins about new sale return
            $admin = User::role('Super Admin')->get();
            Notification::send($admin, new NewSaleReturnNotification($sales));

            // Additional notification when there is due amount
            if ($sales->due_amount > 0) {
                Notification::send($admin, new NewSaleReturnDueNotification($sales));
            }

            $notification = array(
                'message' => 'Sales Return Stored Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('all.return.sale')->with($notification);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Store Sales Return error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to store sales return: ' . $e->getMessage()]);
        }
    }
    // End Method

    public function EditSalesReturn($id){
        $editData = SaleReturn::with('saleReturnItems.product')->findOrFail($id);
        $customers = Customer::all();
        $warehouses = WareHouse::all();
        return view('admin.backend.return-sale.edit_return_sales',compact('editData','customers','warehouses'));
    }
    // End Method

    public function UpdateSalesReturn(Request $request, $id){

        $request->validate([
            'date' => 'required|date',
            'status' => 'required',
        ]);

        $sales = SaleReturn::findOrFail($id);
        $oldStatus = $sales->status;

        $sales->update([
            'date' => $request->date,
            'warehouse_id' => $request->warehouse_id,
            'customer_id' => $request->customer_id,
            'discount' => $request->discount ?? 0,
            'shipping' => $request->shipping ?? 0,
            'status' => $request->status,
            'note' => $request->note,
            'grand_total' => $request->grand_total,
            'paid_amount' => $request->paid_amount,
            'due_amount' => $request->due_amount,
            'full_paid' => $request->full_paid,
        ]);

        // Get old sale return items
        $oldSaleReturnItems = SaleReturnItem::where('sale_return_id', $sales->id)->get();

        // If old status was Return, revert the quantity changes
        if ($oldStatus === 'Return') {
            foreach ($oldSaleReturnItems as $oldItem) {
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    $product->decrement('product_qty', $oldItem->quantity);
                }
            }
        }

        // Delete old sales item
        SaleReturnItem::where('sale_return_id',$sales->id)->delete();

        foreach($request->products as $product_id => $product){
            $productModel = Product::find($product_id);
            if (!$productModel) {
                continue;
            }

            // Calculate stock: if new status is Return, add quantity; otherwise use current stock
            $stockAfter = $request->status === 'Return' 
                ? $productModel->product_qty + $product['quantity'] 
                : $productModel->product_qty;

            SaleReturnItem::create([
                'sale_return_id' => $sales->id,
                'product_id' => $product_id,
                'net_unit_cost' => $product['net_unit_cost'],
                'stock' => $stockAfter,
                'quantity' => $product['quantity'],
                'discount' => $product['discount'] ?? 0,
                'subtotal' => $product['subtotal'],
            ]);

            // Only update product stock if new status is Return
            if ($request->status === 'Return') {
                $productModel->increment('product_qty', $product['quantity']);
            }
        }

    $notification = array(
        'message' => 'Sale Return Updated Successfully',
        'alert-type' => 'success'
     );
     return redirect()->route('all.return.sale')->with($notification);
    }
    // End Method

    // Show Sale Return Details
    public function DetailsSalesReturn($id){
        $sales = SaleReturn::with(['customer','saleReturnItems.product'])->find($id);
        return view('admin.backend.return-sale.sales_return_details',compact('sales'));
    }
    // End Method

    // Invoice Sale Return
    public function InvoiceSalesReturn($id){
        $sales = SaleReturn::with(['customer', 'warehouse', 'saleReturnItems.product'])->find($id);

        if (!$sales) {
            abort(404, 'Sale Return not found');
        }

        // Only allow invoice generation if status is Return
        if ($sales->status !== 'Return') {
            abort(403, 'Invoice can only be generated for completed returns (Status: Return)');
        }

        $pdf = Pdf::loadView('admin.backend.return-sale.invoice_pdf',compact('sales'));
        return $pdf->download('sale_return_'.$id.'.pdf');
    }
    // End Method

    // Delete Sale Return
    public function DeleteSalesReturn($id){
        try {
          DB::beginTransaction();
          $sales = SaleReturn::findOrFail($id);
          $SalesItems = SaleReturnItem::where('sale_return_id',$id)->get();

          // Only revert quantity if status was Return
          if ($sales->status === 'Return') {
              foreach($SalesItems as $item){
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->decrement('product_qty',$item->quantity);
                }
              }
          }
          SaleReturnItem::where('sale_return_id',$id)->delete();
          $sales->delete();
          DB::commit();

          $notification = array(
            'message' => 'Sale Return Deleted Successfully',
            'alert-type' => 'success'
         );
         return redirect()->route('all.sale.return')->with($notification);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
          }
    }
    // End Method

    // Due Sale and Due Return Sale
    public function DueSale(){
        $user = auth()->user();
        if (!$user->hasPermissionTo('due.sales') && !$user->hasPermissionTo('due.menu')) {
            abort(403, 'Unauthorized Action');
        }
        if (!auth()->user()->hasPermissionTo('due.sales')) {
            abort(403, 'Unauthorized Action');
        }
        $sales = Sale::with(['customer','warehouse'])
            ->select('id','customer_id','warehouse_id','due_amount')
            ->where('due_amount', '>', 0)
            ->get();
        return view('admin.backend.due.sale_due',compact('sales'));

    }
    // End Method

    public function DueSaleReturn(){
        $user = auth()->user();
        if (!$user->hasPermissionTo('due.sales.return') && !$user->hasPermissionTo('due.return.sale.menu')) {
            abort(403, 'Unauthorized Action');
        }
        $sales = SaleReturn::with(['customer','warehouse'])
            ->select('id','customer_id','warehouse_id','due_amount')
            ->where('due_amount', '>', 0)
            ->get();
        return view('admin.backend.due.sale_return_due',compact('sales'));

    }
    // End Method

}
