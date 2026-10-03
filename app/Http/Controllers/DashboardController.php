<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Student;
use App\Models\LessonAssignment;
use App\Models\StudentLessonProgress;
use App\Models\Gesture;
use App\Models\GesturePerformance;
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

            $availableSchoolYears = \App\Models\SchoolYear::where('school_id', $schoolId ?: null)
                ->orderByDesc('name')
                ->get();

            $activeSchoolYear = $availableSchoolYears->firstWhere('status', 'active')
                ?? $availableSchoolYears->first();

            $selectedYearName = $request->query('school_year');
            if (!$selectedYearName && $activeSchoolYear) {
                $selectedYearName = $activeSchoolYear->name;
            }

            $selectedSchoolYear = ($selectedYearName && $selectedYearName !== 'all')
                ? $availableSchoolYears->firstWhere('name', $selectedYearName)
                : null;

            $isArchivedView = $selectedSchoolYear && $selectedSchoolYear->status === 'archived';

            // Resolve the school_year_id to filter progress/assignment records.
            // When null (all years), no school_year_id filter is applied.
            $selectedSyId = $selectedSchoolYear?->id;

            // ── Student query — same logic as Student Management tab ─────────────
            // Active year  : direct school_year string match + status=active
            // Archived year: direct match OR EXISTS on lesson_assignments/progress
            //                (student's school_year column may have moved on)
            // All years    : no year or status filter
            $teacherLessonIds = Lesson::where('teacher_id', $teacherId)
                ->whereNull('deleted_at')
                ->pluck('lesson_id');

            $studentQuery = Student::where('teacher_id', $teacherId);

            if ($selectedSchoolYear) {
                if ($isArchivedView) {
                    // Archived: find by direct string OR historical assignment records
                    $syId = $selectedSchoolYear->id;
                    $studentQuery->where(function ($q) use ($selectedSchoolYear, $syId, $teacherLessonIds) {
                        $q->where('school_year', $selectedSchoolYear->name)
                          ->orWhereExists(function ($sub) use ($syId, $teacherLessonIds) {
                              $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                                  ->from('lesson_assignments')
                                  ->whereColumn('lesson_assignments.student_id', 'students.student_id')
                                  ->where('lesson_assignments.school_year_id', $syId)
                                  ->whereIn('lesson_assignments.lesson_id', $teacherLessonIds);
                          })
                          ->orWhereExists(function ($sub) use ($syId, $teacherLessonIds) {
                              $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                                  ->from('student_lesson_progress')
                                  ->whereColumn('student_lesson_progress.student_id', 'students.student_id')
                                  ->where('student_lesson_progress.school_year_id', $syId)
                                  ->whereIn('student_lesson_progress.lesson_id', $teacherLessonIds);
                          });
                    });
                    // Archived view: show all statuses (enrolled + unenrolled)
                } else {
                    // Active year: direct match + enrolled only
                    $studentQuery->where('school_year', $selectedSchoolYear->name)
                                 ->where('status', 'active');
                }
            } elseif ($selectedYearName !== 'all') {
                $studentQuery->where('status', 'active');
            }

            $studentIds = $studentQuery->pluck('student_id');
            $lessonIds  = Lesson::where('teacher_id', $teacherId)->where('status', 'published')->whereNull('deleted_at')->pluck('lesson_id');

            // ── Stat Cards ───────────────────────────────────────────────────────
            $totalStudents = $studentIds->count();

            $newStudentsQuery = Student::where('teacher_id', $teacherId)
                ->where('created_at', '>=', Carbon::now()->subWeek());
            if ($selectedSchoolYear) {
                $newStudentsQuery->where('school_year', $selectedSchoolYear->name);
            }
            $newStudentsThisWeek = $newStudentsQuery->count();

            $activeToday = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->whereDate('last_accessed_at', Carbon::today())
                ->distinct('student_id')
                ->count('student_id');

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
                    ->when($selectedSchoolYear, function ($q) use ($selectedSchoolYear, $isArchivedView, $teacherLessonIds) {
                        if ($isArchivedView) {
                            $syId = $selectedSchoolYear->id;
                            $q->where(function ($q2) use ($selectedSchoolYear, $syId, $teacherLessonIds) {
                                $q2->where('school_year', $selectedSchoolYear->name)
                                   ->orWhereExists(fn($s) => $s->select(\Illuminate\Support\Facades\DB::raw(1))
                                       ->from('lesson_assignments')
                                       ->whereColumn('lesson_assignments.student_id', 'students.student_id')
                                       ->where('lesson_assignments.school_year_id', $syId)
                                       ->whereIn('lesson_assignments.lesson_id', $teacherLessonIds));
                            });
                        } else {
                            $q->where('school_year', $selectedSchoolYear->name)
                              ->where('status', 'active');
                        }
                    })
                    ->when(!$selectedSchoolYear, fn($q) => $q->where('status', 'active'))
                    ->whereDate('created_at', '<=', $day)
                    ->count();

                $sparklineActive[] = StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->whereIn('lesson_id', $lessonIds)
                    ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
                    ->whereDate('last_accessed_at', $day)
                    ->distinct('student_id')
                    ->count('student_id');

                $dayAccuracy = StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->whereIn('lesson_id', $lessonIds)
                    ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
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
                ->map(function ($module) use ($studentIds, $selectedSyId) {
                    $lessonIds = $module->lessons->pluck('lesson_id');

                    $assignedStudentIds = LessonAssignment::whereIn('lesson_id', $lessonIds)
                        ->whereIn('student_id', $studentIds)
                        ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
                        ->distinct('student_id')
                        ->pluck('student_id');

                    $enrolled = $assignedStudentIds->count();

                    $completed = LessonAssignment::whereIn('lesson_id', $lessonIds)
                        ->whereIn('student_id', $studentIds)
                        ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
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
                ->map(function ($lesson) use ($studentIds, $selectedSyId) {
                    $enrolled = StudentLessonProgress::whereIn('student_id', $studentIds)
                        ->where('lesson_id', $lesson->lesson_id)
                        ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
                        ->distinct('student_id')
                        ->count('student_id');

                    $completed = StudentLessonProgress::whereIn('student_id', $studentIds)
                        ->where('lesson_id', $lesson->lesson_id)
                        ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
                        ->where('lesson_completed', 1)
                        ->count();

                    $lesson->enrolled   = $enrolled;
                    $lesson->completion = $enrolled > 0 ? round(($completed / $enrolled) * 100) : 0;

                    $lesson->topStudents = Student::whereIn('student_id',
                        StudentLessonProgress::where('lesson_id', $lesson->lesson_id)
                            ->whereIn('student_id', $studentIds)
                            ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
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
                ->map(function ($lesson) use ($studentIds, $selectedSyId) {
                    $enrolled  = StudentLessonProgress::whereIn('student_id', $studentIds)
                        ->where('lesson_id', $lesson->lesson_id)
                        ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
                        ->distinct('student_id')
                        ->count('student_id');

                    $completed = StudentLessonProgress::whereIn('student_id', $studentIds)
                        ->where('lesson_id', $lesson->lesson_id)
                        ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
                        ->where('lesson_completed', 1)
                        ->count();

                    $lesson->masteryPct = $enrolled > 0 ? round(($completed / $enrolled) * 100) : 0;
                    return $lesson;
                });

            // ── Overall Class Rate ────────────────────────────────────────────────
            $totalProgress  = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
                ->count();
            $totalCompleted = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
                ->where('lesson_completed', 1)
                ->count();
            $classRate = $totalProgress > 0 ? round(($totalCompleted / $totalProgress) * 100) : 0;

            // ── Average Accuracy ──────────────────────────────────────────────────
            $avgScore = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
                ->whereNotNull('quiz_score')
                ->avg('quiz_score');
            $avgAccuracy = $avgScore ? (int) round($avgScore) : 0;

            $avgThisWeek = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
                ->whereNotNull('quiz_score')
                ->where('updated_at', '>=', Carbon::now()->subWeek())
                ->avg('quiz_score');

            $avgLastWeek = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
                ->whereNotNull('quiz_score')
                ->whereBetween('updated_at', [Carbon::now()->subWeeks(2), Carbon::now()->subWeek()])
                ->avg('quiz_score');

            if ($avgThisWeek !== null && $avgLastWeek !== null) {
                $accuracyWeeklyChange = (int) round($avgThisWeek - $avgLastWeek);
            } elseif ($avgThisWeek !== null) {
                $accuracyWeeklyChange = (int) round($avgThisWeek);
            }

            // ── Student Performance (5 most recent, with avg quiz score as proxy) ─
            $recentStudentQuery = Student::where('teacher_id', $teacherId);
            if ($selectedSchoolYear) {
                if ($isArchivedView) {
                    $syId = $selectedSchoolYear->id;
                    $recentStudentQuery->where(function ($q) use ($selectedSchoolYear, $syId, $teacherLessonIds) {
                        $q->where('school_year', $selectedSchoolYear->name)
                          ->orWhereExists(fn($s) => $s->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('lesson_assignments')
                              ->whereColumn('lesson_assignments.student_id', 'students.student_id')
                              ->where('lesson_assignments.school_year_id', $syId)
                              ->whereIn('lesson_assignments.lesson_id', $teacherLessonIds));
                    });
                } else {
                    $recentStudentQuery->where('school_year', $selectedSchoolYear->name)
                                       ->where('status', 'active');
                }
            } else {
                $recentStudentQuery->where('status', 'active');
            }
            $students = $recentStudentQuery
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($student) use ($selectedSyId) {
                    // Scope progress to the selected school year so the % reflects
                    // only what the student did in that year, not all-time.
                    $progressQuery = $student->progress();
                    if ($selectedSyId) {
                        $progressQuery = $progressQuery->where('school_year_id', $selectedSyId);
                    }
                    $progressRecords = $progressQuery->get();
                    $total     = $progressRecords->count();
                    $completed = $progressRecords->where('lesson_completed', 1)->count();
                    $student->performancePct = $total > 0 ? round(($completed / $total) * 100) : 0;
                    return $student;
                });

        // ── My Students (sidebar list) ───────────────────────────────────────
            $allStudentsQuery = Student::where('teacher_id', $teacherId);
            if ($selectedSchoolYear) {
                if ($isArchivedView) {
                    $syId = $selectedSchoolYear->id;
                    $allStudentsQuery->where(function ($q) use ($selectedSchoolYear, $syId, $teacherLessonIds) {
                        $q->where('school_year', $selectedSchoolYear->name)
                          ->orWhereExists(fn($s) => $s->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('lesson_assignments')
                              ->whereColumn('lesson_assignments.student_id', 'students.student_id')
                              ->where('lesson_assignments.school_year_id', $syId)
                              ->whereIn('lesson_assignments.lesson_id', $teacherLessonIds));
                    });
                } else {
                    $allStudentsQuery->where('school_year', $selectedSchoolYear->name)
                                     ->where('status', 'active');
                }
            } else {
                $allStudentsQuery->where('status', 'active');
            }
            $allStudents = $allStudentsQuery->orderBy('first_name')->get();

            // ── Enrollment Trend per School Year ─────────────────────────────────
            // Shows how many students were enrolled under this teacher each year.
            // Logic:
            //   - For each year label, count students whose school_year = that label.
            //   - For past/archived years: count all (they were enrolled then;
            //     their status was changed to inactive when the year transitioned).
            //   - For the current active year: count only active (actually enrolled now).
            // This prevents students who were moved to a new year with inactive status
            // from inflating the count for the new year before they are re-enrolled.
            $now = Carbon::now();
            $currentYear = (int) $now->year;
            $syCutoff = Carbon::create($currentYear, 6, 8, 0, 0, 0);
            $baseStartYear = $now->lt($syCutoff) ? ($currentYear - 1) : $currentYear;

            $activeSyNameForTrend = $activeSchoolYear?->name ?? SchoolYear::currentDepEdLabel();

            $enrollmentTrend = [];
            for ($i = 4; $i >= 0; $i--) {
                $syStart = $baseStartYear - $i;
                $syEnd   = $syStart + 1;
                $syLabel = "{$syStart}-{$syEnd}";
                $startDate = Carbon::create($syStart, 6, 8, 0, 0, 0);
                $endDate   = Carbon::create($syEnd, 4, 8, 23, 59, 59);

                $isCurrentSy = ($syLabel === $activeSyNameForTrend);

                // For the active/current year: count only active (enrolled) students
                // using the direct students.school_year column match.
                //
                // For past years: students.school_year has been updated to the newer
                // year after each transition — so we can't rely on it.
                // Instead, count distinct students who had a lesson_assignment for
                // this teacher's lessons in this school_year_id. This is the only
                // reliable historical record of "was this student enrolled this year".
                $syRecord = $availableSchoolYears->firstWhere('name', $syLabel);

                if ($isCurrentSy) {
                    // Active year: direct column match + enrolled only
                    $count = Student::where('teacher_id', $teacherId)
                        ->where('school_year', $syLabel)
                        ->where('status', 'active')
                        ->count();
                } elseif ($syRecord) {
                    // Past year with a school_years record: count via lesson_assignments
                    $count = DB::table('students')
                        ->where('students.teacher_id', $teacherId)
                        ->whereExists(function ($sub) use ($syRecord, $teacherLessonIds) {
                            $sub->select(DB::raw(1))
                                ->from('lesson_assignments')
                                ->whereColumn('lesson_assignments.student_id', 'students.student_id')
                                ->where('lesson_assignments.school_year_id', $syRecord->id)
                                ->whereIn('lesson_assignments.lesson_id', $teacherLessonIds);
                        })
                        ->count();

                    // Also include students whose school_year still matches this label
                    // (enrolled but never assigned a lesson yet in that year)
                    $directCount = Student::where('teacher_id', $teacherId)
                        ->where('school_year', $syLabel)
                        ->whereNotExists(function ($sub) use ($syRecord, $teacherLessonIds) {
                            $sub->select(DB::raw(1))
                                ->from('lesson_assignments')
                                ->whereColumn('lesson_assignments.student_id', 'students.student_id')
                                ->where('lesson_assignments.school_year_id', $syRecord->id)
                                ->whereIn('lesson_assignments.lesson_id', $teacherLessonIds);
                        })
                        ->count();

                    $count += $directCount;
                } else {
                    // No school_years record — fall back to creation date range
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
                }

                $enrollmentTrend[] = [
                    'school_year' => $syLabel,
                    'start_year'  => $syStart,
                    'end_year'    => $syEnd,
                    'count'       => $count,
                    'date_range'  => "June 8, {$syStart} – April 8, {$syEnd}",
                    'is_current'  => ($i === 0),
                ];
            }

            // ── Student Activity & Activeness Percentage ────────────────────────
            $activeFromProgress = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
                ->where(function ($q) {
                    $q->whereNotNull('last_accessed_at')
                      ->orWhere('lesson_completed', 1)
                      ->orWhereNotNull('quiz_score');
                })
                ->pluck('student_id');

            $activeFromGestures = GesturePerformance::whereIn('student_id', $studentIds)
                ->when($selectedSyId, fn($q) => $q->where('school_year_id', $selectedSyId))
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
            $senyaInsights = (new SenyaInsightsService($teacherId, $selectedSyId))->generate();

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
            'availableSchoolYears',
            'activeSchoolYear',
            'selectedSchoolYear',
            'isArchivedView'
        ));
    }
}
