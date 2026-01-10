<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove return.purchase permission from all roles
        $returnPurchase = Permission::where('name', 'return.purchase')->where('guard_name', 'web')->first();
        
        if ($returnPurchase) {
            // Remove from all roles
            DB::table('role_has_permissions')
                ->where('permission_id', $returnPurchase->id)
                ->delete();
            
            // Delete the permission
            $returnPurchase->delete();
        }

        // Remove return.sale permission from all roles
        $returnSale = Permission::where('name', 'return.sale')->where('guard_name', 'web')->first();
        
        if ($returnSale) {
            // Remove from all roles
            DB::table('role_has_permissions')
                ->where('permission_id', $returnSale->id)
                ->delete();
            
            // Delete the permission
            $returnSale->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate return.purchase and return.sale permissions if needed
        Permission::updateOrCreate(
            ['name' => 'return.purchase', 'guard_name' => 'web'],
            ['group_name' => 'Purchase']
        );

        Permission::updateOrCreate(
            ['name' => 'return.sale', 'guard_name' => 'web'],
            ['group_name' => 'Sale']
        );
    }
};
