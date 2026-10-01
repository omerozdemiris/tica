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
        if (Schema::hasTable('orders')) {
            if (Schema::hasColumn('orders', 'user_id')) {
                Schema::table('orders', function (Blueprint $table) {
                    try {
                        $table->dropForeign(['user_id']);
                    } catch (\Throwable $e) {
                        // Foreign key may not exist; ignore.
                    }
                });

                Schema::table('orders', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable()->change();
                });

                Schema::table('orders', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                    if (!Schema::hasColumn('orders', 'guest_id')) {
                        $table->string('guest_id', 64)->nullable()->after('user_id')->index();
                    }
                });
            }
        }

        if (Schema::hasTable('user_addresses')) {
            if (Schema::hasColumn('user_addresses', 'user_id')) {
                Schema::table('user_addresses', function (Blueprint $table) {
                    try {
                        $table->dropForeign(['user_id']);
                    } catch (\Throwable $e) {
                        // Foreign key may not exist; ignore.
                    }
                });

                Schema::table('user_addresses', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable()->change();
                });

                Schema::table('user_addresses', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                    if (!Schema::hasColumn('user_addresses', 'guest_id')) {
                        $table->string('guest_id', 64)->nullable()->after('user_id')->index();
                    }
                });
            } elseif (!Schema::hasColumn('user_addresses', 'guest_id')) {
                Schema::table('user_addresses', function (Blueprint $table) {
                    $table->string('guest_id', 64)->nullable()->after('user_id')->index();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            if (Schema::hasColumn('orders', 'user_id')) {
                Schema::table('orders', function (Blueprint $table) {
                    try {
                        $table->dropForeign(['user_id']);
                    } catch (\Throwable $e) {
                        //
                    }
                });

                Schema::table('orders', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable(false)->change();
                });

                Schema::table('orders', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
            }

            if (Schema::hasColumn('orders', 'guest_id')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->dropColumn('guest_id');
                });
            }
        }

        if (Schema::hasTable('user_addresses')) {
            if (Schema::hasColumn('user_addresses', 'user_id')) {
                Schema::table('user_addresses', function (Blueprint $table) {
                    try {
                        $table->dropForeign(['user_id']);
                    } catch (\Throwable $e) {
                        //
                    }
                });

                Schema::table('user_addresses', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable(false)->change();
                });

                Schema::table('user_addresses', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
            }

            if (Schema::hasColumn('user_addresses', 'guest_id')) {
                Schema::table('user_addresses', function (Blueprint $table) {
                    $table->dropColumn('guest_id');
                });
            }
        }
    }
};
