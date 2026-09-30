<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * TeacherLeaderSeeder
 *
 * Creates a single demo Teacher Leader account that can be used to log in and
 * explore the Teacher Leader portal.
 *
 * Credentials (change after first login):
 *   Email    : teacherleader@senas.edu
 *   Password : TeacherLeader@2026
 *
 * The account is assigned to the first school in the schools table.
 * Re-running is idempotent — it skips creation if the email already exists.
 */
class TeacherLeaderSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ── 1. Guard: skip if already exists ─────────────────────────────
        $exists = DB::table('users')->where('email', 'teacherleader@senas.edu')->exists();
        if ($exists) {
            $this->command->warn('⚠  Teacher Leader account already exists. Skipping.');
            return;
        }

        // ── 2. Resolve school — pick the first available school ───────────
        $school = DB::table('schools')->orderBy('id')->first();
        if (! $school) {
            $this->command->error('✗  No schools found in the database. Add a school first, then re-run this seeder.');
            return;
        }

        // ── 3. Create the User row ────────────────────────────────────────
        $userId = DB::table('users')->insertGetId([
            'username'          => 'teacher_leader_demo',
            'name'              => 'Teacher Leader',
            'email'             => 'teacherleader@senas.edu',
            'email_verified_at' => $now,
            'password'          => Hash::make('TeacherLeader@2026'),
            'role'              => 'teacher_leader',
            'is_system'         => false,
            'status'            => 'active',
            'google_id'         => null,
            'profile_photo'     => null,
            'remember_token'    => null,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        // ── 4. Create the Teacher profile row (needed for school_id) ──────
        DB::table('teachers')->insert([
            'user_id'        => $userId,
            'school_id'      => $school->id,
            'first_name'     => 'Teacher',
            'last_name'      => 'Leader',
            'specialization' => 'Regular',
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $this->command->info('✅ Teacher Leader account created.');
        $this->command->info('   Email    : teacherleader@senas.edu');
        $this->command->info('   Password : TeacherLeader@2026');
        $this->command->info('   School   : ' . $school->name . ' (id: ' . $school->id . ')');
        $this->command->newLine();
        $this->command->warn('   ⚠  Change the password after first login.');
    }
}
