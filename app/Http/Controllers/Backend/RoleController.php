<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // Show All Permission
    public function AllPermission(){
        $permissions = Permission::all();
        return view('admin.backend.pages.permission.all_permission',compact('permissions'));
    }
    // End Method

    // Add New Permission
    public function AddPermission(){
        return view('admin.backend.pages.permission.add_permission');
    }
    // End Method

    // Store New Permission
    public function StorePermission(Request $request){

        Permission::create([
            'name' => $request->name,
            'group_name' => $request->group_name,
        ]);

        $notification = array(
            'message' => 'Permission Inserted Successfully',
            'alert-type' => 'success'
         );
         return redirect()->route('all.permission')->with($notification);
    }
    // End Method

    // Edit Permission
    public function EditPermission($id){
        $permissions = Permission::find($id);
        return view('admin.backend.pages.permission.edit_permission',compact('permissions'));

     }
     // End Method

    // Update Permission after press edit button
     public function UpdatePermission(Request $request){
        $per_id = $request->id;

        Permission::find($per_id)->update([
            'name' => $request->name,
            'group_name' => $request->group_name,
        ]);

        $notification = array(
            'message' => 'Permission Updated Successfully',
            'alert-type' => 'success'
         );
         return redirect()->route('all.permission')->with($notification);
    }
     // End Method

    // Delete Permission
    public function DeletePermission($id){
        Permission::find($id)->delete();

        $notification = array(
            'message' => 'Permission Deleted Successfully',
            'alert-type' => 'success'
         );
         return redirect()->back()->with($notification);

     }
    // End Method
}
