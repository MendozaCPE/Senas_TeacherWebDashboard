<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per recorded testing attempt (trial). Points at your
     * EXISTING `gestures` table (primary key: gesture_id) -- no new
     * sign/reference table is created here.
     *
     * This is what your confusion matrix / accuracy / precision /
     * recall / F1 / response latency computations get built from.
     * Kept separate from GesturePerformance, which tracks live student
     * practice, not controlled test data.
     */
    public function up(): void
    {
        Schema::create('test_trials', function (Blueprint $table) {
            $table->id();

            // Manual FK definition since gestures' PK is "gesture_id", not "id"
            $table->unsignedBigInteger('gesture_id');
            $table->foreign('gesture_id')->references('gesture_id')->on('gestures')->cascadeOnDelete();

            $table->string('true_label');   // denormalized copy of gestures.name at time of trial
            $table->string('module')->nullable(); // denormalized copy of gesture_modules.name
            $table->enum('sign_type', ['static', 'dynamic'])->default('static');

            $table->string('signer_id');
            $table->unsignedInteger('trial_number');

            $table->json('landmark_data'); // single-frame array for static, sequence-of-frames for dynamic

            $table->string('predicted_label')->nullable();
            $table->float('confidence_score')->nullable();
            $table->boolean('is_correct')->default(false);

            $table->unsignedInteger('response_latency_ms')->nullable();
            $table->timestamp('capture_started_at')->nullable();
            $table->timestamp('feedback_received_at')->nullable();

            $table->timestamps();

            $table->index(['gesture_id', 'signer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_trials');
    }
};