<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make subject_type and subject_id nullable so audit logs can be
        // recorded for actions that have no specific subject (e.g. login/logout).
        DB::statement('ALTER TABLE audit_logs MODIFY COLUMN subject_type VARCHAR(255) NULL');
        DB::statement('ALTER TABLE audit_logs MODIFY COLUMN subject_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE audit_logs MODIFY COLUMN subject_type VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE audit_logs MODIFY COLUMN subject_id BIGINT UNSIGNED NOT NULL');
    }
};
