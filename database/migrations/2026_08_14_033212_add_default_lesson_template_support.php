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
 * How it works:
 *  - A single dedicated "system" Teacher/User row owns all template
 *    modules/lessons/checkpoint exams (role = 'teacher', is_system = true)
 *  - `is_template` flags on modules/lessons/checkpoint_exams mark rows
 *    that belong to that system owner
 *  - `source_template_id` on modules/lessons/checkpoint_exams records which
 *    template a per-teacher copy was cloned from
 *  - The 3 existing default modules (currently owned by teacher_id 1) are
 *    reassigned to the system teacher and flagged as templates
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

        // ── 2. Create the dedicated system template owner + promote data ──
        try {
            DB::transaction(function () {
                $systemTeacherId = $this->ensureSystemTeacher();
                $this->promoteInitialModules($systemTeacherId);
            });
        } catch (\Throwable $e) {
            Log::error('[add_default_lesson_template_support] Skipped auto-promotion: ' . $e->getMessage());
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
            'specialization' => 'System',
        ])->id;
    }

    private function promoteInitialModules(int $systemTeacherId): void
    {
        foreach ($this->initialTemplateModuleIds as $moduleId) {
            $module = Module::withTrashed()->find($moduleId);
            if (!$module) {
                continue;
            }

            $module->teacher_id = $systemTeacherId;
            $module->is_template = true;
            $module->save();

            DB::table('lessons')
                ->where('module_id', $moduleId)
                ->update(['teacher_id' => $systemTeacherId, 'is_template' => true]);

            if (Schema::hasTable('checkpoint_exams')) {
                DB::table('checkpoint_exams')
                    ->where('module_id', $moduleId)
                    ->update(['teacher_id' => $systemTeacherId, 'is_template' => true]);
            }
        }
    }
};