<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checkpoint_exam_attempts', function (Blueprint $table) {
            $table->id('attempt_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('exam_id');
            $table->integer('score')->default(0);
            $table->integer('total_points')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->integer('attempt_number')->default(1);
            $table->integer('xp_earned')->default(0);
            $table->json('answers_data')->nullable();
            $table->json('question_results')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('students')
                  ->onDelete('cascade');

            $table->foreign('exam_id')
                  ->references('exam_id')
                  ->on('checkpoint_exams')
                  ->onDelete('cascade');

            // Indexes for performance
            $table->index(['exam_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkpoint_exam_attempts');
    }
};