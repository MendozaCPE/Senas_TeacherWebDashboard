<?php

namespace App\Services;

use App\Models\Gesture;
use App\Models\GesturePerformance;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentLessonProgress;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Generates all Senya Insights from real database records.
 * Each insight is an array: [ 'icon', 'color', 'category', 'text' ]
 */
class SenyaInsightsService
{
    private int   $teacherId;
    private Collection $studentIds;
    private Collection $lessonIds;

    public function __construct(int $teacherId)
    {
        $this->teacherId  = $teacherId;
        $this->studentIds = Student::where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->pluck('student_id');
        $this->lessonIds  = Lesson::where('teacher_id', $teacherId)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->pluck('lesson_id');
    }

    /** Build and return all insights (each a rich array). */
    public function generate(): array
    {
        if ($this->studentIds->isEmpty()) {
            return [];
        }

        $insights = array_merge(
            $this->topPerformers(),
            $this->mostActiveStudents(),
            $this->leastActiveStudents(),
            $this->studentsNeedingAttention(),
            $this->consistentlyPerfect(),
            $this->highestStreaks(),
            $this->mostFailedAttempts(),
            $this->improvingStudents(),
            $this->difficultGestures(),
            $this->weakestLessons(),
            $this->classPerformanceTrend(),
        );

        // Remove nulls
        return array_values(array_filter($insights));
    }

    // ── 1. Top Performing Students ────────────────────────────────────────────
    private function topPerformers(): array
    {
        $results = StudentLessonProgress::whereIn('student_id', $this->studentIds)
            ->whereIn('lesson_id', $this->lessonIds)
            ->whereNotNull('quiz_score')
            ->select('student_id',
                DB::raw('ROUND(AVG(quiz_score),1) as avg_score'),
                DB::raw('COUNT(*) as attempts'))
            ->groupBy('student_id')
            ->having('attempts', '>=', 2)
            ->orderByDesc('avg_score')
            ->limit(3)
            ->get();

        if ($results->isEmpty()) return [];

        $insights = [];
        foreach ($results as $i => $row) {
            $student = Student::find($row->student_id);
            if (!$student) continue;
            $name    = e($student->first_name . ' ' . $student->last_name);
            $rank    = ['🥇', '🥈', '🥉'][$i] ?? '⭐';
            $insights[] = $this->make(
                'emoji_events', '#10B981', 'Top Performer',
                "{$rank} <strong>{$name}</strong> is among your top performers with an average quiz score of <strong>{$row->avg_score}%</strong> across {$row->attempts} quizzes."
            );
        }
        return $insights;
    }

    // ── 2. Most Active Students ───────────────────────────────────────────────
    private function mostActiveStudents(): array
    {
        $insights = [];

        // By lessons completed
        $top = StudentLessonProgress::whereIn('student_id', $this->studentIds)
            ->whereIn('lesson_id', $this->lessonIds)
            ->where('lesson_completed', 1)
            ->select('student_id', DB::raw('COUNT(*) as completed'))
            ->groupBy('student_id')
            ->orderByDesc('completed')
            ->first();

        if ($top) {
            $student = Student::find($top->student_id);
            if ($student) {
                $name = e($student->first_name . ' ' . $student->last_name);
                $insights[] = $this->make(
                    'local_fire_department', '#F97316', 'Most Active',
                    "🔥 <strong>{$name}</strong> is your most active student — completed <strong>{$top->completed} lesson" . ($top->completed !== 1 ? 's' : '') . "</strong> so far!"
                );
            }
        }

        // Highest streak
        $topStreak = Student::whereIn('student_id', $this->studentIds)
            ->where('streak_days', '>', 0)
            ->orderByDesc('streak_days')
            ->first(['student_id', 'first_name', 'last_name', 'streak_days']);

        if ($topStreak && $topStreak->streak_days >= 3) {
            $name = e($topStreak->first_name . ' ' . $topStreak->last_name);
            $insights[] = $this->make(
                'whatshot', '#F59E0B', 'Highest Streak',
                "🔥 <strong>{$name}</strong> is on a <strong>{$topStreak->streak_days}-day practice streak</strong>! A great example of consistency for the class."
            );
        }

        return $insights;
    }

