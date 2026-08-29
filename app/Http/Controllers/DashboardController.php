<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Student;
use App\Models\LessonAssignment;
use App\Models\StudentLessonProgress;
use App\Models\Gesture;
use App\Models\GesturePerformance;
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
        $totalStudents    = 0;
        $activeToday      = 0;
        $lessonsCompleted = 0;
        $avgAccuracy      = 0;
        $newStudentsThisWeek      = 0;
        $activeTodayPercent       = 0;
        $accuracyWeeklyChange     = 0;
        $lessonsCompletedThisWeek = 0;
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
            $teacherId  = $teacher->id;
            $studentIds = Student::where('teacher_id', $teacherId)->where('status', 'active')->pluck('student_id');
            $lessonIds  = Lesson::where('teacher_id', $teacherId)->where('status', 'published')->whereNull('deleted_at')->pluck('lesson_id');

            // ── Stat Cards ───────────────────────────────────────────────────────
            $totalStudents = $studentIds->count();

            $newStudentsThisWeek = Student::where('teacher_id', $teacherId)
                ->where('status', 'active')
                ->where('created_at', '>=', Carbon::now()->subWeek())
                ->count();

            $activeToday = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->whereDate('last_accessed_at', Carbon::today())
                ->distinct('student_id')
                ->count('student_id');

            $activeTodayPercent = $totalStudents > 0
                ? round(($activeToday / $totalStudents) * 100)
                : 0;

            $lessonsCompleted = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->where('lesson_completed', 1)
                ->count();

            $lessonsCompletedThisWeek = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->where('lesson_completed', 1)
                ->where('updated_at', '>=', Carbon::now()->subWeek())
                ->count();

            // ── Sparklines (last 7 days, ending today) ───────────────────────────
            for ($i = 6; $i >= 0; $i--) {
                $day = Carbon::today()->subDays($i);

                $sparklineTotalStudents[] = Student::where('teacher_id', $teacherId)
                    ->where('status', 'active')
                    ->whereDate('created_at', '<=', $day)
                    ->count();

                $sparklineActive[] = StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->whereIn('lesson_id', $lessonIds)
                    ->whereDate('last_accessed_at', $day)
                    ->distinct('student_id')
                    ->count('student_id');

                $dayAccuracy = StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->whereIn('lesson_id', $lessonIds)
                    ->whereNotNull('quiz_score')
                    ->whereDate('updated_at', $day)
                    ->avg('quiz_score');
                $sparklineAccuracy[] = $dayAccuracy ? (int) round($dayAccuracy) : 0;

                $sparklineLessons[] = StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->whereIn('lesson_id', $lessonIds)
                    ->where('lesson_completed', 1)
                    ->whereDate('updated_at', $day)
                    ->count();
            }

            // ── Your Modules (grouped, for dashboard folder cards) ───────────────
            $modules = Module::where('teacher_id', $teacherId)
                ->with(['lessons' => function ($q) {
                    $q->where('status', 'published')->whereNull('deleted_at')->orderBy('module_order');
                }])
                ->orderBy('module_order')
                ->get()
                ->map(function ($module) use ($studentIds) {
                    $lessonIds = $module->lessons->pluck('lesson_id');

                    // Distinct students who are BOTH assigned to a lesson in this module
                    // AND belong to this teacher
                    $assignedStudentIds = LessonAssignment::whereIn('lesson_id', $lessonIds)
                        ->whereIn('student_id', $studentIds)
                        ->distinct('student_id')
                        ->pluck('student_id');

                    $enrolled = $assignedStudentIds->count();

                    $completed = LessonAssignment::whereIn('lesson_id', $lessonIds)
                        ->whereIn('student_id', $studentIds)
                        ->where('status', 'completed')
                        ->distinct('student_id')
                        ->count('student_id');

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
                ->map(function ($lesson) use ($studentIds) {
                    // Count students who have at least one progress entry for this lesson
                    $enrolled = StudentLessonProgress::whereIn('student_id', $studentIds)
                        ->where('lesson_id', $lesson->lesson_id)
                        ->distinct('student_id')
                        ->count('student_id');

                    // Completion %: of enrolled students, how many finished
                    $completed = StudentLessonProgress::whereIn('student_id', $studentIds)
                        ->where('lesson_id', $lesson->lesson_id)
                        ->where('lesson_completed', 1)
                        ->count();

                    $lesson->enrolled   = $enrolled;
                    $lesson->completion = $enrolled > 0 ? round(($completed / $enrolled) * 100) : 0;

                    // Top 3 enrolled student names for avatar stack
                    $lesson->topStudents = Student::whereIn('student_id',
                        StudentLessonProgress::where('lesson_id', $lesson->lesson_id)
                            ->whereIn('student_id', $studentIds)
                            ->pluck('student_id')
                    )->take(3)->get();

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
                ->map(function ($lesson) use ($studentIds) {
                    $enrolled  = StudentLessonProgress::whereIn('student_id', $studentIds)
                        ->where('lesson_id', $lesson->lesson_id)
                        ->distinct('student_id')
                        ->count('student_id');

                    $completed = StudentLessonProgress::whereIn('student_id', $studentIds)
                        ->where('lesson_id', $lesson->lesson_id)
                        ->where('lesson_completed', 1)
                        ->count();

                    $lesson->masteryPct = $enrolled > 0 ? round(($completed / $enrolled) * 100) : 0;
                    return $lesson;
                });

            // ── Overall Class Rate ────────────────────────────────────────────────
            $totalProgress  = StudentLessonProgress::whereIn('student_id', $studentIds)->whereIn('lesson_id', $lessonIds)->count();
            $totalCompleted = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->where('lesson_completed', 1)
                ->count();
            $classRate = $totalProgress > 0 ? round(($totalCompleted / $totalProgress) * 100) : 0;

            // ── Average Accuracy ──────────────────────────────────────────────────
            $avgScore = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->whereNotNull('quiz_score')
                ->avg('quiz_score');
            $avgAccuracy = $avgScore ? (int) round($avgScore) : 0;

            $avgThisWeek = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->whereNotNull('quiz_score')
                ->where('updated_at', '>=', Carbon::now()->subWeek())
                ->avg('quiz_score');

            $avgLastWeek = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->whereNotNull('quiz_score')
                ->whereBetween('updated_at', [Carbon::now()->subWeeks(2), Carbon::now()->subWeek()])
                ->avg('quiz_score');

            if ($avgThisWeek !== null && $avgLastWeek !== null) {
                $accuracyWeeklyChange = (int) round($avgThisWeek - $avgLastWeek);
            } elseif ($avgThisWeek !== null) {
                $accuracyWeeklyChange = (int) round($avgThisWeek);
            }

            // ── Student Performance (5 most recent, with avg quiz score as proxy) ─
            $students = Student::where('teacher_id', $teacherId)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($student) {
                    $progressRecords = $student->progress;
                    $total     = $progressRecords->count();
                    $completed = $progressRecords->where('lesson_completed', 1)->count();
                    $student->performancePct = $total > 0 ? round(($completed / $total) * 100) : 0;
                    return $student;
                });

        // ── My Students (sidebar list) ───────────────────────────────────────
            $allStudents = Student::where('teacher_id', $teacherId)
                ->where('status', 'active')
                ->orderBy('first_name')
                ->get(['student_id', 'first_name', 'last_name', 'level', 'grade_level', 'fsl_mastery_level', 'total_xp']);

            // ── Enrollment Trend per School Year (5 consecutive school years: June 8 to April 8) ──
            $now = Carbon::now();
            $currentYear = (int) $now->year;
            $syCutoff = Carbon::create($currentYear, 6, 8, 0, 0, 0);
            $baseStartYear = $now->lt($syCutoff) ? ($currentYear - 1) : $currentYear;

            $enrollmentTrend = [];
            for ($i = 4; $i >= 0; $i--) {
                $syStart = $baseStartYear - $i;
                $syEnd   = $syStart + 1;
                $syLabel = "{$syStart}-{$syEnd}";
                $startDate = Carbon::create($syStart, 6, 8, 0, 0, 0);
                $endDate   = Carbon::create($syEnd, 4, 8, 23, 59, 59);

                $count = Student::where('teacher_id', $teacherId)
                    ->where(function ($q) use ($syLabel, $startDate, $endDate) {
                        $q->where('school_year', $syLabel)
                          ->orWhere(function ($sq) use ($startDate, $endDate) {
                              $sq->where(function ($q2) {
                                  $q2->whereNull('school_year')->orWhere('school_year', '');
                              })->whereBetween('created_at', [$startDate, $endDate]);
                          });
                    })
                    ->count();

                $enrollmentTrend[] = [
                    'school_year' => $syLabel,
                    'start_year'  => $syStart,
                    'end_year'    => $syEnd,
                    'count'       => $count,
                    'date_range'  => "June 8, {$syStart} – April 8, {$syEnd}",
                    'is_current'  => ($i === 0),
                ];
            }

            // ── Senya Insights (rich, data-driven) ──────────────────────────────
            $senyaInsights = (new SenyaInsightsService($teacherId))->generate();

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
            $senyaInsights = [];
            $senyaTips     = [];
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
            'lessonsCompleted',
            'lessonsCompletedThisWeek',
            'sparklineTotalStudents',
            'sparklineActive',
            'sparklineAccuracy',
            'sparklineLessons',
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
            'enrollmentTrend'
        ));
    }
}
