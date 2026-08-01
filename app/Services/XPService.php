<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentNotification;
use Illuminate\Support\Facades\DB;


class XPService
{
    // XP thresholds for levels
    const LEVEL_THRESHOLDS = [
        1 => 0,
        2 => 100,
        3 => 250,
        4 => 500,
        5 => 800,
        6 => 1200,
        7 => 1700,
        8 => 2300,
        9 => 3000,
        10 => 4000,
    ];
    // Level names (only in backend)
const LEVEL_NAMES = [
    1 => 'Novice Signer',
    2 => 'Beginner Signer',
    3 => 'Emerging Signer',
    4 => 'Intermediate Signer',
    5 => 'Advanced Beginner',
    6 => 'Competent Signer',
    7 => 'Proficient Signer',
    8 => 'Advanced Signer',
    9 => 'Expert Signer',
    10 => 'Master Signer',
];

    const QUIZ_XP = [
        'failed' => 0,      // 0-59%
        'passed' => 10,     // 60-99%
        'perfect' => 15,    // 100%
        'bonus' => 5,       // First completion bonus
    ];
    

    /**
 * Calculate XP BEFORE the attempt is saved
 * This is cleaner because we don't have to worry about
 * the current attempt affecting the previous best calculation
 */
public function calculateQuizXpBeforeSave(
    Student $student,
    int $quizId,
    float $percentage,
    bool $isPerfect,
    int $attemptNumber
): int {
    // ─── 1. FIRST ATTEMPT PERFECT = 35 XP ──────────────────────
    if ($attemptNumber === 1 && $isPerfect) {
        return 35;
    }

    // ─── 2. FAILING = 0 XP ─────────────────────────────────────
    if ($percentage < 60) {
        return 0;
    }

    // ─── 3. GET PREVIOUS BEST ──────────────────────────────────
    // 🔥 Now we can safely get the previous best because 
    // the current attempt hasn't been saved yet!
    $previousBest = DB::table('quiz_attempts')
        ->where('student_id', $student->student_id)
        ->where('quiz_id', $quizId)
        ->where('status', 'completed')
        ->max('percentage');

    // ─── 4. CHECK TOTAL XP EARNED SO FAR ──────────────────────
    $totalXpEarned = DB::table('quiz_attempts')
        ->where('student_id', $student->student_id)
        ->where('quiz_id', $quizId)
        ->sum('xp_earned');

    if ($totalXpEarned >= 30) {
        return 0;
    }

    // ─── 5. CALCULATE XP ──────────────────────────────────────
    $xpEarned = 0;

    // First time passing
    if ($previousBest === null) {
        $xpEarned = 20;  // First pass = 20 XP
    } 
    // Improvement on subsequent attempts
    else if ($percentage > $previousBest) {
        $improvement = $percentage - $previousBest;
        $xpEarned = floor(($improvement / 10) * 5);  // 5 XP per 10% improvement
        
        // Extra bonus for reaching perfect
        if ($isPerfect) {
            $xpEarned += 5;
        }
    }
    // Same score or lower = 0 XP
    else {
        return 0;
    }

    // ─── 6. CAP AT REMAINING XP ──────────────────────────────
    $remainingXp = 30 - $totalXpEarned;
    $xpEarned = min($xpEarned, $remainingXp);

    return max(0, $xpEarned);
}

/**
 * Get human-readable XP reason for quiz attempts
 */
public function getXpReason(int $attemptNumber, bool $isPerfect, float $percentage, int $xpEarned): string
{
    if ($attemptNumber === 1 && $isPerfect) {
        return "🏆 PERFECT on FIRST TRY! +{$xpEarned} XP (Bonus!)";
    } elseif ($attemptNumber === 1 && $percentage >= 60) {
        return "🎯 Passed on first attempt! +{$xpEarned} XP";
    } elseif ($isPerfect) {
        return "⭐ Perfect score on attempt #{$attemptNumber}! +{$xpEarned} XP";
    } else {
        return "📈 Improved score on attempt #{$attemptNumber}! +{$xpEarned} XP";
    }
}

