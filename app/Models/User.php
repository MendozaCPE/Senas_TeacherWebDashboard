<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'role',
        'status',
        'google_id',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Returns the URL to the user's avatar.
     * Handles three cases:
     *  1. Google OAuth avatar (full https:// URL stored in profile_photo)
     *  2. Uploaded file (relative storage path)
     *  3. Fallback: initials via UI Avatars
     */
    public function avatarUrl(): string
    {
        if (!empty($this->profile_photo)) {
            // If it's already a full URL (e.g. Google avatar), return as-is
            if (str_starts_with($this->profile_photo, 'http://') || str_starts_with($this->profile_photo, 'https://')) {
                return $this->profile_photo;
            }
            // Otherwise it's a local storage path
            return asset('storage/' . $this->profile_photo);
        }
        // Initials fallback using UI Avatars
        $name = urlencode($this->name ?? 'T');
        return "https://ui-avatars.com/api/?name={$name}&background=0d326b&color=fff&size=128&font-size=0.45&bold=true&rounded=true";
    }

    /**
     * Returns true if this user has a real password set
     * (not a Google-only account with a random placeholder hash).
     */
    public function hasPassword(): bool
    {
        return !empty($this->password) && empty($this->google_id);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id', 'id');
    }
}
