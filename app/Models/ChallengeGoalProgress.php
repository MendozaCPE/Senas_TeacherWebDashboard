<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeGoalProgress extends Model
{
    protected $table = 'challenge_goal_progress';
    protected $primaryKey = 'progress_id';
    
    protected $fillable = [
        'challenge_id',
        'goal_type',
        'goal_key',
        'target_value',
        'current_value',
        'is_completed',
        'completed_at',
    ];
    
    protected $casts = [
        'is_completed' => 'boolean',
        'target_value' => 'integer',
        'current_value' => 'integer',
        'completed_at' => 'datetime',
    ];
    
    protected $dates = [
        'completed_at',
        'created_at',
        'updated_at',
    ];
    
    /**
     * Get the challenge that owns this progress.
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(DailyChallenge::class, 'challenge_id', 'challenge_id');
    }
    
    /**
     * Check if this goal is completed.
     */
    public function isGoalCompleted(): bool
    {
        return $this->current_value >= $this->target_value;
    }
    
    /**
     * Get progress percentage for this goal.
     */
    public function getProgressPercentage(): int
    {
        if ($this->target_value === 0) {
            return 0;
        }
        return round(($this->current_value / $this->target_value) * 100);
    }
}