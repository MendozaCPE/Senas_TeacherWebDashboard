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
        return $this->belongsTo(Teacher::class);
    }
}
