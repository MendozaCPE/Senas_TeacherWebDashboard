<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillLessonAssignmentArchive extends Command
{
    protected $signature = 'backfill:lesson-assignment-archive';
    protected $description = 'One-time: archive lesson_assignments from a previous school year so the new year starts at 0% completion.';

    public function handle(): int
    {
        // Find every pair of (archived SY, new active SY) and fix assignments.
        // Logic: for each student whose school_year = newActiveSy->name,
        // their lesson_assignments should be stamped with archivedSy->id
        // (so they appear as old-year history) and status reset to pending.

        $archivedYears = DB::table('school_years')->where('status', 'archived')->get();
        $activeYear    = DB::table('school_years')->where('status', 'active')->latest('id')->first();

        if (!$activeYear) {
            $this->error('No active school year found.');
            return Command::FAILURE;
        }

        $this->info("Active SY: id={$activeYear->id} name={$activeYear->name}");

        // Students in the NEW year whose assignments still carry any school_year_id
        // that is NOT the archived year (including NULL or wrong id).
        $newYearStudentIds = DB::table('students')
            ->where('school_year', $activeYear->name)
            ->pluck('student_id');

        if ($newYearStudentIds->isEmpty()) {
            $this->warn('No students found for the active school year. Nothing to backfill.');
            return Command::SUCCESS;
        }

        $this->info("Students in active year ({$activeYear->name}): {$newYearStudentIds->count()}");

        // Determine which archived year these students came from.
        // Use the most recently archived year.
        $previousYear = DB::table('school_years')
            ->where('status', 'archived')
            ->latest('id')
            ->first();

        if (!$previousYear) {
            $this->warn('No archived school year found. Skipping school_year_id stamp.');
            $previousSyId = null;
        } else {
            $previousSyId = $previousYear->id;
            $this->info("Archived SY: id={$previousYear->id} name={$previousYear->name}");
        }

        // Count how many assignments need fixing:
        // assignments for new-year students that are NOT already stamped with previousSyId
        $toFix = DB::table('lesson_assignments')
            ->whereIn('student_id', $newYearStudentIds)
            ->when($previousSyId, fn($q) => $q->where(function ($q2) use ($previousSyId) {
                $q2->where('school_year_id', '!=', $previousSyId)->orWhereNull('school_year_id');
            }))
            ->count();

        $this->info("Lesson assignments to backfill: {$toFix}");

        if ($toFix === 0) {
            $this->info('Nothing to fix — all assignments are already correctly stamped.');
            return Command::SUCCESS;
        }

        // Stamp with archived year id and reset status to pending.
        $updated = DB::table('lesson_assignments')
            ->whereIn('student_id', $newYearStudentIds)
            ->when($previousSyId, fn($q) => $q->where(function ($q2) use ($previousSyId) {
                $q2->where('school_year_id', '!=', $previousSyId)->orWhereNull('school_year_id');
            }))
            ->update([
                'school_year_id' => $previousSyId,
                'status'         => 'pending',
                'updated_at'     => now(),
            ]);

        $this->info("Updated {$updated} lesson_assignment row(s): school_year_id={$previousSyId}, status=pending.");
        $this->info('Done. Dashboard should now show 0% completion for the new school year.');

        return Command::SUCCESS;
    }
}
