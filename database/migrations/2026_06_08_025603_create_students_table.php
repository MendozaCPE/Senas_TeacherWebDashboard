<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id('student_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('set null');
            $table->string('lrn', 12)->unique();
            $table->string('pin', 4);
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('age');
            $table->string('grade_level')->nullable();
            $table->string('section')->nullable();
            $table->enum('program_type', ['Regular', 'Inclusion', 'Transition', 'Self-contained']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
};