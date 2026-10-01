<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_id',
        'order_number',
        'status',
        'method',
        'total',
        'shipping_address',
        'billing_address',
        'notes',
        'shipping_address_id',
        'is_paid',
        'billing_address_id',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function product()
    {
        return $this->hasManyThrough(Product::class, OrderItem::class, 'order_id', 'id', 'id', 'product_id');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(UserAddress::class, 'shipping_address_id');
    }

    public function billingAddress()
    {
        return $this->belongsTo(UserAddress::class, 'billing_address_id');
    }

    public function shipping()
    {
        return $this->hasOne(Shipping::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function latestReturn()
    {
        return $this->hasOne(ReturnRequest::class)->latestOfMany();
    }

    public function getCustomerNameAttribute()
    {
        return $this->attributes['customer_name']
            ?? $this->shippingAddress?->fullname
            ?? $this->billingAddress?->fullname
            ?? $this->shipping?->customer_name
            ?? $this->user?->name;
    }

    public function getCustomerEmailAttribute()
    {
        if (!empty($this->attributes['customer_email'])) {
            return $this->attributes['customer_email'];
        }
        $address = $this->shippingAddress ?? $this->shipping;
        if ($address && !empty($address->email)) {
            return $address->email;
        }
        return $this->user?->email;
    }

    public function getCustomerPhoneAttribute()
    {
        return $this->attributes['customer_phone']
            ?? $this->shippingAddress?->phone
            ?? $this->billingAddress?->phone
            ?? $this->shipping?->customer_phone;
    }
}
