<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Notification;
use App\Models\Product;
use App\Models\Category;
use App\Models\Theme;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    private function getTheme()
    {
        return Theme::first()->thene ?? 'frontend';
    }

    public function index()
    {
        $template = $this->getTheme();
        $notifications = Auth::user()->notifications()->paginate(15);
        return view($template . '.pages.user.notifications', compact('notifications', 'template'));
    }

    public function show($id)
    {
        $template = $this->getTheme();
        $notification = Auth::user()->notifications()->findOrFail($id);
        
        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        $contextData = [];
        if ($notification->context_type === 'product' && !empty($notification->context_ids)) {
            $contextData = Product::whereIn('id', $notification->context_ids)->get();
        } elseif ($notification->context_type === 'category' && !empty($notification->context_ids)) {
            $contextData = Category::whereIn('id', $notification->context_ids)->get();
        } elseif ($notification->product_id) {
            $contextData = Product::where('id', $notification->product_id)->get();
        }

        return view($template . '.pages.user.notification_detail', compact('notification', 'contextData', 'template'));
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['msg' => 'Tüm bildirimler okundu olarak işaretlendi.']);
    }

    public function clearAll()
    {
        Auth::user()->notifications()->delete();
        return response()->json(['msg' => 'Tüm bildirimler temizlendi.']);
    }

    public function getLatest()
    {
        $notifications = Auth::user()->notifications()->limit(5)->get();
        $unreadCount = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
}
