<?php

namespace App\Services;

use App\Models\Student;
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

    const QUIZ_XP = [
        'failed' => 0,      // 0-59%
        'passed' => 10,     // 60-99%
        'perfect' => 15,    // 100%
        'bonus' => 5,       // First completion bonus
    ];

    /**
     * Calculate XP based on quiz score
     */
    public function calculateQuizXp(float $percentage): int
    {
        if ($percentage >= 100) {
            return self::QUIZ_XP['perfect'];
        } elseif ($percentage >= 60) {
            return self::QUIZ_XP['passed'];
        }
        return self::QUIZ_XP['failed'];
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
    
    // Check if last_activity_date is null or string
    $lastActivity = $student->last_activity_date;
    
    // If it's a string, convert to Carbon
    if (is_string($lastActivity)) {
        $lastActivity = \Carbon\Carbon::parse($lastActivity);
    }
    
    $lastActivityDate = $lastActivity?->toDateString();

    if ($lastActivityDate === $today) {
        return; // Already updated today
    }

    if ($lastActivityDate === now()->subDay()->toDateString()) {
        // Consecutive day
        $student->streak_days += 1;
    } else {
        // Streak broken
        $student->streak_days = 1;
    }

    $student->last_activity_date = now();
    $student->save();

    // Bonus XP for streak milestones
    if ($student->streak_days > 0 && $student->streak_days % 3 === 0) {
        $bonusXp = 5 * ($student->streak_days / 3);
        $this->awardXp($student, (int)$bonusXp, 'streak_bonus', null, null);
    }
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

    /**
     * Get level name based on level number
     */
    public function getLevelName(int $level): string
    {
        $names = [
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
        return $names[$level] ?? 'Level ' . $level;
    }
}