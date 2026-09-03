<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SystemTemplateTeacherSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Check if a system user already exists
        $existing = DB::table('users')->where('is_system', true)->first();

        if ($existing) {
            // User exists — make sure a teacher record is also linked
            $hasTeacher = DB::table('teachers')->where('user_id', $existing->id)->exists();
            if ($hasTeacher) {
                $this->command->warn('System template user already exists (id: ' . $existing->id . ') with a teacher record. Skipping.');
                return;
            }
            // Teacher record missing (partial state) — create it now
            $userId = $existing->id;
            $this->command->warn('System user (id: ' . $userId . ') exists but has no teacher record. Creating teacher record...');
        } else {
            // Create the system user fresh
            $userId = DB::table('users')->insertGetId([
                'username'          => 'system_template',
                'name'              => 'System Template',
                'email'             => 'system@senas.internal',
                'email_verified_at' => $now,
                'password'          => Hash::make(bin2hex(random_bytes(32))),
                'role'              => 'teacher',
                'is_system'         => true,
                'status'            => 'active',
                'google_id'         => null,
                'profile_photo'     => null,
                'remember_token'    => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // Use the first available school_id (NOT NULL constraint on teachers)
        $schoolId = DB::table('schools')->value('id') ?? 1;

        DB::table('teachers')->insert([
            'user_id'    => $userId,
            'school_id'  => $schoolId,
            'first_name' => 'System',
            'last_name'  => 'Template',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->command->info('✅ System template teacher created (user_id: ' . $userId . ').');
    }
}
