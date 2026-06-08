<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id('answer_id');
            $table->foreignId('attempt_id')->constrained('quiz_attempts', 'attempt_id')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('quiz_questions', 'question_id')->onDelete('cascade');
            $table->foreignId('selected_option_id')->nullable()->constrained('quiz_options', 'option_id')->onDelete('set null');
            $table->string('gesture_recognized')->nullable();
            $table->boolean('is_correct');
            $table->integer('points_earned');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_answers');
    }
};