<?php

namespace App\Services;

use App\Models\CheckpointExam;
use App\Models\CheckpointExamQuestion;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Owns everything about the "default curriculum" — the set of
 * modules/lessons/checkpoint exams every teacher account gets out of the box.
 *
 *  - cloneTemplatesForTeacher()   → run once, right after a teacher signs up
 *  - pushTemplateModuleToAllTeachers() → admin clicks "Push to All Teachers"
 *    on one default module, to sync a fix out to teachers who already have
 *    their own copy
 *  - promoteModuleToTemplate()    → turn any existing module into a new
 *    default (used when the admin builds a brand-new default lesson)
 */
class LessonTemplateService
{
    private ?int $templateTeacherIdCache = null;

    /**
     * The single Teacher row that owns every template module/lesson.
     */
    public function templateTeacherId(): int
    {
        if ($this->templateTeacherIdCache !== null) {
            return $this->templateTeacherIdCache;
        }

        $teacher = Teacher::whereHas('user', fn ($q) => $q->where('is_system', true))->first();

        if (!$teacher) {
            throw new \RuntimeException(
                'No system template teacher found. Run the add_default_lesson_template_support migration first.'
            );
        }

        return $this->templateTeacherIdCache = (int) $teacher->id;
    }

    /**
     * Deep-clone every default template module (+ lessons, contents, quiz,
     * checkpoint exams) into a brand-new, fully independent copy owned by
     * $teacherId. Call this once, right after a teacher account is created.
     *
     * @return int number of modules cloned
     */
    public function cloneTemplatesForTeacher(int $teacherId): int
    {
        $templateModules = Module::where('teacher_id', $this->templateTeacherId())
            ->where('is_template', true)
            ->with(['lessons.contents', 'lessons.quiz.questions.options', 'checkpointExams.questions'])
            ->orderBy('module_order')
            ->get();

        $cloned = 0;

        DB::transaction(function () use ($templateModules, $teacherId, &$cloned) {
            foreach ($templateModules as $templateModule) {
                $this->cloneModuleForTeacher($templateModule, $teacherId);
                $cloned++;
            }
        });

        return $cloned;
    }

    /**
     * Sync one default module's current content out to every teacher who
     * already has a copy of it, and clone it fresh for any teacher who
     * doesn't have a copy yet (e.g. it was added after they signed up).
     *
     * Module/lesson rows are updated IN PLACE (their IDs never change) so
     * existing student assignments and progress stay intact. Quiz questions,
     * options, lesson content slides, and checkpoint exam questions are
     * replaced wholesale to match the template — so this is a genuine
     * overwrite of any hand-edits a teacher made to their copy of this
     * module. That's the intended behavior for a "push" button; make sure
     * that's communicated in the admin UI's confirmation dialog.
     *
     * @return array{updated:int, created:int}
     */
    public function pushTemplateModuleToAllTeachers(int $templateModuleId): array
    {
        $template = Module::where('is_template', true)
            ->with(['lessons.contents', 'lessons.quiz.questions.options', 'checkpointExams.questions'])
            ->findOrFail($templateModuleId);

        $teacherIds = Teacher::whereHas('user', fn ($q) => $q->where('is_system', false))
            ->pluck('id');

        $updated = 0;
        $created = 0;

        foreach ($teacherIds as $teacherId) {
            DB::transaction(function () use ($template, $teacherId, &$updated, &$created) {
                $existing = Module::where('teacher_id', $teacherId)
                    ->where('source_template_id', $template->module_id)
                    ->first();

                if ($existing) {
                    $this->syncModuleInPlace($template, $existing);
                    $updated++;
                } else {
                    $this->cloneModuleForTeacher($template, $teacherId);
                    $created++;
                }
            });
        }

        return ['updated' => $updated, 'created' => $created];
    }

    /**
     * Push every default template module to every teacher. Convenience
     * wrapper around pushTemplateModuleToAllTeachers() for a single
     * "Push All" button.
     *
     * @return array{updated:int, created:int}
     */
    public function pushAllTemplatesToAllTeachers(): array
    {
        $totals = ['updated' => 0, 'created' => 0];

        $templateModuleIds = Module::where('teacher_id', $this->templateTeacherId())
            ->where('is_template', true)
            ->pluck('module_id');

        foreach ($templateModuleIds as $moduleId) {
            $result = $this->pushTemplateModuleToAllTeachers($moduleId);
            $totals['updated'] += $result['updated'];
            $totals['created'] += $result['created'];
        }

        return $totals;
    }

