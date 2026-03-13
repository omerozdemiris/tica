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
        Schema::table('user_addresses', function (Blueprint $table) {
            // Eski string sütunları nullable yap
            $table->string('city')->nullable()->change();
            $table->string('state')->nullable()->change();
            
            // Yeni foreign key sütunlarını ekle
            $table->foreignId('city_id')->nullable()->after('country')->constrained('cities')->nullOnDelete();
            $table->foreignId('state_id')->nullable()->after('city_id')->constrained('states')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['state_id']);
            $table->dropColumn(['city_id', 'state_id']);
        });
    }
};
