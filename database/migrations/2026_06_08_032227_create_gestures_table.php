<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gestures', function (Blueprint $table) {
            $table->id('gesture_id');
            $table->string('name');  // "letter_a", "letter_b", "hello"
            $table->string('display_name');  // "Letter A", "Letter B", "Hello"
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();  // Reference image
            $table->string('video_url')->nullable();  // Tutorial video
            $table->string('model_file')->nullable();  // Path to .h5 model file
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced']);
            $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gestures');
    }
};
