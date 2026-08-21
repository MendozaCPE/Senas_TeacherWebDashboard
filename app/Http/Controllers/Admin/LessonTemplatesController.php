<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckpointExamQuestion;
use App\Models\Module;
use App\Models\Teacher;
use App\Services\LessonTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonTemplatesController extends Controller
{
    public function __construct(private LessonTemplateService $templates)
    {
    }

    /**
     * GET /admin/lessons — the default curriculum tab.
     */
public function index()
{
    $systemTeacherId = $this->templates->templateTeacherId();

    $modules = Module::where('teacher_id', $systemTeacherId)
        ->where('is_template', true)
        ->with(['lessons' => function ($q) {
            $q->withTrashed()->orderBy('module_order')->with('quiz.questions');
        }])
        ->with(['checkpointExams' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }])
        ->orderBy('module_order')
        ->get();

    // ✅ FIX: Count teachers who have copies (including original owners)
    $teacherCopyCounts = [];
    foreach ($modules as $module) {
        $originalId = $module->source_template_id ?? $module->module_id;
        
        // Count teachers who have cloned copies (source_template_id matches original)
        $cloneCount = DB::table('modules')
            ->whereNotNull('source_template_id')
            ->where('source_template_id', $originalId)
            ->where('teacher_id', '!=', $systemTeacherId)
            ->distinct('teacher_id')
            ->count('teacher_id');
        
        // Count teachers who own the original modules (4, 6, 7)
        $originalCount = DB::table('modules')
            ->whereNull('source_template_id')
            ->where('module_id', $originalId)
            ->where('teacher_id', '!=', $systemTeacherId)
            ->distinct('teacher_id')
            ->count('teacher_id');
        
        // Total = clone owners + original owners
        $teacherCopyCounts[$module->module_id] = $cloneCount + $originalCount;
    }

    $usedLessonIds = CheckpointExamQuestion::whereHas('exam', function ($q) use ($systemTeacherId) {
        $q->where('teacher_id', $systemTeacherId);
    })->distinct('source_lesson_id')->pluck('source_lesson_id')->toArray();

    // Get all teachers for the push modal
    $allTeachers = Teacher::with('user')
        ->whereHas('user', function ($q) {
            $q->where('is_system', false)
              ->where('role', 'teacher');
        })
        ->orderBy('first_name')
        ->get();

    foreach ($modules as $module) {
        $module->teacherCopyCount = $teacherCopyCounts[$module->module_id] ?? 0;

        // Compute which lessons are still available for a new checkpoint exam
        $moduleUsedLessonIds = CheckpointExamQuestion::whereHas('exam', function ($q) use ($systemTeacherId) {
            $q->where('teacher_id', $systemTeacherId);
        })->where(function ($q) use ($module) {
            $q->whereHas('exam', function ($q2) use ($module) {
                $q2->where('module_id', $module->module_id);
            });
        })->distinct('source_lesson_id')->pluck('source_lesson_id')->toArray();

        $availableLessons = $module->lessons->filter(function ($lesson) use ($moduleUsedLessonIds) {
            return !in_array($lesson->lesson_id, $moduleUsedLessonIds)
                && $lesson->status === 'published'
                && $lesson->quiz
                && $lesson->quiz->questions->count() > 0;
        });

        $module->availableLessonsCount = $availableLessons->count();
        $module->canCreateExam = $module->availableLessonsCount >= 2;
    }

    return view('admin.lessons', compact('modules', 'usedLessonIds', 'allTeachers'));
}
/**
 * GET /admin/lessons/teachers — get list of teachers for the modal
 */
public function getTeachers()
{
    $teachers = Teacher::with('user')
        ->whereHas('user', function ($q) {
            $q->where('is_system', false)
              ->where('role', 'teacher');
        })
        ->get()
        ->map(function ($teacher) {
            // ✅ Check if teacher has copies (source_template_id != NULL)
            $hasCopies = Module::where('teacher_id', $teacher->id)
                ->whereNotNull('source_template_id')
                ->exists();
            
            // ✅ ALSO check if teacher owns the original modules (4, 6, 7)
            $ownsOriginals = Module::where('teacher_id', $teacher->id)
                ->whereIn('module_id', [4, 6, 7])
                ->exists();
            
            // ✅ Teacher has copies if they have cloned modules OR own the originals
            $hasCopies = $hasCopies || $ownsOriginals;
            
            return [
                'id' => $teacher->id,
                'name' => trim($teacher->first_name . ' ' . $teacher->last_name),
                'email' => $teacher->user->email ?? null,
                'has_copies' => $hasCopies,
                'initials' => strtoupper(
                    substr($teacher->first_name, 0, 1) . 
                    substr($teacher->last_name, 0, 1)
                ),
            ];
        });

    return response()->json(['teachers' => $teachers]);
}
    /**
     * POST /admin/lessons/push-selected — push to selected teachers only
     */
    public function pushSelected(Request $request)
    {
        $validated = $request->validate([
            'teacher_ids' => 'required|array|min:1',
            'teacher_ids.*' => 'integer|exists:teachers,id',
            'module_id' => 'nullable|integer|exists:modules,module_id',
        ]);

        $teacherIds = $validated['teacher_ids'];
        $moduleId = $validated['module_id'] ?? null;

        $result = $this->templates->pushTemplatesToSelectedTeachers($teacherIds, $moduleId);

        return response()->json([
            'success' => true,
            'message' => "Pushed to {$result['updated']} existing copies updated, {$result['created']} new copies created for {$result['teachers']} teacher(s).",
            'result' => $result
        ]);
    }

    /**
     * POST /admin/lessons/{module}/push — sync one default module out to
     * every teacher's account.
     */
    public function pushModule(int $moduleId)
    {
        $result = $this->templates->pushTemplateModuleToAllTeachers($moduleId);

        return back()->with(
            'success',
            "Pushed to all teachers — {$result['updated']} existing copies updated, {$result['created']} new copies created."
        );
    }

    /**
     * POST /admin/lessons/push-all — sync every default module out to
     * every teacher's account.
     */
    public function pushAll()
    {
        $result = $this->templates->pushAllTemplatesToAllTeachers();

        return back()->with(
            'success',
            "Pushed all default lessons — {$result['updated']} existing copies updated, {$result['created']} new copies created."
        );
    }

    /**
     * POST /admin/lessons/{module}/promote — turn an already-existing
     * teacher-owned module into a new default template (for when the admin
     * builds a brand-new default lesson under their own account first).
     */
    public function promote(int $moduleId)
    {
        $this->templates->promoteModuleToTemplate($moduleId);

        return redirect()->route('admin.lesson-templates.index')
            ->with('success', 'Module promoted to a default lesson. It will now be cloned for every new teacher.');
    }


}