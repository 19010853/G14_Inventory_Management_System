<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all users with role 'admin' to 'employee'
        // This migration handles the transition from 'admin' role to 'employee' role
        DB::table('users')
            ->where('role', 'admin')
            ->update(['role' => 'employee']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: Update all users with role 'employee' back to 'admin'
        // Note: This will affect all employees, not just the ones that were originally 'admin'
        // Use with caution in production
        DB::table('users')
            ->where('role', 'employee')
            ->update(['role' => 'admin']);
    }
};
