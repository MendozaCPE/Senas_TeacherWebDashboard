<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gesture extends Model
{
    use HasFactory;

    protected $primaryKey = 'gesture_id';
    
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'image_url',
        'video_url',
        'model_file',
        'difficulty',
        'module_id',
    ];

    public function module()
    {
        return $this->belongsTo(GestureModule::class, 'module_id', 'module_id');
    }

    public function performances()
    {
        return $this->hasMany(GesturePerformance::class, 'gesture_id', 'gesture_id');
    }

    // Get student's performance for this gesture
    public function getStudentPerformance($studentId)
    {
        return $this->performances()
                    ->where('student_id', $studentId)
                    ->first();
    }

    // Check if this gesture uses a special model (like J and Z)
    public function usesSpecialModel()
    {
        return str_contains($this->model_file, 'lstm_dynamic');
    }
}