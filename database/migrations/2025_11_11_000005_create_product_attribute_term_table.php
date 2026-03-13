<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_attribute_term', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->nullable()->constrained('attributes')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('stock')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'attribute_id', 'term_id'], 'pat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_term');
    }
};


