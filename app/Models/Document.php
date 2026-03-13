<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'type',
        'parent',
        'queue',
        'name',
        'original_name',
        'extension',
    ];
}


