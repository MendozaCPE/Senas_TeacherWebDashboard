<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixSchoolYearDuplicates extends Command
{
    protected $signature = 'fix:school-year-duplicates';
    protected $description = 'One-time: merge duplicate school_year records and re-stamp all related tables.';

    public function handle(): int
    {
        $this->info('School year records before fix:');
        foreach (DB::table('school_years')->get() as $r) {
            $this->line("  id={$r->id} name={$r->name} status={$r->status}");
        }

        // Map: duplicate_id => canonical_id (the one to KEEP)
        // id=3 (2025-2026) is a duplicate of id=1 (2025-2026)
        // id=4 (2024-2025) is a duplicate of id=2 (2024-2025)
        $merges = [3 => 1, 4 => 2];

        foreach ($merges as $oldId => $keepId) {
            $oldSy = DB::table('school_years')->where('id', $oldId)->first();
            if (!$oldSy) {
                $this->warn("  id={$oldId} not found – skipping.");
                continue;
            }

            $this->info("Merging id={$oldId} ({$oldSy->name}) → id={$keepId}");

            $tables = [
                'lesson_assignments'     => 'school_year_id',
                'student_lesson_progress'=> 'school_year_id',
                'gesture_performances'   => 'school_year_id',
                'teacher_notifications'  => 'related_school_year_id',
                'xp_log'                 => 'school_year_id',
                'audit_logs'             => 'school_year_id',
            ];

            foreach ($tables as $table => $col) {
                try {
                    $n = DB::table($table)->where($col, $oldId)->update([$col => $keepId]);
                    if ($n > 0) $this->line("  {$table}.{$col}: {$n} row(s) re-stamped");
                } catch (\Throwable) {
                    // Table or column may not exist in all envs — safe to skip
                }
            }

            DB::table('school_years')->where('id', $oldId)->delete();
            $this->line("  Deleted school_year id={$oldId}");
        }

        $this->info('School year records after fix:');
        foreach (DB::table('school_years')->get() as $r) {
            $this->line("  id={$r->id} name={$r->name} status={$r->status}");
        }

        return Command::SUCCESS;
    }
}
