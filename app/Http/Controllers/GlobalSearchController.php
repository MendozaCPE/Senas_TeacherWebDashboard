<?php

namespace App\Http\Controllers;

use App\Models\GestureMedia;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\TeacherMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends Controller
{
    /**
     * Return matching students, lessons, and media for the global navbar search bar.
     */
    public function suggestions(Request $request)
    {
        $query = trim($request->input('q', ''));
        if (mb_strlen($query) < 1) {
            return response()->json(['students' => [], 'lessons' => [], 'media' => []]);
        }

        $user      = Auth::user();
        $teacher   = $user?->teacher;
        $teacherId = $teacher?->id;

        // ── 1. Students ───────────────────────────────────────────────────────
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
            $avatar   = "https://ui-avatars.com/api/?name=" . urlencode($s->initials) . "&background=0d326b&color=fff&rounded=true&size=64&bold=true&font-size=0.45";
            $subtitle = "LRN: " . ($s->lrn ?? 'N/A');
            if ($s->grade_level) $subtitle .= " • " . $s->grade_level;
            if ($s->school_year) $subtitle .= " ({$s->school_year})";

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

        // ── 2. Lessons ────────────────────────────────────────────────────────
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
            $subtitle = ucfirst($l->difficulty ?? 'beginner') . ' • ' . ucfirst($l->lesson_type ?? 'interactive') . ' Lesson';
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

        // ── 3. Media ──────────────────────────────────────────────────────────
        // System media — match gesture display_name or file_name
        $systemMedia = GestureMedia::with(['gesture', 'module'])
            ->where(function ($q) use ($query) {
                $q->where('file_name', 'like', "%{$query}%")
                  ->orWhereHas('gesture', fn ($g) =>
                        $g->where('display_name', 'like', "%{$query}%")
                          ->orWhere('name', 'like', "%{$query}%")
                  );
            })
            ->limit(3)
            ->get()
            ->map(function ($m) {
                $title = $m->gesture
                    ? ($m->gesture->display_name ?? $m->gesture->name)
                    : $m->file_name;
                return [
                    'type'       => 'media',
                    'source'     => 'system',
                    'title'      => $title,
                    'subtitle'   => ($m->module ? $m->module->display_name . ' • ' : '') . strtoupper($m->media_type),
                    'badge'      => 'System',
                    'media_type' => $m->media_type,
                    'url'        => route('media.index'),
                    'thumb'      => asset('storage/' . $m->file_path),
                ];
            });

        // Teacher's own uploads only
        $uploadedMedia = collect();
        if ($teacherId) {
            $uploadedMedia = TeacherMedia::where('teacher_id', $teacherId)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('file_name', 'like', "%{$query}%");
                })
                ->limit(3)
                ->get()
                ->map(function ($m) {
                    return [
                        'type'       => 'media',
                        'source'     => 'uploaded',
                        'title'      => $m->title,
                        'subtitle'   => 'My Upload • ' . strtoupper($m->media_type),
                        'badge'      => 'Uploaded',
                        'media_type' => $m->media_type,
                        'url'        => route('media.index'),
                        'thumb'      => asset('storage/' . $m->file_path),
                    ];
                });
        }

        $mediaResults = $systemMedia->concat($uploadedMedia)->take(5);

        return response()->json([
            'students' => $formattedStudents,
            'lessons'  => $formattedLessons,
            'media'    => $mediaResults,
        ]);
    }
}
