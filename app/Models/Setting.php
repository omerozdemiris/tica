<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'logo',
        'white_logo',
        'favicon',
        'title',
        'email',
        'notify_mail',
        'phone',
        'address',
        'instagram',
        'twitter',
        'facebook',
        'youtube',
        'linkedin',
        'whatsapp',
        'contact',
        'socials',
        'google_iframe',
        'visitor_count',
    ];
}


