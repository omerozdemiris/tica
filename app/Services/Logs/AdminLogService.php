<?php

namespace App\Services\Logs;

use App\Models\AdminLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AdminLogService
{
    public function log(string $type, ?array $before = null, ?array $after = null)
    {
        $userId = session('admin_user_id') ?: Auth::id();

        if (!$userId) {
            return;
        }

        AdminLog::create([
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
