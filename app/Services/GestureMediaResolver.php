<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GestureMediaResolver
{
    /**
     * Resolve media for AI-generated lesson content slides.
     *
     * Resolution order per slide:
     *  1. Gesture slides (gesture_name set) → gestures table → gesture_media table
     *  2. Any slide still missing media → teacher_media table (keyword match, teacher-scoped)
     *
     * @param  array     $lesson     The lesson array from the AI service
     * @param  int|null  $teacherId  Optional teacher ID to include teacher-uploaded media
     * @return array  Modified lesson with media resolved
     */
    public function resolve(array $lesson, ?int $teacherId = null): array
    {
        if (empty($lesson['contents']) || !is_array($lesson['contents'])) {
            return $lesson;
        }

        // Pre-fetch teacher media once (avoid N+1 queries)
        $teacherMediaItems = [];
        if ($teacherId) {
            try {
                $teacherMediaItems = DB::table('teacher_media')
                    ->where('teacher_id', $teacherId)
                    ->select('media_id', 'title', 'file_name', 'file_path', 'mime_type', 'media_type')
                    ->get()
                    ->toArray();
            } catch (\Throwable $e) {
                Log::warning('GestureMediaResolver: failed to fetch teacher_media', [
                    'teacher_id' => $teacherId,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        foreach ($lesson['contents'] as &$slide) {
            // Initialise media fields
            $slide['image_url']     = $slide['image_url'] ?? null;
            $slide['video_url']     = $slide['video_url'] ?? null;
            $slide['media_missing'] = false;

            $gestureName = $slide['gesture_name'] ?? null;

            // ── Step 1: resolve gesture media from gestures / gesture_media tables ──
            if (!empty($gestureName)) {
                try {
                    // Prefer rows that have an actual module_id (avoid legacy orphan rows)
                    $gesture = DB::table('gestures')
                        ->whereRaw('LOWER(name) = ?', [strtolower($gestureName)])
                        ->orderByRaw('CASE WHEN module_id IS NULL THEN 1 ELSE 0 END ASC')
                        ->first();

                    if ($gesture) {
                        $rawImagePath = $gesture->image_url ?? null;
                        $rawVideoPath = $gesture->video_url ?? null;

                        // Fallback: check gesture_media table if legacy columns are empty
                        if (empty($rawImagePath) && empty($rawVideoPath)) {
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
                                $rawImagePath = $primaryImage->file_path;
                            }

                            $primaryVideo = DB::table('gesture_media')
                                ->where('gesture_id', $gesture->gesture_id)
                                ->where('media_type', 'video')
                                ->orderBy('order')
                                ->first();

                            if ($primaryVideo) {
                                $rawVideoPath = $primaryVideo->file_path;
                            }
                        }

                        $mediaPath = $rawVideoPath ?: $rawImagePath;
                        if ($mediaPath) {
                            $cleanPath = preg_replace('#^(?:https?://[^/]+)?(?:/storage/|storage/)?#i', '', $mediaPath);
                            $cleanPath = ltrim($cleanPath, '/');
                            $slide['media_path'] = $cleanPath;
                            if ($rawVideoPath) {
                                $slide['video_url'] = asset('storage/' . $cleanPath);
                            }
                            if ($rawImagePath) {
                                $slide['image_url'] = asset('storage/' . $cleanPath);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('GestureMediaResolver query failed for gesture: ' . $gestureName, [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // ── Step 2: fall back to teacher_media if still no media ──
            // Applies to: gesture slides with no match, and image/video content type slides
            $contentType = $slide['content_type'] ?? 'text';
            $needsMedia  = empty($slide['image_url']) && empty($slide['video_url']);
            $isMediaSlide = in_array($contentType, ['image', 'video', 'gesture_demo']);

            if ($needsMedia && ($isMediaSlide || !empty($gestureName)) && !empty($teacherMediaItems)) {
                $matched = $this->findTeacherMedia($slide, $teacherMediaItems);

                if ($matched) {
                    $ext = strtolower(pathinfo($matched->file_name, PATHINFO_EXTENSION));
                    $videoExts = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
                    $cleanPath = preg_replace('#^(?:https?://[^/]+)?(?:/storage/|storage/)?#i', '', $matched->file_path);
                    $cleanPath = ltrim($cleanPath, '/');
                    $url = asset('storage/' . $cleanPath);

                    $slide['media_path'] = $cleanPath;
                    if (in_array($ext, $videoExts)) {
                        $slide['video_url'] = $url;
                    } else {
                        $slide['image_url'] = $url;
                    }

                    $slide['media_source'] = 'teacher_media';
                }
            }

            // ── Step 3: flag missing only when media was expected but not found ──
            if ($needsMedia && ($isMediaSlide || !empty($gestureName))
                && empty($slide['image_url']) && empty($slide['video_url'])) {
                $slide['media_missing'] = true;
            }
        }

        unset($slide); // Break reference

        return $lesson;
    }

    /**
     * Find the best matching teacher media item for a slide using keyword matching.
     *
     * Builds a keyword list from gesture_name, slide title, and content_text,
     * then scores each teacher media item by how many keywords appear in its
     * title or filename. Returns the highest-scoring item (if any match > 0).
     *
     * @param  array   $slide
     * @param  array   $mediaItems  Rows from teacher_media
     * @return object|null
     */
    private function findTeacherMedia(array $slide, array $mediaItems): ?object
    {
        // Build keyword set from the slide
        $rawText = implode(' ', array_filter([
            $slide['gesture_name'] ?? null,
            $slide['title']        ?? null,
            $slide['content_text'] ?? null,
        ]));

        // Extract meaningful words (3+ chars, no stop words)
        $stopWords = ['the', 'and', 'for', 'are', 'was', 'that', 'this', 'with', 'from',
                      'you', 'his', 'her', 'they', 'have', 'had', 'what', 'when', 'will'];

        preg_match_all('/[a-z0-9_]+/i', strtolower($rawText), $matches);
        $keywords = array_filter($matches[0] ?? [], fn($w) =>
            strlen($w) >= 3 && !in_array($w, $stopWords)
        );
        $keywords = array_unique(array_values($keywords));

        if (empty($keywords)) {
            return null;
        }

        $bestScore = 0;
        $bestItem  = null;

        foreach ($mediaItems as $item) {
            $haystack = strtolower(($item->title ?? '') . ' ' . ($item->file_name ?? ''));
            $score = 0;
            foreach ($keywords as $kw) {
                if (str_contains($haystack, $kw)) {
                    $score++;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestItem  = $item;
            }
        }

        return $bestScore > 0 ? $bestItem : null;
    }
}
