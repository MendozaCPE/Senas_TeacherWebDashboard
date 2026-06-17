-- =======================================================
-- Senas Teacher Dashboard — Dummy Data Patch
-- Run this ONLY if the DB already has users 1–5 (from the
-- original seed / senas_db.sql) and you want to ADD:
--   • User 6  → Carlo Bautista (student)
--   • Gestures (FSL Alphabet + Basic Words)
--   • 3 Lessons with lesson_contents & lesson_gestures
--   • 3 Quizzes with quiz_questions & quiz_options
--   • student_lesson_progress for user 6 (student_id 5)
--     and some progress for students 1 & 2
-- =======================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------
-- USER 6 — Carlo Bautista (student)
-- -------------------------------------------------------
INSERT INTO `users`
  (`id`, `username`, `name`, `email`, `password`, `role`, `status`, `google_id`, `created_at`, `updated_at`)
VALUES
  (6, 'carlobautista', 'Carlo Bautista', NULL, NULL, 'student', 'active', NULL, NOW(), NOW());

-- student record (student_id = 5)
INSERT INTO `students`
  (`student_id`, `user_id`, `teacher_id`, `school_id`, `lrn`, `pin`,
   `first_name`, `last_name`, `age`, `grade_level`, `section`, `program_type`, `created_at`, `updated_at`)
VALUES
  (5, 6, 1, 1, '567890123456', '5678',
   'Carlo', 'Bautista', 10, 'Grade 4', 'Sampaguita', 'Regular', NOW(), NOW());

-- -------------------------------------------------------
-- GESTURES
-- -------------------------------------------------------
INSERT INTO `gestures`
  (`gesture_id`, `name`, `display_name`, `description`, `image_url`, `video_url`, `model_file`, `difficulty`, `created_at`, `updated_at`)
VALUES
  (1,  'letter_a',  'Letter A',   'Closed fist with thumb resting beside the index finger.',      NULL, NULL, NULL, 'beginner',     NOW(), NOW()),
  (2,  'letter_b',  'Letter B',   'Four fingers straight up, thumb folded across palm.',           NULL, NULL, NULL, 'beginner',     NOW(), NOW()),
  (3,  'letter_c',  'Letter C',   'Curved hand forming the shape of letter C.',                    NULL, NULL, NULL, 'beginner',     NOW(), NOW()),
  (4,  'letter_d',  'Letter D',   'Index finger points up, other fingers touch thumb.',            NULL, NULL, NULL, 'beginner',     NOW(), NOW()),
  (5,  'letter_e',  'Letter E',   'All fingers bent down to the middle of the palm.',              NULL, NULL, NULL, 'beginner',     NOW(), NOW()),
  (6,  'hello',     'Hello',      'Open hand wave starting from the forehead.',                    NULL, NULL, NULL, 'beginner',     NOW(), NOW()),
  (7,  'thank_you', 'Thank You',  'Flat hand touching chin, then moving forward.',                 NULL, NULL, NULL, 'beginner',     NOW(), NOW()),
  (8,  'please',    'Please',     'Circular motion of flat hand on chest.',                        NULL, NULL, NULL, 'beginner',     NOW(), NOW()),
  (9,  'yes',       'Yes',        'Closed fist nodding up and down like a head nod.',              NULL, NULL, NULL, 'beginner',     NOW(), NOW()),
  (10, 'no',        'No',         'Index and middle finger closing onto thumb.',                   NULL, NULL, NULL, 'beginner',     NOW(), NOW()),
  (11, 'name',      'Name',       'Two H handshapes tapping together twice.',                      NULL, NULL, NULL, 'intermediate', NOW(), NOW()),
  (12, 'school',    'School',     'Both hands clap twice with slight curve.',                      NULL, NULL, NULL, 'intermediate', NOW(), NOW());

-- -------------------------------------------------------
-- LESSONS
-- -------------------------------------------------------
INSERT INTO `lessons`
  (`lesson_id`, `teacher_id`, `title`, `description`, `lesson_type`, `difficulty`, `module_order`, `status`, `created_at`, `updated_at`)
