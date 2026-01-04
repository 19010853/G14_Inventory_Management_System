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
        // For MySQL, we need to use raw SQL as renameColumn may not work
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products CHANGE product_quantity product_qty INTEGER DEFAULT 0');
        } else {
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('product_quantity', 'product_qty');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For MySQL, we need to use raw SQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products CHANGE product_qty product_quantity INTEGER DEFAULT 0');
        } else {
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('product_qty', 'product_quantity');
            });
        }
    }
};
