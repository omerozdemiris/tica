<?php

namespace App\Services;

use App\Models\Visitor;
use Illuminate\Support\Facades\Request;

class TrafficLogger
{
    /**
     * Log a visit to the system.
     */
    public static function logVisit(?string $targetType = 'home', ?string $target = null, ?string $source = null)
    {
        if (!$source) {
            $referer = Request::header('referer');
            if (!$referer) {
                $source = 'direct';
            } elseif (strpos($referer, 'google') !== false) {
                $source = 'google';
            } else {
                $source = 'other';
            }
        }

        $userAgent = Request::header('User-Agent');
        $isMobile = preg_match('/Mobile|Android|BlackBerry|iPhone|iPad|iPod|IEMobile|Opera Mini/i', $userAgent);
        $device = $isMobile ? 'mobile' : 'desktop';

        $platform = 'other';
        if (preg_match('/android/i', $userAgent)) {
            $platform = 'android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'ios';
        } elseif (preg_match('/windows|win32/i', $userAgent)) {
            $platform = 'windows';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macos';
        }

        $ip = Request::ip();
        $exists = Visitor::where('ip_address', $ip)
            ->where('device', $device)
            ->where('platform', $platform)
            ->where('source', $source)
            ->where('target_type', $targetType)
            ->where('target', $target)
            ->where('visited_at', '>=', now()->subMinutes(3))
            ->exists();

        if ($exists) {
            return;
        }

        Visitor::create([
            'ip_address' => $ip,
            'device' => $device,
            'platform' => $platform,
            'source' => $source,
            'target_type' => $targetType,
            'target' => $target,
            'visited_at' => now(),
        ]);
    }
}
