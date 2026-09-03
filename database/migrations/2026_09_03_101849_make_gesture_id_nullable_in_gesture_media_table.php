<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Make gesture_id nullable in gesture_media so Admin can upload
 * standalone system media that isn't tied to an existing gesture.
 *
 * Uses raw SQL because doctrine/dbal is not installed.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop the existing FK, alter the column to nullable, re-add FK
        DB::statement('ALTER TABLE gesture_media DROP FOREIGN KEY gesture_media_gesture_id_foreign');
        DB::statement('ALTER TABLE gesture_media MODIFY gesture_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE gesture_media ADD CONSTRAINT gesture_media_gesture_id_foreign
                        FOREIGN KEY (gesture_id) REFERENCES gestures(gesture_id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE gesture_media DROP FOREIGN KEY gesture_media_gesture_id_foreign');
        DB::statement('ALTER TABLE gesture_media MODIFY gesture_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE gesture_media ADD CONSTRAINT gesture_media_gesture_id_foreign
                        FOREIGN KEY (gesture_id) REFERENCES gestures(gesture_id) ON DELETE CASCADE');
    }
};
