<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'icon',
        'color',
        'order',
        'criteria',
    ];

    protected $casts = [
        'criteria' => 'array',
    ];

    // Relationship with student achievements
    public function studentAchievements()
    {
        return $this->hasMany(StudentAchievement::class, 'achievement_id');
    }

    // Get students who have unlocked this achievement
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_achievements', 'achievement_id', 'student_id')
                    ->withPivot('is_unlocked', 'unlocked_at', 'progress_current', 'progress_target')
                    ->withTimestamps();
    }
}