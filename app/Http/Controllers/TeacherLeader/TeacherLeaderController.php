<?php

namespace App\Http\Controllers\TeacherLeader;

use App\Http\Controllers\Controller;
use App\Models\GestureMedia;
use App\Models\Module;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\LessonTemplateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * TeacherLeaderController
 *
 * Handles all four tabs of the Teacher Leader portal:
 *   - dashboard  : school-wide KPIs + performance widgets
 *   - lessons    : read-only view of default curriculum templates
 *   - media      : read-only view of system gesture media
 *   - analytics  : school-scoped academic charts & breakdowns
 *
 * All data is strictly filtered by the Teacher Leader's assigned school_id.
 * No create / edit / delete actions are exposed.
 */
class TeacherLeaderController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve the current teacher_leader's school_id.
     * Falls back to 0 so queries still run without null errors.
     */
    private function schoolId(): int
    {
        return (int) (Auth::user()->teacher->school_id ?? 0);
    }

    /**
     * Collect the IDs of every teacher in this school.
     */
    private function schoolTeacherIds(int $schoolId): \Illuminate\Support\Collection
    {
        return Teacher::where('school_id', $schoolId)->pluck('id');
    }

    /**
     * Collect the IDs of every student in this school.
     */
    private function schoolStudentIds(int $schoolId): \Illuminate\Support\Collection
    {
        return Student::where('school_id', $schoolId)->pluck('student_id');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. DASHBOARD — School Academic Performance
    // ─────────────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $schoolId   = $this->schoolId();
        $school     = Auth::user()->teacher->school ?? null;

        $teacherIds = $this->schoolTeacherIds($schoolId);
        $studentIds = $this->schoolStudentIds($schoolId);

        // ── KPI Counts ───────────────────────────────────────────────────────
        $totalTeachers = $teacherIds->count();
        $totalStudents = $studentIds->count();

        // Lesson completion rate for the school
        $lessonTotals = DB::table('lesson_assignments')
            ->whereIn('student_id', $studentIds)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
            ->first();

        $totalAssigned  = (int) ($lessonTotals->total ?? 0);
        $totalCompleted = (int) ($lessonTotals->completed ?? 0);
        $completionRate = $totalAssigned > 0
            ? round(($totalCompleted / $totalAssigned) * 100, 1)
            : 0;

        // Average quiz score across the school
        $avgQuizScore = round((float) DB::table('quiz_attempts')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->avg('percentage') ?? 0, 1);

        // Active students in last 7 days
        $activeStudents = Student::whereIn('student_id', $studentIds)
            ->where('last_activity_date', '>=', Carbon::now()->subDays(7))
            ->count();

        // ── 14-Day Activity Trend ─────────────────────────────────────────────
        $activityTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $activityTrend[] = [
                'label'       => Carbon::now()->subDays($i)->format('M j'),
                'completions' => DB::table('lesson_assignments')
                    ->whereIn('student_id', $studentIds)
                    ->where('status', 'completed')
                    ->whereDate('updated_at', $date)
                    ->count(),
                'students'    => Student::whereIn('student_id', $studentIds)
                    ->whereDate('last_activity_date', $date)
                    ->count(),
            ];
        }

        // ── Top Classes (teachers by student avg quiz score) ──────────────────
        $topClasses = Teacher::whereIn('id', $teacherIds)
            ->with('user')
            ->withCount('students')
            ->get()
            ->map(function ($teacher) {
                $studentIds = $teacher->students()->pluck('student_id');
                $avg = DB::table('quiz_attempts')
                    ->whereIn('student_id', $studentIds)
                    ->where('status', 'completed')
                    ->avg('percentage') ?? 0;
                return [
                    'teacher'       => $teacher,
                    'avg_score'     => round((float) $avg, 1),
                    'student_count' => $teacher->students_count,
                ];
            })
            ->sortByDesc('avg_score')
            ->values()
            ->take(5);

        // ── Classes Needing Support (lowest avg score) ────────────────────────
        $needsSupport = Teacher::whereIn('id', $teacherIds)
            ->with('user')
            ->withCount('students')
            ->get()
            ->map(function ($teacher) {
                $studentIds = $teacher->students()->pluck('student_id');
                $avg = DB::table('quiz_attempts')
                    ->whereIn('student_id', $studentIds)
                    ->where('status', 'completed')
                    ->avg('percentage') ?? 0;
                return [
                    'teacher'       => $teacher,
                    'avg_score'     => round((float) $avg, 1),
                    'student_count' => $teacher->students_count,
                ];
            })
            ->filter(fn ($c) => $c['student_count'] > 0)
            ->sortBy('avg_score')
            ->values()
            ->take(3);

        // ── Recent Engagement: newest active students ─────────────────────────
        $recentEngagement = Student::whereIn('student_id', $studentIds)
            ->whereNotNull('last_activity_date')
            ->orderByDesc('last_activity_date')
            ->limit(6)
            ->get();

        // ── Sparklines (7 days) ───────────────────────────────────────────────
        $sparkDates      = [];
        $sparkStudents   = [];
        $sparkLessons    = [];
        $sparkTeachers   = [];
        for ($i = 6; $i >= 0; $i--) {
            $day  = Carbon::now()->subDays($i);
            $date = $day->toDateString();
            $sparkDates[]    = ['short' => $day->format('M j'), 'day' => $i === 0 ? 'Today' : ($i === 1 ? 'Yesterday' : $day->format('l')), 'date' => $day->format('M j, Y')];
            $sparkStudents[] = Student::whereIn('student_id', $studentIds)->whereDate('last_activity_date', $date)->count();
            $sparkLessons[]  = DB::table('lesson_assignments')->whereIn('student_id', $studentIds)->where('status', 'completed')->whereDate('updated_at', $date)->count();
            $sparkTeachers[] = User::whereIn('id', Teacher::whereIn('id', $teacherIds)->pluck('user_id'))->whereDate('updated_at', '<=', $date)->count();
        }

        return view('teacher-leader.dashboard', compact(
            'school', 'schoolId',
            'totalTeachers', 'totalStudents',
            'completionRate', 'totalAssigned', 'totalCompleted',
            'avgQuizScore', 'activeStudents',
            'activityTrend', 'topClasses', 'needsSupport', 'recentEngagement',
            'sparkDates', 'sparkStudents', 'sparkLessons', 'sparkTeachers'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. DEFAULT LESSONS — Read-only curriculum catalog
    // ─────────────────────────────────────────────────────────────────────────

    public function lessons(Request $request)
    {
        $systemTeacherId = app(LessonTemplateService::class)->templateTeacherId();

        $search = trim($request->input('search', ''));

        $query = Module::where('teacher_id', $systemTeacherId)
            ->where('is_template', true)
            ->with([
                'lessons' => function ($q) use ($search) {
                    $q->whereNull('deleted_at')
                      ->orderBy('module_order')
                      ->with('quiz.questions', 'contents');
                    if ($search) {
                        $q->where('title', 'like', "%{$search}%");
                    }
                },
            ])
            ->orderBy('module_order');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('lessons', fn ($l) =>
                      $l->where('title', 'like', "%{$search}%")->whereNull('deleted_at')
                  );
            });
        }

        $modules = $query->get();

        // Flatten lesson count for summary
        $totalLessons  = $modules->sum(fn ($m) => $m->lessons->count());
        $totalModules  = $modules->count();

        return view('teacher-leader.lessons', compact('modules', 'totalLessons', 'totalModules', 'search'));
    }

    /**
     * AJAX — return lesson detail for the preview modal.
     */
    public function lessonPreview(int $lessonId)
    {
        $systemTeacherId = app(LessonTemplateService::class)->templateTeacherId();

        $lesson = \App\Models\Lesson::with(['contents', 'quiz.questions', 'module'])
            ->where('teacher_id', $systemTeacherId)
            ->where('is_template', true)
            ->whereNull('deleted_at')
            ->findOrFail($lessonId);

        return response()->json([
            'lesson'    => $lesson,
            'module'    => $lesson->module ? ['title' => $lesson->module->title] : null,
            'contents'  => $lesson->contents,
            'quiz'      => $lesson->quiz ? [
                'question_count' => $lesson->quiz->questions->count(),
            ] : null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. SYSTEM MEDIA — Read-only gallery
    // ─────────────────────────────────────────────────────────────────────────

    public function media(Request $request)
    {
        $search    = trim($request->input('search', ''));
        $typeFilter = $request->input('type', '');

        $query = GestureMedia::with(['gesture.module', 'module'])
            ->orderBy('order')
            ->orderBy('media_id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhereHas('gesture', fn ($g) =>
                      $g->where('display_name', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                  )
                  ->orWhereHas('module', fn ($m) =>
                      $m->where('display_name', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                  );
            });
        }

        if ($typeFilter) {
            $query->where('media_type', $typeFilter);
        }

        $media = $query->paginate(30)->withQueryString();

        // Counts for filter badges
        $typeCounts = GestureMedia::selectRaw('media_type, COUNT(*) as count')
            ->groupBy('media_type')
            ->pluck('count', 'media_type');

        return view('teacher-leader.media', compact('media', 'search', 'typeFilter', 'typeCounts'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. ANALYTICS — School Academic Insights
    // ─────────────────────────────────────────────────────────────────────────

    public function analytics(Request $request)
    {
        $schoolId   = $this->schoolId();
        $school     = Auth::user()->teacher->school ?? null;
        $teacherIds = $this->schoolTeacherIds($schoolId);
        $studentIds = $this->schoolStudentIds($schoolId);

        $period = $request->get('period', 'weekly');
        $year   = (int) $request->get('year', date('Y'));
        $month  = (int) $request->get('month', date('n'));

        // ── Date window ───────────────────────────────────────────────────────
        [$startDate, $endDate] = $this->buildDateWindow($period, $year, $month);

        $lessonIds = \App\Models\Lesson::whereIn('teacher_id', $teacherIds)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->pluck('lesson_id');

        // ── Top-level KPIs ────────────────────────────────────────────────────
        $avgQuizScore = round((float) DB::table('quiz_attempts')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->avg('percentage') ?? 0, 1);

        $quizPassRate = $studentIds->isNotEmpty()
            ? round((float) DB::table('quiz_attempts')
                ->whereIn('student_id', $studentIds)
                ->where('status', 'completed')
                ->where('percentage', '>=', 75)
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->count() /
              max(1, DB::table('quiz_attempts')
                ->whereIn('student_id', $studentIds)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->count()) * 100, 1)
            : 0;

        $assignmentTotals = DB::table('lesson_assignments')
            ->whereIn('student_id', $studentIds)
            ->whereIn('lesson_id', $lessonIds)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
            ->first();

        $completionRate = ($assignmentTotals && $assignmentTotals->total > 0)
            ? round(($assignmentTotals->completed / $assignmentTotals->total) * 100, 1)
            : 0;

        $activeStudentsCount = Student::whereIn('student_id', $studentIds)
            ->whereBetween('last_activity_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->count();

        // ── Quiz Score Distribution (5 bands) ────────────────────────────────
        $scoreBands = [
            '0–20%'  => [0, 20],
            '21–40%' => [21, 40],
            '41–60%' => [41, 60],
            '61–80%' => [61, 80],
            '81–100%'=> [81, 100],
        ];
        $scoreBuckets = [];
        foreach ($scoreBands as $label => [$lo, $hi]) {
            $scoreBuckets[] = [
                'label' => $label,
                'count' => DB::table('quiz_attempts')
                    ->whereIn('student_id', $studentIds)
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$startDate, $endDate])
                    ->whereBetween('percentage', [$lo, $hi])
                    ->count(),
            ];
        }

        // ── Lesson Completion Trend over time ─────────────────────────────────
        $completionTrend = $this->buildCompletionTrend($period, $year, $month, $studentIds, $startDate, $endDate);

        // ── Per-teacher / class performance ──────────────────────────────────
        $classBreakdown = Teacher::whereIn('id', $teacherIds)
            ->with('user')
            ->get()
            ->map(function ($teacher) use ($startDate, $endDate) {
                $tStudentIds = $teacher->students()->pluck('student_id');
                $tLessonIds  = \App\Models\Lesson::where('teacher_id', $teacher->id)
                    ->where('status', 'published')
                    ->whereNull('deleted_at')
                    ->pluck('lesson_id');

                $avg = DB::table('quiz_attempts')
                    ->whereIn('student_id', $tStudentIds)
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$startDate, $endDate])
                    ->avg('percentage') ?? 0;

                $assigned  = DB::table('lesson_assignments')
                    ->whereIn('student_id', $tStudentIds)
                    ->whereIn('lesson_id', $tLessonIds)
                    ->count();
                $completed = DB::table('lesson_assignments')
                    ->whereIn('student_id', $tStudentIds)
                    ->whereIn('lesson_id', $tLessonIds)
                    ->where('status', 'completed')
                    ->count();

                return [
                    'name'            => trim($teacher->first_name . ' ' . $teacher->last_name),
                    'student_count'   => $tStudentIds->count(),
                    'avg_quiz_score'  => round((float) $avg, 1),
                    'completion_rate' => $assigned > 0 ? round($completed / $assigned * 100, 1) : 0,
                ];
            })
            ->sortByDesc('avg_quiz_score')
            ->values();

        // ── Gesture Mastery per competency ────────────────────────────────────
        $gestureMastery = DB::table('gesture_performances')
            ->join('gestures', 'gesture_performances.gesture_id', '=', 'gestures.gesture_id')
            ->whereIn('gesture_performances.student_id', $studentIds)
            ->where('gesture_performances.attempts', '>', 0)
            ->selectRaw('gestures.display_name as gesture_name,
                COUNT(*) as total_attempts,
                SUM(CASE WHEN gesture_performances.is_mastered = 1 THEN 1 ELSE 0 END) as mastered_count')
            ->groupBy('gestures.gesture_id', 'gestures.display_name')
            ->orderByRaw('mastered_count / total_attempts DESC')
            ->limit(12)
            ->get()
            ->map(function ($row) {
                $rate = $row->total_attempts > 0
                    ? round($row->mastered_count / $row->total_attempts * 100, 1)
                    : 0;
                return [
                    'name' => $row->gesture_name,
                    'rate' => $rate,
                    'color' => $rate >= 80 ? '#22c55e' : ($rate >= 50 ? '#f59e0b' : '#ef4444'),
                ];
            });

        // ── Completion Funnel ─────────────────────────────────────────────────
        $funnelStatuses = ['pending', 'in_progress', 'completed', 'failed'];
        $completionFunnel = [];
        foreach ($funnelStatuses as $status) {
            $completionFunnel[] = [
                'status' => ucfirst(str_replace('_', ' ', $status)),
                'count'  => DB::table('lesson_assignments')
                    ->whereIn('student_id', $studentIds)
                    ->whereIn('lesson_id', $lessonIds)
                    ->where('status', $status)
                    ->count(),
            ];
        }

        // ── Grade-level breakdown ─────────────────────────────────────────────
        $gradeLevelBreakdown = Student::whereIn('student_id', $studentIds)
            ->selectRaw('grade_level, COUNT(*) as student_count')
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->get()
            ->map(function ($row) use ($studentIds, $startDate, $endDate) {
                $gStudentIds = Student::whereIn('student_id', $studentIds)
                    ->where('grade_level', $row->grade_level)
                    ->pluck('student_id');
                $avg = DB::table('quiz_attempts')
                    ->whereIn('student_id', $gStudentIds)
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$startDate, $endDate])
                    ->avg('percentage') ?? 0;
                return [
                    'grade'         => 'Grade ' . $row->grade_level,
                    'student_count' => $row->student_count,
                    'avg_score'     => round((float) $avg, 1),
                ];
            });

        return view('teacher-leader.analytics', compact(
            'school', 'period', 'year', 'month',
            'startDate', 'endDate',
            'avgQuizScore', 'quizPassRate', 'completionRate', 'activeStudentsCount',
            'scoreBuckets', 'completionTrend', 'classBreakdown',
            'gestureMastery', 'completionFunnel', 'gradeLevelBreakdown'
        ));
    }

    /**
     * POST /teacher-leader/analytics/filter
     * Store filters in session → redirect (PRG pattern).
     */
    public function analyticsFilter(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'string', 'in:weekly,monthly,quarterly,yearly'],
            'year'   => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month'  => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        if (empty(array_filter($validated))) {
            session()->forget('tl_analytics_filters');
        } else {
            session(['tl_analytics_filters' => $validated]);
        }

        return redirect()->route('teacher-leader.analytics');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function buildDateWindow(string $period, int $year, int $month): array
    {
        if ($period === 'monthly') {
            return [
                Carbon::create($year, $month, 1)->startOfMonth()->startOfDay(),
                Carbon::create($year, $month, 1)->endOfMonth()->endOfDay(),
            ];
        }
        if ($period === 'quarterly') {
            $qStartMonth = (int) (ceil($month / 3) - 1) * 3 + 1;
            return [
                Carbon::create($year, $qStartMonth, 1)->startOfMonth()->startOfDay(),
                Carbon::create($year, $qStartMonth + 2, 1)->endOfMonth()->endOfDay(),
            ];
        }
        if ($period === 'yearly') {
            return [
                Carbon::create($year, 1, 1)->startOfYear()->startOfDay(),
                Carbon::create($year, 12, 31)->endOfYear()->endOfDay(),
            ];
        }
        // Default: weekly — last 8 weeks
        return [
            Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(7)->startOfDay(),
            Carbon::now()->endOfWeek(Carbon::SUNDAY)->endOfDay(),
        ];
    }

    private function buildCompletionTrend(string $period, int $year, int $month, $studentIds, Carbon $start, Carbon $end): array
    {
        $trend = [];

        if ($period === 'weekly') {
            for ($w = 7; $w >= 0; $w--) {
                $ws = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks($w);
                $we = $ws->copy()->endOfWeek(Carbon::SUNDAY);
                $trend[] = [
                    'label' => $ws->format('M d'),
                    'count' => DB::table('lesson_assignments')
                        ->whereIn('student_id', $studentIds)
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$ws->startOfDay(), $we->endOfDay()])
                        ->count(),
                ];
            }
        } elseif ($period === 'monthly') {
            $mStart = Carbon::create($year, $month, 1)->startOfMonth();
            for ($i = 0; $i < 4; $i++) {
                $ds = $mStart->copy()->addDays($i * 7);
                $de = $i === 3 ? $mStart->copy()->endOfMonth() : $mStart->copy()->addDays(($i + 1) * 7 - 1);
                $trend[] = [
                    'label' => 'Week ' . ($i + 1),
                    'count' => DB::table('lesson_assignments')
                        ->whereIn('student_id', $studentIds)
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$ds->startOfDay(), $de->endOfDay()])
                        ->count(),
                ];
            }
        } elseif ($period === 'quarterly') {
            for ($m = 0; $m < 3; $m++) {
                $mDate = $start->copy()->addMonths($m);
                $trend[] = [
                    'label' => $mDate->format('M Y'),
                    'count' => DB::table('lesson_assignments')
                        ->whereIn('student_id', $studentIds)
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$mDate->copy()->startOfMonth(), $mDate->copy()->endOfMonth()])
                        ->count(),
                ];
            }
        } else { // yearly
            for ($m = 1; $m <= 12; $m++) {
                $mDate = Carbon::create($year, $m, 1);
                $trend[] = [
                    'label' => $mDate->format('M'),
                    'count' => DB::table('lesson_assignments')
                        ->whereIn('student_id', $studentIds)
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$mDate->copy()->startOfMonth(), $mDate->copy()->endOfMonth()])
                        ->count(),
                ];
            }
        }

        return $trend;
    }
}
