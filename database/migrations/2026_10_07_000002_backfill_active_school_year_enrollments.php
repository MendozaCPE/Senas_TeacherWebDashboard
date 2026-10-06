<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill active enrollments created before enrollment history was recorded.
     * The mutable student row is safe to use here because it describes the
     * student's current active school year and enrollment status.
     */
    public function up(): void
    {
        $students = DB::table('students as s')
            ->join('school_years as sy', function ($join) {
                $join->on('sy.name', '=', 's.school_year')
                    ->on('sy.school_id', '=', 's.school_id');
            })
            ->where('sy.status', 'active')
            ->where('s.is_enrolled', true)
            ->whereNotNull('s.school_year')
            ->where('s.school_year', '!=', '')
            ->select(
                's.student_id',
                's.teacher_id',
                's.school_id',
                'sy.id as school_year_id',
                's.school_year as school_year_name',
                's.program_type',
                's.grade_level',
                's.section',
                's.created_at'
            )
            ->get();

        foreach ($students as $student) {
            DB::table('student_year_enrollments')->insertOrIgnore([
                'student_id'       => $student->student_id,
                'teacher_id'       => $student->teacher_id,
                'school_id'        => $student->school_id,
                'school_year_id'   => $student->school_year_id,
                'school_year_name' => $student->school_year_name,
                'program_type'     => $student->program_type,
                'grade_level'      => $student->grade_level,
                'section'          => $student->section,
                'enrolled_at'      => $student->created_at ?? now(),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Backfilled enrollment history is valid application data and is kept.
    }
};
