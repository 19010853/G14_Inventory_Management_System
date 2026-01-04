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
            'brand.menu',
            'all.brand',
            'edit.brand',
            'delete.brand',
            'warehouse.menu',
            'all.warehouse',
            'supplier.menu',
            'all.supplier',
            'customer.menu',
            'all.customer',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $all_permissions = Permission::all();
        $role->syncPermissions($all_permissions);
    }
}
