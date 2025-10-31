<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouses', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'email')) {
                $table->string('email')->nullable()->unique();
            }
            if (!Schema::hasColumn('warehouses', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('warehouses', 'city')) {
                $table->string('city')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (Schema::hasColumn('warehouses', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('warehouses', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('warehouses', 'email')) {
                $table->dropUnique(['email']);
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('warehouses', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};


