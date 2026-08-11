<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->string('type'); // quiz_answered, module_passed, checkpoint_passed, level_up, mastery_promoted, help_request, streak_milestone
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable();       // material symbol name
            $table->string('color')->nullable();       // hex color for icon bg
            $table->json('data')->nullable();          // extra payload (student_id, score, etc.)
            $table->string('action_url')->nullable();  // href when notification is clicked
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('teacher_id')
                  ->references('id')
                  ->on('teachers')
                  ->onDelete('cascade');

            $table->index(['teacher_id', 'is_read']);
            $table->index(['teacher_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_notifications');
    }
};
