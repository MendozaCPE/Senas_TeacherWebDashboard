<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonContent extends Model
{
    protected $primaryKey = 'content_id';

    protected $fillable = [
        'lesson_id',
        'step_number',
        'content_type',
        'title',
        'content_text',
        'media_url',
        'gesture_name',
        'media_missing',
    ];

    /**
     * Valid content types available in the system.
     */
    const TYPES = ['text', 'image', 'video', 'gesture_demo', 'youtube_video'];

    /**
     * Extract a YouTube video ID from various URL formats.
     * Returns null if the URL is not a valid YouTube URL.
     *
     * Supported formats:
     *  - https://www.youtube.com/watch?v=VIDEO_ID
     *  - https://youtu.be/VIDEO_ID
     *  - https://www.youtube.com/embed/VIDEO_ID
     *  - https://www.youtube.com/shorts/VIDEO_ID
     */
    public static function extractYoutubeId(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // youtu.be/VIDEO_ID
        if (preg_match('#youtu\.be/([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return $m[1];
        }

        // youtube.com/watch?v=VIDEO_ID
        if (preg_match('#[?&]v=([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return $m[1];
        }

        // youtube.com/embed/VIDEO_ID
        if (preg_match('#youtube\.com/embed/([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return $m[1];
        }

        // youtube.com/shorts/VIDEO_ID
        if (preg_match('#youtube\.com/shorts/([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Build the standard YouTube embed URL from a raw URL or video ID.
     * Returns null if the URL is not a valid YouTube URL.
     */
    public static function buildYoutubeEmbedUrl(?string $url): ?string
    {
        $id = self::extractYoutubeId($url);
        if (!$id) {
            return null;
        }

        return 'https://www.youtube.com/embed/' . $id . '?rel=0&modestbranding=1';
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id', 'lesson_id');
    }
}
