<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Repair current-year enrollment rows whose student was reassigned to a
     * different teacher after the history row had been created.
     */
    public function up(): void
    {
        DB::table('student_year_enrollments as sye')
            ->join('students as s', 's.student_id', '=', 'sye.student_id')
            ->join('school_years as sy', function ($join) {
                $join->on('sy.name', '=', 's.school_year')
                    ->on('sy.school_id', '=', 's.school_id');
            })
            ->whereColumn('sye.school_year_name', 's.school_year')
            ->where('sy.status', 'active')
            ->where('s.is_enrolled', true)
            ->update([
                'sye.teacher_id'     => DB::raw('s.teacher_id'),
                'sye.school_id'      => DB::raw('s.school_id'),
                'sye.school_year_id' => DB::raw('sy.id'),
                'sye.program_type'   => DB::raw('s.program_type'),
                'sye.grade_level'    => DB::raw('s.grade_level'),
                'sye.section'        => DB::raw('s.section'),
                'sye.updated_at'     => now(),
            ]);
    }

    public function down(): void
    {
        // Ownership corrections reflect the current active enrollment state.
    }
};
