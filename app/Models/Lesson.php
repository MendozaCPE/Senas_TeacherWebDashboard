<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
// Add these at the top
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $primaryKey = 'lesson_id';

    protected $fillable = [
        'teacher_id',
        'module_id',
        'title',
        'description',
        'lesson_type',
        'difficulty',
        'module_order',
        'status',
        'published_at',
        'ai_generated',
        'ai_prompt',
        'is_template',
        'source_template_id',
    ];

    protected $casts = [
        'is_template' => 'boolean',
    ];

    protected $appends = ['hash_id'];

    public function getHashIdAttribute(): string
    {
        return \App\Support\UrlObfuscator::encode($this->lesson_id);
    }

    public function getRouteKey()
    {
        return \App\Support\UrlObfuscator::encode($this->getKey());
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $id = \App\Support\UrlObfuscator::decode($value) ?? $value;
        return $this->where($field ?? $this->getKeyName(), $id)->first();
    }

    /**
     * Scope query to only include published, non-deleted lessons.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNull('deleted_at');
    }


    public function teacher()
{
    // Make sure this is using 'id' from teachers table
    return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
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

     public function assignments(): HasMany
     {
         return $this->hasMany(LessonAssignment::class, 'lesson_id', 'lesson_id');
     }
     public function module()
    {
        return $this->belongsTo(Module::class, 'module_id', 'module_id');
    }

    /**
     * Get the students assigned to this lesson
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'lesson_assignments', 'lesson_id', 'student_id')
                    ->withPivot('status', 'notified', 'assigned_at', 'completed_at', 'score')
                    ->withTimestamps();
    }

/**
 * Check if a student is assigned to this lesson
 */
public function isAssignedToStudent($studentId): bool
{
    return $this->assignments()
                ->where('student_id', $studentId)
                ->exists();
}

/**
 * Get the assignment status for a specific student
 */
public function getStudentStatus($studentId): ?string
{
    $assignment = $this->assignments()
                       ->where('student_id', $studentId)
                       ->first();

    return $assignment ? $assignment->status : null;
}
}