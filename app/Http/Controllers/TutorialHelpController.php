<?php

namespace App\Http\Controllers;

use App\Services\DeepSeekService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TutorialHelpController extends Controller
{
    public function completeOnboarding(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user?->role === 'teacher', 403);

        if (!$user->tutorial_onboarding_completed_at) {
            $user->forceFill(['tutorial_onboarding_completed_at' => now()])->save();
        }

        return response()->json(['completed' => true]);
    }

    public function ask(Request $request, DeepSeekService $deepSeek): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:600'],
        ]);

        $videos = config('tutorial_help.videos', []);
        $guides = array_map(static function (array $video): array {
            return [
                'id' => $video['id'],
                'title' => $video['title'],
                'summary' => $video['summary'],
                'verified_steps' => $video['steps'],
            ];
        }, $videos);

        try {
            $result = $deepSeek->answerTutorialQuestion($validated['question'], $guides);
        } catch (\Throwable $e) {
            Log::warning('Tutorial help answer failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'The AI helper is temporarily unavailable. Please try again, or choose a question below.',
            ], 503);
        }

        $videoId = $result['video_id'] ?? null;
        $video = collect($videos)->firstWhere('id', $videoId);
        if (!$video) {
            $video = $this->guessRelevantVideo($validated['question'], $videos);
        }

        return response()->json([
            'answer' => mb_substr(trim($result['answer']), 0, 2400),
            'steps' => $video['steps'],
            'video' => [
                'id' => $video['id'],
                'title' => $video['title'],
                'youtube_id' => $video['youtube_id'],
            ],
        ]);
    }

    private function guessRelevantVideo(string $question, array $videos): array
    {
        $text = mb_strtolower($question);
        $id = match (true) {
            (str_contains($text, 'account') && (str_contains($text, 'creat') || str_contains($text, 'new') || str_contains($text, 'sign') || str_contains($text, 'register'))), str_contains($text, 'sign up'), str_contains($text, 'signup'), str_contains($text, 'registration') => 'teacher-account',
            str_contains($text, 'student'), str_contains($text, 'enroll'), str_contains($text, 'excel') => 'students',
            str_contains($text, 'media'), str_contains($text, 'upload video'), str_contains($text, 'image') => 'media',
            str_contains($text, 'lesson'), str_contains($text, 'module'), str_contains($text, 'pdf') => 'lessons',
            str_contains($text, 'profile'), str_contains($text, 'setting'), str_contains($text, 'notification'), str_contains($text, 'feedback') => 'settings',
            default => 'performance',
        };

        return collect($videos)->firstWhere('id', $id) ?? $videos[0];
    }
}
