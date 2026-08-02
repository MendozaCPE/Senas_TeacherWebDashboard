<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // checkpoint_exam_assignments.status was truncating 'failed' -> caused
        // SQLSTATE[01000] 1265 warning when a student failed an exam.
        DB::statement("ALTER TABLE checkpoint_exam_assignments
            MODIFY COLUMN status ENUM('pending','in_progress','completed','failed') NOT NULL DEFAULT 'pending'");

        // checkpoint_exam_attempts.status writes the same 'completed'/'failed'
        // values (see submitCheckpointExam) — align it so it doesn't hit the
        // same truncation bug the first time a student fails.
        DB::statement("ALTER TABLE checkpoint_exam_attempts
            MODIFY COLUMN status ENUM('completed','failed') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE checkpoint_exam_assignments
            MODIFY COLUMN status ENUM('pending','in_progress','completed') NOT NULL DEFAULT 'pending'");

        DB::statement("ALTER TABLE checkpoint_exam_attempts
            MODIFY COLUMN status ENUM('completed') NOT NULL");
    }
};