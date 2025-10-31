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
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('customers', 'email')) {
                $table->string('email')->nullable()->unique();
            }
            if (!Schema::hasColumn('customers', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('customers', 'address')) {
                $table->string('address')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('customers', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('customers', 'email')) {
                $table->dropUnique(['email']);
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('customers', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};


