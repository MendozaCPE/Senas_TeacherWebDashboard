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
        'image_url',      // Keep this for backward compatibility
        'video_url',      // Keep this for backward compatibility
        'model_file',
        'difficulty',
        'sign_type',      // 'static' (alphabet/numbers) or 'dynamic' (moving signs like hello)
        'module_id',
    ];

    // New relationship with gesture_media
    public function media()
    {
        return $this->hasMany(GestureMedia::class, 'gesture_id', 'gesture_id');
    }

    public function primaryImage()
    {
        return $this->hasOne(GestureMedia::class, 'gesture_id', 'gesture_id')
                    ->where('media_type', 'image')
                    ->where('is_primary', true);
    }

    public function images()
    {
        return $this->hasMany(GestureMedia::class, 'gesture_id', 'gesture_id')
                    ->where('media_type', 'image')
                    ->orderBy('order');
    }

    public function videos()
    {
        return $this->hasMany(GestureMedia::class, 'gesture_id', 'gesture_id')
                    ->where('media_type', 'video')
                    ->orderBy('order');
    }

    public function module()
    {
        return $this->belongsTo(GestureModule::class, 'module_id', 'module_id');
    }

    public function performances()
    {
        return $this->hasMany(GesturePerformance::class, 'gesture_id', 'gesture_id');
    }

    // Helper to get full URL for media
    public function getMediaUrl($mediaItem)
    {
        return asset('storage/' . $mediaItem->file_path);
    }

    // Get all media URLs for API response
    public function getMediaUrlsAttribute()
    {
        return $this->media->map(function($media) {
            return [
                'type' => $media->media_type,
                'url' => asset('storage/' . $media->file_path),
                'is_primary' => $media->is_primary,
                'file_name' => $media->file_name,
            ];
        });
    }
}