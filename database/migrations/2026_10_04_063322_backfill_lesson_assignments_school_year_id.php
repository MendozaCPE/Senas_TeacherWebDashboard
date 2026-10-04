<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill school_year_id on lesson_assignments rows that were created
     * before the column was introduced (or before it was being set).
     *
     * Strategy: for each NULL row, join to the student record and look up the
     * school_years row whose name matches the student's school_year string and
     * whose school_id matches the student's school_id.
     */
    public function up(): void
    {
        // Update lesson_assignments where school_year_id is NULL
        // by joining to students → school_years (by name + school_id)
        DB::statement("
            UPDATE lesson_assignments la
            INNER JOIN students s ON s.student_id = la.student_id
            INNER JOIN school_years sy
                ON sy.name = s.school_year
               AND sy.school_id = s.school_id
            SET la.school_year_id = sy.id
            WHERE la.school_year_id IS NULL
        ");
    }

    public function down(): void
    {
        // Not reversible — we would need the original NULL values to restore.
        // Intentionally a no-op.
    }
};
