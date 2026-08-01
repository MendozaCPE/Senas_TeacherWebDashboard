<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSetting extends Model
{
    protected $table = 'student_settings';
    protected $primaryKey = 'setting_id';
    
    protected $fillable = [
        'student_id',
        'sound_enabled',
        'notifications_enabled',
    ];
    
    protected $casts = [
        'sound_enabled' => 'boolean',
        'notifications_enabled' => 'boolean',
    ];
    
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}