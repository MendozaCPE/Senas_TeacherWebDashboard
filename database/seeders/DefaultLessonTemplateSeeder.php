<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Services\LessonTemplateService;

class DefaultLessonTemplateSeeder extends Seeder
{
    public function run()
    {
        $systemTeacherId = app(LessonTemplateService::class)->templateTeacherId();

        // Remove any old template modules for system teacher to have a fresh clean state
        $existing = Module::where('teacher_id', $systemTeacherId)->where('is_template', true)->get();
        foreach ($existing as $m) {
            $lessonIds = $m->lessons()->pluck('lesson_id');
            foreach ($lessonIds as $lid) {
                LessonContent::where('lesson_id', $lid)->delete();
                $quiz = Quiz::where('lesson_id', $lid)->first();
                if ($quiz) {
                    $qids = QuizQuestion::where('quiz_id', $quiz->quiz_id)->pluck('question_id');
                    QuizOption::whereIn('question_id', $qids)->delete();
                    QuizQuestion::where('quiz_id', $quiz->quiz_id)->delete();
                    $quiz->delete();
                }
            }
            Lesson::where('module_id', $m->module_id)->delete();
            $m->delete();
        }

        $moduleDefs = [
            [
                'title'         => 'FSL Alphabet & Fingerspelling',
                'description'   => 'Mastering foundational handshapes and letter recognition in Filipino Sign Language.',
                'mastery_level' => 'beginner',
                'module_order'  => 1,
                'lessons'       => [
                    [
                        'title'        => 'Letters A through G',
                        'description'  => 'Introduction to individual fingerspelling handshapes from Letter A to G.',
                        'difficulty'   => 'beginner',
                        'lesson_type'  => 'interactive',
                        'module_order' => 1,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'Hand Orientation Basics', 'text' => 'Hold your dominant hand at shoulder height, palm facing outward toward your conversational partner.', 'type' => 'text'],
                            ['step' => 2, 'title' => 'Forming Letters A, B, and C', 'text' => 'For A, make a fist with thumb beside index. For B, four fingers upright with thumb tucked. For C, curve hand in a C-shape.', 'type' => 'text'],
                            ['step' => 3, 'title' => 'Forming Letters D, E, F, and G', 'text' => 'Follow the step-by-step finger articulation shown in the video reference.', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'Which finger is raised straight up when signing the letter D?', 'type' => 'multiple_choice', 'opts' => [['Thumb', false], ['Index finger', true], ['Pinky finger', false], ['Middle finger', false]]],
                            ['q' => 'True or False: Your palm should face inward toward your chest when fingerspelling.', 'type' => 'true_false', 'opts' => [['True', false], ['False', true]]],
                        ],
                    ],
                    [
                        'title'        => 'Letters H through N',
                        'description'  => 'Forming horizontal and multi-finger hand configurations for H, I, J, K, L, M, and N.',
                        'difficulty'   => 'beginner',
                        'lesson_type'  => 'interactive',
                        'module_order' => 2,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'Horizontal Signs: H and K', 'text' => 'Point index and middle finger sideways for H. For K, place thumb between index and middle.', 'type' => 'text'],
                            ['step' => 2, 'title' => 'The Iconic L and J Motion', 'text' => 'L forms a right angle with thumb and index. J traces the hook shape in the air with your pinky.', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'Which letter requires tracing a hook motion in the air using your pinky finger?', 'type' => 'multiple_choice', 'opts' => [['Letter I', false], ['Letter J', true], ['Letter L', false], ['Letter Y', false]]],
                        ],
                    ],
                    [
                        'title'        => 'Letters O through U',
                        'description'  => 'Curved and closed-palm gestures: O, P, Q, R, S, T, and U.',
                        'difficulty'   => 'beginner',
                        'lesson_type'  => 'interactive',
                        'module_order' => 3,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'Circular Shapes: O and P', 'text' => 'Form a clean circle with all fingertips meeting the thumb for O. For P, point the K-shape downwards.', 'type' => 'text'],
                            ['step' => 2, 'title' => 'Crossed Fingers: The Letter R', 'text' => 'Cross your middle finger over your index finger, symbolizing good luck.', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'How is the letter R formed in FSL fingerspelling?', 'type' => 'multiple_choice', 'opts' => [['Crossing index and middle fingers', true], ['Making a fist with thumb on top', false], ['Pointing two fingers downward', false], ['Opening all five fingers', false]]],
                        ],
                    ],
                    [
                        'title'        => 'Letters V through Z & Double Letters',
                        'description'  => 'Completing the alphabet and learning smooth double-letter bounce transitions.',
                        'difficulty'   => 'beginner',
                        'lesson_type'  => 'interactive',
                        'module_order' => 4,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'Victory and Three Fingers: V and W', 'text' => 'Spread index and middle finger for V. Add ring finger for W.', 'type' => 'text'],
                            ['step' => 2, 'title' => 'Tracing the Z', 'text' => 'Extend index finger and trace the letter Z in the air.', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'Which letter is drawn in the air with the index finger?', 'type' => 'multiple_choice', 'opts' => [['X', false], ['Y', false], ['Z', true], ['V', false]]],
                        ],
                    ],
                    [
                        'title'        => 'Fingerspelling Names and Simple Words',
                        'description'  => 'Fluid transitions, pacing, and spelling personal names clearly without bouncing.',
                        'difficulty'   => 'beginner',
                        'lesson_type'  => 'interactive',
                        'module_order' => 5,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'Rhythm and Smooth Transitions', 'text' => 'Spell at a consistent pace rather than rushing. Keep your elbow relaxed near your side.', 'type' => 'text'],
                            ['step' => 2, 'title' => 'Handling Double Letters', 'text' => 'Slide the repeated letter slightly outward without wildly shaking your hand.', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'What is the correct way to fingerspell a double letter (like "LL")?', 'type' => 'multiple_choice', 'opts' => [['Slide hand slightly outward to the side', true], ['Drop hand and raise it again', false], ['Switch to your other hand', false], ['Wave hand back and forth', false]]],
                        ],
                    ],
                ],
            ],
            [
                'title'         => 'Basic Greetings & Politeness Markers',
                'description'   => 'Daily social interactions, respectful salutations, and courteous responses in FSL.',
                'mastery_level' => 'beginner',
                'module_order'  => 2,
                'lessons'       => [
                    [
                        'title'        => 'Hello, Good Morning & Good Afternoon',
                        'description'  => 'Common greetings used throughout the school day and in the community.',
                        'difficulty'   => 'beginner',
                        'lesson_type'  => 'interactive',
                        'module_order' => 1,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'The "Good" Base Sign', 'text' => 'Place flat hand at your chin and bring it down into your non-dominant supporting palm.', 'type' => 'text'],
                            ['step' => 2, 'title' => 'Morning and Afternoon Time Signs', 'text' => 'Raise your arm like the rising sun for Morning. Lower your forearm horizontally for Afternoon.', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'Where does the hand start when signing "Good"?', 'type' => 'multiple_choice', 'opts' => [['Forehead', false], ['Chin / Mouth', true], ['Chest', false], ['Shoulder', false]]],
                        ],
                    ],
                    [
                        'title'        => 'Please, Thank You & You\'re Welcome',
                        'description'  => 'Expressions of courtesy and appreciation during classroom conversations.',
                        'difficulty'   => 'beginner',
                        'lesson_type'  => 'interactive',
                        'module_order' => 2,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'Signing "Thank You"', 'text' => 'Touch your fingertips to your chin/lips and extend your flat hand forward toward the person with a smile.', 'type' => 'text'],
                            ['step' => 2, 'title' => 'Signing "Please"', 'text' => 'Rub your flat open hand in a gentle circular motion on the center of your chest.', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'What motion is used to sign "Please"?', 'type' => 'multiple_choice', 'opts' => [['Circular rubbing motion on the chest', true], ['Tapping the chin twice', false], ['Clapping hands together', false], ['Waving index finger', false]]],
                        ],
                    ],
                    [
                        'title'        => 'How Are You & Expressing Feelings',
                        'description'  => 'Inquiring about well-being and sharing simple emotional states (happy, tired, fine).',
                        'difficulty'   => 'intermediate',
                        'lesson_type'  => 'interactive',
                        'module_order' => 3,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'Signing "How Are You?"', 'text' => 'Both curved hands twist outward from chest, accompanied by lowered eyebrows for a wh-question.', 'type' => 'text'],
                            ['step' => 2, 'title' => 'Emotions: Happy vs Sad', 'text' => 'Happy brushes upward on the chest with a cheerful face. Sad moves open hands downward with a solemn face.', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'What facial expression accompanies the question "How are you?" in FSL?', 'type' => 'multiple_choice', 'opts' => [['Lowered / furrowed eyebrows', true], ['Raised eyebrows with wide eyes', false], ['Looking away', false], ['Puffed cheeks', false]]],
                        ],
                    ],
                    [
                        'title'        => 'Introducing Family Members',
                        'description'  => 'Signs for Mother, Father, Brother, Sister, and Teacher.',
                        'difficulty'   => 'intermediate',
                        'lesson_type'  => 'interactive',
                        'module_order' => 4,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'Gender Placement Rule in FSL', 'text' => 'Female signs (Mother, Sister, Grandmother) originate near the chin/jaw. Male signs (Father, Brother, Grandfather) originate near the forehead/temple.', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'In FSL, where are female-related family signs typically located?', 'type' => 'multiple_choice', 'opts' => [['Lower face / Chin area', true], ['Upper face / Forehead area', false], ['Chest area', false], ['Shoulder area', false]]],
                        ],
                    ],
                ],
            ],
            [
                'title'         => 'Numbers, Counting & Calendar Signs',
                'description'   => 'Cardinal quantities, monetary denominations, days of the week, and telling time.',
                'mastery_level' => 'intermediate',
                'module_order'  => 3,
                'lessons'       => [
                    [
                        'title'        => 'Cardinal Numbers 1 to 20',
                        'description'  => 'Accurate palm orientation and finger positioning for numbers 1 through 20.',
                        'difficulty'   => 'beginner',
                        'lesson_type'  => 'interactive',
                        'module_order' => 1,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'Numbers 1–5: Palm Facing Inward', 'text' => 'Count with palm facing yourself for numbers 1 to 5 in Filipino Sign Language.', 'type' => 'text'],
                            ['step' => 2, 'title' => 'Numbers 6–10: Palm Facing Outward', 'text' => 'Turn palm outward for numbers 6 to 10.', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'For numbers 1 to 5, which way should your palm face in standard FSL counting?', 'type' => 'multiple_choice', 'opts' => [['Facing inward toward yourself', true], ['Facing outward toward listener', false], ['Facing the floor', false], ['Facing sideways', false]]],
                        ],
                    ],
                    [
                        'title'        => 'Days of the Week & Calendar Time',
                        'description'  => 'Signing Monday through Sunday, Yesterday, Today, and Tomorrow.',
                        'difficulty'   => 'intermediate',
                        'lesson_type'  => 'interactive',
                        'module_order' => 2,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'Letter Initials for Days', 'text' => 'Monday uses M handshape circling clockwise. Tuesday uses T, Wednesday uses W.', 'type' => 'text'],
                            ['step' => 2, 'title' => 'Timeline Orientation', 'text' => 'Behind your shoulder represents Past (Yesterday). In front represents Future (Tomorrow). Center represents Present (Today).', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'How is the future represented on the signing timeline?', 'type' => 'multiple_choice', 'opts' => [['Moving forward in front of the body', true], ['Moving backward behind the shoulder', false], ['Moving downward to the ground', false], ['Tapping the chest', false]]],
                        ],
                    ],
                    [
                        'title'        => 'Telling Time and Scheduling',
                        'description'  => 'Combining number signs with the "time" wrist indicator to indicate clock hours.',
                        'difficulty'   => 'intermediate',
                        'lesson_type'  => 'interactive',
                        'module_order' => 3,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'Tapping the Wrist', 'text' => 'Tap your index finger on your non-dominant wrist where a wristwatch sits, then sign the numeral.', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'What is the base location for signing "Time" in FSL?', 'type' => 'multiple_choice', 'opts' => [['The non-dominant wrist', true], ['The temple', false], ['The palm', false], ['The elbow', false]]],
                        ],
                    ],
                ],
            ],
            [
                'title'         => 'Conversational Dialogues & Cultural Nuances',
                'description'   => 'Natural sentence structures, questions, facial grammar, and Deaf culture etiquette.',
                'mastery_level' => 'advanced',
                'module_order'  => 4,
                'lessons'       => [
                    [
                        'title'        => 'School & Classroom Communication Signs',
                        'description'  => 'Vocabulary for assignments, exams, blackboard, pencils, and teacher instructions.',
                        'difficulty'   => 'intermediate',
                        'lesson_type'  => 'interactive',
                        'module_order' => 1,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'Classroom Directions', 'text' => 'Signs for "Listen/Look", "Read", "Write", "Homework", and "Quiet".', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'Which sign involves two fingers pointing toward your eyes and then toward the board?', 'type' => 'multiple_choice', 'opts' => [['Look / Pay attention', true], ['Write notes', false], ['Read silently', false], ['Leave the room', false]]],
                        ],
                    ],
                    [
                        'title'        => 'Non-Manual Signals & Facial Grammar',
                        'description'  => 'Mastering eyebrow positions, head tilts, and mouth morphemes that alter sentence meaning.',
                        'difficulty'   => 'advanced',
                        'lesson_type'  => 'interactive',
                        'module_order' => 2,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'Yes/No Questions vs Wh- Questions', 'text' => 'Raise eyebrows and tilt head forward for Yes/No questions. Furrow eyebrows for Wh- questions.', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'How do you signal a Yes/No question in Filipino Sign Language?', 'type' => 'multiple_choice', 'opts' => [['Raised eyebrows and slight forward head tilt', true], ['Furrowed eyebrows and looking away', false], ['Covering mouth with hand', false], ['Crossing arms', false]]],
                        ],
                    ],
                    [
                        'title'        => 'Deaf Culture Etiquette & Community Norms',
                        'description'  => 'Gaining attention respectfully, walk-through etiquette, and visual environment awareness.',
                        'difficulty'   => 'advanced',
                        'lesson_type'  => 'interactive',
                        'module_order' => 3,
                        'status'       => 'published',
                        'steps'        => [
                            ['step' => 1, 'title' => 'Respectful Attention Getting', 'text' => 'Gently tap the shoulder, wave hand in visual field, or flicker room lights. Never throw objects or shout.', 'type' => 'text'],
                        ],
                        'quiz_questions' => [
                            ['q' => 'Which is an acceptable method to get a Deaf person\'s attention in a room?', 'type' => 'multiple_choice', 'opts' => [['A gentle tap on the shoulder or small wave', true], ['Shouting their name loudly', false], ['Stomping aggressively', false], ['Pushing their arm', false]]],
                        ],
                    ],
                ],
            ],
        ];

        DB::transaction(function () use ($systemTeacherId, $moduleDefs) {
            foreach ($moduleDefs as $mDef) {
                $module = Module::create([
                    'teacher_id'         => $systemTeacherId,
                    'title'              => $mDef['title'],
                    'description'        => $mDef['description'],
                    'mastery_level'      => $mDef['mastery_level'],
                    'module_order'       => $mDef['module_order'],
                    'status'             => 'published',
                    'is_template'        => true,
                    'source_template_id' => null,
                ]);

                foreach ($mDef['lessons'] as $lDef) {
                    $lesson = Lesson::create([
                        'teacher_id'         => $systemTeacherId,
                        'module_id'          => $module->module_id,
                        'title'              => $lDef['title'],
                        'description'        => $lDef['description'],
                        'lesson_type'        => $lDef['lesson_type'],
                        'difficulty'         => $lDef['difficulty'],
                        'module_order'       => $lDef['module_order'],
                        'status'             => $lDef['status'],
                        'published_at'       => now(),
                        'is_template'        => true,
                        'source_template_id' => null,
                    ]);

                    foreach ($lDef['steps'] as $sDef) {
                        LessonContent::create([
                            'lesson_id'     => $lesson->lesson_id,
                            'step_number'   => $sDef['step'],
                            'content_type'  => $sDef['type'],
                            'title'         => $sDef['title'],
                            'content_text'  => $sDef['text'],
                            'media_url'     => null,
                            'gesture_name'  => null,
                            'media_missing' => 0,
                        ]);
                    }

                    if (!empty($lDef['quiz_questions'])) {
                        $quiz = Quiz::create([
                            'lesson_id'     => $lesson->lesson_id,
                            'title'         => $lesson->title . ' Mastery Check',
                            'description'   => 'Quick assessment on ' . $lesson->title,
                            'total_points'  => count($lDef['quiz_questions']) * 5,
                            'passing_score' => ceil(count($lDef['quiz_questions']) * 5 * 0.7),
                        ]);

                        foreach ($lDef['quiz_questions'] as $qIdx => $qDef) {
                            $question = QuizQuestion::create([
                                'quiz_id'          => $quiz->quiz_id,
                                'question_number'  => $qIdx + 1,
                                'question_type'    => $qDef['type'],
                                'question_text'    => $qDef['q'],
                                'media_url'        => null,
                                'drag_drop_pairs'  => null,
                                'gesture_data'     => null,
                                'gesture_required' => false,
                                'points'           => 5,
                            ]);

                            foreach ($qDef['opts'] as $opt) {
                                QuizOption::create([
                                    'question_id'      => $question->question_id,
                                    'option_text'      => $opt[0],
                                    'option_media_url' => null,
                                    'is_correct'       => $opt[1],
                                ]);
                            }
                        }
                    }
                }
            }
        });
    }
}
