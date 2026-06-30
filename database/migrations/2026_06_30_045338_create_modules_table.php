<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Only create the table if it doesn't exist
        if (!Schema::hasTable('modules')) {
            Schema::create('modules', function (Blueprint $table) {
                $table->id('module_id');
                $table->foreignId('teacher_id')->constrained('teachers', 'id')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->integer('module_order')->default(0);
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->timestamps();
                $table->index(['teacher_id', 'module_order']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('modules');
    }
};