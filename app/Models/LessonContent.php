<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonContent extends Model
{
    protected $primaryKey = 'content_id';

    protected $fillable = [
        'lesson_id',
        'step_number',
        'content_type',
        'title',
        'content_text',
        'media_url',
        'gesture_name',
        'media_missing',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id', 'lesson_id');
    }
}
