<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeacherReportsDummySeeder extends Seeder
{
    public function run(): void
    {
        $teacherId   = 2;   // Christian Paul Mendoza (teacher record)
        $teacherUser = 6;   // user_id for the same person
        $adminUser   = 25;  // CHRISTIAN PAUL MENDOZA (admin account)

        $now = Carbon::now();

        // Student IDs under this teacher: 5,6,7,8,9,10,16,17,18,19,20
        $records = [

            // ── PENDING (teacher hasn't reviewed yet) ─────────────────────
            [
                'student_id'           => 16,  // Liza Reyes
                'teacher_id'           => $teacherId,
                'message'              => 'I cannot access the lesson on colors and shapes in FSL. When I tap on it, the screen just goes blank and nothing loads.',
                'status'               => 'pending',
                'teacher_note'         => null,
                'teacher_response'     => null,
                'teacher_responded_at' => null,
                'teacher_responded_by' => null,
                'escalated_at'         => null,
                'escalated_by'         => null,
                'escalation_reason'    => null,
                'admin_response'       => null,
                'resolved_by'          => null,
                'resolved_at'          => null,
                'responded_at'         => null,
                'created_at'           => $now->copy()->subHours(2),
                'updated_at'           => $now->copy()->subHours(2),
            ],
            [
                'student_id'           => 17,  // Marco Santos
                'teacher_id'           => $teacherId,
                'message'              => 'The quiz for Module 1 Lesson 3 keeps showing the wrong correct answer. I answered "Thank You" sign but it says I got it wrong.',
                'status'               => 'pending',
                'teacher_note'         => null,
                'teacher_response'     => null,
                'teacher_responded_at' => null,
                'teacher_responded_by' => null,
                'escalated_at'         => null,
                'escalated_by'         => null,
                'escalation_reason'    => null,
                'admin_response'       => null,
                'resolved_by'          => null,
                'resolved_at'          => null,
                'responded_at'         => null,
                'created_at'           => $now->copy()->subHours(5),
                'updated_at'           => $now->copy()->subHours(5),
            ],
            [
                'student_id'           => 5,   // CPE Mendoza
                'teacher_id'           => $teacherId,
                'message'              => 'My daily challenge did not update today. It still shows yesterday\'s challenge and I cannot start a new one.',
                'status'               => 'pending',
                'teacher_note'         => null,
                'teacher_response'     => null,
                'teacher_responded_at' => null,
                'teacher_responded_by' => null,
                'escalated_at'         => null,
                'escalated_by'         => null,
                'escalation_reason'    => null,
                'admin_response'       => null,
                'resolved_by'          => null,
                'resolved_at'          => null,
                'responded_at'         => null,
                'created_at'           => $now->copy()->subDay(),
                'updated_at'           => $now->copy()->subDay(),
            ],

            // ── UNDER REVIEW (teacher is looking into it) ─────────────────
            [
                'student_id'           => 18,  // Sofia Cruz
                'teacher_id'           => $teacherId,
                'message'              => 'The gesture recognition camera freezes every time I try to practice the "Hello" sign. It worked before but not anymore.',
                'status'               => 'under_review',
                'teacher_note'         => 'Checking if this is a device-specific issue. Asked Sofia to try on a different device.',
                'teacher_response'     => 'Hi Sofia! I am currently looking into this. Please try using a different browser or device in the meantime. I will get back to you shortly.',
                'teacher_responded_at' => $now->copy()->subHours(3),
                'teacher_responded_by' => $teacherUser,
                'escalated_at'         => null,
                'escalated_by'         => null,
                'escalation_reason'    => null,
                'admin_response'       => null,
                'resolved_by'          => null,
                'resolved_at'          => null,
                'responded_at'         => null,
                'created_at'           => $now->copy()->subDays(2),
                'updated_at'           => $now->copy()->subHours(3),
            ],
            [
                'student_id'           => 19,  // Rafael Garcia
                'teacher_id'           => $teacherId,
                'message'              => 'I accidentally completed the checkpoint exam without finishing all the questions. The timer ran out too fast. Can my attempt be reset?',
                'status'               => 'under_review',
                'teacher_note'         => 'Verifying the attempt in the system. Will check if a reset is possible.',
                'teacher_response'     => 'Hi Rafael, I have noted your concern and am reviewing your exam attempt. Please give me a day to verify this with the records.',
                'teacher_responded_at' => $now->copy()->subHours(6),
                'teacher_responded_by' => $teacherUser,
                'escalated_at'         => null,
                'escalated_by'         => null,
                'escalation_reason'    => null,
                'admin_response'       => null,
                'resolved_by'          => null,
                'resolved_at'          => null,
                'responded_at'         => null,
                'created_at'           => $now->copy()->subDays(3),
                'updated_at'           => $now->copy()->subHours(6),
            ],

            // ── RESOLVED (teacher handled it) ─────────────────────────────
            [
                'student_id'           => 20,  // Aimee Flores
                'teacher_id'           => $teacherId,
                'message'              => 'I forgot my PIN and cannot log in to the app anymore. My parents also do not remember it.',
                'status'               => 'resolved',
                'teacher_note'         => 'Reset PIN via the student management panel.',
                'teacher_response'     => 'Hi Aimee! I have reset your PIN. Your new PIN is 1234. Please log in and change it to something you can remember from the settings.',
                'teacher_responded_at' => $now->copy()->subDays(1),
                'teacher_responded_by' => $teacherUser,
                'escalated_at'         => null,
                'escalated_by'         => null,
                'escalation_reason'    => null,
                'admin_response'       => null,
                'resolved_by'          => $teacherUser,
                'resolved_at'          => $now->copy()->subDays(1),
                'responded_at'         => $now->copy()->subDays(1),
                'created_at'           => $now->copy()->subDays(4),
                'updated_at'           => $now->copy()->subDays(1),
            ],
            [
                'student_id'           => 6,   // Jane
                'teacher_id'           => $teacherId,
                'message'              => 'The sound is not playing during lessons. I can see the videos but there is no audio at all.',
                'status'               => 'resolved',
                'teacher_note'         => 'Student had phone on silent. Reminded about device audio settings.',
                'teacher_response'     => 'Hi! Please check if your phone is on silent mode or if the app volume is muted. Also make sure to allow the app microphone and audio permissions in your phone settings.',
                'teacher_responded_at' => $now->copy()->subDays(2),
                'teacher_responded_by' => $teacherUser,
                'escalated_at'         => null,
                'escalated_by'         => null,
                'escalation_reason'    => null,
                'admin_response'       => null,
                'resolved_by'          => $teacherUser,
                'resolved_at'          => $now->copy()->subDays(2),
                'responded_at'         => $now->copy()->subDays(2),
                'created_at'           => $now->copy()->subDays(6),
                'updated_at'           => $now->copy()->subDays(2),
            ],
            [
                'student_id'           => 7,   // Mark
                'teacher_id'           => $teacherId,
                'message'              => 'My XP points did not increase after completing Lesson 2. I finished the lesson and quiz but my score is the same.',
                'status'               => 'resolved',
                'teacher_note'         => 'Confirmed XP sync delay. Student re-opened app and XP updated.',
                'teacher_response'     => 'Hi Mark! XP updates can sometimes take a few minutes to sync. Please close the app completely and reopen it. If it still does not update after 10 minutes, let me know and I will escalate it.',
                'teacher_responded_at' => $now->copy()->subDays(3),
                'teacher_responded_by' => $teacherUser,
                'escalated_at'         => null,
                'escalated_by'         => null,
                'escalation_reason'    => null,
                'admin_response'       => null,
                'resolved_by'          => $teacherUser,
                'resolved_at'          => $now->copy()->subDays(3),
                'responded_at'         => $now->copy()->subDays(3),
                'created_at'           => $now->copy()->subDays(7),
                'updated_at'           => $now->copy()->subDays(3),
            ],

            // ── ESCALATED (teacher raised to admin) ───────────────────────
            [
                'student_id'           => 8,   // Sarah
                'teacher_id'           => $teacherId,
                'message'              => 'I completed all the lessons but my certificate of completion still has not appeared. Other students in my class got theirs already.',
                'status'               => 'escalated',
                'teacher_note'         => 'Checked student progress — all lessons are marked complete. Certificate generation may be a system bug.',
                'teacher_response'     => 'Hi Sarah! I confirmed you have completed all required lessons. This appears to be a certificate generation issue on the system side. I am raising this to the admin.',
                'teacher_responded_at' => $now->copy()->subDays(1),
                'teacher_responded_by' => $teacherUser,
                'escalated_at'         => $now->copy()->subHours(10),
                'escalated_by'         => $teacherUser,
                'escalation_reason'    => 'Student has completed all required lessons and quizzes but has not received a certificate of completion. Checked the progress records and everything is marked done. This seems to be a system-level certificate generation bug that I cannot fix from the teacher dashboard.',
                'admin_response'       => null,
                'resolved_by'          => null,
                'resolved_at'          => null,
                'responded_at'         => null,
                'created_at'           => $now->copy()->subDays(5),
                'updated_at'           => $now->copy()->subHours(10),
            ],
            [
                'student_id'           => 9,   // Michael
                'teacher_id'           => $teacherId,
                'message'              => 'The app shows my account has been suspended even though I did not do anything wrong. I cannot access any lessons.',
                'status'               => 'escalated',
                'teacher_note'         => 'Student account shows active status in my dashboard but student cannot log in. Need admin to investigate the account suspension.',
                'teacher_response'     => 'Hi Michael! I can see your account is active from my end but I understand you are having trouble logging in. I am escalating this to the admin team right away for immediate assistance.',
                'teacher_responded_at' => $now->copy()->subDays(2),
                'teacher_responded_by' => $teacherUser,
                'escalated_at'         => $now->copy()->subDays(1),
                'escalated_by'         => $teacherUser,
                'escalation_reason'    => 'Student cannot log in and reports a suspension message. The teacher dashboard shows the account as active. This is an account access issue that requires admin-level investigation.',
                'admin_response'       => null,
                'resolved_by'          => null,
                'resolved_at'          => null,
                'responded_at'         => null,
                'created_at'           => $now->copy()->subDays(6),
                'updated_at'           => $now->copy()->subDays(1),
            ],

            // ── CLOSED (admin has responded) ──────────────────────────────
            [
                'student_id'           => 16,  // Liza Reyes (second report)
                'teacher_id'           => $teacherId,
                'message'              => 'My profile picture changed to a default avatar even though I uploaded a custom one. Can it be restored?',
                'status'               => 'closed',
                'teacher_note'         => 'Profile picture issue — beyond teacher permissions. Escalating to admin.',
                'teacher_response'     => 'Hi Liza! Profile picture issues are managed at the system level. I have raised this to our admin team to help restore it.',
                'teacher_responded_at' => $now->copy()->subDays(4),
                'teacher_responded_by' => $teacherUser,
                'escalated_at'         => $now->copy()->subDays(3),
                'escalated_by'         => $teacherUser,
                'escalation_reason'    => 'Student profile picture was reset to default after a recent app update. Teacher does not have access to restore uploaded profile images from the dashboard.',
                'admin_response'       => 'Hi Liza! We have identified the issue — a recent update caused profile pictures to reset for some accounts. We have restored your original profile picture. Please log out and back in to see the change. Sorry for the inconvenience!',
                'resolved_by'          => $adminUser,
                'resolved_at'          => $now->copy()->subDays(2),
                'responded_at'         => $now->copy()->subDays(2),
                'created_at'           => $now->copy()->subDays(8),
                'updated_at'           => $now->copy()->subDays(2),
            ],
        ];

        DB::table('help_requests')->insert($records);

        $this->command->info('✅ ' . count($records) . ' dummy reports seeded for Teacher Christian Paul Mendoza.');
    }
}
