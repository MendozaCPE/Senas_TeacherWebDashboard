<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Student;
use App\Models\StudentLessonProgress;
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

        $students       = collect();
        $lessons        = collect();
        $studentReports = collect();

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
                $query->where('lesson_id', (int) $filterLesson);
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

            // Determine which lessons to show per student
            $lessonsToShow = $filterLesson !== 'all'
                ? $lessons->where('lesson_id', (int) $filterLesson)
                : $lessons;

            $groupedRows = $allRows->groupBy('student_id');

            $totalSteps = 7; // avg lesson steps, used for step-progress %

            // Map over all selected active students to build the summary rows.
            $studentReports = $studentsToReport
                ->map(function ($student) use ($groupedRows, $lessonsToShow, $totalSteps, $gestureByStudent, $lessons) {
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
                            'lessonType'    => $lesson->lesson_type ?? '',
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

                    $totalLessons     = $lessonsToShow->count();
                    $completedLessons = $rows->where('lesson_completed', 1)->count();
                    
                    // Count quizzes taken and quizzes passed
                    $completedQuizRows = $rows->where('quiz_completed', 1);
                    $quizzesTaken      = $completedQuizRows->count();
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
                    $quizPassRate     = $quizzesTaken > 0 ? round(($quizzesPassed / $quizzesTaken) * 100, 1) : 0;
                    $avgScore         = $completedQuizRows->avg('quiz_score') ?? 0;
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
                        'initials'         => strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) ?: '??',
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
                        'lessons'          => $lessonBreakdown,
                    ];
                })
                ->sortBy('studentName')
                ->values();
        } else {
            $studentGesturePerformance = collect();
        }

        return view('reports', compact(
            'students',
            'lessons',
            'studentReports',
            'studentGesturePerformance',
            'teacher'
        ));
    }

    /**
     * Accept filter POST → validate → store in session → redirect to clean /reports URL.
     * Using integer-only validation for IDs prevents any injection via those fields.
     */
    public function applyFilter(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['nullable', 'string'],
            'lesson_id'  => ['nullable', 'string'],
        ]);

        // Normalise 'all' as empty
        $studentId = ($validated['student_id'] ?? 'all') === 'all' ? 'all' : (int) $validated['student_id'];
        $lessonId  = ($validated['lesson_id']  ?? 'all') === 'all' ? 'all' : (int) $validated['lesson_id'];

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

        $filterStudent = $request->get('student_id', 'all');
        $filterLesson  = $request->get('lesson_id', 'all');

        $lessonIds = $lessons->pluck('lesson_id');

        $query = StudentLessonProgress::whereIn('student_id', $studentIds)
            ->whereIn('lesson_id', $lessonIds)  // ← Only THIS teacher's lessons
            ->with(['student', 'lesson']);
        if ($filterStudent !== 'all') $query->where('student_id', $filterStudent);
        if ($filterLesson  !== 'all') $query->where('lesson_id', $filterLesson);

        $allRows = $query->orderBy('student_id')->orderBy('lesson_id')->get();

        $totalSteps = 7;

        $studentsToReport = $students;
        if ($filterStudent !== 'all') {
            $studentsToReport = $students->where('student_id', (int) $filterStudent);
        }

        // Determine which lessons to show per student
        $lessonsToShow = $filterLesson !== 'all'
            ? $lessons->where('lesson_id', (int) $filterLesson)
            : $lessons;

        $groupedRows = $allRows->groupBy('student_id');

        $totalSteps = 7;

        // Map over all selected active students for the PDF layout.
        $studentReports = $studentsToReport
            ->map(function ($student) use ($groupedRows, $lessonsToShow, $totalSteps, $lessons) {
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

                $totalLessons     = $lessonsToShow->count();
                $completedLessons = $rows->where('lesson_completed', 1)->count();
                
                $completedQuizRows = $rows->where('quiz_completed', 1);
                $quizzesTaken      = $completedQuizRows->count();
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
                $quizPassRate     = $quizzesTaken > 0 ? round(($quizzesPassed / $quizzesTaken) * 100, 1) : 0;
                $avgScore         = $completedQuizRows->avg('quiz_score') ?? 0;
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

                return [
                    'studentName'      => trim($student->first_name . ' ' . $student->last_name) ?: 'Unknown Student',
                    'gradeLevel'       => $student->grade_level ?? 'N/A',
                    'totalLessons'     => $totalLessons,
                    'completedLessons' => $completedLessons,
                    'quizzesTaken'     => $quizzesTaken,
                    'quizzesPassed'    => $quizzesPassed,
                    'quizPassRate'     => $quizPassRate,
                    'avgScore'         => round($avgScore, 1),
                    'overallPct'       => $overallPct,
                    'gestureAccuracy'  => $gestureAccuracy,
                    'lastAccessed'     => $lastActiveRaw
                        ? Carbon::parse($lastActiveRaw)->format('M d, Y')
                        : '—',
                    'lessons'          => $lessonBreakdown,
                ];
            })
            ->sortBy('studentName')
            ->values();

        // Overall summary stats (top strip) — computed across all filtered rows/students.
        $totalStudents  = $studentReports->count();
        $totalProgress  = $allRows->count();
        $totalCompleted = $allRows->where('lesson_completed', 1)->count();
        $completionPct  = $totalProgress > 0 ? round(($totalCompleted / $totalProgress) * 100) : 0;
        $avgScore       = $allRows->where('quiz_completed', 1)->avg('quiz_score') ?? 0;
        $activeLearners = $studentReports->where('overallPct', '>', 0)->count();

        $generatedAt   = Carbon::now()->format('F d, Y · g:i A');
        $schoolName    = optional($teacher->school)->name ?? 'School';
        $teacherName   = $teacher->first_name . ' ' . $teacher->last_name;

        $selectedStudentName = 'All Students';
        $selectedLessonName  = 'All Lessons';
        if ($filterStudent !== 'all') {
            $s = $students->firstWhere('student_id', $filterStudent);
            if ($s) $selectedStudentName = $s->first_name . ' ' . $s->last_name;
        }
        if ($filterLesson !== 'all') {
            $l = $lessons->firstWhere('lesson_id', $filterLesson);
            if ($l) $selectedLessonName = $l->title;
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
                    $student['gestureAccuracy'] ?? 0
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
}