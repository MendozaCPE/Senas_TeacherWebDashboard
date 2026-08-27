<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Increase pin column from VARCHAR(4) to VARCHAR(255) so it can store bcrypt hashes.
 * Required for the PIN hashing security upgrade (students:hash-pins).
 * Uses raw SQL to avoid requiring doctrine/dbal.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Raw SQL — avoids the doctrine/dbal requirement for ->change()
        DB::statement('ALTER TABLE `students` MODIFY `pin` VARCHAR(255) NOT NULL');
    }

    public function down(): void
    {
        // Revert column size (WARNING: hashed values will be truncated — data loss)
        DB::statement('ALTER TABLE `students` MODIFY `pin` VARCHAR(4) NOT NULL');
    }
};
