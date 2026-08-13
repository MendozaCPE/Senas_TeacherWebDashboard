<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL doesn't support modifying enums with Doctrine, so we use raw SQL
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('teacher', 'student', 'admin') NOT NULL DEFAULT 'student'");
    }

    public function down(): void
    {
        // Revert admin users to teacher before removing the enum value
        DB::statement("UPDATE users SET role = 'teacher' WHERE role = 'admin'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('teacher', 'student') NOT NULL DEFAULT 'student'");
    }
};
