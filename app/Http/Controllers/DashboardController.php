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
        $averageQuizScore    = null;
        $quizResultCount     = 0;
        $activeThisWeekCount = 0;
        $activeThisWeekPercent = 0;
        $lessonCompletionRate = null;
        $completedLessonsCount = 0;
        $assignedLessonsCount = 0;
        $students         = collect();
        $lessons          = collect();
        $modules          = collect();
        $lessonMastery    = collect();
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
            $syEndDate   = null;
            if ($activeSchoolYear) {
                $syStartDate = $activeSchoolYear->start_date
                    ? Carbon::parse($activeSchoolYear->start_date)->startOfDay()
                    : null;
                $syEndDate = $activeSchoolYear->end_date
                    ? Carbon::parse($activeSchoolYear->end_date)->endOfDay()
                    : null;
            }
            // Match Analytics' July-to-June fallback when a school year has no saved date bounds.
            if ($activeSchoolYear && preg_match('/^(\d{4})-(\d{4})$/', $activeSchoolYear->name, $syYears)) {
                $syStartDate ??= Carbon::create((int) $syYears[1], 7, 1)->startOfDay();
                $syEndDate ??= Carbon::create((int) $syYears[2], 6, 30)->endOfDay();
            }
            $dashboardStartDate = $syStartDate ?? Carbon::now()->startOfYear();
            $dashboardEndDate = $syEndDate ?? Carbon::now()->endOfYear();

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

            // Weekly active engagement: enrolled students with any recorded activity in the last 7 days.
            $activityStart = Carbon::now()->subDays(6)->startOfDay();
            $activityEnd = Carbon::now()->endOfDay();
            $activeThisWeekCount = Student::whereIn('student_id', $studentIds)
                ->where(function ($q) use ($activityStart, $activityEnd) {
                    $q->whereBetween('last_activity_date', [$activityStart, $activityEnd])
                      ->orWhereExists(function ($sub) use ($activityStart, $activityEnd) {
                          $sub->select(DB::raw(1))
                              ->from('quiz_attempts')
                              ->whereColumn('quiz_attempts.student_id', 'students.student_id')
                              ->whereBetween('quiz_attempts.completed_at', [$activityStart, $activityEnd]);
                      })
                      ->orWhereExists(function ($sub) use ($activityStart, $activityEnd) {
                          $sub->select(DB::raw(1))
                              ->from('gesture_performances')
                              ->whereColumn('gesture_performances.student_id', 'students.student_id')
                              ->where(function ($gq) use ($activityStart, $activityEnd) {
                                  $gq->whereBetween('last_attempt_at', [$activityStart, $activityEnd])
                                     ->orWhereBetween('updated_at', [$activityStart, $activityEnd])
                                     ->orWhereBetween('created_at', [$activityStart, $activityEnd]);
                              });
                      })
                      ->orWhereExists(function ($sub) use ($activityStart, $activityEnd) {
                          $sub->select(DB::raw(1))
                              ->from('student_lesson_progress')
                              ->whereColumn('student_lesson_progress.student_id', 'students.student_id')
                              ->where(function ($pq) use ($activityStart, $activityEnd) {
                                  $pq->whereBetween('student_lesson_progress.last_accessed_at', [$activityStart, $activityEnd])
                                     ->orWhereBetween('student_lesson_progress.updated_at', [$activityStart, $activityEnd]);
                              });
                      })
                      ->orWhereExists(function ($sub) use ($activityStart, $activityEnd) {
                          $sub->select(DB::raw(1))
                              ->from('lesson_assignments')
                              ->whereColumn('lesson_assignments.student_id', 'students.student_id')
                              ->where(function ($aq) use ($activityStart, $activityEnd) {
                                  $aq->whereBetween('lesson_assignments.assigned_at', [$activityStart, $activityEnd])
                                     ->orWhereBetween('lesson_assignments.completed_at', [$activityStart, $activityEnd])
                                     ->orWhereBetween('lesson_assignments.updated_at', [$activityStart, $activityEnd]);
                              });
                      });
                })
                ->count();

            $activeThisWeekPercent = $totalStudents > 0
                ? round(($activeThisWeekCount / $totalStudents) * 100, 1)
                : 0;

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
            $assignmentTotals = DB::table('lesson_assignments')
                ->whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->where(function ($q) use ($dashboardStartDate, $dashboardEndDate) {
                    $q->whereBetween('assigned_at', [$dashboardStartDate, $dashboardEndDate])
                      ->orWhereBetween('completed_at', [$dashboardStartDate, $dashboardEndDate])
                      ->orWhereBetween('updated_at', [$dashboardStartDate, $dashboardEndDate]);
                })
                ->selectRaw('count(*) as total')
                ->selectRaw('sum(case when status = "completed" then 1 else 0 end) as completed')
                ->first();

            $assignedLessonsCount = (int) ($assignmentTotals->total ?? 0);
            $completedLessonsCount = (int) ($assignmentTotals->completed ?? 0);
            $lessonCompletionRate = $assignedLessonsCount > 0
                ? round(($completedLessonsCount / $assignedLessonsCount) * 100, 1)
                : null;

            $quizQuery = DB::table('quiz_attempts')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
                ->join('lessons', 'quizzes.lesson_id', '=', 'lessons.lesson_id')
                ->whereIn('quiz_attempts.student_id', $studentIds)
                ->where('quiz_attempts.status', 'completed')
                ->where('lessons.status', 'published')
                ->whereNull('lessons.deleted_at')
                ->whereBetween('quiz_attempts.completed_at', [$dashboardStartDate, $dashboardEndDate]);

            $averageQuizScore = (clone $quizQuery)->avg('quiz_attempts.percentage');
            $averageQuizScore = $averageQuizScore !== null ? round((float) $averageQuizScore, 2) : null;
            $quizResultCount = (clone $quizQuery)->count();

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

        return view('dashboard', compact(
            'greeting', 'firstName', 'user', 'teacher', 'calendarDate',
            'totalStudents', 'activeThisWeekCount', 'activeThisWeekPercent',
            'averageQuizScore', 'quizResultCount', 'lessonCompletionRate',
            'completedLessonsCount', 'assignedLessonsCount',
            'students', 'lessons', 'modules', 'lessonMastery', 'allStudents',
            'senyaTips', 'selectedTip', 'senyaInsights', 'enrollmentTrend',
            'practiceSessions', 'activeSchoolYear', 'monthlyActivity',
            'syMonthOptions', 'selectedMonthParam', 'syStartDate', 'syEndDate',
        ));
    }
}
