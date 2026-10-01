<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductAttributeTerm;
use App\Models\UserAddress;
use App\Models\City;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Mevcut müşterileri al (role=0)
        $users = User::where('role', 0)->get();

        if ($users->isEmpty()) {
            $this->command->warn('Müşteri bulunamadı. Önce müşteri oluşturun.');
            return;
        }

        // Mevcut ürünleri al
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->warn('Ürün bulunamadı. Önce ürün oluşturun.');
            return;
        }

        // Varyantlı ürünleri al
        $variants = ProductAttributeTerm::with('product')->get();

        // Şehir ve ilçeleri al
        $cities = City::with('states')->get();

        if ($cities->isEmpty()) {
            $this->command->warn('Şehir bulunamadı. Önce CitiesStatesSeeder çalıştırın.');
            return;
        }

        $streets = [
            'Atatürk Caddesi',
            'Cumhuriyet Sokak',
            'İnönü Bulvarı',
            'Gazi Mustafa Kemal Caddesi',
            'Fevzi Çakmak Caddesi',
            'Barbaros Caddesi',
            'Mevlana Bulvarı',
            '15 Temmuz Caddesi',
        ];

        $generateAddressData = function (string $fullname) use ($cities, $streets): array {
            $city = $cities->random();
            $state = $city->states->isNotEmpty() ? $city->states->random() : null;

            return [
                'fullname' => $fullname,
                'phone' => '05' . rand(100000000, 999999999),
                'country' => 'Türkiye',
                'city_id' => $city->id,
                'state_id' => $state?->id,
                'city' => $city->name,
                'state' => $state?->name,
                'zip' => str_pad((string) rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'address' => Arr::random($streets) . ' No:' . rand(5, 199) . ' ' . ($state?->name ?? '') . ' / ' . $city->name,
            ];
        };

        // Her kullanıcı için adresleri yeniden oluştur
        $userAddresses = [];
        foreach ($users as $user) {
            UserAddress::where('user_id', $user->id)->whereNull('guest_id')->delete();

            $shippingData = $generateAddressData($user->name);
            $billingData = $generateAddressData($user->name);

            $shippingAddress = UserAddress::create(array_merge($shippingData, [
                'user_id' => $user->id,
                'guest_id' => null,
                'type' => 'shipping',
                'title' => 'Teslimat Adresi',
                'is_default' => true,
            ]));

            $billingAddress = UserAddress::create(array_merge($billingData, [
                'user_id' => $user->id,
                'guest_id' => null,
                'type' => 'billing',
                'title' => 'Fatura Adresi',
                'is_default' => false,
            ]));

            $userAddresses[$user->id] = [
                'shipping' => $shippingAddress,
                'billing' => $billingAddress,
            ];
        }

        $statusPool = [
            'new',
            'new',
            'pending',
            'pending',
            'pending',
            'completed',
            'completed',
            'completed',
            'completed',
            'canceled',
        ];

        $orderNotes = [
            'Teslimat öncesi arayınız.',
            'Kargo teslimatı hafta içi yapılsın.',
            'Apartman görevlisine teslim edilebilir.',
            'Teslimattan önce SMS gönderiniz.',
            'Hızlı teslimat rica olunur.',
            'Paketin sağlam olmasına dikkat ediniz.',
            null,
            null,
        ];

        $orderCount = Order::count();
        $hasShippingColumn = Schema::hasColumn('orders', 'shipping_address');
        $hasBillingColumn = Schema::hasColumn('orders', 'billing_address');

        $createOrderItems = function (Order $order, Carbon $createdAt) use ($products, $variants): float {
            $orderTotal = 0;

            $itemCount = rand(1, min(5, $products->count()));
            $hasVariants = $variants->isNotEmpty();
            $useVariant = $hasVariants && rand(1, 100) <= 30;

            if ($useVariant && $variants->count() > 0) {
                $selectedVariants = $variants->random(min($itemCount, $variants->count()));
                $selectedVariants = $selectedVariants instanceof ProductAttributeTerm ? collect([$selectedVariants]) : $selectedVariants;

                foreach ($selectedVariants as $variant) {
                    if (!$variant->product) {
                        continue;
                    }

                    $quantity = rand(1, 3);
                    $price = $variant->price > 0
                        ? (float) $variant->price
                        : ($variant->product->price > 0 ? (float) $variant->product->price : rand(80, 600));
                    $itemTotal = $price * $quantity;
                    $orderTotal += $itemTotal;

                    $item = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $variant->product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $itemTotal,
                    ]);

                    $item->created_at = $createdAt;
                    $item->updated_at = $createdAt;
                    $item->save();
                }
            } else {
                $selectedProducts = $products->random($itemCount);
                $selectedProducts = $selectedProducts instanceof Product ? collect([$selectedProducts]) : $selectedProducts;

                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, 3);
                    $price = $product->price > 0 ? (float) $product->price : rand(80, 600);
                    $itemTotal = $price * $quantity;
                    $orderTotal += $itemTotal;

                    $item = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $itemTotal,
                    ]);

                    $item->created_at = $createdAt;
                    $item->updated_at = $createdAt;
                    $item->save();
                }
            }

            return $orderTotal;
        };

        $memberOrdersToCreate = 50;
        $guestOrdersToCreate = 10;

        for ($i = 0; $i < $memberOrdersToCreate; $i++) {
            $user = $users->random();

            $orderCount++;
            $status = Arr::random($statusPool);
            $orderNumber = 'ORD-' . str_pad($orderCount, 6, '0', STR_PAD_LEFT);

            while (Order::where('order_number', $orderNumber)->exists()) {
                $orderCount++;
                $orderNumber = 'ORD-' . str_pad($orderCount, 6, '0', STR_PAD_LEFT);
            }

            $createdAt = Carbon::now()
                ->subMonths(rand(0, 11))
                ->subDays(rand(0, 27))
                ->setTime(rand(8, 20), rand(0, 59), rand(0, 59));

            $shippingAddress = $userAddresses[$user->id]['shipping'];
            $billingAddress = $userAddresses[$user->id]['billing'];

            $orderData = [
                'user_id' => $user->id,
                'guest_id' => null,
                'order_number' => $orderNumber,
                'status' => $status,
                'notes' => Arr::random($orderNotes),
                'total' => 0,
                'shipping_address_id' => $shippingAddress->id,
                'billing_address_id' => $billingAddress->id,
            ];

            if ($hasShippingColumn) {
                $orderData['shipping_address'] = $shippingAddress->address;
            }

            if ($hasBillingColumn) {
                $orderData['billing_address'] = $billingAddress->address;
            }

            $order = Order::create($orderData);
            $orderTotal = $createOrderItems($order, $createdAt);

            $order->total = $orderTotal;
            $order->created_at = $createdAt;
            $order->updated_at = (clone $createdAt)->addMinutes(rand(10, 240));
            $order->save();
        }

        // Misafir siparişleri
        $guestNames = [
            'Misafir Müşteri',
            'Ziyaretçi Kullanıcı',
            'Anonim Kullanıcı',
            'Online Misafir',
            'Misafir Alıcı',
            'Deneme Kullanıcı',
            'Geçici Müşteri',
            'Hızlı Sipariş',
            'Yeni Misafir',
            'Konuk Kullanıcı',
        ];

        for ($i = 0; $i < $guestOrdersToCreate; $i++) {
            $guestId = 'guest_' . Str::uuid();
            $guestName = $guestNames[$i % count($guestNames)];

            $shippingData = $generateAddressData($guestName);
            $billingData = $generateAddressData($guestName);

            $shippingAddress = UserAddress::create(array_merge($shippingData, [
                'user_id' => null,
                'guest_id' => $guestId,
                'type' => 'shipping',
                'title' => 'Teslimat Adresi',
                'is_default' => true,
            ]));

            $billingAddress = UserAddress::create(array_merge($billingData, [
                'user_id' => null,
                'guest_id' => $guestId,
                'type' => 'billing',
                'title' => 'Fatura Adresi',
                'is_default' => false,
            ]));

            $orderCount++;
            $status = Arr::random($statusPool);
            $orderNumber = 'ORD-' . str_pad($orderCount, 6, '0', STR_PAD_LEFT);

            while (Order::where('order_number', $orderNumber)->exists()) {
                $orderCount++;
                $orderNumber = 'ORD-' . str_pad($orderCount, 6, '0', STR_PAD_LEFT);
            }

            $createdAt = Carbon::now()
                ->subMonths(rand(0, 11))
                ->subDays(rand(0, 27))
                ->setTime(rand(8, 20), rand(0, 59), rand(0, 59));

            $orderData = [
                'user_id' => null,
                'guest_id' => $guestId,
                'order_number' => $orderNumber,
                'status' => $status,
                'notes' => Arr::random($orderNotes),
                'total' => 0,
                'shipping_address_id' => $shippingAddress->id,
                'billing_address_id' => $billingAddress->id,
            ];

            if ($hasShippingColumn) {
                $orderData['shipping_address'] = $shippingAddress->address;
            }

            if ($hasBillingColumn) {
                $orderData['billing_address'] = $billingAddress->address;
            }

            $order = Order::create($orderData);
            $orderTotal = $createOrderItems($order, $createdAt);

            $order->total = $orderTotal;
            $order->created_at = $createdAt;
            $order->updated_at = (clone $createdAt)->addMinutes(rand(10, 240));
            $order->save();
        }

        $this->command->info("{$memberOrdersToCreate} adet üye siparişi ve {$guestOrdersToCreate} adet misafir siparişi başarıyla oluşturuldu.");
    }
}
