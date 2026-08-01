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
        Schema::create('challenge_goal_progress', function (Blueprint $table) {
    $table->id('progress_id');
    $table->unsignedBigInteger('challenge_id');
    $table->string('goal_type'); // 'time_spent', 'gesture_practice', 'lesson_completion', 'quiz_attempt'
    $table->string('goal_key'); // Unique identifier for the goal
    $table->integer('target_value');
    $table->integer('current_value')->default(0);
    $table->boolean('is_completed')->default(false);
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
    
    $table->foreign('challenge_id')->references('challenge_id')->on('daily_challenges')->onDelete('cascade');
    $table->unique(['challenge_id', 'goal_key']);
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('challenge_goal_progress');
    }
};
