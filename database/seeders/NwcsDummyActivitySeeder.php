<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * NwcsDummyActivitySeeder
 *
 * Seeds realistic-looking lesson completions, quiz attempts,
 * gesture performances, and last_activity_date updates
 * for Nasugbu West Central School (school_id = 1) so the
 * Teacher Leader dashboard charts actually look alive.
 *
 * Safe to re-run — uses upsert/ignore patterns so nothing duplicates.
 */
class NwcsDummyActivitySeeder extends Seeder
{
    public function run(): void
    {
        $schoolId = 1;

        // ── Resolve school teacher IDs (role = 'teacher', not system) ────────
        $teacherIds = DB::table('teachers')
            ->where('school_id', $schoolId)
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->where('users.role', 'teacher')
            ->where('users.is_system', false)
            ->pluck('teachers.id')
            ->toArray();

        if (empty($teacherIds)) {
            $this->command->error('No real teachers found for school_id 1.');
            return;
        }

        // ── Resolve student IDs for this school ───────────────────────────────
        $students = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->get(['student_id', 'teacher_id']);

        if ($students->isEmpty()) {
            $this->command->warn('No students found — skipping lesson/quiz/gesture seeding.');
            return;
        }

        $studentIds = $students->pluck('student_id')->toArray();

        // ── Resolve published lesson IDs for these teachers ───────────────────
        $lessonIds = DB::table('lessons')
            ->whereIn('teacher_id', $teacherIds)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->pluck('lesson_id')
            ->toArray();

        if (empty($lessonIds)) {
            $this->command->warn('No published lessons found for school teachers.');
            return;
        }

        // ── Resolve quiz IDs (one quiz per lesson) ────────────────────────────
        $quizMap = DB::table('quizzes')
            ->whereIn('lesson_id', $lessonIds)
            ->pluck('quiz_id', 'lesson_id') // lesson_id => quiz_id
            ->toArray();

        // ── Resolve gesture IDs ───────────────────────────────────────────────
        $gestureIds = DB::table('gestures')->pluck('gesture_id')->toArray();

        $this->command->info('Seeding activity for ' . count($studentIds) . ' students, ' . count($lessonIds) . ' lessons...');

        $now = Carbon::now();

        // Daily completion profile — more activity mid-week, slight weekend dip
        // Index 0 = 13 days ago, index 13 = today
        $completionChance = [0.30, 0.45, 0.60, 0.70, 0.75, 0.50, 0.35,  // week 1 Mon-Sun
                             0.40, 0.55, 0.65, 0.72, 0.80, 0.55, 0.85]; // week 2 Mon-today

        // ═══════════════════════════════════════════════════════════════════
        // 1.  LESSON ASSIGNMENTS + COMPLETIONS
        // ═══════════════════════════════════════════════════════════════════
        $assignInsert = [];
        $lpUpdates    = []; // student_id => latest completed_at

        foreach ($students as $student) {
            // Assign a random subset of lessons to each student (60-90% of all lessons)
            $myLessons = collect($lessonIds)->random(
                max(3, (int) round(count($lessonIds) * (0.6 + mt_rand(0, 30) / 100)))
            )->toArray();

            foreach ($myLessons as $idx => $lessonId) {
                // Pick a random day in the last 14 days for the assignment
                $daysAgo     = mt_rand(1, 14);
                $assignedAt  = $now->copy()->subDays($daysAgo)->setTime(mt_rand(7, 18), mt_rand(0, 59));

                // Determine status based on day-index completion chance
                $dayIndex    = 13 - ($daysAgo - 1); // map to 0–13
                $chance      = $completionChance[max(0, min(13, $dayIndex))];
                $roll        = mt_rand(0, 100) / 100;

                $status      = 'pending';
                $completedAt = null;

                if ($roll < $chance) {
                    $status      = 'completed';
                    $completedAt = $assignedAt->copy()->addMinutes(mt_rand(15, 90));
                } elseif ($roll < $chance + 0.20) {
                    $status = 'in_progress';
                }

                $assignInsert[] = [
                    'student_id'  => $student->student_id,
                    'lesson_id'   => $lessonId,
                    'status'      => $status,
                    'assigned_at' => $assignedAt,
                    'completed_at'=> $completedAt,
                    'notified'    => 1,
                    'created_at'  => $assignedAt,
                    'updated_at'  => $completedAt ?? $assignedAt,
                ];

                if ($completedAt) {
                    $existing = $lpUpdates[$student->student_id] ?? null;
                    if (!$existing || $completedAt > $existing) {
                        $lpUpdates[$student->student_id] = $completedAt;
                    }
                }
            }
        }

        // Upsert lesson assignments (ignore duplicates)
        foreach (array_chunk($assignInsert, 100) as $chunk) {
            DB::table('lesson_assignments')->upsert(
                $chunk,
                ['student_id', 'lesson_id'], // unique key
                ['status', 'completed_at', 'updated_at']
            );
        }

        $this->command->info('  ✓ lesson_assignments seeded: ' . count($assignInsert));

        // ═══════════════════════════════════════════════════════════════════
        // 2.  QUIZ ATTEMPTS
        // ═══════════════════════════════════════════════════════════════════
        $quizInsert = [];

        // Only seed quizzes for completed lesson assignments
        $completedAssignments = DB::table('lesson_assignments')
            ->whereIn('student_id', $studentIds)
            ->whereIn('lesson_id', $lessonIds)
            ->where('status', 'completed')
            ->get(['student_id', 'lesson_id', 'completed_at']);

        foreach ($completedAssignments as $ca) {
            $quizId = $quizMap[$ca->lesson_id] ?? null;
            if (!$quizId) continue;

            // 80% chance the student took the quiz after completing the lesson
            if (mt_rand(0, 100) > 80) continue;

            // Score: clustered around 68-88%, some outliers
            $base      = mt_rand(55, 95);
            $score     = min(100, max(20, $base + mt_rand(-8, 8)));
            $completedAt = Carbon::parse($ca->completed_at)->addMinutes(mt_rand(2, 20));

            $pts = 5;
            $intScore = (int) round($score / 100 * $pts);
            $quizInsert[] = [
                'student_id'     => $ca->student_id,
                'quiz_id'        => $quizId,
                'percentage'     => $score,
                'score'          => $intScore,
                'total_points'   => $pts,
                'xp_earned'      => $intScore * 10,
                'attempt_number' => 1,
                'status'         => 'completed',
                'started_at'     => $completedAt->copy()->subMinutes(mt_rand(3, 12)),
                'completed_at'   => $completedAt,
                'created_at'     => $completedAt,
                'updated_at'     => $completedAt,
            ];
        }

        foreach ($quizInsert as $qi) {
            DB::table('quiz_attempts')->updateOrInsert(
                ['student_id' => $qi['student_id'], 'quiz_id' => $qi['quiz_id']],
                $qi
            );
        }

        $this->command->info('  ✓ quiz_attempts seeded: ' . count($quizInsert));

        // ═══════════════════════════════════════════════════════════════════
        // 3.  GESTURE PERFORMANCES
        // ═══════════════════════════════════════════════════════════════════
        if (!empty($gestureIds)) {
            $gestureInsert = [];

            foreach ($studentIds as $studentId) {
                // Each student practices a random subset of gestures (40-80%)
                $myGestures = collect($gestureIds)->random(
                    max(3, (int) round(count($gestureIds) * (0.4 + mt_rand(0, 40) / 100)))
                )->toArray();

                foreach ($myGestures as $gestureId) {
                    $attempts  = mt_rand(3, 25);
                    $success   = (int) round($attempts * (0.45 + mt_rand(0, 45) / 100));
                    $success   = min($success, $attempts);
                    $mastery   = $success / $attempts;
                    $masteryLv = $mastery >= 0.85 ? 'mastered'
                               : ($mastery >= 0.65 ? 'proficient'
                               : ($mastery >= 0.45 ? 'developing' : 'needs_practice'));
                    $lastAt    = $now->copy()->subDays(mt_rand(0, 13))->subHours(mt_rand(0, 8));

                    $gestureInsert[] = [
                        'student_id'          => $studentId,
                        'gesture_id'          => $gestureId,
                        'attempts'            => $attempts,
                        'successful_attempts' => $success,
                        'wrong_attempts'      => $attempts - $success,
                        'mastery_level'       => $masteryLv,
                        'is_mastered'         => $masteryLv === 'mastered' ? 1 : 0,
                        'last_attempt_at'     => $lastAt,
                        'created_at'          => $lastAt->copy()->subDays(mt_rand(1, 5)),
                        'updated_at'          => $lastAt,
                    ];
                }
            }

            foreach (array_chunk($gestureInsert, 200) as $chunk) {
                DB::table('gesture_performances')->upsert(
                    $chunk,
                    ['student_id', 'gesture_id'],
                    ['attempts', 'successful_attempts', 'wrong_attempts', 'mastery_level', 'is_mastered', 'last_attempt_at', 'updated_at']
                );
            }

            $this->command->info('  ✓ gesture_performances seeded: ' . count($gestureInsert));
        }

        // ═══════════════════════════════════════════════════════════════════
        // 4.  UPDATE last_activity_date ON STUDENTS
        // ═══════════════════════════════════════════════════════════════════
        foreach ($lpUpdates as $studentId => $lastActive) {
            DB::table('students')
                ->where('student_id', $studentId)
                ->where('school_id', $schoolId)
                ->update([
                    'last_activity_date' => $lastActive,
                    'updated_at'         => $lastActive,
                ]);
        }

        // Ensure all students have at least some recent activity date
        foreach ($studentIds as $sid) {
            $existing = DB::table('students')->where('student_id', $sid)->value('last_activity_date');
            if (!$existing) {
                $randomDay = $now->copy()->subDays(mt_rand(0, 10));
                DB::table('students')->where('student_id', $sid)->update([
                    'last_activity_date' => $randomDay,
                    'updated_at'         => $randomDay,
                ]);
            }
        }

        $this->command->info('  ✓ student last_activity_date updated');

        // ═══════════════════════════════════════════════════════════════════
        // 5.  UPDATE teacher users updated_at to simulate logins
        // ═══════════════════════════════════════════════════════════════════
        $teacherUserIds = DB::table('teachers')
            ->whereIn('id', $teacherIds)
            ->pluck('user_id')
            ->toArray();

        foreach ($teacherUserIds as $uid) {
            $loginDay = $now->copy()->subDays(mt_rand(0, 7))->subHours(mt_rand(0, 5));
            DB::table('users')->where('id', $uid)->update(['updated_at' => $loginDay]);
        }

        $this->command->info('  ✓ teacher last-login timestamps updated');
        $this->command->newLine();
        $this->command->info('✅ NWCS dummy activity seeded successfully.');
        $this->command->info('   Refresh the Teacher Leader dashboard to see live charts.');
    }
}
