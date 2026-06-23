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
        Schema::create('lesson_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lesson_id');
            $table->unsignedBigInteger('student_id');
            $table->timestamp('assigned_at');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->boolean('notified')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->integer('score')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('lesson_id')
                  ->references('lesson_id')
                  ->on('lessons')
                  ->onDelete('cascade');

            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('students')
                  ->onDelete('cascade');

            // Prevent duplicate assignments
            $table->unique(['lesson_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_assignments');
    }
};
