<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * IctCoordinatorSeeder
 *
 * Creates a demo School ICT Coordinator account.
 *
 * Credentials (change after first login):
 *   Email    : ict@senas.edu
 *   Password : IctCoordinator@2026
 *
 * Assigned to the first school in the schools table.
 * Re-running is idempotent — skips if email already exists.
 */
class IctCoordinatorSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        if (DB::table('users')->where('email', 'ict@senas.edu')->exists()) {
            $this->command->warn('⚠  ICT Coordinator account already exists. Skipping.');
            return;
        }

        $school = DB::table('schools')->orderBy('id')->first();
        if (! $school) {
            $this->command->error('✗  No schools found. Add a school first, then re-run this seeder.');
            return;
        }

        $userId = DB::table('users')->insertGetId([
            'username'          => 'ict_coordinator_demo',
            'name'              => 'ICT Coordinator',
            'email'             => 'ict@senas.edu',
            'email_verified_at' => $now,
            'password'          => Hash::make('IctCoordinator@2026'),
            'role'              => 'ict',
            'is_system'         => false,
            'status'            => 'active',
            'google_id'         => null,
            'profile_photo'     => null,
            'remember_token'    => null,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        DB::table('teachers')->insert([
            'user_id'        => $userId,
            'school_id'      => $school->id,
            'first_name'     => 'ICT',
            'last_name'      => 'Coordinator',
            'specialization' => 'Regular',
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $this->command->info('✅ ICT Coordinator account created.');
        $this->command->info('   Email    : ict@senas.edu');
        $this->command->info('   Password : IctCoordinator@2026');
        $this->command->info('   School   : ' . $school->name . ' (id: ' . $school->id . ')');
        $this->command->newLine();
        $this->command->warn('   ⚠  Change the password after first login.');
    }
}