    // ── 3. Least Active Students ──────────────────────────────────────────────
    private function leastActiveStudents(): array
    {
        $insights = [];

        // Students with no activity in last 14 days
        $inactiveIds = StudentLessonProgress::whereIn('student_id', $this->studentIds)
            ->whereIn('lesson_id', $this->lessonIds)
            ->select('student_id', DB::raw('MAX(last_accessed_at) as last_seen'))
            ->groupBy('student_id')
            ->get()
            ->filter(fn($r) => $r->last_seen && Carbon::parse($r->last_seen)->lt(Carbon::now()->subDays(14)))
            ->pluck('student_id');

        if ($inactiveIds->isNotEmpty()) {
            $count   = $inactiveIds->count();
            $sample  = Student::whereIn('student_id', $inactiveIds->take(2))->get();
            $names   = $sample->map(fn($s) => '<strong>' . e($s->first_name . ' ' . $s->last_name) . '</strong>')->join(', ');
            $label   = $count > 2 ? " and {$count} other student" . ($count > 3 ? 's' : '') : '';
            $insights[] = $this->make(
                'schedule', '#64748B', 'Inactive Students',
                "⚠️ {$names}{$label} " . ($count > 1 ? 'have' : 'has') . " not practiced in over <strong>14 days</strong>. A gentle reminder could help re-engage them."
            );
        }

        // Students who have never completed a lesson
        $neverCompleted = Student::whereIn('student_id', $this->studentIds)
            ->whereDoesntHave('progress', function ($q) {
                $q->whereIn('lesson_id', $this->lessonIds)->where('lesson_completed', 1);
            })
            ->count();

        if ($neverCompleted > 0) {
            $insights[] = $this->make(
                'hourglass_empty', '#94A3B8', 'No Completed Lessons',
                "📋 <strong>{$neverCompleted} student" . ($neverCompleted !== 1 ? 's have' : ' has') . "</strong> not completed any lesson yet. Consider assigning easier content to build confidence."
            );
        }

        return $insights;
    }

