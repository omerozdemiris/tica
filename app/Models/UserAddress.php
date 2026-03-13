<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_id',
        'type',
        'title',
        'fullname',
        'tc',
        'phone',
        'country',
        'city_id',
        'state_id',
        'city',
        'state',
        'zip',
        'address',
        'email',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ordersAsShipping(): HasMany
    {
        return $this->hasMany(Order::class, 'shipping_address_id');
    }

    public function ordersAsBilling(): HasMany
    {
        return $this->hasMany(Order::class, 'billing_address_id');
    }

    public function cityRelation(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function stateRelation(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }
}
