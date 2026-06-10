<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function progress()
    {
        return $this->hasMany(StudentLessonProgress::class, 'student_id', 'student_id');
    }
}
