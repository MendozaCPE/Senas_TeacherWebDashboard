<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentLessonProgress;
use App\Models\CheckpointExam;
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

        $rawAvgQuizScore = (clone $quizQuery)
            ->whereBetween('quiz_attempts.completed_at', [$startDate, $endDate])
            ->avg('quiz_attempts.percentage') ?? 0;
        $avgQuizScore = round((float) $rawAvgQuizScore, 2);

        $gestureBaseQuery = DB::table('gesture_performances')
            ->whereIn('student_id', $studentIds)
            ->where('attempts', '>', 0)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('last_attempt_at', [$startDate, $endDate])
                  ->orWhereBetween('updated_at', [$startDate, $endDate])
                  ->orWhereBetween('created_at', [$startDate, $endDate]);
            });

        $totalGesturesAttempted = (clone $gestureBaseQuery)->count();

        $masteredGestures = (clone $gestureBaseQuery)
            ->where('is_mastered', 1)
            ->count();

        $avgMastery = $totalGesturesAttempted > 0
            ? round(($masteredGestures / $totalGesturesAttempted) * 100, 1)
            : 0;

        $assignmentTotals = DB::table('lesson_assignments')
            ->whereIn('student_id', $studentIds)
            ->whereIn('lesson_id', $lessonIds)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('assigned_at', [$startDate, $endDate])
                  ->orWhereBetween('completed_at', [$startDate, $endDate])
                  ->orWhereBetween('updated_at', [$startDate, $endDate]);
            })
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when status = "completed" then 1 else 0 end) as completed')
            ->first();

        $completionRate = ($assignmentTotals && $assignmentTotals->total > 0)
            ? round(($assignmentTotals->completed / $assignmentTotals->total) * 100, 1)
            : 0;

        $avgStreakDays = Student::where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->avg('streak_days') ?? 0;
        $avgStreakDays = round($avgStreakDays);

        $activeInPeriod = Student::where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('last_activity_date', [$startDate, $endDate])
                  ->orWhereExists(function ($sub) use ($startDate, $endDate) {
                      $sub->select(DB::raw(1))
                          ->from('quiz_attempts')
                          ->whereColumn('quiz_attempts.student_id', 'students.student_id')
                          ->whereBetween('quiz_attempts.completed_at', [$startDate, $endDate]);
                  })
                  ->orWhereExists(function ($sub) use ($startDate, $endDate) {
                      $sub->select(DB::raw(1))
                          ->from('gesture_performances')
                          ->whereColumn('gesture_performances.student_id', 'students.student_id')
                          ->where(function ($gq) use ($startDate, $endDate) {
                              $gq->whereBetween('last_attempt_at', [$startDate, $endDate])
                                 ->orWhereBetween('updated_at', [$startDate, $endDate])
                                 ->orWhereBetween('created_at', [$startDate, $endDate]);
                          });
                  });
            })
            ->count();

        $activeLast7Pct = $totalStudents > 0
            ? round(($activeInPeriod / $totalStudents) * 100, 1)
            : 0;

        $streakText = $avgStreakDays . ' ' . ($avgStreakDays === 1 ? 'day' : 'days');

        $classSummary = collect([
            [
                'title'     => 'Avg Quiz Score',
                'value'     => number_format($avgQuizScore, 1) . '%',
                'detail'    => 'Filtered period average',
                'icon'      => 'insights',
                'accent'    => '#dbeafe',
                'iconColor' => '#1e3a8a',
            ],
            [
                'title'     => 'Gesture Mastery',
                'value'     => number_format($avgMastery, 1) . '%',
                'detail'    => 'Practiced signs in period',
                'icon'      => 'school',
                'accent'    => '#ecfdf5',
                'iconColor' => '#15803d',
            ],
            [
                'title'     => 'Lesson Completion',
                'value'     => number_format($completionRate, 1) . '%',
                'detail'    => 'Assigned lessons completed',
                'icon'      => 'menu_book',
                'accent'    => '#eff6ff',
                'iconColor' => '#1e3a8a',
            ],
            [
                'title'     => 'Active Engagement',
                'value'     => $streakText,
                'detail'    => $activeLast7Pct . '% active in period',
                'icon'      => 'bolt',
                'accent'    => '#fef3c7',
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
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('lesson_assignments.completed_at', [$startDate, $endDate])
                  ->orWhereBetween('lesson_assignments.updated_at', [$startDate, $endDate]);
            })
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
            ->whereIn('gesture_performances.student_id', $studentIds)
            ->where('gesture_performances.attempts', '>', 0)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('gesture_performances.last_attempt_at', [$startDate, $endDate])
                  ->orWhereBetween('gesture_performances.updated_at', [$startDate, $endDate])
                  ->orWhereBetween('gesture_performances.created_at', [$startDate, $endDate]);
            })
            ->join('gestures', 'gesture_performances.gesture_id', '=', 'gestures.gesture_id')
            ->select('gesture_performances.gesture_id', 'gestures.name', 'gestures.display_name', 'gestures.sign_type', DB::raw('sum(gesture_performances.successful_attempts) as successes'), DB::raw('sum(gesture_performances.attempts) as attempts'))
            ->groupBy('gesture_performances.gesture_id', 'gestures.name', 'gestures.display_name', 'gestures.sign_type')
            ->orderBy('gestures.name')
            ->get()
            ->map(function ($row) use ($heatColor) {
                $attempts = $row->attempts ?: 0;
                $rate = $attempts > 0 ? round(($row->successes / $attempts) * 100, 1) : 0;
                return [
                    'label'     => $row->display_name ?: $row->name,
                    'sign_type' => $row->sign_type ?? 'static',
                    'rate'      => $rate,
                    'color'     => $heatColor($rate),
                ];
            });

        $masteryCountsRaw = DB::table('gesture_performances')
            ->whereIn('student_id', $studentIds)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('last_attempt_at', [$startDate, $endDate])
                  ->orWhereBetween('updated_at', [$startDate, $endDate])
                  ->orWhereBetween('created_at', [$startDate, $endDate]);
            })
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
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('assigned_at', [$startDate, $endDate])
                  ->orWhereBetween('completed_at', [$startDate, $endDate])
                  ->orWhereBetween('updated_at', [$startDate, $endDate]);
            })
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
            ->whereBetween('quiz_attempts.completed_at', [$startDate, $endDate])
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
            ['label' => '0-20%', 'count' => $scoreBucketsRaw['0-20'] ?? 0],
            ['label' => '21-40%', 'count' => $scoreBucketsRaw['21-40'] ?? 0],
            ['label' => '41-60%', 'count' => $scoreBucketsRaw['41-60'] ?? 0],
            ['label' => '61-80%', 'count' => $scoreBucketsRaw['61-80'] ?? 0],
            ['label' => '81-100%', 'count' => $scoreBucketsRaw['81-100'] ?? 0],
        ]);

        $maxScoreBucket = max(1, $scoreBuckets->max('count'));

        // ── 1. OVERALL CLASS LEADERBOARD (Across Quizzes within the period) ──
        $allQuizAttempts = DB::table('quiz_attempts as qa')
            ->join('quizzes as q', 'qa.quiz_id', '=', 'q.quiz_id')
            ->join('lessons as l', 'q.lesson_id', '=', 'l.lesson_id')
            ->join('students as s', 'qa.student_id', '=', 's.student_id')
            ->whereIn('qa.student_id', $studentIds)
            ->where('qa.status', 'completed')
            ->where('l.status', 'published')
            ->whereNull('l.deleted_at')
            ->whereBetween('qa.completed_at', [$startDate, $endDate])
            ->select('qa.student_id', 's.first_name', 's.last_name', 'qa.percentage', 'qa.quiz_id', 'qa.created_at', 'qa.attempt_id')
            ->orderBy('qa.created_at', 'asc')
            ->get();

        $studentsModelMap = Student::whereIn('student_id', $studentIds)->get()->keyBy('student_id');

        $overallStudents = [];
        foreach ($allQuizAttempts->groupBy('student_id') as $sId => $attempts) {
            $studentInfo = $attempts->first();
            $stModel = $studentsModelMap[$sId] ?? null;
            $quizGroups = $attempts->groupBy('quiz_id');
            
            // Overall class ranking uses overall score average across completed attempts in period
            $overallScore = round($attempts->avg('percentage'), 1);

            $overallStudents[] = [
                'student_id'          => $sId,
                'name'                => trim($studentInfo->first_name . ' ' . $studentInfo->last_name),
                'overall_score'       => $overallScore,
                'best_score'          => $overallScore,
                'total_attempts'      => $attempts->count(),
                'quizzes_count'       => $quizGroups->count(),
                'initials'            => $stModel ? $stModel->initials : strtoupper(substr($studentInfo->first_name, 0, 1) . substr($studentInfo->last_name, 0, 1)),
                'avatar_url'          => $stModel ? $stModel->avatarUrl() : null,
            ];
        }

        // Overall class ranking sort rule: Highest Overall Score DESC, Quizzes Count DESC
        usort($overallStudents, function ($a, $b) {
            if ($a['overall_score'] != $b['overall_score']) {
                return $b['overall_score'] <=> $a['overall_score'];
            }
            return $b['total_attempts'] <=> $a['total_attempts'];
        });

        foreach ($overallStudents as $idx => &$st) {
            $st['rank'] = $idx + 1;
        }
        unset($st);

        // ── 2. PER-LESSON & CHECKPOINT EXAM LEADERBOARDS (Filtered by period) ──
        $publishedLessons = Lesson::where('teacher_id', $teacherId)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->with(['quiz'])
            ->orderBy('module_order')
            ->orderBy('title')
            ->get();

        $lessonLeaderboards = [
            'all' => [
                'lesson_id'    => 'all',
                'title'        => 'All Lessons (Overall Class)',
                'is_overall'   => true,
                'total_ranked' => count($overallStudents),
                'rankings'     => $overallStudents,
            ],
        ];

        $availableLessonsList = [
            ['id' => 'all', 'title' => 'All Lessons (Overall Class)', 'group' => 'Overall', 'type' => 'overall'],
        ];

        foreach ($publishedLessons as $lesson) {
            if (!$lesson->quiz) {
                continue;
            }

            $availableLessonsList[] = [
                'id'    => (string) $lesson->lesson_id,
                'title' => $lesson->title,
                'group' => 'Lessons',
                'type'  => 'lesson',
            ];

            $lessonAttempts = DB::table('quiz_attempts as qa')
                ->join('students as s', 'qa.student_id', '=', 's.student_id')
                ->where('qa.quiz_id', $lesson->quiz->quiz_id)
                ->whereIn('qa.student_id', $studentIds)
                ->where('qa.status', 'completed')
                ->whereBetween('qa.completed_at', [$startDate, $endDate])
                ->select(
                    's.student_id',
                    's.first_name',
                    's.last_name',
                    DB::raw('MAX(qa.percentage) as best_score'),
                    DB::raw('COUNT(qa.attempt_id) as total_attempts')
                )
                ->groupBy('s.student_id', 's.first_name', 's.last_name')
                ->get();

            $lessonRankings = $lessonAttempts->map(function ($item) use ($lesson, $startDate, $endDate, $studentsModelMap) {
                // Find attempt number where they achieved their best score
                $allStudentAttempts = DB::table('quiz_attempts')
                    ->where('student_id', $item->student_id)
                    ->where('quiz_id', $lesson->quiz->quiz_id)
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'asc')
                    ->get();

                $attemptsToAchieve = 1;
                foreach ($allStudentAttempts as $idx => $att) {
                    if ($att->percentage == $item->best_score) {
                        $attemptsToAchieve = $idx + 1;
                        break;
                    }
                }

                $stModel = $studentsModelMap[$item->student_id] ?? null;
                return [
                    'student_id'          => $item->student_id,
                    'name'                => trim($item->first_name . ' ' . $item->last_name),
                    'best_score'          => (float) $item->best_score,
                    'attempts_to_achieve' => $attemptsToAchieve,
                    'total_attempts'      => (int) $item->total_attempts,
                    'initials'            => $stModel ? $stModel->initials : strtoupper(substr($item->first_name, 0, 1) . substr($item->last_name, 0, 1)),
                    'avatar_url'          => $stModel ? $stModel->avatarUrl() : null,
                ];
            })->all();

            // Ranking order: best_score DESC, attempts_to_achieve ASC, total_attempts ASC
            usort($lessonRankings, function ($a, $b) {
                if ($a['best_score'] != $b['best_score']) {
                    return $b['best_score'] <=> $a['best_score'];
                }
                if ($a['attempts_to_achieve'] != $b['attempts_to_achieve']) {
                    return $a['attempts_to_achieve'] <=> $b['attempts_to_achieve'];
                }
                return $a['total_attempts'] <=> $b['total_attempts'];
            });

            foreach ($lessonRankings as $idx => &$lr) {
                $lr['rank'] = $idx + 1;
            }
            unset($lr);

            $lessonLeaderboards[(string) $lesson->lesson_id] = [
                'lesson_id'    => $lesson->lesson_id,
                'title'        => $lesson->title,
                'is_overall'   => false,
                'is_exam'      => false,
                'total_ranked' => count($lessonRankings),
                'rankings'     => $lessonRankings,
            ];
        }

        // Checkpoint Exams Leaderboards
        $publishedCheckpointExams = CheckpointExam::where('teacher_id', $teacherId)
            ->where('status', 'published')
            ->with(['module'])
            ->orderBy('module_id')
            ->orderBy('title')
            ->get();

        foreach ($publishedCheckpointExams as $exam) {
            $examKey = 'exam_' . $exam->exam_id;
            $displayTitle = $exam->title;

            $availableLessonsList[] = [
                'id'    => $examKey,
                'title' => $displayTitle,
                'group' => 'Checkpoint Exams',
                'type'  => 'checkpoint_exam',
            ];

            $examAttempts = DB::table('checkpoint_exam_attempts as cea')
                ->join('students as s', 'cea.student_id', '=', 's.student_id')
                ->where('cea.exam_id', $exam->exam_id)
                ->whereIn('cea.student_id', $studentIds)
                ->whereIn('cea.status', ['completed', 'failed'])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('cea.completed_at', [$startDate, $endDate])
                      ->orWhere(function ($q2) use ($startDate, $endDate) {
                          $q2->whereNull('cea.completed_at')
                             ->whereBetween('cea.created_at', [$startDate, $endDate]);
                      });
                })
                ->select(
                    's.student_id',
                    's.first_name',
                    's.last_name',
                    DB::raw('MAX(cea.percentage) as best_score'),
                    DB::raw('COUNT(cea.attempt_id) as total_attempts')
                )
                ->groupBy('s.student_id', 's.first_name', 's.last_name')
                ->get();

            $examRankings = $examAttempts->map(function ($item) use ($exam, $startDate, $endDate, $studentsModelMap) {
                // Find attempt number where they achieved their best score
                $allStudentAttempts = DB::table('checkpoint_exam_attempts')
                    ->where('student_id', $item->student_id)
                    ->where('exam_id', $exam->exam_id)
                    ->whereIn('status', ['completed', 'failed'])
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('completed_at', [$startDate, $endDate])
                          ->orWhere(function ($q2) use ($startDate, $endDate) {
                              $q2->whereNull('completed_at')
                                 ->whereBetween('created_at', [$startDate, $endDate]);
                          });
                    })
                    ->orderBy('created_at', 'asc')
                    ->get();

                $attemptsToAchieve = 1;
                foreach ($allStudentAttempts as $idx => $att) {
                    if ($att->percentage == $item->best_score) {
                        $attemptsToAchieve = $idx + 1;
                        break;
                    }
                }

                $stModel = $studentsModelMap[$item->student_id] ?? null;
                return [
                    'student_id'          => $item->student_id,
                    'name'                => trim($item->first_name . ' ' . $item->last_name),
                    'best_score'          => (float) $item->best_score,
                    'attempts_to_achieve' => $attemptsToAchieve,
                    'total_attempts'      => (int) $item->total_attempts,
                    'initials'            => $stModel ? $stModel->initials : strtoupper(substr($item->first_name, 0, 1) . substr($item->last_name, 0, 1)),
                    'avatar_url'          => $stModel ? $stModel->avatarUrl() : null,
                ];
            })->all();

            // Ranking order: best_score DESC, attempts_to_achieve ASC, total_attempts ASC
            usort($examRankings, function ($a, $b) {
                if ($a['best_score'] != $b['best_score']) {
                    return $b['best_score'] <=> $a['best_score'];
                }
                if ($a['attempts_to_achieve'] != $b['attempts_to_achieve']) {
                    return $a['attempts_to_achieve'] <=> $b['attempts_to_achieve'];
                }
                return $a['total_attempts'] <=> $b['total_attempts'];
            });

            foreach ($examRankings as $idx => &$er) {
                $er['rank'] = $idx + 1;
            }
            unset($er);

            $lessonLeaderboards[$examKey] = [
                'lesson_id'    => $examKey,
                'title'        => $displayTitle,
                'is_overall'   => false,
                'is_exam'      => true,
                'total_ranked' => count($examRankings),
                'rankings'     => $examRankings,
            ];
        }

        $studentRanking = collect($overallStudents);

        // ── 3. DEDICATED GESTURE PERFORMANCE ANALYTICS (Filtered by period) ──
        $gestureStatsRaw = DB::table('gesture_performances')
            ->whereIn('student_id', $studentIds)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('last_attempt_at', [$startDate, $endDate])
                  ->orWhereBetween('updated_at', [$startDate, $endDate])
                  ->orWhereBetween('created_at', [$startDate, $endDate]);
            })
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

        // Group performances by gesture for per-sign teacher insights
        $gesturePerSignRows = DB::table('gesture_performances as gp')
            ->join('gestures as g', 'gp.gesture_id', '=', 'g.gesture_id')
            ->leftJoin('gesture_modules as gm', 'g.module_id', '=', 'gm.module_id')
            ->join('students as s', 'gp.student_id', '=', 's.student_id')
            ->whereIn('gp.student_id', $studentIds)
            ->where('gp.attempts', '>', 0)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('gp.last_attempt_at', [$startDate, $endDate])
                  ->orWhereBetween('gp.updated_at', [$startDate, $endDate])
                  ->orWhereBetween('gp.created_at', [$startDate, $endDate]);
            })
            ->select(
                'g.gesture_id',
                'g.name',
                'g.display_name',
                'g.sign_type',
                'gm.display_name as module_display_name',
                's.student_id',
                's.first_name',
                's.last_name',
                'gp.attempts',
                'gp.successful_attempts',
                'gp.wrong_attempts',
                'gp.is_mastered',
                'gp.mastery_level'
            )
            ->get()
            ->groupBy('gesture_id');

        $signsBreakdown = [];
        $lowMasterySignsCount = 0;
        $masteredSignsCount = 0;

        foreach ($gesturePerSignRows as $gId => $records) {
            $first = $records->first();
            $gName = $first->display_name ?: $first->name;
            $totAtt = $records->sum('attempts');
            $totSuc = $records->sum('successful_attempts');
            $totWrn = $records->sum('wrong_attempts');
            $acc = $totAtt > 0 ? round(($totSuc / $totAtt) * 100, 1) : 0;
            $masteredStudents = $records->where('is_mastered', 1)->count();

            // Full list of students who attempted this sign, ranked by accuracy DESC, then success DESC
            $rankedStudentsForSign = $records->map(function ($r) use ($studentsModelMap) {
                $studentAcc = $r->attempts > 0 ? round(($r->successful_attempts / $r->attempts) * 100, 1) : 0;
                $stModel = $studentsModelMap[$r->student_id] ?? null;
                return [
                    'student_id'          => $r->student_id,
                    'name'                => trim($r->first_name . ' ' . $r->last_name),
                    'initials'            => $stModel ? $stModel->initials : strtoupper(substr($r->first_name, 0, 1) . substr($r->last_name, 0, 1)),
                    'avatar_url'          => $stModel ? $stModel->avatarUrl() : null,
                    'attempts'            => (int) $r->attempts,
                    'successful_attempts' => (int) $r->successful_attempts,
                    'wrong_attempts'      => (int) $r->wrong_attempts,
                    'accuracy'            => $studentAcc,
                    'is_mastered'         => (bool) $r->is_mastered,
                    'mastery_level'       => $r->mastery_level ?: 'needs_practice',
                ];
            })->sortBy([
                ['accuracy', 'desc'],
                ['successful_attempts', 'desc'],
                ['attempts', 'asc'],
            ])->values()->all();

            foreach ($rankedStudentsForSign as $sIdx => &$sRankItem) {
                $sRankItem['rank'] = $sIdx + 1;
            }
            unset($sRankItem);

            // Find best performing student from the ranked list (Rank 1)
            $bestStudentItem = !empty($rankedStudentsForSign) ? $rankedStudentsForSign[0] : null;

            // Find struggling student from the bottom of the ranked list
            $strugglingStudentItem = null;
            for ($k = count($rankedStudentsForSign) - 1; $k >= 0; $k--) {
                if ($rankedStudentsForSign[$k]['wrong_attempts'] > 0) {
                    $strugglingStudentItem = $rankedStudentsForSign[$k];
                    break;
                }
            }

            $isMasteredMajority = ($acc >= 75 || ($totalStudents > 0 && $masteredStudents >= ceil($totalStudents / 2)));
            if ($isMasteredMajority) {
                $masteredSignsCount++;
            } else {
                $lowMasterySignsCount++;
            }

            $signsBreakdown[] = [
                'gesture_id'          => (string) $gId,
                'gesture_name'        => $gName,
                'sign_type'           => $first->sign_type ?? 'static',
                'module_name'         => $first->module_display_name ?? '',
                'total_attempts'      => $totAtt,
                'successful_attempts' => $totSuc,
                'wrong_attempts'      => $totWrn,
                'accuracy'            => $acc,
                'mastered_students'   => $masteredStudents,
                'status'              => $isMasteredMajority ? 'mastered' : 'needs_practice',
                'status_label'        => $isMasteredMajority ? 'Mastered by Majority' : 'Needs Class Practice',
                'best_student'        => $bestStudentItem ? [
                    'name'     => $bestStudentItem['name'],
                    'accuracy' => (int) round($bestStudentItem['accuracy']),
                    'attempts' => $bestStudentItem['attempts'],
                ] : null,
                'struggling_student'  => $strugglingStudentItem ? [
                    'name'     => $strugglingStudentItem['name'],
                    'accuracy' => (int) round($strugglingStudentItem['accuracy']),
                    'wrong'    => $strugglingStudentItem['wrong_attempts'],
                ] : null,
                'students_ranking'    => $rankedStudentsForSign,
            ];
        }

        // Sort signs by needs practice first, then accuracy ASC
        usort($signsBreakdown, function ($a, $b) {
            if ($a['status'] !== $b['status']) {
                return $a['status'] === 'needs_practice' ? -1 : 1;
            }
            return $a['accuracy'] <=> $b['accuracy'];
        });

        // ── 4. SENYA INSIGHTS GENERATION FOR EACH SECTION (EMOJI-FREE) ──
        $topScorerName = $overallStudents[0]['name'] ?? 'your students';
        $topScorerScore = number_format($overallStudents[0]['best_score'] ?? 0, 2);
        $hardestLessonTitle = $lessonDifficulty->first()['title'] ?? 'recent lessons';
        $hardestLessonAvg = number_format($lessonDifficulty->first()['avg_score'] ?? 0, 2);
        $formattedAvgScore = number_format($avgQuizScore, 2);

        $mostStrugglingSign = collect($signsBreakdown)->where('status', 'needs_practice')->first();
        $topMasteredSign = collect($signsBreakdown)->where('status', 'mastered')->sortByDesc('accuracy')->first();

        $senyaInsights = [
            'kpi' => $avgQuizScore >= 75
                ? "<strong>Great class momentum:</strong> Your students are maintaining a strong <strong>{$formattedAvgScore}% average quiz score</strong> with {$activeLast7Pct}% active in the past 7 days."
                : "<strong>Opportunity for reinforcement:</strong> The class average is currently <strong>{$formattedAvgScore}%</strong>. A quick 10-minute group review could help boost scores.",

            'progress' => count($progressOverTime) > 1
                ? "<strong>Performance Trend:</strong> Quiz scores have tracked from <strong>" . number_format($progressOverTime[0]['value'] ?? 0, 2) . "%</strong> to <strong>" . number_format(end($progressOverTime)['value'] ?? 0, 2) . "%</strong> over this {$period} period."
                : "<strong>Trend:</strong> Continue assigning regular quizzes to build a rich progress trajectory for your class.",

            'difficulty' => !empty($lessonDifficulty) && $lessonDifficulty->isNotEmpty()
                ? "<strong>Focus Recommendation:</strong> <em>'{$hardestLessonTitle}'</em> is currently the most challenging lesson with an average score of <strong>{$hardestLessonAvg}%</strong>."
                : "<strong>Curriculum Balance:</strong> Lessons are progressing smoothly across your modules.",

            'leaderboard' => count($overallStudents) > 0
                ? "<strong>Leading Student:</strong> <strong>{$topScorerName}</strong> is leading the class ranking with <strong>{$topScorerScore}%</strong>. Use the lesson selector above to check standings for any specific lesson."
                : "<strong>Student Ranking:</strong> Student rankings will populate automatically as quizzes and attempts are submitted.",

            'mastery' => ($masteryTotal > 0)
                ? "<strong>Gesture Mastery Breakdown:</strong> <strong>" . number_format($masteryDistribution->firstWhere('key', 'mastered')['pct'] ?? 0, 2) . "%</strong> of total gesture attempts have reached Mastered status. " . ($masteryDistribution->firstWhere('key', 'needs_practice')['count'] ?? 0) . " attempts still need practice."
                : "<strong>Gesture Data:</strong> Gesture mastery distribution will reflect student camera practice results.",

            'gestures' => $mostStrugglingSign
                ? "<strong>Gesture Coaching Tip:</strong> Sign <em>'{$mostStrugglingSign['gesture_name']}'</em> has the lowest class accuracy (<strong>" . number_format($mostStrugglingSign['accuracy'], 2) . "%</strong>)" . ($mostStrugglingSign['struggling_student'] ? ", with <strong>{$mostStrugglingSign['struggling_student']['name']}</strong> needing extra support." : ". Consider demonstrating it in your next class.")
                : ($topMasteredSign
                    ? "<strong>Strong Signing Skills:</strong> Sign <em>'{$topMasteredSign['gesture_name']}'</em> is mastered by the class with a stellar <strong>" . number_format($topMasteredSign['accuracy'], 2) . "%</strong> accuracy rate."
                    : "<strong>Gesture Tracking:</strong> Sign accuracy and student comparisons will appear here as students practice gestures."),
        ];

        $topPerformingGestures = collect($signsBreakdown)
            ->sortByDesc('accuracy')
            ->take(5)
            ->map(function ($s) {
                return [
                    'gesture_name'        => $s['gesture_name'],
                    'attempts'            => $s['total_attempts'],
                    'successful_attempts' => $s['successful_attempts'],
                    'wrong_attempts'      => $s['wrong_attempts'],
                    'accuracy'            => $s['accuracy'],
                ];
            });

        $lowestPerformingGestures = collect($signsBreakdown)
            ->sortBy('accuracy')
            ->take(5)
            ->map(function ($s) {
                return [
                    'gesture_name'        => $s['gesture_name'],
                    'attempts'            => $s['total_attempts'],
                    'successful_attempts' => $s['successful_attempts'],
                    'wrong_attempts'      => $s['wrong_attempts'],
                    'accuracy'            => $s['accuracy'],
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
            'lessonLeaderboards'         => $lessonLeaderboards,
            'availableLessonsList'       => $availableLessonsList,
            'gesturePerformanceOverview' => $gesturePerformanceOverview,
            'signsBreakdown'             => $signsBreakdown,
            'lowMasterySignsCount'       => $lowMasterySignsCount,
            'masteredSignsCount'         => $masteredSignsCount,
            'senyaInsights'              => $senyaInsights,
            'topPerformingGestures'      => $topPerformingGestures,
            'lowestPerformingGestures'   => $lowestPerformingGestures,
            'studentGesturePerformance'  => collect(),
        ];
    }

    private function emptyTeacherData($user): array
    {
        return [
            'totalStudents'              => 0,
            'avgQuizScore'               => 0,
            'avgMastery'                 => 0,
            'completionRate'             => 0,
            'avgStreakDays'              => 0,
            'activeLast7Pct'             => 0,
            'classSummary'               => collect([
                ['title' => 'Avg Quiz Score', 'value' => '0%', 'detail' => 'No quiz attempts yet', 'icon' => 'insights', 'accent' => '#dbeafe', 'iconColor' => '#1e3a8a'],
                ['title' => 'Gesture Mastery', 'value' => '0%', 'detail' => 'No gestures practiced', 'icon' => 'school', 'accent' => '#ecfdf5', 'iconColor' => '#15803d'],
                ['title' => 'Lesson Completion', 'value' => '0%', 'detail' => 'No assignments completed', 'icon' => 'menu_book', 'accent' => '#eff6ff', 'iconColor' => '#1e3a8a'],
                ['title' => 'Active Engagement', 'value' => '0 days', 'detail' => '0% active recently', 'icon' => 'bolt', 'accent' => '#fef3c7', 'iconColor' => '#92400e'],
            ]),
            'progressOverTime'           => [],
            'lessonDifficulty'           => collect(),
            'gestureHeatmap'             => collect(),
            'masteryDistribution'        => collect([
                ['label' => 'Needs Practice', 'key' => 'needs_practice', 'color' => '#ef4444', 'count' => 0, 'pct' => 0],
                ['label' => 'Developing', 'key' => 'developing', 'color' => '#f59e0b', 'count' => 0, 'pct' => 0],
                ['label' => 'Proficient', 'key' => 'proficient', 'color' => '#3b82f6', 'count' => 0, 'pct' => 0],
                ['label' => 'Mastered', 'key' => 'mastered', 'color' => '#10b981', 'count' => 0, 'pct' => 0],
            ]),
            'completionFunnel'           => collect(),
            'completionTotal'            => 0,
            'scoreBuckets'               => collect(),
            'maxScoreBucket'             => 1,
            'masteryTotal'               => 0,
            'studentRanking'             => collect(),
            'lessonLeaderboards'         => ['all' => ['lesson_id' => 'all', 'title' => 'All Lessons', 'total_ranked' => 0, 'rankings' => []]],
            'availableLessonsList'       => [['id' => 'all', 'title' => 'All Lessons']],
            'gesturePerformanceOverview' => [
                'total_gestures'   => 0,
                'total_attempts'   => 0,
                'total_successful' => 0,
                'total_wrong'      => 0,
                'overall_accuracy' => 0,
                'total_mastered'   => 0,
            ],
            'signsBreakdown'             => [],
            'lowMasterySignsCount'       => 0,
            'masteredSignsCount'         => 0,
            'senyaInsights'              => [
                'kpi'         => "👋 <strong>Welcome!</strong> Class insights will automatically appear once students begin completing lessons and practicing gestures.",
                'progress'    => "📈 <strong>Track Progress:</strong> Weekly and monthly trends will graph here after assignments are submitted.",
                'difficulty'  => "🎯 <strong>Lesson Insights:</strong> Hardest modules will be detected to highlight where to focus classroom time.",
                'leaderboard' => "🏆 <strong>Class Rankings:</strong> Student leaderboards per lesson will display here.",
                'mastery'     => "📊 <strong>Gesture Mastery:</strong> Distribution of student sign skills will be tracked here.",
                'gestures'    => "👋 <strong>Sign Breakdown:</strong> Compare top vs struggling performers on each sign gesture.",
            ],
            'topPerformingGestures'      => collect(),
            'lowestPerformingGestures'   => collect(),
            'studentGesturePerformance'  => collect(),
        ];
    }
}