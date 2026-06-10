<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;

class LessonsController extends Controller
{
    public function index()
    {
        $teacher = Auth::user()->teacher;
        
        $lessons = collect();
        if ($teacher) {
            $lessons = Lesson::where('teacher_id', $teacher->id)
                             ->orderBy('module_order', 'asc')
                             ->get();
        }
        
        return view('lessons', compact('lessons'));
    }
}
