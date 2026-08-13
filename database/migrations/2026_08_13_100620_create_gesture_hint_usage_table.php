<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gesture_hint_usage', function (Blueprint $table) {
            $table->id('hint_usage_id');
            $table->unsignedBigInteger('student_id');
            $table->string('module_name');
            $table->string('letter', 5);
            $table->integer('hint_count')->default(0);
            $table->string('session_id')->nullable();
            $table->timestamps();

            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('students')
                  ->onDelete('cascade');

            $table->unique(['student_id', 'module_name', 'letter', 'session_id'], 'hint_usage_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('gesture_hint_usage');
    }
};