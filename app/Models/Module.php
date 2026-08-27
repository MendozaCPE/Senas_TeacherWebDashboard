<?php
// app/Models/Module.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;

    protected $table = 'modules';
    protected $primaryKey = 'module_id';

    protected $fillable = [
        'teacher_id',
        'title',
        'description',
        'mastery_level',  // ← ADD THIS
        'module_order',
        'status',
        'is_template',
        'source_template_id', // ✅ Already there
    ];

    protected $casts = [
        'mastery_level' => 'string',
        'is_template'   => 'boolean', 
    ];

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'module_id')->orderBy('module_order');
    }

    public function checkpointExams()
    {
        return $this->hasMany(CheckpointExam::class, 'module_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}