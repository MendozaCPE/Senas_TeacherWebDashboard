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
        'gesture_required',
        'points',
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