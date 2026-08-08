<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Alters the lesson_contents.content_type enum to include 'youtube_video'.
     * MySQL requires rebuilding the enum via MODIFY COLUMN.
     * The existing media_url column will store the YouTube embed URL (e.g. youtube.com watch URL).
     */
    public function up(): void
    {
        // MySQL: modify the enum column to add the new value
        DB::statement("ALTER TABLE lesson_contents MODIFY COLUMN content_type ENUM('text','image','video','gesture_demo','youtube_video') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, update any youtube_video rows back to 'video' so the down migration doesn't fail
        DB::table('lesson_contents')
            ->where('content_type', 'youtube_video')
            ->update(['content_type' => 'video']);

        DB::statement("ALTER TABLE lesson_contents MODIFY COLUMN content_type ENUM('text','image','video','gesture_demo') NOT NULL");
    }
};
