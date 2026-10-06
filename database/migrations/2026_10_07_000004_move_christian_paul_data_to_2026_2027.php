<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $teacher = DB::table('teachers')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->where('users.email', 'christianpaulmendoza10@gmail.com')
            ->select('teachers.id', 'teachers.school_id')
            ->first();

        if (!$teacher) {
            return;
        }

        $schoolYears = DB::table('school_years')
            ->where('school_id', $teacher->school_id)
            ->whereIn('name', ['2026-2027', '2027-2028'])
            ->get()
            ->keyBy('name');

        // On clean installs, the demo seeder already attaches records to the
        // active year. Only convert databases that contain both year records.
        if (!$schoolYears->has('2026-2027') || !$schoolYears->has('2027-2028')) {
            return;
        }

        $targetYear = $schoolYears->get('2026-2027');
        $sourceYear = $schoolYears->get('2027-2028');
        $studentIds = DB::table('students')
            ->where('teacher_id', $teacher->id)
            ->where('school_id', $teacher->school_id)
            ->pluck('student_id');

        DB::transaction(function () use ($teacher, $targetYear, $sourceYear, $studentIds) {
            // Keep the school-year record for historical navigation, but make
            // 2026-2027 the school's sole active year.
            DB::table('school_years')
                ->where('school_id', $teacher->school_id)
                ->where('status', 'active')
                ->update(['status' => 'archived', 'updated_at' => now()]);
            DB::table('school_years')->where('id', $targetYear->id)->update([
                'status' => 'active',
                'updated_at' => now(),
            ]);
            DB::table('school_years')->where('id', $sourceYear->id)->update([
                'status' => 'archived',
                'updated_at' => now(),
            ]);

            // The analytics demo migration shifted these five students' dated
            // activity forward one calendar year to fit 2027-2028. Rebase it
            // when moving the records back to 2026-2027.
            $demoStudentIds = DB::table('students')
                ->whereIn('lrn', [
                    '123456789001', '123456789002', '123456789003',
                    '123456789004', '123456789005',
                ])
                ->where('teacher_id', $teacher->id)
                ->pluck('student_id');

            $dateColumns = [
                'lesson_assignments' => ['assigned_at', 'completed_at', 'created_at', 'updated_at'],
                'student_lesson_progress' => ['last_accessed_at', 'created_at', 'updated_at'],
                'quiz_attempts' => ['started_at', 'completed_at', 'created_at', 'updated_at'],
                'gesture_performances' => ['first_attempt_at', 'last_attempt_at', 'mastered_at', 'created_at', 'updated_at'],
                'checkpoint_exam_attempts' => ['started_at', 'completed_at', 'created_at', 'updated_at'],
                'student_skill_mastery' => ['created_at', 'updated_at'],
            ];

            foreach ($dateColumns as $table => $columns) {
                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'school_year_id')) {
                    continue;
                }

                foreach ($columns as $column) {
                    if (!Schema::hasColumn($table, $column)) {
                        continue;
                    }

                    DB::table($table)
                        ->whereIn('student_id', $demoStudentIds)
                        ->where('school_year_id', $sourceYear->id)
                        ->whereNotNull($column)
                        ->update([$column => DB::raw("DATE_SUB(`{$column}`, INTERVAL 1 YEAR)")]);
                }
            }

            if (Schema::hasColumn('students', 'last_activity_date')) {
                DB::table('students')
                    ->whereIn('student_id', $demoStudentIds)
                    ->whereNotNull('last_activity_date')
                    ->update(['last_activity_date' => DB::raw('DATE_SUB(`last_activity_date`, INTERVAL 1 YEAR)')]);
            }

            // Move enrollment history, merging an existing 2026-2027 entry
            // for the same student rather than violating its unique key.
            $sourceEnrollments = DB::table('student_year_enrollments')
                ->where('teacher_id', $teacher->id)
                ->where('school_year_name', '2027-2028')
                ->get();

            foreach ($sourceEnrollments as $sourceEnrollment) {
                $targetEnrollment = DB::table('student_year_enrollments')
                    ->where('student_id', $sourceEnrollment->student_id)
                    ->where('school_year_name', '2026-2027')
                    ->first();

                $values = [
                    'teacher_id' => $teacher->id,
                    'school_id' => $teacher->school_id,
                    'school_year_id' => $targetYear->id,
                    'school_year_name' => '2026-2027',
                    'program_type' => $sourceEnrollment->program_type,
                    'grade_level' => $sourceEnrollment->grade_level,
                    'section' => $sourceEnrollment->section,
                    'enrolled_at' => $sourceEnrollment->enrolled_at,
                    'updated_at' => now(),
                ];

                if ($targetEnrollment) {
                    DB::table('student_year_enrollments')->where('id', $targetEnrollment->id)->update($values);
                    DB::table('student_year_enrollments')->where('id', $sourceEnrollment->id)->delete();
                } else {
                    DB::table('student_year_enrollments')->where('id', $sourceEnrollment->id)->update($values);
                }
            }

            // Move assignments. Where the same lesson was already assigned in
            // 2026-2027, retain its row and replace its state with the newer
            // 2027-2028 assignment state.
            $sourceAssignments = DB::table('lesson_assignments')
                ->whereIn('student_id', $studentIds)
                ->where('school_year_id', $sourceYear->id)
                ->get();

            foreach ($sourceAssignments as $sourceAssignment) {
                $targetAssignment = DB::table('lesson_assignments')
                    ->where('student_id', $sourceAssignment->student_id)
                    ->where('lesson_id', $sourceAssignment->lesson_id)
                    ->where('school_year_id', $targetYear->id)
                    ->first();

                $values = [
                    'school_year_id' => $targetYear->id,
                    'assigned_at' => $sourceAssignment->assigned_at,
                    'status' => $sourceAssignment->status,
                    'is_locked' => $sourceAssignment->is_locked,
                    'notified' => $sourceAssignment->notified,
                    'completed_at' => $sourceAssignment->completed_at,
                    'score' => $sourceAssignment->score,
                    'created_at' => $sourceAssignment->created_at,
                    'updated_at' => $sourceAssignment->updated_at,
                ];

                if ($targetAssignment) {
                    DB::table('lesson_assignments')->where('id', $targetAssignment->id)->update($values);
                    DB::table('lesson_assignments')->where('id', $sourceAssignment->id)->delete();
                } else {
                    DB::table('lesson_assignments')->where('id', $sourceAssignment->id)->update($values);
                }
            }

            // The remaining year-scoped activity has no overlapping unique
            // keys, so it can be reassigned directly to the target year.
            foreach ([
                'student_lesson_progress',
                'quiz_attempts',
                'gesture_performances',
                'checkpoint_exam_attempts',
                'student_skill_mastery',
            ] as $table) {
                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'school_year_id')) {
                    continue;
                }

                DB::table($table)
                    ->whereIn('student_id', $studentIds)
                    ->where('school_year_id', $sourceYear->id)
                    ->update(['school_year_id' => $targetYear->id]);
            }

            DB::table('students')
                ->whereIn('student_id', $studentIds)
                ->where('school_year', '2027-2028')
                ->update(['school_year' => '2026-2027', 'updated_at' => now()]);
        });
    }

    public function down(): void
    {
        // The conversion merges duplicate assignments and enrollment rows;
        // reversing it automatically could recreate conflicting records.
    }
};
