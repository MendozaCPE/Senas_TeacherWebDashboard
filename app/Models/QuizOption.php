<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizOption extends Model
{
    protected $primaryKey = 'option_id';

    protected $fillable = [
        'question_id',
        'option_text',
        'option_media_url',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id', 'question_id');
    }
}