<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_id',
        'first_name',
        'last_name',
        'specialization',
        'notification_prefs',
    ];

    protected $casts = [
        'notification_prefs' => 'array',
    ];

    /**
     * Check whether a specific notification preference is enabled.
     * Defaults to true for email_alerts so teachers with no saved prefs
     * still receive emails until they explicitly opt out.
     */
    public function notifPref(string $key, bool $default = true): bool
    {
        $prefs = $this->notification_prefs ?? [];
        return (bool) ($prefs[$key] ?? $default);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'teacher_id', 'id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'teacher_id', 'id');
    }

    public function notifications()
    {
        return $this->hasMany(TeacherNotification::class);
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }
}
