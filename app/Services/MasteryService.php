<?php

namespace App\Services;

use App\Models\Student;
use App\Models\GesturePerformance;
use App\Models\Gesture;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasteryService
{
    /**
     * Calculate mastery probability for a specific skill (gesture)
     * Uses Laplace smoothing: (successes + 1) / (attempts + 2)
     * 0.0 = never attempted, 0.5 = 50% success, 1.0 = perfect mastery
     */
    public function getSkillMastery(int $studentId, int $gestureId): float
    {
        $performance = GesturePerformance::where('student_id', $studentId)
            ->where('gesture_id', $gestureId)
            ->first();

        if (!$performance || $performance->attempts === 0) {
            return 0.0;
        }

        $successes = $performance->successful_attempts ?? 0;
        $attempts = $performance->attempts ?? 0;

        // Laplace smoothing - gives a conservative estimate
        return ($successes + 1) / ($attempts + 2);
    }

    /**
     * Get all weak skills for a student (mastery < threshold)
     * Returns sorted from weakest to strongest
     */
    public function getWeakSkills(int $studentId, float $threshold = 0.6, int $limit = 20): array
    {
        // Get all gestures the student has practiced
        $performances = GesturePerformance::where('student_id', $studentId)
            ->with('gesture')
            ->get();

        $weakSkills = [];

        foreach ($performances as $perf) {
            $mastery = ($perf->successful_attempts + 1) / ($perf->attempts + 2);
            
            if ($mastery < $threshold && $perf->attempts > 0) {
                $weakSkills[] = [
                    'gesture_id' => $perf->gesture_id,
                    'gesture_name' => $perf->gesture->name ?? 'Unknown',
                    'display_name' => $perf->gesture->display_name ?? $perf->gesture->name,
                    'mastery' => round($mastery, 2),
                    'attempts' => $perf->attempts,
                    'successes' => $perf->successful_attempts,
                    'wrong_attempts' => $perf->wrong_attempts,
                ];
            }
        }

        // Sort by mastery (lowest first - needs most help)
        usort($weakSkills, function($a, $b) {
            return $a['mastery'] <=> $b['mastery'];
        });

        return array_slice($weakSkills, 0, $limit);
    }

    /**
     * Get skills the student has never practiced (0 mastery)
     * Grouped by difficulty level
     */
public function getNeverPracticedSkills(int $studentId, int $limit = 10): array
{
    $practicedIds = GesturePerformance::where('student_id', $studentId)
        ->pluck('gesture_id')
        ->toArray();

    // ✅ REMOVE 'is_active' check if column doesn't exist
    $neverPracticed = Gesture::whereNotIn('gesture_id', $practicedIds)
        // ->where('is_active', true)  // ← COMMENT THIS OUT
        ->orderBy('difficulty', 'asc')
        ->limit($limit)
        ->get();

        return $neverPracticed->map(function ($gesture) {
            return [
                'gesture_id' => $gesture->gesture_id,
                'gesture_name' => $gesture->name,
                'display_name' => $gesture->display_name ?? $gesture->name,
                'mastery' => 0.0,
                'attempts' => 0,
                'never_practiced' => true,
                'difficulty' => $gesture->difficulty ?? 'beginner',
            ];
        })->toArray();
    }

    /**
     * Get the student's overall mastery level across all skills
     */
    public function getOverallMastery(int $studentId): array
    {
        $performances = GesturePerformance::where('student_id', $studentId)->get();
        
        if ($performances->isEmpty()) {
            return [
                'average_mastery' => 0,
                'total_skills' => 0,
                'practiced_skills' => 0,
                'mastered_count' => 0,
                'weak_count' => 0,
                'never_practiced' => Gesture::count(),  // Remove where('is_active', true)
            ];
        }

        $totalMastery = 0;
        $masteredCount = 0;
        $weakCount = 0;

        foreach ($performances as $perf) {
            $mastery = ($perf->successful_attempts + 1) / ($perf->attempts + 2);
            $totalMastery += $mastery;
            
            if ($mastery >= 0.8) $masteredCount++;
            if ($mastery < 0.6) $weakCount++;
        }

       $totalSkills = Gesture::count();  // Remove where('is_active', true)
        $practicedSkills = $performances->count();

        return [
            'average_mastery' => $practicedSkills > 0 ? round(($totalMastery / $practicedSkills) * 100, 1) : 0,
            'total_skills' => $totalSkills,
            'practiced_skills' => $practicedSkills,
            'mastered_count' => $masteredCount,
            'weak_count' => $weakCount,
            'never_practiced' => $totalSkills - $practicedSkills,
            'progress_percentage' => $totalSkills > 0 ? round(($masteredCount / $totalSkills) * 100, 1) : 0,
        ];
    }

   /**
 * Find lessons that cover specific weak skills
 * This connects the student's weak areas to lessons
 * Searches across: gesture_demo content, lesson title, description, and quiz questions
 */
public function findLessonsForWeakSkills(int $studentId, array $weakSkills, int $limit = 20): array
{
    $skillNames = array_column($weakSkills, 'gesture_name');
    
    if (empty($skillNames)) {
        return [];
    }

    // Convert to uppercase for case-insensitive matching
    $skillNamesUpper = array_map('strtoupper', $skillNames);

    // Find lessons that contain these gesture names in:
    // 1. gesture_demo content (gesture_name field)
    // 2. Lesson title
    // 3. Lesson description
    // 4. Quiz questions
    $lessons = Lesson::where('status', 'published')
        ->where(function($query) use ($skillNamesUpper, $skillNames) {
            
            // 1. Match by gesture_demo content (case-insensitive)
            $query->whereHas('contents', function($q) use ($skillNamesUpper) {
                $q->where('content_type', 'gesture_demo')
                  ->where(function($inner) use ($skillNamesUpper) {
                      foreach ($skillNamesUpper as $name) {
                          $inner->orWhereRaw('UPPER(gesture_name) = ?', [$name]);
                      }
                  });
            });
            
            // 2. Match by lesson title (case-insensitive)
            foreach ($skillNames as $name) {
                $query->orWhere('title', 'LIKE', '%' . $name . '%');
            }
            
            // 3. Match by lesson description (case-insensitive)
            foreach ($skillNames as $name) {
                $query->orWhere('description', 'LIKE', '%' . $name . '%');
            }
            
            // 4. Match by quiz questions (case-insensitive)
            $query->orWhereHas('quiz.questions', function($q) use ($skillNames) {
                foreach ($skillNames as $name) {
                    $q->orWhere('question_text', 'LIKE', '%' . $name . '%');
                }
            });
        })
        ->with(['contents', 'quiz', 'module'])
        ->orderBy('difficulty', 'asc')
        ->limit($limit)
        ->get();

    $result = [];
    foreach ($lessons as $lesson) {
        // Find which weak skills this lesson covers
        $coveredSkills = [];
        $foundSkillNames = [];
        
        // Check in gesture_demo content
        foreach ($lesson->contents as $content) {
            if ($content->content_type === 'gesture_demo') {
                $contentName = strtoupper($content->gesture_name ?? '');
                foreach ($weakSkills as $weak) {
                    $weakName = strtoupper($weak['gesture_name'] ?? '');
                    if ($contentName === $weakName && !in_array($weak['gesture_id'], array_column($coveredSkills, 'gesture_id'))) {
                        $coveredSkills[] = $weak;
                        $foundSkillNames[] = $weak['gesture_name'];
                    }
                }
            }
        }
        
        // If no gesture_demo content matched, check title and description
        if (empty($coveredSkills)) {
            $lessonTitle = strtoupper($lesson->title ?? '');
            $lessonDesc = strtoupper($lesson->description ?? '');
            
            foreach ($weakSkills as $weak) {
                $weakName = strtoupper($weak['gesture_name'] ?? '');
                if (strpos($lessonTitle, $weakName) !== false || 
                    strpos($lessonDesc, $weakName) !== false) {
                    if (!in_array($weak['gesture_id'], array_column($coveredSkills, 'gesture_id'))) {
                        $coveredSkills[] = $weak;
                        $foundSkillNames[] = $weak['gesture_name'];
                    }
                }
            }
        }
        
        // If still no match, check quiz questions
        if (empty($coveredSkills) && $lesson->quiz) {
            foreach ($lesson->quiz->questions as $question) {
                $questionText = strtoupper($question->question_text ?? '');
                foreach ($weakSkills as $weak) {
                    $weakName = strtoupper($weak['gesture_name'] ?? '');
                    if (strpos($questionText, $weakName) !== false) {
                        if (!in_array($weak['gesture_id'], array_column($coveredSkills, 'gesture_id'))) {
                            $coveredSkills[] = $weak;
                            $foundSkillNames[] = $weak['gesture_name'];
                        }
                    }
                }
            }
        }

        $result[] = [
            'lesson' => $lesson,
            'covered_skills' => $coveredSkills,
            'weak_skill_count' => count($coveredSkills),
            'found_skill_names' => $foundSkillNames,
            'recommendation_reason' => $this->getRecommendationReason($coveredSkills),
        ];
    }

    // Sort by number of weak skills covered (most coverage first)
    usort($result, function($a, $b) {
        return $b['weak_skill_count'] <=> $a['weak_skill_count'];
    });

    return $result;
}
    /**
     * Get human-readable recommendation reason
     */
    private function getRecommendationReason(array $skills): string
    {
        if (empty($skills)) {
            return 'New skill to learn';
        }

        $count = count($skills);
        $names = array_column($skills, 'display_name');
        
        if ($count === 1) {
            return "Practice '{$names[0]}' (needs improvement)";
        } elseif ($count <= 3) {
            return "Practice: " . implode(', ', $names) . " (needs improvement)";
        } else {
            return "Practice " . $count . " gestures that need improvement";
        }
    }

    /**
     * Update mastery in real-time after a practice session
     */
    public function updateMasteryAfterPractice(int $studentId, int $gestureId): array
    {
        $performance = GesturePerformance::where('student_id', $studentId)
            ->where('gesture_id', $gestureId)
            ->first();

        if (!$performance) {
            return ['mastery' => 0, 'level' => 'never_practiced'];
        }

        $mastery = ($performance->successful_attempts + 1) / ($performance->attempts + 2);
        
        // Determine mastery level
        $level = $this->getMasteryLevel($mastery);

        return [
            'mastery' => round($mastery, 2),
            'level' => $level,
            'attempts' => $performance->attempts,
            'successes' => $performance->successful_attempts,
        ];
    }

    /**
     * Get human-readable mastery level
     */
    public function getMasteryLevel(float $mastery): string
    {
        if ($mastery >= 0.9) return 'expert';
        if ($mastery >= 0.75) return 'proficient';
        if ($mastery >= 0.6) return 'competent';
        if ($mastery >= 0.4) return 'learning';
        return 'needs_practice';
    }

    /**
     * Get the next recommended skill based on learning path
     */
    public function getNextRecommendedSkill(int $studentId, string $learningGoal): ?array
    {
        $weakSkills = $this->getWeakSkills($studentId, 0.6, 5);
        
        // If there are weak skills, recommend the weakest one first
        if (!empty($weakSkills)) {
            return $weakSkills[0];
        }

        // If no weak skills, recommend a new skill
        $neverPracticed = $this->getNeverPracticedSkills($studentId, 1);
        if (!empty($neverPracticed)) {
            return $neverPracticed[0];
        }

        return null;
    }
}