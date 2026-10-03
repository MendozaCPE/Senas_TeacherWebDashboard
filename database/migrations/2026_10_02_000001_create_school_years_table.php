<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Create the school_years table ────────────────────────────────
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable()->index();
            $table->string('name', 20);          // e.g. "2025-2026"
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');

            // One active school year per school
            $table->index(['school_id', 'status']);
        });

        // ── 2. Add school_year_id to all performance / progress tables ───────
        // We add it NULLABLE first so existing rows are not broken,
        // then backfill, then we leave it nullable (historical rows from before
        // the system existed remain allowed to be null — the app always works
        // off the active school year when querying).

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->unsignedBigInteger('school_year_id')->nullable()->after('student_id')->index();
        });

        Schema::table('gesture_performances', function (Blueprint $table) {
            $table->unsignedBigInteger('school_year_id')->nullable()->after('student_id')->index();
        });

        Schema::table('student_lesson_progress', function (Blueprint $table) {
            $table->unsignedBigInteger('school_year_id')->nullable()->after('student_id')->index();
        });

        Schema::table('lesson_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('school_year_id')->nullable()->after('student_id')->index();
        });

        Schema::table('checkpoint_exam_attempts', function (Blueprint $table) {
            $table->unsignedBigInteger('school_year_id')->nullable()->after('student_id')->index();
        });

        if (Schema::hasTable('student_skill_mastery')) {
            Schema::table('student_skill_mastery', function (Blueprint $table) {
                $table->unsignedBigInteger('school_year_id')->nullable()->after('student_id')->index();
            });
        }

        // ── 3. Seed initial school years from existing student data ─────────
        // DepEd SY: July–June.
        $nowMonth = (int) Carbon::now()->format('n');
        $nowYear  = (int) Carbon::now()->format('Y');
        $syStart  = $nowMonth >= 7 ? $nowYear : ($nowYear - 1);
        $syEnd    = $syStart + 1;
        $defaultActiveSy = "{$syStart}-{$syEnd}";

        // Find all distinct school_year strings currently in students table
        $existingYears = DB::table('students')
            ->whereNotNull('school_year')
            ->where('school_year', '!=', '')
            ->distinct()
            ->pluck('school_year')
            ->toArray();

        // Determine which one is active (prefer the highest/latest year)
        rsort($existingYears);
        $activeYearName = !empty($existingYears) ? $existingYears[0] : $defaultActiveSy;
        if (!in_array($activeYearName, $existingYears)) {
            $existingYears[] = $activeYearName;
        }

        $schools = DB::table('schools')->pluck('id');
        $schoolList = $schools->isEmpty() ? [null] : $schools->all();

        // Mapping from "schoolId_yearName" to created school_year_id
        $syMap = [];

        foreach ($schoolList as $schoolId) {
            foreach ($existingYears as $yName) {
                $status = ($yName === $activeYearName) ? 'active' : 'archived';
                $parts = explode('-', $yName);
                $start = isset($parts[0]) && is_numeric($parts[0]) ? (int)$parts[0] : $syStart;
                $end   = isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : ($start + 1);

                $syId = DB::table('school_years')->insertGetId([
                    'school_id'  => $schoolId,
                    'name'       => $yName,
                    'start_date' => "{$start}-07-01",
                    'end_date'   => "{$end}-06-30",
                    'status'     => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $key = ($schoolId ?? 'null') . '_' . $yName;
                $syMap[$key] = $syId;

                // Backfill records for students matching this school and school_year
                $query = DB::table('students')->where('school_year', $yName);
                if ($schoolId !== null) {
                    $query->where('school_id', $schoolId);
                } else {
                    $query->whereNull('school_id');
                }
                $matchingStudentIds = $query->pluck('student_id');
                self::backfillSchoolYearForStudents($syId, $matchingStudentIds);
            }
        }

        // Fallback: any remaining records with NULL school_year_id get active year
        $fallbackSyId = DB::table('school_years')->where('status', 'active')->orderBy('id', 'desc')->value('id');
        if ($fallbackSyId) {
            self::backfillSchoolYear($fallbackSyId);
        }

        // ── 4. Fix unique constraints safely ─────────────────────────────────
        // Now that school_year_id is backfilled on all rows, add the new composite
        // unique key FIRST so foreign keys always have a covering index,
        // then drop the old 2-column unique key.

        try {
            DB::statement('ALTER TABLE gesture_performances ADD UNIQUE KEY gp_student_gesture_year (student_id, gesture_id, school_year_id)');
        } catch (\Throwable) {}

        try {
            DB::statement('ALTER TABLE gesture_performances DROP INDEX gesture_performances_student_id_gesture_id_unique');
        } catch (\Throwable) {}

        try {
            DB::statement('ALTER TABLE lesson_assignments ADD UNIQUE KEY la_lesson_student_year (lesson_id, student_id, school_year_id)');
        } catch (\Throwable) {}

        try {
            DB::statement('ALTER TABLE lesson_assignments DROP INDEX lesson_assignments_lesson_id_student_id_unique');
        } catch (\Throwable) {}

        if (Schema::hasTable('student_skill_mastery')) {
            try {
                DB::statement('ALTER TABLE student_skill_mastery ADD UNIQUE KEY ssm_student_gesture_year (student_id, gesture_id, school_year_id)');
            } catch (\Throwable) {}
            try {
                DB::statement('ALTER TABLE student_skill_mastery DROP INDEX student_skill_mastery_student_id_gesture_id_unique');
            } catch (\Throwable) {}
        }
    }

    public function down(): void
    {
        // Restore old unique constraints
        try {
            DB::statement('ALTER TABLE gesture_performances DROP INDEX gp_student_gesture_year');
            DB::statement('ALTER TABLE gesture_performances ADD UNIQUE KEY gesture_performances_student_id_gesture_id_unique (student_id, gesture_id)');
        } catch (\Throwable) {}

        try {
            DB::statement('ALTER TABLE lesson_assignments DROP INDEX la_lesson_student_year');
            DB::statement('ALTER TABLE lesson_assignments ADD UNIQUE KEY lesson_assignments_lesson_id_student_id_unique (lesson_id, student_id)');
        } catch (\Throwable) {}

        if (Schema::hasTable('student_skill_mastery')) {
            try {
                DB::statement('ALTER TABLE student_skill_mastery DROP INDEX ssm_student_gesture_year');
                DB::statement('ALTER TABLE student_skill_mastery ADD UNIQUE KEY student_skill_mastery_student_id_gesture_id_unique (student_id, gesture_id)');
            } catch (\Throwable) {}
            Schema::table('student_skill_mastery', function (Blueprint $table) {
                $table->dropColumn('school_year_id');
            });
        }

        Schema::table('checkpoint_exam_attempts', function (Blueprint $table) {
            $table->dropColumn('school_year_id');
        });
        Schema::table('lesson_assignments', function (Blueprint $table) {
            $table->dropColumn('school_year_id');
        });
        Schema::table('student_lesson_progress', function (Blueprint $table) {
            $table->dropColumn('school_year_id');
        });
        Schema::table('gesture_performances', function (Blueprint $table) {
            $table->dropColumn('school_year_id');
        });
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn('school_year_id');
        });

        Schema::dropIfExists('school_years');
    }

    // ── Backfill helpers ─────────────────────────────────────────────────────

    private static function backfillSchoolYear(int $syId): void
    {
        DB::table('quiz_attempts')->whereNull('school_year_id')->update(['school_year_id' => $syId]);
        DB::table('gesture_performances')->whereNull('school_year_id')->update(['school_year_id' => $syId]);
        DB::table('student_lesson_progress')->whereNull('school_year_id')->update(['school_year_id' => $syId]);
        DB::table('lesson_assignments')->whereNull('school_year_id')->update(['school_year_id' => $syId]);
        DB::table('checkpoint_exam_attempts')->whereNull('school_year_id')->update(['school_year_id' => $syId]);
        if (Schema::hasTable('student_skill_mastery')) {
            DB::table('student_skill_mastery')->whereNull('school_year_id')->update(['school_year_id' => $syId]);
        }
    }

    private static function backfillSchoolYearForStudents(int $syId, \Illuminate\Support\Collection $studentIds): void
    {
        if ($studentIds->isEmpty()) {
            return;
        }

        DB::table('quiz_attempts')->whereIn('student_id', $studentIds)->whereNull('school_year_id')->update(['school_year_id' => $syId]);
        DB::table('gesture_performances')->whereIn('student_id', $studentIds)->whereNull('school_year_id')->update(['school_year_id' => $syId]);
        DB::table('student_lesson_progress')->whereIn('student_id', $studentIds)->whereNull('school_year_id')->update(['school_year_id' => $syId]);
        DB::table('lesson_assignments')->whereIn('student_id', $studentIds)->whereNull('school_year_id')->update(['school_year_id' => $syId]);
        DB::table('checkpoint_exam_attempts')->whereIn('student_id', $studentIds)->whereNull('school_year_id')->update(['school_year_id' => $syId]);
        if (Schema::hasTable('student_skill_mastery')) {
            DB::table('student_skill_mastery')->whereIn('student_id', $studentIds)->whereNull('school_year_id')->update(['school_year_id' => $syId]);
        }
    }
};
