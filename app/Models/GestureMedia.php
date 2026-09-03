<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GestureMedia extends Model
{
    use HasFactory;

    protected $primaryKey = 'media_id';
    
    protected $fillable = [
        'gesture_id',   // nullable — null means Admin-uploaded standalone media
        'module_id',
        'media_type',
        'file_path',
        'file_name',
        'display_name', // Admin-editable title, persists across page loads
        'mime_type',
        'file_size',
        'is_primary',
        'order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'file_size' => 'integer',
    ];

    public function gesture()
    {
        return $this->belongsTo(Gesture::class, 'gesture_id', 'gesture_id');
    }

    public function module()
    {
        return $this->belongsTo(GestureModule::class, 'module_id', 'module_id');
    }

    // Get full URL
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}