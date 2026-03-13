<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'title',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];
}
