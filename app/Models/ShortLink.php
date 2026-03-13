<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortLink extends Model
{
    protected $fillable = ['hash', 'original_url', 'visitor_type', 'target_type', 'target_id'];
}

