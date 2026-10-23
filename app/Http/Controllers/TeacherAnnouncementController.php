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

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'lesson_id' => ['required', 'integer', 'min:1'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $lesson = Lesson::where('teacher_id', $teacher->id)->where('lesson_id', $data['lesson_id'])->firstOrFail();
        $eligibleStudentIds = DB::table('lesson_assignments')
            ->join('students', 'students.student_id', '=', 'lesson_assignments.student_id')
            ->where('lesson_assignments.lesson_id', $lesson->lesson_id)
            ->where('students.teacher_id', $teacher->id)
            ->where('students.is_enrolled', true)
            ->distinct()
            ->pluck('students.student_id');

        $selectedIds = collect($data['student_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        if ($selectedIds->isEmpty() || $selectedIds->diff($eligibleStudentIds)->isNotEmpty()) {
            return back()->withErrors(['student_ids' => 'Select one or more students assigned to this lesson.'])->withInput();
        }
        $recipients = Student::where('teacher_id', $teacher->id)->where('is_enrolled', true)->whereIn('student_id', $selectedIds)->get(['student_id']);

        if ($recipients->isEmpty()) {
            return back()->withErrors(['student_ids' => 'There are no enrolled students assigned to this lesson.'])->withInput();
        }

        DB::transaction(function () use ($teacher, $data, $lesson, $recipients) {
            $announcement = TeacherAnnouncement::create([
                'teacher_id' => $teacher->id,
                'lesson_id' => $lesson->lesson_id,
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
                    'data' => ['announcement_id' => $announcement->id],
                    'action_url' => null,
                ]);
            }
        });

        return redirect()->route('dashboard')->with('announcement_success', 'Announcement sent to ' . $recipients->count() . ' student' . ($recipients->count() === 1 ? '' : 's') . '.');
    }

    public function lessonStudents(Request $request, int $lessonId)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);
        $lesson = Lesson::where('teacher_id', $teacher->id)->where('lesson_id', $lessonId)->firstOrFail();

        $students = Student::query()
            ->join('lesson_assignments', 'lesson_assignments.student_id', '=', 'students.student_id')
            ->where('lesson_assignments.lesson_id', $lesson->lesson_id)
            ->where('students.teacher_id', $teacher->id)
            ->where('students.is_enrolled', true)
            ->distinct()
            ->orderBy('students.first_name')
            ->get(['students.student_id', 'students.first_name', 'students.last_name']);

        return response()->json(['students' => $students]);
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
