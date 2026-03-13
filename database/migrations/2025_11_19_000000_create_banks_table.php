<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('banks')) {
            Schema::create('banks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained()->cascadeOnDelete();
                $table->string('bank_name');
                $table->string('bank_iban');
                $table->string('bank_receiver');
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('stores')) {
            $store = DB::table('stores')->first();

            if (
                $store &&
                (($store->bank_name ?? null) ||
                    ($store->bank_iban ?? null) ||
                    ($store->bank_receiver ?? null))
            ) {
                DB::table('banks')->insert([
                    'store_id' => $store->id,
                    'bank_name' => $store->bank_name ?? 'Banka',
                    'bank_iban' => $store->bank_iban ?? '',
                    'bank_receiver' => $store->bank_receiver ?? '',
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $columnsToDrop = collect(['bank_name', 'bank_iban', 'bank_receiver'])
                ->filter(fn(string $column) => Schema::hasColumn('stores', $column))
                ->values()
                ->all();

            if (!empty($columnsToDrop)) {
                Schema::table('stores', function (Blueprint $table) use ($columnsToDrop) {
                    $table->dropColumn($columnsToDrop);
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stores')) {
            if (!Schema::hasColumn('stores', 'bank_name')) {
                Schema::table('stores', function (Blueprint $table) {
                    $table->string('bank_name')->nullable()->after('phone');
                });
            }

            if (!Schema::hasColumn('stores', 'bank_iban')) {
                Schema::table('stores', function (Blueprint $table) {
                    $table->string('bank_iban')->nullable()->after('bank_name');
                });
            }

            if (!Schema::hasColumn('stores', 'bank_receiver')) {
                Schema::table('stores', function (Blueprint $table) {
                    $table->string('bank_receiver')->nullable()->after('bank_iban');
                });
            }

            if (Schema::hasTable('banks')) {
                $bank = DB::table('banks')->where('status', true)->first() ?? DB::table('banks')->first();

                if ($bank) {
                    DB::table('stores')
                        ->where('id', $bank->store_id)
                        ->update([
                            'bank_name' => $bank->bank_name,
                            'bank_iban' => $bank->bank_iban,
                            'bank_receiver' => $bank->bank_receiver,
                        ]);
                }
            }
        }

        Schema::dropIfExists('banks');
    }
};

