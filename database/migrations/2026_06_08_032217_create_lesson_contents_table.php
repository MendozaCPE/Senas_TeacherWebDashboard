<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lesson_contents', function (Blueprint $table) {
            $table->id('content_id');
            $table->foreignId('lesson_id')->constrained('lessons', 'lesson_id')->onDelete('cascade');
            $table->integer('step_number');
            $table->enum('content_type', ['text', 'image', 'video', 'gesture_demo']);
            $table->string('title')->nullable();
            $table->text('content_text')->nullable();
            $table->string('media_url')->nullable();
            $table->string('gesture_name')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lesson_contents');
    }
};