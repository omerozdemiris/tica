<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;



class Store extends Model

{

    protected $fillable = [
        'sell_enabled',
        'auth_required',
        'tc_required',
        'phone_required',
        'maintenance',
        'auto_stock',
        'tax_enabled',
        'tax_rate',
        'meta_title',
        'meta_description',
        'about',
        'privacy_policy',
        'cookie_policy',
        'distance_selling',
        'notify_order_complete',
        'verify_required',
        'allow_wire_payments',
        'show_categories',
        'show_new_products',
        'shipping_price',
        'shipping_price_limit',
        'price_notification',
        'stock_notification',
        'cart_reminder',
        'cart_remind_time',
        'cart_remind_message',
        'facebook_meta_code',
        'google_tag_manager',
        'google_ads',
    ];



    protected $casts = [

        'sell_enabled' => 'boolean',

        'auth_required' => 'boolean',

        'tc_required' => 'boolean',

        'phone_required' => 'boolean',

        'maintenance' => 'boolean',

        'auto_stock' => 'boolean',

        'tax_enabled' => 'boolean',

        'tax_rate' => 'decimal:4',

        'notify_order_complete' => 'boolean',

        'verify_required' => 'boolean',

        'allow_wire_payments' => 'boolean',

        'show_categories' => 'boolean',

        'show_new_products' => 'boolean',

        'price_notification' => 'boolean',

        'stock_notification' => 'boolean',

        'cart_reminder' => 'boolean',
        'cart_remind_time' => 'integer',
        'cart_remind_message' => 'string',
    ];



    public function banks(): HasMany

    {

        return $this->hasMany(Bank::class);
    }
}
