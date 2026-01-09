<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmployeeAccountCreatedMail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // Show All Permission
    public function AllPermission(){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
        $permissions = Permission::all();
        $totalPermissions = $permissions->count();
        return view('admin.pages.permission.all_permission',compact('permissions', 'totalPermissions'));
    }
    // End Method

    // Show All Roles
    public function AllRoles(){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
        $roles = Role::all();
        return view('admin.pages.role.all_role',compact('roles'));
    }
    // End Method

    // Add New Roles
    public function AddRoles(){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
        return view('admin.pages.role.add_role');
    }
    // End Method

    // Store New Roles
    public function StoreRoles(Request $request){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }

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
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
        $roles = Role::find($id);
        return view('admin.pages.role.edit_role',compact('roles'));

     }
     // End Method

     // Update Roles after press edit button
     public function UpdateRoles(Request $request){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
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
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
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
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
        $roles = Role::all();
        $permissions = Permission::all();
        $permission_groups = User::getpermissionGroups();
        return view('admin.pages.rolesetup.add_roles_permission',compact('roles','permissions','permission_groups'));

    }
     // End Method

    // Store Role in Permission
     public function StoreRolePermission(Request $request){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
        
        // Validate request
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission' => 'required|array',
            'permission.*' => 'exists:permissions,id',
        ], [
            'role_id.required' => 'Please select a role.',
            'role_id.exists' => 'Selected role does not exist.',
            'permission.required' => 'Please select at least one permission.',
            'permission.array' => 'Permissions must be an array.',
            'permission.*.exists' => 'One or more selected permissions do not exist.',
        ]);
        
        $role = Role::findOrFail($request->role_id);
        $permissions = $request->permission ?? [];

        if (!empty($permissions)) {
            // Get permission names from IDs
            $permissionNames = Permission::whereIn('id', $permissions)->pluck('name')->toArray();
            // Use syncPermissions to avoid duplicate entries
            $role->syncPermissions($permissionNames);
        } else {
            // If no permissions selected, remove all permissions
            $role->syncPermissions([]);
        }

        $notification = array(
            'message' => 'Role Permission Added Successfully',
            'alert-type' => 'success'
         );
         return redirect()->route('all.roles.permission')->with($notification);

     }
      // End Method

    //Show All Roles with their Permissions
    public function AllRolesPermission(){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
        $roles = Role::all();
        return view('admin.pages.rolesetup.all_roles_permission',compact('roles'));
      }
      // End Method

    // Edit Admin Roles with their Permissions
    public function AdminEditRoles($id){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
        $role = Role::find($id);
        $permissions = Permission::all();
        $permission_groups = User::getpermissionGroups();
        return view('admin.pages.rolesetup.edit_roles_permission',compact('role','permissions','permission_groups'));

    }
    // End Method

    // Update Admin Roles with their Permissions after press edit button
    public function AdminRolesUpdate(Request $request, $id){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
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
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }

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

    //Show All Employee Roles with their Permissions
    public function AllEmployee(){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
        // Query both 'employee' and 'admin' roles to show existing users
        // This handles migration from old 'admin' role to new 'employee' role
        $alladmin = User::whereIn('role', ['employee', 'admin'])->latest()->get();
        return view('admin.pages.admin.all_admin',compact('alladmin'));
    }
    // End Method

    // Add Employee Roles with their Permissions
    public function AddEmployee(){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
        $roles = Role::all();
        return view('admin.pages.admin.add_admin',compact('roles'));
    }
    // End Method

    // Store Employee Roles with their Permissions
    public function StoreEmployee(Request $request){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'roles' => ['nullable', 'exists:roles,id'],
        ]);

        // Generate a random password
        $randomPassword = Str::random(32);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($randomPassword);
        $user->role = 'employee';
        $user->save();

        if ($request->roles) {
            $role = Role::where('id',$request->roles)->where('guard_name','web')->first();
            if ($role) {
                $user->assignRole($role->name);
            }
        }

        // Send email with password
        try {
            Mail::to($user->email)->send(new EmployeeAccountCreatedMail($user->name, $randomPassword));
        } catch (\Exception $e) {
            \Log::error('Failed to send employee account creation email: ' . $e->getMessage());
            // Continue even if email fails
        }

        $notification = array(
            'message' => 'New Employee Inserted Successfully. Password has been sent to their email.',
            'alert-type' => 'success'
         );
         return redirect()->route('all.employee')->with($notification);

    }
    // End Method

    // Details Employee - View Employee Information
    public function DetailsEmployee($id){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
        $admin = User::findOrFail($id);
        return view('admin.pages.admin.details_admin',compact('admin'));
    }
    // End Method

    // Edit Employee Roles
    public function EditEmployeeRoles($id){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }
        $employee = User::findOrFail($id);
        $roles = Role::all();
        $permissions = Permission::all();
        $permission_groups = User::getpermissionGroups();
        return view('admin.pages.admin.edit_employee_roles',compact('employee','roles','permissions','permission_groups'));
    }
    // End Method

    // Update Employee Roles
    public function UpdateEmployeeRoles(Request $request, $id){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }

        $employee = User::findOrFail($id);
        
        // Update roles if provided
        if ($request->has('roles') && $request->roles) {
            $role = Role::where('id', $request->roles)->where('guard_name', 'web')->first();
            if ($role) {
                // Remove all existing roles and assign new one
                $employee->syncRoles([$role->name]);
            }
        } else {
            // If no role selected, remove all roles
            $employee->syncRoles([]);
        }

        // Update permissions if provided
        if ($request->has('permission')) {
            $permissions = $request->permission ?? [];
            if (!empty($permissions)) {
                $permissionNames = Permission::whereIn('id', $permissions)->pluck('name')->toArray();
                $employee->syncPermissions($permissionNames);
            } else {
                $employee->syncPermissions([]);
            }
        }

        $notification = array(
            'message' => 'Employee Roles Updated Successfully',
            'alert-type' => 'success'
         );
         return redirect()->route('all.employee')->with($notification);
    }
    // End Method

    // Delete Employee Roles with their Permissions
    public function DeleteEmployee($id){
        if (!auth()->user()->hasPermissionTo('role_and_permission.all')) {
            abort(403, 'Unauthorized Action');
        }

        $admin = User::find($id);
        if (!is_null($admin)) {
            $admin->delete();
        }

        $notification = array(
            'message' => 'Employee Deleted Successfully',
            'alert-type' => 'success'
         );
         return redirect()->back()->with($notification);

    }
     // End Method
}
