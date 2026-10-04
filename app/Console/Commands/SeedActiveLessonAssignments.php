<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedActiveLessonAssignments extends Command
{
    protected $signature = 'seed:active-lesson-assignments';
    protected $description = 'One-time: create active-year (sy_id=5) lesson_assignment records for 2026-2027 students, based on their archived sy_id=1 records. Preserves archived records untouched.';

    public function handle(): int
    {
        $activeSyId   = 5;  // 2026-2027
        $archivedSyId = 1;  // 2025-2026

        // Get all 2026-2027 student IDs
        $studentIds = DB::table('students')
            ->where('school_year', '2026-2027')
            ->where('status', 'active')
            ->pluck('student_id');

        $this->info("Active 2026-2027 students: " . $studentIds->count());

        // Get the archived assignments for these students
        $archived = DB::table('lesson_assignments')
            ->whereIn('student_id', $studentIds)
            ->where('school_year_id', $archivedSyId)
            ->get();

        $this->info("Archived sy_id={$archivedSyId} records found: " . $archived->count());

        $created = 0;
        $skipped = 0;

        foreach ($archived as $row) {
            // Skip if an active-year record already exists for this student+lesson
            $exists = DB::table('lesson_assignments')
                ->where('lesson_id',      $row->lesson_id)
                ->where('student_id',     $row->student_id)
                ->where('school_year_id', $activeSyId)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            DB::table('lesson_assignments')->insert([
                'lesson_id'      => $row->lesson_id,
                'student_id'     => $row->student_id,
                'assigned_at'    => $row->assigned_at,
                'status'         => 'pending',
                'is_locked'      => $row->is_locked ?? false,
                'notified'       => $row->notified ?? false,
                'school_year_id' => $activeSyId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            $created++;
        }

        $this->info("Created: {$created} | Skipped (already existed): {$skipped}");
        $this->info("Done. Dashboard can now safely filter by school_year_id={$activeSyId}.");

        return Command::SUCCESS;
    }
}
