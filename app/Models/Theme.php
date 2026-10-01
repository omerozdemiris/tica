<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;
    protected $table = 'theme';
    protected $fillable = ['color', 'thene'];
    protected $casts = [
        'color' => 'string',
        'thene' => 'string',
    ];
}
