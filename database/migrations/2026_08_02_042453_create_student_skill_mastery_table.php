<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_skill_mastery', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('gesture_id');
            $table->float('mastery_probability')->default(0);
            $table->integer('attempts')->default(0);
            $table->integer('successes')->default(0);
            $table->string('mastery_level')->default('never_practiced');
            $table->timestamp('last_practiced_at')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
            $table->foreign('gesture_id')->references('gesture_id')->on('gestures')->onDelete('cascade');
            $table->unique(['student_id', 'gesture_id']);
            
            // Indexes for performance
            $table->index(['student_id', 'mastery_level']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_skill_mastery');
    }
};