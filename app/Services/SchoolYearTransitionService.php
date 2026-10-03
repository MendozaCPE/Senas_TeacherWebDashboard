<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SchoolYearTransitionService
 *
 * Handles the complete, transaction-safe school-year transition for a single
 * teacher's classroom. Rules:
 *   - Never deletes students, lessons, or permanent data.
 *   - Archives the current active school year (scoped to this school).
 *   - Creates/activates the new school year.
 *   - Marks students as "inactive" (unenrolled state) for the new year
 *     by NOT creating new lesson assignments yet — the teacher does that
 *     manually via Student Management → Enroll Student.
 *   - Logs every step in the audit_logs table.
 *   - Rolls back everything if any step fails.
 */
class SchoolYearTransitionService
{
    /**
     * Execute the school-year transition for the given teacher.
     *
     * @param  Teacher $teacher    The teacher performing the transition.
     * @param  int     $notifId    The ID of the teacher_notification that triggered this.
     * @return array{success: bool, message: string, new_school_year?: SchoolYear}
     */
    public function transition(Teacher $teacher, int $notifId): array
    {
        $schoolId   = (int) ($teacher->school_id ?? 0);
        $user       = Auth::user();
        $actorName  = $user ? ($teacher->first_name . ' ' . $teacher->last_name) : 'System';
        $actorRole  = $user?->role ?? 'teacher';

        try {
            return DB::transaction(function () use ($teacher, $notifId, $schoolId, $actorName, $actorRole) {

                // ── Step 1: Check notification status ─────────────────────────
                $notif = TeacherNotification::where('id', $notifId)
                    ->where('teacher_id', $teacher->id)
                    ->first();

                if ($notif && $notif->action_status === 'completed') {
                    return [
                        'success' => false,
                        'message' => 'You have already completed the transition for this school year.',
                    ];
                }

                // ── Step 2: Determine source and target school year names ─────
                $fromSyName = $notif?->data['from_school_year'] ?? null;
                $newSyName  = $notif?->data['target_school_year'] ?? null;

                if (!$fromSyName || !$newSyName) {
                    // Fall back to resolving from active school year or teacher's students
                    $currentYear = SchoolYear::where('school_id', $schoolId ?: null)
                        ->where('status', 'active')
                        ->latest('id')
                        ->first();

                    if (!$currentYear) {
                        $currentYear = SchoolYear::where('school_id', $schoolId ?: null)
                            ->latest('id')
                            ->first();
                    }

                    if ($currentYear) {
                        $fromSyName = $currentYear->name;
                    } else {
                        $mostCommon = Student::where('school_id', $schoolId ?: null)
                            ->whereNotNull('school_year')
                            ->value('school_year');
                        $fromSyName = $mostCommon ?: SchoolYear::currentDepEdLabel();
                    }

                    [$startY, $endY] = explode('-', $fromSyName);
                    $newStartY = (int) $endY;
                    $newEndY   = $newStartY + 1;
                    $newSyName = "{$newStartY}-{$newEndY}";
                } else {
                    [$newStartY, $newEndY] = explode('-', $newSyName);
                    $newStartY = (int) $newStartY;
                    $newEndY   = (int) $newEndY;
                }

                // ── Step 3: Archive the previous school year record ───────────
                $previousYear = SchoolYear::where('school_id', $schoolId ?: null)
                    ->where('name', $fromSyName)
                    ->first();

                // Keep a safe reference to the archived year name for later use.
                // If no DB record was found we still know the name from $fromSyName.
                $archivedYearName = $previousYear?->name ?? $fromSyName;

                if ($previousYear) {
                    $previousYear->update(['status' => 'archived']);

                    AuditLog::record(
                        action:      'SCHOOL_YEAR_ARCHIVED',
                        module:      'school_year',
                        description: "School year {$fromSyName} marked as archived for school_id={$schoolId}.",
                        userId:      Auth::id(),
                        userName:    $actorName,
                        userRole:    $actorRole,
                        subjectType: SchoolYear::class,
                        subjectId:   $previousYear->id,
                        oldValues:   ['status' => 'active'],
                        newValues:   ['status' => 'archived'],
                    );
                }

                // ── Step 4: Find or create the active new school year ─────────
                $newYear = SchoolYear::firstOrCreate(
                    [
                        'school_id' => $schoolId ?: null,
                        'name'      => $newSyName,
                    ],
                    [
                        'start_date' => "{$newStartY}-07-01",
                        'end_date'   => "{$newEndY}-06-30",
                        'status'     => 'active',
                    ]
                );

                if ($newYear->status !== 'active') {
                    $newYear->update(['status' => 'active']);
                }

                AuditLog::record(
                    action:      'SCHOOL_YEAR_ACTIVATED',
                    module:      'school_year',
                    description: "School year {$newSyName} active for school_id={$schoolId}.",
                    userId:      Auth::id(),
                    userName:    $actorName,
                    userRole:    $actorRole,
                    subjectType: SchoolYear::class,
                    subjectId:   $newYear->id,
                    newValues:   ['name' => $newSyName, 'status' => 'active'],
                );

                // ── Step 5: Make teacher's students inactive for new school year ──
                // We do NOT delete or reset students. We mark them inactive so the
                // teacher must manually re-enroll them via Student Management.
                // Their permanent data (XP, achievements, LRN) is untouched.
                $studentIds = Student::where('teacher_id', $teacher->id)
                    ->where('status', 'active')
                    ->pluck('student_id');

                if ($studentIds->isNotEmpty()) {
                    Student::whereIn('student_id', $studentIds)
                        ->update([
                            'status'      => 'inactive',
                            'school_year' => $newSyName,
                        ]);

                    AuditLog::record(
                        action:      'STUDENTS_UNENROLLED_FOR_NEW_SCHOOL_YEAR',
                        module:      'school_year',
                        description: "{$studentIds->count()} student(s) set to inactive for new school year {$newSyName} under teacher {$actorName}.",
                        userId:      Auth::id(),
                        userName:    $actorName,
                        userRole:    $actorRole,
                        subjectType: Teacher::class,
                        subjectId:   $teacher->id,
                        oldValues:   ['school_year' => $archivedYearName, 'status' => 'active'],
                        newValues:   ['school_year' => $newSyName, 'status' => 'inactive'],
                    );
                }

                // ── Step 6: Mark the triggering notification as completed ─────
                TeacherNotification::where('id', $notifId)
                    ->where('teacher_id', $teacher->id)
                    ->update([
                        'action_status'          => 'completed',
                        'is_read'                => true,
                        'read_at'                => now(),
                        'related_school_year_id' => $newYear->id,
                    ]);

                // ── Step 7: Final audit log ──────────────────────────────────
                AuditLog::record(
                    action:      'SCHOOL_YEAR_TRANSITION_COMPLETED',
                    module:      'school_year',
                    description: "School year transition completed for teacher {$actorName}. Archived: {$archivedYearName}. New active: {$newSyName}. Students unenrolled: {$studentIds->count()}.",
                    userId:      Auth::id(),
                    userName:    $actorName,
                    userRole:    $actorRole,
                    subjectType: Teacher::class,
                    subjectId:   $teacher->id,
                );

                return [
                    'success'         => true,
                    'message'         => "School year transition complete. {$archivedYearName} has been archived. {$newSyName} is now active. {$studentIds->count()} student(s) set to inactive — enroll them via Student Management.",
                    'new_school_year' => $newYear,
                    'archived_year'   => $archivedYearName,
                    'student_count'   => $studentIds->count(),
                ];
            });
        } catch (\Throwable $e) {
            Log::error('SchoolYearTransitionService::transition failed', [
                'teacher_id' => $teacher->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'The transition failed and was rolled back. Please try again or contact support. Error: ' . $e->getMessage(),
            ];
        }
    }
}
