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
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('data')->nullable(); // Stores product_ids, category_ids, etc.
            $table->timestamps();
        });

        // Insert default sections
        DB::table('home_sections')->insert([
            [
                'name' => 'slider',
                'title' => 'Slider',
                'description' => 'Ana sayfa en üst slider alanı',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'new_products',
                'title' => 'Yeni Ürünler',
                'description' => 'Yeni eklenen ürünlerin listelendiği alan',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'all_categories',
                'title' => 'Tüm Kategoriler',
                'description' => 'Kategorilerin listelendiği alan',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};
