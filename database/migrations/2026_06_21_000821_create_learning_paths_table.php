<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_paths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            
            // Learning preferences
            $table->enum('fsl_level', ['Beginner', 'Intermediate', 'Advanced'])->nullable();
            $table->enum('learning_goal', ['Alphabet_Numbers', 'Greetings', 'Classroom_Words', 'Everything'])->nullable();
            $table->enum('practice_time', ['5_10_min', '15_20_min', '30_min', '1_hour_plus'])->nullable();
            
            // Assessment status
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            
            // ✅ Fix: Reference the correct primary key column
            $table->foreign('student_id')
                  ->references('student_id')  // ← Use student_id, not id
                  ->on('students')
                  ->onDelete('cascade');
            
            // Ensure one learning path per student
            $table->unique('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_paths');
    }
};