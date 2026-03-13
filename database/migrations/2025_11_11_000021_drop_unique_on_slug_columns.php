<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            try {
                $table->dropUnique('categories_slug_unique');
            } catch (\Throwable $e) {}
        });
        Schema::table('blog_posts', function (Blueprint $table) {
            try {
                $table->dropUnique('blog_posts_slug_unique');
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unique('slug');
        });
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->unique('slug');
        });
    }
};


