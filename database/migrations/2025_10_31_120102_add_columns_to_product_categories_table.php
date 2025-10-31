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
        Schema::table('product_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('product_categories', 'category_name')) {
                $table->string('category_name')->nullable();
            }
            if (!Schema::hasColumn('product_categories', 'category_slug')) {
                $table->string('category_slug')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            if (Schema::hasColumn('product_categories', 'category_slug')) {
                $table->dropColumn('category_slug');
            }
            if (Schema::hasColumn('product_categories', 'category_name')) {
                $table->dropColumn('category_name');
            }
        });
    }
};


