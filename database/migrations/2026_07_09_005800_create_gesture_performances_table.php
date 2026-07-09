//database/migrations/2026_07_09_005800_create_gesture_performances_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gesture_performances', function (Blueprint $table) {
            $table->id('performance_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('gesture_id');
            $table->unsignedBigInteger('module_id')->nullable(); // Add this for direct module reference
            
            // Performance metrics
            $table->integer('attempts')->default(0);
            $table->integer('successful_attempts')->default(0);
            $table->integer('wrong_attempts')->default(0);
            $table->integer('consecutive_wrong')->default(0);
            
            // Timing
            $table->timestamp('first_attempt_at')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('mastered_at')->nullable();
            
            // Mastery status
            $table->boolean('is_mastered')->default(false);
            $table->enum('mastery_level', ['needs_practice', 'developing', 'proficient', 'mastered'])
                ->default('needs_practice');
            
            // Session tracking
            $table->string('session_id', 100)->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
            $table->foreign('gesture_id')->references('gesture_id')->on('gestures')->onDelete('cascade');
            $table->foreign('module_id')->references('module_id')->on('gesture_modules')->onDelete('set null');
            
            // Unique constraint - one record per student per gesture
            $table->unique(['student_id', 'gesture_id']);
            
            // Indexes
            $table->index(['student_id', 'module_id']);
            $table->index(['is_mastered', 'mastery_level']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('gesture_performances');
    }
};