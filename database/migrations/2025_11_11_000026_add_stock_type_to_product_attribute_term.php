<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_attribute_term', function (Blueprint $table) {
            if (!Schema::hasColumn('product_attribute_term', 'stock_type')) {
                $table->tinyInteger('stock_type')->default(1)->after('stock'); // 1: in stock (unlimited), 0: limited
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_attribute_term', function (Blueprint $table) {
            if (Schema::hasColumn('product_attribute_term', 'stock_type')) {
                $table->dropColumn('stock_type');
            }
        });
    }
};