VALUES
  (1, 1, 'FSL Alphabet: Letters A–E',
   'Learn the Filipino Sign Language (FSL) handshapes for the first five letters of the alphabet. Practice each letter carefully and repeat until comfortable.',
   'gesture', 'beginner', 1, 'published', NOW(), NOW()),

  (2, 1, 'Basic FSL Greetings',
   'Learn everyday greetings in Filipino Sign Language: Hello, Thank You, Please, Yes, and No.',
   'gesture', 'beginner', 2, 'published', NOW(), NOW()),

  (3, 1, 'FSL School Vocabulary',
   'Learn how to sign important school-related words including your Name and the word School in FSL.',
   'interactive', 'intermediate', 3, 'published', NOW(), NOW());

-- -------------------------------------------------------
-- LESSON CONTENTS
-- -------------------------------------------------------

-- Lesson 1: FSL Alphabet A–E
INSERT INTO `lesson_contents`
  (`lesson_id`, `step_number`, `content_type`, `title`, `content_text`, `media_url`, `gesture_name`, `created_at`, `updated_at`)
VALUES
  (1, 1, 'text',         'Introduction to FSL Alphabet',
   'The Filipino Sign Language (FSL) alphabet uses specific handshapes to represent each letter. In this lesson, you will learn letters A, B, C, D, and E. Practice each sign slowly and carefully.',
   NULL, NULL, NOW(), NOW()),
  (1, 2, 'gesture_demo', 'Letter A',
   'Make a closed fist. Place your thumb beside your index finger, not over your fingers. This is the sign for Letter A.',
   NULL, 'letter_a', NOW(), NOW()),
  (1, 3, 'gesture_demo', 'Letter B',
   'Hold up all four fingers straight and together. Fold your thumb across the palm. This is the sign for Letter B.',
   NULL, 'letter_b', NOW(), NOW()),
  (1, 4, 'gesture_demo', 'Letter C',
   'Curve all your fingers and thumb to form the shape of the letter C. Keep your hand sideways.',
   NULL, 'letter_c', NOW(), NOW()),
  (1, 5, 'gesture_demo', 'Letter D',
   'Point your index finger upward while the other fingers touch your thumb to form a circle. This represents the letter D.',
   NULL, 'letter_d', NOW(), NOW()),
  (1, 6, 'gesture_demo', 'Letter E',
   'Bend all your fingers down toward the middle of your palm. Your fingers should be in a hooked position. This is the letter E.',
   NULL, 'letter_e', NOW(), NOW()),
  (1, 7, 'text',         'Practice Time',
   'Now try making each letter sign yourself: A, B, C, D, E. Practice in front of a mirror so you can see your own hand shapes clearly. When you are ready, take the quiz!',
   NULL, NULL, NOW(), NOW());

-- Lesson 2: Basic FSL Greetings
INSERT INTO `lesson_contents`
  (`lesson_id`, `step_number`, `content_type`, `title`, `content_text`, `media_url`, `gesture_name`, `created_at`, `updated_at`)
VALUES
  (2, 1, 'text',         'Why Greetings Matter',
   'Greetings are the first step to communication. In this lesson, you will learn five important FSL signs: Hello, Thank You, Please, Yes, and No. These are used every day!',
   NULL, NULL, NOW(), NOW()),
  (2, 2, 'gesture_demo', 'Hello',
   'Open your hand flat. Start with your fingers near your forehead and move your hand outward and downward. This is the sign for Hello.',
   NULL, 'hello', NOW(), NOW()),
  (2, 3, 'gesture_demo', 'Thank You',
   'Place your flat hand on your chin with fingers pointing upward. Move your hand forward and slightly downward. This means Thank You.',
   NULL, 'thank_you', NOW(), NOW()),
  (2, 4, 'gesture_demo', 'Please',
   'Place your flat hand on your chest and move it in a circular motion. This is the sign for Please.',
   NULL, 'please', NOW(), NOW()),
  (2, 5, 'gesture_demo', 'Yes',
   'Make a closed fist and move your wrist up and down like a nod. This means Yes.',
   NULL, 'yes', NOW(), NOW()),
  (2, 6, 'gesture_demo', 'No',
   'Extend your index finger and middle finger, then bring them down to meet your thumb twice. This means No.',
   NULL, 'no', NOW(), NOW()),
  (2, 7, 'text',         'You Did It!',
   'Great job learning FSL greetings! Practice these signs with a classmate or family member. Come back anytime to review. Take the quiz when you feel ready!',
   NULL, NULL, NOW(), NOW());

