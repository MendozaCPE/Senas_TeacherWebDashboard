<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningPath extends Model
{
    // ✅ Specify the correct table name
    protected $table = 'learning_paths';
    
    protected $fillable = [
        'student_id',
        'fsl_level',
        'learning_goal',
        'practice_time',
        'is_completed',
        'completed_at'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime'
    ];

    // Add constants for the enum values
    const GOALS = [
        'ALPHABETS_NUMBERS' => 'Alphabet_Numbers',
        'FINGERSPELLING' => 'Fingerspelling',
        'GREETINGS_FSL_WORDS' => 'Greetings_FSL_Words',
        'EVERYTHING' => 'Everything',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}