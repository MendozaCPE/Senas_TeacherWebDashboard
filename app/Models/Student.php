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
    'school_year',
    'program_type',
    'fsl_mastery_level',
    'status',
    'total_xp',
    'level',
    'streak_days',
    'last_activity_date',
];

    // ─── AUTO-ASSIGN LESSONS ON STUDENT CREATION ────────────────────────
    protected static function booted()
    {
        static::created(function ($student) {
            // Auto-assign all published lessons to new student
            $lessons = Lesson::where('status', 'published')
                            ->orderBy('module_order', 'asc')
                            ->get();
            
            if ($lessons->isEmpty()) {
                return;
            }

            $assignments = [];
            $firstLessonInModule = [];

            // Track first lesson per module
            foreach ($lessons as $lesson) {
                $moduleId = $lesson->module_id;
                
                // If this is the first lesson in a module, it should be unlocked
                if (!isset($firstLessonInModule[$moduleId])) {
                    $firstLessonInModule[$moduleId] = $lesson->lesson_id;
                }
            }

            foreach ($lessons as $lesson) {
                // First lesson in each module is unlocked (is_locked = 0)
                // All other lessons are locked (is_locked = 1)
                $isLocked = ($firstLessonInModule[$lesson->module_id] !== $lesson->lesson_id);
                
                $assignments[] = [
                    'student_id' => $student->student_id,
                    'lesson_id' => $lesson->lesson_id,
                    'assigned_at' => now(),
                    'status' => 'pending',
                    'is_locked' => $isLocked ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Batch insert for performance
            LessonAssignment::insert($assignments);
        });
    }

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

    /**
     * Manually assign lessons to a student (for existing students)
     */
    public function assignLessons()
    {
        $lessons = Lesson::where('status', 'published')
                        ->orderBy('module_order', 'asc')
                        ->get();
        
        if ($lessons->isEmpty()) {
            return ['success' => false, 'message' => 'No published lessons found'];
        }

        $assignments = [];
        $firstLessonInModule = [];

        // Track first lesson per module
        foreach ($lessons as $lesson) {
            $moduleId = $lesson->module_id;
            if (!isset($firstLessonInModule[$moduleId])) {
                $firstLessonInModule[$moduleId] = $lesson->lesson_id;
            }
        }

        foreach ($lessons as $lesson) {
            // Check if already assigned
            $exists = LessonAssignment::where('student_id', $this->student_id)
                                     ->where('lesson_id', $lesson->lesson_id)
                                     ->exists();
            
            if ($exists) {
                continue;
            }

            $isLocked = ($firstLessonInModule[$lesson->module_id] !== $lesson->lesson_id);
            
            $assignments[] = [
                'student_id' => $this->student_id,
                'lesson_id' => $lesson->lesson_id,
                'assigned_at' => now(),
                'status' => 'pending',
                'is_locked' => $isLocked ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($assignments)) {
            LessonAssignment::insert($assignments);
            return ['success' => true, 'message' => count($assignments) . ' lessons assigned'];
        }

        return ['success' => true, 'message' => 'No new lessons to assign'];
    }
 /**
     * Get the daily challenges for this student.
     */
    public function dailyChallenges(): HasMany
    {
        return $this->hasMany(DailyChallenge::class, 'student_id', 'student_id');
    }
    
    /**
     * Get today's daily challenge for this student.
     */
    public function getTodayChallenge(): ?DailyChallenge
    {
        return $this->dailyChallenges()
            ->whereDate('challenge_date', now()->toDateString())
            ->first();
    }

}