<?php

namespace App\Http\Controllers;

use App\Models\GestureMedia;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherMedia;
use App\Services\LessonTemplateService;
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

        if ($user?->role === 'grade_leader') {
            return $this->gradeLeaderSuggestions($query, $teacher?->school_id);
        }

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
                'url'      => route('students', ['search' => $fullName, 'open_student' => $s->student_id]),
                'details_url' => route('students', ['search' => $fullName, 'open_student' => $s->student_id]),
                'performance_url' => url('/reports?open_student=' . $s->student_id),
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
            'teachers' => [],
            'students' => $formattedStudents,
            'lessons'  => $formattedLessons,
            'media'    => $mediaResults,
        ]);
    }

    private function gradeLeaderSuggestions(string $query, ?int $schoolId)
    {
        if (!$schoolId) {
            return response()->json(['teachers' => [], 'students' => [], 'lessons' => [], 'media' => []]);
        }

        $teachers = Teacher::query()
            ->with('user')
            ->where('school_id', $schoolId)
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$query}%")
                    ->orWhere('specialization', 'like', "%{$query}%");
            })
            ->orderBy('first_name')
            ->limit(5)
            ->get();

        $formattedTeachers = $teachers->map(function ($teacher) {
            $name = trim($teacher->first_name . ' ' . $teacher->last_name);
            return [
                'id' => $teacher->id,
                'type' => 'teacher',
                'title' => $name,
                'subtitle' => $teacher->specialization ?: 'Classroom teacher',
                'badge' => 'Teacher',
                'avatar' => $teacher->user?->avatarUrl()
                    ?? 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0d326b&color=fff&size=64&bold=true&rounded=true',
                'url' => route('grade-leader.reports', ['open_teacher' => $teacher->id]),
            ];
        });

        $students = Student::query()
            ->with('teacher')
            ->where('school_id', $schoolId)
            ->whereHas('teacher', fn ($teacher) => $teacher->where('school_id', $schoolId))
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$query}%")
                    ->orWhere('lrn', 'like', "%{$query}%");
            })
            ->orderBy('first_name')
            ->limit(5)
            ->get();

        $formattedStudents = $students->map(function ($student) {
            $name = trim($student->first_name . ' ' . $student->last_name);
            $teacherName = $student->teacher
                ? trim($student->teacher->first_name . ' ' . $student->teacher->last_name)
                : 'No assigned teacher';
            return [
                'id' => $student->student_id,
                'type' => 'student',
                'title' => $name,
                'subtitle' => 'Class: ' . $teacherName . ($student->grade_level ? ' · Grade ' . $student->grade_level : ''),
                'badge' => $student->fsl_mastery_level ?: 'Student',
                'avatar' => $student->avatarUrl(),
                'url' => $student->teacher_id
                    ? route('grade-leader.reports', ['open_teacher' => $student->teacher_id, 'open_student' => $student->student_id])
                    : route('grade-leader.reports'),
            ];
        });

        $templateTeacherId = app(LessonTemplateService::class)->templateTeacherId();
        $lessons = Lesson::query()
            ->where('teacher_id', $templateTeacherId)
            ->where('is_template', true)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('difficulty', 'like', "%{$query}%")
                    ->orWhere('lesson_type', 'like', "%{$query}%");
            })
            ->orderBy('module_order')
            ->limit(5)
            ->get()
            ->map(fn ($lesson) => [
                'id' => $lesson->lesson_id,
                'type' => 'lesson',
                'title' => $lesson->title,
                'subtitle' => ucfirst($lesson->difficulty ?? 'beginner') . ' · ' . ucfirst($lesson->lesson_type ?? 'interactive') . ' lesson',
                'badge' => ucfirst($lesson->status ?? 'draft'),
                'url' => route('grade-leader.lessons', ['open_lesson' => $lesson->lesson_id]),
            ]);

        $media = GestureMedia::with(['gesture', 'module'])
            ->where(function ($q) use ($query) {
                $q->where('file_name', 'like', "%{$query}%")
                    ->orWhereHas('gesture', fn ($gesture) => $gesture
                        ->where('display_name', 'like', "%{$query}%")
                        ->orWhere('name', 'like', "%{$query}%"));
            })
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $title = $item->gesture
                    ? ($item->gesture->display_name ?? $item->gesture->name)
                    : ($item->display_name ?: $item->file_name);
                return [
                    'id' => $item->media_id,
                    'type' => 'media',
                    'source' => 'system',
                    'title' => $title,
                    'subtitle' => ($item->module?->display_name ? $item->module->display_name . ' · ' : '') . strtoupper($item->media_type),
                    'badge' => 'System',
                    'media_type' => $item->media_type,
                    'thumb' => asset('storage/' . $item->file_path),
                    'url' => route('grade-leader.media', ['open_media' => $item->media_id]),
                ];
            });

        return response()->json([
            'teachers' => $formattedTeachers,
            'students' => $formattedStudents,
            'lessons' => $lessons,
            'media' => $media,
        ]);
    }
}
