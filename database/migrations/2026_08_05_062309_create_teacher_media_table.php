<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_media', function (Blueprint $table) {
            $table->id('media_id');
            $table->foreignId('teacher_id')->constrained('teachers', 'id')->onDelete('cascade');
            $table->string('title');
            $table->string('file_name');
            $table->string('file_path');          // relative path inside storage/app/public
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->nullable();  // bytes
            $table->enum('media_type', ['image', 'video', 'gif']);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'media_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_media');
    }
};
