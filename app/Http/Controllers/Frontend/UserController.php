<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use App\Models\UserAddress;
use App\Models\City;
use App\Models\State;
use App\Models\Theme;
use App\Models\NotificationPermission;
use App\Services\Logs\CustomerLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function dashboard(): View
    {
        $theme = Theme::first();
        $user = Auth::user();

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $statistics = (object) [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'total_spent' => Order::where('user_id', $user->id)->sum('total'),
            'completed_orders' => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
        ];

        $store = Store::first();
        $meta = (object) [
            'title' => 'Hesabım | ' . ($store->meta_title ?? config('app.name')),
        ];

        $data = (object) [
            'user' => $user,
            'orders' => $orders,
            'statistics' => $statistics,
            'meta' => $meta,
        ];

        return view($theme->thene . '.pages.user.dashboard', [
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    public function orders(): View
    {
        $theme = Theme::first();
        $user = Auth::user();

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->with('returns')
            ->latest()
            ->paginate(10);

        $store = Store::first();
        $meta = (object) [
            'title' => 'Siparişlerim | ' . ($store->meta_title ?? config('app.name')),
        ];

        $data = (object) [
            'orders' => $orders,
            'meta' => $meta,
        ];

        return view($theme->thene . '.pages.user.orders', [
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    public function orderShow(Order $order): View|RedirectResponse
    {
        $theme = Theme::first();
        $user = Auth::user();
        if ($order->user_id !== $user->id) {
            return redirect()->route('user.orders')->withErrors('Sipariş bulunamadı.');
        }

        $order->load(['items.product', 'items.variant', 'shippingAddress', 'billingAddress', 'returns.items.orderItem']);

        $store = Store::first();
        $meta = (object) [
            'title' => 'Sipariş #' . $order->order_number . ' | ' . ($store->meta_title ?? config('app.name')),
        ];

        $data = (object) [
            'order' => $order,
            'meta' => $meta,
        ];

        return view($theme->thene . '.pages.user.order-show', [
            'data' => $data,
            'meta' => $meta,
            'store' => $store,
        ]);
    }

    public function profile(): View
    {
        $theme = Theme::first();
        $user = Auth::user();
        $store = Store::first();
        $meta = (object) [
            'title' => 'Profil Bilgileri | ' . ($store->meta_title ?? config('app.name')),
        ];

        $data = (object) [
            'user' => $user,
            'meta' => $meta,
        ];

        return view($theme->thene . '.pages.user.profile', [
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $before = $user->toArray();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? $user->phone;

        if (!empty($validated['password'])) {
            if (Hash::check($validated['password'], $user->password)) {
                return back()->withErrors(['password' => 'Yeni şifreniz eski şifrenizle aynı olamaz.']);
            }
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $after = $user->fresh()->toArray();
        app(CustomerLogService::class)->log('Profil Bilgileri Güncellendi', $before, $after);

        $notificationTypes = ['web', 'email', 'sms'];
        $permissionTypes = ['price', 'stock'];
        $notificationChanges = [];

        foreach ($notificationTypes as $notifType) {
            foreach ($permissionTypes as $permType) {
                $status = $request->boolean("notification_{$permType}_{$notifType}");
                $permission = NotificationPermission::where([
                    'user_id' => $user->id,
                    'notification_type' => $notifType,
                    'permission_type' => $permType
                ])->first();

                $oldStatus = $permission ? (bool)$permission->status : false;
                if ($oldStatus !== $status) {
                    $notificationChanges[] = [
                        'type' => $notifType,
                        'permission' => $permType,
                        'before' => $oldStatus,
                        'after' => $status
                    ];
                }

                NotificationPermission::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'notification_type' => $notifType,
                        'permission_type' => $permType
                    ],
                    ['status' => $status]
                );
            }
        }

        if (!empty($notificationChanges)) {
            app(CustomerLogService::class)->log('Bildirim Ayarları Güncellendi', null, $notificationChanges);
        }

        return back()->with('status', 'Profil bilgileriniz güncellendi.');
    }

    public function addresses(): View
    {
        $theme = Theme::first();
        $user = Auth::user();
        $addresses = $user->addresses()->latest()->get();

        $store = Store::first();
        $meta = (object) [
            'title' => 'Adreslerim | ' . ($store->meta_title ?? config('app.name')),
        ];

        $data = (object) [
            'addresses' => $addresses,
            'meta' => $meta,
        ];

        return view($theme->thene . '.pages.user.addresses', [
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $store = Store::first();

        $validated = $request->validate([
            'address_id' => ['nullable', 'integer', Rule::exists('user_addresses', 'id')->where('user_id', $user->id)],
            'title' => ['required', 'string', 'max:255'],
            'fullname' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'tc' => ['nullable', 'integer', 'digits:11'],
            'country' => ['nullable', 'string', 'max:150'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
            'zip' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($store->tc_required && empty($validated['tc'])) {
            return back()->with(['status', 'TC kimlik numarası zorunludur.']);
        }

        $cityModel = null;
        $stateModel = null;
        if (!empty($validated['city_id'])) {
            $cityModel = City::find($validated['city_id']);
        }
        if (!empty($validated['state_id'])) {
            $stateModel = State::find($validated['state_id']);
        }

        $payload = [
            'user_id' => $user->id,
            'guest_id' => null,
            'type' => 'shipping',
            'title' => $validated['title'],
            'fullname' => $validated['fullname'],
            'tc' => $validated['tc'] ?? null,
            'phone' => $validated['phone'] ?? $user->phone,
            'country' => $validated['country'] ?? 'Türkiye',
            'city_id' => $validated['city_id'] ?? null,
            'state_id' => $validated['state_id'] ?? null,
            'city' => $cityModel?->name ?? $request->input('city_name'),
            'state' => $stateModel?->name ?? $request->input('state_name'),
            'zip' => $validated['zip'] ?? null,
            'address' => $validated['address'],
            'email' => $validated['email'] ?? $user->email,
            'is_default' => $request->boolean('is_default'),
        ];


        if (!empty($validated['address_id'])) {
            $address = UserAddress::where('user_id', $user->id)->findOrFail($validated['address_id']);
            $before = $address->toArray();
            $address->update($payload);
            app(CustomerLogService::class)->log('Adres Güncellendi', $before, $address->fresh()->toArray());
        } else {
            $address = UserAddress::create($payload);
            app(CustomerLogService::class)->log('Adres Eklendi', null, $address->toArray());
        }

        if ($payload['is_default']) {
            UserAddress::where('user_id', $user->id)
                ->where('id', '!=', $validated['address_id'] ?? 0)
                ->update(['is_default' => false]);
        }

        return back()->with('status', 'Adres bilgileriniz kaydedildi.');
    }
}
