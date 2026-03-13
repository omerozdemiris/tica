<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('shipping_company_id')->nullable()->constrained('shipping_companies')->onDelete('set null');
            $table->string('shipping_address')->nullable();
            $table->string('tracking_no')->nullable();
            $table->string('tracking_link')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email');
            $table->timestamps();
            $table->index('order_id');
            $table->index('shipping_company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};
