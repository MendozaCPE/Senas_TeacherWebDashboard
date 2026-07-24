<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentLessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

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
            $studentIds = Student::where('teacher_id', $teacherId)->pluck('student_id');

            $students = Student::where('teacher_id', $teacherId)
                ->orderBy('first_name')
                ->get();

            $lessons = Lesson::where('teacher_id', $teacherId)
                ->orderBy('module_order')
                ->get();

            // Read filters from session (set via POST, never from URL)
            $filters       = session('reports_filters', []);
            $filterStudent = $filters['student_id'] ?? 'all';
            $filterLesson  = $filters['lesson_id']  ?? 'all';

            $query = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->with(['student', 'lesson']);

            if ($filterStudent !== 'all') {
                $query->where('student_id', (int) $filterStudent);
            }
            if ($filterLesson !== 'all') {
                $query->where('lesson_id', (int) $filterLesson);
            }

            $allRows = $query->orderBy('last_accessed_at', 'desc')->get();


            $totalSteps = 7; // avg lesson steps, used for step-progress %

            // Collapse many student×lesson rows into ONE summary row per student.
            $studentReports = $allRows
                ->groupBy('student_id')
                ->map(function ($rows, $studentId) use ($totalSteps) {
                    $student = $rows->first()->student;

                    $lessonBreakdown = $rows->map(function ($row) use ($totalSteps) {
                        $stepPct = $totalSteps > 0
                            ? min(100, round(($row->current_step / $totalSteps) * 100))
                            : 0;

                        return [
                            'lessonTitle'   => optional($row->lesson)->title ?? '—',
                            'lessonType'    => optional($row->lesson)->lesson_type ?? '',
                            'stepPct'       => $stepPct,
                            'completed'     => (bool) $row->lesson_completed,
                            'quizCompleted' => (bool) $row->quiz_completed,
                            'quizScore'     => $row->quiz_score,
                            'lastAccessed'  => $row->last_accessed_at
                                ? Carbon::parse($row->last_accessed_at)->diffForHumans()
                                : '—',
                        ];
                    })->values();

                    $totalLessons     = $rows->count();
                    $completedLessons = $rows->where('lesson_completed', 1)->count();
                    $quizzesTaken     = $rows->where('quiz_completed', 1)->count();
                    $avgScore         = $rows->where('quiz_completed', 1)->avg('quiz_score') ?? 0;
                    $overallPct       = $totalLessons > 0
                        ? round(($completedLessons / $totalLessons) * 100)
                        : 0;
                    $lastActiveRaw    = $rows->sortByDesc('last_accessed_at')->first()->last_accessed_at;

                    return [
                        'student_id'       => $studentId,
                        'studentName'      => trim(optional($student)->first_name . ' ' . optional($student)->last_name) ?: 'Unknown Student',
                        'gradeLevel'       => optional($student)->grade_level ?? 'N/A',
                        'initials'         => $student
                            ? strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1))
                            : '??',
                        'totalLessons'     => $totalLessons,
                        'completedLessons' => $completedLessons,
                        'quizzesTaken'     => $quizzesTaken,
                        'avgScore'         => round($avgScore, 1),
                        'overallPct'       => $overallPct,
                        'lastAccessed'     => $lastActiveRaw
                            ? Carbon::parse($lastActiveRaw)->diffForHumans()
                            : '—',
                        'lessons'          => $lessonBreakdown,
                    ];
                })
                ->sortBy('studentName')
                ->values();
        }

        return view('reports', compact(
            'students',
            'lessons',
            'studentReports',
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
        $studentIds = Student::where('teacher_id', $teacherId)->pluck('student_id');
        $students   = Student::where('teacher_id', $teacherId)->orderBy('first_name')->get();
        $lessons    = Lesson::where('teacher_id', $teacherId)->orderBy('module_order')->get();

        $filterStudent = $request->get('student_id', 'all');
        $filterLesson  = $request->get('lesson_id', 'all');

        $query = StudentLessonProgress::whereIn('student_id', $studentIds)
            ->with(['student', 'lesson']);
        if ($filterStudent !== 'all') $query->where('student_id', $filterStudent);
        if ($filterLesson  !== 'all') $query->where('lesson_id', $filterLesson);

        $allRows = $query->orderBy('student_id')->orderBy('lesson_id')->get();

        $totalSteps = 7;

        // Group into one clean block per student — same shape the web
        // Reports page already uses, so the PDF reads the same way.
        $studentReports = $allRows
            ->groupBy('student_id')
            ->map(function ($rows, $studentId) use ($totalSteps) {
                $student = $rows->first()->student;

                $lessonBreakdown = $rows->map(function ($row) use ($totalSteps) {
                    return [
                        'lessonTitle'   => optional($row->lesson)->title ?? '—',
                        'difficulty'    => optional($row->lesson)->difficulty ?? '—',
                        'completed'     => (bool) $row->lesson_completed,
                        'quizCompleted' => (bool) $row->quiz_completed,
                        'quizScore'     => $row->quiz_score,
                        'currentStep'   => $row->current_step,
                        'totalSteps'    => $totalSteps,
                        'lastAccessed'  => $row->last_accessed_at
                            ? Carbon::parse($row->last_accessed_at)->format('M d, Y')
                            : '—',
                    ];
                })->values();

                $totalLessons     = $rows->count();
                $completedLessons = $rows->where('lesson_completed', 1)->count();
                $quizzesTaken     = $rows->where('quiz_completed', 1)->count();
                $avgScore         = $rows->where('quiz_completed', 1)->avg('quiz_score') ?? 0;
                $overallPct       = $totalLessons > 0
                    ? round(($completedLessons / $totalLessons) * 100)
                    : 0;
                $lastActiveRaw    = $rows->sortByDesc('last_accessed_at')->first()->last_accessed_at;

                return [
                    'studentName'      => trim(optional($student)->first_name . ' ' . optional($student)->last_name) ?: 'Unknown Student',
                    'gradeLevel'       => optional($student)->grade_level ?? 'N/A',
                    'totalLessons'     => $totalLessons,
                    'completedLessons' => $completedLessons,
                    'quizzesTaken'     => $quizzesTaken,
                    'avgScore'         => round($avgScore, 1),
                    'overallPct'       => $overallPct,
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

        $pdf = Pdf::loadView('pdf.report', compact(
            'studentReports',
            'teacher',
            'teacherName',
            'schoolName',
            'generatedAt',
            'totalStudents',
            'totalCompleted',
            'totalProgress',
            'completionPct',
            'avgScore',
            'selectedStudentName',
            'selectedLessonName'
        ))->setPaper('a4', 'portrait');

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

        // Reuse the exact same data-building logic as the web Analytics
        // page (AnalyticsController@index), so the PDF and the web page
        // always show matching numbers and charts.
        $data = (new AnalyticsController())->buildAnalyticsData($teacher, $request);

        $data['teacher']     = $teacher;
        $data['teacherName'] = $teacher->first_name . ' ' . $teacher->last_name;
        $data['schoolName']  = optional($teacher->school)->name ?? 'School';
        $data['generatedAt'] = Carbon::now()->format('F d, Y · g:i A');

        $pdf = Pdf::loadView('pdf.analytics', $data)->setPaper('a4', 'portrait');

        return $pdf->download('senas-analytics-' . now()->format('Y-m-d') . '.pdf');
    }
}