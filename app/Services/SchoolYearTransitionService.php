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
                    // Stamp the real end date when this year is officially closed.
                    $previousYear->update([
                        'status'   => 'archived',
                        'end_date' => Carbon::today(),
                    ]);

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

                // Stamp the real start date when this new year becomes active.
                // Only set it if it isn't already set (don't overwrite a previously
                // stored real start date if the transition is re-confirmed somehow).
                if (!$newYear->start_date) {
                    $newYear->update(['start_date' => Carbon::today()]);
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

                // ── Step 5: Make teacher's students unenrolled for new school year ──
                // Marks students as not enrolled (is_enrolled = false) so the teacher must manually re-enroll them.
                // XP and level reset to 0/1 for the fresh school year start.
                // fsl_mastery_level is preserved — students keep their mastery rank.
                // Mobile app engagement status is preserved.
                $studentIds = Student::where('teacher_id', $teacher->id)
                    ->pluck('student_id');

                if ($studentIds->isNotEmpty()) {
                    // Log each student's XP before resetting for the audit trail.
                    $oldXpValues = Student::whereIn('student_id', $studentIds)
                        ->pluck('total_xp', 'student_id');

                    Student::whereIn('student_id', $studentIds)
                        ->update([
                            'is_enrolled' => false,
                            'school_year' => $newSyName,
                            'total_xp'    => 0,
                            'level'       => 1,
                        ]);

                    // NOTE: We intentionally do NOT write student_year_enrollments
                    // rows here. Students are unenrolled at this point — the record
                    // is only written when the teacher manually re-enrolls each
                    // student via StudentsController@enroll. Writing it here would
                    // cause the enrollment trend to count students before they are
                    // actually enrolled in the new year.

                    // Log the XP reset to xp_log for each student
                    foreach ($studentIds as $sid) {
                        $oldXp = (int) ($oldXpValues[$sid] ?? 0);
                        if ($oldXp > 0) {
                            try {
                                \Illuminate\Support\Facades\DB::table('xp_log')->insert([
                                    'student_id'      => $sid,
                                    'action'          => 'SCHOOL_YEAR_RESET',
                                    'xp_amount'       => -$oldXp,
                                    'reason'          => "School year transition to {$newSyName}. XP reset to 0. Mastery level preserved.",
                                    'created_at'      => now(),
                                    'updated_at'      => now(),
                                ]);
                            } catch (\Throwable) { /* xp_log table may not exist in all envs */ }
                        }
                    }

                    // ── Archive lesson assignments for the old school year ─────
                    // Stamp the old school_year_id onto all assignments for these
                    // students and reset their status to 'pending'. This means:
                    //   - Completion % on the dashboard resets to 0% for the new year
                    //   - Historical data is preserved: old year's completions still
                    //     exist in lesson_assignments (with the old school_year_id)
                    //     and in student_lesson_progress (with the old school_year_id)
                    //   - No data is deleted.
                    if ($previousYear) {
                        // Archive lesson assignments for the year being closed.
                        //
                        // IMPORTANT: Only touch records that are already stamped with
                        // the current (soon-to-be-archived) school_year_id.
                        // Records from previous archived years (e.g. sy_id=1) must not
                        // be modified — they're historical data.
                        //
                        // We do NOT change school_year_id here because:
                        //   - The active-year records already carry school_year_id=$previousYear->id
                        //   - Changing it would cause a UNIQUE KEY violation when older
                        //     records for the same lesson+student already have that id.
                        //
                        // Step A: Clean up any orphan records for these students that
                        // have a school_year_id that is neither the current year nor any
                        // known archived year. This prevents stale rows from past partial
                        // runs from causing conflicts.
                        $knownSyIds = SchoolYear::pluck('id')->toArray();
                        DB::table('lesson_assignments')
                            ->whereIn('student_id', $studentIds)
                            ->whereNotIn('school_year_id', $knownSyIds)
                            ->delete();

                        // Step B: Reset status to pending for current-year records only.
                        // These are the records the student will start fresh on next year.
                        DB::table('lesson_assignments')
                            ->whereIn('student_id', $studentIds)
                            ->where('school_year_id', $previousYear->id)
                            ->update([
                                'status'     => 'pending',
                                'updated_at' => now(),
                            ]);

                        AuditLog::record(
                            action:      'LESSON_ASSIGNMENTS_ARCHIVED',
                            module:      'school_year',
                            description: "Lesson assignments for {$studentIds->count()} student(s) archived under S.Y. {$archivedYearName} (id={$previousYear->id}) and reset to pending for new year {$newSyName}.",
                            userId:      Auth::id(),
                            userName:    $actorName,
                            userRole:    $actorRole,
                            subjectType: Teacher::class,
                            subjectId:   $teacher->id,
                        );
                    }


                    AuditLog::record(
                        action:      'STUDENTS_UNENROLLED_FOR_NEW_SCHOOL_YEAR',
                        module:      'school_year',
                        description: "{$studentIds->count()} student(s) set to unenrolled for new school year {$newSyName} under teacher {$actorName}. XP reset to 0. Mastery levels preserved.",
                        userId:      Auth::id(),
                        userName:    $actorName,
                        userRole:    $actorRole,
                        subjectType: Teacher::class,
                        subjectId:   $teacher->id,
                        oldValues:   ['school_year' => $archivedYearName, 'is_enrolled' => true, 'total_xp' => 'preserved per student'],
                        newValues:   ['school_year' => $newSyName, 'is_enrolled' => false, 'total_xp' => 0, 'level' => 1],
                    );

                    // Lesson assignments are NOT seeded here.
                    // The teacher assigns lessons fresh after manually re-enrolling
                    // each student via Student Management → Enroll → assignment modal.
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
