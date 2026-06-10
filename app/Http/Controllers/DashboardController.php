<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentLessonProgress;
use Illuminate\Support\Facades\Auth;
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
            $displayName = $user->name;
        }

        // Default stats (fallback if no teacher record)
        $totalStudents = 0;
        $activeToday = 0;
        $lessonsCompleted = 0;
        $students = collect();

        if ($teacher) {
            $teacherId = $teacher->id;
            
            // Total students for this teacher
            $totalStudents = Student::where('teacher_id', $teacherId)->count();
            
            // Get student IDs
            $studentIds = Student::where('teacher_id', $teacherId)->pluck('student_id');

            // Active today
            $activeToday = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->whereDate('last_accessed_at', Carbon::today())
                ->distinct('student_id')
                ->count('student_id');

            // Lessons completed
            $lessonsCompleted = StudentLessonProgress::whereIn('student_id', $studentIds)
                ->where('lesson_completed', 1)
                ->count();
                
            // Recent students to list
            $students = Student::where('teacher_id', $teacherId)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }

        return view('dashboard', compact(
            'displayName', 
            'user', 
            'teacher', 
            'totalStudents', 
            'activeToday', 
            'lessonsCompleted',
            'students'
        ));
    }
}
