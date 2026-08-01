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
        Schema::create('daily_challenges', function (Blueprint $table) {
    $table->id('challenge_id');
    $table->unsignedBigInteger('student_id');
    $table->date('challenge_date');
    $table->string('theme'); // 'Alphabet_Numbers', 'Greetings', etc.
    $table->json('goals'); // Store all goals with progress
    $table->integer('total_xp_rewarded')->default(0);
    $table->boolean('is_completed')->default(false);
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
    
    $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
    $table->unique(['student_id', 'challenge_date']);
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_challenges');
    }
};
