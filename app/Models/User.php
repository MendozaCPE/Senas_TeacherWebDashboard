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
     * Uses the uploaded profile photo if set, otherwise generates initials-based fallback.
     */
    public function avatarUrl(): string
    {
        if (!empty($this->profile_photo)) {
            return asset('storage/' . $this->profile_photo);
        }
        // Initials fallback using UI Avatars (no external DiceBear — works offline too)
        $name = urlencode($this->name ?? 'T');
        return "https://ui-avatars.com/api/?name={$name}&background=0d326b&color=fff&size=128&font-size=0.45&bold=true&rounded=true";
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
