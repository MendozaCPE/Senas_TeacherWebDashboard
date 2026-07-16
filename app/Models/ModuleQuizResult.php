<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleQuizResult extends Model
{
    use HasFactory;

    protected $table = 'module_quiz_results';

    protected $fillable = [
        'student_id',
        'module_id',
        'score',
        'percentage',
        'passed',
        'attempt_number',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'percentage' => 'float',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id', 'module_id');
    }
}
