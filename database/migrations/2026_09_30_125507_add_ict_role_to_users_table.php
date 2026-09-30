<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('teacher','student','admin','teacher_leader','ict') NOT NULL DEFAULT 'student'");
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET role = 'teacher' WHERE role = 'ict'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('teacher','student','admin','teacher_leader') NOT NULL DEFAULT 'student'");
    }
};
