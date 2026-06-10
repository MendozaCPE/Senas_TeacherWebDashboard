<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentsController extends Controller
{
    public function index()
    {
        $teacher = Auth::user()->teacher;
        
        $totalStudents = 0;
        $newThisWeek = 0;
        $students = collect();
        
        if ($teacher) {
            $totalStudents = Student::where('teacher_id', $teacher->id)->count();
            $newThisWeek = Student::where('teacher_id', $teacher->id)
                                  ->where('created_at', '>=', Carbon::now()->subWeek())
                                  ->count();
                                  
            // Get all students for this teacher, ideally paginate them
            $students = Student::where('teacher_id', $teacher->id)
                               ->orderBy('created_at', 'desc')
                               ->paginate(10);
        }
        
        return view('students', compact('totalStudents', 'newThisWeek', 'students'));
    }
}
