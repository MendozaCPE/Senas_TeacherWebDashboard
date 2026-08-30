<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->unique(); // one rating per student (updateable)
            $table->tinyInteger('rating')->unsigned(); // 1–5
            $table->text('feedback')->nullable();
            $table->boolean('is_approved')->default(false); // must be approved by admin before showing on landing page
            $table->timestamps();

            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('students')
                  ->onDelete('cascade');

            $table->index('rating');
            $table->index('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_ratings');
    }
};