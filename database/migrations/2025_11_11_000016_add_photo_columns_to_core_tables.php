<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'photo')) {
                $table->string('photo')->nullable()->after('is_active');
            }
        });
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'photo')) {
                $table->string('photo')->nullable()->after('description');
            }
        });
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'photo')) {
                $table->string('photo')->nullable()->after('is_published');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'photo')) {
                $table->dropColumn('photo');
            }
        });
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'photo')) {
                $table->dropColumn('photo');
            }
        });
        Schema::table('blog_posts', function (Blueprint $table) {
            if (Schema::hasColumn('blog_posts', 'photo')) {
                $table->dropColumn('photo');
            }
        });
    }
};


