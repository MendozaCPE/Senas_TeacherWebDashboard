<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentNotification;
use App\Models\TeacherAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherAnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);

        if ($request->has('lesson_id') && !$request->has('lesson_ids')) {
            $request->merge(['lesson_ids' => [(int) $request->input('lesson_id')]]);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'lesson_ids' => ['required', 'array', 'min:1'],
            'lesson_ids.*' => ['required', 'integer', 'distinct'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'distinct'],
            'classroom_id' => ['nullable'],
        ]);

        $activeClassroomId = $request->input('classroom_id');
        if ($activeClassroomId === null || $activeClassroomId === '') {
            $activeClassroomId = session('active_classroom_id');
        }
        if ($activeClassroomId === 'all' || $activeClassroomId === '0') {
            $activeClassroomId = null;
        }

        $lessonIds = collect($data['lesson_ids'])->map(fn ($id) => (int) $id)->values();
        $lessons = Lesson::where('teacher_id', $teacher->id)
            ->whereIn('lesson_id', $lessonIds)
            ->get();

        if ($lessons->isEmpty()) {
            return back()->withErrors(['lesson_ids' => 'Select one or more valid lessons.'])->withInput();
        }

        $eligibleStudentIds = DB::table('lesson_assignments')
            ->join('students', 'students.student_id', '=', 'lesson_assignments.student_id')
            ->whereIn('lesson_assignments.lesson_id', $lessonIds)
            ->where('students.teacher_id', $teacher->id)
            ->where('students.is_enrolled', true)
            ->when($activeClassroomId, fn ($q, $cId) => $q->where('students.classroom_id', $cId))
            ->distinct()
            ->pluck('students.student_id');

        $selectedIds = collect($data['student_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        if ($selectedIds->isEmpty() || $selectedIds->diff($eligibleStudentIds)->isNotEmpty()) {
            return back()->withErrors(['student_ids' => 'Select one or more students assigned to the chosen lesson(s).'])->withInput();
        }

        $recipients = Student::where('teacher_id', $teacher->id)
            ->where('is_enrolled', true)
            ->when($activeClassroomId, fn ($q, $cId) => $q->where('classroom_id', $cId))
            ->whereIn('student_id', $selectedIds)
            ->get(['student_id']);

        if ($recipients->isEmpty()) {
            return back()->withErrors(['student_ids' => 'There are no enrolled students assigned to the chosen lesson(s).'])->withInput();
        }

        DB::transaction(function () use ($teacher, $data, $lessonIds, $recipients) {
            $announcement = TeacherAnnouncement::create([
                'teacher_id' => $teacher->id,
                'lesson_id' => $lessonIds->first(),
                'lesson_ids' => $lessonIds->all(),
                'title' => 'Announcement',
                'message' => $data['message'],
                'type' => 'lesson',
                'recipient_count' => $recipients->count(),
            ]);

            foreach ($recipients as $student) {
                StudentNotification::create([
                    'student_id' => $student->student_id,
                    'type' => 'announcement',
                    'title' => 'Announcement from your teacher',
                    'message' => $data['message'],
                    'icon' => 'campaign',
                    'color' => '#1a6fd4',
                    'data' => [
                        'announcement_id' => $announcement->id,
                        'lesson_ids' => $lessonIds->all(),
                    ],
                    'action_url' => null,
                ]);
            }
        });

        $lessonCount = $lessonIds->count();
        $lessonText = $lessonCount === 1 ? '1 lesson' : "{$lessonCount} lessons";
        return redirect()->route('dashboard')->with('announcement_success', "Announcement for {$lessonText} sent to {$recipients->count()} student" . ($recipients->count() === 1 ? '' : 's') . '.');
    }

    public function lessonStats(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);

        $activeClassroomId = $request->query('classroom_id');
        if ($activeClassroomId === null || $activeClassroomId === '') {
            $activeClassroomId = session('active_classroom_id');
        }
        if ($activeClassroomId === 'all' || $activeClassroomId === '0') {
            $activeClassroomId = null;
        }

        $lessonStudentCounts = DB::table('lesson_assignments')
            ->join('students', 'students.student_id', '=', 'lesson_assignments.student_id')
            ->where('students.teacher_id', $teacher->id)
            ->where('students.is_enrolled', true)
            ->when($activeClassroomId, fn ($q, $cId) => $q->where('students.classroom_id', $cId))
            ->groupBy('lesson_assignments.lesson_id')
            ->select('lesson_assignments.lesson_id', DB::raw('count(distinct lesson_assignments.student_id) as assigned_count'))
            ->pluck('assigned_count', 'lesson_assignments.lesson_id');

        $lessons = Lesson::where('teacher_id', $teacher->id)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->orderBy('title')
            ->get(['lesson_id', 'title'])
            ->map(function ($lesson) use ($lessonStudentCounts) {
                $count = (int) ($lessonStudentCounts[$lesson->lesson_id] ?? 0);
                return [
                    'lesson_id' => $lesson->lesson_id,
                    'title' => $lesson->title,
                    'assigned_count' => $count,
                    'has_students' => ($count > 0),
                ];
            });

        return response()->json([
            'lessons' => $lessons,
            'active_classroom_id' => $activeClassroomId,
        ]);
    }

    public function lessonStudents(Request $request, ?int $lessonId = null)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);

        $lessonIds = [];
        if ($lessonId) {
            $lessonIds[] = $lessonId;
        }
        if ($request->has('lesson_ids')) {
            $param = $request->query('lesson_ids');
            if (is_array($param)) {
                $lessonIds = array_merge($lessonIds, array_map('intval', $param));
            } elseif (is_string($param)) {
                $lessonIds = array_merge($lessonIds, array_map('intval', explode(',', $param)));
            }
        }
        $lessonIds = array_values(array_unique(array_filter($lessonIds)));

        if (empty($lessonIds)) {
            return response()->json(['students' => [], 'active_classroom_id' => null]);
        }

        $activeClassroomId = $request->query('classroom_id');
        if ($activeClassroomId === null || $activeClassroomId === '') {
            $activeClassroomId = session('active_classroom_id');
        }
        if ($activeClassroomId === 'all' || $activeClassroomId === '0') {
            $activeClassroomId = null;
        }

        $students = Student::query()
            ->join('lesson_assignments', 'lesson_assignments.student_id', '=', 'students.student_id')
            ->leftJoin('classrooms', 'classrooms.id', '=', 'students.classroom_id')
            ->whereIn('lesson_assignments.lesson_id', $lessonIds)
            ->where('students.teacher_id', $teacher->id)
            ->where('students.is_enrolled', true)
            ->when($activeClassroomId, fn ($q, $cId) => $q->where('students.classroom_id', $cId))
            ->distinct()
            ->orderBy('students.first_name')
            ->get([
                'students.student_id',
                'students.first_name',
                'students.last_name',
                'students.classroom_id',
                'classrooms.name as classroom_name',
            ]);

        return response()->json([
            'students' => $students,
            'active_classroom_id' => $activeClassroomId,
            'lesson_ids' => $lessonIds,
        ]);
    }

    public function destroy(Request $request, TeacherAnnouncement $announcement)
    {
        abort_unless($request->user()->teacher && $announcement->teacher_id === $request->user()->teacher->id, 404);

        DB::transaction(function () use ($announcement) {
            StudentNotification::where('type', 'announcement')
                ->where('data->announcement_id', $announcement->id)
                ->delete();
            $announcement->delete();
        });

        return redirect()->route('dashboard')->with('announcement_success', 'Announcement deleted.');
    }
}
