<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'stock')) {
                $table->integer('stock')->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'stock_type')) {
                $table->tinyInteger('stock_type')->default(0)->after('stock'); // 0: limited, 1: unlimited
            }
            if (!Schema::hasColumn('products', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('description');
            }
            if (!Schema::hasColumn('products', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'stock_type')) $table->dropColumn('stock_type');
            if (Schema::hasColumn('products', 'stock')) $table->dropColumn('stock');
            if (Schema::hasColumn('products', 'meta_description')) $table->dropColumn('meta_description');
            if (Schema::hasColumn('products', 'meta_title')) $table->dropColumn('meta_title');
        });
    }
};


