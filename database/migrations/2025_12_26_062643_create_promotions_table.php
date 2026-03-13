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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->integer('discount_rate');
            $table->tinyInteger('condition_type'); // 1: Quota, 2: Date
            $table->integer('usage_limit')->nullable(); // For Quota
            $table->integer('usage_count')->default(0);
            $table->dateTime('start_date')->nullable(); // For Date
            $table->dateTime('end_date')->nullable(); // For Date
            $table->boolean('is_active')->default(true);
            $table->json('data'); // Stores selection_type (products/categories) and item_ids
            $table->timestamps();
        });

        // Add applied_promotion_id to carts table to track applied coupon
        Schema::table('carts', function (Blueprint $table) {
            $table->unsignedBigInteger('applied_promotion_id')->nullable()->after('total_items');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('applied_promotion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['applied_promotion_id', 'discount_amount']);
        });
    }
};
