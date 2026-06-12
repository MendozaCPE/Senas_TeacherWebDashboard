<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentLessonProgress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        // Build a display name
        if ($teacher && $teacher->first_name) {
            $displayName = $teacher->first_name . ($teacher->last_name ? ' ' . $teacher->last_name : '');
        } else {
            $displayName = $user->name ?? 'Teacher';
        }

        // Default stats (fallback if no teacher record)
        $totalStudents    = 0;
        $activeToday      = 0;
        $lessonsCompleted = 0;
        $students         = collect();
        $lessons          = collect();
        $lessonMastery    = collect();
        $classRate        = 0;
        $needsAttention   = collect();
        $topLesson        = null;

        if ($teacher) {
            $teacherId  = $teacher->id;
            $studentIds = Student::where('teacher_id', $teacherId)->pluck('student_id');

            // ── Stat Cards ───────────────────────────────────────────────────────
            $totalStudents = $studentIds->count();

            $activeToday = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereDate('last_accessed_at', Carbon::today())
                ->distinct('student_id')
                ->count('student_id');

            $lessonsCompleted = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->where('lesson_completed', 1)
                ->count();

            // ── Your Lessons (with enrolled count + completion %) ────────────────
            $lessons = Lesson::where('teacher_id', $teacherId)
                ->orderBy('module_order')
                ->take(3)
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
            $totalProgress = StudentLessonProgress::whereIn('student_id', $studentIds)->count();
            $totalCompleted = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->where('lesson_completed', 1)
                ->count();
            $classRate = $totalProgress > 0 ? round(($totalCompleted / $totalProgress) * 100) : 0;

            // ── Student Performance (5 most recent, with avg quiz score as proxy) ─
            $students = Student::where('teacher_id', $teacherId)
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

            // ── Needs Attention: students with 0% completion on any started lesson ─
            // or students with quiz_score below 50 (struggling)
            $needsAttentionIds = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->where('lesson_completed', 0)
                ->where(function ($q) {
                    $q->where('quiz_score', '<', 50)
                      ->orWhereNotNull('last_accessed_at');
                })
                ->distinct('student_id')
                ->pluck('student_id');

            $needsAttention = Student::whereIn('student_id', $needsAttentionIds)
                ->take(5)
                ->get()
                ->map(function ($student) {
                    $hasLowScore = $student->progress()
                        ->where('quiz_score', '<', 50)
                        ->whereNotNull('quiz_score')
                        ->exists();
                    $student->alertType = $hasLowScore ? 'Alert' : 'Review';
                    return $student;
                });

            // ── Top Lesson Insight ────────────────────────────────────────────────
            $topLesson = $lessonMastery->sortByDesc('masteryPct')->first();
        }

        // Circumference for SVG circle stroke: 2 * pi * r = 2 * 3.14159 * 64 ≈ 402
        $circleCircumference = 402;
        $circleDashOffset    = $classRate > 0
            ? round($circleCircumference * (1 - $classRate / 100))
            : $circleCircumference;

        return view('dashboard', compact(
            'displayName',
            'user',
            'teacher',
            'totalStudents',
            'activeToday',
            'lessonsCompleted',
            'students',
            'lessons',
            'lessonMastery',
            'classRate',
            'circleDashOffset',
            'needsAttention',
            'topLesson'
        ));
    }
}
