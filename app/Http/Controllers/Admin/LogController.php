<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\CustomerLog;
use App\Models\User;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function adminLogs(Request $request)
    {
        $query = AdminLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('type')) {
            $query->where('type', 'like', '%' . $request->type . '%');
        }

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();
        $users = User::whereIn('role', [1, 2])->get();

        return view('admin.pages.logs.admin', compact('logs', 'users'));
    }

    public function customerLogs(Request $request)
    {
        $query = CustomerLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('type')) {
            $query->where('type', 'like', '%' . $request->type . '%');
        }

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();
        $users = User::where('role', 0)->get();

        return view('admin.pages.logs.customer', compact('logs', 'users'));
    }
}
