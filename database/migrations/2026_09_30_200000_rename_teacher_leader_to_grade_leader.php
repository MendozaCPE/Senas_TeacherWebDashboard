<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Widen the enum to include both old and new values temporarily
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('teacher','student','admin','teacher_leader','grade_leader','ict') NOT NULL DEFAULT 'student'");
        // 2. Rename existing rows
        DB::statement("UPDATE users SET role = 'grade_leader' WHERE role = 'teacher_leader'");
        // 3. Drop the old value
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('teacher','student','admin','grade_leader','ict') NOT NULL DEFAULT 'student'");
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET role = 'teacher_leader' WHERE role = 'grade_leader'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('teacher','student','admin','teacher_leader','ict') NOT NULL DEFAULT 'student'");
    }
};
