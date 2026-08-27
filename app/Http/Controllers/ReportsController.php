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
                ->with('module')
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
                ->map(function ($student) use ($groupedRows, $lessonsToShow, $totalSteps, $gestureByStudent) {
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
                    $quizzesTaken     = $rows->where('quiz_completed', 1)->count();
                    $avgScore         = $rows->where('quiz_completed', 1)->avg('quiz_score') ?? 0;
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
            ->with('module')
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
            ->map(function ($student) use ($groupedRows, $lessonsToShow, $totalSteps) {
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
                $quizzesTaken     = $rows->where('quiz_completed', 1)->count();
                $avgScore         = $rows->where('quiz_completed', 1)->avg('quiz_score') ?? 0;
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

        // Merge session analytics filters so the PDF matches the web view
        $filters = session('analytics_filters', []);
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
            ['label' => 'Completion Rate','value' => number_format($data['completionRate'] ?? 0, 1) . '%'],
            ['label' => 'Avg Streak',      'value' => ($data['avgStreakDays'] ?? 0) . ' d'],
            ['label' => 'Active (Last 7d)','value' => number_format($data['activeLast7Pct'] ?? 0, 1) . '%'],
        ]);
        $pdf->Ln(2);

        /* ── Insight box ── */
        $pdf->insightBox(
            'Class Performance Summary',
            'As of ' . now()->format('F d, Y') . ', your class has ' . ($data['totalStudents'] ?? 0) . ' enrolled students. ' .
            'The average quiz score is ' . number_format($data['avgQuizScore'] ?? 0, 1) . '% and average gesture mastery is ' .
            number_format($data['avgMastery'] ?? 0, 1) . '%. Lesson completion rate stands at ' .
            number_format($data['completionRate'] ?? 0, 1) . '%.',
            'gold'
        );
        $pdf->Ln(2);

        /* ── Class Progress Over Time ── */
        $period       = $request->get('period', 'weekly');
        $year         = (int) $request->get('year', date('Y'));
        $progressPts  = array_values((array) ($data['progressOverTime'] ?? []));
        $periodLabel  = ucfirst($period) . ' average quiz score ' . $year;
        $countLabel   = count($progressPts) . ' ' . $period;
        $pdf->sectionTitle('Class Progress Over Time');
        $pdf->progressLineChart($progressPts, $periodLabel, $countLabel);
        $pdf->Ln(2);

        /* ── Module Difficulty Ranking ── */
        $pdf->sectionTitle('Module Difficulty Ranking');
        $pdf->moduleDifficultyList($data['lessonDifficulty'] ?? []);
        $pdf->Ln(2);

        /* ── Student Ranking (new page) ── */
        $pdf->AddPage();
        $pdf->sectionTitle('Student Ranking');
        if (!empty($data['studentRanking']) && count($data['studentRanking']) > 0) {
            $isOverall = !isset($data['studentRanking'][0]['best_score']);

            $rankHeaders = [
                ['label' => 'Rank',         'width' => 18,  'align' => 'C'],
                ['label' => 'Student Name', 'width' => 80,  'align' => 'L'],
                ['label' => $isOverall ? 'Quizzes' : 'Attempts', 'width' => 28,  'align' => 'C'],
                ['label' => $isOverall ? 'Avg Score' : 'Best Score', 'width' => 0,   'align' => 'C'],
            ];
            $rankRows = [];
            foreach ($data['studentRanking'] as $i => $s) {
                $rankLabel = ($i === 0) ? '1st' : (($i === 1) ? '2nd' : (($i === 2) ? '3rd' : '#' . ($i + 1)));
                
                $scoreVal = $isOverall ? ($s['overall_score'] ?? $s['avg_score'] ?? 0) : ($s['best_score'] ?? 0);
                $attemptVal = $isOverall ? ($s['quizzes_count'] ?? $s['lesson_count'] ?? 0) : ($s['attempts_to_achieve'] ?? 0);
                
                $rankRows[] = [
                    $rankLabel,
                    $s['name'],
                    $attemptVal,
                    number_format($scoreVal, 1) . '%',
                ];
            }
            $pdf->dataTable($rankHeaders, $rankRows);
        } else {
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(148, 163, 184);
            $pdf->Cell($usableW, 6, 'No quiz attempt data available yet.', 0, 1, 'C');
        }
        $pdf->Ln(4);

        /* ── Mastery Level Distribution ── */
        $pdf->sectionTitle('Mastery Level Distribution');
        if (!empty($data['masteryDistribution']) && ($data['masteryTotal'] ?? 0) > 0) {
            $mColors = [
                [239, 68, 68],   // red  — needs practice
                [245, 158, 11],  // amber — developing
                [59, 130, 246],  // blue  — proficient
                [16, 185, 129],  // green — mastered
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
            $pdf->Cell($usableW, 6, 'No mastery data available yet.', 0, 1, 'C');
        }
        $pdf->Ln(4);

        /* ── Completion Funnel ── */
        $pdf->sectionTitle('Lesson Completion Funnel');
        if (!empty($data['completionFunnel']) && ($data['completionTotal'] ?? 0) > 0) {
            $fColors = [
                [250, 204, 21],  // yellow — pending
                [59, 130, 246],  // blue   — in progress
                [16, 185, 129],  // green  — completed
                [239, 68, 68],   // red    — failed
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
            $pdf->Cell($usableW, 6, 'No lesson assignment data available yet.', 0, 1, 'C');
        }
        $pdf->Ln(4);

        /* ── Gesture Performance (new page) ── */
        $pdf->AddPage();
        $pdf->sectionTitle('Gesture Performance Analytics');

        /* Overview row */
        $gOverview = $data['gesturePerformanceOverview'] ?? [];
        if (!empty($gOverview) && ($gOverview['total_attempts'] ?? 0) > 0) {
            $pdf->summaryStrip([
                ['label' => 'Total Gestures',  'value' => (string) ($gOverview['total_gestures'] ?? 0)],
                ['label' => 'Total Attempts',  'value' => (string) ($gOverview['total_attempts'] ?? 0)],
                ['label' => 'Successful',       'value' => (string) ($gOverview['total_successful'] ?? 0)],
                ['label' => 'Overall Accuracy', 'value' => number_format($gOverview['overall_accuracy'] ?? 0, 1) . '%'],
                ['label' => 'Mastered',         'value' => (string) ($gOverview['total_mastered'] ?? 0)],
            ]);
            $pdf->Ln(4);
        }

        /* Best-performing gestures */
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(13, 50, 107);
        $pdf->Cell($usableW, 6, 'Best-Performing Gestures', 0, 1, 'L');
        $pdf->SetTextColor(51, 65, 85);
        if (!empty($data['topPerformingGestures']) && count($data['topPerformingGestures']) > 0) {
            foreach ($data['topPerformingGestures'] as $g) {
                $pdf->barRow(
                    $g['gesture_name'] . ' (' . $g['successful_attempts'] . '/' . $g['attempts'] . ')',
                    (float) $g['accuracy'],
                    100,
                    [16, 185, 129]  // green
                );
            }
        } else {
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(148, 163, 184);
            $pdf->Cell($usableW, 6, 'No gesture performance records available.', 0, 1, 'C');
        }
        $pdf->Ln(4);

        /* Lowest-performing gestures */
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(13, 50, 107);
        $pdf->Cell($usableW, 6, 'Struggling Gestures (Lowest Accuracy)', 0, 1, 'L');
        $pdf->SetTextColor(51, 65, 85);
        if (!empty($data['lowestPerformingGestures']) && count($data['lowestPerformingGestures']) > 0) {
            foreach ($data['lowestPerformingGestures'] as $g) {
                $pdf->barRow(
                    $g['gesture_name'] . ' (' . $g['successful_attempts'] . '/' . $g['attempts'] . ')',
                    (float) $g['accuracy'],
                    100,
                    [239, 68, 68]  // red
                );
            }
        } else {
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(148, 163, 184);
            $pdf->Cell($usableW, 6, 'No gesture performance records available.', 0, 1, 'C');
        }

        return $pdf->download('senas-analytics-' . now()->format('Y-m-d') . '.pdf');
    }
}