<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckpointExamAttempt extends Model
{
    protected $table = 'checkpoint_exam_attempts';
    protected $primaryKey = 'attempt_id';

    protected $fillable = [
        'student_id',
        'exam_id',
        'score',
        'total_points',
        'percentage',
        'status',
        'attempt_number',
        'xp_earned',
        'answers_data',
        'question_results',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'answers_data' => 'array',
        'question_results' => 'array',
        'percentage' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function exam()
    {
        return $this->belongsTo(CheckpointExam::class, 'exam_id', 'exam_id');
    }
}