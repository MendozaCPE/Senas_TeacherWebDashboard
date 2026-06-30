<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $primaryKey = 'module_id';
    protected $table = 'modules';

    protected $fillable = [
        'teacher_id',
        'title',
        'description',
        'module_order',
        'status',
    ];

    protected $casts = [
        'module_order' => 'integer',
    ];

    // Relationships
    public function teacher()
    {
        // FIXED: Use 'id' from teachers table
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'module_id', 'module_id');
    }

    // Scope for published modules
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}