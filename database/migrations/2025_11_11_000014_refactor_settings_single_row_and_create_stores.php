<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Refactor settings to single-row concrete columns
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'group')) {
                $table->dropUnique(['group','key']);
            }
        });
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'group')) {
                $table->dropColumn(['group','key','value']);
            }
            if (!Schema::hasColumn('settings', 'logo')) {
                $table->string('logo')->nullable();
            }
            if (!Schema::hasColumn('settings', 'white_logo')) {
                $table->string('white_logo')->nullable();
            }
            if (!Schema::hasColumn('settings', 'favicon')) {
                $table->string('favicon')->nullable();
            }
            if (!Schema::hasColumn('settings', 'title')) {
                $table->string('title')->nullable();
            }
            if (!Schema::hasColumn('settings', 'contact')) {
                $table->text('contact')->nullable();
            }
            if (!Schema::hasColumn('settings', 'socials')) {
                $table->text('socials')->nullable();
            }
            if (!Schema::hasColumn('settings', 'google_iframe')) {
                $table->longText('google_iframe')->nullable();
            }
            if (!Schema::hasColumn('settings', 'visitor_count')) {
                $table->unsignedBigInteger('visitor_count')->default(0);
            }
        });
        // Ensure single default row
        if (DB::table('settings')->count() === 0) {
            DB::table('settings')->insert(['created_at' => now(), 'updated_at' => now()]);
        }

        // Create stores table as single-row settings for store
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->boolean('sell_enabled')->default(false);
            $table->boolean('auth_required')->default(false);
            $table->boolean('maintenance')->default(false);
            $table->boolean('auto_stock')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->longText('about')->nullable();
            $table->longText('privacy_policy')->nullable();
            $table->longText('cookie_policy')->nullable();
            $table->longText('distance_selling')->nullable();
            $table->timestamps();
        });
        DB::table('stores')->insert(['created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
        // Cannot reliably revert to key/value; leave settings columns as is
    }
};


