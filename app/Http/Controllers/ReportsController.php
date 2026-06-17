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
    public function index(Request $request)
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        $students = collect();
        $lessons  = collect();
        $reportRows = collect();

        if ($teacher) {
            $teacherId  = $teacher->id;
            $studentIds = Student::where('teacher_id', $teacherId)->pluck('student_id');

            $students = Student::where('teacher_id', $teacherId)
                ->orderBy('first_name')
                ->get();

            $lessons = Lesson::where('teacher_id', $teacherId)
                ->orderBy('module_order')
                ->get();

            // Filters
            $filterStudent = $request->get('student_id', 'all');
            $filterLesson  = $request->get('lesson_id', 'all');

            // Build report rows (student × lesson progress)
            $query = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->with(['student', 'lesson']);

            if ($filterStudent !== 'all') {
                $query->where('student_id', $filterStudent);
            }
            if ($filterLesson !== 'all') {
                $query->where('lesson_id', $filterLesson);
            }

            $reportRows = $query->orderBy('last_accessed_at', 'desc')->get()->map(function ($row) {
                $row->studentName = optional($row->student)->first_name . ' ' . optional($row->student)->last_name;
                $row->lessonTitle = optional($row->lesson)->title ?? '—';
                $row->statusLabel = $row->lesson_completed ? 'Completed' : 'In Progress';
                $row->quizLabel   = $row->quiz_completed
                    ? ($row->quiz_score . '/' . (optional($row->lesson)->quiz_total ?? '?') . ' pts')
                    : 'Not taken';
                $row->lastAccessed = Carbon::parse($row->last_accessed_at)->diffForHumans();
                return $row;
            });
        }

        return view('reports', compact(
            'students',
            'lessons',
            'reportRows',
            'teacher'
        ));
    }

    /**
     * Export a professional PDF report.
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

        $reportRows = $query->orderBy('student_id')->orderBy('lesson_id')->get()->map(function ($row) {
            $row->studentName  = optional($row->student)->first_name . ' ' . optional($row->student)->last_name;
            $row->lessonTitle  = optional($row->lesson)->title ?? '—';
            $row->difficulty   = optional($row->lesson)->difficulty ?? '—';
            $row->statusLabel  = $row->lesson_completed ? 'Completed' : 'In Progress';
            $row->quizLabel    = $row->quiz_completed
                ? ($row->quiz_score . ' pts')
                : ($row->quiz_completed === 0 && $row->lesson_completed === 1 ? 'Not taken' : 'Pending');
            $row->lastAccessed = Carbon::parse($row->last_accessed_at)->format('M d, Y');
            return $row;
        });

        // Summary stats
        $totalStudents  = $students->count();
        $totalCompleted = $reportRows->where('lesson_completed', 1)->count();
        $totalProgress  = $reportRows->count();
        $completionPct  = $totalProgress > 0 ? round(($totalCompleted / $totalProgress) * 100) : 0;
        $avgScore       = $reportRows->where('quiz_completed', 1)->avg('quiz_score') ?? 0;

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
            'reportRows',
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
            'selectedLessonName',
            'lessons'
        ))->setPaper('a4', 'portrait');

        $filename = 'senas-report-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export Analytics PDF
     */
    public function exportAnalyticsPdf()
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) abort(403);

        $teacherId  = $teacher->id;
        $studentIds = Student::where('teacher_id', $teacherId)->pluck('student_id');
        $totalStudents = $studentIds->count();

        $lessonCompletion = Lesson::where('teacher_id', $teacherId)
            ->orderBy('module_order')
            ->get()
            ->map(function ($lesson) use ($studentIds, $totalStudents) {
                $completed = StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->where('lesson_id', $lesson->lesson_id)
                    ->where('lesson_completed', 1)->count();
                $enrolled  = StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->where('lesson_id', $lesson->lesson_id)
                    ->distinct('student_id')->count('student_id');
                $lesson->completionPct  = $totalStudents > 0 ? round(($completed / $totalStudents) * 100) : 0;
                $lesson->completedCount = $completed;
                $lesson->enrolledCount  = $enrolled;
                return $lesson;
            });

        $studentPerformance = Student::where('teacher_id', $teacherId)->get()->map(function ($s) {
            $prog = $s->progress;
            $s->completedLessons = $prog->where('lesson_completed', 1)->count();
            $s->avgScore = round($prog->whereNotNull('quiz_score')->avg('quiz_score') ?? 0);
            return $s;
        })->sortByDesc('completedLessons');

        $totalCompleted  = StudentLessonProgress::whereIn('student_id', $studentIds)->where('lesson_completed', 1)->count();
        $totalProgress   = StudentLessonProgress::whereIn('student_id', $studentIds)->count();
        $completionPct   = $totalProgress > 0 ? round(($totalCompleted / $totalProgress) * 100) : 0;
        $avgScore        = StudentLessonProgress::whereIn('student_id', $studentIds)
            ->whereNotNull('quiz_score')->avg('quiz_score') ?? 0;

        $teacherName  = $teacher->first_name . ' ' . $teacher->last_name;
        $schoolName   = optional($teacher->school)->name ?? 'School';
        $generatedAt  = Carbon::now()->format('F d, Y · g:i A');

        $pdf = Pdf::loadView('pdf.analytics', compact(
            'teacher', 'teacherName', 'schoolName', 'generatedAt',
            'totalStudents', 'totalCompleted', 'totalProgress', 'completionPct',
            'avgScore', 'lessonCompletion', 'studentPerformance'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('senas-analytics-' . now()->format('Y-m-d') . '.pdf');
    }
}
