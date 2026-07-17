<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $primaryKey = 'question_id';

    protected $fillable = [
        'quiz_id',
        'question_number',
        'question_type',
        'question_text',
        'media_url',
        'drag_drop_pairs',    // ADD THIS
        'gesture_data',       // ADD THIS
        'gesture_required',
        'points',
    ];

    // ADD THIS CASTS ARRAY
    protected $casts = [
        'drag_drop_pairs' => 'array',
        'gesture_data' => 'array',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }

    public function options()
    {
        return $this->hasMany(QuizOption::class, 'question_id', 'question_id');
    }
}