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
use Illuminate\Support\Facades\Notification;

class SaleController extends Controller
{
    //Show All Sales
    public function AllSales(){
        $allData = Sale::orderBy('id','desc')->get();
        return view('admin.backend.sales.all_sales',compact('allData'));
    }
    //End Method

    // Add Sale
    public function AddSale(){
        $customers = Customer::all();
        $warehouses = Warehouse::all();

        return view('admin.backend.sales.add_sales',compact('customers','warehouses'));
    }
    //End Method

    // Store Sale
    public function StoreSale(Request $request){
        $request->validate([
            'date' => 'required|date',
            'status' => 'required',
            'customer_id' => 'required',
            'warehouse_id' => 'required',
            'products' => 'required|array|min:1',
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

            // Store Sale Items & Update Stock
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

                SaleItem::create([
                    'sale_id' => $sales->id,
                    'product_id' => $productData['id'],
                    'net_unit_cost' => $netUnitCost,
                    'stock' => $stockBeforeSale, // Stock before sale
                    'quantity' => $productData['quantity'],
                    'discount' => $productData['discount'] ?? 0,
                    'subtotal' => $subtotal,
                ]);

                // Decrease product stock after sale
                $product->decrement('product_qty', $productData['quantity']);
            }

            // Update grand total after all items processed
            $sales->update(['grand_total' => $grandTotal + ($request->shipping ?? 0) - ($request->discount ?? 0)]);

            DB::commit();

            $admin = User::role('Super Admin')->get();
            Notification::send($admin, new NewSaleNotification($sales));

            $notification = array(
                'message' => 'Sale Stored Successfully',
                'alert-type' => 'success'
            );

            return redirect()->route('all.sale')->with($notification);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Store Sale error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to store sale: ' . $e->getMessage()]);
        }
    }
    //End Method

    // Edit Sale
    public function EditSales($id) {
        $editData = Sale::with('saleItems.product')->findOrFail($id);
        $customers = Customer::all();
        $warehouses = Warehouse::all();
        return view('admin.backend.sales.edit_sales', compact('editData','customers','warehouses'));
    }
    // End Method

    // Update Sale
    public function UpdateSales(Request $request, $id) {
        $request->validate([
            'date' => 'required|date',
            'status' => 'required'
        ]);

        $sales = Sale::findOrFail($id);
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

        // Delete old sales items
        SaleItem::where('sale_id', $sales->id)->delete();

        foreach ($request->products as $product_id => $product){
            SaleItem::create([
                'sale_id' => $sales->id,
                'product_id' => $product_id,
                'net_unit_cost' => $product['net_unit_cost'],
                'stock' => $product['stock'],
                'quantity' => $product['quantity'],
                'discount' => $product['discount'] ?? 0,
                'subtotal' => $product['subtotal'],
            ]);

            // Update product stock
            $productModel = Product::find($product_id);
            if ($productModel) {
                $productModel->product_qty += $product['quantity'];
                $productModel->save();
            }
        }

        $notification = array(
            'message' => 'Sale Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.sale')->with($notification);
    }
    // End Method

    // Delete Sale
    public function DeleteSales($id){
        try {
            DB::beginTransaction();
            $sales = Sale::findOrFail($id);
            $saleItems = SaleItem::where('sale_id', $id)->get();

            foreach ($saleItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('product_qty', $item->quantity);
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

        $pdf = Pdf::loadView('admin.backend.sales.invoice_pdf',compact('sales'));
        return $pdf->download('sales_'.$id.'.pdf');
    }
    // End Method
}
