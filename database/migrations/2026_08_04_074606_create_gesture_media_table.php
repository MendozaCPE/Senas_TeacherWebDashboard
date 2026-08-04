<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gesture_media', function (Blueprint $table) {
            $table->id('media_id');
            $table->foreignId('gesture_id')->constrained('gestures', 'gesture_id');
            $table->enum('media_type', ['image', 'video']);
            $table->string('file_path'); // This will store the relative path
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable(); // in bytes
            $table->boolean('is_primary')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            // Index for faster lookups
            $table->index(['gesture_id', 'media_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('gesture_media');
    }
};