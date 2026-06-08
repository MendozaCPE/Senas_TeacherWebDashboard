<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\School;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ==========================================
        // 1. CREATE SCHOOL (Nasugbu West Central School)
        // ==========================================
        $school = School::create([
            'name' => 'Nasugbu West Central School',
            'address' => 'Concepcion St., Barangay IV, Nasugbu, Batangas',
            'region' => 'IV-A',
            'division' => 'Batangas Province',
        ]);

        // ==========================================
        // 2. TEACHER ACCOUNT (Maam Mila)
        // ==========================================
        $teacherUser = User::create([
            'name' => 'Emma Ruth',  // ← CHANGED (was null)
            'username' => 'emmaruth',
            'email' => 'emmaruth@deped.gov.ph',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'active',
            'google_id' => null,
        ]);

        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'school_id' => $school->id,
            'first_name' => 'Emma',
            'last_name' => 'Ruth',
            'specialization' => 'SNED',
        ]);

        // ==========================================
        // 3. STUDENT ACCOUNTS
        // ==========================================

        // Student 1: Regular
        $student1User = User::create([
            'name' => 'Juan Dela Cruz',  // ← CHANGED
            'username' => 'juandelacruz',
            'email' => null,
            'password' => null,
            'role' => 'student',
            'status' => 'active',
            'google_id' => null,
        ]);

        Student::create([
            'user_id' => $student1User->id,
            'teacher_id' => $teacher->id,
            'school_id' => $school->id,
            'lrn' => '123456789012',
            'pin' => '1234',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'age' => 10,
            'grade_level' => 'Grade 4',
            'section' => 'Rose',
            'program_type' => 'Regular',
        ]);

        // Student 2: Inclusion
        $student2User = User::create([
            'name' => 'Maria Santos',  // ← CHANGED
            'username' => 'mariasantos',
            'email' => null,
            'password' => null,
            'role' => 'student',
            'status' => 'active',
            'google_id' => null,
        ]);

        Student::create([
            'user_id' => $student2User->id,
            'teacher_id' => $teacher->id,
            'school_id' => $school->id,
            'lrn' => '234567890123',
            'pin' => '2345',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'age' => 9,
            'grade_level' => 'Grade 3',
            'section' => 'Sunflower',
            'program_type' => 'Inclusion',
        ]);

        // Student 3: Self-contained
        $student3User = User::create([
            'name' => 'Pedro Reyes',  // ← CHANGED
            'username' => 'pedroreyes',
            'email' => null,
            'password' => null,
            'role' => 'student',
            'status' => 'active',
            'google_id' => null,
        ]);

        Student::create([
            'user_id' => $student3User->id,
            'teacher_id' => $teacher->id,
            'school_id' => $school->id,
            'lrn' => '345678901234',
            'pin' => '3456',
            'first_name' => 'Pedro',
            'last_name' => 'Reyes',
            'age' => 12,
            'grade_level' => null,
            'section' => 'SPED A',
            'program_type' => 'Self-contained',
        ]);

        // Student 4: Transition
        $student4User = User::create([
            'name' => 'Ana Salvador',  // ← CHANGED
            'username' => 'anasalvador',
            'email' => null,
            'password' => null,
            'role' => 'student',
            'status' => 'active',
            'google_id' => null,
        ]);

        Student::create([
            'user_id' => $student4User->id,
            'teacher_id' => $teacher->id,
            'school_id' => $school->id,
            'lrn' => '456789012345',
            'pin' => '4567',
            'first_name' => 'Ana',
            'last_name' => 'Salvador',
            'age' => 11,
            'grade_level' => 'Grade 5',
            'section' => 'Orchid',
            'program_type' => 'Transition',
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('School: Nasugbu West Central School (Batangas Province, Region IV-A)');
        $this->command->info('Teacher login: emmaruth@deped.gov.ph / password123');
        $this->command->info('Student logins: LRN + PIN (1234, 2345, 3456, 4567)');
    }
}