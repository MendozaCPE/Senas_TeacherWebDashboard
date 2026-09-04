<?php

namespace App\Http\Controllers;

use App\Models\HelpRequest;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentLessonProgress;
use App\Models\CheckpointExam;
use App\Models\CheckpointExamAssignment;
use App\Models\CheckpointExamAttempt;
use App\Services\TcPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Report page — one row per STUDENT, with a per-lesson breakdown
     * attached to each row so the modal can render it without extra queries.
     */
    public function index(Request $request)
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        $students        = collect();
        $lessons         = collect();
        $checkpointExams = collect();
        $studentReports  = collect();

        if ($teacher) {
            $teacherId  = $teacher->id;
            $studentIds = Student::where('teacher_id', $teacherId)->where('status', 'active')->pluck('student_id');

            $students = Student::where('teacher_id', $teacherId)
                ->where('status', 'active')
                ->orderBy('first_name')
                ->get();

            $modules          = Module::where('teacher_id', $teacherId)->orderBy('module_order')->get();
            $teacherModuleIds = $modules->pluck('module_id');

            $lessons = Lesson::where('teacher_id', $teacherId)
                ->where('status', 'published')
                ->whereNull('deleted_at')
                ->where(function($q) use ($teacherModuleIds) {
                    $q->whereIn('module_id', $teacherModuleIds)
                      ->orWhereNull('module_id');
                })
                ->with(['module', 'quiz'])
                ->orderBy('module_order')
                ->get();

            $checkpointExams = CheckpointExam::where('teacher_id', $teacherId)
                ->where('status', 'published')
                ->where(function($q) use ($teacherModuleIds) {
                    $q->whereIn('module_id', $teacherModuleIds)
                      ->orWhereNull('module_id');
                })
                ->with(['module', 'questions'])
                ->orderBy('module_id')
                ->orderBy('title')
                ->get();

            // Read filters from session (set via POST, never from URL)
            $filters       = session('reports_filters', []);
            $filterStudent = $filters['student_id'] ?? 'all';
            $filterLesson  = $filters['lesson_id']  ?? 'all';

            $lessonIds = $lessons->pluck('lesson_id');

            $query = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)  // ← Only THIS teacher's lessons
                ->with(['student', 'lesson']);

            if ($filterStudent !== 'all') {
                $query->where('student_id', (int) $filterStudent);
            }
            if ($filterLesson !== 'all') {
                if (str_starts_with((string)$filterLesson, 'exam_')) {
                    $query->whereRaw('1 = 0');
                } else {
                    $targetLessonId = (int) str_replace('lesson_', '', $filterLesson);
                    $query->where('lesson_id', $targetLessonId);
                }
            }

            $allRows = $query->orderBy('last_accessed_at', 'desc')->get();

            // Fetch Gesture Performance per student strictly from gesture_performances
            $gQuery = DB::table('gesture_performances as gp')
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
                );

            if ($filterStudent !== 'all') {
                $gQuery->where('gp.student_id', (int) $filterStudent);
            }

            $studentGesturePerformance = $gQuery
                ->orderBy('s.first_name')
                ->orderBy('s.last_name')
                ->get()
                ->map(function ($row) {
                    $attempts = (int) $row->attempts;
                    $success  = (int) $row->successful_attempts;
                    $accuracy = $attempts > 0 ? round(($success / $attempts) * 100, 1) : 0;
                    return [
                        'student_id'          => $row->student_id,
                        'studentName'         => trim($row->first_name . ' ' . $row->last_name),
                        'gestureName'         => $row->g_display_name ?: $row->g_name,
                        'attempts'            => $attempts,
                        'successfulAttempts'  => $success,
                        'wrongAttempts'       => (int) $row->wrong_attempts,
                        'accuracy'            => $accuracy,
                        'masteryLevel'        => $row->mastery_level ?? 'needs_practice',
                        'isMastered'          => (bool) $row->is_mastered,
                        'lastAttemptAt'       => $row->last_attempt_at ? Carbon::parse($row->last_attempt_at)->format('M d, Y') : '—',
                    ];
                });

            $gestureByStudent = $studentGesturePerformance->groupBy('student_id');

            $studentsToReport = $students;
            if ($filterStudent !== 'all') {
                $studentsToReport = $students->where('student_id', (int) $filterStudent);
            }

            // Determine which lessons and checkpoint exams to show per student
            if ($filterLesson !== 'all') {
                if (str_starts_with((string)$filterLesson, 'exam_')) {
                    $targetExamId = (int) str_replace('exam_', '', $filterLesson);
                    $lessonsToShow = collect();
                    $checkpointExamsToShow = $checkpointExams->where('exam_id', $targetExamId);
                } else {
                    $targetLessonId = (int) str_replace('lesson_', '', $filterLesson);
                    $lessonsToShow = $lessons->where('lesson_id', $targetLessonId);
                    $checkpointExamsToShow = collect();
                }
            } else {
                $lessonsToShow = $lessons;
                $checkpointExamsToShow = $checkpointExams;
            }

            $groupedRows = $allRows->groupBy('student_id');

            $checkpointAssignments = DB::table('checkpoint_exam_assignments')
                ->whereIn('student_id', $studentIds)
                ->whereIn('exam_id', $checkpointExams->pluck('exam_id'))
                ->get()
                ->groupBy('student_id');

            $checkpointAttempts = DB::table('checkpoint_exam_attempts')
                ->whereIn('student_id', $studentIds)
                ->whereIn('exam_id', $checkpointExams->pluck('exam_id'))
                ->whereIn('status', ['completed', 'failed'])
                ->get()
                ->groupBy('student_id');

            $totalSteps = 7; // avg lesson steps, used for step-progress %

            // Map over all selected active students to build the summary rows.
            $studentReports = $studentsToReport
                ->map(function ($student) use ($groupedRows, $lessonsToShow, $checkpointExamsToShow, $totalSteps, $gestureByStudent, $lessons, $checkpointAssignments, $checkpointAttempts) {
                    $rows        = $groupedRows->get($student->student_id) ?? collect();
                    $progressMap = $rows->keyBy(fn($r) => $r->lesson_id);

                    // Build one entry per lesson (started OR not started)
                    $lessonBreakdown = $lessonsToShow->map(function ($lesson) use ($progressMap, $totalSteps) {
                        $row     = $progressMap->get($lesson->lesson_id);
                        $started = $row !== null;
                        $stepPct = ($started && $totalSteps > 0)
                            ? min(100, round(($row->current_step / $totalSteps) * 100))
                            : 0;

                        return [
                            'lessonTitle'   => $lesson->title,
                            'difficulty'    => $lesson->difficulty ?? '—',
                            'lessonType'    => $lesson->lesson_type ?? '',
                            'is_exam'       => false,
                            'moduleTitle'   => $lesson->module ? $lesson->module->title : 'Unassigned Lessons',
                            'module_id'     => $lesson->module_id,
                            'ai_generated'  => (bool) $lesson->ai_generated,
                            'started'       => $started,
                            'stepPct'       => $stepPct,
                            'completed'     => $started && (bool) $row->lesson_completed,
                            'quizCompleted' => $started && (bool) $row->quiz_completed,
                            'quizScore'     => $started ? $row->quiz_score : null,
                            'lastAccessed'  => ($started && $row->last_accessed_at)
                                ? Carbon::parse($row->last_accessed_at)->diffForHumans()
                                : '—',
                        ];
                    })->values();

                    // Build one entry per checkpoint exam (started OR not started)
                    $stAssignments = $checkpointAssignments->get($student->student_id) ?? collect();
                    $stAttempts    = $checkpointAttempts->get($student->student_id) ?? collect();

                    $checkpointBreakdown = $checkpointExamsToShow->map(function ($exam) use ($stAssignments, $stAttempts) {
                        $assign        = $stAssignments->firstWhere('exam_id', $exam->exam_id);
                        $examAttempts  = $stAttempts->where('exam_id', $exam->exam_id);
                        $bestAttempt   = $examAttempts->sortByDesc('percentage')->first();
                        $latestAttempt = $examAttempts->sortByDesc('completed_at')->first();

                        $started   = ($assign !== null && $assign->status !== 'pending') || $examAttempts->isNotEmpty();
                        $completed = ($assign !== null && $assign->status === 'completed') || ($bestAttempt !== null && $bestAttempt->percentage >= ($exam->passing_score ?? 60));
                        $failed    = ($assign !== null && $assign->status === 'failed') || ($bestAttempt !== null && !$completed);

                        $score = null;
                        if ($bestAttempt) {
                            $score = round($bestAttempt->percentage, 1);
                        } elseif ($assign && $assign->score !== null) {
                            $score = round($assign->score, 1);
                        }

                        $lastAccessed = null;
                        if ($latestAttempt && $latestAttempt->completed_at) {
                            $lastAccessed = Carbon::parse($latestAttempt->completed_at)->diffForHumans();
                        } elseif ($assign && $assign->completed_at) {
                            $lastAccessed = Carbon::parse($assign->completed_at)->diffForHumans();
                        } elseif ($assign && $assign->updated_at) {
                            $lastAccessed = Carbon::parse($assign->updated_at)->diffForHumans();
                        }

                        return [
                            'lessonTitle'    => $exam->title,
                            'difficulty'     => 'Exam',
                            'lessonType'     => 'checkpoint_exam',
                            'is_exam'        => true,
                            'exam_id'        => $exam->exam_id,
                            'moduleTitle'    => $exam->module ? $exam->module->title : 'Unassigned Checkpoint Exams',
                            'module_id'      => $exam->module_id,
                            'ai_generated'   => false,
                            'started'        => $started,
                            'stepPct'        => $completed ? 100 : ($started ? 50 : 0),
                            'completed'      => $completed,
                            'failed'         => $failed,
                            'quizCompleted'  => $bestAttempt !== null || ($assign !== null && $assign->score !== null),
                            'quizScore'      => $score,
                            'attemptCount'   => $examAttempts->count(),
                            'passingScore'   => $exam->passing_score ?? 60,
                            'totalPoints'    => $exam->total_points,
                            'lastAccessed'   => $lastAccessed ?: '—',
                        ];
                    })->values();

                    $allContentBreakdown = $lessonBreakdown->concat($checkpointBreakdown);

                    $totalLessons     = $lessonsToShow->count() + $checkpointExamsToShow->count();
                    $completedLessons = $rows->where('lesson_completed', 1)->count() + $checkpointBreakdown->where('completed', true)->count();
                    
                    // Count quizzes taken and quizzes passed
                    $completedQuizRows = $rows->where('quiz_completed', 1);
                    $quizzesTaken      = $completedQuizRows->count() + $checkpointBreakdown->where('quizCompleted', true)->count();
                    $quizzesPassed     = 0;
                    foreach ($completedQuizRows as $r) {
                        $l = $lessons->firstWhere('lesson_id', $r->lesson_id);
                        $q = $l ? $l->quiz : null;
                        $score = (float) $r->quiz_score;
                        $passingScore = $q ? (float) $q->passing_score : 60;
                        $totalPoints  = $q ? (float) $q->total_points : 5;
                        
                        $isPass = false;
                        if ($passingScore > 10) {
                            $isPass = ($score > 10) ? ($score >= $passingScore) : (($totalPoints > 0 ? ($score / $totalPoints) * 100 : 0) >= $passingScore);
                        } else {
                            $isPass = ($score <= 10) ? ($score >= $passingScore) : ($score >= ($totalPoints > 0 ? ($passingScore / $totalPoints) * 100 : 60));
                        }
                        if ($isPass) {
                            $quizzesPassed++;
                        }
                    }
                    $quizzesPassed += $checkpointBreakdown->where('completed', true)->count();

                    $quizPassRate     = $quizzesTaken > 0 ? round(($quizzesPassed / $quizzesTaken) * 100, 1) : 0;
                    
                    $quizScoresSum = $completedQuizRows->sum('quiz_score');
                    $examScoresSum = $checkpointBreakdown->where('quizCompleted', true)->whereNotNull('quizScore')->sum('quizScore');
                    $totalScoredCount = $completedQuizRows->count() + $checkpointBreakdown->where('quizCompleted', true)->whereNotNull('quizScore')->count();
                    $avgScore = $totalScoredCount > 0 ? round(($quizScoresSum + $examScoresSum) / $totalScoredCount, 1) : 0;

                    $overallPct       = $totalLessons > 0
                        ? round(($completedLessons / $totalLessons) * 100)
                        : 0;

                    $lastActiveRaw    = $rows->isNotEmpty()
                        ? $rows->sortByDesc('last_accessed_at')->first()->last_accessed_at
                        : null;

                    // Compute student's gesture performance strictly from gesture_performances
                    $gRows        = $gestureByStudent->get($student->student_id) ?? collect();
                    $totAttempts  = (int) $gRows->sum('attempts');
                    $totSuccess   = (int) $gRows->sum('successfulAttempts');
                    $totWrong     = (int) $gRows->sum('wrongAttempts');
                    $totMastered  = (int) $gRows->where('isMastered', true)->count();
                    $gAccuracy    = $totAttempts > 0 ? round(($totSuccess / $totAttempts) * 100, 1) : 0;

                    return [
                        'student_id'       => $student->student_id,
                        'studentName'      => trim($student->first_name . ' ' . $student->last_name) ?: 'Unknown Student',
                        'gradeLevel'       => $student->grade_level ?? 'N/A',
                        'initials'         => $student->initials,
                        'avatar_url'       => $student->avatarUrl(),
                        'totalLessons'     => $totalLessons,
                        'completedLessons' => $completedLessons,
                        'quizzesTaken'     => $quizzesTaken,
                        'quizzesPassed'    => $quizzesPassed,
                        'quizPassRate'     => $quizPassRate,
                        'avgScore'         => round($avgScore, 1),
                        'overallPct'       => $overallPct,
                        'gestureAccuracy'  => $gAccuracy,
                        'gestureAttempts'  => $totAttempts,
                        'gestureSuccess'   => $totSuccess,
                        'gestureWrong'     => $totWrong,
                        'gesturesMastered' => $totMastered,
                        'gestureBreakdown' => $gRows->values(),
                        'lastAccessed'     => $lastActiveRaw
                            ? Carbon::parse($lastActiveRaw)->diffForHumans()
                            : '—',
                        'lessons'          => $allContentBreakdown,
                    ];
                })
                ->sortBy('studentName')
                ->values();
        } else {
            $checkpointExams = collect();
            $studentGesturePerformance = collect();
        }

        return view('reports', compact(
            'students',
            'lessons',
            'checkpointExams',
            'studentReports',
            'studentGesturePerformance',
            'teacher'
        ));
    }

    /**
     * Accept filter POST → validate → store in session → redirect to clean /reports URL.
     * Using string validation for IDs allows prefixes like 'lesson_1' or 'exam_1'.
     */
    public function applyFilter(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['nullable', 'string'],
            'lesson_id'  => ['nullable', 'string'],
        ]);

        // Normalise 'all' as empty
        $studentId = ($validated['student_id'] ?? 'all') === 'all' ? 'all' : (int) $validated['student_id'];
        $lessonId  = ($validated['lesson_id']  ?? 'all') === 'all' ? 'all' : $validated['lesson_id'];

        if ($studentId === 'all' && $lessonId === 'all') {
            session()->forget('reports_filters');
        } else {
            session(['reports_filters' => [
                'student_id' => $studentId,
                'lesson_id'  => $lessonId,
            ]]);
        }

        return redirect()->route('reports');
    }

    /**
     * Export a professional PDF report — grouped by STUDENT (one clean
     * band per student with their lessons nested underneath), instead
     * of a flat table that repeats the student name on every row.
     */
    public function exportPdf(Request $request)
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            abort(403);
        }

        $teacherId  = $teacher->id;
        $studentIds = Student::where('teacher_id', $teacherId)->where('status', 'active')->pluck('student_id');
        $students   = Student::where('teacher_id', $teacherId)->where('status', 'active')->orderBy('first_name')->get();
        
        $modules          = Module::where('teacher_id', $teacherId)->orderBy('module_order')->get();
        $teacherModuleIds = $modules->pluck('module_id');

        $lessons = Lesson::where('teacher_id', $teacherId)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->where(function($q) use ($teacherModuleIds) {
                $q->whereIn('module_id', $teacherModuleIds)
                  ->orWhereNull('module_id');
            })
            ->with(['module', 'quiz'])
            ->orderBy('module_order')
            ->get();

        $checkpointExams = CheckpointExam::where('teacher_id', $teacherId)
            ->where('status', 'published')
            ->where(function($q) use ($teacherModuleIds) {
                $q->whereIn('module_id', $teacherModuleIds)
                  ->orWhereNull('module_id');
            })
            ->with(['module', 'questions'])
            ->orderBy('module_order', 'asc')
            ->orderBy('title')
            ->get();

        $filterStudent = $request->get('student_id', 'all');
        $filterLesson  = $request->get('lesson_id', 'all');

        $lessonIds = $lessons->pluck('lesson_id');

        $query = StudentLessonProgress::whereIn('student_id', $studentIds)
            ->whereIn('lesson_id', $lessonIds)  // ← Only THIS teacher's lessons
            ->with(['student', 'lesson']);
        if ($filterStudent !== 'all') $query->where('student_id', $filterStudent);
        if ($filterLesson  !== 'all') {
            if (str_starts_with((string)$filterLesson, 'exam_')) {
                $query->whereRaw('1 = 0');
            } else {
                $targetLessonId = (int) str_replace('lesson_', '', $filterLesson);
                $query->where('lesson_id', $targetLessonId);
            }
        }

        $allRows = $query->orderBy('student_id')->orderBy('lesson_id')->get();

        $totalSteps = 7;

        $studentsToReport = $students;
        if ($filterStudent !== 'all') {
            $studentsToReport = $students->where('student_id', (int) $filterStudent);
        }

        // Determine which lessons and checkpoint exams to show per student
        if ($filterLesson !== 'all') {
            if (str_starts_with((string)$filterLesson, 'exam_')) {
                $targetExamId = (int) str_replace('exam_', '', $filterLesson);
                $lessonsToShow = collect();
                $checkpointExamsToShow = $checkpointExams->where('exam_id', $targetExamId);
            } else {
                $targetLessonId = (int) str_replace('lesson_', '', $filterLesson);
                $lessonsToShow = $lessons->where('lesson_id', $targetLessonId);
                $checkpointExamsToShow = collect();
            }
        } else {
            $lessonsToShow = $lessons;
            $checkpointExamsToShow = $checkpointExams;
        }

        $groupedRows = $allRows->groupBy('student_id');

        $checkpointAssignments = DB::table('checkpoint_exam_assignments')
            ->whereIn('student_id', $studentIds)
            ->whereIn('exam_id', $checkpointExams->pluck('exam_id'))
            ->get()
            ->groupBy('student_id');

        $checkpointAttempts = DB::table('checkpoint_exam_attempts')
            ->whereIn('student_id', $studentIds)
            ->whereIn('exam_id', $checkpointExams->pluck('exam_id'))
            ->whereIn('status', ['completed', 'failed'])
            ->get()
            ->groupBy('student_id');

        $totalSteps = 7;

        // Map over all selected active students for the PDF layout.
        $studentReports = $studentsToReport
            ->map(function ($student) use ($groupedRows, $lessonsToShow, $checkpointExamsToShow, $totalSteps, $lessons, $checkpointAssignments, $checkpointAttempts) {
                $rows        = $groupedRows->get($student->student_id) ?? collect();
                $progressMap = $rows->keyBy(fn($r) => $r->lesson_id);

                $lessonBreakdown = $lessonsToShow->map(function ($lesson) use ($progressMap, $totalSteps) {
                    $row     = $progressMap->get($lesson->lesson_id);
                    $started = $row !== null;
                    $stepPct = ($started && $totalSteps > 0)
                        ? min(100, round(($row->current_step / $totalSteps) * 100))
                        : 0;

                    return [
                        'lessonTitle'   => $lesson->title,
                        'difficulty'    => $lesson->difficulty ?? '—',
                        'moduleTitle'   => $lesson->module ? $lesson->module->title : 'Unassigned Lessons',
                        'module_id'     => $lesson->module_id,
                        'ai_generated'  => (bool) $lesson->ai_generated,
                        'started'       => $started,
                        'completed'     => $started && (bool) $row->lesson_completed,
                        'quizCompleted' => $started && (bool) $row->quiz_completed,
                        'quizScore'     => $started ? $row->quiz_score : null,
                        'currentStep'   => $started ? $row->current_step : 0,
                        'totalSteps'    => $totalSteps,
                        'stepPct'       => $stepPct,
                        'lastAccessed'  => ($started && $row->last_accessed_at)
                            ? Carbon::parse($row->last_accessed_at)->format('M d, Y')
                            : '—',
                    ];
                })->values();

                // Checkpoint exams breakdown for PDF
                $stAssignments = $checkpointAssignments->get($student->student_id) ?? collect();
                $stAttempts    = $checkpointAttempts->get($student->student_id) ?? collect();

                $checkpointBreakdown = $checkpointExamsToShow->map(function ($exam) use ($stAssignments, $stAttempts) {
                    $assign        = $stAssignments->firstWhere('exam_id', $exam->exam_id);
                    $examAttempts  = $stAttempts->where('exam_id', $exam->exam_id);
                    $bestAttempt   = $examAttempts->sortByDesc('percentage')->first();
                    $latestAttempt = $examAttempts->sortByDesc('completed_at')->first();

                    $started   = ($assign !== null && $assign->status !== 'pending') || $examAttempts->isNotEmpty();
                    $completed = ($assign !== null && $assign->status === 'completed') || ($bestAttempt !== null && $bestAttempt->percentage >= ($exam->passing_score ?? 60));
                    $failed    = ($assign !== null && $assign->status === 'failed') || ($bestAttempt !== null && !$completed);

                    $score = null;
                    if ($bestAttempt) {
                        $score = round($bestAttempt->percentage, 1);
                    } elseif ($assign && $assign->score !== null) {
                        $score = round($assign->score, 1);
                    }

                    $lastAccessed = null;
                    if ($latestAttempt && $latestAttempt->completed_at) {
                        $lastAccessed = Carbon::parse($latestAttempt->completed_at)->format('M d, Y');
                    } elseif ($assign && $assign->completed_at) {
                        $lastAccessed = Carbon::parse($assign->completed_at)->format('M d, Y');
                    } elseif ($assign && $assign->updated_at) {
                        $lastAccessed = Carbon::parse($assign->updated_at)->format('M d, Y');
                    }

                    return [
                        'lessonTitle'    => '[Exam] ' . $exam->title,
                        'difficulty'     => 'Exam',
                        'moduleTitle'    => $exam->module ? $exam->module->title : 'Unassigned Checkpoint Exams',
                        'module_id'      => $exam->module_id,
                        'ai_generated'   => false,
                        'started'        => $started,
                        'completed'      => $completed,
                        'failed'         => $failed,
                        'quizCompleted'  => $bestAttempt !== null || ($assign !== null && $assign->score !== null),
                        'quizScore'      => $score,
                        'currentStep'    => $completed ? 1 : ($started ? 1 : 0),
                        'totalSteps'     => 1,
                        'stepPct'        => $completed ? 100 : ($started ? 50 : 0),
                        'lastAccessed'   => $lastAccessed ?: '—',
                    ];
                })->values();

                $allContentBreakdown = $lessonBreakdown->concat($checkpointBreakdown);

                $totalLessons     = $lessonsToShow->count() + $checkpointExamsToShow->count();
                $completedLessons = $rows->where('lesson_completed', 1)->count() + $checkpointBreakdown->where('completed', true)->count();
                
                $completedQuizRows = $rows->where('quiz_completed', 1);
                $quizzesTaken      = $completedQuizRows->count() + $checkpointBreakdown->where('quizCompleted', true)->count();
                $quizzesPassed     = 0;
                foreach ($completedQuizRows as $r) {
                    $l = $lessons->firstWhere('lesson_id', $r->lesson_id);
                    $q = $l ? $l->quiz : null;
                    $score = (float) $r->quiz_score;
                    $passingScore = $q ? (float) $q->passing_score : 60;
                    $totalPoints  = $q ? (float) $q->total_points : 5;
                    
                    $isPass = false;
                    if ($passingScore > 10) {
                        $isPass = ($score > 10) ? ($score >= $passingScore) : (($totalPoints > 0 ? ($score / $totalPoints) * 100 : 0) >= $passingScore);
                    } else {
                        $isPass = ($score <= 10) ? ($score >= $passingScore) : ($score >= ($totalPoints > 0 ? ($passingScore / $totalPoints) * 100 : 60));
                    }
                    if ($isPass) {
                        $quizzesPassed++;
                    }
                }
                $quizzesPassed += $checkpointBreakdown->where('completed', true)->count();

                $quizPassRate     = $quizzesTaken > 0 ? round(($quizzesPassed / $quizzesTaken) * 100, 1) : 0;

                $quizScoresSum = $completedQuizRows->sum('quiz_score');
                $examScoresSum = $checkpointBreakdown->where('quizCompleted', true)->whereNotNull('quizScore')->sum('quizScore');
                $totalScoredCount = $completedQuizRows->count() + $checkpointBreakdown->where('quizCompleted', true)->whereNotNull('quizScore')->count();
                $avgScore = $totalScoredCount > 0 ? round(($quizScoresSum + $examScoresSum) / $totalScoredCount, 1) : 0;

                $overallPct       = $totalLessons > 0
                    ? round(($completedLessons / $totalLessons) * 100)
                    : 0;

                $lastActiveRaw    = $rows->isNotEmpty()
                    ? $rows->sortByDesc('last_accessed_at')->first()->last_accessed_at
                    : null;
                $gestureAccuracyData = DB::table('gesture_performances')
                    ->where('student_id', $student->student_id)
                    ->where('attempts', '>', 0)
                    ->selectRaw('SUM(successful_attempts) as successes, SUM(attempts) as total')
                    ->first();
                $gestureAccuracy = ($gestureAccuracyData && $gestureAccuracyData->total > 0)
                    ? round(($gestureAccuracyData->successes / $gestureAccuracyData->total) * 100, 1)
                    : 0;

                // Per-sign breakdown for richer gesture report
                $gesturePerSign = DB::table('gesture_performances as gp')
                    ->join('gestures as g', 'g.gesture_id', '=', 'gp.gesture_id')
                    ->where('gp.student_id', $student->student_id)
                    ->where('gp.attempts', '>', 0)
                    ->selectRaw('g.name as gesture_name, SUM(gp.successful_attempts) as successes, SUM(gp.attempts) as total, SUM(gp.wrong_attempts) as wrongs')
                    ->groupBy('gp.gesture_id', 'g.name')
                    ->get()
                    ->map(function ($r) {
                        $acc = $r->total > 0 ? round(($r->successes / $r->total) * 100, 1) : 0;
                        return [
                            'name'     => $r->gesture_name,
                            'accuracy' => $acc,
                            'attempts' => (int) $r->total,
                            'correct'  => (int) $r->successes,
                            'wrong'    => (int) $r->wrongs,
                        ];
                    });

                $gestureMastered   = $gesturePerSign->where('accuracy', '>=', 75)->sortByDesc('accuracy')->values()->all();
                $gestureStruggling = $gesturePerSign->where('accuracy', '<', 75)->sortBy('accuracy')->values()->all();
                $gestureTotalSigns = $gesturePerSign->count();
                // Full list sorted: mastered first, then by accuracy desc
                $gestureSignList   = $gesturePerSign->sortByDesc('accuracy')->values()->all();

                return [
                    'studentName'        => trim($student->first_name . ' ' . $student->last_name) ?: 'Unknown Student',
                    'gradeLevel'         => $student->grade_level ?? 'N/A',
                    'totalLessons'       => $totalLessons,
                    'completedLessons'   => $completedLessons,
                    'quizzesTaken'       => $quizzesTaken,
                    'quizzesPassed'      => $quizzesPassed,
                    'quizPassRate'       => $quizPassRate,
                    'avgScore'           => round($avgScore, 1),
                    'overallPct'         => $overallPct,
                    'gestureAccuracy'    => $gestureAccuracy,
                    'gestureTotalSigns'  => $gestureTotalSigns,
                    'gestureMastered'    => $gestureMastered,
                    'gestureStruggling'  => $gestureStruggling,
                    'gestureSignList'    => $gestureSignList,
                    'lastAccessed'       => $lastActiveRaw
                        ? Carbon::parse($lastActiveRaw)->format('M d, Y')
                        : '—',
                    'lessons'            => $allContentBreakdown,
                ];
            })
            ->sortBy('studentName')
            ->values();

        // Overall summary stats (top strip) — computed across all filtered rows/students.
        $totalStudents  = $studentReports->count();
        $totalAssignedContent = $lessonsToShow->count() + $checkpointExamsToShow->count();
        $totalCompleted = $studentReports->sum('completedLessons');
        $completionPct  = ($totalStudents > 0 && $totalAssignedContent > 0)
            ? round(($totalCompleted / ($totalStudents * $totalAssignedContent)) * 100)
            : 0;
        $scoredStudents = $studentReports->filter(fn($r) => $r['quizzesTaken'] > 0);
        $avgScore       = $scoredStudents->isNotEmpty() ? $scoredStudents->avg('avgScore') : 0;
        $activeLearners = $studentReports->where('overallPct', '>', 0)->count();

        $generatedAt   = Carbon::now()->format('F d, Y · g:i A');
        $schoolName    = optional($teacher->school)->name ?? 'School';
        $teacherName   = $teacher->first_name . ' ' . $teacher->last_name;

        $selectedStudentName = 'All Students';
        $selectedLessonName  = 'All Lessons & Checkpoint Exams';
        if ($filterStudent !== 'all') {
            $s = $students->firstWhere('student_id', $filterStudent);
            if ($s) $selectedStudentName = $s->first_name . ' ' . $s->last_name;
        }
        if ($filterLesson !== 'all') {
            if (str_starts_with((string)$filterLesson, 'exam_')) {
                $targetExamId = (int) str_replace('exam_', '', $filterLesson);
                $e = $checkpointExams->firstWhere('exam_id', $targetExamId);
                if ($e) $selectedLessonName = 'Exam: ' . $e->title;
            } else {
                $targetLessonId = (int) str_replace('lesson_', '', $filterLesson);
                $l = $lessons->firstWhere('lesson_id', $targetLessonId);
                if ($l) $selectedLessonName = $l->title;
            }
        }

        /* ── Build PDF with TCPDF via TcPdfService ── */
        $paperSize     = $request->get('paper_size', 'A4');
        $runningHeader = $request->get('running_header', 'first');
        $pageNumbers   = $request->get('page_numbers', 'footer');

        // Whitelist to prevent arbitrary values reaching TCPDF
        if (!array_key_exists($paperSize, \App\Services\TcPdfService::PAPER_SIZES)) {
            $paperSize = 'A4';
        }
        if (!in_array($runningHeader, ['every', 'first', 'none'])) {
            $runningHeader = 'first';
        }
        if (!in_array($pageNumbers, ['footer', 'none'])) {
            $pageNumbers = 'footer';
        }

        $pdf = new TcPdfService(
            'Student Progress Report',
            $teacherName,
            $schoolName,
            $generatedAt,
            $paperSize,
            $runningHeader,
            $pageNumbers
        );

        $pdf->AddPage();

        /* IMPORTANT: TCPDF discards Header()'s own SetY() call once control
         * returns here — without this line, everything below draws on top
         * of the navy header instead of underneath it. */
        $pdf->SetY($pdf->bodyStartY());

        /* -- Report Summary KPI strip (6 Uniform KPIs) -- */
        $pdf->sectionTitle('Report Summary');
        $pdf->summaryStrip([
            ['label' => 'Total Students',    'value' => (string) $totalStudents],
            ['label' => 'Lessons Assigned',  'value' => (string) $lessons->count()],
            ['label' => 'Completions',       'value' => (string) $totalCompleted],
            ['label' => 'Completion Rate',   'value' => $completionPct . '%'],
            ['label' => 'Avg Quiz Score',    'value' => number_format($avgScore, 1)],
            ['label' => 'Active Learners',   'value' => (string) $activeLearners],
        ]);
        $pdf->Ln(2);

        /* ── Insight Box ── */
        $pdf->insightBox(
            'Curriculum Progress Insight',
            "Across {$totalStudents} students and {$lessons->count()} assigned lessons, the class has achieved a {$completionPct}% overall completion rate with an average quiz score of " . number_format($avgScore, 1) . " pts. {$activeLearners} learners are actively progressing through the curriculum.",
            'gold'
        );
        $pdf->Ln(2);

        /* ── Filter info line ── */
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(148, 163, 184);
        $lm = $pdf->getOriginalMargins()['left'];
        $usableW = $pdf->getPageWidth() - $lm - $pdf->getOriginalMargins()['right'];
        $pdf->Cell($usableW, 5, 'Filter: ' . $selectedStudentName . '  ·  ' . $selectedLessonName, 0, 1, 'R');
        $pdf->Ln(2);

        /* ── Student Progress Breakdown ── */
        $pdf->sectionTitle('Student Progress Breakdown');

        /* Table column headers definition */
        $headers = [
            ['label' => 'Lesson',      'width' => 75, 'align' => 'L'],
            ['label' => 'Difficulty',  'width' => 28, 'align' => 'L'],
            ['label' => 'Status',      'width' => 32, 'align' => 'L'],
            ['label' => 'Quiz Score',  'width' => 24, 'align' => 'C'],
            ['label' => 'Last Active', 'width' => 0,  'align' => 'L'],
        ];

        if ($studentReports->isEmpty()) {
            $pdf->SetFont('helvetica', 'I', 9);
            $pdf->SetTextColor(148, 163, 184);
            $pdf->Cell($usableW, 8, 'No records match the selected filters.', 0, 1, 'C');
        } else {
            foreach ($studentReports as $student) {
                /* Student divider band */
                $pdf->studentBand(
                    $student['studentName'],
                    $student['gradeLevel'],
                    $student['completedLessons'],
                    $student['totalLessons'],
                    $student['overallPct'],
                    $student['quizzesTaken'],
                    $student['avgScore'],
                    $student['gestureAccuracy'] ?? 0,
                    $student['gestureTotalSigns'] ?? 0
                );

                /* Group lessons by module */
                $grouped = collect($student['lessons'])->groupBy('moduleTitle');
                $rowIdx  = 0;

                foreach ($grouped as $moduleTitle => $moduleLessons) {
                    $pdf->moduleBand($moduleTitle);

                    foreach ($moduleLessons as $lesson) {
                        $pdf->lessonRow(
                            $lesson['lessonTitle'],
                            (bool) $lesson['ai_generated'],
                            $lesson['difficulty'] ?? '-',
                            $lesson['started'],
                            $lesson['completed'],
                            $lesson['quizCompleted'],
                            $lesson['quizScore'] !== null ? (float) $lesson['quizScore'] : null,
                            $lesson['lastAccessed'],
                            $rowIdx % 2 === 1
                        );
                        $rowIdx++;
                    }
                }

                /* ── Per-Student Gesture Performance Per Sign ── */
                $gestureSignList = $student['gestureSignList'] ?? [];

                if (!empty($gestureSignList)) {
                    $pdf->Ln(2);
                    // Section header bar
                    $pdf->SetFont('helvetica', 'B', 8);
                    $pdf->SetTextColor(13, 50, 107);
                    $pdf->SetFillColor(236, 244, 255);
                    $pdf->SetDrawColor(193, 213, 240);
                    $pdf->SetX($lm);
                    $pdf->Cell($usableW, 8, '  Student Performance Per Gesture  (' . count($gestureSignList) . ' signs)', 'B', 1, 'L', true);

                    // Column header row
                    $sNameW = $usableW * 0.28;
                    $sBadgW = $usableW * 0.20;
                    $sAttW  = $usableW * 0.12;
                    $sCorW  = $usableW * 0.12;
                    $sIncW  = $usableW * 0.12;
                    $sAccW  = $usableW - $sNameW - $sBadgW - $sAttW - $sCorW - $sIncW;

                    $pdf->SetFont('helvetica', 'B', 7);
                    $pdf->SetTextColor(100, 116, 139);
                    $pdf->SetFillColor(248, 250, 252);
                    $pdf->SetX($lm);
                    $pdf->Cell($sNameW, 6.5, 'SIGN', 'B', 0, 'L', true);
                    $pdf->Cell($sBadgW, 6.5, 'MASTERY LEVEL', 'B', 0, 'L', true);
                    $pdf->Cell($sAttW,  6.5, 'ATTEMPTS', 'B', 0, 'C', true);
                    $pdf->Cell($sCorW,  6.5, 'CORRECT', 'B', 0, 'C', true);
                    $pdf->Cell($sIncW,  6.5, 'INCORRECT', 'B', 0, 'C', true);
                    $pdf->Cell($sAccW,  6.5, 'ACCURACY', 'B', 1, 'R', true);

                    $pdf->SetFillColor(255, 255, 255);
                    $gRowH   = 8;
                    $altFill = false;

                    foreach ($gestureSignList as $g) {
                        if ($pdf->GetY() + $gRowH > $pdf->getPageHeight() - $pdf->getBreakMargin()) {
                            $pdf->AddPage();
                            // Reprint column headers on continuation page
                            $pdf->SetFont('helvetica', 'B', 7);
                            $pdf->SetTextColor(100, 116, 139);
                            $pdf->SetFillColor(248, 250, 252);
                            $pdf->SetX($lm);
                            $pdf->Cell($sNameW, 6.5, 'SIGN', 'B', 0, 'L', true);
                            $pdf->Cell($sBadgW, 6.5, 'MASTERY LEVEL', 'B', 0, 'L', true);
                            $pdf->Cell($sAttW,  6.5, 'ATTEMPTS', 'B', 0, 'C', true);
                            $pdf->Cell($sCorW,  6.5, 'CORRECT', 'B', 0, 'C', true);
                            $pdf->Cell($sIncW,  6.5, 'INCORRECT', 'B', 0, 'C', true);
                            $pdf->Cell($sAccW,  6.5, 'ACCURACY', 'B', 1, 'R', true);
                            $pdf->SetFillColor(255, 255, 255);
                            $altFill = false;
                        }

                        // Mastery badge label + colors
                        $acc = $g['accuracy'];
                        if ($acc >= 75) {
                            $badgeLabel = 'MASTERED';
                            $badgeColor = [16, 185, 129];
                            $accColor   = [16, 185, 129];
                        } elseif ($acc >= 60) {
                            $badgeLabel = 'PROFICIENT';
                            $badgeColor = [59, 130, 246];
                            $accColor   = [59, 130, 246];
                        } elseif ($acc >= 40) {
                            $badgeLabel = 'DEVELOPING';
                            $badgeColor = [245, 158, 11];
                            $accColor   = [180, 83, 9];
                        } else {
                            $badgeLabel = 'NEEDS PRACTICE';
                            $badgeColor = [239, 68, 68];
                            $accColor   = [220, 38, 38];
                        }

                        $bg = $altFill ? [248, 252, 255] : [255, 255, 255];
                        $pdf->SetFillColor(...$bg);

                        // Sign name
                        $pdf->SetFont('helvetica', 'B', 8);
                        $pdf->SetTextColor(13, 50, 107);
                        $pdf->SetX($lm);
                        $pdf->Cell($sNameW, $gRowH, \Illuminate\Support\Str::limit($g['name'], 18), 'B', 0, 'L', true);

                        // Mastery badge (text only, colored)
                        $pdf->SetFont('helvetica', 'B', 6.5);
                        $pdf->SetTextColor(...$badgeColor);
                        $pdf->Cell($sBadgW, $gRowH, $badgeLabel, 'B', 0, 'L', true);

                        // Attempts
                        $pdf->SetFont('helvetica', '', 7.5);
                        $pdf->SetTextColor(71, 85, 105);
                        $pdf->Cell($sAttW, $gRowH, (string) $g['attempts'], 'B', 0, 'C', true);

                        // Correct (green)
                        $pdf->SetFont('helvetica', 'B', 7.5);
                        $pdf->SetTextColor(16, 185, 129);
                        $pdf->Cell($sCorW, $gRowH, (string) $g['correct'], 'B', 0, 'C', true);

                        // Incorrect (red)
                        $pdf->SetTextColor(239, 68, 68);
                        $pdf->Cell($sIncW, $gRowH, (string) $g['wrong'], 'B', 0, 'C', true);

                        // Accuracy (right-aligned, colored by level)
                        $pdf->SetFont('helvetica', 'B', 8);
                        $pdf->SetTextColor(...$accColor);
                        $pdf->Cell($sAccW, $gRowH, number_format($acc, 1) . '%', 'B', 1, 'R', true);

                        $altFill = !$altFill;
                    }

                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetTextColor(51, 65, 85);
                }



                $pdf->Ln(3);
            }

        }

        $filename = 'senas-report-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export Analytics PDF
     */
    public function exportAnalyticsPdf(Request $request)
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) abort(403);

        // Prefer POST-submitted filter values (from hidden inputs in the modal),
        // fall back to session values so the PDF always matches the page filter.
        $sessionFilters = session('analytics_filters', []);
        $filters = array_merge($sessionFilters, array_filter([
            'period' => $request->input('period'),
            'year'   => $request->input('year'),
            'month'  => $request->input('month'),
        ], fn($v) => $v !== null && $v !== ''));
        $request->merge($filters);

        $data        = (new AnalyticsController())->buildAnalyticsData($teacher, $request);
        $teacherName = trim($teacher->first_name . ' ' . $teacher->last_name);
        $schoolName  = optional($teacher->school)->name ?? 'School';
        $generatedAt = Carbon::now()->format('F d, Y · g:i A');

        /* ── Build PDF with TCPDF via TcPdfService ── */
        $paperSize     = $request->get('paper_size', 'A4');
        $runningHeader = $request->get('running_header', 'first');
        $pageNumbers   = $request->get('page_numbers', 'footer');

        if (!array_key_exists($paperSize, \App\Services\TcPdfService::PAPER_SIZES)) {
            $paperSize = 'A4';
        }
        if (!in_array($runningHeader, ['every', 'first', 'none'])) {
            $runningHeader = 'first';
        }
        if (!in_array($pageNumbers, ['footer', 'none'])) {
            $pageNumbers = 'footer';
        }

        $pdf = new TcPdfService('Class Analytics Report', $teacherName, $schoolName, $generatedAt, $paperSize, $runningHeader, $pageNumbers);
        $pdf->AddPage();

        /* IMPORTANT: same fix as exportPdf() — force the cursor below the
         * branded header before drawing anything, since TCPDF resets the
         * cursor to the top margin after Header() returns. Without this the
         * KPI strip and metadata row overlap, exactly like the screenshot. */
        $pdf->SetY($pdf->bodyStartY());

        $lm     = $pdf->getOriginalMargins()['left'];
        $usableW = $pdf->getPageWidth() - $lm - $pdf->getOriginalMargins()['right'];

        /* ── 6 KPI Summary Strip ── */
        $pdf->sectionTitle('Class Summary');
        $pdf->summaryStrip([
            ['label' => 'Total Students',  'value' => (string) ($data['totalStudents'] ?? 0)],
            ['label' => 'Avg Quiz Score',  'value' => number_format($data['avgQuizScore'] ?? 0, 1) . '%'],
            ['label' => 'Gesture Mastery', 'value' => number_format($data['avgMastery'] ?? 0, 1) . '%'],
            ['label' => 'Completion Rate', 'value' => number_format($data['completionRate'] ?? 0, 1) . '%'],
            ['label' => 'Avg Streak',      'value' => ($data['avgStreakDays'] ?? 0) . ' d'],
            ['label' => 'Active (Last 7d)','value' => number_format($data['activeLast7Pct'] ?? 0, 1) . '%'],
        ]);
        $pdf->Ln(1);

        /* ── Insight box ── */
        $pdf->insightBox(
            'Class Performance Summary',
            'As of ' . now()->format('F d, Y') . ', your class has ' . ($data['totalStudents'] ?? 0) . ' enrolled students. ' .
            'The average quiz score is ' . number_format($data['avgQuizScore'] ?? 0, 1) . '% and average gesture mastery is ' .
            number_format($data['avgMastery'] ?? 0, 1) . '%. Lesson completion rate stands at ' .
            number_format($data['completionRate'] ?? 0, 1) . '%.',
            'gold'
        );
        $pdf->Ln(1);

        /* ── Class Progress Over Time ── */
        $period      = $request->get('period', 'weekly');
        $year        = (int) $request->get('year', date('Y'));
        $progressPts = array_values((array) ($data['progressOverTime'] ?? []));
        $periodLabel = ucfirst($period) . ' average quiz score ' . $year;
        $countLabel  = count($progressPts) . ' ' . $period;
        $pdf->sectionTitle('Class Progress Over Time');
        $pdf->progressLineChart($progressPts, $periodLabel, $countLabel);
        $pdf->Ln(1);

        /* ── Module Difficulty Ranking ── */
        $pdf->sectionTitle('Module Difficulty Ranking');
        $pdf->moduleDifficultyList($data['lessonDifficulty'] ?? []);
        $pdf->Ln(1);

        /* ── Student Ranking — flows naturally, no forced page break ── */
        $pdf->sectionTitle('Student Ranking');
        if (!empty($data['studentRanking']) && count($data['studentRanking']) > 0) {
            $isOverall = !isset($data['studentRanking'][0]['best_score']);
            $rankHeaders = [
                ['label' => 'Rank',         'width' => 18, 'align' => 'C'],
                ['label' => 'Student Name', 'width' => 80, 'align' => 'L'],
                ['label' => $isOverall ? 'Quizzes' : 'Attempts',   'width' => 28, 'align' => 'C'],
                ['label' => $isOverall ? 'Avg Score' : 'Best Score','width' => 0,  'align' => 'C'],
            ];
            $rankRows = [];
            foreach ($data['studentRanking'] as $i => $s) {
                $rankLabel  = ($i === 0) ? '1st' : (($i === 1) ? '2nd' : (($i === 2) ? '3rd' : '#' . ($i + 1)));
                $scoreVal   = $isOverall ? ($s['overall_score'] ?? $s['avg_score'] ?? 0) : ($s['best_score'] ?? 0);
                $attemptVal = $isOverall ? ($s['quizzes_count'] ?? $s['lesson_count'] ?? 0) : ($s['attempts_to_achieve'] ?? 0);
                $rankRows[] = [$rankLabel, $s['name'], $attemptVal, number_format($scoreVal, 1) . '%'];
            }
            $pdf->dataTable($rankHeaders, $rankRows);
        } else {
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(148, 163, 184);
            $pdf->Cell($usableW, 5, 'No quiz attempt data available yet.', 0, 1, 'C');
        }
        $pdf->Ln(2);

        /* ── Mastery Distribution ── */
        $pdf->sectionTitle('Mastery Level Distribution');
        if (!empty($data['masteryDistribution']) && ($data['masteryTotal'] ?? 0) > 0) {
            $mColors = [
                [239, 68, 68],
                [245, 158, 11],
                [59, 130, 246],
                [16, 185, 129],
            ];
            foreach ($data['masteryDistribution'] as $i => $seg) {
                $pdf->barRow(
                    $seg['label'] . ' (' . $seg['count'] . ')',
                    (float) ($seg['pct'] ?? 0),
                    100,
                    $mColors[$i % count($mColors)]
                );
            }
        } else {
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(148, 163, 184);
            $pdf->Cell($usableW, 5, 'No mastery data available yet.', 0, 1, 'C');
        }
        $pdf->Ln(2);

        /* ── Completion Funnel ── */
        $pdf->sectionTitle('Lesson Completion Funnel');
        if (!empty($data['completionFunnel']) && ($data['completionTotal'] ?? 0) > 0) {
            $fColors = [
                [250, 204, 21],
                [59, 130, 246],
                [16, 185, 129],
                [239, 68, 68],
            ];
            $total = max(1, $data['completionTotal']);
            foreach ($data['completionFunnel'] as $i => $step) {
                $pct = round(($step['count'] / $total) * 100, 1);
                $pdf->barRow(
                    $step['label'] . ' (' . $step['count'] . ')',
                    $pct,
                    100,
                    $fColors[$i % count($fColors)]
                );
            }
        } else {
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(148, 163, 184);
            $pdf->Cell($usableW, 5, 'No lesson assignment data available yet.', 0, 1, 'C');
        }
        $pdf->Ln(2);

        /* ── Score Distribution ── */
        $pdf->sectionTitle('Quiz Score Distribution');
        $scoreTotal = collect($data['scoreBuckets'] ?? [])->sum('count');
        if ($scoreTotal > 0) {
            $sColors = [
                [239, 68, 68],
                [245, 158, 11],
                [59, 130, 246],
                [30, 75, 143],
                [13, 50, 107],
            ];
            foreach (($data['scoreBuckets'] ?? []) as $i => $bucket) {
                $pct = round(($bucket['count'] / $scoreTotal) * 100, 1);
                $pdf->barRow(
                    $bucket['label'] . '% — ' . $bucket['count'] . ' attempts (' . $pct . '%)',
                    $pct,
                    100,
                    $sColors[$i % count($sColors)]
                );
            }
        } else {
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(148, 163, 184);
            $pdf->Cell($usableW, 5, 'No quiz attempt data available yet.', 0, 1, 'C');
        }
        $pdf->Ln(2);



        /* ── Gesture Performance (new page) ── */
        $pdf->AddPage();
        $pdf->sectionTitle('Gesture Performance Analytics');

        /* Overview strip */
        $gOverview = $data['gesturePerformanceOverview'] ?? [];
        if (!empty($gOverview) && ($gOverview['total_attempts'] ?? 0) > 0) {
            $pdf->summaryStrip([
                ['label' => 'Total Signs',      'value' => (string) ($gOverview['total_gestures'] ?? 0)],
                ['label' => 'Total Attempts',   'value' => (string) ($gOverview['total_attempts'] ?? 0)],
                ['label' => 'Correct',          'value' => (string) ($gOverview['total_successful'] ?? 0)],
                ['label' => 'Class Accuracy',   'value' => number_format($gOverview['overall_accuracy'] ?? 0, 1) . '%'],
                ['label' => 'Signs Mastered',   'value' => (string) ($data['masteredSignsCount'] ?? 0)],
                ['label' => 'Needs Practice',   'value' => (string) ($data['lowMasterySignsCount'] ?? 0)],
            ]);
            $pdf->Ln(3);
        }

        $signsBreakdownPdf = $data['signsBreakdown'] ?? [];

        if (empty($signsBreakdownPdf)) {
            $pdf->SetFont('helvetica', 'I', 9);
            $pdf->SetTextColor(148, 163, 184);
            $pdf->Cell($usableW, 8, 'No student gesture practices recorded in this period.', 0, 1, 'C');
        } else {

            /* ── Overview: Best 5 vs. Worst 5 side-by-side ── */
            $topGestures  = collect($signsBreakdownPdf)->sortByDesc('accuracy')->take(5)->values()->all();
            $worstGestures = collect($signsBreakdownPdf)->sortBy('accuracy')->take(5)->values()->all();

            $halfW = $usableW / 2 - 3;

            // Left column header
            $pdf->SetFont('helvetica', 'B', 8.5);
            $pdf->SetTextColor(22, 101, 52);   // green-800
            $leftX = $lm;
            $rightX = $lm + $halfW + 6;
            $pdf->SetXY($leftX, $pdf->GetY());
            $pdf->Cell($halfW, 6, 'Best-Performing Signs (Top 5)', 'B', 0, 'L');
            $pdf->SetTextColor(153, 27, 27);    // red-800
            $pdf->SetX($rightX);
            $pdf->Cell($halfW, 6, 'Signs Needing Most Practice (Bottom 5)', 'B', 1, 'L');
            $pdf->Ln(1);

            $maxRows = max(count($topGestures), count($worstGestures));
            for ($ri = 0; $ri < $maxRows; $ri++) {
                $rowY = $pdf->GetY();

                // Left: top gesture
                if (isset($topGestures[$ri])) {
                    $g = $topGestures[$ri];
                    $pdf->SetXY($leftX, $rowY);
                    $pdf->SetFont('helvetica', 'B', 8);
                    $pdf->SetTextColor(13, 50, 107);
                    $pdf->Cell($halfW - 26, 5, \Illuminate\Support\Str::limit($g['gesture_name'], 22), 0, 0, 'L');
                    $pdf->SetFont('helvetica', 'B', 8);
                    $pdf->SetTextColor(22, 101, 52);
                    $pdf->Cell(26, 5, number_format($g['accuracy'], 1) . '%', 0, 0, 'R');
                    $pdf->SetXY($leftX, $rowY + 5);
                    $pdf->SetDrawColor(220, 252, 231);
                    $pdf->SetFillColor(220, 252, 231);
                    $barW = round(($halfW) * min(100, max(2, $g['accuracy'])) / 100);
                    $pdf->Rect($leftX, $rowY + 5, $halfW, 3, 'DF', [], [220, 252, 231]);
                    $pdf->SetFillColor(16, 185, 129);
                    $pdf->Rect($leftX, $rowY + 5, $barW, 3, 'F');
                    // Best student note
                    $pdf->SetXY($leftX, $rowY + 9);
                    $pdf->SetFont('helvetica', '', 7);
                    $pdf->SetTextColor(100, 116, 139);
                    $bestName = !empty($g['best_student']['name']) ? 'Best: ' . $g['best_student']['name'] . ' (' . ($g['best_student']['accuracy'] ?? 0) . '%)' : '';
                    $pdf->Cell($halfW, 4, $bestName, 0, 0, 'L');
                }

                // Right: worst gesture
                if (isset($worstGestures[$ri])) {
                    $g = $worstGestures[$ri];
                    $pdf->SetXY($rightX, $rowY);
                    $pdf->SetFont('helvetica', 'B', 8);
                    $pdf->SetTextColor(13, 50, 107);
                    $pdf->Cell($halfW - 26, 5, \Illuminate\Support\Str::limit($g['gesture_name'], 22), 0, 0, 'L');
                    $pdf->SetFont('helvetica', 'B', 8);
                    $pdf->SetTextColor(153, 27, 27);
                    $pdf->Cell(26, 5, number_format($g['accuracy'], 1) . '%', 0, 0, 'R');
                    $pdf->SetXY($rightX, $rowY + 5);
                    $barW = round(($halfW) * min(100, max(2, $g['accuracy'])) / 100);
                    $pdf->SetFillColor(254, 226, 226);
                    $pdf->Rect($rightX, $rowY + 5, $halfW, 3, 'DF', [], [254, 226, 226]);
                    $pdf->SetFillColor(239, 68, 68);
                    $pdf->Rect($rightX, $rowY + 5, $barW, 3, 'F');
                    // Struggling student note
                    $pdf->SetXY($rightX, $rowY + 9);
                    $pdf->SetFont('helvetica', '', 7);
                    $pdf->SetTextColor(100, 116, 139);
                    $strName = !empty($g['struggling_student']['name']) ? 'Struggling: ' . $g['struggling_student']['name'] . ' (' . ($g['struggling_student']['accuracy'] ?? 0) . '%)' : '';
                    $pdf->Cell($halfW, 4, $strName, 0, 0, 'L');
                }

                $pdf->SetY($rowY + 14);
            }
            $pdf->Ln(6);

            /* ── Per-Sign Student Ranking Tables — flows directly after overview ── */
            $pdf->Ln(3);
            $pdf->sectionTitle('Per-Sign Student Breakdown & Rankings');
            $pdf->Ln(1);

            // Column widths that exactly fill $usableW (A4 usable ≈ 170mm)
            // Rank(14) + Student(auto) + Att(20) + Correct(20) + Wrong(18) + Acc(24) + Status(24) = 120 fixed
            $fixedW  = 14 + 20 + 20 + 18 + 24 + 24; // = 120
            $nameW   = $usableW - $fixedW;             // remaining for name column
            $rankW   = 14;
            $attW    = 20;
            $corW    = 20;
            $wrnW    = 18;
            $accW    = 24;
            $statW   = 24;
            $tblRowH = 6.5;

            foreach ($signsBreakdownPdf as $sign) {
                $isMastered    = $sign['status'] === 'mastered';
                $headerBgR     = $isMastered ? [240, 253, 244] : [255, 251, 235];
                $headerBorderR = $isMastered ? [86, 200, 129]  : [251, 191, 36];
                $accentColor   = $isMastered ? [22, 101, 52]   : [146, 64, 14];
                $barColor      = $isMastered ? [16, 185, 129]  : [245, 158, 11];

                // Estimate block height: header(11) + best/struggling row(7) + thead(7) + rows
                $students  = $sign['students_ranking'] ?? [];
                $blockH    = 11 + 7 + $tblRowH + (count($students) * $tblRowH) + 4;
                $pageH     = $pdf->getPageHeight() - $pdf->getBreakMargin();
                if ($pdf->GetY() + min($blockH, 35) > $pageH) {
                    $pdf->AddPage();
                }

                // ── Sign header bar ──
                $hdrY = $pdf->GetY();
                $pdf->SetFillColor(...$headerBgR);
                $pdf->SetDrawColor(...$headerBorderR);
                $pdf->Rect($lm, $hdrY, $usableW, 11, 'DF');

                $pdf->SetXY($lm + 3, $hdrY + 1.5);
                $pdf->SetFont('helvetica', 'B', 9.5);
                $pdf->SetTextColor(13, 50, 107);
                $pdf->Cell($nameW + $rankW, 5, 'Sign: ' . $sign['gesture_name'], 0, 0, 'L');

                $statusLabel = $isMastered ? 'Mastered by Majority' : 'Needs Class Practice';
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->SetTextColor(...$accentColor);
                $pdf->Cell(45, 5, $statusLabel, 0, 0, 'C');

                $pdf->SetFont('helvetica', 'B', 9.5);
                $pdf->SetTextColor(...$accentColor);
                $pdf->Cell(0, 5, number_format($sign['accuracy'], 1) . '% accuracy', 0, 0, 'R');

                $pdf->SetXY($lm + 3, $hdrY + 7);
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetTextColor(71, 85, 105);
                $pdf->Cell($usableW - 6, 3.5, $sign['total_attempts'] . ' attempts · ' . $sign['successful_attempts'] . ' correct · ' . $sign['wrong_attempts'] . ' wrong', 0, 1, 'L');

                // ── Accuracy bar ──
                $pdf->SetXY($lm, $hdrY + 11);
                $barFillW = round($usableW * min(100, max(1, $sign['accuracy'])) / 100);
                $pdf->SetFillColor(226, 232, 240);
                $pdf->Rect($lm, $hdrY + 11, $usableW, 2.5, 'F');
                $pdf->SetFillColor(...$barColor);
                $pdf->Rect($lm, $hdrY + 11, $barFillW, 2.5, 'F');
                $pdf->SetY($hdrY + 14);

                // ── Best / Struggling row ──
                if (!empty($sign['best_student']) || !empty($sign['struggling_student'])) {
                    $bsY = $pdf->GetY();
                    $pdf->SetFillColor(248, 250, 252);
                    $pdf->Rect($lm, $bsY, $usableW, 7, 'F');

                    $pdf->SetXY($lm + 3, $bsY + 1.5);
                    $pdf->SetFont('helvetica', 'B', 7.5);
                    $pdf->SetTextColor(22, 101, 52);
                    $bestTxt = !empty($sign['best_student'])
                        ? 'Best: ' . $sign['best_student']['name'] . ' — ' . ($sign['best_student']['accuracy'] ?? 0) . '% (' . ($sign['best_student']['attempts'] ?? 0) . ' attempts)'
                        : 'Best: —';
                    $pdf->Cell($usableW / 2 - 3, 4.5, $bestTxt, 0, 0, 'L');

                    $pdf->SetFont('helvetica', 'B', 7.5);
                    $pdf->SetTextColor(153, 27, 27);
                    $strTxt = !empty($sign['struggling_student'])
                        ? 'Struggling: ' . $sign['struggling_student']['name'] . ' — ' . ($sign['struggling_student']['accuracy'] ?? 0) . '% (' . ($sign['struggling_student']['wrong'] ?? 0) . ' mistakes)'
                        : 'Struggling: —';
                    $pdf->Cell(0, 4.5, $strTxt, 0, 1, 'L');
                    $pdf->SetY($bsY + 7);
                }

                // ── Student ranking table with computed column widths ──
                if (!empty($students)) {
                    // Table header
                    $pdf->SetFillColor(13, 50, 107);
                    $pdf->SetTextColor(255, 255, 255);
                    $pdf->SetFont('helvetica', 'B', 6.5);
                    $pdf->SetDrawColor(13, 50, 107);
                    $pdf->SetX($lm);
                    $pdf->Cell($rankW, $tblRowH, 'RANK',    1, 0, 'C', true);
                    $pdf->Cell($nameW, $tblRowH, 'STUDENT', 1, 0, 'L', true);
                    $pdf->Cell($attW,  $tblRowH, 'ATT',     1, 0, 'C', true);
                    $pdf->Cell($corW,  $tblRowH, 'CORRECT', 1, 0, 'C', true);
                    $pdf->Cell($wrnW,  $tblRowH, 'WRONG',   1, 0, 'C', true);
                    $pdf->Cell($accW,  $tblRowH, 'ACCURACY',1, 0, 'C', true);
                    $pdf->Cell($statW, $tblRowH, 'STATUS',  1, 1, 'C', true);

                    // Table rows
                    $pdf->SetFont('helvetica', '', 7.5);
                    $pdf->SetDrawColor(226, 232, 240);
                    $fillRow = false;
                    foreach ($students as $sr) {
                        if ($pdf->GetY() + $tblRowH > $pdf->getPageHeight() - $pdf->getBreakMargin()) {
                            $pdf->AddPage();
                            // Reprint header on continuation
                            $pdf->SetFillColor(13, 50, 107);
                            $pdf->SetTextColor(255, 255, 255);
                            $pdf->SetFont('helvetica', 'B', 6.5);
                            $pdf->SetDrawColor(13, 50, 107);
                            $pdf->SetX($lm);
                            $pdf->Cell($rankW, $tblRowH, 'RANK',    1, 0, 'C', true);
                            $pdf->Cell($nameW, $tblRowH, 'STUDENT', 1, 0, 'L', true);
                            $pdf->Cell($attW,  $tblRowH, 'ATT',     1, 0, 'C', true);
                            $pdf->Cell($corW,  $tblRowH, 'CORRECT', 1, 0, 'C', true);
                            $pdf->Cell($wrnW,  $tblRowH, 'WRONG',   1, 0, 'C', true);
                            $pdf->Cell($accW,  $tblRowH, 'ACCURACY',1, 0, 'C', true);
                            $pdf->Cell($statW, $tblRowH, 'STATUS',  1, 1, 'C', true);
                            $pdf->SetFont('helvetica', '', 7.5);
                            $pdf->SetDrawColor(226, 232, 240);
                            $fillRow = false;
                        }

                        $rankLabel = match((int)$sr['rank']) {
                            1 => '1st', 2 => '2nd', 3 => '3rd',
                            default => '#' . $sr['rank']
                        };
                        $statusTxt = $sr['is_mastered'] ? 'Mastered' : 'Practicing';
                        $bg = $fillRow ? [248, 252, 255] : [255, 255, 255];
                        $pdf->SetFillColor(...$bg);
                        $pdf->SetTextColor(51, 65, 85);
                        $pdf->SetX($lm);
                        $pdf->Cell($rankW, $tblRowH, $rankLabel,                          'B', 0, 'C', true);
                        $pdf->Cell($nameW, $tblRowH, (string) $sr['name'],                'B', 0, 'L', true);
                        $pdf->Cell($attW,  $tblRowH, (string) $sr['attempts'],            'B', 0, 'C', true);
                        $pdf->Cell($corW,  $tblRowH, (string) $sr['successful_attempts'], 'B', 0, 'C', true);
                        $pdf->Cell($wrnW,  $tblRowH, (string) $sr['wrong_attempts'],      'B', 0, 'C', true);
                        $pdf->Cell($accW,  $tblRowH, number_format($sr['accuracy'], 1) . '%', 'B', 0, 'C', true);
                        $pdf->Cell($statW, $tblRowH, $statusTxt,                          'B', 1, 'C', true);
                        $fillRow = !$fillRow;
                    }
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetTextColor(51, 65, 85);
                } else {
                    $pdf->SetFont('helvetica', 'I', 7.5);
                    $pdf->SetTextColor(148, 163, 184);
                    $pdf->Cell($usableW, 5, 'No individual student data for this sign.', 0, 1, 'C');
                }

                $pdf->Ln(2);
            }
        }


        return $pdf->download('senas-analytics-' . now()->format('Y-m-d') . '.pdf');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TEACHER HELP-REQUEST (STUDENT REPORT) METHODS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /reports/help-requests/{studentId}
     * Returns all help-requests for a specific student (teacher's own students only).
     */
    public function studentHelpRequests(int $studentId)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Ensure the student belongs to this teacher
        $student = Student::where('student_id', $studentId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $reports = HelpRequest::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($r) => $this->formatHelpRequest($r));

        return response()->json(['reports' => $reports]);
    }

    /**
     * POST /reports/help-requests/{id}/review
     * Teacher updates status and/or adds a response to a help request.
     */
    public function teacherReviewReport(Request $request, int $id)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $report = HelpRequest::findOrFail($id);

        // Verify the student belongs to this teacher
        $student = Student::where('student_id', $report->student_id)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $validated = $request->validate([
            'status'           => 'required|in:under_review,resolved',
            'teacher_response' => 'nullable|string|max:2000',
        ]);

        // Prevent re-opening an already escalated or closed report
        if (in_array($report->status, ['escalated', 'closed'])) {
            return response()->json([
                'success' => false,
                'message' => 'This report has already been escalated to admin and cannot be modified.',
            ], 422);
        }

        $report->update([
            'status'                => $validated['status'],
            'teacher_response'      => $validated['teacher_response'] ?? $report->teacher_response,
            'teacher_responded_at'  => now(),
            'teacher_responded_by'  => Auth::id(),
        ]);

        return response()->json([
            'success'              => true,
            'status'               => $report->status,
            'statusLabel'          => $report->statusLabel,
            'teacher_response'     => $report->teacher_response,
            'teacher_responded_at' => $report->teacher_responded_at?->format('M d, Y g:i A'),
        ]);
    }

    /**
     * POST /reports/help-requests/{id}/escalate
     * Teacher escalates a report to admin.
     */
    public function escalateReport(Request $request, int $id)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $report = HelpRequest::findOrFail($id);

        // Verify the student belongs to this teacher
        $student = Student::where('student_id', $report->student_id)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        if ($report->status === 'escalated' || $report->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'This report has already been escalated.',
            ], 422);
        }

        $validated = $request->validate([
            'escalation_reason' => 'required|string|max:2000',
        ]);

        $report->escalateToAdmin(Auth::id(), $validated['escalation_reason']);

        return response()->json([
            'success'      => true,
            'status'       => $report->status,
            'statusLabel'  => $report->statusLabel,
            'escalated_at' => $report->escalated_at?->format('M d, Y g:i A'),
        ]);
    }

    /**
     * GET /reports/help-requests/{id}
     * Returns a single help request detail (teacher must own the student).
     */
    public function getTeacherHelpRequest(int $id)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $report = HelpRequest::findOrFail($id);

        // Verify the student belongs to this teacher
        Student::where('student_id', $report->student_id)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        return response()->json($this->formatHelpRequest($report, true));
    }

    /** Format a HelpRequest for JSON responses */
    private function formatHelpRequest(HelpRequest $r, bool $full = false): array
    {
        $data = [
            'help_request_id'      => $r->help_request_id,
            'message'              => $r->message,
            'status'               => $r->status,
            'statusLabel'          => $r->statusLabel,
            'teacher_response'     => $r->teacher_response,
            'teacher_responded_at' => $r->teacher_responded_at?->format('M d, Y g:i A'),
            'escalated_at'         => $r->escalated_at?->format('M d, Y g:i A'),
            'escalation_reason'    => $r->escalation_reason,
            'admin_response'       => $r->admin_response,
            'created_at'           => $r->created_at->format('M d, Y g:i A'),
        ];

        if ($full && $r->escalator) {
            $data['escalated_by_name'] = $r->escalator->name;
        }

        return $data;
    }

    /**
     * GET /reports/student/{studentId}/achievements
     * Returns all achievements (unlocked + in-progress) for a student
     * who belongs to the authenticated teacher.
     */
    public function studentAchievements(int $studentId)
    {
        $teacher = Auth::user()->teacher;

        // Ensure the student belongs to this teacher
        $student = Student::where('student_id', $studentId)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->firstOrFail();

        $rows = StudentAchievement::with('achievement')
            ->where('student_id', $studentId)
            ->get()
            ->sortByDesc('is_unlocked')
            ->sortBy(fn($r) => $r->achievement?->order ?? 999)
            ->values()
            ->map(function ($row) {
                $a = $row->achievement;
                if (!$a) return null;
                return [
                    'achievement_id'   => $a->id,
                    'code'             => $a->code,
                    'name'             => $a->name,
                    'description'      => $a->description,
                    'category'         => $a->category,
                    'icon'             => $a->icon,
                    'color'            => $a->color,
                    'is_unlocked'      => (bool) $row->is_unlocked,
                    'unlocked_at'      => $row->unlocked_at?->toIso8601String(),
                    'progress_current' => (int) $row->progress_current,
                    'progress_target'  => (int) $row->progress_target,
                ];
            })
            ->filter()
            ->values();

        return response()->json(['achievements' => $rows]);
    }

    /**
     * GET /reports/student/{studentId}/learning-path
     * Returns learning path info + XP history + lesson activity for charts.
     */
    public function studentLearningPath(int $studentId)
    {
        $teacher = Auth::user()->teacher;

        $student = Student::where('student_id', $studentId)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->firstOrFail();

        // ── Learning Path setup ──────────────────────────────────────
        $lp = DB::table('learning_paths')->where('student_id', $studentId)->first();

        // ── XP over the last 14 days ─────────────────────────────────
        $xpLog = DB::table('xp_log')
            ->where('student_id', $studentId)
            ->where('created_at', '>=', Carbon::now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as day, SUM(xp_amount) as total_xp')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        // Build a full 14-day series (fill gaps with 0)
        $xpSeries  = [];
        $xpLabels  = [];
        $cumulative = 0;
        $cumulativeSeries = [];
        for ($i = 13; $i >= 0; $i--) {
            $day   = Carbon::now()->subDays($i)->toDateString();
            $label = Carbon::now()->subDays($i)->format('M j');
            $xp    = isset($xpLog[$day]) ? (int) $xpLog[$day]->total_xp : 0;
            $cumulative += $xp;
            $xpLabels[]         = $label;
            $xpSeries[]         = $xp;
            $cumulativeSeries[] = $cumulative;
        }

        // ── Lessons completed per day over last 14 days ───────────────
        $lessonActivity = DB::table('lesson_assignments')
            ->where('student_id', $studentId)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', Carbon::now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(completed_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $lessonSeries = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->toDateString();
            $lessonSeries[] = isset($lessonActivity[$day]) ? (int) $lessonActivity[$day]->count : 0;
        }

        // ── Gesture accuracy over last 7 sessions ────────────────────
        $gesturePerf = DB::table('gesture_performances')
            ->where('student_id', $studentId)
            ->orderBy('last_attempt_at', 'desc')
            ->take(10)
            ->get(['attempts', 'successful_attempts', 'mastery_level',
                   'last_attempt_at']);

        $gestureTrend = $gesturePerf->map(function ($g) {
            $acc = $g->attempts > 0
                ? round(($g->successful_attempts / $g->attempts) * 100, 1)
                : 0;
            return [
                'accuracy'      => $acc,
                'mastery_level' => $g->mastery_level,
                'date'          => Carbon::parse($g->last_attempt_at)->format('M j'),
            ];
        })->values()->toArray();

        // ── Mastery distribution for doughnut ────────────────────────
        $masteryDist = DB::table('gesture_performances')
            ->where('student_id', $studentId)
            ->selectRaw('mastery_level, COUNT(*) as cnt')
            ->groupBy('mastery_level')
            ->pluck('cnt', 'mastery_level')
            ->toArray();

        // ── Quiz scores over time ─────────────────────────────────────
        $quizHistory = DB::table('quiz_attempts')
            ->where('student_id', $studentId)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'asc')
            ->take(10)
            ->get(['percentage', 'completed_at'])
            ->map(fn($q) => [
                'score' => round((float) $q->percentage, 1),
                'label' => Carbon::parse($q->completed_at)->format('M j'),
            ])->values()->toArray();

        // ── Stats summary ─────────────────────────────────────────────
        $totalXpEarned = DB::table('xp_log')
            ->where('student_id', $studentId)
            ->sum('xp_amount');

        $longestStreak = $student->streak_days ?? 0;

        $completedLessonsTotal = DB::table('lesson_assignments')
            ->where('student_id', $studentId)
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'learning_path' => $lp ? [
                'fsl_level'    => $lp->fsl_level,
                'learning_goal'=> $lp->learning_goal,
                'practice_time'=> $lp->practice_time,
                'is_completed' => (bool) $lp->is_completed,
                'completed_at' => $lp->completed_at,
                'created_at'   => $lp->created_at,
            ] : null,
            'stats' => [
                'total_xp'         => (int) $totalXpEarned,
                'streak_days'      => (int) $longestStreak,
                'completed_lessons'=> (int) $completedLessonsTotal,
                'current_level'    => $student->fsl_mastery_level ?? 'Beginner',
            ],
            'charts' => [
                'labels'       => $xpLabels,
                'xp_daily'     => $xpSeries,
                'xp_cumulative'=> $cumulativeSeries,
                'lessons_daily'=> $lessonSeries,
                'quiz_history' => $quizHistory,
                'gesture_trend'=> $gestureTrend,
                'mastery_dist' => $masteryDist,
            ],
        ]);
    }
}