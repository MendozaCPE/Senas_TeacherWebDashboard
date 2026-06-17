<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentLessonProgress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return view('analytics', [
                'totalAttempts'    => 0,
                'avgPerformance'   => 0,
                'practiceCompletion' => 0,
                'activeStudents'   => 0,
                'lessonCompletion' => collect(),
                'topPerformer'     => null,
                'weeklyData'       => [],
                'alerts'           => collect(),
                'displayName'      => $user->name ?? 'Teacher',
                'teacher'          => null,
            ]);
        }

        $teacherId  = $teacher->id;
        $studentIds = Student::where('teacher_id', $teacherId)->pluck('student_id');
        $totalStudents = $studentIds->count();

        // ── Stat 1: Total Quiz Attempts ──────────────────────────────────────────
        $totalAttempts = DB::table('quiz_attempts')
            ->whereIn('student_id', $studentIds)
            ->count();

        // ── Stat 2: Average Performance (avg quiz percentage across attempts) ────
        $avgPerformance = DB::table('quiz_attempts')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->avg('percentage') ?? 0;

        // ── Stat 3: Practice Completion (% of progress records that are complete) ─
        $totalProgress  = StudentLessonProgress::whereIn('student_id', $studentIds)->count();
        $totalCompleted = StudentLessonProgress::whereIn('student_id', $studentIds)
            ->where('lesson_completed', 1)->count();
        $practiceCompletion = $totalProgress > 0
            ? round(($totalCompleted / $totalProgress) * 100, 1)
            : 0;

        // Active students (any progress in last 7 days)
        $activeStudents = StudentLessonProgress::whereIn('student_id', $studentIds)
            ->where('last_accessed_at', '>=', Carbon::now()->subDays(7))
            ->distinct('student_id')
            ->count('student_id');

        // ── Lesson Completion Rates ──────────────────────────────────────────────
        $lessonCompletion = Lesson::where('teacher_id', $teacherId)
            ->orderBy('module_order')
            ->get()
            ->map(function ($lesson) use ($studentIds, $totalStudents) {
                $enrolled = StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->where('lesson_id', $lesson->lesson_id)
                    ->distinct('student_id')
                    ->count('student_id');

                $completed = StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->where('lesson_id', $lesson->lesson_id)
                    ->where('lesson_completed', 1)
                    ->count();

                $pct = $totalStudents > 0 ? round(($completed / $totalStudents) * 100) : 0;

                $lesson->enrolledCount  = $enrolled;
                $lesson->completedCount = $completed;
                $lesson->completionPct  = $pct;
                return $lesson;
            });

        // ── Weekly Progress Data (last 7 days, completions per day) ─────────────
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date  = Carbon::now()->subDays($i);
            $label = $date->format('D'); // Mon, Tue…
            $count = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereDate('last_accessed_at', $date->toDateString())
                ->count();
            $weeklyData[] = ['label' => $label, 'count' => $count, 'date' => $date->format('M d')];
        }

        // ── Top Performer ────────────────────────────────────────────────────────
        $topPerformer = Student::where('teacher_id', $teacherId)
            ->get()
            ->map(function ($student) {
                $progress  = $student->progress;
                $completed = $progress->where('lesson_completed', 1)->count();
                $avgScore  = $progress->whereNotNull('quiz_score')->avg('quiz_score') ?? 0;
                $student->completedLessons = $completed;
                $student->avgScore = round($avgScore);
                return $student;
            })
            ->sortByDesc('completedLessons')
            ->first();

        // ── Alerts: students who haven't accessed anything in 5+ days ───────────
        $recentStudentIds = StudentLessonProgress::whereIn('student_id', $studentIds)
            ->where('last_accessed_at', '>=', Carbon::now()->subDays(5))
            ->distinct('student_id')
            ->pluck('student_id');

        $inactiveStudents = Student::where('teacher_id', $teacherId)
            ->whereIn('student_id', $studentIds)
            ->whereNotIn('student_id', $recentStudentIds)
            ->get();

        $quizFailStudents = Student::where('teacher_id', $teacherId)
            ->whereIn('student_id',
                StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->whereNotNull('quiz_score')
                    ->where('quiz_score', '<', 3)
                    ->pluck('student_id')
            )->get();

        $alerts = collect();
        foreach ($inactiveStudents as $s) {
            $alerts->push([
                'type'    => 'inactive',
                'color'   => 'yellow',
                'title'   => $s->first_name . ' ' . $s->last_name . ' hasn\'t practiced recently',
                'sub'     => 'No activity in the last 5 days',
                'time'    => 'Needs attention',
            ]);
        }
        foreach ($quizFailStudents as $s) {
            $alerts->push([
                'type'    => 'quiz_fail',
                'color'   => 'red',
                'title'   => $s->first_name . ' ' . $s->last_name . ' has a low quiz score',
                'sub'     => 'Quiz score below 60% — may need review',
                'time'    => 'Performance drop',
            ]);
        }
        if ($alerts->isEmpty()) {
            $alerts->push([
                'type'  => 'positive',
                'color' => 'green',
                'title' => 'All students are on track!',
                'sub'   => 'No alerts at this time.',
                'time'  => 'Just now',
            ]);
        }

        $displayName = $teacher->first_name . ' ' . $teacher->last_name;

        return view('analytics', compact(
            'totalAttempts',
            'avgPerformance',
            'practiceCompletion',
            'activeStudents',
            'lessonCompletion',
            'topPerformer',
            'weeklyData',
            'alerts',
            'displayName',
            'teacher',
            'totalStudents'
        ));
    }
}
