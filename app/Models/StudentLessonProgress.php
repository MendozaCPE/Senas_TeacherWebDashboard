<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLessonProgress extends Model
{
    use HasFactory;

    protected $table = 'student_lesson_progress';
    protected $primaryKey = 'progress_id';

    protected $fillable = [
        'student_id',
        'lesson_id',
        'current_step',
        'lesson_completed',
        'quiz_completed',
        'quiz_score',
        'last_accessed_at',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id', 'lesson_id');
    }
    public function learningPath()
    {
        return $this->hasOne(LearningPath::class);
    }
}
