<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeds dummy achievement progress for ALL students of
 * teacher christianpaulmendoza10@gmail.com.
 *
 * Achievements are assigned based on each student's actual
 * total_xp and streak_days values already in the DB, so the
 * data stays consistent regardless of which students exist.
 */
class PaulStudentAchievementsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ── 1. Resolve teacher via email ──────────────────────────────
        $user = DB::table('users')
            ->where('email', 'christianpaulmendoza10@gmail.com')
            ->first();

        if (!$user) {
            $this->command->error('User christianpaulmendoza10@gmail.com not found.');
            return;
        }

        $teacher = DB::table('teachers')->where('user_id', $user->id)->first();

        if (!$teacher) {
            $this->command->error('Teacher record not found for that user.');
            return;
        }

        // ── 2. Get all students under this teacher ────────────────────
        $students = DB::table('students')
            ->where('teacher_id', $teacher->id)
            ->get();

        if ($students->isEmpty()) {
            $this->command->error('No students found for this teacher.');
            return;
        }

        // ── 3. Resolve all achievements by code ──────────────────────
        $achievements = DB::table('achievements')->get()->keyBy('code');

        if ($achievements->isEmpty()) {
            $this->command->error('No achievements found. Run AchievementSeeder first.');
            return;
        }

        // ── 4. Helper: insert a student_achievement row (skip if exists)
        $upsert = function (
            int    $studentId,
            string $code,
            bool   $unlocked,
            int    $current,
            int    $target,
            ?Carbon $unlockedAt = null
        ) use ($achievements, $now) {
            if (!$achievements->has($code)) {
                return;
            }

            $achId = $achievements[$code]->id;

            $exists = DB::table('student_achievements')
                ->where('student_id', $studentId)
                ->where('achievement_id', $achId)
                ->exists();

            if ($exists) {
                return;
            }

            DB::table('student_achievements')->insert([
                'student_id'       => $studentId,
                'achievement_id'   => $achId,
                'is_unlocked'      => $unlocked,
                'unlocked_at'      => $unlocked ? ($unlockedAt ?? $now) : null,
                'progress_current' => $current,
                'progress_target'  => $target,
                'metadata'         => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        };

        // ── 5. Seed achievements for every student ────────────────────
        foreach ($students as $s) {
            $id     = $s->student_id;
            $xp     = (int) ($s->total_xp    ?? 0);
            $streak = (int) ($s->streak_days  ?? 0);
            $level  = strtolower($s->fsl_mastery_level ?? 'beginner');

            // ── XP milestones ─────────────────────────────────────────
            $xpTiers = [
                'xp_50'   => 50,
                'xp_100'  => 100,
                'xp_250'  => 250,
                'xp_500'  => 500,
                'xp_1000' => 1000,
                'xp_2500' => 2500,
                'xp_5000' => 5000,
            ];
            $daysAgo = 20;
            foreach ($xpTiers as $code => $threshold) {
                if ($xp >= $threshold) {
                    $upsert($id, $code, true, $threshold, $threshold, $now->copy()->subDays($daysAgo));
                } else {
                    $upsert($id, $code, false, min($xp, $threshold - 1), $threshold);
                }
                $daysAgo = max(1, $daysAgo - 3);
            }

            // ── Beginner achievements ─────────────────────────────────
            // Count completed lessons for this student
            $completedLessons = DB::table('lesson_assignments')
                ->where('student_id', $id)
                ->where('status', 'completed')
                ->count();

            $upsert($id, 'beginner_welcome',
                $completedLessons >= 1, min($completedLessons, 1), 1,
                $completedLessons >= 1 ? $now->copy()->subDays(15) : null);

            $upsert($id, 'beginner_5_lessons',
                $completedLessons >= 5, min($completedLessons, 5), 5,
                $completedLessons >= 5 ? $now->copy()->subDays(10) : null);

            $upsert($id, 'beginner_10_lessons',
                $completedLessons >= 10, min($completedLessons, 10), 10,
                $completedLessons >= 10 ? $now->copy()->subDays(5) : null);

            // Alphabet / Numbers — use XP as a proxy for gesture mastery
            $alphabetProgress = min(26, (int) ($xp / 15));
            $upsert($id, 'alphabet_master',
                $alphabetProgress >= 26, $alphabetProgress, 26,
                $alphabetProgress >= 26 ? $now->copy()->subDays(8) : null);

            $numberProgress = min(10, (int) ($xp / 40));
            $upsert($id, 'numbers_master',
                $numberProgress >= 10, $numberProgress, 10,
                $numberProgress >= 10 ? $now->copy()->subDays(6) : null);

            // ── Intermediate achievements ─────────────────────────────
            $isIntermediate = in_array($level, ['intermediate', 'advanced', 'graduated']);
            $upsert($id, 'intermediate_reached',
                $isIntermediate, $isIntermediate ? 1 : 0, 1,
                $isIntermediate ? $now->copy()->subDays(9) : null);

            $intermediateLessons = DB::table('lesson_assignments')
                ->join('lessons', 'lesson_assignments.lesson_id', '=', 'lessons.lesson_id')
                ->where('lesson_assignments.student_id', $id)
                ->where('lesson_assignments.status', 'completed')
                ->where('lessons.difficulty', 'intermediate')
                ->count();

            $upsert($id, 'intermediate_5_lessons',
                $intermediateLessons >= 5, min($intermediateLessons, 5), 5,
                $intermediateLessons >= 5 ? $now->copy()->subDays(7) : null);

            $upsert($id, 'intermediate_10_lessons',
                $intermediateLessons >= 10, min($intermediateLessons, 10), 10,
                $intermediateLessons >= 10 ? $now->copy()->subDays(3) : null);

            $greetingsDone = $isIntermediate && $xp >= 400;
            $upsert($id, 'greetings_master',
                $greetingsDone, $greetingsDone ? 1 : 0, 1,
                $greetingsDone ? $now->copy()->subDays(5) : null);

            // ── Advanced achievements ─────────────────────────────────
            $isAdvanced = in_array($level, ['advanced', 'graduated']);
            $upsert($id, 'advanced_reached',
                $isAdvanced, $isAdvanced ? 1 : 0, 1,
                $isAdvanced ? $now->copy()->subDays(3) : null);

            $advancedLessons = DB::table('lesson_assignments')
                ->join('lessons', 'lesson_assignments.lesson_id', '=', 'lessons.lesson_id')
                ->where('lesson_assignments.student_id', $id)
                ->where('lesson_assignments.status', 'completed')
                ->where('lessons.difficulty', 'advanced')
                ->count();

            $upsert($id, 'advanced_5_lessons',
                $advancedLessons >= 5, min($advancedLessons, 5), 5,
                $advancedLessons >= 5 ? $now->copy()->subDays(2) : null);

            // ── Graduation ────────────────────────────────────────────
            $isGraduated = $level === 'graduated';
            $upsert($id, 'graduated',
                $isGraduated, $isGraduated ? 1 : 0, 1,
                $isGraduated ? $now->copy()->subDays(1) : null);

            // ── Streak achievements ───────────────────────────────────
            $upsert($id, 'streak_3',
                $streak >= 3, min($streak, 3), 3,
                $streak >= 3 ? $now->copy()->subDays(max(1, $streak - 2)) : null);

            $upsert($id, 'streak_7',
                $streak >= 7, min($streak, 7), 7,
                $streak >= 7 ? $now->copy()->subDays(max(1, $streak - 6)) : null);

            $upsert($id, 'streak_30',
                $streak >= 30, min($streak, 30), 30,
                $streak >= 30 ? $now->copy()->subDays(1) : null);

            // ── Special achievements ──────────────────────────────────
            // quiz_whiz: scored 100% on any quiz attempt
            $perfectQuiz = DB::table('quiz_attempts')
                ->where('student_id', $id)
                ->where('percentage', 100)
                ->where('status', 'completed')
                ->exists();

            $upsert($id, 'quiz_whiz',
                $perfectQuiz, $perfectQuiz ? 1 : 0, 1,
                $perfectQuiz ? $now->copy()->subDays(5) : null);

            $upsert($id, 'leaderboard_top',
                $xp >= 1000, $xp >= 1000 ? 1 : 0, 1,
                $xp >= 1000 ? $now->copy()->subDays(4) : null);

            $upsert($id, 'all_badges', false, 0, 1);
        }

        // ── Summary ──────────────────────────────────────────────────
        $studentIds = $students->pluck('student_id')->toArray();

        $total    = DB::table('student_achievements')->whereIn('student_id', $studentIds)->count();
        $unlocked = DB::table('student_achievements')->whereIn('student_id', $studentIds)->where('is_unlocked', true)->count();

        $this->command->info("✅ Achievement data seeded for all " . count($students) . " students of christianpaulmendoza10@gmail.com");
        $this->command->info("   Total rows: {$total}  |  Unlocked: {$unlocked}");
        $this->command->newLine();

        foreach ($students as $s) {
            $tot = DB::table('student_achievements')->where('student_id', $s->student_id)->count();
            $unl = DB::table('student_achievements')->where('student_id', $s->student_id)->where('is_unlocked', true)->count();
            $this->command->line("   {$s->first_name} {$s->last_name}: {$unl}/{$tot} unlocked");
        }
    }
}
