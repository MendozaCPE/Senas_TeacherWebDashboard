<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'module',
        'description',
        'subject_type',
        'subject_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Record an audit log entry.
     */
    public static function record(
        string $action,
        string $module,
        string $description,
        ?int $userId = null,
        ?string $userName = null,
        ?string $userRole = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): self {
        return static::create([
            'user_id'      => $userId,
            'user_name'    => $userName,
            'user_role'    => $userRole,
            'action'       => $action,
            'module'       => $module,
            'description'  => $description,
            'subject_type' => $subjectType,
            'subject_id'   => $subjectId,
            'old_values'   => $oldValues,
            'new_values'   => $newValues,
            'ip_address'   => request()->ip(),
            'user_agent'   => substr(request()->userAgent() ?? '', 0, 512),
        ]);
    }
}
