<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAnnouncement extends Model
{
    protected $fillable = ['teacher_id', 'lesson_id', 'lesson_ids', 'module_id', 'title', 'message', 'type', 'school_year_id', 'recipient_count'];

    protected $casts = [
        'lesson_ids' => 'array',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
