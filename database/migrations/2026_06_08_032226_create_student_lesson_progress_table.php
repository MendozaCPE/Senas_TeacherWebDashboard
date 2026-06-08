<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('student_lesson_progress', function (Blueprint $table) {
            $table->id('progress_id');
            $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained('lessons', 'lesson_id')->onDelete('cascade');
            $table->integer('current_step')->default(0);  // Which content step they're on
            $table->boolean('lesson_completed')->default(false);
            $table->boolean('quiz_completed')->default(false);
            $table->integer('quiz_score')->nullable();
            $table->timestamp('last_accessed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_lesson_progress');
    }
};
