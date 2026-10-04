<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAssignmentStamp extends Command
{
    protected $signature = 'fix:assignment-stamp';
    protected $description = 'One-time: stamp lesson_assignments for 2026-2027 students with the correct archived school_year_id.';

    public function handle(): int
    {
        // Students currently in 2026-2027 were previously in 2025-2026 (id=1).
        // Their lesson_assignments should carry school_year_id=1 (archived year)
        // so the archived-year filter finds the right historical data.
        $archivedSyId = 1; // 2025-2026

        $studentIds = DB::table('students')
            ->where('school_year', '2026-2027')
            ->pluck('student_id');

        $this->info("Students in 2026-2027: " . $studentIds->count());

        if ($studentIds->isEmpty()) {
            $this->warn('No students found for 2026-2027.');
            return Command::SUCCESS;
        }

        // Count how many need fixing (not already stamped with id=1)
        $toFix = DB::table('lesson_assignments')
            ->whereIn('student_id', $studentIds)
            ->where('school_year_id', '!=', $archivedSyId)
            ->count();

        $this->info("lesson_assignments to re-stamp: {$toFix}");

        if ($toFix === 0) {
            $this->info('Nothing to fix — all already stamped correctly.');
            return Command::SUCCESS;
        }

        $updated = DB::table('lesson_assignments')
            ->whereIn('student_id', $studentIds)
            ->where('school_year_id', '!=', $archivedSyId)
            ->update([
                'school_year_id' => $archivedSyId,
                'status'         => 'pending',
                'updated_at'     => now(),
            ]);

        $this->info("Updated {$updated} row(s): school_year_id={$archivedSyId}, status=pending.");
        $this->info('Done. Archived year filter should now show correct enrolled counts.');

        return Command::SUCCESS;
    }
}
