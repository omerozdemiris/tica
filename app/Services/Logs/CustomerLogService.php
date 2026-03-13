<?php

namespace App\Services\Logs;

use App\Models\CustomerLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class CustomerLogService
{
    public function log(string $type, ?array $before = null, ?array $after = null)
    {
        $userId = Auth::id();

        if (!$userId) {
            return;
        }

        CustomerLog::create([
            'user_id' => $userId,
            'type' => $type,
            'before' => $before,
            'after' => $after,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
        ]);
    }
}