    /**
     * Award XP to student
     */
    public function awardXp(Student $student, int $amount, string $action, ?int $quizAttemptId = null, ?int $lessonId = null, ?string $customReason = null): void
    {
        DB::transaction(function () use ($student, $amount, $action, $quizAttemptId, $lessonId, $customReason) {
            // Update student total XP
            $student->total_xp += $amount;
            $student->save();

            // Log the XP
            try {
                DB::table('xp_log')->insert([
                    'student_id' => $student->student_id,
                    'quiz_attempt_id' => $quizAttemptId,
                    'lesson_id' => $lessonId,
                    'action' => $action,
                    'xp_amount' => $amount,
                    'reason' => $customReason ?? $this->getReason($action, $amount),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::warning('XP log table not found: ' . $e->getMessage());
            }

            // Update level if needed
            $this->updateLevel($student);
        });
    }

    /**
     * Check if this is the student's first completion of this quiz
     * Fixed: Using DB::table() instead of QuizAttempt model
     */
    public function isFirstQuizCompletion(int $studentId, int $quizId): bool
    {
        // Check if there's any passed attempt for this student and quiz
        $attempt = DB::table('quiz_attempts')
            ->where('student_id', $studentId)
            ->where('quiz_id', $quizId)
            ->where('status', 'completed') // or 'passed' depending on your status values
            ->first();

        return $attempt === null;
    }

    /**
     * Update student's level based on XP
     */
    public function updateLevel(Student $student): void
    {
        $newLevel = 1;
        foreach (self::LEVEL_THRESHOLDS as $level => $threshold) {
            if ($student->total_xp >= $threshold) {
                $newLevel = $level;
            }
        }
        $student->level = $newLevel;
        $student->save();
    }
/**
     * Update streak
     */
    public function updateStreak(Student $student): void
    {
        $today = now()->toDateString();
        $lastActivity = $student->last_activity_date;
        
        if (is_string($lastActivity)) {
            $lastActivity = \Carbon\Carbon::parse($lastActivity);
        }
        $lastActivityDate = $lastActivity?->toDateString();

        // Already updated today → no change
        if ($lastActivityDate === $today) {
            return;
        }

        // First time ever or streak broken
        if ($lastActivity === null) {
            $student->streak_days = 1;
        } 
        // Consecutive day
        else if ($lastActivityDate === now()->subDay()->toDateString()) {
            $student->streak_days += 1;
        } 
        // Streak broken (gap > 1 day) - reset to 1 for new streak
        else {
            $student->streak_days = 1;
        }

        $student->last_activity_date = now();
        $student->save();

        // ✅ Check for streak milestones after saving
        $this->checkStreakMilestones($student);

        // Bonus XP for streak milestones (every 3 days)
        if ($student->streak_days > 0 && $student->streak_days % 3 === 0) {
            $bonusXp = 5 * ($student->streak_days / 3);
            $this->awardXp($student, (int)$bonusXp, 'streak_bonus', null, null);
        }
    }

    /**
     * ✅ NEW: Check and create streak milestone notifications
     */
    protected function checkStreakMilestones(Student $student): void
    {
        $currentStreak = $student->streak_days ?? 0;
        
        if ($currentStreak < 7) {
            return; // Only check when streak is 7 or more
        }

        // ─── CHECK MAJOR MILESTONES (7, 14, 21, 30) ──────────────
        $milestones = [7, 14, 21, 30];
        $lastMilestone = $student->last_streak_milestone ?? 0;

        foreach ($milestones as $milestone) {
            if ($currentStreak >= $milestone && $milestone > $lastMilestone) {
                $this->createStreakNotification(
                    $student,
                    'milestone',
                    $milestone,
                    "🔥 {$milestone}-Day Streak!",
                    "Amazing! You've been learning for {$milestone} days straight. Keep going!"
                );
                
                $student->last_streak_milestone = $milestone;
                $student->save();
                break; // Only trigger the first new milestone
            }
        }

        // ─── CHECK "KEEP GOING" NOTIFICATIONS (8, 15, 22, 29) ──
        $keepGoingDays = [8, 15, 22, 29];
        $lastKeepGoing = $student->last_keep_going_notification ?? 0;

        foreach ($keepGoingDays as $day) {
            if ($currentStreak >= $day && $day > $lastKeepGoing) {
                $this->createStreakNotification(
                    $student,
                    'keep_going',
                    $day,
                    "💪 {$day} Days and Going Strong!",
                    "You're on a {$currentStreak}-day streak! Keep up the great work! 🌟"
                );
                
                $student->last_keep_going_notification = $day;
                $student->save();
                break; // Only trigger the first new keep-going notification
            }
        }
    }

    /**
     * ✅ NEW: Create streak notification
     */
    protected function createStreakNotification(Student $student, string $type, int $day, string $title, string $message): void
    {
        // Check if notification already exists for this milestone
        $exists = StudentNotification::where('student_id', $student->student_id)
            ->where('type', 'streak')
            ->where('data->milestone', $day)
            ->exists();

        if ($exists) {
            return; // Skip duplicate
        }

        StudentNotification::create([
            'student_id' => $student->student_id,
            'type' => 'streak',
            'title' => $title,
            'message' => $message,
            'icon' => 'flame',
            'color' => '#EF4444',
            'data' => [
                'streak_days' => $student->streak_days,
                'milestone' => $day,
                'type' => $type,
            ],
            'action_url' => '/(tabs)/dashboard',
            'is_read' => false,
        ]);
    }


    /**
     * Get human-readable reason
     */
    private function getReason(string $action, int $amount): string
    {
        $reasons = [
            'quiz_completed' => "Completed quiz: +{$amount} XP",
            'lesson_completed' => "Completed lesson: +{$amount} XP",
            'streak_bonus' => "Streak bonus: +{$amount} XP",
            'daily_challenge' => "Daily challenge: +{$amount} XP",
            'slide_completed' => "Completed slide: +{$amount} XP",
        ];
        return $reasons[$action] ?? "Earned {$amount} XP";
    }

    /**
     * Get next level XP requirement
     */
    public function getNextLevelXp(Student $student): int
    {
        $currentLevel = $student->level;
        $nextLevel = $currentLevel + 1;

        if (isset(self::LEVEL_THRESHOLDS[$nextLevel])) {
            return self::LEVEL_THRESHOLDS[$nextLevel];
        }

        // If beyond level 10, use formula
        return self::LEVEL_THRESHOLDS[10] + (($currentLevel - 9) * 1000);
    }

    /**
     * Get XP progress to next level (0-100%)
     */
    public function getLevelProgress(Student $student): float
    {
        $currentXp = $student->total_xp;
        $currentLevel = $student->level;
        $nextLevelXp = $this->getNextLevelXp($student);

        $currentLevelXp = self::LEVEL_THRESHOLDS[$currentLevel] ?? 0;
        $xpNeeded = $nextLevelXp - $currentLevelXp;
        $xpEarned = $currentXp - $currentLevelXp;

        return $xpNeeded > 0 ? min(100, ($xpEarned / $xpNeeded) * 100) : 100;
    }

   

public function getLevelName(int $level): string
{
    return self::LEVEL_NAMES[$level] ?? 'Level ' . $level;
}

// Add this method to get level thresholds
public function getLevelThresholds(): array
{
    return self::LEVEL_THRESHOLDS;
}

// Add this method to get all level data
public function getLevelData(): array
{
    $data = [];
    foreach (self::LEVEL_THRESHOLDS as $level => $threshold) {
        $data[] = [
            'level' => $level,
            'xp_required' => $threshold,
            'name' => self::LEVEL_NAMES[$level] ?? "Level $level",
        ];
    }
    return $data;
}


}