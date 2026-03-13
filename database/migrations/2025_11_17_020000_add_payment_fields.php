<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'method')) {
                $table->string('method', 20)->default('card')->after('status');
            }
        });

        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('maintenance');
            }
            if (!Schema::hasColumn('stores', 'bank_iban')) {
                $table->string('bank_iban')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('stores', 'bank_receiver')) {
                $table->string('bank_receiver')->nullable()->after('bank_iban');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'method')) {
                $table->dropColumn('method');
            }
        });

        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'bank_receiver')) {
                $table->dropColumn('bank_receiver');
            }
            if (Schema::hasColumn('stores', 'bank_iban')) {
                $table->dropColumn('bank_iban');
            }
            if (Schema::hasColumn('stores', 'bank_name')) {
                $table->dropColumn('bank_name');
            }
        });
    }
};
