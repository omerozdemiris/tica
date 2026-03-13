<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationMailService;
use App\Services\SmsService;
use App\Services\HashLinkService;

class NotificationService
{
    public static function handleProductUpdate(Product $product, array $oldData, array $newData)
    {
        $store = Store::first();
        if (!$store) return;

        if ($store->price_notification) {
            self::checkPriceChange($product, $oldData, $newData);
        }

        if ($store->stock_notification) {
            self::checkStockChange($product, $oldData, $newData);
        }
    }

    private static function checkPriceChange(Product $product, array $oldData, array $newData)
    {
        $oldPrice = (float)($oldData['price'] ?? 0);
        $newPrice = (float)($newData['price'] ?? 0);
        $oldDiscount = (float)($oldData['discount_price'] ?? 0);
        $newDiscount = (float)($newData['discount_price'] ?? 0);

        $shouldNotify = false;

        if ($newDiscount > 0) {
            if ($oldDiscount == 0 || $newDiscount < $oldDiscount) {
                $shouldNotify = true;
            }
        } elseif ($newPrice < $oldPrice) {
            $shouldNotify = true;
        }

        if ($shouldNotify) {
            $currentPrice = $newDiscount > 0 ? $newDiscount : $newPrice;
            $oldRefPrice = $oldDiscount > 0 ? $oldDiscount : $oldPrice;

            if ($oldRefPrice > 0) {
                $dropPercent = round((($oldRefPrice - $currentPrice) / $oldRefPrice) * 100);
                $title = "Fiyat Düştü! %{$dropPercent} İndirim";
                $text = "{$product->title} ürününde büyük indirim! Yeni fiyat: " . number_format($currentPrice, 2, ',', '.') . " TL. Hemen incele!";

                self::createGlobalProductNotification($product, $title, $text, 'event', 'price', $currentPrice);
            }
        }
    }

    private static function checkStockChange(Product $product, array $oldData, array $newData)
    {
        $oldStock = $oldData['stock'] ?? null;
        $newStock = $newData['stock'] ?? null;
        $oldStockType = (int)($oldData['stock_type'] ?? 0);
        $newStockType = (int)($newData['stock_type'] ?? 0);

        if ($oldStockType === 1 && $newStockType === 0 && $newStock > 0) {
            $title = "Stok Güncellendi";
            $text = "{$product->title} için {$newStock} adet kaldı, hemen incele!";
            self::createGlobalProductNotification($product, $title, $text, 'event', 'stock', null, $newStock);
        } elseif (($oldStock === 0 || $oldStock === null && $oldStockType === 0) && $newStock > 0) {
            $title = "Stoklar Yenilendi";
            $text = "{$product->title} stokları yenilendi, Hemen Gözat!";
            self::createGlobalProductNotification($product, $title, $text, 'event', 'stock', null, $newStock);
        }
    }

    private static function createGlobalProductNotification(Product $product, string $title, string $text, string $type, ?string $permissionType = null, $price = null, $stock = null)
    {
        $users = User::where('role', 0)->get();

        foreach ($users as $user) {
            // Web Notification
            if ($user->hasNotificationPermission('web', $permissionType)) {
                Notification::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'title' => $title,
                    'text' => $text,
                    'type' => $type,
                    'price' => $price,
                    'stock' => $stock,
                ]);
            }

            // Email Notification
            if ($user->hasNotificationPermission('email', $permissionType)) {
                $shortLink = HashLinkService::createShortLink(route('products.show', [$product->id, $product->slug]), 'email', 'product', $product->id);

                if ($permissionType === 'price') {
                    NotificationMailService::sendPriceDropEmail($user, $product, $title, $text, $shortLink);
                } elseif ($permissionType === 'stock') {
                    NotificationMailService::sendStockUpdateEmail($user, $product, $title, $text, $shortLink);
                }
            }

            // SMS Notification
            if ($user->phone && $user->hasNotificationPermission('sms', $permissionType)) {
                $smsLink = HashLinkService::createShortLink(route('products.show', [$product->id, $product->slug]), 'sms', 'product', $product->id);
                $smsText = $text . " " . $smsLink;
                (new SmsService())->sendSms($user->phone, $smsText);
            }
        }
    }

    public static function createManualNotification(array $userIds, array $contextIds, string $contextType, string $type, string $title)
    {
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user && $user->hasNotificationPermission('web')) {
                Notification::create([
                    'user_id' => $userId,
                    'title' => $title,
                    'text' => "Yeni bir bildiriminiz var.",
                    'context_ids' => $contextIds,
                    'context_type' => $contextType,
                    'type' => $type,
                ]);
            }
        }
    }

    public static function createDirectNotification(int $userId, string $title, string $text, string $type = 'event', array $contextIds = [], string $contextType = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'text' => $text,
            'type' => $type,
            'context_ids' => $contextIds,
            'context_type' => $contextType,
        ]);
    }

    public static function createCartReminderNotification(int $userId, string $title, string $text, string $type = 'remind', array $contextIds = [], string $contextType = 'product')
    {
        $store = Store::first();
        if (!$store || !$store->cart_reminder) return;

        $user = User::find($userId);
        if (!$user) return;

        $cart = Cart::where('user_id', $user->id)->first();
        if (!$cart || $cart->total_items == 0) return;

        $currentProductIds = $cart->items->pluck('product_id')->toArray();
        $remindTimeHours = (int)($store->cart_remind_time ?? 0);

        $lastNotification = Notification::where('user_id', $userId)
            ->where('type', 'remind')
            ->latest()
            ->first();

        $shouldSend = false;

        if (!$lastNotification) {
            $shouldSend = true;
        } else {
            $lastSentAt = $lastNotification->created_at;
            $minutesSinceLastSent = $lastSentAt->diffInMinutes(now());

            if ($minutesSinceLastSent >= $remindTimeHours) {
                $shouldSend = true;
            } else {
                $lastContextIds = $lastNotification->context_ids ?? [];
                $newItemsAdded = count(array_diff($currentProductIds, $lastContextIds)) > 0;
                if ($newItemsAdded) {
                    $shouldSend = true;
                }
            }
        }

        if ($shouldSend) {
            $notification = Notification::create([
                'user_id' => $userId,
                'title' => $store->cart_remind_message ?? $title,
                'text' => $text,
                'type' => 'remind',
                'context_ids' => $currentProductIds,
                'context_type' => 'product',
            ]);
            if ($user->email) {
                NotificationMailService::sendCartReminderEmail($user, $notification->title, $notification->text);
            }

            return $notification;
        }

        return null;
    }

    public static function processAllCartReminders()
    {
        $store = Store::first();
        if (!$store || !$store->cart_reminder) return;

        $carts = Cart::where('total_items', '>', 0)
            ->whereNotNull('user_id')
            ->get();

        foreach ($carts as $cart) {
            self::createCartReminderNotification(
                $cart->user_id,
                $store->cart_remind_message ?? 'Sepetinizde Ürünler Var!',
                'Sepetinizdeki ürünleri kaçırmayın, hemen kontrol edin.',
                'remind'
            );
        }
    }
}
