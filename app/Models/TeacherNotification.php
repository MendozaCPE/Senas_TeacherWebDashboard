<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherNotification extends Model
{
    protected $table = 'teacher_notifications';

    protected $fillable = [
        'teacher_id',
        'type',
        'title',
        'message',
        'icon',
        'color',
        'data',
        'action_url',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data'       => 'array',
        'is_read'    => 'boolean',
        'read_at'    => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    // ── Static factory ───────────────────────────────────────────────────────

    /**
     * Icon & colour map keyed by notification type.
     */
    public static function typeConfig(string $type): array
    {
        return match ($type) {
            'quiz_answered'      => ['icon' => 'quiz',             'color' => '#3B82F6'],
            'module_passed'      => ['icon' => 'workspace_premium','color' => '#8B5CF6'],
            'checkpoint_passed'  => ['icon' => 'military_tech',    'color' => '#F59E0B'],
            'level_up'           => ['icon' => 'trending_up',      'color' => '#10B981'],
            'mastery_promoted'   => ['icon' => 'star',             'color' => '#8B5CF6'],
            'help_request'       => ['icon' => 'help',             'color' => '#EF4444'],
            'streak_milestone'   => ['icon' => 'local_fire_department', 'color' => '#F97316'],
            'module_completed'   => ['icon' => 'book',             'color' => '#15803D'],
            'challenge_completed' => ['icon' => 'emoji_events',    'color' => '#8B5CF6'], // 🆕 ADD THIS
            default              => ['icon' => 'notifications',    'color' => '#6B7280'],
        };
    }

    public static function createForTeacher(
        int    $teacherId,
        string $type,
        string $title,
        string $message,
        ?array $data       = null,
        ?string $actionUrl = null,
    ): self {
        $cfg = self::typeConfig($type);

        return self::create([
            'teacher_id' => $teacherId,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'icon'       => $cfg['icon'],
            'color'      => $cfg['color'],
            'data'       => $data,
            'action_url' => $actionUrl,
            'is_read'    => false,
        ]);
    }
}