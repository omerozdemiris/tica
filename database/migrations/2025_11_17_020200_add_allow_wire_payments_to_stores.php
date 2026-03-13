<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'allow_wire_payments')) {
                $table->boolean('allow_wire_payments')->default(false)->after('notify_order_complete');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'allow_wire_payments')) {
                $table->dropColumn('allow_wire_payments');
            }
        });
    }
};

