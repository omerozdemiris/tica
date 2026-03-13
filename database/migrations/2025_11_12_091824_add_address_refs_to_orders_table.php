<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping_address_id')) {
                $table->foreignId('shipping_address_id')->nullable()->after('customer_email')->constrained('user_addresses')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'billing_address_id')) {
                $table->foreignId('billing_address_id')->nullable()->after('shipping_address_id')->constrained('user_addresses')->nullOnDelete();
            }
        });

        DB::table('orders')
            ->select('id', 'user_id', 'customer_name', 'customer_phone', 'shipping_address', 'billing_address')
            ->orderBy('id')
            ->chunkById(100, function ($orders) {
                foreach ($orders as $order) {
                    $shippingId = null;
                    $billingId = null;

                    if (!empty($order->shipping_address)) {
                        $shippingId = DB::table('user_addresses')
                            ->where('user_id', $order->user_id)
                            ->where('type', 'shipping')
                            ->where('address', $order->shipping_address)
                            ->value('id');

                        if (!$shippingId) {
                            $shippingId = DB::table('user_addresses')->insertGetId([
                                'user_id' => $order->user_id,
                                'type' => 'shipping',
                                'title' => 'Teslimat Adresi',
                                'fullname' => $order->customer_name ?? 'Müşteri',
                                'phone' => $order->customer_phone,
                                'address' => $order->shipping_address,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    if (!empty($order->billing_address)) {
                        $billingId = DB::table('user_addresses')
                            ->where('user_id', $order->user_id)
                            ->where('type', 'billing')
                            ->where('address', $order->billing_address)
                            ->value('id');

                        if (!$billingId) {
                            $billingId = DB::table('user_addresses')->insertGetId([
                                'user_id' => $order->user_id,
                                'type' => 'billing',
                                'title' => 'Fatura Adresi',
                                'fullname' => $order->customer_name ?? 'Müşteri',
                                'phone' => $order->customer_phone,
                                'address' => $order->billing_address,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    $update = [];
                    if ($shippingId) {
                        $update['shipping_address_id'] = $shippingId;
                    }
                    if ($billingId) {
                        $update['billing_address_id'] = $billingId;
                    }
                    if (!empty($update)) {
                        DB::table('orders')->where('id', $order->id)->update($update);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'billing_address_id')) {
                $table->dropForeign(['billing_address_id']);
                $table->dropColumn('billing_address_id');
            }
            if (Schema::hasColumn('orders', 'shipping_address_id')) {
                $table->dropForeign(['shipping_address_id']);
                $table->dropColumn('shipping_address_id');
            }
        });
    }
};
