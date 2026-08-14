<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckpointExamQuestion;
use App\Models\Module;
use App\Models\Teacher;
use App\Services\LessonTemplateService;
use Illuminate\Http\Request;

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
                $q->withTrashed()->orderBy('module_order');
            }])
            ->with(['checkpointExams' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }])
            ->orderBy('module_order')
            ->get();

        // How many teachers currently have their own copy of each module —
        // shown next to the "Push to All Teachers" button.
        $teacherCopyCounts = Module::whereNotNull('source_template_id')
            ->whereIn('source_template_id', $modules->pluck('module_id'))
            ->selectRaw('source_template_id, count(*) as c')
            ->groupBy('source_template_id')
            ->pluck('c', 'source_template_id');

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
                // Check if this teacher has copies of default modules
                $hasCopies = Module::where('teacher_id', $teacher->id)
                    ->whereNotNull('source_template_id')
                    ->exists();
                
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