<?php

namespace App\Services;

use App\Mail\GenericNotificationMail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationMailService
{
    /**
     * Send price drop notification via email.
     */
    public static function sendPriceDropEmail(User $user, Product $product, string $title, string $text, $shortLink = null)
    {
        try {
            Mail::to($user->email)->send(new GenericNotificationMail($title, $text, $product, $shortLink));
        } catch (\Exception $e) {
            Log::error("Fiyat düşüş maili gönderilemedi ({$user->email}): " . $e->getMessage());
        }
    }

    /**
     * Send stock update notification via email.
     */
    public static function sendStockUpdateEmail(User $user, Product $product, string $title, string $text, $shortLink = null)
    {
        try {
            Mail::to($user->email)->send(new GenericNotificationMail($title, $text, $product, $shortLink));
        } catch (\Exception $e) {
            Log::error("Stok güncelleme maili gönderilemedi ({$user->email}): " . $e->getMessage());
        }
    }

    /**
     * Send generic notification via email.
     */
    public static function sendGenericEmail(User $user, string $title, string $text)
    {
        try {
            Mail::to($user->email)->send(new GenericNotificationMail($title, $text));
        } catch (\Exception $e) {
            Log::error("Genel bildirim maili gönderilemedi ({$user->email}): " . $e->getMessage());
        }
    }

    /**
     * Send cart reminder notification via email.
     */
    public static function sendCartReminderEmail(User $user, string $title, string $text)
    {
        try {
            Mail::to($user->email)->send(new GenericNotificationMail($title, $text));
        } catch (\Exception $e) {
            Log::error("Sepet hatırlatma maili gönderilemedi ({$user->email}): " . $e->getMessage());
        }
    }
}
