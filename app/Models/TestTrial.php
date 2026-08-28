<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestTrial extends Model
{
    protected $fillable = [
        'gesture_id',
        'true_label',
        'module',
        'sign_type',
        'signer_id',
        'trial_number',
        'landmark_data',
        'predicted_label',
        'confidence_score',
        'is_correct',
        'response_latency_ms',
        'capture_started_at',
        'feedback_received_at',
    ];

    protected $casts = [
        'landmark_data' => 'array',
        'is_correct' => 'boolean',
        'confidence_score' => 'float',
        'capture_started_at' => 'datetime',
        'feedback_received_at' => 'datetime',
    ];

    public function gesture(): BelongsTo
    {
        return $this->belongsTo(Gesture::class, 'gesture_id', 'gesture_id');
    }
}