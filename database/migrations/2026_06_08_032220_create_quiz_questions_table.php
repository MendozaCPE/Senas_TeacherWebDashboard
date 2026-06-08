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
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id('question_id');
            $table->foreignId('quiz_id')->constrained('quizzes', 'quiz_id')->onDelete('cascade');
            $table->integer('question_number');
            $table->enum('question_type', ['multiple_choice', 'gesture_recognition', 'true_false']);
            $table->text('question_text');  // "Which sign shows letter A?"
            $table->string('media_url')->nullable();  // Image/video for the question
            $table->string('gesture_required')->nullable(); // For gesture recognition: "letter_a"
            $table->integer('points')->default(1);
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
        Schema::dropIfExists('quiz_questions');
    }
};
