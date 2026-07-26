<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Add these at the top
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\QuizAttempt;

class Student extends Model
{
    use HasFactory;

    protected $primaryKey = 'student_id';

    protected $fillable = [
    'user_id',
    'teacher_id',
    'school_id',
    'lrn',
    'pin',
    'first_name',
    'last_name',
    'age',
    'grade_level',
    'section',
    'program_type',
    'fsl_mastery_level',
    'status',
    'total_xp',
    'level',
    'streak_days',
    'last_activity_date',
];
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function progress()
    {
        return $this->hasMany(StudentLessonProgress::class, 'student_id', 'student_id');
    }

    /**
     * Get the lessons assigned to this student
     */
    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'lesson_assignments', 'student_id', 'lesson_id')
                    ->withPivot('status', 'notified', 'assigned_at', 'completed_at', 'score')
                    ->withTimestamps();
    }

    /**
     * Get all assignments for this student
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(LessonAssignment::class, 'student_id', 'student_id');
    }

    /**
     * Get all promotion history records for this student.
     */
    public function promotions(): HasMany
    {
        return $this->hasMany(StudentPromotion::class, 'student_id', 'student_id')
                    ->orderBy('promoted_at', 'desc');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'student_id', 'student_id');
    }
    // Relationship with achievements
public function achievements()
{
    return $this->hasMany(StudentAchievement::class, 'student_id', 'student_id');
}

public function unlockedAchievements()
{
    return $this->hasMany(StudentAchievement::class, 'student_id', 'student_id')
                ->where('is_unlocked', true);
}

public function achievementRecords()
{
    return $this->belongsToMany(Achievement::class, 'student_achievements', 'student_id', 'achievement_id')
                ->withPivot('is_unlocked', 'unlocked_at', 'progress_current', 'progress_target')
                ->withTimestamps();
}

}
