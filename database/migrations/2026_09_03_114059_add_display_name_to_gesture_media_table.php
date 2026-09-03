<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add display_name column to gesture_media.
 * This gives every media record its own editable title that persists
 * independently of the gesture's name/display_name.
 * Uses raw SQL — no doctrine/dbal needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE gesture_media ADD COLUMN display_name VARCHAR(255) NULL AFTER file_name');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE gesture_media DROP COLUMN display_name');
    }
};
