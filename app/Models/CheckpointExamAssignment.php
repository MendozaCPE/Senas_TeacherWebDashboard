<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckpointExamAssignment extends Model
{
    use HasFactory;

    protected $table = 'checkpoint_exam_assignments';

    protected $primaryKey = 'assignment_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'exam_id',
        'student_id',
        'assigned_at',
        'started_at',
        'completed_at',
        'score',
        'status',
        'is_locked',
        'notified',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'float',
        'is_locked' => 'boolean',
        'notified' => 'boolean',
    ];

    // Relationships
    public function exam()
    {
        return $this->belongsTo(CheckpointExam::class, 'exam_id', 'exam_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}