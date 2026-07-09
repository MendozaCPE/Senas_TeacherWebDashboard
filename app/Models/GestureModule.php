<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GestureModule extends Model
{
    use HasFactory;

    protected $primaryKey = 'module_id';
    
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'difficulty',
        'model_file',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function gestures()
    {
        return $this->hasMany(Gesture::class, 'module_id', 'module_id');
    }

    // Get all gestures with their names for a module
    public function getGestureNamesAttribute()
    {
        return $this->gestures->pluck('name')->toArray();
    }

    // Get total gestures count
    public function getTotalGesturesAttribute()
    {
        return $this->gestures()->count();
    }

    // Scope for active modules
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope ordered by order field
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}