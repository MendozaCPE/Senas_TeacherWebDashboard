// database/migrations/xxxx_xx_xx_create_student_notifications_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('type'); // achievement, promotion, lesson, streak, system
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->json('data')->nullable(); // Additional data for the notification
            $table->string('action_url')->nullable(); // Where to navigate when tapped
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->onUpdate('current_timestamp');

            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('students')
                  ->onDelete('cascade');

            $table->index(['student_id', 'is_read']);
            $table->index(['student_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_notifications');
    }
};