-- Lesson 3: School Vocabulary
INSERT INTO `lesson_contents`
  (`lesson_id`, `step_number`, `content_type`, `title`, `content_text`, `media_url`, `gesture_name`, `created_at`, `updated_at`)
VALUES
  (3, 1, 'text',         'School Words in FSL',
   'In this lesson, you will learn signs for two important school words: Name and School. These will help you communicate in a classroom setting using FSL.',
   NULL, NULL, NOW(), NOW()),
  (3, 2, 'gesture_demo', 'Name',
   'Form the letter H with both hands (index and middle finger extended horizontally). Tap one hand on top of the other twice. This is the sign for Name.',
   NULL, 'name', NOW(), NOW()),
  (3, 3, 'gesture_demo', 'School',
   'Hold both hands out slightly curved, palms facing each other. Clap them together twice gently. This is the sign for School.',
   NULL, 'school', NOW(), NOW()),
  (3, 4, 'text',         'Practice',
   'Try combining the signs you have learned: "Hello, my Name is ___. I go to School." Practice signing this full sentence. Then take the quiz!',
   NULL, NULL, NOW(), NOW());

-- -------------------------------------------------------
-- LESSON GESTURES (pivot)
-- -------------------------------------------------------
INSERT INTO `lesson_gestures` (`lesson_id`, `gesture_id`, `created_at`, `updated_at`) VALUES
  (1, 1, NOW(), NOW()),
  (1, 2, NOW(), NOW()),
  (1, 3, NOW(), NOW()),
  (1, 4, NOW(), NOW()),
  (1, 5, NOW(), NOW()),
  (2, 6, NOW(), NOW()),
  (2, 7, NOW(), NOW()),
  (2, 8, NOW(), NOW()),
  (2, 9, NOW(), NOW()),
  (2,10, NOW(), NOW()),
  (3,11, NOW(), NOW()),
  (3,12, NOW(), NOW());

-- -------------------------------------------------------
-- QUIZZES
-- -------------------------------------------------------
INSERT INTO `quizzes`
  (`quiz_id`, `lesson_id`, `title`, `description`, `total_points`, `passing_score`, `created_at`, `updated_at`)
VALUES
  (1, 1, 'Quiz: FSL Alphabet A–E',
   'Test your knowledge of the FSL handshapes for letters A through E.',
   5, 60, NOW(), NOW()),
  (2, 2, 'Quiz: Basic FSL Greetings',
   'Test your knowledge of Hello, Thank You, Please, Yes, and No in FSL.',
   5, 60, NOW(), NOW()),
  (3, 3, 'Quiz: FSL School Words',
   'Test your understanding of the FSL signs for Name and School.',
   3, 70, NOW(), NOW());

-- -------------------------------------------------------
-- QUIZ QUESTIONS
-- -------------------------------------------------------
INSERT INTO `quiz_questions`
  (`question_id`, `quiz_id`, `question_number`, `question_type`, `question_text`, `media_url`, `gesture_required`, `points`, `created_at`, `updated_at`)
