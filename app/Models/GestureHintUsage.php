<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GestureHintUsage extends Model
{
    protected $table = 'gesture_hint_usage';
    protected $primaryKey = 'hint_usage_id';
    
    protected $fillable = [
        'student_id',
        'module_name',
        'letter',
        'hint_count',
        'session_id',
    ];

    protected $casts = [
        'hint_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get formatted hint usage for a student and module
     */
    public static function getFormattedHintUsage($studentId, $moduleName)
    {
        $hints = self::where('student_id', $studentId)
            ->where('module_name', $moduleName)
            ->get();

        if ($hints->isEmpty()) {
            return null;
        }

        return $hints->map(function ($hint) {
            return [
                'letter' => $hint->letter,
                'count' => $hint->hint_count,
            ];
        })->toArray();
    }
}