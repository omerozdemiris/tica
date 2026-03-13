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
        if (Schema::hasTable('promotions')) {
            Schema::table('promotions', function (Blueprint $table) {
                if (Schema::hasColumn('promotions', 'usage_limit') && !Schema::hasColumn('promotions', 'usage')) {
                    $table->renameColumn('usage_limit', 'usage');
                } elseif (!Schema::hasColumn('promotions', 'usage')) {
                    $table->integer('usage')->nullable()->after('condition_type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            if (Schema::hasColumn('promotions', 'usage')) {
                $table->renameColumn('usage', 'usage_limit');
            }
        });
    }
};

