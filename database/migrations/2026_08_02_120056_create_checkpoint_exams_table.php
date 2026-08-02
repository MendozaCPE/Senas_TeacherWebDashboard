// database/migrations/xxxx_xx_xx_create_checkpoint_exams_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('checkpoint_exams', function (Blueprint $table) {
            $table->id('exam_id');
            $table->foreignId('module_id')->constrained('modules', 'module_id')->onDelete('cascade');
            $table->unsignedBigInteger('teacher_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('total_points')->default(0);
            $table->integer('passing_score')->default(0);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->index(['module_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('checkpoint_exams');
    }
};