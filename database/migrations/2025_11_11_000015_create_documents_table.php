<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // products, categories, blog_posts etc.
            $table->unsignedBigInteger('parent'); // parent id
            $table->unsignedInteger('queue')->default(0);
            $table->string('name'); // stored filename
            $table->string('original_name')->nullable();
            $table->string('extension', 10)->nullable();
            $table->timestamps();
            $table->index(['type','parent']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};


