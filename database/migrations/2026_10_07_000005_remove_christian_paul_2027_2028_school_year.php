<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $teacher = DB::table('teachers')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->where('users.email', 'christianpaulmendoza10@gmail.com')
            ->select('teachers.school_id')
            ->first();

        if (!$teacher) {
            return;
        }

        $schoolYear = DB::table('school_years')
            ->where('school_id', $teacher->school_id)
            ->where('name', '2027-2028')
            ->first();

        if (!$schoolYear) {
            return;
        }

        $yearScopedTables = [
            'lesson_assignments',
            'student_lesson_progress',
            'quiz_attempts',
            'gesture_performances',
            'checkpoint_exam_attempts',
            'student_skill_mastery',
        ];

        foreach ($yearScopedTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'school_year_id')
                && DB::table($table)->where('school_year_id', $schoolYear->id)->exists()) {
                throw new RuntimeException("Cannot remove 2027-2028: {$table} still references it.");
            }
        }

        if (DB::table('student_year_enrollments')
            ->where('school_id', $teacher->school_id)
            ->where(function ($query) use ($schoolYear) {
                $query->where('school_year_id', $schoolYear->id)
                    ->orWhere('school_year_name', '2027-2028');
            })
            ->exists()) {
            throw new RuntimeException('Cannot remove 2027-2028: enrollment history still references it.');
        }

        DB::table('school_years')->where('id', $schoolYear->id)->delete();
    }

    public function down(): void
    {
        // Recreating the removed school-year row would require restoring its
        // original dates and metadata, so this cleanup is intentionally final.
    }
};
