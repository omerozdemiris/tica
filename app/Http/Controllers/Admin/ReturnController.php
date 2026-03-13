<?php

namespace App\Http\Controllers\Admin;

use App\Models\ReturnRequest;
use App\Models\Setting;
use App\Services\Logs\AdminLogService;
use App\Services\SmsService;
use App\Mail\NotifyAdminReturnMail;
use App\Mail\ReturnStatusMail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ReturnController extends Controller
{
    public function index(Request $request, ?string $status = null): View
    {
        $query = ReturnRequest::query()->with(['order.user', 'items.orderItem.product']);

        $filters = [
            'order_number' => $request->string('order_number')->toString(),
            'customer' => $request->string('customer')->toString(),
            'email' => $request->string('email')->toString(),
            'date_start' => $request->date('date_start'),
            'date_end' => $request->date('date_end'),
        ];

        if ($status) {
            $query->where('status', $status);
        }

        if ($filters['order_number']) {
            $query->where('order_number', 'like', '%' . $filters['order_number'] . '%');
        }

        if ($filters['customer']) {
            $query->where('customer_name', 'like', '%' . $filters['customer'] . '%');
        }

        if ($filters['email']) {
            $query->where('customer_email', 'like', '%' . $filters['email'] . '%');
        }

        if ($filters['date_start']) {
            $query->whereDate('created_at', '>=', $filters['date_start']);
        }

        if ($filters['date_end']) {
            $query->whereDate('created_at', '<=', $filters['date_end']);
        }

        $returns = $query->latest()->paginate(20)->withQueryString();

        return view('admin.pages.returns.index', [
            'returns' => $returns,
            'filters' => $filters,
            'activeStatus' => $status,
        ]);
    }

    public function pending(Request $request): View
    {
        return $this->index($request, 'pending');
    }

    public function processed(Request $request): View
    {
        return $this->index($request, 'processed');
    }

    public function show(ReturnRequest $return): View
    {
        $return->load(['order.user', 'order.items.product', 'items.orderItem.product']);

        return view('admin.pages.returns.show', [
            'return' => $return,
        ]);
    }

    public function update(ReturnRequest $return, Request $request): RedirectResponse
    {
        $before = $return->toArray();
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'processed', 'rejected'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $return->update($validated);

        app(AdminLogService::class)->log('İade Talebi Güncellendi', $before, $return->fresh()->toArray());

        $this->sendReturnStatusSms($return, $validated['status']);

        return back()->with('status', 'İade talebi güncellendi.');
    }

    public function status(Request $request, string $status): RedirectResponse
    {
        $return = ReturnRequest::findOrFail($request->input('return_id'));
        $before = $return->toArray();

        if (!in_array($status, ['pending', 'processed', 'rejected'])) {
            return redirect()->route('admin.returns.index');
        }

        $return->update([
            'status' => $status,
            'notes' => $request->input('notes')
        ]);

        app(AdminLogService::class)->log('İade Durumu Güncellendi', $before, $return->fresh()->toArray());

        $this->sendCustomerNotification($return, $status);
        $this->sendReturnStatusSms($return, $status);

        return redirect()->route('admin.returns.index')->with('status', 'İade durumu güncellendi.');
    }

    protected function sendAdminNotification(ReturnRequest $return, string $status): void
    {
        $settings = Setting::first();
        $notifyMail = $settings?->notify_mail;

        if (!$notifyMail) {
            return;
        }

        try {
            Mail::to($notifyMail)->send(new NotifyAdminReturnMail($return, $status));
        } catch (\Exception $e) {
            Log::warning('Admin iade bildirimi gönderilemedi', [
                'return_id' => $return->id,
                'notify_mail' => $notifyMail,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendCustomerNotification(ReturnRequest $return, string $status): void
    {
        $email = $return->order->user?->email;
        if (!$email) {
            return;
        }

        try {
            Mail::to($email)->send(new ReturnStatusMail($return, $status));
        } catch (\Exception $e) {
            Log::error('Müşteri iade bildirimi gönderilemedi', [
                'return_id' => $return->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendReturnStatusSms(ReturnRequest $return, string $status): void
    {
        $phone = $return->customer_phone;
        if (!$phone) {
            return;
        }

        $message = match ($status) {
            'pending' => "Sayın {$return->customer_name}, iade talebiniz (#{$return->order_number}) incelenmektedir.",
            'processed' => "Sayın {$return->customer_name}, iade talebiniz (#{$return->order_number}) onaylanmıştır. İade işleminiz tamamlanmıştır.",
            'rejected' => "Sayın {$return->customer_name}, iade talebiniz (#{$return->order_number}) reddedilmiştir. Detaylar için e-postanızı kontrol ediniz.",
            default => null,
        };

        if (!$message) {
            return;
        }

        try {
            app(SmsService::class)->sendSms($phone, $message);
        } catch (\Exception $e) {
            Log::error('Müşteri SMS bildirimi gönderilemedi', [
                'return_id' => $return->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
