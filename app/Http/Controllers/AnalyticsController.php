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
    public function index(\Illuminate\Http\Request $request)
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return view('analytics', $this->emptyTeacherData($user));
        }

        // Merge session filters into request so buildAnalyticsData() works unchanged
        $filters = session('analytics_filters', []);
        $request->merge($filters);

        $data = $this->buildAnalyticsData($teacher, $request);

        return view('analytics', $data);
    }

    /**
     * Accept filter POST → store in session → redirect to clean /analytics URL
     */
    public function applyFilter(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'string', 'in:weekly,monthly,quarterly,yearly'],
            'year'   => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month'  => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        if (empty(array_filter($validated))) {
            session()->forget('analytics_filters');
        } else {
            session(['analytics_filters' => $validated]);
        }

        return redirect()->route('analytics');
    }

    public function exportPdf(\Illuminate\Http\Request $request)
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            abort(404, 'No teacher profile found.');
        }

        $data = $this->buildAnalyticsData($teacher, $request);

        $data['teacher']     = $teacher;
        $data['teacherName'] = trim($teacher->first_name . ' ' . $teacher->last_name);
        $data['schoolName']  = $teacher->school->name ?? 'N/A';
        $data['generatedAt'] = now()->format('F j, Y \a\t g:i A');

        $pdf = \PDF::loadView('pdf.analytics', $data);

        return $pdf->stream('senas-analytics-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Builds every value the analytics view (web or PDF) needs, given a
     * teacher and the current request's filters (period/year/month).
     * Both index() and exportPdf() call this so the two outputs can
     * never show different numbers.
     */
    public function buildAnalyticsData($teacher, \Illuminate\Http\Request $request): array
    {
        $teacherId  = $teacher->id;
        $studentIds = Student::where('teacher_id', $teacherId)->where('status', 'active')->pluck('student_id');
        $lessonIds  = Lesson::where('teacher_id', $teacherId)->where('status', 'published')->whereNull('deleted_at')->pluck('lesson_id');
        $totalStudents = $studentIds->count();

        if ($totalStudents === 0) {
            return [
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
                'masteryTotal'        => 0,
                'studentRanking'      => collect(),
            ];
        }

        // Filter parameters
        $period = $request->get('period', 'weekly');
        $year   = (int) $request->get('year', date('Y'));
        $month  = (int) $request->get('month', date('n'));

        // Base quiz attempt query for top stats (only from published, active lessons)
        $quizQuery = DB::table('quiz_attempts')
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
            ->join('lessons', 'quizzes.lesson_id', '=', 'lessons.lesson_id')
            ->whereIn('quiz_attempts.student_id', $studentIds)
            ->where('quiz_attempts.status', 'completed')
            ->where('lessons.status', 'published')
            ->whereNull('lessons.deleted_at');

        if ($period === 'weekly') {
            $startDate = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(7)->startOfDay();
            $endDate   = Carbon::now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        } elseif ($period === 'monthly') {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth()->startOfDay();
            $endDate   = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
        } elseif ($period === 'quarterly') {
            $qStartMonth = (ceil($month / 3) - 1) * 3 + 1;
            $startDate   = Carbon::create($year, $qStartMonth, 1)->startOfMonth()->startOfDay();
            $endDate     = Carbon::create($year, $qStartMonth + 2, 1)->endOfMonth()->endOfDay();
        } elseif ($period === 'yearly') {
            $startDate = Carbon::create($year, 1, 1)->startOfYear()->startOfDay();
            $endDate   = Carbon::create($year, 12, 31)->endOfYear()->endOfDay();
        } else {
            $startDate = Carbon::now()->subMonths(6)->startOfDay();
            $endDate   = Carbon::now()->endOfDay();
        }

        $avgQuizScore = (clone $quizQuery)
            ->whereBetween('quiz_attempts.completed_at', [$startDate, $endDate])
            ->avg('quiz_attempts.percentage') ?? 0;

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
            ->whereIn('lesson_id', $lessonIds)
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when status = "completed" then 1 else 0 end) as completed')
            ->first();

        $completionRate = $assignmentTotals->total > 0
            ? round(($assignmentTotals->completed / $assignmentTotals->total) * 100, 1)
            : 0;

        $avgStreakDays = Student::where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->avg('streak_days') ?? 0;
        $avgStreakDays = round($avgStreakDays, 1);

        $activeLast7Days = Student::where('teacher_id', $teacherId)
            ->where('status', 'active')
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
        if ($period === 'weekly') {
            for ($weeksAgo = 7; $weeksAgo >= 0; $weeksAgo--) {
                $wStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks($weeksAgo);
                $wEnd   = $wStart->copy()->endOfWeek(Carbon::SUNDAY);
                $val    = DB::table('quiz_attempts')
                    ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
                    ->join('lessons', 'quizzes.lesson_id', '=', 'lessons.lesson_id')
                    ->whereIn('quiz_attempts.student_id', $studentIds)
                    ->where('quiz_attempts.status', 'completed')
                    ->where('lessons.status', 'published')
                    ->whereNull('lessons.deleted_at')
                    ->whereBetween('quiz_attempts.completed_at', [$wStart->copy()->startOfDay(), $wEnd->copy()->endOfDay()])
                    ->avg('quiz_attempts.percentage') ?: 0;
                $progressOverTime[] = [
                    'label' => $wStart->format('M d'),
                    'value' => round($val, 1),
                ];
            }
        } elseif ($period === 'monthly') {
            $mStart = Carbon::create($year, $month, 1)->startOfMonth();
            // 4 intervals (weeks of the month)
            for ($i = 0; $i < 4; $i++) {
                $dStart = $mStart->copy()->addDays($i * 7);
                $dEnd   = ($i === 3) ? $mStart->copy()->endOfMonth() : $mStart->copy()->addDays(($i + 1) * 7 - 1);
                $val    = DB::table('quiz_attempts')
                    ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
                    ->join('lessons', 'quizzes.lesson_id', '=', 'lessons.lesson_id')
                    ->whereIn('quiz_attempts.student_id', $studentIds)
                    ->where('quiz_attempts.status', 'completed')
                    ->where('lessons.status', 'published')
                    ->whereNull('lessons.deleted_at')
                    ->whereBetween('quiz_attempts.completed_at', [$dStart->copy()->startOfDay(), $dEnd->copy()->endOfDay()])
                    ->avg('quiz_attempts.percentage') ?: 0;
                $progressOverTime[] = [
                    'label' => $dStart->format('M d'),
                    'value' => round($val, 1),
                ];
            }
        } elseif ($period === 'quarterly') {
            $qStartMonth = (ceil($month / 3) - 1) * 3 + 1;
            for ($m = 0; $m < 3; $m++) {
                $curM = Carbon::create($year, $qStartMonth + $m, 1);
                $val  = DB::table('quiz_attempts')
                    ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
                    ->join('lessons', 'quizzes.lesson_id', '=', 'lessons.lesson_id')
                    ->whereIn('quiz_attempts.student_id', $studentIds)
                    ->where('quiz_attempts.status', 'completed')
                    ->where('lessons.status', 'published')
                    ->whereNull('lessons.deleted_at')
                    ->whereBetween('quiz_attempts.completed_at', [$curM->copy()->startOfMonth()->startOfDay(), $curM->copy()->endOfMonth()->endOfDay()])
                    ->avg('quiz_attempts.percentage') ?: 0;
                $progressOverTime[] = [
                    'label' => $curM->format('M Y'),
                    'value' => round($val, 1),
                ];
            }
        } elseif ($period === 'yearly') {
            for ($m = 1; $m <= 12; $m++) {
                $curM = Carbon::create($year, $m, 1);
                $val  = DB::table('quiz_attempts')
                    ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
                    ->join('lessons', 'quizzes.lesson_id', '=', 'lessons.lesson_id')
                    ->whereIn('quiz_attempts.student_id', $studentIds)
                    ->where('quiz_attempts.status', 'completed')
                    ->where('lessons.status', 'published')
                    ->whereNull('lessons.deleted_at')
                    ->whereBetween('quiz_attempts.completed_at', [$curM->copy()->startOfMonth()->startOfDay(), $curM->copy()->endOfMonth()->endOfDay()])
                    ->avg('quiz_attempts.percentage') ?: 0;
                $progressOverTime[] = [
                    'label' => $curM->format('M'),
                    'value' => round($val, 1),
                ];
            }
        }

        $lessonDifficulty = DB::table('lesson_assignments')
            ->whereIn('lesson_assignments.student_id', $studentIds)
            ->whereIn('lesson_assignments.lesson_id', $lessonIds)
            ->whereNotNull('lesson_assignments.score')
            ->join('lessons', 'lesson_assignments.lesson_id', '=', 'lessons.lesson_id')
            ->where('lessons.status', 'published')
            ->whereNull('lessons.deleted_at')
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
            ->whereIn('lesson_id', $lessonIds)
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
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
            ->join('lessons', 'quizzes.lesson_id', '=', 'lessons.lesson_id')
            ->whereIn('quiz_attempts.student_id', $studentIds)
            ->where('quiz_attempts.status', 'completed')
            ->where('lessons.status', 'published')
            ->whereNull('lessons.deleted_at')
            ->selectRaw("CASE
                WHEN quiz_attempts.percentage <= 20 THEN '0-20'
                WHEN quiz_attempts.percentage <= 40 THEN '21-40'
                WHEN quiz_attempts.percentage <= 60 THEN '41-60'
                WHEN quiz_attempts.percentage <= 80 THEN '61-80'
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
            ->where('status', 'active')
            ->withCount(['quizAttempts as attempts' => function ($q) {
                $q->where('quiz_attempts.status', 'completed')
                  ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
                  ->join('lessons', 'quizzes.lesson_id', '=', 'lessons.lesson_id')
                  ->where('lessons.status', 'published')
                  ->whereNull('lessons.deleted_at');
            }])
            ->with(['quizAttempts' => function ($q) {
                $q->where('quiz_attempts.status', 'completed')
                  ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
                  ->join('lessons', 'quizzes.lesson_id', '=', 'lessons.lesson_id')
                  ->where('lessons.status', 'published')
                  ->whereNull('lessons.deleted_at')
                  ->select('quiz_attempts.attempt_id', 'quiz_attempts.student_id', 'quiz_attempts.percentage');
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
            ->sortByDesc('avg_score')
            ->values();

        // ── DEDICATED GESTURE PERFORMANCE ANALYTICS (STRICTLY FROM gesture_performances) ──
        $gestureStatsRaw = DB::table('gesture_performances')
            ->whereIn('student_id', $studentIds)
            ->selectRaw('
                COUNT(DISTINCT gesture_id) as total_gestures,
                SUM(attempts) as total_attempts,
                SUM(successful_attempts) as total_successful,
                SUM(wrong_attempts) as total_wrong,
                SUM(CASE WHEN is_mastered = 1 THEN 1 ELSE 0 END) as total_mastered
            ')
            ->first();

        $gTotalAttempts   = (int) ($gestureStatsRaw->total_attempts ?? 0);
        $gTotalSuccessful = (int) ($gestureStatsRaw->total_successful ?? 0);
        $gTotalWrong      = (int) ($gestureStatsRaw->total_wrong ?? 0);

        $gesturePerformanceOverview = [
            'total_gestures'   => (int) ($gestureStatsRaw->total_gestures ?? 0),
            'total_attempts'   => $gTotalAttempts,
            'total_successful' => $gTotalSuccessful,
            'total_wrong'      => $gTotalWrong,
            'overall_accuracy' => $gTotalAttempts > 0 ? round(($gTotalSuccessful / $gTotalAttempts) * 100, 1) : 0,
            'total_mastered'   => (int) ($gestureStatsRaw->total_mastered ?? 0),
        ];

        // Best-performing gestures (highest accuracy) from gesture_performances
        $topPerformingGestures = DB::table('gesture_performances as gp')
            ->join('gestures as g', 'gp.gesture_id', '=', 'g.gesture_id')
            ->whereIn('gp.student_id', $studentIds)
            ->where('gp.attempts', '>', 0)
            ->select(
                'g.gesture_id',
                'g.name',
                'g.display_name',
                DB::raw('SUM(gp.attempts) as total_attempts'),
                DB::raw('SUM(gp.successful_attempts) as total_successful'),
                DB::raw('SUM(gp.wrong_attempts) as total_wrong'),
                DB::raw('ROUND((SUM(gp.successful_attempts) / SUM(gp.attempts)) * 100, 1) as accuracy')
            )
            ->groupBy('g.gesture_id', 'g.name', 'g.display_name')
            ->orderByDesc('accuracy')
            ->orderByDesc('total_attempts')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'gesture_name'        => $row->display_name ?: $row->name,
                    'attempts'            => (int) $row->total_attempts,
                    'successful_attempts' => (int) $row->total_successful,
                    'wrong_attempts'      => (int) $row->total_wrong,
                    'accuracy'            => (float) $row->accuracy,
                ];
            });

        // Lowest-performing gestures (lowest accuracy / struggling) from gesture_performances
        $lowestPerformingGestures = DB::table('gesture_performances as gp')
            ->join('gestures as g', 'gp.gesture_id', '=', 'g.gesture_id')
            ->whereIn('gp.student_id', $studentIds)
            ->where('gp.attempts', '>', 0)
            ->select(
                'g.gesture_id',
                'g.name',
                'g.display_name',
                DB::raw('SUM(gp.attempts) as total_attempts'),
                DB::raw('SUM(gp.successful_attempts) as total_successful'),
                DB::raw('SUM(gp.wrong_attempts) as total_wrong'),
                DB::raw('ROUND((SUM(gp.successful_attempts) / SUM(gp.attempts)) * 100, 1) as accuracy')
            )
            ->groupBy('g.gesture_id', 'g.name', 'g.display_name')
            ->orderBy('accuracy', 'asc')
            ->orderByDesc('total_attempts')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'gesture_name'        => $row->display_name ?: $row->name,
                    'attempts'            => (int) $row->total_attempts,
                    'successful_attempts' => (int) $row->total_successful,
                    'wrong_attempts'      => (int) $row->total_wrong,
                    'accuracy'            => (float) $row->accuracy,
                ];
            });

        // Student performance per gesture from gesture_performances
        $studentGesturePerformance = DB::table('gesture_performances as gp')
            ->join('students as s', 'gp.student_id', '=', 's.student_id')
            ->join('gestures as g', 'gp.gesture_id', '=', 'g.gesture_id')
            ->whereIn('gp.student_id', $studentIds)
            ->where('gp.attempts', '>', 0)
            ->select(
                's.student_id',
                's.first_name',
                's.last_name',
                'g.gesture_id',
                'g.name as g_name',
                'g.display_name as g_display_name',
                'gp.attempts',
                'gp.successful_attempts',
                'gp.wrong_attempts',
                'gp.mastery_level',
                'gp.is_mastered',
                'gp.last_attempt_at'
            )
            ->orderBy('s.first_name')
            ->orderBy('s.last_name')
            ->get()
            ->map(function ($row) {
                $attempts = (int) $row->attempts;
                $success  = (int) $row->successful_attempts;
                $accuracy = $attempts > 0 ? round(($success / $attempts) * 100, 1) : 0;
                return [
                    'student_name'        => trim($row->first_name . ' ' . $row->last_name),
                    'gesture_name'        => $row->g_display_name ?: $row->g_name,
                    'attempts'            => $attempts,
                    'successful_attempts' => $success,
                    'wrong_attempts'      => (int) $row->wrong_attempts,
                    'accuracy'            => $accuracy,
                    'mastery_level'       => $row->mastery_level ?? 'needs_practice',
                    'is_mastered'         => (bool) $row->is_mastered,
                    'last_attempt_at'     => $row->last_attempt_at ? Carbon::parse($row->last_attempt_at)->format('M d, Y') : '—',
                ];
            });

        return [
            'totalStudents'              => $totalStudents,
            'avgQuizScore'               => $avgQuizScore,
            'avgMastery'                 => $avgMastery,
            'completionRate'             => $completionRate,
            'avgStreakDays'              => $avgStreakDays,
            'activeLast7Pct'             => $activeLast7Pct,
            'classSummary'               => $classSummary,
            'progressOverTime'           => $progressOverTime,
            'lessonDifficulty'           => $lessonDifficulty,
            'gestureHeatmap'             => $gestureHeatmap,
            'masteryDistribution'        => $masteryDistribution,
            'completionFunnel'           => $completionFunnel,
            'completionTotal'            => $completionTotal,
            'scoreBuckets'               => $scoreBuckets,
            'maxScoreBucket'             => $maxScoreBucket,
            'masteryTotal'               => $masteryTotal,
            'studentRanking'             => $studentRanking,
            'gesturePerformanceOverview' => $gesturePerformanceOverview,
            'topPerformingGestures'      => $topPerformingGestures,
            'lowestPerformingGestures'   => $lowestPerformingGestures,
            'studentGesturePerformance'  => $studentGesturePerformance,
        ];
    }

    private function emptyTeacherData($user): array
    {
        return [
            'totalAttempts'              => 0,
            'avgPerformance'             => 0,
            'practiceCompletion'         => 0,
            'activeStudents'             => 0,
            'totalStudents'              => 0,
            'topPerformer'               => null,
            'weeklyData'                 => [],
            'topLessons'                 => collect(),
            'quizBuckets'                => collect([
                ['label' => '0-49', 'count' => 0, 'color' => '#ef4444'],
                ['label' => '50-69', 'count' => 0, 'color' => '#f59e0b'],
                ['label' => '70-84', 'count' => 0, 'color' => '#3b82f6'],
                ['label' => '85-100', 'count' => 0, 'color' => '#10b981'],
            ]),
            'displayName'                => $user->name ?? 'Teacher',
            'teacher'                    => null,
            'atRiskCount'                => 0,
            'avgLessonsPerStudent'        => 0,
            'gesturePerformanceOverview' => [
                'total_gestures'   => 0,
                'total_attempts'   => 0,
                'total_successful' => 0,
                'total_wrong'      => 0,
                'overall_accuracy' => 0,
                'total_mastered'   => 0,
            ],
            'topPerformingGestures'      => collect(),
            'lowestPerformingGestures'   => collect(),
            'studentGesturePerformance'  => collect(),
        ];
    }
}