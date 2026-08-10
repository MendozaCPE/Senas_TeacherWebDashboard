<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GestureMediaResolver
{
    /**
     * Resolve gesture media for AI-generated lesson content slides.
     *
     * For each slide that has a gesture_name, query the gestures table.
     * If found → attach image_url / video_url, media_missing = false.
     * If not found → media_missing = true, leave urls null.
     *
     * Checks gesture_media table (file_path) as a fallback when the legacy
     * image_url / video_url columns on the gestures row are empty.
     *
     * @param  array  $lesson  The lesson array from DeepSeekService::generate()
     * @return array  Modified lesson with media resolved
     */
    public function resolve(array $lesson): array
    {
        if (empty($lesson['contents']) || !is_array($lesson['contents'])) {
            return $lesson;
        }

        foreach ($lesson['contents'] as &$slide) {
            // Initialise media fields
            $slide['image_url']     = $slide['image_url'] ?? null;
            $slide['video_url']     = $slide['video_url'] ?? null;
            $slide['media_missing'] = false;

            $gestureName = $slide['gesture_name'] ?? null;

            if (empty($gestureName)) {
                // No gesture requested — nothing to resolve
                continue;
            }

            try {
                // Prefer rows that have an actual module_id (avoid legacy orphan rows)
                $gesture = DB::table('gestures')
                    ->whereRaw('LOWER(name) = ?', [strtolower($gestureName)])
                    ->orderByRaw('CASE WHEN module_id IS NULL THEN 1 ELSE 0 END ASC')
                    ->first();

                if ($gesture) {
                    $imageUrl = $gesture->image_url ?? null;
                    $videoUrl = $gesture->video_url ?? null;

                    // Fallback: check gesture_media table if legacy columns are empty
                    if (empty($imageUrl) && empty($videoUrl)) {
                        $primaryImage = DB::table('gesture_media')
                            ->where('gesture_id', $gesture->gesture_id)
                            ->where('media_type', 'image')
                            ->where('is_primary', true)
                            ->first();

                        if (!$primaryImage) {
                            $primaryImage = DB::table('gesture_media')
                                ->where('gesture_id', $gesture->gesture_id)
                                ->where('media_type', 'image')
                                ->orderBy('order')
                                ->first();
                        }

                        if ($primaryImage) {
                            $imageUrl = asset('storage/' . $primaryImage->file_path);
                        }

                        $primaryVideo = DB::table('gesture_media')
                            ->where('gesture_id', $gesture->gesture_id)
                            ->where('media_type', 'video')
                            ->orderBy('order')
                            ->first();

                        if ($primaryVideo) {
                            $videoUrl = asset('storage/' . $primaryVideo->file_path);
                        }
                    }

                    $slide['image_url'] = $imageUrl;
                    $slide['video_url'] = $videoUrl;

                    // Flag missing only if still no media after both checks
                    if (empty($imageUrl) && empty($videoUrl)) {
                        $slide['media_missing'] = true;
                    }
                } else {
                    $slide['media_missing'] = true;
                }
            } catch (\Throwable $e) {
                Log::warning('GestureMediaResolver query failed for gesture: ' . $gestureName, [
                    'error' => $e->getMessage(),
                ]);
                $slide['media_missing'] = true;
            }
        }

        unset($slide); // Break reference

        return $lesson;
    }
}
