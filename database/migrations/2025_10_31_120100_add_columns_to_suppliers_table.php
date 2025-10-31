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
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('suppliers', 'email')) {
                $table->string('email')->nullable()->unique();
            }
            if (!Schema::hasColumn('suppliers', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('suppliers', 'address')) {
                $table->string('address')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('suppliers', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('suppliers', 'email')) {
                $table->dropUnique(['email']);
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('suppliers', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};


