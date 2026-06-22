<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $primaryKey = 'lesson_id';

    protected $fillable = [
        'teacher_id',
        'title',
        'description',
        'lesson_type',
        'difficulty',
        'module_order',
        'status',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    // Add this relationship for lesson contents
    public function contents()
    {
        return $this->hasMany(LessonContent::class, 'lesson_id', 'lesson_id')
            ->orderBy('step_number');
    }

    // Add this relationship for quiz
    public function quiz()
    {
        return $this->hasOne(Quiz::class, 'lesson_id', 'lesson_id');
    }
}