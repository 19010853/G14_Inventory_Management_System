<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get old permissions to remove
        $oldPermissions = [
            'edit.brand',
            'delete.brand',
            'all.transfers',  // Should be all.transfer (singular)
            'transfers.menu',  // Should be transfer.menu (singular)
            'reports.all',     // Should be all.report
        ];

        // Remove old permissions from role_permissions pivot table first
        foreach ($oldPermissions as $oldPermName) {
            $permission = Permission::where('name', $oldPermName)->first();
            if ($permission) {
                // Remove from all roles
                DB::table('role_has_permissions')
                    ->where('permission_id', $permission->id)
                    ->delete();
                
                // Delete the permission
                $permission->delete();
            }
        }

        // Handle permission name changes and role updates
        $permissionMappings = [
            'reports.all' => 'all.report',
        ];

        foreach ($permissionMappings as $oldName => $newName) {
            $oldPermission = Permission::where('name', $oldName)->first();
            $newPermission = Permission::where('name', $newName)->first();

            if ($oldPermission && !$newPermission) {
                // Rename the permission
                $oldPermission->update(['name' => $newName]);
                
                // Update group_name if needed
                if ($oldName === 'reports.all') {
                    $oldPermission->update(['group_name' => 'Report']);
                }
            } elseif ($oldPermission && $newPermission) {
                // Both exist - migrate roles from old to new, then delete old
                $rolesWithOld = DB::table('role_has_permissions')
                    ->where('permission_id', $oldPermission->id)
                    ->pluck('role_id')
                    ->unique();

                foreach ($rolesWithOld as $roleId) {
                    // Check if role already has new permission
                    $hasNew = DB::table('role_has_permissions')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $newPermission->id)
                        ->exists();

                    if (!$hasNew) {
                        // Add new permission to role
                        DB::table('role_has_permissions')->insert([
                            'permission_id' => $newPermission->id,
                            'role_id' => $roleId,
                        ]);
                    }
                }

                // Remove old permission from all roles
                DB::table('role_has_permissions')
                    ->where('permission_id', $oldPermission->id)
                    ->delete();
                
                // Delete old permission
                $oldPermission->delete();
            }
        }

        // Note: all.report is already created by migration 2026_01_09_171216
        // No need to grant report.menu since it no longer exists

        // Note: all.transfer and transfer.menu are already created by migration 2026_01_09_171216
        // We just need to migrate from old permissions if they exist
        $allTransfer = Permission::where('name', 'all.transfer')->where('guard_name', 'web')->first();
        $transferMenu = Permission::where('name', 'transfer.menu')->where('guard_name', 'web')->first();
        
        if (!$allTransfer || !$transferMenu) {
            // If they don't exist, they should have been created by the previous migration
            \Log::warning('Permissions all.transfer or transfer.menu not found. Please run migration 2026_01_09_171216 first.');
            return;
        }

        // Migrate from all.transfers to all.transfer if needed
        $oldTransfers = Permission::where('name', 'all.transfers')->first();
        if ($oldTransfers) {
            $rolesWithOldTransfers = DB::table('role_has_permissions')
                ->where('permission_id', $oldTransfers->id)
                ->pluck('role_id')
                ->unique();

            foreach ($rolesWithOldTransfers as $roleId) {
                $hasNew = DB::table('role_has_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $allTransfer->id)
                    ->exists();

                if (!$hasNew) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $allTransfer->id,
                        'role_id' => $roleId,
                    ]);
                }
            }

            DB::table('role_has_permissions')
                ->where('permission_id', $oldTransfers->id)
                ->delete();
            
            $oldTransfers->delete();
        }

        // transfer.menu is already retrieved above

        $rolesWithAllTransfer = DB::table('role_has_permissions')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('permissions.name', 'all.transfer')
            ->pluck('role_has_permissions.role_id')
            ->unique();

        foreach ($rolesWithAllTransfer as $roleId) {
            $hasMenu = DB::table('role_has_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $transferMenu->id)
                ->exists();

            if (!$hasMenu) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $transferMenu->id,
                    'role_id' => $roleId,
                ]);
            }
        }

        // For roles that had edit.brand or delete.brand, ensure they have all.brand
        $allBrand = Permission::where('name', 'all.brand')->first();
        if ($allBrand) {
            // Get roles that had edit.brand or delete.brand (if they still exist in pivot table from previous state)
            // Since we're deleting them, we need to check if any roles need all.brand
            // This is handled by the permission seeder, but we ensure consistency here
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This migration removes old permissions, so reversing would require
        // recreating them, which is not recommended. This is a one-way migration.
        // If you need to rollback, restore from backup.
    }
};
