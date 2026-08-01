<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyChallenge extends Model
{
    protected $table = 'daily_challenges';
    protected $primaryKey = 'challenge_id';
    
    protected $fillable = [
        'student_id',
        'challenge_date',
        'theme',
        'goals',
        'total_xp_rewarded',
        'is_completed',
        'completed_at',
    ];
    
    protected $casts = [
        'goals' => 'array',
        'is_completed' => 'boolean',
        'challenge_date' => 'date',
        'completed_at' => 'datetime',
    ];
    
    protected $dates = [
        'challenge_date',
        'completed_at',
        'created_at',
        'updated_at',
    ];
    
    /**
     * Get the student that owns this challenge.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
    
    /**
     * Get the goal progress entries for this challenge.
     */
    public function goalProgress(): HasMany
    {
        return $this->hasMany(ChallengeGoalProgress::class, 'challenge_id', 'challenge_id');
    }
    
    /**
     * Check if all goals are completed.
     */
    public function areAllGoalsCompleted(): bool
    {
        if ($this->goalProgress()->count() === 0) {
            return false;
        }
        return $this->goalProgress()->where('is_completed', false)->count() === 0;
    }
    
    /**
     * Get progress percentage.
     */
    public function getProgressPercentage(): int
    {
        $total = $this->goalProgress()->count();
        if ($total === 0) {
            return 0;
        }
        $completed = $this->goalProgress()->where('is_completed', true)->count();
        return round(($completed / $total) * 100);
    }
    
    /**
     * Get completed goals count.
     */
    public function getCompletedGoalsCount(): int
    {
        return $this->goalProgress()->where('is_completed', true)->count();
    }
    
    /**
     * Get total goals count.
     */
    public function getTotalGoalsCount(): int
    {
        return $this->goalProgress()->count();
    }
}