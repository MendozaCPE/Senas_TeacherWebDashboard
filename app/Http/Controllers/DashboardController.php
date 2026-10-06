<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Student;
use App\Models\StudentYearEnrollment;
use App\Models\LessonAssignment;
use App\Models\StudentLessonProgress;
use App\Models\Gesture;
use App\Models\GesturePerformance;
use App\Models\CheckpointExamAttempt;
use App\Models\DailyChallenge;
use App\Models\SchoolYear;
use App\Services\SenyaInsightsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        // Build time-based greeting
        $hour = Carbon::now()->format('H');
        if ($hour < 12) {
            $greeting = 'Good morning';
        } elseif ($hour < 18) {
            $greeting = 'Good afternoon';
        } else {
            $greeting = 'Good evening';
        }

        // Get first name for display
        if ($teacher && $teacher->first_name) {
            $firstName = $teacher->first_name;
        } else {
            $firstName = $user->name ?? 'Teacher';
        }

        // Calendar month selector
        $calendarMonth = $request->query('month');
        try {
            $calendarDate = $calendarMonth
                ? Carbon::createFromFormat('Y-m', $calendarMonth)->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (\Exception $e) {
            $calendarDate = Carbon::now()->startOfMonth();
        }

        // Default stats (fallback if no teacher record)
        $totalStudents       = 0;
        $activeToday         = 0;
        $totalLessons        = 0;
        $avgAccuracy         = 0;
        $newStudentsThisWeek = 0;
        $activeTodayPercent  = 0;
        $accuracyWeeklyChange= 0;
        $newLessonsThisWeek  = 0;
        $sparklineTotalStudents   = [];
        $sparklineActive          = [];
        $sparklineAccuracy        = [];
        $sparklineLessons         = [];
        $students         = collect();
        $lessons          = collect();
        $modules          = collect();
        $lessonMastery    = collect();
        $classRate        = 0;
        $needsAttention   = collect();
        $topLesson        = null;
        $allStudents      = collect();

        if ($teacher) {
            $teacherId = $teacher->id;
            $schoolId  = (int) ($teacher->school_id ?? 0);

            // Always use the active school year — no user-selectable filter on the dashboard.
            $activeSchoolYear = \App\Models\SchoolYear::where('school_id', $schoolId ?: null)
                ->orderByDesc('name')
                ->get()
                ->firstWhere('status', 'active')
                ?? \App\Models\SchoolYear::where('school_id', $schoolId ?: null)
                    ->orderByDesc('name')
                    ->first();

            // The active year's ID — used to scope progress/assignment queries.
            $activeSyId = $activeSchoolYear?->id;

            // ── School Year Date Boundaries ──────────────────────────────────────
            $syStartDate = null;
            $syEndDate   = Carbon::now()->endOfDay();
            if ($activeSchoolYear) {
                $syStartDate = $activeSchoolYear->start_date
                    ? Carbon::parse($activeSchoolYear->start_date)->startOfDay()
                    : null;
                $syEndDate = $activeSchoolYear->end_date
                    ? Carbon::parse($activeSchoolYear->end_date)->endOfDay()
                    : Carbon::now()->endOfDay();
            }
            // Fall back to DepEd default start (July 1) when no real date is stored yet
            if (!$syStartDate && $activeSchoolYear) {
                [$syY] = explode('-', $activeSchoolYear->name);
                $syStartDate = Carbon::create((int)$syY, 7, 1)->startOfDay();
            }

            // ── Month Filter Options (active-year-scoped) ────────────────────────
            // Build Month-Year options from the active school year start to today.
            $syMonthOptions = [];
            $cursor = $syStartDate ? $syStartDate->copy()->startOfMonth() : Carbon::now()->startOfMonth();
            $monthCap = Carbon::now()->startOfMonth();
            while ($cursor->lte($monthCap)) {
                $syMonthOptions[] = [
                    'value' => $cursor->format('Y-m'),
                    'label' => $cursor->format('M Y'),
                ];
                $cursor->addMonth();
            }

            // Selected month from query string (default: current month)
            $selectedMonthParam = $request->query('month');
            $validMonths = array_column($syMonthOptions, 'value');
            if (!$selectedMonthParam || !in_array($selectedMonthParam, $validMonths)) {
                $selectedMonthParam = $monthCap->format('Y-m');
            }
            try {
                $selectedMonthDate = Carbon::createFromFormat('Y-m', $selectedMonthParam)->startOfMonth();
            } catch (\Exception) {
                $selectedMonthDate = Carbon::now()->startOfMonth();
            }

            // $monthlyActivity is built below, after $studentIds is resolved.

            // ── Student query — active year, currently enrolled ──────────────────
            $teacherLessonIds = Lesson::where('teacher_id', $teacherId)
                ->whereNull('deleted_at')
                ->pluck('lesson_id');

            $studentIds = Student::where('teacher_id', $teacherId)
                ->where('school_year', $activeSchoolYear?->name)
                ->where('is_enrolled', true)
                ->pluck('student_id');

            $lessonIds = Lesson::where('teacher_id', $teacherId)
                ->where('status', 'published')
                ->whereNull('deleted_at')
                ->pluck('lesson_id');

            // ── Monthly Activity Data (active-year-scoped, per month) ─────────────
            $monthlyActivity = [];
            foreach ($syMonthOptions as $mo) {
                $moStart = Carbon::createFromFormat('Y-m', $mo['value'])->startOfMonth();
                $moEnd   = $moStart->copy()->endOfMonth();

                $moActiveStudents = StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->when($activeSyId, fn($q) => $q->where('school_year_id', $activeSyId))
                    ->whereBetween('last_accessed_at', [$moStart, $moEnd])
                    ->distinct('student_id')
                    ->count('student_id');

                $moCompletions = StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->when($activeSyId, fn($q) => $q->where('school_year_id', $activeSyId))
                    ->where('lesson_completed', 1)
                    ->whereBetween('updated_at', [$moStart, $moEnd])
                    ->count();

                $monthlyActivity[] = [
                    'month'           => $mo['value'],
                    'label'           => $mo['label'],
                    'short'           => $moStart->format('M'),
                    'active_students' => $moActiveStudents,
                    'completions'     => $moCompletions,
                ];
            }

            // ── Stat Cards ───────────────────────────────────────────────────────
            $totalStudents = $studentIds->count();

            // Year-over-year student count change:
            // current S.Y. enrollments minus previous S.Y. enrollments
            $activeSyName   = $activeSchoolYear?->name ?? '';
            $prevSyName     = '';
            if ($activeSyName && preg_match('/^(\d{4})-(\d{4})$/', $activeSyName, $m)) {
                $prevSyName = ($m[1] - 1) . '-' . ($m[2] - 1);
            }
            $currentSyCount  = $totalStudents;
            $previousSyCount = $prevSyName
                ? StudentYearEnrollment::where('teacher_id', $teacherId)
                    ->where('school_year_name', $prevSyName)
                    ->count()
                : 0;
            $newStudentsThisWeek = $currentSyCount - $previousSyCount;

            // Active Today: any student who accessed a lesson, practiced a gesture,
            // attempted a checkpoint exam, or did a daily challenge today.
            $activeTodayLesson = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereDate('last_accessed_at', Carbon::today())
                ->pluck('student_id');

            $activeTodayGesture = GesturePerformance::whereIn('student_id', $studentIds)
                ->whereDate('last_attempt_at', Carbon::today())
                ->pluck('student_id');

            $activeTodayExam = CheckpointExamAttempt::whereIn('student_id', $studentIds)
                ->whereDate('updated_at', Carbon::today())
                ->pluck('student_id');

            $activeTodayChallenge = DailyChallenge::whereIn('student_id', $studentIds)
                ->whereDate('challenge_date', Carbon::today())
                ->pluck('student_id');

            $activeToday = $activeTodayLesson
                ->merge($activeTodayGesture)
                ->merge($activeTodayExam)
                ->merge($activeTodayChallenge)
                ->unique()
                ->count();

            $activeTodayPercent = $totalStudents > 0
                ? round(($activeToday / $totalStudents) * 100)
                : 0;

            $totalLessons = Lesson::where('teacher_id', $teacherId)
                ->whereNull('deleted_at')
                ->count();

            $newLessonsThisWeek = Lesson::where('teacher_id', $teacherId)
                ->whereNull('deleted_at')
                ->where('created_at', '>=', Carbon::now()->subWeek())
                ->count();

            // ── Sparklines (last 7 days, ending today) ───────────────────────────
            $sparklineDates = [];
            for ($i = 6; $i >= 0; $i--) {
                $day = Carbon::today()->subDays($i);

                $sparklineDates[] = [
                    'day'   => $i === 0 ? 'Today' : ($i === 1 ? 'Yesterday' : $day->format('l')),
                    'date'  => $day->format('M j, Y'),
                    'short' => $day->format('M j'),
                ];

                $sparklineTotalStudents[] = Student::where('teacher_id', $teacherId)
                    ->where('school_year', $activeSchoolYear?->name)
                    ->where('is_enrolled', true)
                    ->whereDate('created_at', '<=', $day)
                    ->count();

                $sparklineActive[] = StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->whereIn('lesson_id', $lessonIds)
                    ->when($activeSyId, fn($q) => $q->where('school_year_id', $activeSyId))
                    ->whereDate('last_accessed_at', $day)
                    ->distinct('student_id')
                    ->count('student_id');

                $dayAccuracy = StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->whereIn('lesson_id', $lessonIds)
                    ->when($activeSyId, fn($q) => $q->where('school_year_id', $activeSyId))
                    ->whereNotNull('quiz_score')
                    ->whereDate('updated_at', $day)
                    ->avg('quiz_score');
                $sparklineAccuracy[] = $dayAccuracy ? (int) round($dayAccuracy) : 0;

                $sparklineLessons[] = Lesson::where('teacher_id', $teacherId)
                    ->whereNull('deleted_at')
                    ->whereDate('created_at', '<=', $day)
                    ->count();
            }

            // ── Your Modules (grouped, for dashboard folder cards) ───────────────
            $modules = Module::where('teacher_id', $teacherId)
                ->with(['lessons' => function ($q) {
                    $q->where('status', 'published')->whereNull('deleted_at')->orderBy('module_order');
                }])
                ->orderBy('module_order')
                ->get()
                ->map(function ($module) use ($activeSyId, $studentIds, $teacherId, $activeSchoolYear) {
                    $lessonIds = $module->lessons->pluck('lesson_id');

                    if ($lessonIds->isEmpty()) {
                        $module->enrolled     = 0;
                        $module->completion   = 0;
                        $module->topStudents  = collect();
                        $module->extraStudents = 0;
                        return $module;
                    }

                    $assignedQuery = LessonAssignment::whereIn('lesson_id', $lessonIds)
                        ->join('students', 'students.student_id', '=', 'lesson_assignments.student_id')
                        ->where('students.teacher_id', $teacherId)
                        ->where('students.school_year', $activeSchoolYear?->name)
                        ->where('students.is_enrolled', true);

                    if ($activeSyId) {
                        $assignedQuery->where('lesson_assignments.school_year_id', $activeSyId);
                    }

                    $assignedStudentIds = (clone $assignedQuery)
                        ->distinct('lesson_assignments.student_id')
                        ->pluck('lesson_assignments.student_id');

                    $enrolled = $assignedStudentIds->count();

                    $completedQuery = (clone $assignedQuery)->whereExists(function ($sub) use ($activeSyId) {
                        $sub->select(DB::raw(1))
                            ->from('student_lesson_progress')
                            ->whereColumn('student_lesson_progress.student_id', 'lesson_assignments.student_id')
                            ->whereColumn('student_lesson_progress.lesson_id', 'lesson_assignments.lesson_id')
                            ->where('student_lesson_progress.lesson_completed', 1)
                            ->when($activeSyId, fn($q) => $q->where('student_lesson_progress.school_year_id', $activeSyId));
                    });

                    $completed = $completedQuery->distinct('lesson_assignments.student_id')->count('lesson_assignments.student_id');

                    $module->enrolled   = $enrolled;
                    $module->completion = $enrolled > 0 ? round(($completed / $enrolled) * 100) : 0;

                    $module->topStudents   = Student::whereIn('student_id', $assignedStudentIds)->take(3)->get();
                    $module->extraStudents = max(0, $enrolled - 3);

                    return $module;
                });

            // ── Your Lessons (with enrolled count + completion %) ────────────────
            $lessons = Lesson::where('teacher_id', $teacherId)
                ->where('status', 'published')
                ->whereNull('deleted_at')
                ->orderBy('module_order')
                ->get()
                ->map(function ($lesson) use ($activeSyId, $teacherId, $activeSchoolYear) {
                    $assignedQuery = LessonAssignment::where('lesson_assignments.lesson_id', $lesson->lesson_id)
                        ->join('students', 'students.student_id', '=', 'lesson_assignments.student_id')
                        ->where('students.teacher_id', $teacherId)
                        ->where('students.school_year', $activeSchoolYear?->name)
                        ->where('students.is_enrolled', true);

                    if ($activeSyId) {
                        $assignedQuery->where('lesson_assignments.school_year_id', $activeSyId);
                    }

                    $assignedStudentIds = (clone $assignedQuery)
                        ->distinct('lesson_assignments.student_id')
                        ->pluck('lesson_assignments.student_id');

                    $enrolled = $assignedStudentIds->count();

                    $completedQuery = (clone $assignedQuery)->whereExists(function ($sub) use ($activeSyId) {
                        $sub->select(DB::raw(1))
                            ->from('student_lesson_progress')
                            ->whereColumn('student_lesson_progress.student_id', 'lesson_assignments.student_id')
                            ->whereColumn('student_lesson_progress.lesson_id', 'lesson_assignments.lesson_id')
                            ->where('student_lesson_progress.lesson_completed', 1)
                            ->when($activeSyId, fn($q) => $q->where('student_lesson_progress.school_year_id', $activeSyId));
                    });

                    $completed = $completedQuery->distinct('lesson_assignments.student_id')->count('lesson_assignments.student_id');

                    $lesson->enrolled   = $enrolled;
                    $lesson->completion = $enrolled > 0 ? round(($completed / $enrolled) * 100) : 0;

                    $lesson->topStudents  = Student::whereIn('student_id', $assignedStudentIds)->take(3)->get();
                    $lesson->extraStudents = max(0, $enrolled - 3);

                    return $lesson;
                });

            // ── Student Mastery per lesson (all lessons, not just top 3) ─────────
            $lessonMastery = Lesson::where('teacher_id', $teacherId)
                ->where('status', 'published')
                ->whereNull('deleted_at')
                ->orderBy('module_order')
                ->take(4)
                ->get()
                ->map(function ($lesson) use ($studentIds, $activeSyId) {
                    $enrolled  = StudentLessonProgress::whereIn('student_id', $studentIds)
                        ->where('lesson_id', $lesson->lesson_id)
                        ->when($activeSyId, fn($q) => $q->where('school_year_id', $activeSyId))
                        ->distinct('student_id')
                        ->count('student_id');

                    $completed = StudentLessonProgress::whereIn('student_id', $studentIds)
                        ->where('lesson_id', $lesson->lesson_id)
                        ->when($activeSyId, fn($q) => $q->where('school_year_id', $activeSyId))
                        ->where('lesson_completed', 1)
                        ->count();

                    $lesson->masteryPct = $enrolled > 0 ? round(($completed / $enrolled) * 100) : 0;
                    return $lesson;
                });

            // ── Overall Class Rate ────────────────────────────────────────────────
            $totalProgress  = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->when($activeSyId, fn($q) => $q->where('school_year_id', $activeSyId))
                ->count();
            $totalCompleted = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->when($activeSyId, fn($q) => $q->where('school_year_id', $activeSyId))
                ->where('lesson_completed', 1)
                ->count();
            $classRate = $totalProgress > 0 ? round(($totalCompleted / $totalProgress) * 100) : 0;

            // ── Average Accuracy ──────────────────────────────────────────────────
            $avgScore = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->when($activeSyId, fn($q) => $q->where('school_year_id', $activeSyId))
                ->whereNotNull('quiz_score')
                ->avg('quiz_score');
            $avgAccuracy = $avgScore ? (int) round($avgScore) : 0;

            $avgThisWeek = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->when($activeSyId, fn($q) => $q->where('school_year_id', $activeSyId))
                ->whereNotNull('quiz_score')
                ->where('updated_at', '>=', Carbon::now()->subWeek())
                ->avg('quiz_score');

            $avgLastWeek = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->when($activeSyId, fn($q) => $q->where('school_year_id', $activeSyId))
                ->whereNotNull('quiz_score')
                ->whereBetween('updated_at', [Carbon::now()->subWeeks(2), Carbon::now()->subWeek()])
                ->avg('quiz_score');

            if ($avgThisWeek !== null && $avgLastWeek !== null) {
                $accuracyWeeklyChange = (int) round($avgThisWeek - $avgLastWeek);
            } elseif ($avgThisWeek !== null) {
                $accuracyWeeklyChange = (int) round($avgThisWeek);
            }

            // ── Student Performance (5 most recent enrolled students) ─────────────
            $students = Student::where('teacher_id', $teacherId)
                ->whereIn('student_id', $studentIds->isEmpty() ? [-1] : $studentIds)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($student) use ($activeSyId) {
                    $progressQuery = $student->progress();
                    if ($activeSyId) {
                        $progressQuery = $progressQuery->where('school_year_id', $activeSyId);
                    }
                    $progressRecords = $progressQuery->get();
                    $total     = $progressRecords->count();
                    $completed = $progressRecords->where('lesson_completed', 1)->count();
                    $student->performancePct = $total > 0 ? round(($completed / $total) * 100) : 0;
                    return $student;
                });

        // ── My Students (sidebar list) — active year, currently enrolled ────────
            $allStudents = Student::where('teacher_id', $teacherId)
                ->where('school_year', $activeSchoolYear?->name)
                ->where('is_enrolled', true)
                ->orderBy('first_name')
                ->get();

            // ── Enrollment Trend per School Year ─────────────────────────────────
            // Queries student_year_enrollments — an immutable, append-only log that
            // records one row per student per school year at the moment of enrollment.
            // This is the authoritative source and is immune to the students.school_year
            // column being overwritten during SY transitions.
            //
            // IMPORTANT: Anchor the rightmost year to the ACTUAL active school year
            // stored in the database — not today's calendar date. This prevents the
            // chart from sliding forward when the calendar year advances but no new
            // school year has been created yet (e.g. it's Oct 2026 but the active SY
            // is still 2025-2026).
            $activeSyNameForTrend = $activeSchoolYear?->name ?? SchoolYear::currentDepEdLabel();
            [$baseStartYear] = explode('-', $activeSyNameForTrend);
            $baseStartYear = (int) $baseStartYear;

            $enrollmentTrend = [];
            for ($i = 4; $i >= 0; $i--) {
                $syStart = $baseStartYear - $i;
                $syEnd   = $syStart + 1;
                $syLabel = "{$syStart}-{$syEnd}";

                // Count distinct students enrolled in this school year under this teacher.
                // student_year_enrollments is not overwritten on transition, so past years
                // always return the correct historical count.
                $count = $i === 0
                    ? $totalStudents
                    : StudentYearEnrollment::where('teacher_id', $teacherId)
                        ->where('school_year_name', $syLabel)
                        ->distinct('student_id')
                        ->count('student_id');

                $enrollmentTrend[] = [
                    'school_year' => $syLabel,
                    'start_year'  => $syStart,
                    'end_year'    => $syEnd,
                    'count'       => $count,
                    'date_range'  => "July 1, {$syStart} – June 30, {$syEnd}",
                    'is_current'  => ($i === 0),
                ];
            }

            // ── Student Activity & Activeness Percentage ────────────────────────
            $activeFromProgress = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->when($activeSyId, fn($q) => $q->where('school_year_id', $activeSyId))
                ->where(function ($q) {
                    $q->whereNotNull('last_accessed_at')
                      ->orWhere('lesson_completed', 1)
                      ->orWhereNotNull('quiz_score');
                })
                ->pluck('student_id');

            $activeFromGestures = GesturePerformance::whereIn('student_id', $studentIds)
                ->when($activeSyId, fn($q) => $q->where('school_year_id', $activeSyId))
                ->where('attempts', '>', 0)
                ->pluck('student_id');

            $activeStudentIds = $activeFromProgress->merge($activeFromGestures)->unique()->values();
            $activeStudentsCount = $activeStudentIds->count();

            $activenessPercent = $totalStudents > 0
                ? (int) round(($activeStudentsCount / $totalStudents) * 100)
                : 0;

            $practiceSessions = [
                'active_students' => $activeStudentsCount,
                'total_students'  => $totalStudents,
                'activity_pct'    => $activenessPercent,
                'subtitle'        => $totalStudents > 0
                    ? "{$activeStudentsCount} of {$totalStudents} Active Students"
                    : "No enrolled students",
            ];

            // ── Senya Insights (rich, data-driven) ──────────────────────────────
            $senyaInsights = (new SenyaInsightsService($teacherId, $activeSyId))->generate();

            // ── Legacy senyaTips (kept for JS rotator compatibility) ──────────────
            $senyaTips = array_map(fn($i) => $i['text'], $senyaInsights);

            // Fallback if no data-driven insights exist
            if (empty($senyaTips)) {
                if ($totalStudents > 0) {
                    $senyaTips[] = "You have <span class=\"font-black text-[#0d326b]\">{$totalStudents} student" . ($totalStudents === 1 ? '' : 's') . "</span> enrolled. Consistent practice yields the best learning retention!";
                }
                $senyaTips[] = "Short, 10-minute daily practice sessions boost student sign language memory by over 40%!";
                $senyaTips[] = "Encourage students to practice hand movements in front of visual feedback for faster gesture mastery.";
                $senyaTips[] = "Check the My Students section below to review individual student mastery levels and progress.";
                $senyaInsights = array_map(fn($t) => [
                    'icon' => 'lightbulb', 'color' => '#F59E0B', 'category' => 'Tip', 'text' => $t
                ], $senyaTips);
            }

        } else {
            // No teacher record — empty state
            $enrollmentTrend = [];
            for ($i = 4; $i >= 0; $i--) {
                $syStart = 2026 - $i;
                $syEnd   = $syStart + 1;
                $enrollmentTrend[] = [
                    'school_year' => "{$syStart}-{$syEnd}",
                    'start_year'  => $syStart,
                    'end_year'    => $syEnd,
                    'count'       => 0,
                    'date_range'  => "June 8, {$syStart} – April 8, {$syEnd}",
                    'is_current'  => ($i === 0),
                ];
            }
            $senyaInsights    = [];
            $senyaTips        = [];
            $practiceSessions = [
                'active_students' => 0,
                'total_students'  => 0,
                'total_attempts'  => 0,
                'activity_pct'    => 0,
                'subtitle'        => 'Active Monitoring',
            ];
            $monthlyActivity      = [];
            $syMonthOptions       = [];
            $selectedMonthParam   = Carbon::now()->format('Y-m');
            $syStartDate          = null;
            $syEndDate            = null;
            $activeSchoolYear     = null;
        }

        // Session-based tip rotation for the legacy single-tip rotator
        $senyaTips = array_values(array_unique($senyaTips));
        $tipCount  = count($senyaTips);
        if ($tipCount > 0) {
            $prevIndex = session('senya_tip_index', -1);
            $nextIndex = ($prevIndex + 1) % $tipCount;
            session(['senya_tip_index' => $nextIndex]);
            $selectedTip = $senyaTips[$nextIndex];
        } else {
            $selectedTip = 'Keep your students engaged with regular lesson assignments!';
        }

        // Circumference for SVG circle stroke: 2 * pi * r = 2 * 3.14159 * 64 ≈ 402
        $circleCircumference = 402;
        $circleDashOffset    = $classRate > 0
            ? round($circleCircumference * (1 - $classRate / 100))
            : $circleCircumference;

        return view('dashboard', compact(
            'greeting',
            'firstName',
            'user',
            'teacher',
            'calendarDate',
            'totalStudents',
            'newStudentsThisWeek',
            'activeToday',
            'activeTodayPercent',
            'avgAccuracy',
            'accuracyWeeklyChange',
            'totalLessons',
            'newLessonsThisWeek',
            'sparklineTotalStudents',
            'sparklineActive',
            'sparklineAccuracy',
            'sparklineLessons',
            'sparklineDates',
            'students',
            'lessons',
            'modules',
            'lessonMastery',
            'classRate',
            'circleDashOffset',
            'allStudents',
            'senyaTips',
            'selectedTip',
            'senyaInsights',
            'enrollmentTrend',
            'practiceSessions',
            'activeSchoolYear',
            'monthlyActivity',
            'syMonthOptions',
            'selectedMonthParam',
            'syStartDate',
            'syEndDate'
        ));
    }
}
