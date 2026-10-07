<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Add the existing Christian Paul Mendoza demo dataset and associate it
     * with his school's active school year for teacher analytics.
     */
    public function up(): void
    {
        $teacher = DB::table('teachers')
            ->join('users', 'teachers.user_id', '=', 'users.id')
            ->where('users.email', 'christianpaulmendoza10@gmail.com')
            ->select('teachers.*')
            ->first();

        if (!$teacher) {
            $teacher = DB::table('teachers')
                ->where('first_name', 'like', 'Christian%')
                ->where('last_name', 'like', '%Mendoza%')
                ->first();
        }

        if (!$teacher) {
            return;
        }

        $schoolYear = DB::table('school_years')
            ->where('school_id', $teacher->school_id)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        if (!$schoolYear) {
            return;
        }

        // Reuse the project's curated dummy student, lesson, and activity data.
        $seedExitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PaulDummyDataSeeder',
            '--force' => true,
        ]);
        if ($seedExitCode !== 0) {
            throw new RuntimeException('Seeding the Christian Paul Mendoza analytics data failed: ' . Artisan::output());
        }

        $now = now();
        $studentIds = DB::table('students')
            ->whereIn('lrn', [
                '123456789001', '123456789002', '123456789003',
                '123456789004', '123456789005',
            ])
            ->pluck('student_id');

        if ($studentIds->isEmpty()) {
            throw new RuntimeException('The Christian Paul Mendoza dummy students were not created.');
        }

        DB::table('students')->whereIn('student_id', $studentIds)->update([
            'teacher_id' => $teacher->id,
            'school_id' => $teacher->school_id,
            'school_year' => $schoolYear->name,
            'is_enrolled' => true,
            'updated_at' => $now,
        ]);

        foreach (['lesson_assignments', 'student_lesson_progress', 'quiz_attempts', 'gesture_performances', 'checkpoint_exam_attempts', 'student_skill_mastery'] as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'school_year_id')) {
                continue;
            }

            DB::table($table)->whereIn('student_id', $studentIds)->update([
                'school_year_id' => $schoolYear->id,
            ]);
        }

        // The demo seeder creates activity relative to today's date. If the
        // active school year is a different calendar year, move only this
        // demo activity into the selected year so analytics date filters can
        // find it. Keep historical rows for other school years untouched.
        if (preg_match('/^(\d{4})-(\d{4})$/', $schoolYear->name, $yearParts)) {
            $yearOffset = (int) $yearParts[1] - (int) Carbon::now()->format('Y');
            if ($yearOffset !== 0) {
                $dateColumnsByTable = [
                    'students' => ['last_activity_date'],
                    'lesson_assignments' => ['assigned_at', 'completed_at', 'created_at', 'updated_at'],
                    'student_lesson_progress' => ['last_accessed_at', 'created_at', 'updated_at'],
                    'quiz_attempts' => ['started_at', 'completed_at', 'created_at', 'updated_at'],
                    'gesture_performances' => ['first_attempt_at', 'last_attempt_at', 'mastered_at', 'created_at', 'updated_at'],
                    'checkpoint_exam_attempts' => ['started_at', 'completed_at', 'created_at', 'updated_at'],
                    'student_skill_mastery' => ['created_at', 'updated_at'],
                ];

                foreach ($dateColumnsByTable as $table => $columns) {
                    if (!Schema::hasTable($table)) {
                        continue;
                    }

                    foreach ($columns as $column) {
                        if (!Schema::hasColumn($table, $column)) {
                            continue;
                        }

                        $query = DB::table($table)->whereIn('student_id', $studentIds)->whereNotNull($column);
                        if ($table !== 'students' && Schema::hasColumn($table, 'school_year_id')) {
                            $query->where('school_year_id', $schoolYear->id);
                        }

                        $expression = $yearOffset > 0
                            ? "DATE_ADD(`{$column}`, INTERVAL {$yearOffset} YEAR)"
                            : "DATE_SUB(`{$column}`, INTERVAL " . abs($yearOffset) . " YEAR)";
                        $query->update([$column => DB::raw($expression)]);
                    }
                }
            }
        }

        foreach (DB::table('students')->whereIn('student_id', $studentIds)->get() as $student) {
            DB::table('student_year_enrollments')->insertOrIgnore([
                'student_id' => $student->student_id,
                'teacher_id' => $teacher->id,
                'school_id' => $teacher->school_id,
                'school_year_id' => $schoolYear->id,
                'school_year_name' => $schoolYear->name,
                'program_type' => $student->program_type,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'enrolled_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Keep the demo records on rollback; removing seeded classroom data would
     * also delete rows that may have been edited after migration.
     */
    public function down(): void
    {
        // Intentionally irreversible: this migration seeds analytics demo data.
    }
};
