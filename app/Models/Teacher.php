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
    ];

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
