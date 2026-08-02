// database/migrations/xxxx_xx_xx_create_checkpoint_exam_assignments_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('checkpoint_exam_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->foreignId('exam_id')->constrained('checkpoint_exams', 'exam_id')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->float('score')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'expired'])->default('pending');
            $table->boolean('is_locked')->default(true);
            $table->boolean('notified')->default(false);
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('checkpoint_exam_assignments');
    }
};