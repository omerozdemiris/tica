<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBackup extends Model
{
    protected $fillable = [
        'file_name',
        'label',
        'is_default',
    ];
}


