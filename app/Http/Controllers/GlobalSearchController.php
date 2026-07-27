<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends Controller
{
    /**
     * Return matching students and lessons for the global navbar search bar.
     */
    public function suggestions(Request $request)
    {
        $query = trim($request->input('q', ''));
        if (mb_strlen($query) < 1) {
            return response()->json(['students' => [], 'lessons' => []]);
        }

        $user      = Auth::user();
        $teacher   = $user?->teacher;
        $teacherId = $teacher?->id;

        // ── 1. Search Students ────────────────────────────────────────────────
        $studentQuery = Student::query();
        if ($teacherId) {
            $studentQuery->where('teacher_id', $teacherId);
        }

        $students = $studentQuery->where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%")
              ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$query}%")
              ->orWhere('lrn', 'like', "%{$query}%")
              ->orWhere('grade_level', 'like', "%{$query}%");
        })
        ->orderBy('first_name')
        ->limit(5)
        ->get();

        $formattedStudents = $students->map(function ($s) {
            $fullName = trim($s->first_name . ' ' . $s->last_name);
            $avatar   = "https://ui-avatars.com/api/?name=" . urlencode($fullName) . "&background=0d326b&color=fff&rounded=true&size=64";
            
            $subtitle = "LRN: " . ($s->lrn ?? 'N/A');
            if ($s->grade_level) {
                $subtitle .= " • " . $s->grade_level;
            }
            if ($s->school_year) {
                $subtitle .= " ({$s->school_year})";
            }

            return [
                'id'       => $s->student_id,
                'type'     => 'student',
                'title'    => $fullName,
                'subtitle' => $subtitle,
                'badge'    => $s->fsl_mastery_level ?? 'Beginner',
                'status'   => $s->status ?? 'active',
                'avatar'   => $avatar,
                'url'      => route('students', ['search' => $fullName, 'highlight' => $s->student_id]),
            ];
        });

        // ── 2. Search Lessons ─────────────────────────────────────────────────
        $lessonQuery = Lesson::query();
        if ($teacherId) {
            $lessonQuery->where('teacher_id', $teacherId);
        }

        $lessons = $lessonQuery->where(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%")
              ->orWhere('difficulty', 'like', "%{$query}%")
              ->orWhere('lesson_type', 'like', "%{$query}%");
        })
        ->orderBy('updated_at', 'desc')
        ->limit(5)
        ->get();

        $formattedLessons = $lessons->map(function ($l) {
            $typeFormatted = ucfirst($l->lesson_type ?? 'interactive');
            $diffFormatted = ucfirst($l->difficulty ?? 'beginner');
            $subtitle = "{$diffFormatted} • {$typeFormatted} Lesson";

            return [
                'id'       => $l->lesson_id,
                'hash_id'  => $l->hash_id,
                'type'     => 'lesson',
                'title'    => $l->title,
                'subtitle' => $subtitle,
                'badge'    => ucfirst($l->status ?? 'draft'),
                'url'      => route('lessons.view', $l->hash_id),
            ];
        });

        return response()->json([
            'students' => $formattedStudents,
            'lessons'  => $formattedLessons,
        ]);
    }
}
