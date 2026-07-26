<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAchievement extends Model
{
    use HasFactory;

    protected $table = 'student_achievements';

    protected $fillable = [
        'student_id',
        'achievement_id',
        'is_unlocked',
        'unlocked_at',
        'progress_current',
        'progress_target',
        'metadata',
    ];

    protected $casts = [
        'is_unlocked' => 'boolean',
        'unlocked_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationship with student
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    // Relationship with achievement
    public function achievement()
    {
        return $this->belongsTo(Achievement::class, 'achievement_id');
    }
}