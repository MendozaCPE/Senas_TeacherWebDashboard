<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckpointExamQuestion extends Model
{
    use HasFactory;

    protected $table = 'checkpoint_exam_questions';

    protected $primaryKey = 'question_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'exam_id',
        'source_lesson_id',
        'source_question_id',
        'question_number',
        'question_text',
        'question_type',
        'media_url',
        'points',
        'options_data',
        'drag_drop_pairs',
        'gesture_data',
        'correct_answer',
    ];

    protected $casts = [
        'options_data' => 'array',
        'drag_drop_pairs' => 'array',
        'gesture_data' => 'array',
        'points' => 'integer',
    ];

    // Relationships
    public function exam()
    {
        return $this->belongsTo(CheckpointExam::class, 'exam_id', 'exam_id');
    }

    public function sourceLesson()
    {
        return $this->belongsTo(Lesson::class, 'source_lesson_id', 'lesson_id');
    }

    public function sourceQuestion()
    {
        return $this->belongsTo(QuizQuestion::class, 'source_question_id', 'question_id');
    }
}