VALUES
  -- Quiz 1
  (1,  1, 1, 'multiple_choice',  'Which handshape represents the letter A in FSL?', NULL, NULL, 1, NOW(), NOW()),
  (2,  1, 2, 'multiple_choice',  'For the letter B, your thumb should be:', NULL, NULL, 1, NOW(), NOW()),
  (3,  1, 3, 'true_false',       'The FSL sign for letter C is formed by curving all fingers and the thumb.', NULL, NULL, 1, NOW(), NOW()),
  (4,  1, 4, 'multiple_choice',  'In the FSL letter D, which finger points upward?', NULL, NULL, 1, NOW(), NOW()),
  (5,  1, 5, 'true_false',       'For FSL letter E, the fingers are stretched out straight.', NULL, NULL, 1, NOW(), NOW()),
  -- Quiz 2
  (6,  2, 1, 'multiple_choice',  'How do you sign "Hello" in FSL?', NULL, NULL, 1, NOW(), NOW()),
  (7,  2, 2, 'multiple_choice',  'The sign for "Thank You" starts by placing your hand on your:', NULL, NULL, 1, NOW(), NOW()),
  (8,  2, 3, 'true_false',       'The FSL sign for "Please" uses a circular motion on the chest.', NULL, NULL, 1, NOW(), NOW()),
  (9,  2, 4, 'multiple_choice',  'The sign for "Yes" looks like:', NULL, NULL, 1, NOW(), NOW()),
  (10, 2, 5, 'true_false',       'The sign for "No" in FSL is the same as the sign for "Yes".', NULL, NULL, 1, NOW(), NOW()),
  -- Quiz 3
  (11, 3, 1, 'multiple_choice',  'The FSL sign for "Name" uses which handshape?', NULL, NULL, 1, NOW(), NOW()),
  (12, 3, 2, 'multiple_choice',  'How many times do you tap your hands together for the sign "School"?', NULL, NULL, 1, NOW(), NOW()),
  (13, 3, 3, 'true_false',       'You can sign "Hello, my Name is ___. I go to School." using only the signs learned in this course.', NULL, NULL, 1, NOW(), NOW());

-- -------------------------------------------------------
-- QUIZ OPTIONS
-- -------------------------------------------------------
INSERT INTO `quiz_options`
  (`option_id`, `question_id`, `option_text`, `option_media_url`, `is_correct`, `created_at`, `updated_at`)
