<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminReportsDummySeeder extends Seeder
{
    public function run(): void
    {
        $adminId = 6; // Christian Paul Mendoza (admin)

        $now = Carbon::now();

        $records = [
            // ── Pending ────────────────────────────────────────────────
            [
                'student_id'     => 1,
                'message'        => 'I cannot open the lesson on Filipino Sign Language basics. The page keeps loading and then showing a blank screen.',
                'status'         => 'pending',
                'admin_response' => null,
                'resolved_by'    => null,
                'resolved_at'    => null,
                'responded_at'   => null,
                'created_at'     => $now->copy()->subHours(3),
                'updated_at'     => $now->copy()->subHours(3),
            ],
            [
                'student_id'     => 2,
                'message'        => 'The quiz for Module 2 seems to have the wrong answers. Question 3 about handshapes is marked wrong even though I selected the correct one.',
                'status'         => 'pending',
                'admin_response' => null,
                'resolved_by'    => null,
                'resolved_at'    => null,
                'responded_at'   => null,
                'created_at'     => $now->copy()->subHours(7),
                'updated_at'     => $now->copy()->subHours(7),
            ],
            [
                'student_id'     => 3,
                'message'        => 'My progress for Lesson 4 was reset. I already completed it yesterday but now it shows as not started.',
                'status'         => 'pending',
                'admin_response' => null,
                'resolved_by'    => null,
                'resolved_at'    => null,
                'responded_at'   => null,
                'created_at'     => $now->copy()->subDay(),
                'updated_at'     => $now->copy()->subDay(),
            ],
            [
                'student_id'     => 4,
                'message'        => 'The video for the gesture "thank you" is not playing. I tried refreshing the page but it still does not work.',
                'status'         => 'pending',
                'admin_response' => null,
                'resolved_by'    => null,
                'resolved_at'    => null,
                'responded_at'   => null,
                'created_at'     => $now->copy()->subDays(2),
                'updated_at'     => $now->copy()->subDays(2),
            ],

            // ── In Progress ────────────────────────────────────────────
            [
                'student_id'     => 5,
                'message'        => 'I accidentally submitted the checkpoint exam before answering all questions. Can you reset my attempt?',
                'status'         => 'in_progress',
                'admin_response' => 'Hi! We are looking into this now and will reset your attempt shortly. Please check back in a few minutes.',
                'resolved_by'    => $adminId,
                'resolved_at'    => null,
                'responded_at'   => $now->copy()->subHours(1),
                'created_at'     => $now->copy()->subDays(2),
                'updated_at'     => $now->copy()->subHours(1),
            ],
            [
                'student_id'     => 1,
                'message'        => 'The app crashes on my tablet whenever I try to use the camera for gesture recognition practice.',
                'status'         => 'in_progress',
                'admin_response' => 'Thank you for reporting this. Our team is investigating device compatibility. We will update you once a fix is available.',
                'resolved_by'    => $adminId,
                'resolved_at'    => null,
                'responded_at'   => $now->copy()->subHours(5),
                'created_at'     => $now->copy()->subDays(3),
                'updated_at'     => $now->copy()->subHours(5),
            ],

            // ── Responded ──────────────────────────────────────────────
            [
                'student_id'     => 2,
                'message'        => 'I forgot my PIN and cannot log in to the app. How can I reset it?',
                'status'         => 'responded',
                'admin_response' => 'Please ask your teacher to reset your PIN from their dashboard under Students > Manage. They can generate a new one for you right away.',
                'resolved_by'    => $adminId,
                'resolved_at'    => null,
                'responded_at'   => $now->copy()->subDays(2),
                'created_at'     => $now->copy()->subDays(4),
                'updated_at'     => $now->copy()->subDays(2),
            ],
            [
                'student_id'     => 3,
                'message'        => 'The streak counter is not updating even though I practice every day.',
                'status'         => 'responded',
                'admin_response' => 'Streaks update at midnight. Make sure you complete at least one lesson activity before 11:59 PM each day. If the issue continues, let us know!',
                'resolved_by'    => $adminId,
                'resolved_at'    => null,
                'responded_at'   => $now->copy()->subDays(3),
                'created_at'     => $now->copy()->subDays(5),
                'updated_at'     => $now->copy()->subDays(3),
            ],

            // ── Resolved ───────────────────────────────────────────────
            [
                'student_id'     => 4,
                'message'        => 'I completed a lesson but my XP points did not increase.',
                'status'         => 'resolved',
                'admin_response' => 'This was a sync issue on our end. We have manually credited your XP for the completed lesson. You should now see the correct total. Sorry for the inconvenience!',
                'resolved_by'    => $adminId,
                'resolved_at'    => $now->copy()->subDays(2),
                'responded_at'   => $now->copy()->subDays(3),
                'created_at'     => $now->copy()->subDays(6),
                'updated_at'     => $now->copy()->subDays(2),
            ],
            [
                'student_id'     => 5,
                'message'        => 'Some of the sign language images are blurry and hard to see clearly on my phone.',
                'status'         => 'resolved',
                'admin_response' => 'We have updated the image assets for better resolution on mobile screens. Please refresh the app and the images should now be clear. Thank you for the feedback!',
                'resolved_by'    => $adminId,
                'resolved_at'    => $now->copy()->subDays(1),
                'responded_at'   => $now->copy()->subDays(4),
                'created_at'     => $now->copy()->subDays(7),
                'updated_at'     => $now->copy()->subDays(1),
            ],
            [
                'student_id'     => 1,
                'message'        => 'The daily challenge did not appear today. Is there a problem with the schedule?',
                'status'         => 'resolved',
                'admin_response' => 'There was a brief delay in generating daily challenges due to a scheduled maintenance window. Everything is back to normal. You will see a new challenge tomorrow!',
                'resolved_by'    => $adminId,
                'resolved_at'    => $now->copy()->subDays(3),
                'responded_at'   => $now->copy()->subDays(5),
                'created_at'     => $now->copy()->subDays(8),
                'updated_at'     => $now->copy()->subDays(3),
            ],
            [
                'student_id'     => 2,
                'message'        => 'I cannot hear any audio during the lessons. The sound icon shows it is on but nothing plays.',
                'status'         => 'resolved',
                'admin_response' => 'We found that audio was muted in your app settings. We have reset your audio settings to the default. Please log out and log back in to apply the fix.',
                'resolved_by'    => $adminId,
                'resolved_at'    => $now->copy()->subDays(4),
                'responded_at'   => $now->copy()->subDays(6),
                'created_at'     => $now->copy()->subDays(10),
                'updated_at'     => $now->copy()->subDays(4),
            ],
        ];

        DB::table('help_requests')->insert($records);

        $this->command->info('✅ Admin Reports dummy data seeded — ' . count($records) . ' help requests inserted.');
    }
}
