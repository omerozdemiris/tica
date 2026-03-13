<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\UserVerificationMail;
use App\Services\NotificationService;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 0);

        $filters = [
            'name' => trim($request->get('name', '')),
            'email' => trim($request->get('email', '')),
            'phone' => trim($request->get('phone', '')),
            'status' => $request->get('status', ''),
        ];

        if ($filters['name'] !== '') {
            $query->where(function ($q) use ($filters) {
                $term = $filters['name'];
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('username', 'like', '%' . $term . '%');
            });
        }

        if ($filters['email'] !== '') {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if ($filters['phone'] !== '') {
            $phoneTerm = $filters['phone'];
            $query->whereHas('orders', function ($orderQuery) use ($phoneTerm) {
                $orderQuery->whereHas('shippingAddress', function ($addressQuery) use ($phoneTerm) {
                    $addressQuery->where('phone', 'like', '%' . $phoneTerm . '%');
                })->orWhereHas('billingAddress', function ($addressQuery) use ($phoneTerm) {
                    $addressQuery->where('phone', 'like', '%' . $phoneTerm . '%');
                })->orWhereHas('shipping', function ($shippingQuery) use ($phoneTerm) {
                    $shippingQuery->where('customer_phone', 'like', '%' . $phoneTerm . '%');
                });
            });
        }

        if ($filters['status'] === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($filters['status'] === 'pending') {
            $query->whereNull('email_verified_at');
        } else {
            $filters['status'] = '';
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        return view('admin.pages.customers.index', [
            'customers' => $customers,
            'filters' => $filters,
        ]);
    }

    public function show(int $id)
    {
        $customer = User::where('role', 0)->findOrFail($id);
        $orders = \App\Models\Order::where('user_id', $customer->id)
            ->latest()
            ->limit(10)
            ->get();

        $cart = \App\Models\Cart::with(['items.product', 'items.variant'])
            ->where('user_id', $customer->id)
            ->latest('updated_at')
            ->first();

        $hasCartItems = $cart && $cart->items->isNotEmpty();
        $cartItemCount = $hasCartItems ? $cart->items->sum('quantity') : 0;
        $cartTotal = $hasCartItems ? $cart->total_price : 0;

        $purchaseChartData = [
            'labels' => [],
            'values' => []
        ];

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            $purchaseChartData['labels'][] = $date->format('M');
            $purchaseChartData['values'][] = \App\Models\Order::where('user_id', $customer->id)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        return view('admin.pages.customers.show', compact(
            'customer',
            'orders',
            'cart',
            'hasCartItems',
            'cartItemCount',
            'cartTotal',
            'purchaseChartData'
        ));
    }

    public function update(Request $request, int $id)
    {
        $customer = User::where('role', 0)->findOrFail($id);
        $before = $customer->toArray();
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'special_characters'],
            'email' => ['required', 'string', 'max:255', 'unique:users,email,' . $customer->id, 'special_characters'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $customer->update($validator->validated());

        app(AdminLogService::class)->log('Müşteri Bilgileri Güncellendi', $before, $customer->fresh()->toArray());

        return $this->jsonSuccess('Müşteri bilgileri güncellendi');
    }

    public function sendVerification(Request $request, int $id)
    {
        $customer = User::where('role', 0)->findOrFail($id);

        app(AdminLogService::class)->log('Müşteriye Doğrulama E-postası Gönderildi', null, ['customer_id' => $customer->id, 'email' => $customer->email]);

        if (empty($customer->email)) {
            return $this->jsonError('Müşterinin e-posta adresi bulunamadı.', 422);
        }

        if ($customer->email_verified_at) {
            return $this->jsonError('Müşteri e-postası zaten doğrulanmış.', 422);
        }

        $hash = sha1($customer->email);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addDay(),
            [
                'id' => $customer->id,
                'hash' => $hash
            ]
        );

        Mail::to($customer->email)->send(new UserVerificationMail($customer, $verificationUrl));

        return $this->jsonSuccess('Doğrulama e-postası gönderildi.');
    }

    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:custom,event'],
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
            $request->type,
            $request->title
        );

        return $this->jsonSuccess('Bildirimler başarıyla gönderildi.');
    }
}
