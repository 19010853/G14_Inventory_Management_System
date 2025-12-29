<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // Show All Permission
    public function AllPermission(){
        $permissions = Permission::all();
        return view('admin.pages.permission.all_permission',compact('permissions'));
    }
    // End Method

    // Add New Permission
    public function AddPermission(){
        return view('admin.pages.permission.add_permission');
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
        return view('admin.pages.permission.edit_permission',compact('permissions'));

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

    // Show All Roles
    public function AllRoles(){
        $roles = Role::all();
        return view('admin.backend.pages.role.all_role',compact('roles'));
    }
    // End Method

    // Add New Roles
    public function AddRoles(){
        return view('admin.backend.pages.role.add_role');
    }
    // End Method

    // Store New Roles
    public function StoreRoles(Request $request){

        Role::create([
            'name' => $request->name,
        ]);

        $notification = array(
            'message' => 'Role Inserted Successfully',
            'alert-type' => 'success'
         );
         return redirect()->route('all.roles')->with($notification);
    }
     // End Method

     public function EditRoles($id){
        $roles = Role::find($id);
        return view('admin.backend.pages.role.edit_role',compact('roles'));

     }
     // End Method

     // Update Roles after press edit button
     public function UpdateRoles(Request $request){
        $role_id = $request->id;

        Role::find($role_id)->update([
            'name' => $request->name,
        ]);

        $notification = array(
            'message' => 'Role Updated Successfully',
            'alert-type' => 'success'
         );
         return redirect()->route('all.roles')->with($notification);
    }
     // End Method

        // Delete Roles
       public function DeleteRoles($id){
        Role::find($id)->delete();

        $notification = array(
            'message' => 'Role Deleted Successfully',
            'alert-type' => 'success'
         );
         return redirect()->back()->with($notification);

     }
      // End Method

    // Add Roles in Permission
    public function AddRolesPermission(){
        $roles = Role::all();
        $permissions = Permission::all();
        $permission_groups = User::getpermissionGroups();
        return view('admin.backend.pages.rolesetup.add_roles_permission',compact('roles','permissions','permission_groups'));

    }
     // End Method

    // Store Role in Permission
     public function StoreRolePermission(Request $request){

        $data = array();
        $permissions = $request->permission;

        foreach ($permissions as $key => $item){
            $data['role_id'] = $request->role_id;
            $data['permission_id'] = $item;

            DB::table('role_has_permissions')->insert($data);
        } // End Foreach



        $notification = array(
            'message' => 'Role Permission Added Successfully',
            'alert-type' => 'success'
         );
         return redirect()->route('all.roles.permission')->with($notification);

     }
      // End Method

    //Show All Roles with their Permissions
    public function AllRolesPermission(){
        $roles = Role::all();
        return view('admin.backend.pages.rolesetup.all_roles_permission',compact('roles'));
      }
      // End Method

    // Edit Admin Roles with their Permissions
    public function AdminEditRoles($id){
        $role = Role::find($id);
        $permissions = Permission::all();
        $permission_groups = User::getpermissionGroups();
        return view('admin.backend.pages.rolesetup.edit_roles_permission',compact('role','permissions','permission_groups'));

    }
    // End Method

    // Update Admin Roles with their Permissions after press edit button
    public function AdminRolesUpdate(Request $request, $id){
        $role = Role::find($id);
        $permissions = $request->permission;

        if (!empty($permissions)) {
            $permissionNames = Permission::whereIn('id',$permissions)->pluck('name')->toArray();
            $role->syncPermissions($permissionNames);
        } else {
            $role->syncPermissions([]);
        }

        $notification = array(
            'message' => 'Role Permission Updated Successfully',
            'alert-type' => 'success'
         );
         return redirect()->route('all.roles.permission')->with($notification);

    }
       // End Method

    // Delete Admin Roles with their Permissions
    public function AdminDeleteRoles($id){

        $role = Role::find($id);
        if (!is_null($role)) {
           $role->delete();
        }

       $notification = array(
            'message' => 'Role Permission Deleted Successfully',
            'alert-type' => 'success'
         );
         return redirect()->back()->with($notification);
    }
    // End Method

    //Show All Admin Roles with their Permissions
    public function AllAdmin(){
        $alladmin = User::where('role','admin')->latest()->get();
        return view('admin.backend.pages.admin.all_admin',compact('alladmin'));
    }
    // End Method

    // Add Admin Roles with their Permissions
    public function AddAdmin(){
        $roles = Role::all();
        return view('admin.backend.pages.admin.add_admin',compact('roles'));
    }
    // End Method

    // Store Admin Roles with their Permissions
    public function StoreAdmin(Request $request){

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = 'admin';
        $user->save();

        if ($request->roles) {
            $role = Role::where('id',$request->roles)->where('guard_name','web')->first();
            if ($role) {
                $user->assignRole($role->name);
            }
        }

        $notification = array(
            'message' => 'New Admin Inserted Successfully',
            'alert-type' => 'success'
         );
         return redirect()->route('all.admin')->with($notification);

    }
    // End Method

    // Edit Admin Roles with their Permissions
    public function EditAdmin($id){
        $admin = User::find($id);
        $roles = Role::all();
        return view('admin.backend.pages.admin.edit_admin',compact('admin','roles'));
    }
    // End Method

    // Update Admin Roles with their Permissions after press edit button
    public function UpdateAdmin(Request $request,$id){

        $user = User::find($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = 'admin';
        $user->save();

        $user->roles()->detach();

        if ($request->roles) {
            $role = Role::where('id',$request->roles)->where('guard_name','web')->first();
            if ($role) {
                $user->assignRole($role->name);
            }
        }

        $notification = array(
            'message' => 'New Admin Updated Successfully',
            'alert-type' => 'success'
         );
         return redirect()->route('all.admin')->with($notification);

    }
     // End Method

    // Delete Admin Roles with their Permissions
    public function DeleteAdmin($id){

        $admin = User::find($id);
        if (!is_null($admin)) {
            $admin->delete();
        }

        $notification = array(
            'message' => ' Admin Deleted Successfully',
            'alert-type' => 'success'
         );
         return redirect()->back()->with($notification);

    }
     // End Method
}
