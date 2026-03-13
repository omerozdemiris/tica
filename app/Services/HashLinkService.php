<?php

namespace App\Services;

use App\Models\ShortLink;
use Illuminate\Support\Str;

class HashLinkService
{
    /**
     * Create a shortened link and return the full URL.
     */
    public static function createShortLink(string $originalUrl, string $visitorType, ?string $targetType = null, ?int $targetId = null): string
    {
        $hash = Str::random(6);
        
        // Ensure unique hash
        while (ShortLink::where('hash', $hash)->exists()) {
            $hash = Str::random(6);
        }

        ShortLink::create([
            'hash' => $hash,
            'original_url' => $originalUrl,
            'visitor_type' => $visitorType,
            'target_type' => $targetType,
            'target_id' => $targetId,
        ]);

        return env('APP_URL') . '/' . $hash;
    }

    /**
     * Resolve a hash to its original data.
     */
    public static function resolveHash(string $hash)
    {
        return ShortLink::where('hash', $hash)->first();
    }
}

