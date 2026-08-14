<?php

use App\Models\CheckpointExam;
use App\Models\Module;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Adds "default curriculum" template support.
 *
 * ✅ FIXED VERSION - CLONES modules instead of MOVING them
 * 
 * How it works:
 *  - A single dedicated "system" Teacher/User row owns all template
 *    modules/lessons/checkpoint exams (role = 'teacher', is_system = true)
 *  - `is_template` flags on modules/lessons/checkpoint_exams mark rows
 *    that belong to that system owner
 *  - `source_template_id` on modules/lessons/checkpoint_exams records which
 *    template a per-teacher copy was cloned from
 *  - ✅ FIX: Modules 4, 6, 7 are CLONED to the system teacher, NOT moved
 *  - ✅ Teacher 1 keeps their original modules with student data intact
 */
return new class extends Migration
{
    // Update these if your real default-module IDs differ
    private array $initialTemplateModuleIds = [4, 6, 7];

    public function up(): void
    {
        // ── 1. Additive columns only ─────────────────────────────────────
        if (!Schema::hasColumn('users', 'is_system')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_system')->default(false)->after('role')->index();
            });
        }

        Schema::table('modules', function (Blueprint $table) {
            if (!Schema::hasColumn('modules', 'is_template')) {
                $table->boolean('is_template')->default(false)->after('teacher_id')->index();
            }
            if (!Schema::hasColumn('modules', 'source_template_id')) {
                $table->unsignedBigInteger('source_template_id')->nullable()->after('is_template')->index();
            }
        });

        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'is_template')) {
                $table->boolean('is_template')->default(false)->after('teacher_id')->index();
            }
            if (!Schema::hasColumn('lessons', 'source_template_id')) {
                $table->unsignedBigInteger('source_template_id')->nullable()->after('is_template')->index();
            }
        });

        if (Schema::hasTable('checkpoint_exams')) {
            Schema::table('checkpoint_exams', function (Blueprint $table) {
                if (!Schema::hasColumn('checkpoint_exams', 'is_template')) {
                    $table->boolean('is_template')->default(false)->after('teacher_id')->index();
                }
                if (!Schema::hasColumn('checkpoint_exams', 'source_template_id')) {
                    $table->unsignedBigInteger('source_template_id')->nullable()->after('is_template')->index();
                }
            });
        }

        // ── 2. Create the dedicated system template owner ────────────────
        try {
            DB::transaction(function () {
                $systemTeacherId = $this->ensureSystemTeacher();
                
                // ✅ FIX: CLONE modules to system teacher (DO NOT MOVE!)
                $this->cloneModulesToSystemTeacher($systemTeacherId);
            });
        } catch (\Throwable $e) {
            Log::error('[add_default_lesson_template_support] Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_system')) {
                $table->dropColumn('is_system');
            }
        });

        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'source_template_id')) $table->dropColumn('source_template_id');
            if (Schema::hasColumn('modules', 'is_template')) $table->dropColumn('is_template');
        });

        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'source_template_id')) $table->dropColumn('source_template_id');
            if (Schema::hasColumn('lessons', 'is_template')) $table->dropColumn('is_template');
        });

        if (Schema::hasTable('checkpoint_exams')) {
            Schema::table('checkpoint_exams', function (Blueprint $table) {
                if (Schema::hasColumn('checkpoint_exams', 'source_template_id')) $table->dropColumn('source_template_id');
                if (Schema::hasColumn('checkpoint_exams', 'is_template')) $table->dropColumn('is_template');
            });
        }

        // We deliberately do NOT delete the system user/teacher or revert
        // module ownership on rollback — that's a data decision, not a
        // schema one. Do it manually if you really want to undo it.
    }

    /**
     * Find (or create) the single system User + Teacher row that owns the
     * default curriculum.
     */
    private function ensureSystemTeacher(): int
    {
        $existing = Teacher::whereHas('user', fn ($q) => $q->where('is_system', true))->first();
        if ($existing) {
            return (int) $existing->id;
        }

        // role stays 'teacher' — `is_system` is what marks this account
        $email = 'system-templates@internal.local';
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::forceCreate([
                'username'          => 'system_templates',
                'name'              => 'Default Curriculum',
                'email'             => $email,
                'email_verified_at' => now(),
                'password'          => Hash::make(Str::password(32)),
                'role'              => 'teacher',
                'status'            => 'inactive',
                'is_system'         => true,
            ]);
        } elseif (!$user->is_system) {
            $user->forceFill(['is_system' => true])->save();
        }

        return (int) Teacher::create([
            'user_id'        => $user->id,
            'school_id'      => School::query()->value('id'),
            'first_name'     => 'Default',
            'last_name'      => 'Curriculum',
            'specialization' => 'Regular',  // ✅ FIX: Use 'Regular' instead of 'System'
        ])->id;
    }

    /**
     * ✅ FIX: CLONE modules to system teacher instead of moving them!
     * This preserves Teacher 1's modules with all student data.
     */
    private function cloneModulesToSystemTeacher(int $systemTeacherId): void
    {
        foreach ($this->initialTemplateModuleIds as $moduleId) {
            $originalModule = Module::withTrashed()->find($moduleId);
            if (!$originalModule) {
                Log::warning("Module {$moduleId} not found, skipping clone");
                continue;
            }

            // ✅ CLONE the module to system teacher
            $clonedModule = Module::create([
                'teacher_id'         => $systemTeacherId,
                'title'              => $originalModule->title,
                'description'        => $originalModule->description,
                'mastery_level'      => $originalModule->mastery_level,
                'module_order'       => $originalModule->module_order,
                'status'             => 'published',
                'is_template'        => true,
                'source_template_id' => $originalModule->module_id, // Track which original it came from
            ]);

            Log::info("✅ Cloned module {$originalModule->module_id} to system teacher as module {$clonedModule->module_id}");

            // ✅ CLONE lessons
            $originalLessons = DB::table('lessons')
                ->where('module_id', $originalModule->module_id)
                ->get();

            foreach ($originalLessons as $originalLesson) {
                $clonedLessonId = DB::table('lessons')->insertGetId([
                    'teacher_id'         => $systemTeacherId,
                    'module_id'          => $clonedModule->module_id,
                    'title'              => $originalLesson->title,
                    'description'        => $originalLesson->description,
                    'lesson_type'        => $originalLesson->lesson_type,
                    'difficulty'         => $originalLesson->difficulty,
                    'module_order'       => $originalLesson->module_order,
                    'status'             => 'published',
                    'published_at'       => now(),
                    'is_template'        => true,
                    'source_template_id' => $originalLesson->lesson_id, // Track original lesson
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);

                // ✅ CLONE lesson contents
                $contents = DB::table('lesson_contents')
                    ->where('lesson_id', $originalLesson->lesson_id)
                    ->get();

                foreach ($contents as $content) {
                    DB::table('lesson_contents')->insert([
                        'lesson_id'     => $clonedLessonId,
                        'step_number'   => $content->step_number,
                        'content_type'  => $content->content_type,
                        'title'         => $content->title,
                        'content_text'  => $content->content_text,
                        'media_url'     => $content->media_url,
                        'gesture_name'  => $content->gesture_name,
                        'media_missing' => $content->media_missing,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }

                // ✅ CLONE quiz
                $originalQuiz = DB::table('quizzes')
                    ->where('lesson_id', $originalLesson->lesson_id)
                    ->first();

                if ($originalQuiz) {
                    $clonedQuizId = DB::table('quizzes')->insertGetId([
                        'lesson_id'     => $clonedLessonId,
                        'title'         => $originalQuiz->title,
                        'description'   => $originalQuiz->description,
                        'total_points'  => $originalQuiz->total_points,
                        'passing_score' => $originalQuiz->passing_score,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);

                    // ✅ CLONE quiz questions
                    $questions = DB::table('quiz_questions')
                        ->where('quiz_id', $originalQuiz->quiz_id)
                        ->get();

                    foreach ($questions as $question) {
                        $clonedQuestionId = DB::table('quiz_questions')->insertGetId([
                            'quiz_id'          => $clonedQuizId,
                            'question_number'  => $question->question_number,
                            'question_type'    => $question->question_type,
                            'question_text'    => $question->question_text,
                            'media_url'        => $question->media_url,
                            'drag_drop_pairs'  => $question->drag_drop_pairs,
                            'gesture_data'     => $question->gesture_data,
                            'gesture_required' => $question->gesture_required,
                            'points'           => $question->points,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);

                        // ✅ CLONE quiz options
                        $options = DB::table('quiz_options')
                            ->where('question_id', $question->question_id)
                            ->get();

                        foreach ($options as $option) {
                            DB::table('quiz_options')->insert([
                                'question_id'       => $clonedQuestionId,
                                'option_text'       => $option->option_text,
                                'option_media_url'  => $option->option_media_url,
                                'is_correct'        => $option->is_correct,
                                'created_at'        => now(),
                                'updated_at'        => now(),
                            ]);
                        }
                    }
                }
            }

            // ✅ CLONE checkpoint exams
            if (Schema::hasTable('checkpoint_exams')) {
                $originalExams = DB::table('checkpoint_exams')
                    ->where('module_id', $originalModule->module_id)
                    ->get();

                foreach ($originalExams as $originalExam) {
                    $clonedExamId = DB::table('checkpoint_exams')->insertGetId([
                        'module_id'           => $clonedModule->module_id,
                        'teacher_id'          => $systemTeacherId,
                        'title'               => $originalExam->title,
                        'description'         => $originalExam->description,
                        'total_points'        => $originalExam->total_points,
                        'passing_score'       => $originalExam->passing_score,
                        'time_limit_minutes'  => $originalExam->time_limit_minutes,
                        'status'              => 'published',
                        'is_template'         => true,
                        'source_template_id'  => $originalExam->exam_id,
                        'published_at'        => now(),
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);

                    // ✅ CLONE checkpoint exam questions
                    $examQuestions = DB::table('checkpoint_exam_questions')
                        ->where('exam_id', $originalExam->exam_id)
                        ->get();

                    foreach ($examQuestions as $eq) {
                        DB::table('checkpoint_exam_questions')->insert([
                            'exam_id'             => $clonedExamId,
                            'source_lesson_id'    => $eq->source_lesson_id,
                            'source_question_id'  => $eq->source_question_id,
                            'question_number'     => $eq->question_number,
                            'question_text'       => $eq->question_text,
                            'question_type'       => $eq->question_type,
                            'media_url'           => $eq->media_url,
                            'points'              => $eq->points,
                            'options_data'        => $eq->options_data,
                            'drag_drop_pairs'     => $eq->drag_drop_pairs,
                            'gesture_data'        => $eq->gesture_data,
                            'correct_answer'      => $eq->correct_answer,
                            'created_at'          => now(),
                            'updated_at'          => now(),
                        ]);
                    }
                }
            }
        }

        Log::info("✅ Successfully cloned all modules to system teacher");
    }
};