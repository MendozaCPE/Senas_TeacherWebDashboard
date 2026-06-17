<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\School;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ==========================================
        // 1. CREATE SCHOOL (Nasugbu West Central School)
        // ==========================================
        $school = School::create([
            'name' => 'Nasugbu West Central School',
            'address' => 'Concepcion St., Barangay IV, Nasugbu, Batangas',
            'region' => 'IV-A',
            'division' => 'Batangas Province',
        ]);

        // ==========================================
        // 2. TEACHER ACCOUNT (Maam Mila)
        // ==========================================
        $teacherUser = User::create([
            'name' => 'Emma Ruth',
            'username' => 'emmaruth',
            'email' => 'emmaruth@deped.gov.ph',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'active',
            'google_id' => null,
        ]);

        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'school_id' => $school->id,
            'first_name' => 'Emma',
            'last_name' => 'Ruth',
            'specialization' => 'SNED',
        ]);

        // ==========================================
        // 3. STUDENT ACCOUNTS
        // ==========================================

        // Student 1: Regular
        $student1User = User::create([
            'name' => 'Juan Dela Cruz',
            'username' => 'juandelacruz',
            'email' => null,
            'password' => null,
            'role' => 'student',
            'status' => 'active',
            'google_id' => null,
        ]);

        $student1 = Student::create([
            'user_id' => $student1User->id,
            'teacher_id' => $teacher->id,
            'school_id' => $school->id,
            'lrn' => '123456789012',
            'pin' => '1234',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'age' => 10,
            'grade_level' => 'Grade 4',
            'section' => 'Rose',
            'program_type' => 'Regular',
        ]);

        // Student 2: Inclusion
        $student2User = User::create([
            'name' => 'Maria Santos',
            'username' => 'mariasantos',
            'email' => null,
            'password' => null,
            'role' => 'student',
            'status' => 'active',
            'google_id' => null,
        ]);

        $student2 = Student::create([
            'user_id' => $student2User->id,
            'teacher_id' => $teacher->id,
            'school_id' => $school->id,
            'lrn' => '234567890123',
            'pin' => '2345',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'age' => 9,
            'grade_level' => 'Grade 3',
            'section' => 'Sunflower',
            'program_type' => 'Inclusion',
        ]);

        // Student 3: Self-contained
        $student3User = User::create([
            'name' => 'Pedro Reyes',
            'username' => 'pedroreyes',
            'email' => null,
            'password' => null,
            'role' => 'student',
            'status' => 'active',
            'google_id' => null,
        ]);

        $student3 = Student::create([
            'user_id' => $student3User->id,
            'teacher_id' => $teacher->id,
            'school_id' => $school->id,
            'lrn' => '345678901234',
            'pin' => '3456',
            'first_name' => 'Pedro',
            'last_name' => 'Reyes',
            'age' => 12,
            'grade_level' => null,
            'section' => 'SPED A',
            'program_type' => 'Self-contained',
        ]);

        // Student 4: Transition
        $student4User = User::create([
            'name' => 'Ana Salvador',
            'username' => 'anasalvador',
            'email' => null,
            'password' => null,
            'role' => 'student',
            'status' => 'active',
            'google_id' => null,
        ]);

        $student4 = Student::create([
            'user_id' => $student4User->id,
            'teacher_id' => $teacher->id,
            'school_id' => $school->id,
            'lrn' => '456789012345',
            'pin' => '4567',
            'first_name' => 'Ana',
            'last_name' => 'Salvador',
            'age' => 11,
            'grade_level' => 'Grade 5',
            'section' => 'Orchid',
            'program_type' => 'Transition',
        ]);

        // Student 5 (user_id = 6): Regular — accessible by user 6
        $student5User = User::create([
            'name' => 'Carlo Bautista',
            'username' => 'carlobautista',
            'email' => null,
            'password' => null,
            'role' => 'student',
            'status' => 'active',
            'google_id' => null,
        ]);

        $student5 = Student::create([
            'user_id' => $student5User->id,
            'teacher_id' => $teacher->id,
            'school_id' => $school->id,
            'lrn' => '567890123456',
            'pin' => '5678',
            'first_name' => 'Carlo',
            'last_name' => 'Bautista',
            'age' => 10,
            'grade_level' => 'Grade 4',
            'section' => 'Sampaguita',
            'program_type' => 'Regular',
        ]);

        // ==========================================
        // 4. GESTURES (FSL Alphabet & Basic Words)
        // ==========================================
        $gestures = [];
        $gestureData = [
            ['name' => 'letter_a', 'display_name' => 'Letter A', 'description' => 'Closed fist with thumb resting beside the index finger.', 'difficulty' => 'beginner'],
            ['name' => 'letter_b', 'display_name' => 'Letter B', 'description' => 'Four fingers straight up, thumb folded across palm.', 'difficulty' => 'beginner'],
            ['name' => 'letter_c', 'display_name' => 'Letter C', 'description' => 'Curved hand forming the shape of letter C.', 'difficulty' => 'beginner'],
            ['name' => 'letter_d', 'display_name' => 'Letter D', 'description' => 'Index finger points up, other fingers touch thumb.', 'difficulty' => 'beginner'],
            ['name' => 'letter_e', 'display_name' => 'Letter E', 'description' => 'All fingers bent down to the middle of the palm.', 'difficulty' => 'beginner'],
            ['name' => 'hello',    'display_name' => 'Hello',    'description' => 'Open hand wave starting from the forehead.', 'difficulty' => 'beginner'],
            ['name' => 'thank_you','display_name' => 'Thank You', 'description' => 'Flat hand touching chin, then moving forward.', 'difficulty' => 'beginner'],
            ['name' => 'please',   'display_name' => 'Please',   'description' => 'Circular motion of flat hand on chest.', 'difficulty' => 'beginner'],
            ['name' => 'yes',      'display_name' => 'Yes',      'description' => 'Closed fist nodding up and down like a head nod.', 'difficulty' => 'beginner'],
            ['name' => 'no',       'display_name' => 'No',       'description' => 'Index and middle finger closing onto thumb.', 'difficulty' => 'beginner'],
            ['name' => 'name',     'display_name' => 'Name',     'description' => 'Two H handshapes tapping together twice.', 'difficulty' => 'intermediate'],
            ['name' => 'school',   'display_name' => 'School',   'description' => 'Both hands clap twice with slight curve.', 'difficulty' => 'intermediate'],
        ];

        foreach ($gestureData as $g) {
            $id = DB::table('gestures')->insertGetId([
                'name'         => $g['name'],
                'display_name' => $g['display_name'],
                'description'  => $g['description'],
                'image_url'    => null,
                'video_url'    => null,
                'model_file'   => null,
                'difficulty'   => $g['difficulty'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            $gestures[$g['name']] = $id;
        }

        // ==========================================
        // 5. LESSONS
        // ==========================================

        // --- Lesson 1: FSL Alphabet (Beginner) ---
        $lesson1Id = DB::table('lessons')->insertGetId([
            'teacher_id'  => $teacher->id,
            'title'       => 'FSL Alphabet: Letters A–E',
            'description' => 'Learn the Filipino Sign Language (FSL) handshapes for the first five letters of the alphabet. Practice each letter carefully and repeat until comfortable.',
            'lesson_type' => 'gesture',
            'difficulty'  => 'beginner',
            'module_order'=> 1,
            'status'      => 'published',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Lesson 1 Contents
        $lesson1Contents = [
            ['step' => 1, 'type' => 'text',         'title' => 'Introduction to FSL Alphabet',
             'text' => 'The Filipino Sign Language (FSL) alphabet uses specific handshapes to represent each letter. In this lesson, you will learn letters A, B, C, D, and E. Practice each sign slowly and carefully.'],
            ['step' => 2, 'type' => 'gesture_demo', 'title' => 'Letter A',
             'text' => 'Make a closed fist. Place your thumb beside your index finger, not over your fingers. This is the sign for Letter A.',
             'gesture' => 'letter_a'],
            ['step' => 3, 'type' => 'gesture_demo', 'title' => 'Letter B',
             'text' => 'Hold up all four fingers straight and together. Fold your thumb across the palm. This is the sign for Letter B.',
             'gesture' => 'letter_b'],
            ['step' => 4, 'type' => 'gesture_demo', 'title' => 'Letter C',
             'text' => 'Curve all your fingers and thumb to form the shape of the letter C. Keep your hand sideways.',
             'gesture' => 'letter_c'],
            ['step' => 5, 'type' => 'gesture_demo', 'title' => 'Letter D',
             'text' => 'Point your index finger upward while the other fingers touch your thumb to form a circle. This represents the letter D.',
             'gesture' => 'letter_d'],
            ['step' => 6, 'type' => 'gesture_demo', 'title' => 'Letter E',
             'text' => 'Bend all your fingers down toward the middle of your palm. Your fingers should be in a hooked position. This is the letter E.',
             'gesture' => 'letter_e'],
            ['step' => 7, 'type' => 'text',         'title' => 'Practice Time',
             'text' => 'Now try making each letter sign yourself: A, B, C, D, E. Practice in front of a mirror so you can see your own hand shapes clearly. When you are ready, take the quiz!'],
        ];

        foreach ($lesson1Contents as $c) {
            DB::table('lesson_contents')->insert([
                'lesson_id'    => $lesson1Id,
                'step_number'  => $c['step'],
                'content_type' => $c['type'],
                'title'        => $c['title'],
                'content_text' => $c['text'],
                'media_url'    => null,
                'gesture_name' => $c['gesture'] ?? null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // Lesson 1 Gestures mapping
        foreach (['letter_a', 'letter_b', 'letter_c', 'letter_d', 'letter_e'] as $g) {
            DB::table('lesson_gestures')->insert([
                'lesson_id'  => $lesson1Id,
                'gesture_id' => $gestures[$g],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // --- Lesson 2: Greetings in FSL (Beginner) ---
        $lesson2Id = DB::table('lessons')->insertGetId([
            'teacher_id'  => $teacher->id,
            'title'       => 'Basic FSL Greetings',
            'description' => 'Learn everyday greetings in Filipino Sign Language: Hello, Thank You, Please, Yes, and No.',
            'lesson_type' => 'gesture',
            'difficulty'  => 'beginner',
            'module_order'=> 2,
            'status'      => 'published',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Lesson 2 Contents
        $lesson2Contents = [
            ['step' => 1, 'type' => 'text',         'title' => 'Why Greetings Matter',
             'text' => 'Greetings are the first step to communication. In this lesson, you will learn five important FSL signs: Hello, Thank You, Please, Yes, and No. These are used every day!'],
            ['step' => 2, 'type' => 'gesture_demo', 'title' => 'Hello',
             'text' => 'Open your hand flat. Start with your fingers near your forehead and move your hand outward and downward. This is the sign for Hello.',
             'gesture' => 'hello'],
            ['step' => 3, 'type' => 'gesture_demo', 'title' => 'Thank You',
             'text' => 'Place your flat hand on your chin with fingers pointing upward. Move your hand forward and slightly downward. This means Thank You.',
             'gesture' => 'thank_you'],
            ['step' => 4, 'type' => 'gesture_demo', 'title' => 'Please',
             'text' => 'Place your flat hand on your chest and move it in a circular motion. This is the sign for Please.',
             'gesture' => 'please'],
            ['step' => 5, 'type' => 'gesture_demo', 'title' => 'Yes',
             'text' => 'Make a closed fist and move your wrist up and down like a nod. This means Yes.',
             'gesture' => 'yes'],
            ['step' => 6, 'type' => 'gesture_demo', 'title' => 'No',
             'text' => 'Extend your index finger and middle finger, then bring them down to meet your thumb twice. This means No.',
             'gesture' => 'no'],
            ['step' => 7, 'type' => 'text',         'title' => 'You Did It!',
             'text' => 'Great job learning FSL greetings! Practice these signs with a classmate or family member. Come back anytime to review. Take the quiz when you feel ready!'],
        ];

        foreach ($lesson2Contents as $c) {
            DB::table('lesson_contents')->insert([
                'lesson_id'    => $lesson2Id,
                'step_number'  => $c['step'],
                'content_type' => $c['type'],
                'title'        => $c['title'],
                'content_text' => $c['text'],
                'media_url'    => null,
                'gesture_name' => $c['gesture'] ?? null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // Lesson 2 Gestures mapping
        foreach (['hello', 'thank_you', 'please', 'yes', 'no'] as $g) {
            DB::table('lesson_gestures')->insert([
                'lesson_id'  => $lesson2Id,
                'gesture_id' => $gestures[$g],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // --- Lesson 3: School Words (Intermediate) ---
        $lesson3Id = DB::table('lessons')->insertGetId([
            'teacher_id'  => $teacher->id,
            'title'       => 'FSL School Vocabulary',
            'description' => 'Learn how to sign important school-related words including your Name and the word School in FSL.',
            'lesson_type' => 'interactive',
            'difficulty'  => 'intermediate',
            'module_order'=> 3,
            'status'      => 'published',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Lesson 3 Contents
        $lesson3Contents = [
            ['step' => 1, 'type' => 'text',         'title' => 'School Words in FSL',
             'text' => 'In this lesson, you will learn signs for two important school words: Name and School. These will help you communicate in a classroom setting using FSL.'],
            ['step' => 2, 'type' => 'gesture_demo', 'title' => 'Name',
             'text' => 'Form the letter H with both hands (index and middle finger extended horizontally). Tap one hand on top of the other twice. This is the sign for Name.',
             'gesture' => 'name'],
            ['step' => 3, 'type' => 'gesture_demo', 'title' => 'School',
             'text' => 'Hold both hands out slightly curved, palms facing each other. Clap them together twice gently. This is the sign for School.',
             'gesture' => 'school'],
            ['step' => 4, 'type' => 'text',         'title' => 'Practice',
             'text' => 'Try combining the signs you have learned: "Hello, my Name is ___. I go to School." Practice signing this full sentence. Then take the quiz!'],
        ];

        foreach ($lesson3Contents as $c) {
            DB::table('lesson_contents')->insert([
                'lesson_id'    => $lesson3Id,
                'step_number'  => $c['step'],
                'content_type' => $c['type'],
                'title'        => $c['title'],
                'content_text' => $c['text'],
                'media_url'    => null,
                'gesture_name' => $c['gesture'] ?? null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // Lesson 3 Gestures mapping
        foreach (['name', 'school'] as $g) {
            DB::table('lesson_gestures')->insert([
                'lesson_id'  => $lesson3Id,
                'gesture_id' => $gestures[$g],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ==========================================
        // 6. QUIZZES
        // ==========================================

        // --- Quiz 1: Alphabet A–E ---
        $quiz1Id = DB::table('quizzes')->insertGetId([
            'lesson_id'    => $lesson1Id,
            'title'        => 'Quiz: FSL Alphabet A–E',
            'description'  => 'Test your knowledge of the FSL handshapes for letters A through E.',
            'total_points' => 5,
            'passing_score'=> 60,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Quiz 1 Questions & Options
        $quiz1Questions = [
            [
                'number' => 1,
                'type'   => 'multiple_choice',
                'text'   => 'Which handshape represents the letter A in FSL?',
                'points' => 1,
                'options' => [
                    ['text' => 'Closed fist with thumb beside index finger', 'correct' => true],
                    ['text' => 'Four fingers pointing up, thumb across palm', 'correct' => false],
                    ['text' => 'Curved hand like letter C', 'correct' => false],
                    ['text' => 'Index finger pointing up', 'correct' => false],
                ],
            ],
            [
                'number' => 2,
                'type'   => 'multiple_choice',
                'text'   => 'For the letter B, your thumb should be:',
                'points' => 1,
                'options' => [
                    ['text' => 'Pointing up', 'correct' => false],
                    ['text' => 'Folded across the palm', 'correct' => true],
                    ['text' => 'Touching your chin', 'correct' => false],
                    ['text' => 'Extended to the side', 'correct' => false],
                ],
            ],
            [
                'number' => 3,
                'type'   => 'true_false',
                'text'   => 'The FSL sign for letter C is formed by curving all fingers and the thumb.',
                'points' => 1,
                'options' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],
            [
                'number' => 4,
                'type'   => 'multiple_choice',
                'text'   => 'In the FSL letter D, which finger points upward?',
                'points' => 1,
                'options' => [
                    ['text' => 'Pinky finger', 'correct' => false],
                    ['text' => 'Middle finger', 'correct' => false],
                    ['text' => 'Index finger', 'correct' => true],
                    ['text' => 'Ring finger', 'correct' => false],
                ],
            ],
            [
                'number' => 5,
                'type'   => 'true_false',
                'text'   => 'For FSL letter E, the fingers are stretched out straight.',
                'points' => 1,
                'options' => [
                    ['text' => 'True', 'correct' => false],
                    ['text' => 'False', 'correct' => true],
                ],
            ],
        ];

        foreach ($quiz1Questions as $q) {
            $qId = DB::table('quiz_questions')->insertGetId([
                'quiz_id'          => $quiz1Id,
                'question_number'  => $q['number'],
                'question_type'    => $q['type'],
                'question_text'    => $q['text'],
                'media_url'        => null,
                'gesture_required' => null,
                'points'           => $q['points'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            foreach ($q['options'] as $opt) {
                DB::table('quiz_options')->insert([
                    'question_id'      => $qId,
                    'option_text'      => $opt['text'],
                    'option_media_url' => null,
                    'is_correct'       => $opt['correct'] ? 1 : 0,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }

        // --- Quiz 2: Basic Greetings ---
        $quiz2Id = DB::table('quizzes')->insertGetId([
            'lesson_id'    => $lesson2Id,
            'title'        => 'Quiz: Basic FSL Greetings',
            'description'  => 'Test your knowledge of Hello, Thank You, Please, Yes, and No in FSL.',
            'total_points' => 5,
            'passing_score'=> 60,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $quiz2Questions = [
            [
                'number' => 1,
                'type'   => 'multiple_choice',
                'text'   => 'How do you sign "Hello" in FSL?',
                'points' => 1,
                'options' => [
                    ['text' => 'Open flat hand waved from the forehead outward', 'correct' => true],
                    ['text' => 'Closed fist nodding up and down', 'correct' => false],
                    ['text' => 'Circular motion on the chest', 'correct' => false],
                    ['text' => 'Flat hand on chin moving forward', 'correct' => false],
                ],
            ],
            [
                'number' => 2,
                'type'   => 'multiple_choice',
                'text'   => 'The sign for "Thank You" starts by placing your hand on your:',
                'points' => 1,
                'options' => [
                    ['text' => 'Forehead', 'correct' => false],
                    ['text' => 'Chin', 'correct' => true],
                    ['text' => 'Chest', 'correct' => false],
                    ['text' => 'Shoulder', 'correct' => false],
                ],
            ],
            [
                'number' => 3,
                'type'   => 'true_false',
                'text'   => 'The FSL sign for "Please" uses a circular motion on the chest.',
                'points' => 1,
                'options' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],
            [
                'number' => 4,
                'type'   => 'multiple_choice',
                'text'   => 'The sign for "Yes" looks like:',
                'points' => 1,
                'options' => [
                    ['text' => 'Open hand waving', 'correct' => false],
                    ['text' => 'A closed fist nodding up and down', 'correct' => true],
                    ['text' => 'Two fingers closing onto the thumb', 'correct' => false],
                    ['text' => 'Flat hand moving forward from chin', 'correct' => false],
                ],
            ],
            [
                'number' => 5,
                'type'   => 'true_false',
                'text'   => 'The sign for "No" in FSL is the same as the sign for "Yes".',
                'points' => 1,
                'options' => [
                    ['text' => 'True', 'correct' => false],
                    ['text' => 'False', 'correct' => true],
                ],
            ],
        ];

        foreach ($quiz2Questions as $q) {
            $qId = DB::table('quiz_questions')->insertGetId([
                'quiz_id'          => $quiz2Id,
                'question_number'  => $q['number'],
                'question_type'    => $q['type'],
                'question_text'    => $q['text'],
                'media_url'        => null,
                'gesture_required' => null,
                'points'           => $q['points'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            foreach ($q['options'] as $opt) {
                DB::table('quiz_options')->insert([
                    'question_id'      => $qId,
                    'option_text'      => $opt['text'],
                    'option_media_url' => null,
                    'is_correct'       => $opt['correct'] ? 1 : 0,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }

        // --- Quiz 3: School Vocabulary ---
        $quiz3Id = DB::table('quizzes')->insertGetId([
            'lesson_id'    => $lesson3Id,
            'title'        => 'Quiz: FSL School Words',
            'description'  => 'Test your understanding of the FSL signs for Name and School.',
            'total_points' => 3,
            'passing_score'=> 70,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $quiz3Questions = [
            [
                'number' => 1,
                'type'   => 'multiple_choice',
                'text'   => 'The FSL sign for "Name" uses which handshape?',
                'points' => 1,
                'options' => [
                    ['text' => 'Letter H handshape on both hands tapped together', 'correct' => true],
                    ['text' => 'Open palm waved sideways', 'correct' => false],
                    ['text' => 'Closed fist with thumb up', 'correct' => false],
                    ['text' => 'Curved hand like letter C', 'correct' => false],
                ],
            ],
            [
                'number' => 2,
                'type'   => 'multiple_choice',
                'text'   => 'How many times do you tap your hands together for the sign "School"?',
                'points' => 1,
                'options' => [
                    ['text' => 'Once', 'correct' => false],
                    ['text' => 'Twice', 'correct' => true],
                    ['text' => 'Three times', 'correct' => false],
                    ['text' => 'Four times', 'correct' => false],
                ],
            ],
            [
                'number' => 3,
                'type'   => 'true_false',
                'text'   => 'You can sign "Hello, my Name is ___. I go to School." using only the signs learned in this course.',
                'points' => 1,
                'options' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],
        ];

        foreach ($quiz3Questions as $q) {
            $qId = DB::table('quiz_questions')->insertGetId([
                'quiz_id'          => $quiz3Id,
                'question_number'  => $q['number'],
                'question_type'    => $q['type'],
                'question_text'    => $q['text'],
                'media_url'        => null,
                'gesture_required' => null,
                'points'           => $q['points'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            foreach ($q['options'] as $opt) {
                DB::table('quiz_options')->insert([
                    'question_id'      => $qId,
                    'option_text'      => $opt['text'],
                    'option_media_url' => null,
                    'is_correct'       => $opt['correct'] ? 1 : 0,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }

        // ==========================================
        // 7. STUDENT LESSON PROGRESS (for user 6 / student 5)
        // ==========================================

        // Carlo (student5) has completed Lesson 1 with a passing quiz score,
        // is in progress on Lesson 2, and has not started Lesson 3 yet.
        DB::table('student_lesson_progress')->insert([
            [
                'student_id'        => $student5->student_id,
                'lesson_id'         => $lesson1Id,
                'current_step'      => 7,
                'lesson_completed'  => 1,
                'quiz_completed'    => 1,
                'quiz_score'        => 4,
                'last_accessed_at'  => now()->subDays(2),
                'created_at'        => now()->subDays(5),
                'updated_at'        => now()->subDays(2),
            ],
            [
                'student_id'        => $student5->student_id,
                'lesson_id'         => $lesson2Id,
                'current_step'      => 4,
                'lesson_completed'  => 0,
                'quiz_completed'    => 0,
                'quiz_score'        => null,
                'last_accessed_at'  => now()->subDay(),
                'created_at'        => now()->subDays(3),
                'updated_at'        => now()->subDay(),
            ],
        ]);

        // Also give some other students progress on lesson 1 for realistic dashboard data
        DB::table('student_lesson_progress')->insert([
            [
                'student_id'       => $student1->student_id,
                'lesson_id'        => $lesson1Id,
                'current_step'     => 7,
                'lesson_completed' => 1,
                'quiz_completed'   => 1,
                'quiz_score'       => 5,
                'last_accessed_at' => now()->subDays(1),
                'created_at'       => now()->subDays(4),
                'updated_at'       => now()->subDays(1),
            ],
            [
                'student_id'       => $student2->student_id,
                'lesson_id'        => $lesson1Id,
                'current_step'     => 3,
                'lesson_completed' => 0,
                'quiz_completed'   => 0,
                'quiz_score'       => null,
                'last_accessed_at' => now()->subDays(2),
                'created_at'       => now()->subDays(3),
                'updated_at'       => now()->subDays(2),
            ],
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('School: Nasugbu West Central School (Batangas Province, Region IV-A)');
        $this->command->info('Teacher login: emmaruth@deped.gov.ph / password123');
        $this->command->info('Student logins: LRN + PIN (1234, 2345, 3456, 4567, 5678)');
        $this->command->info('');
        $this->command->info('📚 Lessons seeded: 3 lessons with gestures, quizzes, questions & options');
        $this->command->info('👤 User 6 = Carlo Bautista (carlobautista) — student with lesson progress');
    }
}