<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Brand Group
            ['name' => 'brand.menu', 'group_name' => 'Brand'],
            ['name' => 'all.brand', 'group_name' => 'Brand'],
            
            // Warehouse Group
            ['name' => 'warehouse.menu', 'group_name' => 'WareHouse'],
            ['name' => 'all.warehouse', 'group_name' => 'WareHouse'],
            
            // Supplier Group
            ['name' => 'supplier.menu', 'group_name' => 'Supplier'],
            ['name' => 'all.supplier', 'group_name' => 'Supplier'],
            
            // Customer Group
            ['name' => 'customer.menu', 'group_name' => 'Customer'],
            ['name' => 'all.customer', 'group_name' => 'Customer'],
            
            // Category Group (Product)
            ['name' => 'category.menu', 'group_name' => 'Product'],
            ['name' => 'all.category', 'group_name' => 'Product'],
            
            // Product Group
            ['name' => 'product.menu', 'group_name' => 'Product'],
            ['name' => 'all.product', 'group_name' => 'Product'],
            
            // Purchase Group
            ['name' => 'purchase.menu', 'group_name' => 'Purchase'],
            ['name' => 'all.purchase', 'group_name' => 'Purchase'],
            
            // Return Purchase Group
            ['name' => 'return.purchase.menu', 'group_name' => 'Purchase'],
            ['name' => 'all.return.purchase', 'group_name' => 'Purchase'],
            
            // Sale Group
            ['name' => 'sale.menu', 'group_name' => 'Sale'],
            ['name' => 'all.sale', 'group_name' => 'Sale'],
            
            // Return Sale Group
            ['name' => 'return.sale.menu', 'group_name' => 'Sale'],
            ['name' => 'all.return.sale', 'group_name' => 'Sale'],
            
            // Due Sales Group
            ['name' => 'due.menu', 'group_name' => 'Due'],
            ['name' => 'due.sales', 'group_name' => 'Due'],
            
            // Due Sales Return Group
            ['name' => 'due.return.sale.menu', 'group_name' => 'Due'],
            ['name' => 'due.sales.return', 'group_name' => 'Due'],
            
            // Transfer Group
            ['name' => 'transfer.menu', 'group_name' => 'Transfers'],
            ['name' => 'all.transfer', 'group_name' => 'Transfers'],
            
            // Report Group
            ['name' => 'all.report', 'group_name' => 'Report'],
            
            // Role & Permission Group
            ['name' => 'role_and_permission.all', 'group_name' => 'Role & Permission'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                ['group_name' => $permission['group_name']]
            );
        }

        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $all_permissions = Permission::all();
        $role->syncPermissions($all_permissions);
    }
}
