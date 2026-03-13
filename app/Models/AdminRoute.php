<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdminRoute extends Model
{
    use HasFactory;

    protected $table = 'routes';

    protected $fillable = [
        'name',
        'uri',
        'method',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'route_user', 'route_id', 'user_id')->withTimestamps();
    }

    public static function groupFromRouteName(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        $name = Str::startsWith($routeName, 'admin.')
            ? Str::after($routeName, 'admin.')
            : $routeName;

        return Str::before($name, '.') ?: $name;
    }
}

