<?php

namespace Database\Seeders;

use App\Models\TeacherNotification;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TeacherNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $teacherIds = [2, 3];
        TeacherNotification::whereIn('teacher_id', $teacherIds)->delete();

        $now = Carbon::now();

        $notifications = [

            // ── quiz_answered ─────────────────────────────────────────────
            [
                'teacher_id' => 2, 'type' => 'quiz_answered',
                'title'   => '🏆 CPE Mendoza answered a quiz',
                'message' => 'Scored 100% on "FSL Alphabet: Letters A–E" (passed) — Attempt #1',
                'icon' => 'quiz', 'color' => '#3B82F6',
                'data'       => json_encode(['student_id' => 5, 'lesson_id' => 1, 'lesson_title' => 'FSL Alphabet: Letters A–E', 'percentage' => 100, 'status' => 'completed', 'attempt_number' => 1]),
                'action_url' => '/reports?open_student=5',
                'is_read' => false, 'read_at' => null,
                'created_at' => $now->copy()->subMinutes(4),
                'updated_at' => $now->copy()->subMinutes(4),
            ],
            [
                'teacher_id' => 2, 'type' => 'quiz_answered',
                'title'   => '✅ Jane answered a quiz',
                'message' => 'Scored 80% on "Basic FSL Greetings" (passed) — Attempt #1',
                'icon' => 'quiz', 'color' => '#3B82F6',
                'data'       => json_encode(['student_id' => 6, 'lesson_id' => 2, 'lesson_title' => 'Basic FSL Greetings', 'percentage' => 80, 'status' => 'completed', 'attempt_number' => 1]),
                'action_url' => '/reports?open_student=6',
                'is_read' => false, 'read_at' => null,
                'created_at' => $now->copy()->subMinutes(18),
                'updated_at' => $now->copy()->subMinutes(18),
            ],
            [
                'teacher_id' => 2, 'type' => 'quiz_answered',
                'title'   => '❌ Mark answered a quiz',
                'message' => 'Scored 45% on "FSL School Vocabulary" (failed) — Attempt #2',
                'icon' => 'quiz', 'color' => '#3B82F6',
                'data'       => json_encode(['student_id' => 7, 'lesson_id' => 3, 'lesson_title' => 'FSL School Vocabulary', 'percentage' => 45, 'status' => 'failed', 'attempt_number' => 2]),
                'action_url' => '/reports?open_student=7',
                'is_read' => false, 'read_at' => null,
                'created_at' => $now->copy()->subMinutes(35),
                'updated_at' => $now->copy()->subMinutes(35),
            ],
            [
                'teacher_id' => 2, 'type' => 'quiz_answered',
                'title'   => '🎯 Sarah answered a quiz',
                'message' => 'Scored 92% on "Family & Relationships" (passed) — Attempt #1',
                'icon' => 'quiz', 'color' => '#3B82F6',
                'data'       => json_encode(['student_id' => 8, 'lesson_id' => 5, 'lesson_title' => 'Family & Relationships', 'percentage' => 92, 'status' => 'completed', 'attempt_number' => 1]),
                'action_url' => '/reports?open_student=8',
                'is_read' => true, 'read_at' => $now->copy()->subMinutes(50),
                'created_at' => $now->copy()->subHours(1),
                'updated_at' => $now->copy()->subMinutes(50),
            ],

            // ── level_up ──────────────────────────────────────────────────
            [
                'teacher_id' => 2, 'type' => 'level_up',
                'title'   => '📈 CPE Mendoza leveled up!',
                'message' => 'Reached Level 3 (Emerging Signer) with 250 XP',
                'icon' => 'trending_up', 'color' => '#10B981',
                'data'       => json_encode(['student_id' => 5, 'old_level' => 2, 'new_level' => 3, 'level_name' => 'Emerging Signer', 'total_xp' => 250]),
                'action_url' => '/reports?open_student=5',
                'is_read' => false, 'read_at' => null,
                'created_at' => $now->copy()->subHours(2),
                'updated_at' => $now->copy()->subHours(2),
            ],
            [
                'teacher_id' => 2, 'type' => 'level_up',
                'title'   => '📈 Michael leveled up!',
                'message' => 'Reached Level 2 (Beginner Signer) with 105 XP',
                'icon' => 'trending_up', 'color' => '#10B981',
                'data'       => json_encode(['student_id' => 9, 'old_level' => 1, 'new_level' => 2, 'level_name' => 'Beginner Signer', 'total_xp' => 105]),
                'action_url' => '/reports?open_student=9',
                'is_read' => true, 'read_at' => $now->copy()->subHours(3),
                'created_at' => $now->copy()->subHours(3)->subMinutes(20),
                'updated_at' => $now->copy()->subHours(3),
            ],
            [
                'teacher_id' => 2, 'type' => 'level_up',
                'title'   => '📈 Jane leveled up!',
                'message' => 'Reached Level 2 (Beginner Signer) with 102 XP',
                'icon' => 'trending_up', 'color' => '#10B981',
                'data'       => json_encode(['student_id' => 6, 'old_level' => 1, 'new_level' => 2, 'level_name' => 'Beginner Signer', 'total_xp' => 102]),
                'action_url' => '/reports?open_student=6',
                'is_read' => false, 'read_at' => null,
                'created_at' => $now->copy()->subHours(1)->subMinutes(10),
                'updated_at' => $now->copy()->subHours(1)->subMinutes(10),
            ],

            // ── module_passed ─────────────────────────────────────────────
            [
                'teacher_id' => 2, 'type' => 'module_passed',
                'title'   => '🏅 Sarah passed a module quiz',
                'message' => 'Scored 88% on the "Basics" checkpoint quiz — Attempt #1',
                'icon' => 'workspace_premium', 'color' => '#8B5CF6',
                'data'       => json_encode(['student_id' => 8, 'module_id' => 12, 'module_name' => 'Basics', 'percentage' => 88, 'attempt_number' => 1]),
                'action_url' => '/reports?open_student=8',
                'is_read' => false, 'read_at' => null,
                'created_at' => $now->copy()->subHours(4),
                'updated_at' => $now->copy()->subHours(4),
            ],
            [
                'teacher_id' => 2, 'type' => 'module_passed',
                'title'   => '🏅 CPE Mendoza passed a module quiz',
                'message' => 'Scored 100% on the "Basic Greetings" checkpoint quiz — Attempt #1',
                'icon' => 'workspace_premium', 'color' => '#8B5CF6',
                'data'       => json_encode(['student_id' => 5, 'module_id' => 14, 'module_name' => 'Basic Greetings', 'percentage' => 100, 'attempt_number' => 1]),
                'action_url' => '/reports?open_student=5',
                'is_read' => true, 'read_at' => $now->copy()->subHours(5),
                'created_at' => $now->copy()->subHours(5)->subMinutes(10),
                'updated_at' => $now->copy()->subHours(5),
            ],

            // ── checkpoint_passed ─────────────────────────────────────────
            [
                'teacher_id' => 2, 'type' => 'checkpoint_passed',
                'title'   => '✅ Jane took a checkpoint exam',
                'message' => 'Scored 75% on "Module 1 Checkpoint" — Passed — Attempt #1',
                'icon' => 'military_tech', 'color' => '#F59E0B',
                'data'       => json_encode(['student_id' => 6, 'exam_title' => 'Module 1 Checkpoint', 'percentage' => 75, 'passed' => true, 'attempt_number' => 1]),
                'action_url' => '/reports?open_student=6',
                'is_read' => false, 'read_at' => null,
                'created_at' => $now->copy()->subHours(6),
                'updated_at' => $now->copy()->subHours(6),
            ],
            [
                'teacher_id' => 2, 'type' => 'checkpoint_passed',
                'title'   => '❌ Mark took a checkpoint exam',
                'message' => 'Scored 40% on "Module 1 Checkpoint" — Failed — Attempt #1',
                'icon' => 'military_tech', 'color' => '#F59E0B',
                'data'       => json_encode(['student_id' => 7, 'exam_title' => 'Module 1 Checkpoint', 'percentage' => 40, 'passed' => false, 'attempt_number' => 1]),
                'action_url' => '/reports?open_student=7',
                'is_read' => true, 'read_at' => $now->copy()->subHours(7),
                'created_at' => $now->copy()->subHours(7)->subMinutes(15),
                'updated_at' => $now->copy()->subHours(7),
            ],

            // ── mastery_promoted ──────────────────────────────────────────
            [
                'teacher_id' => 2, 'type' => 'mastery_promoted',
                'title'   => '⬆️ You promoted Sarah',
                'message' => 'Sarah has been promoted from Intermediate to Advanced',
                'icon' => 'star', 'color' => '#8B5CF6',
                'data'       => json_encode(['student_id' => 8, 'from_level' => 'Intermediate', 'to_level' => 'Advanced', 'xp' => 620, 'forced' => false]),
                'action_url' => '/reports?open_student=8',
                'is_read' => false, 'read_at' => null,
                'created_at' => $now->copy()->subDays(1)->subHours(2),
                'updated_at' => $now->copy()->subDays(1)->subHours(2),
            ],

            // ── help_request ──────────────────────────────────────────────
            [
                'teacher_id' => 2, 'type' => 'help_request',
                'title'   => '🆘 Michael sent a help request',
                'message' => "I'm having trouble recognizing the gesture for the letter R. The camera keeps saying it's wrong even when I think I'm doing it right.",
                'icon' => 'help', 'color' => '#EF4444',
                'data'       => json_encode(['student_id' => 9, 'help_request_id' => 1]),
                'action_url' => '/reports?open_student=9',
                'is_read' => false, 'read_at' => null,
                'created_at' => $now->copy()->subDays(1)->subHours(5),
                'updated_at' => $now->copy()->subDays(1)->subHours(5),
            ],
            [
                'teacher_id' => 2, 'type' => 'help_request',
                'title'   => '🆘 Mark sent a help request',
                'message' => 'Can you review my quiz answers for School Vocabulary? I think some questions were unclear.',
                'icon' => 'help', 'color' => '#EF4444',
                'data'       => json_encode(['student_id' => 7, 'help_request_id' => 2]),
                'action_url' => '/reports?open_student=7',
                'is_read' => true, 'read_at' => $now->copy()->subDays(2),
                'created_at' => $now->copy()->subDays(2)->subHours(1),
                'updated_at' => $now->copy()->subDays(2),
            ],

            // ── streak_milestone ──────────────────────────────────────────
            [
                'teacher_id' => 2, 'type' => 'streak_milestone',
                'title'   => '🔥 CPE Mendoza hit a 7-day streak!',
                'message' => 'Practiced every day for 7 days in a row. Keep it up!',
                'icon' => 'local_fire_department', 'color' => '#F97316',
                'data'       => json_encode(['student_id' => 5, 'streak_days' => 7]),
                'action_url' => '/reports?open_student=5',
                'is_read' => false, 'read_at' => null,
                'created_at' => $now->copy()->subDays(2)->subHours(3),
                'updated_at' => $now->copy()->subDays(2)->subHours(3),
            ],
            [
                'teacher_id' => 2, 'type' => 'streak_milestone',
                'title'   => '🔥 Jane hit a 3-day streak!',
                'message' => 'Practiced every day for 3 days in a row. Great consistency!',
                'icon' => 'local_fire_department', 'color' => '#F97316',
                'data'       => json_encode(['student_id' => 6, 'streak_days' => 3]),
                'action_url' => '/reports?open_student=6',
                'is_read' => true, 'read_at' => $now->copy()->subDays(3),
                'created_at' => $now->copy()->subDays(3)->subHours(2),
                'updated_at' => $now->copy()->subDays(3),
            ],

            // ── recent unread ─────────────────────────────────────────────
            [
                'teacher_id' => 2, 'type' => 'quiz_answered',
                'title'   => '🎯 u answered a quiz',
                'message' => 'Scored 68% on "FSL Alphabet: Letters A–E" (passed) — Attempt #3',
                'icon' => 'quiz', 'color' => '#3B82F6',
                'data'       => json_encode(['student_id' => 10, 'lesson_id' => 1, 'lesson_title' => 'FSL Alphabet: Letters A–E', 'percentage' => 68, 'status' => 'completed', 'attempt_number' => 3]),
                'action_url' => '/reports?open_student=10',
                'is_read' => false, 'read_at' => null,
                'created_at' => $now->copy()->subMinutes(52),
                'updated_at' => $now->copy()->subMinutes(52),
            ],

        ];

        TeacherNotification::insert($notifications);

        $this->command->info('✅ Inserted ' . count($notifications) . ' notifications for teacher_id 2 (Christian Paul Mendoza).');
    }
}
