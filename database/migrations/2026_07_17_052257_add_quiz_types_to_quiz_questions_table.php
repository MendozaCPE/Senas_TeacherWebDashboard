<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            // Add new columns for drag-and-drop and gesture recognition
            $table->json('drag_drop_pairs')->nullable()->after('media_url')
                ->comment('JSON array of pairs for drag-and-drop: [{"left":"item1","right":"match1"},...]');
            
            $table->json('gesture_data')->nullable()->after('drag_drop_pairs')
                ->comment('JSON data for gesture recognition quiz: {"target_gesture":"letter_a","variations":["A","a"],"media_path":"..."}');
            
            // Modify question_type enum to include new types
            DB::statement("ALTER TABLE quiz_questions MODIFY COLUMN question_type ENUM('multiple_choice', 'true_false', 'drag_drop', 'gesture') NOT NULL");
        });
    }

    public function down()
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn(['drag_drop_pairs', 'gesture_data']);
            DB::statement("ALTER TABLE quiz_questions MODIFY COLUMN question_type ENUM('multiple_choice', 'true_false') NOT NULL");
        });
    }
};