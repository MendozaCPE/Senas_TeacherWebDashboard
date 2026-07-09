<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaulDummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $teacherId = 3; // paul@gmail.com
        $schoolId  = 1; // Nasugbu West Central School
        $now       = Carbon::now();

        // ── 1. STUDENTS ──────────────────────────────────────────────
        // 'program_type' enum: Regular | Inclusion | Transition | Self-contained
        // 'fsl_mastery_level' enum: Beginner | Intermediate | Advanced
        $studentsData = [
            ['lrn'=>'123456789001','pin'=>'1111','first_name'=>'Liza',  'last_name'=>'Reyes',   'age'=>10,'grade_level'=>'4','section'=>'Sampaguita','program_type'=>'Self-contained','fsl_mastery_level'=>'Beginner',    'status'=>'active',  'total_xp'=>320, 'level'=>3, 'streak_days'=>5, 'last_activity_date'=>$now->copy()->subDays(1)->toDateString()],
            ['lrn'=>'123456789002','pin'=>'2222','first_name'=>'Marco',  'last_name'=>'Santos',  'age'=>11,'grade_level'=>'5','section'=>'Sampaguita','program_type'=>'Self-contained','fsl_mastery_level'=>'Intermediate','status'=>'active',  'total_xp'=>750, 'level'=>7, 'streak_days'=>12,'last_activity_date'=>$now->copy()->subDays(0)->toDateString()],
            ['lrn'=>'123456789003','pin'=>'3333','first_name'=>'Sofia',  'last_name'=>'Cruz',    'age'=> 9,'grade_level'=>'3','section'=>'Rosal',     'program_type'=>'Self-contained','fsl_mastery_level'=>'Beginner',    'status'=>'active',  'total_xp'=>100, 'level'=>1, 'streak_days'=>2, 'last_activity_date'=>$now->copy()->subDays(3)->toDateString()],
            ['lrn'=>'123456789004','pin'=>'4444','first_name'=>'Rafael', 'last_name'=>'Garcia',  'age'=>12,'grade_level'=>'6','section'=>'Rosal',     'program_type'=>'Self-contained','fsl_mastery_level'=>'Advanced',    'status'=>'active',  'total_xp'=>1200,'level'=>11,'streak_days'=>20,'last_activity_date'=>$now->copy()->subDays(0)->toDateString()],
            ['lrn'=>'123456789005','pin'=>'5555','first_name'=>'Aimee',  'last_name'=>'Flores',  'age'=>10,'grade_level'=>'4','section'=>'Sampaguita','program_type'=>'Self-contained','fsl_mastery_level'=>'Beginner',    'status'=>'inactive','total_xp'=>50,  'level'=>1, 'streak_days'=>0, 'last_activity_date'=>$now->copy()->subDays(14)->toDateString()],
        ];

        $studentIds = [];
        foreach ($studentsData as $s) {
            // avoid duplicates on re-run
            $existing = DB::table('students')->where('lrn', $s['lrn'])->first();
            if ($existing) {
                $studentIds[] = $existing->student_id;
                continue;
            }

            // Create a user row first (required by FK)
            $existingUser = DB::table('users')
                ->where('name', $s['first_name'] . ' ' . $s['last_name'])
                ->where('role', 'student')
                ->first();

            if ($existingUser) {
                $userId = $existingUser->id;
            } else {
                $userId = DB::table('users')->insertGetId([
                    'name'       => $s['first_name'] . ' ' . $s['last_name'],
                    'username'   => strtolower($s['first_name'] . '.' . $s['last_name'] . '.' . $s['lrn']),
                    'email'      => null,
                    'password'   => bcrypt('password'),
                    'role'       => 'student',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $id = DB::table('students')->insertGetId(array_merge($s, [
                'user_id'     => $userId,
                'teacher_id'  => $teacherId,
                'school_id'   => $schoolId,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]));
            $studentIds[] = $id;
        }

        // ── 2. MODULES ───────────────────────────────────────────────
        $modulesData = [
            ['title'=>'FSL Alphabet',        'description'=>'Learn the Filipino Sign Language alphabet A–Z.', 'module_order'=>1,'status'=>'published'],
            ['title'=>'Basic Greetings',     'description'=>'Everyday greetings and polite expressions.',    'module_order'=>2,'status'=>'published'],
            ['title'=>'Numbers & Counting',  'description'=>'Signs for numbers 1–20.',                       'module_order'=>3,'status'=>'published'],
        ];
        $moduleIds = [];
        foreach ($modulesData as $m) {
            $existing = DB::table('modules')->where('teacher_id',$teacherId)->where('title',$m['title'])->first();
            if ($existing) { $moduleIds[] = $existing->module_id; continue; }
            $moduleIds[] = DB::table('modules')->insertGetId(array_merge($m,[
                'teacher_id'=>$teacherId,'created_at'=>$now,'updated_at'=>$now,
            ]));
        }

        // ── 3. LESSONS ───────────────────────────────────────────────
        $lessonsData = [
            // Module 1
            ['module_id'=>$moduleIds[0],'title'=>'Letters A to E','description'=>'Hand shapes for FSL letters A, B, C, D, E.','lesson_type'=>'gesture','difficulty'=>'beginner',     'module_order'=>1,'status'=>'published','published_at'=>$now->copy()->subDays(10)],
            ['module_id'=>$moduleIds[0],'title'=>'Letters F to J','description'=>'Hand shapes for FSL letters F, G, H, I, J.','lesson_type'=>'gesture','difficulty'=>'beginner',     'module_order'=>2,'status'=>'published','published_at'=>$now->copy()->subDays(8)],
            ['module_id'=>$moduleIds[0],'title'=>'Letters K to O','description'=>'Hand shapes for FSL letters K, L, M, N, O.','lesson_type'=>'gesture','difficulty'=>'intermediate','module_order'=>3,'status'=>'published','published_at'=>$now->copy()->subDays(6)],
            // Module 2
            ['module_id'=>$moduleIds[1],'title'=>'Hello & Goodbye',   'description'=>'Signs for greeting and parting.', 'lesson_type'=>'gesture','difficulty'=>'beginner',     'module_order'=>1,'status'=>'published','published_at'=>$now->copy()->subDays(7)],
            ['module_id'=>$moduleIds[1],'title'=>'Please & Thank You', 'description'=>'Polite expressions in FSL.',     'lesson_type'=>'gesture','difficulty'=>'beginner',     'module_order'=>2,'status'=>'published','published_at'=>$now->copy()->subDays(5)],
            // Module 3
            ['module_id'=>$moduleIds[2],'title'=>'Numbers 1–5',  'description'=>'Counting from one to five.',  'lesson_type'=>'gesture','difficulty'=>'beginner',     'module_order'=>1,'status'=>'published','published_at'=>$now->copy()->subDays(4)],
            ['module_id'=>$moduleIds[2],'title'=>'Numbers 6–10', 'description'=>'Counting from six to ten.',  'lesson_type'=>'gesture','difficulty'=>'beginner',     'module_order'=>2,'status'=>'published','published_at'=>$now->copy()->subDays(2)],
        ];
        $lessonIds = [];
        foreach ($lessonsData as $l) {
            $existing = DB::table('lessons')->where('teacher_id',$teacherId)->where('title',$l['title'])->first();
            if ($existing) { $lessonIds[] = $existing->lesson_id; continue; }
            $lessonIds[] = DB::table('lessons')->insertGetId(array_merge($l,[
                'teacher_id'=>$teacherId,'created_at'=>$now,'updated_at'=>$now,
            ]));
        }

        // ── 4. LESSON CONTENTS (2 steps each) ────────────────────────
        $contentsMap = [
            0 => [
                ['step_number'=>1,'content_type'=>'text',        'title'=>'Introduction','content_text'=>'The FSL alphabet uses one hand. Let\'s start with A, B, C, D, E.','gesture_name'=>null],
                ['step_number'=>2,'content_type'=>'gesture_demo','title'=>'Letter A',   'content_text'=>'Make a fist with your thumb on the side.','gesture_name'=>'letter_a'],
                ['step_number'=>3,'content_type'=>'gesture_demo','title'=>'Letter B',   'content_text'=>'Hold all fingers straight up, thumb folded in.','gesture_name'=>'letter_b'],
            ],
            1 => [
                ['step_number'=>1,'content_type'=>'text',        'title'=>'Introduction','content_text'=>'Next letters: F, G, H, I, J.','gesture_name'=>null],
                ['step_number'=>2,'content_type'=>'gesture_demo','title'=>'Letter F',   'content_text'=>'Touch your index fingertip to your thumb, other fingers spread.','gesture_name'=>'letter_f'],
            ],
            2 => [
                ['step_number'=>1,'content_type'=>'text',        'title'=>'Introduction','content_text'=>'Mid-alphabet letters: K, L, M, N, O.','gesture_name'=>null],
                ['step_number'=>2,'content_type'=>'gesture_demo','title'=>'Letter K',   'content_text'=>'Extend index and middle fingers in a V, thumb tucked between them.','gesture_name'=>'letter_k'],
            ],
            3 => [
                ['step_number'=>1,'content_type'=>'text',        'title'=>'Hello',       'content_text'=>'Open palm, move hand outward from forehead.','gesture_name'=>'hello'],
                ['step_number'=>2,'content_type'=>'gesture_demo','title'=>'Goodbye',     'content_text'=>'Wave your open hand side to side.','gesture_name'=>'goodbye'],
            ],
            4 => [
                ['step_number'=>1,'content_type'=>'gesture_demo','title'=>'Please',      'content_text'=>'Flat hand, circular motion on chest.','gesture_name'=>'please'],
                ['step_number'=>2,'content_type'=>'gesture_demo','title'=>'Thank You',   'content_text'=>'Flat hand from chin moving outward.','gesture_name'=>'thank_you'],
            ],
            5 => [
                ['step_number'=>1,'content_type'=>'text',        'title'=>'Intro',       'content_text'=>'Numbers 1–5 use your dominant hand.','gesture_name'=>null],
                ['step_number'=>2,'content_type'=>'gesture_demo','title'=>'Number 1',    'content_text'=>'Point index finger up.','gesture_name'=>'number_1'],
                ['step_number'=>3,'content_type'=>'gesture_demo','title'=>'Number 5',    'content_text'=>'Spread all five fingers open.','gesture_name'=>'number_5'],
            ],
            6 => [
                ['step_number'=>1,'content_type'=>'text',        'title'=>'Intro',       'content_text'=>'Numbers 6–10 require both hands.','gesture_name'=>null],
                ['step_number'=>2,'content_type'=>'gesture_demo','title'=>'Number 6',    'content_text'=>'Tap pinky of one hand to thumb of other.','gesture_name'=>'number_6'],
            ],
        ];
        foreach ($contentsMap as $lessonIdx => $steps) {
            if (!isset($lessonIds[$lessonIdx])) continue;
            $lid = $lessonIds[$lessonIdx];
            $alreadyHas = DB::table('lesson_contents')->where('lesson_id',$lid)->exists();
            if ($alreadyHas) continue;
            foreach ($steps as $step) {
                DB::table('lesson_contents')->insert(array_merge($step,[
                    'lesson_id'=>$lid,'media_url'=>null,'created_at'=>$now,'updated_at'=>$now,
                ]));
            }
        }

        // ── 5. QUIZZES & QUESTIONS ────────────────────────────────────
        // One quiz per lesson
        $quizIds = [];
        foreach ($lessonIds as $idx => $lid) {
            $existing = DB::table('quizzes')->where('lesson_id',$lid)->first();
            if ($existing) { $quizIds[$idx] = $existing->quiz_id; continue; }
            $quizIds[$idx] = DB::table('quizzes')->insertGetId([
                'lesson_id'    => $lid,
                'title'        => 'Quiz ' . ($idx + 1),
                'description'  => 'Test your knowledge.',
                'total_points' => 10,
                'passing_score'=> 70,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        // One question + 4 options per quiz (skipping if already exists)
        $questionTexts = [
            'Which hand shape represents the FSL letter A?',
            'How do you sign the letter F in FSL?',
            'What letter uses a V-shape with thumb tucked between fingers?',
            'Which gesture means "Hello" in FSL?',
            'What does moving a flat hand from your chin outward mean?',
            'How many fingers are raised for the number 1?',
            'Which number requires touching the pinky to the opposite thumb?',
        ];
        foreach ($quizIds as $idx => $qzId) {
            $alreadyHas = DB::table('quiz_questions')->where('quiz_id',$qzId)->exists();
            if ($alreadyHas) continue;
            $qId = DB::table('quiz_questions')->insertGetId([
                'quiz_id'        => $qzId,
                'question_number'=> 1,
                'question_type'  => 'multiple_choice',
                'question_text'  => $questionTexts[$idx] ?? 'What is the correct sign?',
                'points'         => 10,
                'media_url'      => null,
                'gesture_required'=> null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
            $options = [
                ['option_text'=>'Option A – Correct answer','is_correct'=>true],
                ['option_text'=>'Option B – Wrong answer', 'is_correct'=>false],
                ['option_text'=>'Option C – Wrong answer', 'is_correct'=>false],
                ['option_text'=>'Option D – Wrong answer', 'is_correct'=>false],
            ];
            foreach ($options as $opt) {
                DB::table('quiz_options')->insert(array_merge($opt,[
                    'question_id'=>$qId,'option_media_url'=>null,'created_at'=>$now,'updated_at'=>$now,
                ]));
            }
        }

        // ── 6. LESSON ASSIGNMENTS ─────────────────────────────────────
        // Different progress states per student
        // [studentIdx, lessonIdx, status, is_locked, score, daysAgo]
        $assignments = [
            // Liza (idx 0) – completed first 2, in_progress on 3rd
            [0, 0, 'completed', false, 90,  8],
            [0, 1, 'completed', false, 80,  6],
            [0, 2, 'in_progress', false, null, 3],
            [0, 3, 'pending',  true,  null, 0],
            // Marco (idx 1) – completed most, advanced
            [1, 0, 'completed', false, 100, 9],
            [1, 1, 'completed', false, 90,  7],
            [1, 2, 'completed', false, 85,  5],
            [1, 3, 'completed', false, 95,  4],
            [1, 4, 'completed', false, 70,  3],
            [1, 5, 'in_progress', false, null, 1],
            // Sofia (idx 2) – just started
            [2, 0, 'in_progress', false, null, 2],
            [2, 1, 'pending',  true,  null, 0],
            // Rafael (idx 3) – all completed
            [3, 0, 'completed', false, 100, 12],
            [3, 1, 'completed', false, 100, 10],
            [3, 2, 'completed', false, 90,  8],
            [3, 3, 'completed', false, 85,  6],
            [3, 4, 'completed', false, 95,  5],
            [3, 5, 'completed', false, 80,  4],
            [3, 6, 'completed', false, 90,  2],
            // Aimee (idx 4) – inactive, only one assigned
            [4, 0, 'pending',  false, null, 14],
        ];

        foreach ($assignments as [$sIdx, $lIdx, $status, $locked, $score, $daysAgo]) {
            if (!isset($studentIds[$sIdx], $lessonIds[$lIdx])) continue;
            $sid = $studentIds[$sIdx];
            $lid = $lessonIds[$lIdx];
            $already = DB::table('lesson_assignments')
                ->where('student_id',$sid)->where('lesson_id',$lid)->exists();
            if ($already) continue;
            DB::table('lesson_assignments')->insert([
                'lesson_id'   => $lid,
                'student_id'  => $sid,
                'assigned_at' => $now->copy()->subDays($daysAgo + 1),
                'status'      => $status,
                'notified'    => true,
                'completed_at'=> $status === 'completed' ? $now->copy()->subDays($daysAgo) : null,
                'score'       => $score,
                'is_locked'   => $locked,
                'created_at'  => $now->copy()->subDays($daysAgo + 1),
                'updated_at'  => $now,
            ]);
        }

        // ── 7. STUDENT LESSON PROGRESS ────────────────────────────────
        $progressData = [
            // [studentIdx, lessonIdx, current_step, lesson_completed, quiz_completed, quiz_score]
            [0, 0, 3, true,  true,  90],
            [0, 1, 2, true,  true,  80],
            [0, 2, 2, false, false, null],
            [1, 0, 3, true,  true,  100],
            [1, 1, 2, true,  true,  90],
            [1, 2, 3, true,  true,  85],
            [1, 3, 2, true,  true,  95],
            [1, 4, 2, true,  true,  70],
            [1, 5, 2, false, false, null],
            [2, 0, 1, false, false, null],
            [3, 0, 3, true,  true,  100],
            [3, 1, 2, true,  true,  100],
            [3, 2, 3, true,  true,  90],
            [3, 3, 2, true,  true,  85],
            [3, 4, 2, true,  true,  95],
            [3, 5, 3, true,  true,  80],
            [3, 6, 2, true,  true,  90],
        ];
        foreach ($progressData as [$sIdx, $lIdx, $step, $lessonDone, $quizDone, $qScore]) {
            if (!isset($studentIds[$sIdx], $lessonIds[$lIdx])) continue;
            $sid = $studentIds[$sIdx];
            $lid = $lessonIds[$lIdx];
            $already = DB::table('student_lesson_progress')
                ->where('student_id',$sid)->where('lesson_id',$lid)->exists();
            if ($already) continue;
            DB::table('student_lesson_progress')->insert([
                'student_id'       => $sid,
                'lesson_id'        => $lid,
                'current_step'     => $step,
                'lesson_completed' => $lessonDone,
                'quiz_completed'   => $quizDone,
                'quiz_score'       => $qScore,
                'last_accessed_at' => $now->copy()->subDays(rand(0,5)),
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // ── 8. LEARNING PATHS ─────────────────────────────────────────
        // learning_goal enum: Alphabet_Numbers | Greetings | Classroom_Words | Everything
        // practice_time enum: 5_10_min | 15_20_min | 30_min | 1_hour_plus
        $lpData = [
            [0, 'Beginner',    'Alphabet_Numbers',   '30_min',      false],
            [1, 'Intermediate','Greetings',           '30_min',      false],
            [2, 'Beginner',    'Alphabet_Numbers',   '15_20_min',   false],
            [3, 'Advanced',    'Everything',          '1_hour_plus', true],
        ];
        foreach ($lpData as [$sIdx, $level, $goal, $time, $done]) {
            if (!isset($studentIds[$sIdx])) continue;
            $sid = $studentIds[$sIdx];
            $already = DB::table('learning_paths')->where('student_id',$sid)->exists();
            if ($already) continue;
            DB::table('learning_paths')->insert([
                'student_id'   => $sid,
                'fsl_level'    => $level,
                'learning_goal'=> $goal,
                'practice_time'=> $time,
                'is_completed' => $done,
                'completed_at' => $done ? $now->copy()->subDays(1) : null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        // ── 9. QUIZ ATTEMPTS & XP LOGS ───────────────────────────────
        // Completed assignments → quiz attempts + xp_log
        $completedAttempts = [
            // [studentIdx, lessonIdx (maps to quiz), score, xp]
            [0, 0, 90,  50],
            [0, 1, 80,  40],
            [1, 0, 100, 60],
            [1, 1, 90,  50],
            [1, 2, 85,  45],
            [1, 3, 95,  55],
            [1, 4, 70,  30],
            [3, 0, 100, 60],
            [3, 1, 100, 60],
            [3, 2, 90,  50],
            [3, 3, 85,  45],
            [3, 4, 95,  55],
            [3, 5, 80,  40],
            [3, 6, 90,  50],
        ];
        foreach ($completedAttempts as [$sIdx, $lIdx, $score, $xp]) {
            if (!isset($studentIds[$sIdx], $quizIds[$lIdx])) continue;
            $sid  = $studentIds[$sIdx];
            $qzId = $quizIds[$lIdx];
            $lid  = $lessonIds[$lIdx];
            $already = DB::table('quiz_attempts')
                ->where('student_id',$sid)->where('quiz_id',$qzId)->where('status','completed')->exists();
            if ($already) continue;
            $pct = $score;
            $attemptId = DB::table('quiz_attempts')->insertGetId([
                'student_id'          => $sid,
                'quiz_id'             => $qzId,
                'score'               => $score / 10,
                'total_points'        => 10,
                'percentage'          => $pct,
                'status'              => 'completed',
                'xp_earned'           => $xp,
                'is_first_completion' => true,
                'attempt_number'      => 1,
                'is_first_attempt_perfect'=> $score === 100,
                'started_at'          => $now->copy()->subDays(2),
                'completed_at'        => $now->copy()->subDays(1),
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
            DB::table('xp_log')->insert([
                'student_id'      => $sid,
                'quiz_attempt_id' => $attemptId,
                'lesson_id'       => $lid,
                'action'          => 'quiz_completed',
                'xp_amount'       => $xp,
                'reason'          => 'Completed quiz with score ' . $score . '%',
                'created_at'      => $now->copy()->subDays(1),
                'updated_at'      => $now->copy()->subDays(1),
            ]);
        }

        $this->command->info('✅ Paul dummy data seeded successfully.');
        $this->command->info('   Students: Liza, Marco, Sofia, Rafael, Aimee');
        $this->command->info('   Modules: 3  |  Lessons: 7  |  Quizzes: 7');
    }
}
