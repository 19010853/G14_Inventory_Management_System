<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    // Show All Sale Returns
     public function AllSalesReturn(){
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

            /// Store Sales Items & Update Stock
            foreach($request->products as $productData){
                $product = Product::findOrFail($productData['id']);
                $netUnitCost = $productData['net_unit_cost'] ?? $product->price;

                if ($netUnitCost === null) {
                    throw new \Exception("Net Unit cost is missing for the product id " . $productData['id']);
                }

                $subtotal = ($netUnitCost * $productData['quantity']) - ($productData['discount'] ?? 0);
                $grandTotal += $subtotal;

                SaleReturnItem::create([
                    'sale_return_id' => $sales->id,
                    'product_id' => $productData['id'],
                    'net_unit_cost' => $netUnitCost,
                    'stock' => $product->product_qty + $productData['quantity'],
                    'quantity' => $productData['quantity'],
                    'discount' => $productData['discount'] ?? 0,
                    'subtotal' => $subtotal,
                ]);

                $product->increment('product_qty', $productData['quantity']);
            }

            $sales->update(['grand_total' => $grandTotal + ($request->shipping ?? 0) - ($request->discount ?? 0)]);

            DB::commit();

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

    // Delete old sales item
    SaleReturnItem::where('sale_return_id',$sales->id)->delete();

    foreach($request->products as $product_id => $product){
        SaleReturnItem::create([
            'sale_return_id' => $sales->id,
            'product_id' => $product_id,
            'net_unit_cost' => $product['net_unit_cost'],
            'stock' => $product['stock'],
            'quantity' => $product['quantity'],
            'discount' => $product['discount'] ?? 0,
            'subtotal' => $product['subtotal'],
        ]);

        /// Update Product Stock

        $productModel = Product::find($product_id);
        if ($productModel) {
            $productModel->product_qty += $product['quantity'];
            $productModel->save();
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

    // Delete Sale Return
    public function DeleteSalesReturn($id){
        try {
          DB::beginTransaction();
          $sales = SaleReturn::findOrFail($id);
          $SalesItems = SaleReturnItem::where('sale_return_id',$id)->get();

          foreach($SalesItems as $item){
            $product = Product::find($item->product_id);
            if ($product) {
                $product->decrement('product_qty',$item->quantity);
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
        $sales = Sale::with(['customer','warehouse'])
            ->select('id','customer_id','warehouse_id','due_amount')
            ->where('due_amount', '>', 0)
            ->get();
        return view('admin.backend.due.sale_due',compact('sales'));

    }
    // End Method

    public function DueSaleReturn(){
        $sales = SaleReturn::with(['customer','warehouse'])
            ->select('id','customer_id','warehouse_id','due_amount')
            ->where('due_amount', '>', 0)
            ->get();
        return view('admin.backend.due.sale_return_due',compact('sales'));

    }
    // End Method

}
