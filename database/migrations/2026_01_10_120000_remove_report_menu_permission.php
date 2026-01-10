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
        // Remove report.menu permission from all roles
        $reportMenu = Permission::where('name', 'report.menu')->where('guard_name', 'web')->first();
        
        if ($reportMenu) {
            // Remove from all roles
            DB::table('role_has_permissions')
                ->where('permission_id', $reportMenu->id)
                ->delete();
            
            // Delete the permission
            $reportMenu->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate report.menu permission if needed
        Permission::updateOrCreate(
            ['name' => 'report.menu', 'guard_name' => 'web'],
            ['group_name' => 'Report']
        );
    }
};
