<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tracking_link',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function shippings()
    {
        return $this->hasMany(Shipping::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, Shipping::class, 'shipping_company_id', 'id', 'id', 'order_id')->where('status', 'completed');
    }
}
