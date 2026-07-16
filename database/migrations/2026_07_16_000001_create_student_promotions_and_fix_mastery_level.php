<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Fix the fsl_mastery_level enum to include 'Completed'
        DB::statement("ALTER TABLE students MODIFY COLUMN fsl_mastery_level ENUM('Beginner','Intermediate','Advanced','Completed') NOT NULL DEFAULT 'Beginner'");

        // 2. Create the student_promotions history table
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('from_level', 20);
            $table->string('to_level', 20);
            $table->unsignedInteger('xp_at_promotion')->default(0);
            $table->unsignedBigInteger('promoted_by')->nullable(); // teacher user_id
            $table->boolean('was_forced')->default(false);
            $table->timestamp('promoted_at')->useCurrent();
            $table->timestamps();

            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('students')
                  ->onDelete('cascade');

            $table->foreign('promoted_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_promotions');

        DB::statement("ALTER TABLE students MODIFY COLUMN fsl_mastery_level ENUM('Beginner','Intermediate','Advanced') NOT NULL DEFAULT 'Beginner'");
    }
};
