<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Lesson;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ModulesController extends Controller
{
    /**
     * Delete a module with options for its lessons.
     * POST /modules/{id}/delete-with-options
     */
    public function deleteWithOptions(Request $request, $id)
    {
        $validated = $request->validate([
            'lesson_action' => 'required|in:delete,move_to_unassigned',
        ]);

        $module = Module::findOrFail($id);
        $teacherId = $this->resolveTeacherId();

        // Verify module belongs to teacher
        if ($module->teacher_id !== $teacherId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $lessonCount = $module->lessons()->count();

        DB::transaction(function () use ($module, $validated) {
            $lessonAction = $validated['lesson_action'];

            switch ($lessonAction) {
                case 'delete':
                    // 🔥 HARD DELETE - Remove everything
                    foreach ($module->lessons as $lesson) {
                        // Call the hardDelete logic for each lesson
                        $this->hardDeleteLesson($lesson);
                    }
                    break;

                case 'move_to_unassigned':
                    // Move lessons to unassigned (set module_id to null)
                    Lesson::where('module_id', $module->module_id)
                        ->update(['module_id' => null]);
                    break;
            }

            // Soft delete the module itself (so it can be restored if needed)
            $module->delete();
        });

        $actionLabels = [
            'delete' => "permanently deleted all {$lessonCount} lesson(s) and all associated student data",
            'move_to_unassigned' => "moved all {$lessonCount} lesson(s) to 'Unassigned'",
        ];

        return response()->json([
            'success' => true,
            'message' => "Module '{$module->title}' deleted successfully. " . ($actionLabels[$validated['lesson_action']] ?? ''),
            'redirect' => route('lessons.index'),
        ]);
    }

    /**
     * Hard delete a single lesson and all its associated data.
     * This is the same logic from LessonsController::hardDelete
     */
    private function hardDeleteLesson(Lesson $lesson)
    {
        $lessonId = $lesson->lesson_id;

        // 1. Delete quiz attempts & student answers
        if ($lesson->quiz) {
            $quizId = $lesson->quiz->quiz_id;

            DB::table('student_answers')
                ->whereIn('attempt_id', function($query) use ($quizId) {
                    $query->select('attempt_id')
                        ->from('quiz_attempts')
                        ->where('quiz_id', $quizId);
                })
                ->delete();

            DB::table('quiz_attempts')
                ->where('quiz_id', $quizId)
                ->delete();

            $lesson->quiz->questions()->each(function ($question) {
                $question->options()->delete();
            });
            $lesson->quiz->questions()->delete();
            $lesson->quiz()->delete();
        }

        // 2. Delete student lesson progress
        DB::table('student_lesson_progress')
            ->where('lesson_id', $lessonId)
            ->delete();

        // 3. Delete lesson assignments
        DB::table('lesson_assignments')
            ->where('lesson_id', $lessonId)
            ->delete();

        // 4. Delete checkpoint exam data
        DB::table('checkpoint_exam_attempts')
            ->whereIn('exam_id', function($query) use ($lessonId) {
                $query->select('exam_id')
                    ->from('checkpoint_exam_questions')
                    ->where('source_lesson_id', $lessonId);
            })
            ->delete();

        DB::table('checkpoint_exam_questions')
            ->where('source_lesson_id', $lessonId)
            ->delete();

        DB::table('checkpoint_exam_assignments')
            ->whereIn('exam_id', function($query) use ($lessonId) {
                $query->select('exam_id')
                    ->from('checkpoint_exam_questions')
                    ->where('source_lesson_id', $lessonId);
            })
            ->delete();

        // 5. Delete lesson contents
        $lesson->contents()->delete();

        // 6. Force delete (not soft delete)
        $lesson->forceDelete();
    }

    /**
     * Show the delete options page.
     * GET /modules/{id}/delete-options
     */
    public function showDeleteOptions($id)
    {
        $module = Module::withCount('lessons')->findOrFail($id);
        
        return view('modules.delete-options', compact('module'));
    }

    /**
     * Update module details (title, description, mastery_level).
     * PUT/POST /modules/{id}
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'mastery_level' => 'required|in:beginner,intermediate,advanced',
        ]);

        $module = Module::findOrFail($id);
        $teacherId = $this->resolveTeacherId();

        if ($module->teacher_id !== $teacherId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $module->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'mastery_level' => $validated['mastery_level'],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Module '{$module->title}' updated successfully.",
            'module' => $module,
        ]);
    }

    /**
     * Simple delete - redirects to the options page.
     */
    public function destroy($id)
    {
        $module = Module::findOrFail($id);
        return redirect()->route('modules.delete-options', $module->module_id);
    }

    private function resolveTeacherId(): int
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->teacher) {
                return (int) $user->teacher->id;
            }

            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                return (int) $teacher->id;
            }
        }

        $firstTeacher = Teacher::first();
        return $firstTeacher ? (int) $firstTeacher->id : 1;
    }
}