    /**
     * Turn an existing (regular, teacher-owned) module into a new default
     * template: moves it to the system teacher and flags it + its lessons
     * and checkpoint exams as templates. Use this when the admin builds a
     * brand-new default lesson from scratch and wants to promote it.
     */
    public function promoteModuleToTemplate(int $moduleId): Module
    {
        return DB::transaction(function () use ($moduleId) {
            $module = Module::findOrFail($moduleId);
            $systemTeacherId = $this->templateTeacherId();

            $module->update(['teacher_id' => $systemTeacherId, 'is_template' => true]);

            Lesson::where('module_id', $module->module_id)
                ->update(['teacher_id' => $systemTeacherId, 'is_template' => true]);

            CheckpointExam::where('module_id', $module->module_id)
                ->update(['teacher_id' => $systemTeacherId, 'is_template' => true]);

            return $module->fresh();
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // Internal helpers
    // ─────────────────────────────────────────────────────────────────

    private function cloneModuleForTeacher(Module $template, int $teacherId): Module
    {
        $newModule = Module::create([
            'teacher_id'         => $teacherId,
            'title'              => $template->title,
            'description'        => $template->description,
            'mastery_level'      => $template->mastery_level,
            'module_order'       => $template->module_order,
            'status'             => $template->status,
            'is_template'        => false,
            'source_template_id' => $template->module_id,
        ]);

        // lesson_id (template) => lesson_id (clone) — needed to remap
        // checkpoint exam question source references below.
        $lessonIdMap = [];
        // question_id (template) => question_id (clone)
        $questionIdMap = [];

        foreach ($template->lessons as $templateLesson) {
            $newLesson = Lesson::create([
                'teacher_id'         => $teacherId,
                'module_id'          => $newModule->module_id,
                'title'              => $templateLesson->title,
                'description'        => $templateLesson->description,
                'lesson_type'        => $templateLesson->lesson_type,
                'difficulty'         => $templateLesson->difficulty,
                'module_order'       => $templateLesson->module_order,
                'status'             => $templateLesson->status,
                'published_at'       => $templateLesson->published_at,
                'ai_generated'       => $templateLesson->ai_generated,
                'ai_prompt'          => $templateLesson->ai_prompt,
                'is_template'        => false,
                'source_template_id' => $templateLesson->lesson_id,
            ]);

            $lessonIdMap[$templateLesson->lesson_id] = $newLesson->lesson_id;

            foreach ($templateLesson->contents as $content) {
                LessonContent::create([
                    'lesson_id'     => $newLesson->lesson_id,
                    'step_number'   => $content->step_number,
                    'content_type'  => $content->content_type,
                    'title'         => $content->title,
                    'content_text'  => $content->content_text,
                    'media_url'     => $content->media_url,
                    'gesture_name'  => $content->gesture_name,
                    'media_missing' => $content->media_missing,
                ]);
            }

            if ($templateLesson->quiz) {
                $newQuiz = Quiz::create([
                    'lesson_id'     => $newLesson->lesson_id,
                    'title'         => $templateLesson->quiz->title,
                    'description'   => $templateLesson->quiz->description,
                    'total_points'  => $templateLesson->quiz->total_points,
                    'passing_score' => $templateLesson->quiz->passing_score,
                ]);

                foreach ($templateLesson->quiz->questions as $q) {
                    $newQuestion = QuizQuestion::create([
                        'quiz_id'          => $newQuiz->quiz_id,
                        'question_number'  => $q->question_number,
                        'question_type'    => $q->question_type,
                        'question_text'    => $q->question_text,
                        'media_url'        => $q->media_url,
                        'drag_drop_pairs'  => $q->drag_drop_pairs,
                        'gesture_data'     => $q->gesture_data,
                        'gesture_required' => $q->gesture_required,
                        'points'           => $q->points,
                    ]);

                    $questionIdMap[$q->question_id] = $newQuestion->question_id;

                    foreach ($q->options as $opt) {
                        QuizOption::create([
                            'question_id'       => $newQuestion->question_id,
                            'option_text'       => $opt->option_text,
                            'option_media_url'  => $opt->option_media_url,
                            'is_correct'        => $opt->is_correct,
                        ]);
                    }
                }
            }
        }

        foreach ($template->checkpointExams as $exam) {
            $newExam = CheckpointExam::create([
                'module_id'           => $newModule->module_id,
                'teacher_id'          => $teacherId,
                'title'               => $exam->title,
                'description'         => $exam->description,
                'total_points'        => $exam->total_points,
                'passing_score'       => $exam->passing_score,
                'time_limit_minutes'  => $exam->time_limit_minutes,
                'status'              => $exam->status,
                'published_at'        => null, // a fresh copy shouldn't inherit "published" state / assignments
                'is_template'         => false,
                'source_template_id'  => $exam->exam_id,
            ]);

            foreach ($exam->questions as $q) {
                CheckpointExamQuestion::create([
                    'exam_id'             => $newExam->exam_id,
                    'source_lesson_id'    => $lessonIdMap[$q->source_lesson_id] ?? null,
                    'source_question_id'  => $questionIdMap[$q->source_question_id] ?? null,
                    'question_number'     => $q->question_number,
                    'question_text'       => $q->question_text,
                    'question_type'       => $q->question_type,
                    'media_url'           => $q->media_url,
                    'points'              => $q->points,
                    'options_data'        => $q->options_data,
                    'drag_drop_pairs'     => $q->drag_drop_pairs,
                    'gesture_data'        => $q->gesture_data,
                    'correct_answer'      => $q->correct_answer,
                ]);
            }
        }

        return $newModule;
    }

    /**
     * Update an already-cloned module (and everything under it) in place so
     * it matches the template's current content. Module/lesson rows keep
     * their IDs; child rows (contents/questions/options/exam questions) are
     * replaced wholesale.
     */
    private function syncModuleInPlace(Module $template, Module $existing): void
    {
        $existing->update([
            'title'         => $template->title,
            'description'   => $template->description,
            'mastery_level' => $template->mastery_level,
        ]);

        $existingLessonsBySource = $existing->lessons()->get()->keyBy('source_template_id');
        $lessonIdMap = [];
        $questionIdMap = [];

        foreach ($template->lessons as $templateLesson) {
            $targetLesson = $existingLessonsBySource->get($templateLesson->lesson_id);

            if ($targetLesson) {
                $targetLesson->update([
                    'title'        => $templateLesson->title,
                    'description'  => $templateLesson->description,
                    'lesson_type'  => $templateLesson->lesson_type,
                    'difficulty'   => $templateLesson->difficulty,
                    'module_order' => $templateLesson->module_order,
                    'status'       => $templateLesson->status,
                ]);
            } else {
                // Template gained a new lesson since this teacher's copy was made.
                $targetLesson = Lesson::create([
                    'teacher_id'         => $existing->teacher_id,
                    'module_id'          => $existing->module_id,
                    'title'              => $templateLesson->title,
                    'description'        => $templateLesson->description,
                    'lesson_type'        => $templateLesson->lesson_type,
                    'difficulty'         => $templateLesson->difficulty,
                    'module_order'       => $templateLesson->module_order,
                    'status'             => $templateLesson->status,
                    'is_template'        => false,
                    'source_template_id' => $templateLesson->lesson_id,
                ]);
            }

            $lessonIdMap[$templateLesson->lesson_id] = $targetLesson->lesson_id;

            // Replace content slides wholesale.
            LessonContent::where('lesson_id', $targetLesson->lesson_id)->delete();
            foreach ($templateLesson->contents as $content) {
                LessonContent::create([
                    'lesson_id'     => $targetLesson->lesson_id,
                    'step_number'   => $content->step_number,
                    'content_type'  => $content->content_type,
                    'title'         => $content->title,
                    'content_text'  => $content->content_text,
                    'media_url'     => $content->media_url,
                    'gesture_name'  => $content->gesture_name,
                    'media_missing' => $content->media_missing,
                ]);
            }

            // Replace quiz questions/options wholesale.
            $targetQuiz = Quiz::firstOrNew(['lesson_id' => $targetLesson->lesson_id]);
            if ($templateLesson->quiz) {
                $targetQuiz->fill([
                    'title'         => $templateLesson->quiz->title,
                    'description'   => $templateLesson->quiz->description,
                    'total_points'  => $templateLesson->quiz->total_points,
                    'passing_score' => $templateLesson->quiz->passing_score,
                ])->save();

                QuizQuestion::where('quiz_id', $targetQuiz->quiz_id)->delete(); // cascades to options if your FK does; otherwise delete below
                foreach ($templateLesson->quiz->questions as $q) {
                    $newQuestion = QuizQuestion::create([
                        'quiz_id'          => $targetQuiz->quiz_id,
                        'question_number'  => $q->question_number,
                        'question_type'    => $q->question_type,
                        'question_text'    => $q->question_text,
                        'media_url'        => $q->media_url,
                        'drag_drop_pairs'  => $q->drag_drop_pairs,
                        'gesture_data'     => $q->gesture_data,
                        'gesture_required' => $q->gesture_required,
                        'points'           => $q->points,
                    ]);

                    $questionIdMap[$q->question_id] = $newQuestion->question_id;

                    foreach ($q->options as $opt) {
                        QuizOption::create([
                            'question_id'      => $newQuestion->question_id,
                            'option_text'      => $opt->option_text,
                            'option_media_url' => $opt->option_media_url,
                            'is_correct'       => $opt->is_correct,
                        ]);
                    }
                }
            } elseif ($targetQuiz->exists) {
                QuizQuestion::where('quiz_id', $targetQuiz->quiz_id)->delete();
                $targetQuiz->delete();
            }
        }

        // Replace checkpoint exams wholesale (kept simple: delete + recreate,
        // since exam questions are denormalized snapshots anyway).
        $existingExamsBySource = CheckpointExam::where('module_id', $existing->module_id)
            ->get()->keyBy('source_template_id');

        foreach ($template->checkpointExams as $exam) {
            $targetExam = $existingExamsBySource->get($exam->exam_id);

            if ($targetExam) {
                $targetExam->update([
                    'title'              => $exam->title,
                    'description'        => $exam->description,
                    'total_points'       => $exam->total_points,
                    'passing_score'      => $exam->passing_score,
                    'time_limit_minutes' => $exam->time_limit_minutes,
                ]);
                CheckpointExamQuestion::where('exam_id', $targetExam->exam_id)->delete();
            } else {
                $targetExam = CheckpointExam::create([
                    'module_id'          => $existing->module_id,
                    'teacher_id'         => $existing->teacher_id,
                    'title'              => $exam->title,
                    'description'        => $exam->description,
                    'total_points'       => $exam->total_points,
                    'passing_score'      => $exam->passing_score,
                    'time_limit_minutes' => $exam->time_limit_minutes,
                    'status'             => $exam->status,
                    'is_template'        => false,
                    'source_template_id' => $exam->exam_id,
                ]);
            }

            foreach ($exam->questions as $q) {
                CheckpointExamQuestion::create([
                    'exam_id'            => $targetExam->exam_id,
                    'source_lesson_id'   => $lessonIdMap[$q->source_lesson_id] ?? null,
                    'source_question_id' => $questionIdMap[$q->source_question_id] ?? null,
                    'question_number'    => $q->question_number,
                    'question_text'      => $q->question_text,
                    'question_type'      => $q->question_type,
                    'media_url'          => $q->media_url,
                    'points'             => $q->points,
                    'options_data'       => $q->options_data,
                    'drag_drop_pairs'    => $q->drag_drop_pairs,
                    'gesture_data'       => $q->gesture_data,
                    'correct_answer'     => $q->correct_answer,
                ]);
            }
        }
    }
/**
 * Push templates to selected teachers only
 * 
 * @param array $teacherIds Array of teacher IDs
 * @param int|null $moduleId Specific module ID to push, or null for all
 * @return array{teachers:int, updated:int, created:int}
 */
public function pushTemplatesToSelectedTeachers(array $teacherIds, ?int $moduleId = null): array
{
    $totals = ['teachers' => 0, 'updated' => 0, 'created' => 0];

    foreach ($teacherIds as $teacherId) {
        // Verify teacher exists and is not system
        $teacher = Teacher::find($teacherId);
        if (!$teacher) continue;
        
        $user = User::find($teacher->user_id);
        if (!$user || $user->is_system) continue;

        if ($moduleId) {
            // Push specific module
            $result = $this->pushTemplateModuleToTeacher($moduleId, $teacherId);
            $totals['updated'] += $result['updated'];
            $totals['created'] += $result['created'];
        } else {
            // Push all modules
            $templateModuleIds = Module::where('teacher_id', $this->templateTeacherId())
                ->where('is_template', true)
                ->pluck('module_id');
            
            foreach ($templateModuleIds as $mid) {
                $result = $this->pushTemplateModuleToTeacher($mid, $teacherId);
                $totals['updated'] += $result['updated'];
                $totals['created'] += $result['created'];
            }
        }
        $totals['teachers']++;
    }

    return $totals;
}

/**
 * Push a specific module to a specific teacher
 * 
 * @return array{updated:int, created:int}
 */
private function pushTemplateModuleToTeacher(int $templateModuleId, int $teacherId): array
{
    $template = Module::where('is_template', true)
        ->with(['lessons.contents', 'lessons.quiz.questions.options', 'checkpointExams.questions'])
        ->findOrFail($templateModuleId);

    $existing = Module::where('teacher_id', $teacherId)
        ->where('source_template_id', $template->module_id)
        ->first();

    if ($existing) {
        $this->syncModuleInPlace($template, $existing);
        return ['updated' => 1, 'created' => 0];
    } else {
        $this->cloneModuleForTeacher($template, $teacherId);
        return ['updated' => 0, 'created' => 1];
    }
}

}