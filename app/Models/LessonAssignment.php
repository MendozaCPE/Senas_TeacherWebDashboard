<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonAssignment extends Model
{
    protected $table = 'lesson_assignments';
    
    protected $fillable = [
        'lesson_id',
        'student_id',
        'school_year_id',
        'assigned_at',
        'status',
        'notified',
        'completed_at',
        'score',
    ];
    
    protected $casts = [
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'notified' => 'boolean',
    ];
    
    /**
     * Get the lesson for this assignment
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id', 'lesson_id');
    }
    
    /**
     * Get the student for this assignment
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
<<<<<<< HEAD
    
=======

>>>>>>> 32c92a8ef81791da90b87e7d2913428d541b329a
    /**
     * Get the school year for this assignment
     */
    public function schoolYear(): BelongsTo
    {
<<<<<<< HEAD
        return $this->belongsTo(SchoolYear::class, 'school_year_id', 'id');
=======
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
>>>>>>> 32c92a8ef81791da90b87e7d2913428d541b329a
    }
}