<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Term extends Model
{
    protected $fillable = ['attribute_id', 'name', 'value', 'file'];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}


