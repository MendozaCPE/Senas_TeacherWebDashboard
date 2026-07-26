<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── ACHIEVEMENT DEFINITIONS ────────────────────────────
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();           // e.g., 'xp_100'
            $table->string('name');                     // e.g., 'XP Enthusiast'
            $table->string('description');              // e.g., 'Earn 100 XP'
            $table->string('category');                 // xp, beginner, intermediate, advanced, graduation, special
            $table->string('icon')->nullable();         // emoji or icon name
            $table->string('color')->nullable();        // hex color
            $table->integer('order')->default(0);       // display order
            $table->json('criteria')->nullable();       // Flexible criteria store
            $table->timestamps();
        });

        // ─── STUDENT ACHIEVEMENT PROGRESS ─────────────────────
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            
            // ✅ FIXED: Reference student_id column explicitly
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')
                  ->references('student_id')  // ← This matches your primary key
                  ->on('students')
                  ->onDelete('cascade');
            
            $table->foreignId('achievement_id')
                  ->constrained('achievements')
                  ->onDelete('cascade');
            
            $table->boolean('is_unlocked')->default(false);
            $table->timestamp('unlocked_at')->nullable();
            
            // Progress tracking (for achievements with progress bars)
            $table->integer('progress_current')->default(0);
            $table->integer('progress_target')->default(0);
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            $table->unique(['student_id', 'achievement_id']);
            $table->index(['student_id', 'is_unlocked']);
        });

        // ─── ACHIEVEMENT CRITERIA TYPES ────────────────────────
        Schema::create('achievement_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('achievement_id')
                  ->constrained('achievements')
                  ->onDelete('cascade');
            $table->string('type');        // xp, lessons_completed, quizzes_passed, etc.
            $table->integer('threshold');
            $table->string('operator')->default('>=');
            $table->json('filters')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_achievements');
        Schema::dropIfExists('achievement_criteria');
        Schema::dropIfExists('achievements');
    }
};