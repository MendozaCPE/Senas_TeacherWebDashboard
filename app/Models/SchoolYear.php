<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolYear extends Model
{
    protected $table = 'school_years';

    protected $fillable = [
        'school_id',
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeForSchool($query, ?int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    /**
     * Get the active school year for a given school_id (or null-school global).
     */
    public static function activeForSchool(?int $schoolId): ?self
    {
        return static::where('school_id', $schoolId)
                     ->where('status', 'active')
                     ->latest()
                     ->first();
    }

    /**
     * Get or create the active school year for a school.
     * Used when no active year exists (e.g. right after first install).
     */
    public static function currentOrCreate(int $schoolId, string $name): self
    {
        $existing = static::activeForSchool($schoolId);
        if ($existing) {
            return $existing;
        }

        [$start, $end] = explode('-', $name);
        return static::create([
            'school_id'  => $schoolId,
            'name'       => $name,
            'start_date' => "{$start}-07-01",
            'end_date'   => "{$end}-06-30",
            'status'     => 'active',
        ]);
    }

    /**
     * Derive the current DepEd school year label from today's date.
     * DepEd: July of year Y to June of year Y+1 = "Y-(Y+1)"
     */
    public static function currentDepEdLabel(): string
    {
        $now   = \Carbon\Carbon::now();
        $month = (int) $now->format('n');
        $year  = (int) $now->format('Y');
        $start = $month >= 7 ? $year : $year - 1;
        return $start . '-' . ($start + 1);
    }
}
