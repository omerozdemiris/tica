<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckoutSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_oid',
        'cart_id',
        'order_id',
        'user_id',
        'guest_id',
        'shipping_address_id',
        'billing_address_id',
        'amount',
        'currency',
        'cart_snapshot',
        'customer_data',
        'payment_service_token',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'cart_snapshot' => 'array',
        'customer_data' => 'array',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(UserAddress::class, 'shipping_address_id');
    }

    public function billingAddress()
    {
        return $this->belongsTo(UserAddress::class, 'billing_address_id');
    }
}
