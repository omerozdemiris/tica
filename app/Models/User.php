<?php

namespace App\Models;

use App\Models\AdminRoute;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, MustVerifyEmailTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guest_id',
        'email',
        'phone',
        'password',
        'username',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function adminRoutes()
    {
        return $this->belongsToMany(AdminRoute::class, 'route_user', 'user_id', 'route_id')->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return in_array((int) $this->role, [1, 2], true);
    }

    public function isSuperAdmin(): bool
    {
        return (int) $this->role === 2;
    }

    public function hasRouteAccess(?string $routeName): bool
    {
        $group = AdminRoute::groupFromRouteName($routeName);

        if (!$group) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->adminRoutes()->where('name', $group)->exists();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->whereNull('read_at')->latest();
    }

    public function notificationPermissions()
    {
        return $this->hasMany(NotificationPermission::class);
    }

    public function hasNotificationPermission(string $notificationType, ?string $permissionType = null): bool
    {
        $query = $this->notificationPermissions()
            ->where('notification_type', $notificationType)
            ->where('status', true);

        if ($permissionType) {
            $query->where('permission_type', $permissionType);
        }

        return $query->exists();
    }

    public function getInitialsAttribute(): string
    {
        $name = trim($this->name);
        if (empty($name)) {
            return '?';
        }

        $words = preg_split('/\s+/', $name);
        $initials = '';

        // İlk kelimenin ilk harfi
        if (isset($words[0]) && !empty($words[0])) {
            $firstChar = mb_substr($words[0], 0, 1, 'UTF-8');
            $initials .= mb_strtoupper($firstChar, 'UTF-8');
        }

        // Son kelimenin ilk harfi (eğer birden fazla kelime varsa)
        if (count($words) > 1 && isset($words[count($words) - 1]) && !empty($words[count($words) - 1])) {
            $lastChar = mb_substr($words[count($words) - 1], 0, 1, 'UTF-8');
            $initials .= mb_strtoupper($lastChar, 'UTF-8');
        }

        return $initials;
    }

    /**
     * Override default verification notification to use custom template.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail());
    }
}
