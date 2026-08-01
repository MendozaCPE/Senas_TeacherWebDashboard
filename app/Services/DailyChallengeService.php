<?php

namespace App\Services;

use App\Models\DailyChallenge;
use App\Models\Student;
use App\Models\LearningPath;
use App\Models\LessonAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyChallengeService
{
    private XPService $xpService;
    
    public function __construct(XPService $xpService)
    {
        $this->xpService = $xpService;
    }
    
    /**
     * Generate or retrieve today's daily challenge for a student.
     */
    public function generateDailyChallenge(Student $student): DailyChallenge
    {
        // Check if today's challenge already exists
        $existing = DailyChallenge::where('student_id', $student->student_id)
            ->whereDate('challenge_date', Carbon::today())
            ->first();
            
        if ($existing) {
            return $existing;
        }
        
        // Get learning path
        $learningPath = LearningPath::where('student_id', $student->student_id)->first();
        $goal = $learningPath->learning_goal ?? 'Everything';
        $practiceTime = $learningPath->practice_time ?? '5_10_min';
        
        // Parse practice time
        $timeMinutes = $this->parsePracticeTime($practiceTime);
        
        // Build goals based on learning path
        $goals = $this->buildGoals($student, $goal, $timeMinutes);
        
        // Create challenge
        $challenge = DailyChallenge::create([
            'student_id' => $student->student_id,
            'challenge_date' => Carbon::today(),
            'theme' => $goal,
            'goals' => $goals,
            'total_xp_rewarded' => 0,
            'is_completed' => false,
        ]);
        
        // Create goal progress entries
        foreach ($goals as $goalData) {
            DB::table('challenge_goal_progress')->insert([
                'challenge_id' => $challenge->challenge_id,
                'goal_type' => $goalData['type'],
                'goal_key' => $goalData['id'],
                'target_value' => $goalData['target'],
                'current_value' => 0,
                'is_completed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        return $challenge->load('goalProgress');
    }
    
    /**
     * Build goals for the challenge based on learning path.
     */
    private function buildGoals(Student $student, string $goal, int $timeMinutes): array
    {
        $goals = [];
        
        // 1. Time-based goal (always present)
        $goals[] = [
            'id' => 'time_' . uniqid(),
            'type' => 'time_spent',
            'title' => 'Practice Time',
            'description' => "Spend {$timeMinutes} minutes practicing gestures",
            'target' => $timeMinutes,
            'xp_reward' => 20,
            'icon' => '⏱️',
        ];
        
        // 2. Gesture practice goal (always present)
        $gestureCount = $this->getTargetGestureCount($goal);
        $goals[] = [
            'id' => 'gesture_' . uniqid(),
            'type' => 'gesture_practice',
            'title' => 'Master Gestures',
            'description' => "Practice {$gestureCount} {$this->getThemeDisplayName($goal)} gestures",
            'target' => $gestureCount,
            'xp_reward' => 15,
            'icon' => '✋',
        ];
        
        // 3. Lesson completion goal (if lessons available)
        $hasAvailableLesson = $this->hasAvailableLesson($student);
        if ($hasAvailableLesson) {
            $goals[] = [
                'id' => 'lesson_' . uniqid(),
                'type' => 'lesson_completion',
                'title' => 'Complete a Lesson',
                'description' => 'Finish one lesson from your learning path',
                'target' => 1,
                'xp_reward' => 15,
                'icon' => '📚',
            ];
        }
        
        // 4. Quiz goal (if available)
        $hasAvailableQuiz = $this->hasAvailableQuiz($student);
        if ($hasAvailableQuiz) {
            $goals[] = [
                'id' => 'quiz_' . uniqid(),
                'type' => 'quiz_attempt',
                'title' => 'Perfect Score',
                'description' => 'Get 100% on a quiz',
                'target' => 1,
                'xp_reward' => 10,
                'icon' => '⭐',
            ];
        }
        
        // Ensure we have at least 3 goals
        while (count($goals) < 3) {
            $goals[] = [
                'id' => 'bonus_' . uniqid(),
                'type' => 'bonus_practice',
                'title' => 'Extra Practice',
                'description' => 'Practice any gesture for 2 minutes',
                'target' => 2,
                'xp_reward' => 5,
                'icon' => '🎯',
            ];
        }
        
        // Limit to max 5 goals
        return array_slice($goals, 0, 5);
    }
    
    /**
     * Parse practice time string to minutes.
     */
    private function parsePracticeTime(string $practiceTime): int
    {
        $map = [
            '5_10_min' => 5,
            '15_20_min' => 15,
            '30_min' => 30,
            '1_hour_plus' => 60,
        ];
        return $map[$practiceTime] ?? 5;
    }
    
    /**
     * Get target gesture count based on goal.
     */
    private function getTargetGestureCount(string $goal): int
    {
        $map = [
            'Alphabet_Numbers' => 5,
            'Greetings' => 4,
            'Classroom_Words' => 4,
            'Everything' => 5,
        ];
        return $map[$goal] ?? 5;
    }
    
    /**
     * Get display name for the theme.
     */
    private function getThemeDisplayName(string $goal): string
    {
        $map = [
            'Alphabet_Numbers' => 'alphabet & number',
            'Greetings' => 'greeting',
            'Classroom_Words' => 'classroom',
            'Everything' => 'gesture',
        ];
        return $map[$goal] ?? 'gesture';
    }
    
    /**
     * Check if student has available lessons.
     */
    private function hasAvailableLesson(Student $student): bool
    {
        return LessonAssignment::where('student_id', $student->student_id)
            ->where('status', '!=', 'completed')
            ->exists();
    }
    
    /**
     * Check if student has available quizzes.
     */
    private function hasAvailableQuiz(Student $student): bool
    {
        return DB::table('lesson_assignments as la')
            ->join('lessons as l', 'la.lesson_id', '=', 'l.lesson_id')
            ->join('quizzes as q', 'l.lesson_id', '=', 'q.lesson_id')
            ->where('la.student_id', $student->student_id)
            ->where('la.status', '!=', 'completed')
            ->exists();
    }
    
    /**
     * Update goal progress.
     */
    public function updateGoalProgress(
        DailyChallenge $challenge,
        string $goalId,
        int $incrementBy
    ): array {
        $progress = DB::table('challenge_goal_progress')
            ->where('challenge_id', $challenge->challenge_id)
            ->where('goal_key', $goalId)
            ->first();
            
        if (!$progress) {
            throw new \Exception('Goal not found');
        }
        
        $newValue = min($progress->current_value + $incrementBy, $progress->target_value);
        $isCompleted = $newValue >= $progress->target_value;
        
        DB::table('challenge_goal_progress')
            ->where('progress_id', $progress->progress_id)
            ->update([
                'current_value' => $newValue,
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? now() : null,
                'updated_at' => now(),
            ]);
            
        return [
            'progress_id' => $progress->progress_id,
            'current_value' => $newValue,
            'target_value' => $progress->target_value,
            'is_completed' => $isCompleted,
            'xp_earned' => 0,
        ];
    }
    
    /**
     * Award XP for a completed goal.
     */
    public function awardGoalXp(Student $student, DailyChallenge $challenge, string $goalId): int
    {
        $goals = collect($challenge->goals);
        $goal = $goals->firstWhere('id', $goalId);
        
        if (!$goal) {
            return 0;
        }
        
        $xpReward = $goal['xp_reward'];
        
        $this->xpService->awardXp(
            $student,
            $xpReward,
            'challenge_goal_completed',
            null,
            null,
            "🎯 Daily Challenge: {$goal['title']} - +{$xpReward} XP"
        );
        $this->xpService->updateStreak($student);
        
        // Update total xp rewarded
        $challenge->total_xp_rewarded += $xpReward;
        $challenge->save();
        
        return $xpReward;
    }
    
    /**
     * Award bonus XP for completing all goals.
     */
    public function awardBonusXp(Student $student, DailyChallenge $challenge): int
    {
        $bonusXp = 50;
        
        $this->xpService->awardXp(
            $student,
            $bonusXp,
            'challenge_completed',
            null,
            null,
            "🏆 Daily Challenge COMPLETE! +{$bonusXp} XP Bonus!"
        );
        $this->xpService->updateStreak($student);
        
        $challenge->is_completed = true;
        $challenge->completed_at = now();
        $challenge->total_xp_rewarded += $bonusXp;
        $challenge->save();
        
        return $bonusXp;
    }

    /**
     * Record progress toward TODAY's challenge goal(s) of a given type, from
     * any activity endpoint (lesson completed, quiz passed, gesture practiced,
     * etc). This only ever looks at today's challenge/goal row, so it doesn't
     * matter whether the underlying lesson/module/quiz was already completed
     * on a previous day — if the activity happened today, today's goal
     * advances. Safe to call even when the student has no goal of this type
     * today (no-op), and safe to call multiple times (already-completed goals
     * are skipped).
     */
    public function recordProgressByType(Student $student, string $goalType, int $incrementBy = 1): void
    {
        $challenge = DailyChallenge::where('student_id', $student->student_id)
            ->whereDate('challenge_date', Carbon::today())
            ->first();

        // No challenge generated yet today (e.g. the student triggered an
        // activity before ever opening the dashboard) — generate it now so
        // today's activity still counts instead of being silently dropped.
        if (!$challenge) {
            $challenge = $this->generateDailyChallenge($student);
        }

        $goalsOfType = collect($challenge->goals)->where('type', $goalType);

        foreach ($goalsOfType as $goal) {
            $this->applyGoalProgress($student, $challenge, $goal['id'], $incrementBy);
        }
    }

    /**
     * Shared progress-update + XP-award logic for a single goal. Used
     * internally by recordProgressByType(); mirrors the update logic in
     * StudentAuthController::updateChallengeProgress().
     */
    private function applyGoalProgress(Student $student, DailyChallenge $challenge, string $goalId, int $incrementBy): void
    {
        $progress = DB::table('challenge_goal_progress')
            ->where('challenge_id', $challenge->challenge_id)
            ->where('goal_key', $goalId)
            ->first();

        if (!$progress || $progress->is_completed) {
            // Nothing to do: goal missing, or already completed today.
            return;
        }

        $newValue = min($progress->current_value + $incrementBy, $progress->target_value);
        $isCompleted = $newValue >= $progress->target_value;

        DB::table('challenge_goal_progress')
            ->where('progress_id', $progress->progress_id)
            ->update([
                'current_value' => $newValue,
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? now() : null,
                'updated_at' => now(),
            ]);

        if ($isCompleted) {
            $this->awardGoalXp($student, $challenge, $goalId);
            $this->maybeAwardChallengeBonus($student, $challenge);
        }
    }

    /**
     * Check whether every goal in today's challenge is now complete, and if
     * so, award the completion bonus once.
     */
    private function maybeAwardChallengeBonus(Student $student, DailyChallenge $challenge): void
    {
        $challenge->refresh();

        if ($challenge->is_completed) {
            return;
        }

        $allProgress = DB::table('challenge_goal_progress')
            ->where('challenge_id', $challenge->challenge_id)
            ->get();

        $allCompleted = $allProgress->isNotEmpty() && $allProgress->every(fn($p) => (bool) $p->is_completed);

        if ($allCompleted) {
            $this->awardBonusXp($student, $challenge);
        }
    }
}