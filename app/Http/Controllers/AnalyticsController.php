<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentLessonProgress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return view('analytics', [
                'totalAttempts'       => 0,
                'avgPerformance'      => 0,
                'practiceCompletion'  => 0,
                'activeStudents'      => 0,
                'totalStudents'       => 0,
                'topPerformer'        => null,
                'weeklyData'          => [],
                'topLessons'          => collect(),
                'quizBuckets'         => collect([
                    ['label' => '0-49', 'count' => 0, 'color' => '#ef4444'],
                    ['label' => '50-69', 'count' => 0, 'color' => '#f59e0b'],
                    ['label' => '70-84', 'count' => 0, 'color' => '#3b82f6'],
                    ['label' => '85-100', 'count' => 0, 'color' => '#10b981'],
                ]),
                'displayName'         => $user->name ?? 'Teacher',
                'teacher'             => null,
                'atRiskCount'         => 0,
                'avgLessonsPerStudent' => 0,
            ]);
        }

        $teacherId  = $teacher->id;
        $studentIds = Student::where('teacher_id', $teacherId)->pluck('student_id');
        $totalStudents = $studentIds->count();

        if ($totalStudents === 0) {
            return view('analytics', [
                'totalStudents'       => 0,
                'avgQuizScore'        => 0,
                'avgMastery'          => 0,
                'completionRate'      => 0,
                'avgStreakDays'       => 0,
                'activeLast7Pct'      => 0,
                'classSummary'        => collect(),
                'progressOverTime'    => collect(),
                'lessonDifficulty'    => collect(),
                'gestureHeatmap'      => collect(),
                'masteryDistribution' => collect(),
                'completionFunnel'    => collect(),
                'completionTotal'     => 0,
                'scoreBuckets'        => collect(),
                'maxScoreBucket'      => 1,
            ]);
        }

        $avgQuizScore = DB::table('quiz_attempts')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->avg('percentage') ?? 0;

        $totalGesturesAttempted = DB::table('gesture_performances')
            ->whereIn('student_id', $studentIds)
            ->where('attempts', '>', 0)
            ->count();

        $masteredGestures = DB::table('gesture_performances')
            ->whereIn('student_id', $studentIds)
            ->where('is_mastered', 1)
            ->count();

        $avgMastery = $totalGesturesAttempted > 0
            ? round(($masteredGestures / $totalGesturesAttempted) * 100, 1)
            : 0;

        $assignmentTotals = DB::table('lesson_assignments')
            ->whereIn('student_id', $studentIds)
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when status = "completed" then 1 else 0 end) as completed')
            ->first();

        $completionRate = $assignmentTotals->total > 0
            ? round(($assignmentTotals->completed / $assignmentTotals->total) * 100, 1)
            : 0;

        $avgStreakDays = Student::where('teacher_id', $teacherId)
            ->avg('streak_days') ?? 0;
        $avgStreakDays = round($avgStreakDays, 1);

        $activeLast7Days = Student::where('teacher_id', $teacherId)
            ->where('last_activity_date', '>=', Carbon::now()->subDays(7))
            ->count();

        $activeLast7Pct = $totalStudents > 0
            ? round(($activeLast7Days / $totalStudents) * 100, 1)
            : 0;

        $classSummary = collect([
            [
                'title' => 'Avg Quiz Score',
                'value' => number_format($avgQuizScore, 1) . '%',
                'detail' => 'From completed quizzes',
                'icon' => 'insights',
                'accent' => '#dbeafe',
                'iconColor' => '#1e3a8a',
            ],
            [
                'title' => 'Avg Mastery',
                'value' => number_format($avgMastery, 1) . '%',
                'detail' => 'Gestures marked mastered',
                'icon' => 'school',
                'accent' => '#ecfdf5',
                'iconColor' => '#15803d',
            ],
            [
                'title' => 'Completion Rate',
                'value' => number_format($completionRate, 1) . '%',
                'detail' => 'Lessons completed vs assigned',
                'icon' => 'menu_book',
                'accent' => '#eff6ff',
                'iconColor' => '#1e3a8a',
            ],
            [
                'title' => 'Avg Engagement',
                'value' => number_format($avgStreakDays, 1) . 'd',
                'detail' => $activeLast7Pct . '% active last 7 days',
                'icon' => 'bolt',
                'accent' => '#fef3c7',
                'iconColor' => '#92400e',
            ],
        ]);

        $progressOverTime = [];
        for ($weeksAgo = 7; $weeksAgo >= 0; $weeksAgo--) {
            $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks($weeksAgo);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
            $value = DB::table('quiz_attempts')
                ->whereIn('student_id', $studentIds)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                ->avg('percentage') ?: 0;
            $progressOverTime[] = [
                'label' => $weekStart->format('M d'),
                'value' => round($value, 1),
            ];
        }

        $lessonDifficulty = DB::table('lesson_assignments')
            ->whereIn('lesson_assignments.student_id', $studentIds)
            ->whereNotNull('lesson_assignments.score')
            ->join('lessons', 'lesson_assignments.lesson_id', '=', 'lessons.lesson_id')
            ->select('lesson_assignments.lesson_id', 'lessons.title', DB::raw('avg(lesson_assignments.score) as avg_score'), DB::raw('count(*) as attempts'))
            ->groupBy('lesson_assignments.lesson_id', 'lessons.title')
            ->orderBy('avg_score')
            ->limit(8)
            ->get()
            ->map(function ($row) {
                return [
                    'title' => $row->title,
                    'avg_score' => round($row->avg_score, 1),
                    'attempts' => $row->attempts,
                ];
            });

        $heatColor = function (float $rate) {
            $rate = max(0, min(100, $rate));
            if ($rate <= 50) {
                $r = 255;
                $g = (int) round(5.1 * $rate);
                $b = 0;
            } else {
                $r = (int) round(510 - 5.1 * $rate);
                $g = 255;
                $b = 0;
            }
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        };

        $gestureHeatmap = DB::table('gesture_performances')
            ->whereIn('student_id', $studentIds)
            ->where('attempts', '>', 0)
            ->join('gestures', 'gesture_performances.gesture_id', '=', 'gestures.gesture_id')
            ->select('gesture_performances.gesture_id', 'gestures.name', 'gestures.display_name', DB::raw('sum(gesture_performances.successful_attempts) as successes'), DB::raw('sum(gesture_performances.attempts) as attempts'))
            ->groupBy('gesture_performances.gesture_id', 'gestures.name', 'gestures.display_name')
            ->orderBy('gestures.name')
            ->get()
            ->map(function ($row) use ($heatColor) {
                $attempts = $row->attempts ?: 0;
                $rate = $attempts > 0 ? round(($row->successes / $attempts) * 100, 1) : 0;
                return [
                    'label' => $row->display_name ?: $row->name,
                    'rate' => $rate,
                    'color' => $heatColor($rate),
                ];
            });

        $masteryCountsRaw = DB::table('gesture_performances')
            ->whereIn('student_id', $studentIds)
            ->selectRaw('mastery_level, count(*) as count')
            ->groupBy('mastery_level')
            ->pluck('count', 'mastery_level')
            ->all();

        $masteryDistribution = collect([
            ['label' => 'Needs Practice', 'key' => 'needs_practice', 'color' => '#ef4444', 'count' => $masteryCountsRaw['needs_practice'] ?? 0],
            ['label' => 'Developing', 'key' => 'developing', 'color' => '#f59e0b', 'count' => $masteryCountsRaw['developing'] ?? 0],
            ['label' => 'Proficient', 'key' => 'proficient', 'color' => '#3b82f6', 'count' => $masteryCountsRaw['proficient'] ?? 0],
            ['label' => 'Mastered', 'key' => 'mastered', 'color' => '#10b981', 'count' => $masteryCountsRaw['mastered'] ?? 0],
        ]);

        $masteryTotal = $masteryDistribution->sum('count');
        $masteryDistribution = $masteryDistribution->map(function ($item) use ($masteryTotal) {
            $item['pct'] = $masteryTotal > 0 ? round(($item['count'] / $masteryTotal) * 100, 1) : 0;
            return $item;
        });

        $completionFunnelRaw = DB::table('lesson_assignments')
            ->whereIn('student_id', $studentIds)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $completionFunnel = collect([
            ['label' => 'Pending', 'status' => 'pending', 'color' => '#facc15', 'count' => $completionFunnelRaw['pending'] ?? 0],
            ['label' => 'In Progress', 'status' => 'in_progress', 'color' => '#3b82f6', 'count' => $completionFunnelRaw['in_progress'] ?? 0],
            ['label' => 'Completed', 'status' => 'completed', 'color' => '#10b981', 'count' => $completionFunnelRaw['completed'] ?? 0],
            ['label' => 'Failed', 'status' => 'failed', 'color' => '#ef4444', 'count' => $completionFunnelRaw['failed'] ?? 0],
        ]);

        $completionTotal = $completionFunnel->sum('count');

        $scoreBucketsRaw = DB::table('quiz_attempts')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->selectRaw("CASE
                WHEN percentage <= 20 THEN '0-20'
                WHEN percentage <= 40 THEN '21-40'
                WHEN percentage <= 60 THEN '41-60'
                WHEN percentage <= 80 THEN '61-80'
                ELSE '81-100'
            END AS bucket")
            ->selectRaw('count(*) as count')
            ->groupBy('bucket')
            ->pluck('count', 'bucket')
            ->all();

        $scoreBuckets = collect([
            ['label' => '0-20', 'count' => $scoreBucketsRaw['0-20'] ?? 0],
            ['label' => '21-40', 'count' => $scoreBucketsRaw['21-40'] ?? 0],
            ['label' => '41-60', 'count' => $scoreBucketsRaw['41-60'] ?? 0],
            ['label' => '61-80', 'count' => $scoreBucketsRaw['61-80'] ?? 0],
            ['label' => '81-100', 'count' => $scoreBucketsRaw['81-100'] ?? 0],
        ]);
 
        $maxScoreBucket = max(1, $scoreBuckets->max('count'));
 
        $studentRanking = Student::where('teacher_id', $teacherId)
            ->withCount(['quizAttempts as attempts' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->with(['quizAttempts' => function ($q) {
                $q->where('status', 'completed')->select('attempt_id', 'student_id', 'percentage');
            }])
            ->get()
            ->map(function ($student) {
                $avg = $student->quizAttempts->avg('percentage');
                return [
                    'name'      => trim($student->first_name . ' ' . $student->last_name),
                    'avg_score' => $avg ? round($avg) : 0,
                    'attempts'  => $student->attempts,
                ];
            })
            ->filter(fn ($s) => $s['attempts'] > 0)
            ->sortByDesc('avg_score')
            ->values();
 
        return view('analytics', compact(
            'totalStudents',
            'avgQuizScore',
            'avgMastery',
            'completionRate',
            'avgStreakDays',
            'activeLast7Pct',
            'classSummary',
            'progressOverTime',
            'lessonDifficulty',
            'gestureHeatmap',
            'masteryDistribution',
            'completionFunnel',
            'completionTotal',
            'scoreBuckets',
            'maxScoreBucket',
            'masteryTotal',
            'studentRanking'
        ));
    }
}
