<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bank;
use App\Models\Store;
use App\Models\ShippingCompany;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreSettingsController extends Controller
{
    public function index()
    {
        $storeSettings = Store::first() ?? new Store();
        return view('admin.pages.settings.store.index', [
            'storeSettings' => $storeSettings,
            'banks' => Bank::orderBy('created_at')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sell_enabled' => ['nullable', 'boolean'],
            'auth_required' => ['nullable', 'boolean'],
            'maintenance' => ['nullable', 'boolean'],
            'tc_required' => ['nullable', 'boolean'],
            'phone_required' => ['nullable', 'boolean'],
            'auto_stock' => ['nullable', 'boolean'],
            'tax_enabled' => ['nullable', 'boolean'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:tax_enabled,1'],
            'notify_order_complete' => ['nullable', 'boolean'],
            'verify_required' => ['nullable', 'boolean'],
            'allow_wire_payments' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255', 'special_characters'],
            'meta_description' => ['nullable', 'string'],
            'about' => ['nullable', 'string',],
            'privacy_policy' => ['nullable', 'string',],
            'cookie_policy' => ['nullable', 'string',],
            'distance_selling' => ['nullable', 'string',],
            'show_categories' => ['nullable', 'boolean'],
            'show_new_products' => ['nullable', 'boolean'],
            'shipping_price' => ['nullable', 'numeric', 'min:0'],
            'shipping_price_limit' => ['nullable', 'numeric', 'min:0'],
            'price_notification' => ['nullable', 'boolean'],
            'stock_notification' => ['nullable', 'boolean'],
            'cart_reminder' => ['nullable', 'boolean'],
            'cart_remind_time' => ['nullable', 'integer', 'min:1'],
            'cart_remind_message' => ['required_if:cart_reminder,1', 'string', 'max:255'],
            'facebook_meta_code' => ['nullable', 'string'],
            'google_tag_manager' => ['nullable', 'string'],
            'google_ads' => ['nullable', 'string'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }

        $data = $validator->validated();
        foreach (
            [
                'sell_enabled',
                'auth_required',
                'tc_required',
                'phone_required',
                'maintenance',
                'auto_stock',
                'tax_enabled',
                'notify_order_complete',
                'verify_required',
                'allow_wire_payments',
                'show_categories',
                'show_new_products',
                'price_notification',
                'stock_notification',
                'cart_reminder',
                'cart_remind_time',
            ] as $flag
        ) {
            $data[$flag] = (bool)($data[$flag] ?? false);
        }

        $row = Store::first() ?? new Store();
        $before = $row->toArray();
        $row->fill($data);
        $row->save();

        app(AdminLogService::class)->log('Mağaza Ayarları Güncellendi', $before, $row->fresh()->toArray());

        return $this->jsonSuccess('Mağaza ayarları güncellendi');
    }
}
