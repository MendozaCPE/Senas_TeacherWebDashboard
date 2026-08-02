<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\UrlObfuscator;

class CheckpointExam extends Model
{
    use HasFactory;

    protected $table = 'checkpoint_exams';

    protected $primaryKey = 'exam_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'module_id',
        'teacher_id',
        'title',
        'description',
        'total_points',
        'passing_score',
        'status',
        'published_at',
    ];

    protected $casts = [
        'total_points' => 'integer',
        'passing_score' => 'integer',
        'published_at' => 'datetime',
    ];

    // Relationships
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id', 'module_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function questions()
    {
        return $this->hasMany(CheckpointExamQuestion::class, 'exam_id', 'exam_id');
    }

    public function assignments()
    {
        return $this->hasMany(CheckpointExamAssignment::class, 'exam_id', 'exam_id');
    }

    // Accessor for hash_id (obfuscated ID)
    public function getHashIdAttribute()
    {
        return UrlObfuscator::encode($this->exam_id);
    }
}