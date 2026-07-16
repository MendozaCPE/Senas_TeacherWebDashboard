<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'from_level',
        'to_level',
        'xp_at_promotion',
        'promoted_by',
        'was_forced',
        'promoted_at',
    ];

    protected $casts = [
        'was_forced'   => 'boolean',
        'promoted_at'  => 'datetime',
    ];

    /**
     * The student that was promoted.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * The teacher (user) who performed the promotion.
     */
    public function promotedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }
}
