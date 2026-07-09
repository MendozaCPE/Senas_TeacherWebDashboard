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
                $gesture = DB::table('gestures')
                    ->whereRaw('LOWER(name) = ?', [strtolower($gestureName)])
                    ->first();

                if ($gesture) {
                    $slide['image_url'] = $gesture->image_url ?? null;
                    $slide['video_url'] = $gesture->video_url ?? null;

                    // If the gesture record exists but has no media, still flag it
                    if (empty($gesture->image_url) && empty($gesture->video_url)) {
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
