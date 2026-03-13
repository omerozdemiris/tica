<?php

namespace App\Http\Controllers\Admin;

use App\Models\Notification;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Services\NotificationService;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function webIndex(Request $request)
    {
        $query = User::where('role', 0)
            ->whereHas('notificationPermissions', function ($q) {
                $q->where('notification_type', 'web')->where('status', true);
            });

        if ($request->filled('keyword')) {
            $term = $request->keyword;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%');
            });
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        return view('admin.pages.notifications.web.index', compact('customers'));
    }

    public function webHistory()
    {
        $notifications = Notification::with('user', 'product')->latest()->paginate(20);
        return view('admin.pages.notifications.web.history', compact('notifications'));
    }

    public function sendWebNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'context_type' => ['required', 'string', 'in:product,category'],
            'context_ids' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }

        NotificationService::createManualNotification(
            $request->user_ids,
            $request->context_ids ?? [],
            $request->context_type,
            'custom',
            $request->title
        );

        app(AdminLogService::class)->log('Manuel Bildirim Gönderildi', null, [
            'user_ids' => $request->user_ids,
            'title' => $request->title,
            'context_type' => $request->context_type,
            'context_ids' => $request->context_ids
        ]);

        return $this->jsonSuccess('Bildirimler başarıyla gönderildi.');
    }

    public function webClearAll()
    {
        Notification::truncate();

        app(AdminLogService::class)->log('Tüm Web Bildirimleri Silindi (Temizlendi)', null, null);

        return response()->json(['msg' => 'Tüm bildirimler başarıyla silindi.', 'code' => 1]);
    }

    public function webDeleteSelected(Request $request)
    {
        $ids = $request->ids;
        if (!empty($ids)) {
            Notification::whereIn('id', $ids)->delete();

            app(AdminLogService::class)->log('Seçilen Web Bildirimleri Silindi', null, ['ids' => $ids]);

            return response()->json(['msg' => 'Seçilen bildirimler başarıyla silindi.', 'code' => 1]);
        }
        return response()->json(['msg' => 'Hiçbir bildirim seçilmedi.', 'code' => 0], 400);
    }
}
