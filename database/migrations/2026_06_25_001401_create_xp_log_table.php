<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xp_log', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('quiz_attempt_id')->nullable();
            $table->unsignedBigInteger('lesson_id')->nullable();
            $table->string('action'); // 'quiz_completed', 'lesson_completed', 'streak_bonus', 'daily_challenge'
            $table->integer('xp_amount');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
            $table->foreign('quiz_attempt_id')->references('attempt_id')->on('quiz_attempts')->onDelete('cascade');
            $table->foreign('lesson_id')->references('lesson_id')->on('lessons')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xp_log');
    }
};