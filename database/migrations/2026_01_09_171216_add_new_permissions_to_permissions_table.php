<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            // Category Group (Product)
            ['name' => 'category.menu', 'group_name' => 'Product'],
            
            // Return Purchase Group
            ['name' => 'return.purchase.menu', 'group_name' => 'Purchase'],
            ['name' => 'all.return.purchase', 'group_name' => 'Purchase'],
            
            // Return Sale Group
            ['name' => 'return.sale.menu', 'group_name' => 'Sale'],
            ['name' => 'all.return.sale', 'group_name' => 'Sale'],
            
            // Due Sales Return Group
            ['name' => 'due.return.sale.menu', 'group_name' => 'Due'],
            
            // Transfer Group (rename from transfers.menu and all.transfers)
            ['name' => 'transfer.menu', 'group_name' => 'Transfers'],
            ['name' => 'all.transfer', 'group_name' => 'Transfers'],
            
            // Report Group
            ['name' => 'report.menu', 'group_name' => 'Report'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                ['group_name' => $permission['group_name']]
            );
        }

        // Update existing roles that have all.* permissions to also have .menu permissions
        $this->updateExistingRoles();
    }

    /**
     * Update existing roles to include .menu permissions when they have all.* permissions
     */
    private function updateExistingRoles(): void
    {
        $menuPermissionMap = [
            'all.brand' => 'brand.menu',
            'all.warehouse' => 'warehouse.menu',
            'all.supplier' => 'supplier.menu',
            'all.customer' => 'customer.menu',
            'all.category' => 'category.menu',
            'all.product' => 'product.menu',
            'all.purchase' => 'purchase.menu',
            'all.return.purchase' => 'return.purchase.menu',
            'all.sale' => 'sale.menu',
            'all.return.sale' => 'return.sale.menu',
            'due.sales' => 'due.menu',
            'due.sales.return' => 'due.return.sale.menu',
            'all.transfer' => 'transfer.menu',
            'reports.all' => 'report.menu',
        ];

        // Also handle old permission names
        $oldPermissionMap = [
            'all.transfers' => 'transfer.menu',
            'transfers.menu' => 'transfer.menu',
            'return.purchase' => 'return.purchase.menu',
            'return.sale' => 'return.sale.menu',
        ];

        $allRoles = \Spatie\Permission\Models\Role::all();

        foreach ($allRoles as $role) {
            $permissions = $role->permissions->pluck('name')->toArray();
            $newPermissions = $permissions;

            // Add .menu permissions for all.* permissions
            foreach ($menuPermissionMap as $allPermission => $menuPermission) {
                if (in_array($allPermission, $permissions) && !in_array($menuPermission, $newPermissions)) {
                    $newPermissions[] = $menuPermission;
                }
            }

            // Handle old permission names - migrate to new names
            foreach ($oldPermissionMap as $oldPermission => $newPermission) {
                if (in_array($oldPermission, $permissions)) {
                    // Remove old permission
                    $newPermissions = array_filter($newPermissions, function($p) use ($oldPermission) {
                        return $p !== $oldPermission;
                    });
                    // Add new permission if not already present
                    if (!in_array($newPermission, $newPermissions)) {
                        $newPermissions[] = $newPermission;
                    }
                }
            }

            // Update role permissions if changed
            if (count($newPermissions) !== count($permissions) || array_diff($newPermissions, $permissions)) {
                $role->syncPermissions($newPermissions);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove new permissions
        $permissionsToRemove = [
            'category.menu',
            'return.purchase.menu',
            'all.return.purchase',
            'return.sale.menu',
            'all.return.sale',
            'due.return.sale.menu',
            'transfer.menu',
            'all.transfer',
            'report.menu',
        ];

        foreach ($permissionsToRemove as $permissionName) {
            Permission::where('name', $permissionName)->where('guard_name', 'web')->delete();
        }
    }
};
