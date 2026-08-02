// database/migrations/xxxx_xx_xx_create_checkpoint_exam_questions_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('checkpoint_exam_questions', function (Blueprint $table) {
            $table->id('question_id');
            $table->foreignId('exam_id')->constrained('checkpoint_exams', 'exam_id')->onDelete('cascade');
            $table->foreignId('source_lesson_id')->constrained('lessons', 'lesson_id')->onDelete('cascade');
            $table->unsignedBigInteger('source_question_id')->nullable();
            $table->integer('question_number');
            $table->text('question_text');
            $table->enum('question_type', ['multiple_choice', 'true_false', 'drag_drop', 'gesture']);
            $table->string('media_url')->nullable();
            $table->integer('points')->default(1);
            $table->json('options_data')->nullable();
            $table->json('drag_drop_pairs')->nullable();
            $table->json('gesture_data')->nullable();
            $table->string('correct_answer')->nullable();
            $table->timestamps();

            $table->foreign('source_question_id')->references('question_id')->on('quiz_questions')->onDelete('set null');
            $table->index(['exam_id', 'question_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('checkpoint_exam_questions');
    }
};