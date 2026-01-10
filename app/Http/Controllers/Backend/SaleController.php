<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\NewSaleNotification;
use App\Notifications\NewSaleDueNotification;
use Illuminate\Support\Facades\Notification;

class SaleController extends Controller
{
    //Show All Sales
    public function AllSales(){
        $user = auth()->user();
        if (!$user->hasPermissionTo('all.sale') && !$user->hasPermissionTo('sale.menu')) {
            abort(403, 'Unauthorized Action');
        }
        $allData = Sale::orderBy('id','desc')->get();
        return view('admin.backend.sales.all_sales',compact('allData'));
    }
    //End Method

    // Add Sale
    public function AddSale(){
        if (!auth()->user()->hasPermissionTo('all.sale')) {
            abort(403, 'Unauthorized Action');
        }
        $customers = Customer::all();
        $warehouses = Warehouse::all();

        return view('admin.backend.sales.add_sales',compact('customers','warehouses'));
    }
    //End Method

    // Store Sale
    public function StoreSale(Request $request){
        if (!auth()->user()->hasPermissionTo('all.sale')) {
            abort(403, 'Unauthorized Action');
        }
        $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:Sale,Pending,Ordered',
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.cost' => 'nullable|numeric|min:0',
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
            'status.in' => 'The status must be one of: Sale, Pending, or Ordered.',
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'The selected customer is invalid.',
            'warehouse_id.required' => 'Please select a warehouse.',
            'warehouse_id.exists' => 'The selected warehouse is invalid.',
            'products.required' => 'Please add at least one product to the order.',
            'products.min' => 'Please add at least one product to the order.',
            'products.*.id.required' => 'Product ID is required.',
            'products.*.id.exists' => 'One or more selected products are invalid.',
            'products.*.quantity.required' => 'Product quantity is required.',
            'products.*.quantity.numeric' => 'Product quantity must be a number.',
            'products.*.quantity.min' => 'Product quantity must be at least 1.',
            'products.*.cost.numeric' => 'Product cost must be a number.',
            'products.*.net_unit_cost.numeric' => 'Net unit cost must be a number.',
            'products.*.discount.numeric' => 'Product discount must be a number.',
            'discount.numeric' => 'Discount must be a number.',
            'shipping.numeric' => 'Shipping must be a number.',
            'paid_amount.numeric' => 'Paid amount must be a number.',
            'due_amount.numeric' => 'Due amount must be a number.',
        ]);

        try {
            DB::beginTransaction();

            $grandTotal = 0;

            $sales = Sale::create([
                'date' => $request->date,
                'warehouse_id' => $request->warehouse_id,
                'customer_id' => $request->customer_id,
                'discount' => $request->discount ?? 0,
                'shipping' => $request->shipping ?? 0,
                'status' => $request->status,
                'note' => $request->note,
                'grand_total' => 0,
                'paid_amount' => $request->paid_amount ?? 0,
                'due_amount' => $request->due_amount ?? 0,
            ]);

            // Store Sale Items & Update Stock (only if status = Sale)
            foreach ($request->products as $productData) {
                $product = Product::findOrFail($productData['id']);
                
                // Get net unit cost from 'cost' field (sent by JavaScript) or fallback to product price
                $netUnitCost = $productData['cost'] ?? $productData['net_unit_cost'] ?? $product->price;

                if ($netUnitCost === null) {
                    throw new \Exception("Net Unit cost is missing for the product id " . $productData['id']);
                }

                $subtotal = ($netUnitCost * $productData['quantity']) - ($productData['discount'] ?? 0);
                $grandTotal += $subtotal;

                // Stock before sale (for record keeping)
                $stockBeforeSale = $product->product_qty;
                // Stock after sale: if status is Sale, subtract quantity; otherwise keep current
                $stockAfterSale = $request->status === 'Sale' 
                    ? $stockBeforeSale - $productData['quantity'] 
                    : $stockBeforeSale;

                SaleItem::create([
                    'sale_id' => $sales->id,
                    'product_id' => $productData['id'],
                    'net_unit_cost' => $netUnitCost,
                    'stock' => $stockBeforeSale, // Stock before sale
                    'quantity' => $productData['quantity'],
                    'discount' => $productData['discount'] ?? 0,
                    'subtotal' => $subtotal,
                ]);

                // Only decrease product stock if status is Sale
                if ($request->status === 'Sale') {
                    $product->decrement('product_qty', $productData['quantity']);
                }
            }

            // Update grand total after all items processed
            $sales->update(['grand_total' => $grandTotal + ($request->shipping ?? 0) - ($request->discount ?? 0)]);

            DB::commit();

            $admin = User::role('Super Admin')->get();
            Notification::send($admin, new NewSaleNotification($sales));

            // Additional notification when sale has due amount
            if ($sales->due_amount > 0) {
                Notification::send($admin, new NewSaleDueNotification($sales));
            }

            $notification = array(
                'message' => 'Sale Stored Successfully',
                'alert-type' => 'success'
            );

            return redirect()->route('all.sale')->with($notification);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Store Sale error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            // Do not show internal error details to the user; just a generic message
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to store sale. Please try again or contact the administrator.']);
        }
    }
    //End Method

    // Edit Sale
    public function EditSales($id) {
        if (!auth()->user()->hasPermissionTo('all.sale')) {
            abort(403, 'Unauthorized Action');
        }
        $editData = Sale::with('saleItems.product')->findOrFail($id);
        $customers = Customer::all();
        $warehouses = Warehouse::all();
        return view('admin.backend.sales.edit_sales', compact('editData','customers','warehouses'));
    }
    // End Method

    // Update Sale
    public function UpdateSales(Request $request, $id) {
        if (!auth()->user()->hasPermissionTo('all.sale')) {
            abort(403, 'Unauthorized Action');
        }
        $request->validate([
            'date' => 'required|date',
            'status' => 'required'
        ]);

        $sales = Sale::findOrFail($id);
        $oldStatus = $sales->status;

        // Calculate due_amount from grand_total, paid_amount, and full_paid
        $grandTotal = $request->grand_total ?? $sales->grand_total;
        $paidAmount = $request->paid_amount ?? 0;
        $fullPaid = $request->full_paid ?? 0;
        $dueAmount = max(0, $grandTotal - $paidAmount - $fullPaid);

        $sales->update([
            'date' => $request->date,
            'warehouse_id' => $request->warehouse_id,
            'customer_id' => $request->customer_id,
            'discount' => $request->discount ?? 0,
            'shipping' => $request->shipping ?? 0,
            'status' => $request->status,
            'note' => $request->note,
            'grand_total' => $grandTotal,
            'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount,
            'full_paid' => $fullPaid,
        ]);

        // Get old sale items
        $oldSaleItems = SaleItem::where('sale_id', $sales->id)->get();

        // If old status was Sale, revert the quantity changes
        if ($oldStatus === 'Sale') {
            foreach ($oldSaleItems as $oldItem) {
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    $product->increment('product_qty', $oldItem->quantity);
                }
            }
        }

        // Delete old sales items
        SaleItem::where('sale_id', $sales->id)->delete();

        foreach ($request->products as $product_id => $product){
            $productModel = Product::find($product_id);
            if (!$productModel) {
                continue;
            }

            // Stock before update
            $stockBefore = $productModel->product_qty;

            SaleItem::create([
                'sale_id' => $sales->id,
                'product_id' => $product_id,
                'net_unit_cost' => $product['net_unit_cost'],
                'stock' => $stockBefore,
                'quantity' => $product['quantity'],
                'discount' => $product['discount'] ?? 0,
                'subtotal' => $product['subtotal'],
            ]);

            // Only update product stock if new status is Sale
            if ($request->status === 'Sale') {
                $productModel->decrement('product_qty', $product['quantity']);
            }
        }

        $notification = array(
            'message' => 'Sale Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.sale')->with($notification);
    }
    // End Method

    // Pay Sale (for due.sales permission only)
    public function PaySale($id) {
        $user = auth()->user();
        if (!$user->hasPermissionTo('due.sales') && !$user->hasPermissionTo('all.sale')) {
            abort(403, 'Unauthorized Action');
        }
        
        $sale = Sale::with(['customer', 'warehouse', 'saleItems.product'])->findOrFail($id);
        
        // Verify that this sale has a due amount
        if ($sale->due_amount <= 0) {
            $notification = array(
                'message' => 'This sale has no due amount.',
                'alert-type' => 'warning'
            );
            return redirect()->route('due.sale')->with($notification);
        }
        
        return view('admin.backend.sales.pay_sale', compact('sale'));
    }
    // End Method

    // Update Sale Payment (for due.sales permission only)
    public function UpdateSalePayment(Request $request, $id) {
        $user = auth()->user();
        if (!$user->hasPermissionTo('due.sales') && !$user->hasPermissionTo('all.sale')) {
            abort(403, 'Unauthorized Action');
        }
        
        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'full_paid' => 'nullable|numeric|min:0',
        ]);
        
        try {
            DB::beginTransaction();
            
            $sale = Sale::findOrFail($id);
            
            // Calculate new paid amount and due amount
            $newPaidAmount = $request->paid_amount;
            $fullPaid = $request->full_paid ?? 0;
            $grandTotal = $sale->grand_total;
            
            // Calculate due amount
            $newDueAmount = max(0, $grandTotal - $newPaidAmount - $fullPaid);
            
            $sale->update([
                'paid_amount' => $newPaidAmount,
                'full_paid' => $fullPaid,
                'due_amount' => $newDueAmount,
            ]);
            
            DB::commit();
            
            // Send notification if due amount is paid
            if ($newDueAmount == 0 && $sale->due_amount > 0) {
                $users = User::whereHas('roles', function($query) {
                    $query->where('name', 'Admin');
                })->get();
                Notification::send($users, new NewSaleDueNotification($sale));
            }
            
            $notification = array(
                'message' => 'Payment Updated Successfully',
                'alert-type' => 'success'
            );
            
            return redirect()->route('due.sale')->with($notification);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update Sale Payment error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update payment. Please try again.']);
        }
    }
    // End Method

    // Delete Sale
    public function DeleteSales($id){
        if (!auth()->user()->hasPermissionTo('all.sale')) {
            abort(403, 'Unauthorized Action');
        }
        try {
            DB::beginTransaction();
            $sales = Sale::findOrFail($id);
            $saleItems = SaleItem::where('sale_id', $id)->get();

            // Only revert quantity if status was Sale
            if ($sales->status === 'Sale') {
                foreach ($saleItems as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('product_qty', $item->quantity);
                    }
                }
            }

            SaleItem::where('sale_id', $id)->delete();
            $sales->delete();
            DB::commit();

            $notification = array(
                'message' => 'Sale Deleted Successfully',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
          }
    }
    // End Method

    // Show Sale Details
    public function DetailsSales($id){
        $sales = Sale::with(['customer','saleItems.product'])->find($id);
        return view('admin.backend.sales.sales_details',compact('sales'));
    }
     // End Method

     // Invoice Sale
    public function InvoiceSales($id){
        $sales = Sale::with(['customer', 'warehouse', 'saleItems.product'])->find($id);

        // Only allow invoice generation if status is Sale
        if ($sales->status !== 'Sale') {
            abort(403, 'Invoice can only be generated for completed sales (Status: Sale)');
        }

        $pdf = Pdf::loadView('admin.backend.sales.invoice_pdf',compact('sales'));
        return $pdf->download('sales_'.$id.'.pdf');
    }
    // End Method
}