VALUES
  -- Q1 (letter A)
  (1,  1, 'Closed fist with thumb beside index finger',      NULL, 1, NOW(), NOW()),
  (2,  1, 'Four fingers pointing up, thumb across palm',     NULL, 0, NOW(), NOW()),
  (3,  1, 'Curved hand like letter C',                       NULL, 0, NOW(), NOW()),
  (4,  1, 'Index finger pointing up',                        NULL, 0, NOW(), NOW()),
  -- Q2 (letter B thumb)
  (5,  2, 'Pointing up',                                     NULL, 0, NOW(), NOW()),
  (6,  2, 'Folded across the palm',                          NULL, 1, NOW(), NOW()),
  (7,  2, 'Touching your chin',                              NULL, 0, NOW(), NOW()),
  (8,  2, 'Extended to the side',                            NULL, 0, NOW(), NOW()),
  -- Q3 (letter C true/false)
  (9,  3, 'True',                                            NULL, 1, NOW(), NOW()),
  (10, 3, 'False',                                           NULL, 0, NOW(), NOW()),
  -- Q4 (letter D finger)
  (11, 4, 'Pinky finger',                                    NULL, 0, NOW(), NOW()),
  (12, 4, 'Middle finger',                                   NULL, 0, NOW(), NOW()),
  (13, 4, 'Index finger',                                    NULL, 1, NOW(), NOW()),
  (14, 4, 'Ring finger',                                     NULL, 0, NOW(), NOW()),
  -- Q5 (letter E true/false)
  (15, 5, 'True',                                            NULL, 0, NOW(), NOW()),
  (16, 5, 'False',                                           NULL, 1, NOW(), NOW()),
  -- Q6 (Hello)
  (17, 6, 'Open flat hand waved from the forehead outward',  NULL, 1, NOW(), NOW()),
  (18, 6, 'Closed fist nodding up and down',                 NULL, 0, NOW(), NOW()),
  (19, 6, 'Circular motion on the chest',                    NULL, 0, NOW(), NOW()),
  (20, 6, 'Flat hand on chin moving forward',                NULL, 0, NOW(), NOW()),
  -- Q7 (Thank You placement)
  (21, 7, 'Forehead',                                        NULL, 0, NOW(), NOW()),
  (22, 7, 'Chin',                                            NULL, 1, NOW(), NOW()),
  (23, 7, 'Chest',                                           NULL, 0, NOW(), NOW()),
  (24, 7, 'Shoulder',                                        NULL, 0, NOW(), NOW()),
  -- Q8 (Please true/false)
  (25, 8, 'True',                                            NULL, 1, NOW(), NOW()),
  (26, 8, 'False',                                           NULL, 0, NOW(), NOW()),
  -- Q9 (Yes)
  (27, 9, 'Open hand waving',                                NULL, 0, NOW(), NOW()),
  (28, 9, 'A closed fist nodding up and down',               NULL, 1, NOW(), NOW()),
  (29, 9, 'Two fingers closing onto the thumb',              NULL, 0, NOW(), NOW()),
  (30, 9, 'Flat hand moving forward from chin',              NULL, 0, NOW(), NOW()),
  -- Q10 (No true/false)
  (31,10, 'True',                                            NULL, 0, NOW(), NOW()),
  (32,10, 'False',                                           NULL, 1, NOW(), NOW()),
  -- Q11 (Name handshape)
  (33,11, 'Letter H handshape on both hands tapped together', NULL, 1, NOW(), NOW()),
  (34,11, 'Open palm waved sideways',                        NULL, 0, NOW(), NOW()),
  (35,11, 'Closed fist with thumb up',                       NULL, 0, NOW(), NOW()),
  (36,11, 'Curved hand like letter C',                       NULL, 0, NOW(), NOW()),
  -- Q12 (School taps)
  (37,12, 'Once',                                            NULL, 0, NOW(), NOW()),
  (38,12, 'Twice',                                           NULL, 1, NOW(), NOW()),
  (39,12, 'Three times',                                     NULL, 0, NOW(), NOW()),
  (40,12, 'Four times',                                      NULL, 0, NOW(), NOW()),
  -- Q13 (School sentence true/false)
  (41,13, 'True',                                            NULL, 1, NOW(), NOW()),
  (42,13, 'False',                                           NULL, 0, NOW(), NOW());

-- -------------------------------------------------------
-- STUDENT LESSON PROGRESS
-- -------------------------------------------------------
-- Student 5 (Carlo / user 6): completed lesson 1, in-progress on lesson 2
INSERT INTO `student_lesson_progress`
  (`student_id`, `lesson_id`, `current_step`, `lesson_completed`, `quiz_completed`, `quiz_score`, `last_accessed_at`, `created_at`, `updated_at`)
VALUES
  (5, 1, 7, 1, 1, 4, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
  (5, 2, 4, 0, 0, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Student 1 (Juan): completed lesson 1 with perfect score
INSERT INTO `student_lesson_progress`
  (`student_id`, `lesson_id`, `current_step`, `lesson_completed`, `quiz_completed`, `quiz_score`, `last_accessed_at`, `created_at`, `updated_at`)
VALUES
  (1, 1, 7, 1, 1, 5, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Student 2 (Maria): in-progress on lesson 1
INSERT INTO `student_lesson_progress`
  (`student_id`, `lesson_id`, `current_step`, `lesson_completed`, `quiz_completed`, `quiz_score`, `last_accessed_at`, `created_at`, `updated_at`)
VALUES
  (2, 1, 3, 0, 0, NULL, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY));

SET FOREIGN_KEY_CHECKS = 1;

-- Done! 
-- Summary:
--   users:                   +1  (id=6, Carlo Bautista)
--   students:                +1  (id=5, user_id=6)
--   gestures:                +12 (FSL letters A-E, Hello, Thank You, Please, Yes, No, Name, School)
--   lessons:                 +3  (published)
--   lesson_contents:         +18
--   lesson_gestures:         +12
--   quizzes:                 +3
--   quiz_questions:          +13
--   quiz_options:            +42
--   student_lesson_progress: +4  (2 for Carlo, 1 for Juan, 1 for Maria)
