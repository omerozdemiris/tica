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
        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'tax_enabled')) {
                $table->boolean('tax_enabled')->default(false)->after('auto_stock');
            }
            if (!Schema::hasColumn('stores', 'tax_rate')) {
                $table->decimal('tax_rate', 8, 4)->nullable()->after('tax_enabled');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'tax_behavior')) {
                $table->tinyInteger('tax_behavior')->default(0)->after('price');
            }
            if (!Schema::hasColumn('products', 'tax_rate')) {
                $table->decimal('tax_rate', 8, 4)->nullable()->after('tax_behavior');
            }
        });

        Schema::table('product_attribute_term', function (Blueprint $table) {
            if (!Schema::hasColumn('product_attribute_term', 'tax_behavior')) {
                $table->tinyInteger('tax_behavior')->default(0)->after('price');
            }
            if (!Schema::hasColumn('product_attribute_term', 'tax_rate')) {
                $table->decimal('tax_rate', 8, 4)->nullable()->after('tax_behavior');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'tax_enabled')) {
                $table->dropColumn('tax_enabled');
            }
            if (Schema::hasColumn('stores', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'tax_behavior')) {
                $table->dropColumn('tax_behavior');
            }
            if (Schema::hasColumn('products', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
        });

        Schema::table('product_attribute_term', function (Blueprint $table) {
            if (Schema::hasColumn('product_attribute_term', 'tax_behavior')) {
                $table->dropColumn('tax_behavior');
            }
            if (Schema::hasColumn('product_attribute_term', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
        });
    }
};
