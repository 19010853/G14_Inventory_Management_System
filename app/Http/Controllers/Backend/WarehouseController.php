<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    //All Warehouse
    public function AllWarehouse(){
        $warehouse = Warehouse::latest()->get();
        return view('admin.backend.warehouse.all_warehouse',compact('warehouse'));
    }
    //End Method

    //Add Warehouse
    public function AddWarehouse(){
        return view('admin.backend.warehouse.add_warehouse');
    }
    //End Method

    //Store Warehouse
    public function StoreWarehouse(Request $request){
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:warehouses,email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        Warehouse::insert([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'address' => $validatedData['address'],
        ]);

        $notification = array(
            'message' => 'Warehouse Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.warehouse')->with($notification);
    }
    //End Method

    //Edit Warehouse
    public function EditWarehouse($id){
        $warehouse = Warehouse::find($id);
        return view('admin.backend.warehouse.edit_warehouse',compact('warehouse'));
    }
    //End Method

    //Update Warehouse
    public function UpdateWarehouse(Request $request){
        $warehouse_id = $request->id;

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:warehouses,email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        Warehouse::find($warehouse_id)->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'address' => $validatedData['address'],
        ]);

        $notification = array(
            'message' => 'Warehouse Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.warehouse')->with($notification);
    }
    //End Method

    //Delete Warehouse
    public function DeleteWarehouse($id){
        Warehouse::find($id)->delete();

        $notification = array(
            'message' => 'Warehouse Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
    //End Method
}
