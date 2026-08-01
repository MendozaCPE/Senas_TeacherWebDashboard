<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AchievementService
{
    protected XPService $xpService;

    public function __construct(XPService $xpService)
    {
        $this->xpService = $xpService;
    }

    /**
     * Check and unlock achievements for a student
     * Call this after any student activity (quiz, lesson, gesture, etc.)
     */
    public function checkAndUnlockAchievements(Student $student): array
    {
        $unlocked = [];
        
        // Get all achievements
        $achievements = Achievement::all();
        
        foreach ($achievements as $achievement) {
            // Skip if already unlocked
            if ($this->isAchievementUnlocked($student, $achievement)) {
                continue;
            }
            
            // Check if criteria are met
            if ($this->checkCriteria($student, $achievement)) {
                $this->unlockAchievement($student, $achievement);
                $unlocked[] = $achievement;
            }
        }
        
        // ✅ ADD: Create notifications for newly unlocked achievements
        if (!empty($unlocked)) {
            foreach ($unlocked as $achievement) {
                $this->createAchievementNotification($student, $achievement);
            }
        }
        
        return $unlocked;
    }

    /**
     * ✅ NEW: Create notification for an unlocked achievement
     */
    protected function createAchievementNotification(Student $student, Achievement $achievement)
    {
        $iconMap = [
            'achievement' => 'trophy',
            'promotion' => 'star',
            'lesson' => 'book',
            'streak' => 'flame',
            'system' => 'notifications',
        ];

        $colorMap = [
            'achievement' => '#F59E0B',
            'promotion' => '#8B5CF6',
            'lesson' => '#3B82F6',
            'streak' => '#EF4444',
            'system' => '#6B7280',
        ];

        StudentNotification::create([
            'student_id' => $student->student_id,
            'type' => 'achievement',
            'title' => '🏆 New Achievement Unlocked!',
            'message' => "You earned \"{$achievement->name}\"! " . ($achievement->description ?? 'Keep up the great work!'),
            'icon' => $iconMap['achievement'],
            'color' => $colorMap['achievement'],
            'data' => ['achievement' => $achievement],
            'action_url' => '/(tabs)/achievements',
            'is_read' => false,
        ]);
    }
    /**
     * Check if a specific achievement is unlocked
     */
    public function isAchievementUnlocked(Student $student, Achievement $achievement): bool
    {
        return StudentAchievement::where('student_id', $student->student_id)
            ->where('achievement_id', $achievement->id)
            ->where('is_unlocked', true)
            ->exists();
    }

    /**
     * Unlock an achievement for a student
     */
    public function unlockAchievement(Student $student, Achievement $achievement): StudentAchievement
    {
        $record = StudentAchievement::updateOrCreate(
            [
                'student_id' => $student->student_id,
                'achievement_id' => $achievement->id,
            ],
            [
                'is_unlocked' => true,
                'unlocked_at' => now(),
            ]
        );
        
        // Award bonus XP for unlocking achievement (optional)
        $bonusXp = $this->getAchievementBonusXp($achievement);
        if ($bonusXp > 0) {
            $this->xpService->awardXp(
                $student,
                $bonusXp,
                'achievement_unlocked',
                null,
                null,
                "🏆 Achievement unlocked: {$achievement->name} (+{$bonusXp} XP)"
            );
        }
        
        Log::info('Achievement unlocked', [
            'student' => $student->student_id,
            'achievement' => $achievement->code,
            'xp_bonus' => $bonusXp,
        ]);
        
        return $record;
    }

    /**
     * Check if criteria are met for an achievement
     */
    protected function checkCriteria(Student $student, Achievement $achievement): bool
    {
        $criteria = $achievement->criteria;
        
        if (empty($criteria)) {
            return false;
        }
        
        // Handle different criterion types
        foreach ($criteria as $criterion) {
            $type = $criterion['type'] ?? null;
            $threshold = $criterion['threshold'] ?? 0;
            $filters = $criterion['filters'] ?? [];
            
            $value = $this->getCriterionValue($student, $type, $filters);
            
            if ($value < $threshold) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get the value for a specific criterion type
     */
  public function getCriterionValue(Student $student, string $type, array $filters = []): int
{
    return match($type) {
        'xp' => $this->getXpValue($student),
        'lessons_completed' => $this->getLessonsCompleted($student, $filters),
        'quizzes_passed' => $this->getQuizzesPassed($student, $filters),
        'perfect_scores' => $this->getPerfectScores($student, $filters),
        'gesture_mastered' => $this->getGestureMastered($student, $filters),
        'gesture_mastered_percentage' => $this->getGestureMasteredPercentage($student, $filters), // ← ADD THIS
        'streak_days' => $this->getStreakDays($student),
        'gesture_attempts' => $this->getGestureAttempts($student, $filters),
        'leaderboard_top' => $this->getLeaderboardTop($student, $filters),
        'level' => $this->getLevelValue($student, $filters),
        'modules_completed' => $this->getModulesCompleted($student, $filters),
        default => 0,
    };
}

    // ─── CRITERIA VALUE GETTERS ──────────────────────────────

    protected function getXpValue(Student $student): int
    {
        return (int) ($student->total_xp ?? 0);
    }

    protected function getLessonsCompleted(Student $student, array $filters = []): int
    {
        $query = DB::table('student_lesson_progress as slp')
            ->join('lessons as l', 'slp.lesson_id', '=', 'l.lesson_id')
            ->where('slp.student_id', $student->student_id)
            ->where('slp.lesson_completed', true);
        
        // Filter by difficulty/level
        if (isset($filters['difficulty'])) {
            $query->where('l.difficulty', $filters['difficulty']);
        }
        
        // Filter by module
        if (isset($filters['module_id'])) {
            $query->where('l.module_id', $filters['module_id']);
        }
        
        return $query->count();
    }

    protected function getQuizzesPassed(Student $student, array $filters = []): int
    {
        $query = DB::table('quiz_attempts as qa')
            ->join('quizzes as q', 'qa.quiz_id', '=', 'q.quiz_id')
            ->where('qa.student_id', $student->student_id)
            ->where('qa.status', 'completed')
            ->where('qa.percentage', '>=', 60);
        
        // Filter by minimum percentage (e.g., 80%)
        if (isset($filters['min_percentage'])) {
            $query->where('qa.percentage', '>=', $filters['min_percentage']);
        }
        
        // Count DISTINCT quizzes
        return $query->distinct('qa.quiz_id')->count('qa.quiz_id');
    }

    protected function getPerfectScores(Student $student, array $filters = []): int
    {
        $query = DB::table('quiz_attempts as qa')
            ->join('quizzes as q', 'qa.quiz_id', '=', 'q.quiz_id')
            ->where('qa.student_id', $student->student_id)
            ->where('qa.percentage', '=', 100);
        
        // Filter by difficulty
        if (isset($filters['difficulty'])) {
            $query->where('q.difficulty', $filters['difficulty']);
        }
        
        // Count distinct quizzes with 100%
        return $query->distinct('qa.quiz_id')->count('qa.quiz_id');
    }

    protected function getGestureMastered(Student $student, array $filters = []): int
    {
        $query = DB::table('gesture_performances as gp')
            ->join('gestures as g', 'gp.gesture_id', '=', 'g.gesture_id')
            ->where('gp.student_id', $student->student_id)
            ->whereIn('gp.mastery_level', ['mastered', 'proficient']);
        
        // Filter by module
        if (isset($filters['module_name'])) {
            $query->join('gesture_modules as gm', 'g.module_id', '=', 'gm.module_id')
                  ->where('gm.name', $filters['module_name']);
        }
        
        return $query->count();
    }

    protected function getStreakDays(Student $student): int
    {
        return (int) ($student->streak_days ?? 0);
    }

    protected function getGestureAttempts(Student $student, array $filters = []): int
    {
        $query = DB::table('gesture_performances')
            ->where('student_id', $student->student_id);
        
        // Sum attempts or count sessions?
        // Let's count distinct session_ids or sum attempts
        if (isset($filters['distinct_sessions'])) {
            return $query->distinct('session_id')->count('session_id');
        }
        
        return $query->sum('attempts') ?? 0;
    }

    protected function getLeaderboardTop(Student $student, array $filters = []): int
    {
        // Check if student has ever been #1 on any lesson leaderboard
        $rank = DB::table('quiz_attempts as qa')
            ->join('quizzes as q', 'qa.quiz_id', '=', 'q.quiz_id')
            ->where('qa.student_id', $student->student_id)
            ->where('qa.status', 'completed')
            ->groupBy('q.lesson_id')
            ->select('q.lesson_id', DB::raw('MAX(qa.percentage) as best_score'))
            ->having('best_score', '>=', 80)
            ->count();
        
        return $rank > 0 ? 1 : 0;
    }

    protected function getLevelValue(Student $student, array $filters = []): int
    {
        $currentLevel = $student->fsl_mastery_level ?? 'Beginner';
        $targetLevel = $filters['target_level'] ?? null;
        
        if ($targetLevel && $currentLevel === $targetLevel) {
            return 1; // Achievement unlocked
        }
        
        // For progression: reached Intermediate
        $levelOrder = ['Beginner' => 1, 'Intermediate' => 2, 'Advanced' => 3, 'Graduated' => 4];
        $current = $levelOrder[$currentLevel] ?? 0;
        $target = $levelOrder[$targetLevel] ?? 0;
        
        return $current >= $target ? 1 : 0;
    }

  protected function getModulesCompleted(Student $student, array $filters = []): int
{
    $query = DB::table('gesture_modules as gm')
        ->whereNotExists(function ($sub) use ($student) {
            $sub->select(DB::raw(1))
                ->from('gestures as g')
                ->leftJoin('gesture_performances as gp', function ($join) use ($student) {
                    $join->on('g.gesture_id', '=', 'gp.gesture_id')
                         ->where('gp.student_id', '=', $student->student_id);
                })
                ->whereColumn('g.module_id', 'gm.module_id')
                ->where(function ($q) {
                    $q->whereNull('gp.mastery_level')
                      ->orWhereNotIn('gp.mastery_level', ['mastered', 'proficient']);
                });
        });
    
    if (isset($filters['module_name'])) {
        $query->where('gm.name', $filters['module_name']);
    }
    
    return $query->count();
}

    protected function getAchievementBonusXp(Achievement $achievement): int
    {
        // Map achievement code to bonus XP
        $bonusMap = [
            'xp_50' => 5,
            'xp_100' => 10,
            'xp_250' => 15,
            'xp_500' => 25,
            'xp_1000' => 50,
            'xp_2500' => 75,
            'xp_5000' => 100,
            'beginner_welcome' => 10,
            'beginner_10_lessons' => 20,
            'intermediate_reached' => 30,
            'advanced_reached' => 50,
            'graduated' => 100,
            'streak_7' => 20,
            'streak_30' => 50,
            'all_badges' => 200,
            'leaderboard_top' => 50,
            'quiz_whiz' => 20,
        ];
        
        return $bonusMap[$achievement->code] ?? 10;
    }

    // 2. Add this new method after getModulesCompleted():
/**
 * Get percentage of gestures mastered in a module (returns percentage as integer)
 * e.g., 50% mastery = returns 50
 */
protected function getGestureMasteredPercentage(Student $student, array $filters = []): int
{
    $moduleName = $filters['module_name'] ?? null;
    
    if (!$moduleName) {
        return 0;
    }
    
    // Handle special case for 'alphabet' (combine both parts)
    if ($moduleName === 'alphabet') {
        // Get all alphabet modules
        $modules = DB::table('gesture_modules')
            ->where('name', 'LIKE', 'alphabet_part%')
            ->pluck('module_id')
            ->toArray();
        
        if (empty($modules)) {
            return 0;
        }
        
        // Get all gestures in alphabet modules
        $gestureIds = DB::table('gestures')
            ->whereIn('module_id', $modules)
            ->pluck('gesture_id')
            ->toArray();
        
        $totalGestures = count($gestureIds);
        if ($totalGestures === 0) {
            return 0;
        }
        
        // Count mastered + proficient
        $masteredCount = DB::table('gesture_performances')
            ->where('student_id', $student->student_id)
            ->whereIn('gesture_id', $gestureIds)
            ->whereIn('mastery_level', ['mastered', 'proficient'])
            ->count();
        
        return (int) round(($masteredCount / $totalGestures) * 100);
    }
    
    // Get module ID by name
    $module = DB::table('gesture_modules')
        ->where('name', $moduleName)
        ->first();
    
    if (!$module) {
        return 0;
    }
    
    // Get all gestures in this module
    $gestureIds = DB::table('gestures')
        ->where('module_id', $module->module_id)
        ->pluck('gesture_id')
        ->toArray();
    
    $totalGestures = count($gestureIds);
    if ($totalGestures === 0) {
        return 0;
    }
    
    // Count mastered + proficient
    $masteredCount = DB::table('gesture_performances')
        ->where('student_id', $student->student_id)
        ->whereIn('gesture_id', $gestureIds)
        ->whereIn('mastery_level', ['mastered', 'proficient'])
        ->count();
    
    return (int) round(($masteredCount / $totalGestures) * 100);
}

}
