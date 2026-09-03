<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpRequest extends Model
{
    protected $table = 'help_requests';
    protected $primaryKey = 'help_request_id';

    /**
     * Status flow:
     *  pending       → Teacher receives, hasn't looked yet
     *  under_review  → Teacher is actively reviewing
     *  resolved      → Teacher resolved it directly
     *  escalated     → Teacher escalated to Admin
     *  closed        → Admin closed after handling escalation
     */
    const STATUS_PENDING      = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_RESOLVED     = 'resolved';
    const STATUS_ESCALATED    = 'escalated';
    const STATUS_CLOSED       = 'closed';

    protected $fillable = [
        'student_id',
        'teacher_id',
        'message',
        'status',
        // Teacher fields
        'teacher_note',
        'teacher_response',
        'teacher_responded_at',
        'teacher_responded_by',
        // Escalation fields
        'escalated_at',
        'escalated_by',
        'escalation_reason',
        // Admin fields (kept for backward compat, now only used when status = escalated/closed)
        'admin_response',
        'resolved_by',
        'resolved_at',
        'responded_at',
    ];

    protected $casts = [
        'resolved_at'           => 'datetime',
        'responded_at'          => 'datetime',
        'teacher_responded_at'  => 'datetime',
        'escalated_at'          => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function escalator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_by');
    }

    public function teacherResponder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_responded_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /** Only reports escalated to admin */
    public function scopeEscalated($query)
    {
        return $query->where('status', self::STATUS_ESCALATED);
    }

    // ─── Helper Methods ───────────────────────────────────────────────────────

    public function markAsUnderReview(): void
    {
        $this->update(['status' => self::STATUS_UNDER_REVIEW]);
    }

    public function markAsResolved(int $userId = null, string $response = null): void
    {
        $this->update([
            'status'                => self::STATUS_RESOLVED,
            'teacher_response'      => $response ?? $this->teacher_response,
            'teacher_responded_at'  => now(),
            'teacher_responded_by'  => $userId,
        ]);
    }

    public function escalateToAdmin(int $userId, string $reason = null): void
    {
        $this->update([
            'status'            => self::STATUS_ESCALATED,
            'escalated_at'      => now(),
            'escalated_by'      => $userId,
            'escalation_reason' => $reason,
        ]);
    }

    /** Whether this report is visible to admin (only escalated ones) */
    public function isEscalated(): bool
    {
        return $this->status === self::STATUS_ESCALATED || $this->status === self::STATUS_CLOSED;
    }

    /** Human-readable status label */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'      => 'Pending',
            'under_review' => 'Under Review',
            'resolved'     => 'Resolved',
            'escalated'    => 'Escalated',
            'closed'       => 'Closed',
            default        => ucfirst($this->status),
        };
    }
}
