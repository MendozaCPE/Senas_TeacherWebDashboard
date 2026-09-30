<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the role ENUM to include 'teacher_leader'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('teacher', 'student', 'admin', 'teacher_leader') NOT NULL DEFAULT 'student'");
    }

    public function down(): void
    {
        // Move teacher_leader users back to teacher before removing the enum value
        DB::statement("UPDATE users SET role = 'teacher' WHERE role = 'teacher_leader'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('teacher', 'student', 'admin') NOT NULL DEFAULT 'student'");
    }
};
