<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EscalatedReportsDummySeeder extends Seeder
{
    public function run(): void
    {
        $adminUserId = 6;
        $now = Carbon::now();

        // Grab 2 resolved report IDs to escalate
        $ids = DB::table('help_requests')->where('status', 'resolved')->limit(2)->pluck('help_request_id');

        foreach ($ids as $id) {
            DB::table('help_requests')->where('help_request_id', $id)->update([
                'status'            => 'escalated',
                'escalated_at'      => $now->subHours(rand(1, 12)),
                'escalated_by'      => $adminUserId,
                'escalation_reason' => 'Student concern could not be resolved at teacher level. Requires admin review and action.',
                'updated_at'        => $now,
            ]);
        }

        $this->command->info('✅ ' . count($ids) . ' reports escalated for admin view.');
    }
}