    // ── 4. Students Needing Attention ─────────────────────────────────────────
    private function studentsNeedingAttention(): array
    {
        $insights = [];

        // Declining performance: avg quiz score < 60%
        $struggling = StudentLessonProgress::whereIn('student_id', $this->studentIds)
            ->whereIn('lesson_id', $this->lessonIds)
            ->whereNotNull('quiz_score')
            ->select('student_id', DB::raw('ROUND(AVG(quiz_score),1) as avg_score'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('student_id')
            ->having('avg_score', '<', 60)
            ->having('cnt', '>=', 1)
            ->orderBy('avg_score')
            ->get();

        foreach ($struggling->take(2) as $row) {
            $student = Student::find($row->student_id);
            if (!$student) continue;
            $name = e($student->first_name . ' ' . $student->last_name);
            $insights[] = $this->make(
                'warning', '#EF4444', 'Needs Attention',
                "🚨 <strong>{$name}</strong> is averaging only <strong>{$row->avg_score}%</strong> on quizzes. They may need individual support or lesson review."
            );
        }

        // Most consecutive wrong gesture attempts
        try {
            $consec = GesturePerformance::whereIn('student_id', $this->studentIds)
                ->where('consecutive_wrong', '>=', 5)
                ->orderByDesc('consecutive_wrong')
                ->with(['student', 'gesture'])
                ->first();

            if ($consec && $consec->student && $consec->gesture) {
                $name    = e($consec->student->first_name . ' ' . $consec->student->last_name);
                $gesture = e($consec->gesture->display_name ?? $consec->gesture->name);
                $insights[] = $this->make(
                    'gesture', '#DC2626', 'Repeated Mistakes',
                    "✋ <strong>{$name}</strong> has made <strong>{$consec->consecutive_wrong} consecutive wrong attempts</strong> on the gesture \"<strong>{$gesture}</strong>\". Direct practice guidance is recommended."
                );
            }
        } catch (\Throwable $e) {}

        return $insights;
    }

    // ── 5. Consistently Perfect Students ─────────────────────────────────────
    private function consistentlyPerfect(): array
    {
        $perfect = StudentLessonProgress::whereIn('student_id', $this->studentIds)
            ->whereIn('lesson_id', $this->lessonIds)
            ->where('quiz_score', 100)
            ->select('student_id', DB::raw('COUNT(*) as perfect_count'))
            ->groupBy('student_id')
            ->having('perfect_count', '>=', 2)
            ->orderByDesc('perfect_count')
            ->first();

        if (!$perfect) return [];

        $student = Student::find($perfect->student_id);
        if (!$student) return [];

        $name = e($student->first_name . ' ' . $student->last_name);
        return [$this->make(
            'star', '#8B5CF6', 'Perfect Scorer',
            "⭐ <strong>{$name}</strong> has achieved a <strong>perfect score</strong> on <strong>{$perfect->perfect_count} quiz" . ($perfect->perfect_count !== 1 ? 'zes' : '') . "</strong>! Outstanding performance."
        )];
    }

    // ── 6. Highest Streaks ────────────────────────────────────────────────────
    private function highestStreaks(): array
    {
        $top = Student::whereIn('student_id', $this->studentIds)
            ->where('streak_days', '>=', 5)
            ->orderByDesc('streak_days')
            ->take(3)
            ->get(['student_id', 'first_name', 'last_name', 'streak_days']);

        if ($top->isEmpty()) return [];

        $insights = [];
        foreach ($top as $s) {
            $name = e($s->first_name . ' ' . $s->last_name);
            $insights[] = $this->make(
                'local_fire_department', '#F97316', 'Streak Leader',
                "🔥 <strong>{$name}</strong> has maintained a <strong>{$s->streak_days}-day practice streak</strong>. Their consistency is paying off!"
            );
        }
        return $insights;
    }

    // ── 7. Most Failed Attempts ───────────────────────────────────────────────
    private function mostFailedAttempts(): array
    {
        $insights = [];

        // By quiz attempts with status = 'failed'
        try {
            $failed = DB::table('quiz_attempts')
                ->whereIn('student_id', $this->studentIds)
                ->where('status', 'failed')
                ->select('student_id', DB::raw('COUNT(*) as fail_count'))
                ->groupBy('student_id')
                ->having('fail_count', '>=', 3)
                ->orderByDesc('fail_count')
                ->first();

            if ($failed) {
                $student = Student::find($failed->student_id);
                if ($student) {
                    $name = e($student->first_name . ' ' . $student->last_name);
                    $insights[] = $this->make(
                        'cancel', '#EF4444', 'Most Failed Attempts',
                        "❌ <strong>{$name}</strong> has failed quizzes <strong>{$failed->fail_count} times</strong>. They may need additional support before retrying."
                    );
                }
            }
        } catch (\Throwable $e) {}

        // Gestures with highest wrong attempts
        try {
            $hardGestures = GesturePerformance::whereIn('student_id', $this->studentIds)
                ->where('wrong_attempts', '>', 0)
                ->select('gesture_id',
                    DB::raw('SUM(wrong_attempts) as total_wrong'),
                    DB::raw('SUM(attempts) as total_attempts'),
                    DB::raw('COUNT(DISTINCT student_id) as student_count'))
                ->groupBy('gesture_id')
                ->orderByDesc('total_wrong')
                ->limit(2)
                ->get();

            foreach ($hardGestures as $row) {
                $gesture = Gesture::find($row->gesture_id);
                if (!$gesture) continue;
                $gName    = e($gesture->display_name ?? $gesture->name);
                $errRate  = $row->total_attempts > 0
                    ? round(($row->total_wrong / $row->total_attempts) * 100)
                    : 0;
                $insights[] = $this->make(
                    'front_hand', '#F59E0B', 'Difficult Gesture',
                    "✋ The gesture \"<strong>{$gName}</strong>\" has a <strong>{$errRate}% error rate</strong> ({$row->total_wrong} wrong out of {$row->total_attempts} attempts across {$row->student_count} student" . ($row->student_count !== 1 ? 's' : '') . ")."
                );
            }
        } catch (\Throwable $e) {}

        return $insights;
    }

    // ── 8. Improving Students ─────────────────────────────────────────────────
    private function improvingStudents(): array
    {
        // Compare avg score first half vs second half of quiz attempts per student
        $insights = [];

        try {
            $studentScores = DB::table('quiz_attempts')
                ->whereIn('student_id', $this->studentIds)
                ->where('status', 'completed')
                ->select('student_id', 'percentage', 'completed_at')
                ->orderBy('student_id')
                ->orderBy('completed_at')
                ->get()
                ->groupBy('student_id');

            foreach ($studentScores as $sid => $attempts) {
                if ($attempts->count() < 4) continue;
                $mid    = (int) floor($attempts->count() / 2);
                $early  = $attempts->take($mid)->avg('percentage');
                $recent = $attempts->skip($mid)->avg('percentage');
                $delta  = round($recent - $early, 1);

                if ($delta >= 15) {
                    $student = Student::find($sid);
                    if (!$student) continue;
                    $name = e($student->first_name . ' ' . $student->last_name);
                    $insights[] = $this->make(
                        'trending_up', '#10B981', 'Improving',
                        "📈 <strong>{$name}</strong> has improved their quiz scores by <strong>+{$delta}%</strong> over time (from {$early}% → {$recent}%). Great progress!"
                    );
                    break; // one improvement insight is enough
                }
            }
        } catch (\Throwable $e) {}

        return $insights;
    }

    // ── 9. Difficult Gestures (class-wide) ────────────────────────────────────
    private function difficultGestures(): array
    {
        $insights = [];

        try {
            // Top gesture where mastery_level = 'needs_practice' across most students
            $unmastered = GesturePerformance::whereIn('student_id', $this->studentIds)
                ->where('mastery_level', 'needs_practice')
                ->where('attempts', '>=', 3)
                ->select('gesture_id', DB::raw('COUNT(DISTINCT student_id) as struggling_count'))
                ->groupBy('gesture_id')
                ->orderByDesc('struggling_count')
                ->first();

            if ($unmastered) {
                $gesture = Gesture::find($unmastered->gesture_id);
                if ($gesture) {
                    $gName = e($gesture->display_name ?? $gesture->name);
                    $insights[] = $this->make(
                        'pan_tool', '#EF4444', 'Class-Wide Struggle',
                        "🤚 <strong>{$unmastered->struggling_count} student" . ($unmastered->struggling_count !== 1 ? 's' : '') . "</strong> are still struggling with the \"<strong>{$gName}</strong>\" gesture after multiple attempts. Consider a group review session."
                    );
                }
            }

            // Most mastered gesture (positive insight)
            $mastered = GesturePerformance::whereIn('student_id', $this->studentIds)
                ->where('is_mastered', true)
                ->select('gesture_id', DB::raw('COUNT(DISTINCT student_id) as mastered_count'))
                ->groupBy('gesture_id')
                ->orderByDesc('mastered_count')
                ->first();

            if ($mastered && $mastered->mastered_count >= 2) {
                $gesture = Gesture::find($mastered->gesture_id);
                if ($gesture) {
                    $gName = e($gesture->display_name ?? $gesture->name);
                    $insights[] = $this->make(
                        'check_circle', '#10B981', 'Mastered Gesture',
                        "✅ The \"<strong>{$gName}</strong>\" gesture has been mastered by <strong>{$mastered->mastered_count} student" . ($mastered->mastered_count !== 1 ? 's' : '') . "</strong> — the highest mastery rate in your class!"
                    );
                }
            }
        } catch (\Throwable $e) {}

        return $insights;
    }

    // ── 10. Weakest Lessons ───────────────────────────────────────────────────
    private function weakestLessons(): array
    {
        $insights = [];

        $weak = StudentLessonProgress::whereIn('student_id', $this->studentIds)
            ->whereIn('lesson_id', $this->lessonIds)
            ->whereNotNull('quiz_score')
            ->select('lesson_id',
                DB::raw('ROUND(AVG(quiz_score),1) as avg_score'),
                DB::raw('COUNT(DISTINCT student_id) as student_count'))
            ->groupBy('lesson_id')
            ->having('student_count', '>=', 2)
            ->orderBy('avg_score')
            ->limit(2)
            ->get();

        foreach ($weak as $row) {
            $lesson = Lesson::find($row->lesson_id);
            if (!$lesson) continue;
            $title = e($lesson->title);
            $insights[] = $this->make(
                'menu_book', '#F97316', 'Weakest Lesson',
                "📚 The lesson \"<strong>{$title}</strong>\" has an average score of <strong>{$row->avg_score}%</strong> across {$row->student_count} students — the lowest in your class. A review may help."
            );
        }

        // Best lesson (positive)
        $best = StudentLessonProgress::whereIn('student_id', $this->studentIds)
            ->whereIn('lesson_id', $this->lessonIds)
            ->whereNotNull('quiz_score')
            ->select('lesson_id',
                DB::raw('ROUND(AVG(quiz_score),1) as avg_score'),
                DB::raw('COUNT(DISTINCT student_id) as student_count'))
            ->groupBy('lesson_id')
            ->having('student_count', '>=', 2)
            ->having('avg_score', '>=', 80)
            ->orderByDesc('avg_score')
            ->first();

        if ($best) {
            $lesson = Lesson::find($best->lesson_id);
            if ($lesson) {
                $title = e($lesson->title);
                $insights[] = $this->make(
                    'workspace_premium', '#8B5CF6', 'Best Lesson',
                    "🏆 \"<strong>{$title}</strong>\" is your class's strongest lesson with an average score of <strong>{$best->avg_score}%</strong>. Students have mastered this well!"
                );
            }
        }

        return $insights;
    }

    // ── 11. Class Performance Trend ───────────────────────────────────────────
    private function classPerformanceTrend(): array
    {
        $thisWeek = StudentLessonProgress::whereIn('student_id', $this->studentIds)
            ->whereIn('lesson_id', $this->lessonIds)
            ->whereNotNull('quiz_score')
            ->where('updated_at', '>=', Carbon::now()->subDays(7))
            ->avg('quiz_score');

        $lastWeek = StudentLessonProgress::whereIn('student_id', $this->studentIds)
            ->whereIn('lesson_id', $this->lessonIds)
            ->whereNotNull('quiz_score')
            ->whereBetween('updated_at', [Carbon::now()->subDays(14), Carbon::now()->subDays(7)])
            ->avg('quiz_score');

        if ($thisWeek === null || $lastWeek === null) return [];

        $thisWeek = round($thisWeek, 1);
        $lastWeek = round($lastWeek, 1);
        $delta    = round($thisWeek - $lastWeek, 1);

        if ($delta >= 5) {
            return [$this->make(
                'trending_up', '#10B981', 'Class Trending Up',
                "📈 Your class average quiz score <strong>improved by +{$delta}%</strong> this week ({$lastWeek}% → {$thisWeek}%). Great momentum — keep it up!"
            )];
        } elseif ($delta <= -5) {
            return [$this->make(
                'trending_down', '#EF4444', 'Class Trending Down',
                "📉 Your class average quiz score <strong>dropped by {$delta}%</strong> this week ({$lastWeek}% → {$thisWeek}%). Consider revisiting recent lessons or providing extra practice."
            )];
        } else {
            return [$this->make(
                'bar_chart', '#64748B', 'Class Stable',
                "📊 Your class performance is <strong>consistent</strong> this week — avg score {$thisWeek}% (vs {$lastWeek}% last week). Keep encouraging regular practice!"
            )];
        }
    }

    // ── Helper ────────────────────────────────────────────────────────────────
    private function make(string $icon, string $color, string $category, string $text): array
    {
        return compact('icon', 'color', 'category', 'text');
    }
}
