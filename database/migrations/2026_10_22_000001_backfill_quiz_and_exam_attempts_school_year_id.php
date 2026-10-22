<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill school_year_id on quiz_attempts, checkpoint_exam_attempts,
     * student_lesson_progress, and gesture_performances rows where school_year_id IS NULL.
     */
    public function up(): void
    {
        // 1. Backfill quiz_attempts by matching student's school_year & school_id
        DB::statement("
            UPDATE IGNORE quiz_attempts qa
            INNER JOIN students s ON s.student_id = qa.student_id
            INNER JOIN school_years sy
                ON sy.name = s.school_year
               AND sy.school_id = s.school_id
            SET qa.school_year_id = sy.id
            WHERE qa.school_year_id IS NULL
        ");

        // Fallback for quiz_attempts where student's school has an active school year
        DB::statement("
            UPDATE IGNORE quiz_attempts qa
            INNER JOIN students s ON s.student_id = qa.student_id
            INNER JOIN school_years sy
                ON sy.school_id = s.school_id
               AND sy.status = 'active'
            SET qa.school_year_id = sy.id
            WHERE qa.school_year_id IS NULL
        ");

        // 2. Backfill checkpoint_exam_attempts
        DB::statement("
            UPDATE IGNORE checkpoint_exam_attempts cea
            INNER JOIN students s ON s.student_id = cea.student_id
            INNER JOIN school_years sy
                ON sy.name = s.school_year
               AND sy.school_id = s.school_id
            SET cea.school_year_id = sy.id
            WHERE cea.school_year_id IS NULL
        ");

        DB::statement("
            UPDATE IGNORE checkpoint_exam_attempts cea
            INNER JOIN students s ON s.student_id = cea.student_id
            INNER JOIN school_years sy
                ON sy.school_id = s.school_id
               AND sy.status = 'active'
            SET cea.school_year_id = sy.id
            WHERE cea.school_year_id IS NULL
        ");

        // 3. Backfill student_lesson_progress
        DB::statement("
            UPDATE IGNORE student_lesson_progress slp
            INNER JOIN students s ON s.student_id = slp.student_id
            INNER JOIN school_years sy
                ON sy.name = s.school_year
               AND sy.school_id = s.school_id
            SET slp.school_year_id = sy.id
            WHERE slp.school_year_id IS NULL
        ");

        DB::statement("
            UPDATE IGNORE student_lesson_progress slp
            INNER JOIN students s ON s.student_id = slp.student_id
            INNER JOIN school_years sy
                ON sy.school_id = s.school_id
               AND sy.status = 'active'
            SET slp.school_year_id = sy.id
            WHERE slp.school_year_id IS NULL
        ");

        // 4. Backfill gesture_performances
        // Note: gesture_performances has a UNIQUE key (student_id, gesture_id, school_year_id).
        // First, delete redundant NULL rows where a row with that student, gesture, and target school year already exists.
        DB::statement("
            DELETE gp_null FROM gesture_performances gp_null
            INNER JOIN students s ON s.student_id = gp_null.student_id
            INNER JOIN school_years sy
                ON sy.name = s.school_year
               AND sy.school_id = s.school_id
            INNER JOIN gesture_performances gp_existing
                ON gp_existing.student_id = gp_null.student_id
               AND gp_existing.gesture_id = gp_null.gesture_id
               AND gp_existing.school_year_id = sy.id
            WHERE gp_null.school_year_id IS NULL
        ");

        DB::statement("
            UPDATE IGNORE gesture_performances gp
            INNER JOIN students s ON s.student_id = gp.student_id
            INNER JOIN school_years sy
                ON sy.name = s.school_year
               AND sy.school_id = s.school_id
            SET gp.school_year_id = sy.id
            WHERE gp.school_year_id IS NULL
        ");

        // Fallback for gesture_performances
        DB::statement("
            DELETE gp_null FROM gesture_performances gp_null
            INNER JOIN students s ON s.student_id = gp_null.student_id
            INNER JOIN school_years sy
                ON sy.school_id = s.school_id
               AND sy.status = 'active'
            INNER JOIN gesture_performances gp_existing
                ON gp_existing.student_id = gp_null.student_id
               AND gp_existing.gesture_id = gp_null.gesture_id
               AND gp_existing.school_year_id = sy.id
            WHERE gp_null.school_year_id IS NULL
        ");

        DB::statement("
            UPDATE IGNORE gesture_performances gp
            INNER JOIN students s ON s.student_id = gp.student_id
            INNER JOIN school_years sy
                ON sy.school_id = s.school_id
               AND sy.status = 'active'
            SET gp.school_year_id = sy.id
            WHERE gp.school_year_id IS NULL
        ");
    }

    public function down(): void
    {
        // Not reversible
    }
};
