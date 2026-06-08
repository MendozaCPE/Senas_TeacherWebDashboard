<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id('quiz_id');
            $table->foreignId('lesson_id')->constrained('lessons', 'lesson_id')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('total_points')->default(0);
            $table->integer('passing_score')->default(70);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quizzes');
    }
};