<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    //All Supplier
    public function AllSupplier(){
        $supplier = Supplier::latest()->get();
        return view('admin.backend.supplier.all_supplier',compact('supplier'));
    }
    // End Method

    //Add Supplier
    public function AddSupplier(){
        return view('admin.backend.supplier.add_supplier');
    }
    // End Method

    //Store Supplier
    public function StoreSupplier(Request $request){
        // Clean email - if it's not a valid email format, set to null
        $email = $request->email;
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = null;
            // Merge cleaned email back to request for validation
            $request->merge(['email' => null]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        try {
            Supplier::create([
                'name' => $request->name,
                'email' => $email,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            $notification = array(
                'message' => 'Supplier Inserted Successfully',
                'alert-type' => 'success'
            );

            return redirect()->route('all.supplier')->with($notification);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            \Log::error('Failed to store supplier: ' . $e->getMessage());
            
            $notification = array(
                'message' => 'Failed to insert supplier: ' . $e->getMessage(),
                'alert-type' => 'error'
            );

            return redirect()->back()->withInput()->with($notification);
        }
    }
    // End Method

    // Edit Supplier
    public function EditSupplier($id){
        $supplier = Supplier::find($id);
        return view('admin.backend.supplier.edit_supplier',compact('supplier'));
    }
    // End Method

    // Update Supplier
    public function UpdateSupplier(Request $request){
        $supplier_id = $request->id;

        Supplier::find($supplier_id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        $notification = array(
            'message' => 'Supplier Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.supplier')->with($notification);
    }
    // End Method

    // Delete Supplier
    public function DeleteSupplier($id){
        Supplier::find($id)->delete();

        $notification = array(
            'message' => 'Supplier Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
    // End Method

    // All Customer
    public function AllCustomer(){
        $customer = Customer::latest()->get();
        return view('admin.backend.customer.all_customer',compact('customer'));
    }
    // End Method

    // Add Customer
    public function AddCustomer(){
        return view('admin.backend.customer.add_customer');
    }
    // End Method

    // Store Customer
    public function StoreCustomer(Request $request){
        Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        $notification = array(
            'message' => 'Customer Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.customer')->with($notification);
    }
    // End Method

    // Edit Customer
    public function EditCustomer($id){
        $customer = Customer::find($id);
        return view('admin.backend.customer.edit_customer',compact('customer'));
    }
    // End Method

    // Update Customer
    public function UpdateCustomer(Request $request){
        $customer_id = $request->id;

        Customer::find($customer_id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        $notification = array(
            'message' => 'Customer Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.customer')->with($notification);
    }
    // End Method

    // Delete Customer
    public function DeleteCustomer($id){
        Customer::find($id)->delete();

        $notification = array(
            'message' => 'Customer Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
    // End Method
}
