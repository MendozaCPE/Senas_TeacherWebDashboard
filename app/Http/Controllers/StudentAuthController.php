<?php

namespace App\Http\Controllers;

use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\Student;
use App\Models\User;
use App\Services\XPService;  
use App\Models\Gesture;
use App\Models\GestureModule;
use App\Models\GesturePerformance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StudentAuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lrn' => 'required|string|size:12',
            'pin' => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid input',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Find student by LRN
        $student = Student::where('lrn', $request->lrn)->first();

        if (! $student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        // Verify PIN
        if ($student->pin !== $request->pin) {
            return response()->json(['message' => 'Invalid PIN'], 401);
        }

        // Get user data
        $user = User::find($student->user_id);

        if (! $user) {
            return response()->json(['message' => 'User account not found'], 404);
        }

        // Create token for mobile app
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'role' => $user->role,
                'student' => [
                    'id' => $student->student_id,
                    'lrn' => $student->lrn,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'program_type' => $student->program_type,
                    'grade_level' => $student->grade_level,
                    'section' => $student->section,
                    'fsl_mastery_level' => $student->fsl_mastery_level,
                ],
            ],
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        return response()->json([
            'user' => $user,
            'student' => [
                'id' => $student->student_id,
                'lrn' => $student->lrn,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'program_type' => $student->program_type,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'fsl_mastery_level' => $student->fsl_mastery_level,
            ],
        ]);
    }

    public function updateLevel(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (! $student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'fsl_mastery_level' => 'required|string|in:Beginner,Intermediate,Advanced',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid level'], 422);
        }

        $student->fsl_mastery_level = $request->fsl_mastery_level;
        $student->save();

        return response()->json([
            'message' => 'Level updated successfully',
            'fsl_mastery_level' => $student->fsl_mastery_level,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function saveLearningPath(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (! $student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'fsl_level' => 'required|string|in:Beginner,Intermediate,Advanced',
            'learning_goal' => 'required|string|in:Alphabet_Numbers,Greetings,Classroom_Words,Everything',
            'practice_time' => 'required|string|in:5_10_min,15_20_min,30_min,1_hour_plus',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 422);
        }

        $learningPath = LearningPath::updateOrCreate(
            ['student_id' => $student->student_id],
            [
                'fsl_level' => $request->fsl_level,
                'learning_goal' => $request->learning_goal,
                'practice_time' => $request->practice_time,
                'is_completed' => true,
                'completed_at' => now(),
            ]
        );

        $student->fsl_mastery_level = $request->fsl_level;
        $student->save();

        return response()->json([
            'message' => 'Learning path saved successfully',
            'learning_path' => $learningPath,
        ]);
    }

    public function getLearningPath(Request $request)
    {
        try {
            $user = $request->user();
            $student = Student::where('user_id', $user->id)->first();

            if (! $student) {
                return response()->json(['message' => 'Student not found'], 404);
            }

            $learningPath = LearningPath::where('student_id', $student->student_id)->first();

            return response()->json([
                'learning_path' => $learningPath,
                'student_level' => $student->fsl_mastery_level,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching learning path',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
public function getLessons(Request $request)
{
    try {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // 🔥 SYNC: Check and fix any inconsistent lock statuses
        $this->syncLessonLocks($student);

        // Get all lesson assignments for THIS student only
        $assignments = LessonAssignment::where('student_id', $student->student_id)
            ->with(['lesson' => function ($query) {
                $query->where('status', 'published')
                      ->with(['contents', 'quiz', 'module']);
            }])
            ->get();

        // Group lessons by module
        $modulesMap = [];
        
        foreach ($assignments as $assignment) {
            $lesson = $assignment->lesson;
            
            if (!$lesson) {
                continue;
            }
            
            $module = $lesson->module;
            $moduleId = $module ? $module->module_id : null;
            $moduleTitle = $module ? $module->title : 'General Lessons';
            $moduleDescription = $module ? $module->description : 'Default module for lessons';
            
            if (!isset($modulesMap[$moduleId])) {
                $modulesMap[$moduleId] = [
                    'module_id' => $moduleId,
                    'title' => $moduleTitle,
                    'description' => $moduleDescription,
                    'lessons' => []
                ];
            }
            
            // 🔥 Get the HIGHEST score from ALL quiz attempts for this lesson
            $highestScore = DB::table('quiz_attempts as qa')
                ->join('quizzes as q', 'qa.quiz_id', '=', 'q.quiz_id')
                ->where('qa.student_id', $student->student_id)
                ->where('q.lesson_id', $lesson->lesson_id)
                ->where('qa.status', 'completed')
                ->max('qa.percentage');
            
            // If no completed attempts, check student_lesson_progress as backup
            if ($highestScore === null) {
                $progress = DB::table('student_lesson_progress')
                    ->where('student_id', $student->student_id)
                    ->where('lesson_id', $lesson->lesson_id)
                    ->first();
                $highestScore = $progress ? $progress->quiz_score : null;
            }
            
            // 🔥 Determine status based on highest score
            $hasPassed = $highestScore !== null && $highestScore >= 60;
            $hasFailed = $highestScore !== null && $highestScore < 60;
            
            // Determine status
            $status = $assignment->status;
            if ($hasPassed) {
                $status = 'completed';
            } elseif ($hasFailed) {
                $status = 'failed';
            }
            
            $modulesMap[$moduleId]['lessons'][] = [
                'assignment_id' => $assignment->id,
                'lesson_id' => $lesson->lesson_id,
                'title' => $lesson->title,
                'description' => $lesson->description,
                'lesson_type' => $lesson->lesson_type,
                'difficulty' => $lesson->difficulty,
                'status' => $status,
                'score' => $highestScore, // 🔥 Send the highest score
                'is_locked' => (bool) $assignment->is_locked,
                'assigned_at' => $assignment->assigned_at,
                'module_order' => $lesson->module_order ?? 0,
                'total_steps' => $lesson->contents->count() + ($lesson->quiz ? 1 : 0),
                'has_quiz' => $lesson->quiz ? true : false,
            ];
        }
        
        // Sort lessons within each module by module_order
        foreach ($modulesMap as &$module) {
            usort($module['lessons'], function($a, $b) {
                return ($a['module_order'] ?? 0) - ($b['module_order'] ?? 0);
            });
        }
        
        // Sort modules by module_id (ASCENDING)
        ksort($modulesMap);
        
        // Convert map to array
        $modules = array_values($modulesMap);

        $xpService = new XPService();

        return response
        ()->json([
            'success' => true,
            'modules' => $modules,
            'student' => [
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'fsl_mastery_level' => $student->fsl_mastery_level,
                'total_xp' => $student->total_xp ?? 0,
                'level' => $student->level ?? 1,
                'streak_days' => $student->streak_days ?? 0,
                'next_level_xp' => $xpService->getNextLevelXp($student),
                'level_progress' => $xpService->getLevelProgress($student),
                'level_name' => $xpService->getLevelName($student->level ?? 1),
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Get all lessons as a flat list (for dashboard)
 * This returns lessons directly without module grouping
 */
public function getAllLessons(Request $request)
{
    try {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Get all lesson assignments for this student
        $assignments = LessonAssignment::where('student_id', $student->student_id)
            ->with(['lesson' => function ($query) {
                $query->where('status', 'published')
                      ->with(['contents', 'quiz', 'module']);
            }])
            ->orderBy('assigned_at', 'desc')
            ->get();

        $lessons = $assignments->map(function ($assignment) {
            $lesson = $assignment->lesson;

            if (!$lesson) {
                return null;
            }

            // 🔥 Get the HIGHEST score from ALL quiz attempts for this lesson
            $highestScore = DB::table('quiz_attempts as qa')
                ->join('quizzes as q', 'qa.quiz_id', '=', 'q.quiz_id')
                ->where('qa.student_id', $assignment->student_id)
                ->where('q.lesson_id', $lesson->lesson_id)
                ->where('qa.status', 'completed')
                ->max('qa.percentage');

            // If no completed attempts, check student_lesson_progress as backup
            if ($highestScore === null) {
                $progress = DB::table('student_lesson_progress')
                    ->where('student_id', $assignment->student_id)
                    ->where('lesson_id', $lesson->lesson_id)
                    ->first();
                $highestScore = $progress ? $progress->quiz_score : null;
            }

            $progress = DB::table('student_lesson_progress')
                ->where('student_id', $assignment->student_id)
                ->where('lesson_id', $lesson->lesson_id)
                ->first();

            $statusMap = [
                'in_progress' => 'in_progress',
                'completed' => 'completed',
                'failed' => 'failed',
            ];
            $status = $statusMap[$assignment->status] ?? 'pending';

            // 🔥 Determine if lesson is locked
            $isLocked = $assignment->is_locked;

            // 🔥 NEW: Determine if this is the next lesson
            $isNextLesson = false;
            if ($lesson->module) {
                // Get the module order
                $moduleOrder = $lesson->module_order;
                
                // Check if there's a previous lesson in this module
                $prevLesson = Lesson::where('module_id', $lesson->module->module_id)
                    ->where('module_order', '<', $moduleOrder)
                    ->where('status', 'published')
                    ->orderBy('module_order', 'desc')
                    ->first();
                
                if (!$prevLesson) {
                    // No previous lesson = this is the first lesson
                    $isNextLesson = true;
                } else {
                    // Check if previous lesson is completed with passing score
                    $prevAssignment = LessonAssignment::where('student_id', $assignment->student_id)
                        ->where('lesson_id', $prevLesson->lesson_id)
                        ->first();
                    
                    if ($prevAssignment && $prevAssignment->status === 'completed' && $prevAssignment->score >= 60) {
                        $isNextLesson = true;
                    }
                }
            } else {
                // No module = treat as first lesson (unlocked)
                $isNextLesson = true;
            }

            return [
                'assignment_id' => $assignment->id,
                'lesson_id' => $lesson->lesson_id,
                'title' => $lesson->title,
                'description' => $lesson->description,
                'lesson_type' => $lesson->lesson_type,
                'difficulty' => $lesson->difficulty,
                'status' => $status,
                'is_locked' => (bool) $isLocked,
                'is_next_lesson' => $isNextLesson, // 🔥 ADD THIS
                'score' => $highestScore,
                'assigned_at' => $assignment->assigned_at,
                'progress' => $progress ? [
                    'current_step' => $progress->current_step ?? 0,
                    'lesson_completed' => $progress->lesson_completed ?? false,
                    'quiz_completed' => $progress->quiz_completed ?? false,
                    'quiz_score' => $progress->quiz_score ?? null,
                ] : null,
                'total_steps' => $lesson->contents->count() + ($lesson->quiz ? 1 : 0),
                'has_quiz' => $lesson->quiz ? true : false,
            ];
        })->filter()->values();

        // ════════════════════════════════════════════════════════════
        // 🎯 Include XP data in the response
        // ════════════════════════════════════════════════════════════
        $xpService = new XPService();

        return response()->json([
            'success' => true,
            'lessons' => $lessons,
            'student' => [
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'fsl_mastery_level' => $student->fsl_mastery_level,
                'total_xp' => $student->total_xp ?? 0,
                'level' => $student->level ?? 1,
                'streak_days' => $student->streak_days ?? 0,
                'next_level_xp' => $xpService->getNextLevelXp($student),
                'level_progress' => $xpService->getLevelProgress($student),
                'level_name' => $xpService->getLevelName($student->level ?? 1),
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}
/**
 * Get a specific lesson with all content and quiz
 */
public function getLessonById(Request $request, $lessonId)
{
    try {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $student = Student::where('user_id', $user->id)->first();

        if (! $student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Check if student has access to this lesson
        $assignment = LessonAssignment::where('student_id', $student->student_id)
            ->where('lesson_id', $lessonId)
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'You do not have access to this lesson'], 403);
        }

        // Get the lesson with all content
        $lesson = Lesson::with(['contents', 'quiz.questions.options'])
            ->where('lesson_id', $lessonId)
            ->where('status', 'published')
            ->first();

        if (! $lesson) {
            return response()->json(['error' => 'Lesson not found'], 404);
        }

        // Get progress
        $progress = DB::table('student_lesson_progress')
            ->where('student_id', $student->student_id)
            ->where('lesson_id', $lessonId)
            ->first();

        // Format contents
        $contents = $lesson->contents->map(function ($content) {
            return [
                'content_id' => $content->content_id,
                'step_number' => $content->step_number,
                'content_type' => $content->content_type,
                'title' => $content->title,
                'content_text' => $content->content_text,
                'media_url' => $content->media_url ? asset('storage/'.$content->media_url) : null,
                'gesture_name' => $content->gesture_name,
            ];
        });

        // Format quiz
        $quiz = null;
        if ($lesson->quiz) {
            $questions = $lesson->quiz->questions->map(function ($question) {
                return [
                    'question_id' => $question->question_id,
                    'question_number' => $question->question_number,
                    'question_type' => $question->question_type,
                    'question_text' => $question->question_text,
                    'media_url' => $question->media_url ? asset('storage/'.$question->media_url) : null,
                    'points' => $question->points,
                    'options' => $question->options->map(function ($option) {
                        return [
                            'option_id' => $option->option_id,
                            'option_text' => $option->option_text,
                            'option_media_url' => $option->option_media_url ? asset('storage/'.$option->option_media_url) : null,
                            'is_correct' => $option->is_correct,
                        ];
                    }),
                ];
            });

            $quiz = [
                'quiz_id' => $lesson->quiz->quiz_id,
                'title' => $lesson->quiz->title,
                'description' => $lesson->quiz->description,
                'total_points' => $lesson->quiz->total_points,
                'passing_score' => $lesson->quiz->passing_score,
                'questions' => $questions,
            ];
        }

        return response()->json([
            'success' => true,
            'lesson' => [
                'lesson_id' => $lesson->lesson_id,
                'title' => $lesson->title,
                'description' => $lesson->description,
                'lesson_type' => $lesson->lesson_type,
                'difficulty' => $lesson->difficulty,
                'status' => $lesson->status,
                'contents' => $contents,
                'quiz' => $quiz,
                'total_steps' => $contents->count() + ($quiz ? 1 : 0),
                'assignment_status' => $assignment->status,
                'progress' => $progress ? [
                    'current_step' => $progress->current_step ?? 0,
                    'lesson_completed' => $progress->lesson_completed ?? false,
                    'quiz_completed' => $progress->quiz_completed ?? false,
                    'quiz_score' => $progress->quiz_score ?? null,
                ] : null,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Update lesson progress and quiz scores
 */
/**
 * Update lesson progress and quiz scores
 */
public function updateLessonProgress(Request $request, $lessonId)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (! $student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'current_step' => 'nullable|integer|min:0',
            'lesson_completed' => 'boolean',
            'quiz_completed' => 'boolean',
            'quiz_score' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 422);
        }

        // Update progress
        DB::table('student_lesson_progress')
            ->updateOrInsert(
                [
                    'student_id' => $student->student_id,
                    'lesson_id' => $lessonId,
                ],
                [
                    'current_step' => $request->input('current_step', 0),
                    'lesson_completed' => $request->input('lesson_completed', false),
                    'quiz_completed' => $request->input('quiz_completed', false),
                    'quiz_score' => $request->input('quiz_score'),
                    'last_accessed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

        // Update assignment status
        $assignment = LessonAssignment::where('student_id', $student->student_id)
            ->where('lesson_id', $lessonId)
            ->first();

        if ($assignment) {
            // Use enum values: 'in_progress', 'completed', 'failed'
            $status = 'in_progress';
            $lessonCompleted = $request->input('lesson_completed', false);
            $quizCompleted = $request->input('quiz_completed', false);

            if ($lessonCompleted && $quizCompleted) {
                $status = 'completed';
                $assignment->completed_at = now();
                $assignment->score = $request->input('quiz_score');
            }
            $assignment->status = $status;
            $assignment->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Progress updated successfully',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function submitQuizAttempt(Request $request, $lessonId)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,quiz_id',
            'answers' => 'required|array',
            'score' => 'required|integer',
            'total_points' => 'required|integer',
            'percentage' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 422);
        }

        // Calculate status
        $status = $request->percentage >= 60 ? 'completed' : 'failed';

        // ─── GET ATTEMPT NUMBER ──────────────────────────────────────────
        $attemptNumber = DB::table('quiz_attempts')
            ->where('student_id', $student->student_id)
            ->where('quiz_id', $request->quiz_id)
            ->count() + 1;

        // ============================================================
        // 🎯 CALCULATE XP (BEFORE saving the attempt)
        // ============================================================
        $xpService = new XPService();
        $isPerfect = $request->percentage == 100;
        
        // 🔥 Calculate XP BEFORE saving the attempt
        $xpEarned = $xpService->calculateQuizXpBeforeSave(
            $student,
            $request->quiz_id,
            $request->percentage,
            $isPerfect,
            $attemptNumber
        );

        // ─── CREATE QUIZ ATTEMPT WITH XP ──────────────────────────────
        $attemptId = DB::table('quiz_attempts')->insertGetId([
            'student_id' => $student->student_id,
            'quiz_id' => $request->quiz_id,
            'score' => $request->score,
            'total_points' => $request->total_points,
            'percentage' => $request->percentage,
            'status' => $status,
            'attempt_number' => $attemptNumber,
            'xp_earned' => $xpEarned,  // ← Save XP immediately
            'is_first_completion' => ($attemptNumber === 1),  // ← Set this too
            'started_at' => now(),
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Save answers
        foreach ($request->answers as $answer) {
            $selectedOptionId = isset($answer['selected_option_id']) ? $answer['selected_option_id'] : null;
            $questionId = isset($answer['question_id']) ? $answer['question_id'] : null;
            
            if (!$questionId && $selectedOptionId) {
                $option = DB::table('options')->where('option_id', $selectedOptionId)->first();
                if ($option) {
                    $questionId = $option->question_id;
                }
            }
            
            if (!$questionId) {
                continue;
            }
            
            $isCorrect = isset($answer['is_correct']) ? (bool)$answer['is_correct'] : false;
            $pointsEarned = $isCorrect ? 1 : 0;
            
            DB::table('student_answers')->insert([
                'attempt_id' => $attemptId,
                'question_id' => $questionId,
                'selected_option_id' => $selectedOptionId,
                'gesture_recognized' => $answer['gesture_recognized'] ?? null,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ============================================================
        // 🎯 AWARD XP IF EARNED
        // ============================================================
        if ($xpEarned > 0) {
            $reason = $xpService->getXpReason($attemptNumber, $isPerfect, $request->percentage, $xpEarned);
            $xpService->awardXp(
                $student,
                $xpEarned,
                'quiz_completed',
                $attemptId,
                $lessonId,
                $reason
            );

            // Update streak
            $xpService->updateStreak($student);
        }

        // ─── GET PREVIOUS BEST FOR COMPARISON ──────────────────────────
        // 🔥 Now we can safely get the previous best (excluding current attempt)
        $previousBest = DB::table('quiz_attempts')
            ->where('student_id', $student->student_id)
            ->where('quiz_id', $request->quiz_id)
            ->where('status', 'completed')
            ->where('attempt_id', '!=', $attemptId)  // ← Exclude current attempt
            ->max('percentage');
        
        $isImproved = ($previousBest !== null && $request->percentage > $previousBest);

        // ─── UPDATE PROGRESS ────────────────────────────────────────────
        $existingProgress = DB::table('student_lesson_progress')
            ->where('student_id', $student->student_id)
            ->where('lesson_id', $lessonId)
            ->first();

        $currentHighestScore = $existingProgress ? $existingProgress->quiz_score : null;
        $newScore = $request->percentage;

        if ($currentHighestScore === null || $newScore > $currentHighestScore) {
            DB::table('student_lesson_progress')
                ->updateOrInsert(
                    [
                        'student_id' => $student->student_id,
                        'lesson_id' => $lessonId,
                    ],
                    [
                        'quiz_completed' => true,
                        'quiz_score' => $newScore,
                        'last_accessed_at' => now(),
                        'updated_at' => now(),
                    ]
                );
        }

        // ─── UPDATE ASSIGNMENT ──────────────────────────────────────────
        $assignment = LessonAssignment::where('student_id', $student->student_id)
            ->where('lesson_id', $lessonId)
            ->first();
            
        if ($assignment) {
            $assignment->status = $status;
            $assignment->completed_at = now();
            $assignment->score = $request->percentage;
            
            if ($status === 'completed' && $request->percentage >= 60) {
                $currentLesson = Lesson::find($lessonId);
                
                if ($currentLesson && $currentLesson->module) {
                    $nextLesson = Lesson::where('module_id', $currentLesson->module->module_id)
                        ->where('module_order', '>', $currentLesson->module_order)
                        ->orderBy('module_order', 'asc')
                        ->first();
                    
                    if ($nextLesson) {
                        $nextAssignment = LessonAssignment::where('student_id', $student->student_id)
                            ->where('lesson_id', $nextLesson->lesson_id)
                            ->first();
                        
                        if ($nextAssignment) {
                            $nextAssignment->is_locked = false;
                            $nextAssignment->save();
                        }
                    }
                }
            }
            
            $assignment->save();
        }

        $student->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Quiz submitted successfully',
            'attempt_id' => $attemptId,
            'score' => $request->score,
            'total_points' => $request->total_points,
            'percentage' => $request->percentage,
            'status' => $status,
            'xp_earned' => $xpEarned,
            'attempt_number' => $attemptNumber,
            'is_first_completion' => ($attemptNumber === 1),
            'is_improved' => $isImproved,
            'total_xp' => $student->total_xp,
            'level' => $student->level,
            'streak_days' => $student->streak_days,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}
/**
 * Get human-readable XP reason
 */
private function getXpReason(int $attemptNumber, bool $isPerfect, float $percentage, int $xpEarned): string
{
    if ($attemptNumber === 1 && $isPerfect) {
        return "🏆 PERFECT on FIRST TRY! +{$xpEarned} XP (Bonus!)";
    } elseif ($attemptNumber === 1 && $percentage >= 60) {
        return "🎯 Passed on first attempt! +{$xpEarned} XP";
    } elseif ($isPerfect) {
        return "⭐ Perfect score on attempt #{$attemptNumber}! +{$xpEarned} XP";
    } else {
        return "📈 Improved score on attempt #{$attemptNumber}! +{$xpEarned} XP";
    }
}
/**
 * Award XP for completing a slide
 */
public function awardSlideXp(Request $request, $lessonId)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'slide_index' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 422);
        }

        $slideIndex = $request->slide_index;

        // Check if XP was already awarded for this slide
        $alreadyAwarded = DB::table('xp_log')
            ->where('student_id', $student->student_id)
            ->where('lesson_id', $lessonId)
            ->where('action', 'slide_completed')
            ->where('reason', 'LIKE', "Slide {$slideIndex}%")
            ->exists();

        if ($alreadyAwarded) {
            return response()->json([
                'success' => true,
                'message' => 'XP already awarded for this slide',
                'xp_earned' => 0,
            ]);
        }

        // Award 2 XP per slide
        $xpEarned = 2;

        $xpService = new XPService();
        $xpService->awardXp(
            $student,
            $xpEarned,
            'slide_completed',
            null,
            $lessonId,
            "Slide {$slideIndex} completed"
        );

        $student->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Slide XP awarded',
            'xp_earned' => $xpEarned,
            'total_xp' => $student->total_xp,
            'level' => $student->level,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function getAttempts(Request $request, $lessonId)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $attempts = DB::table('quiz_attempts')
            ->where('student_id', $student->student_id)
            ->where('quiz_id', function($query) use ($lessonId) {
                $query->select('quiz_id')
                    ->from('quizzes')
                    ->where('lesson_id', $lessonId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'attempts' => $attempts
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Get leaderboard for a specific lesson
 */
public function getLessonLeaderboard(Request $request, $lessonId)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // First, verify the lesson exists
        $lesson = Lesson::find($lessonId);
        if (!$lesson) {
            return response()->json(['error' => 'Lesson not found'], 404);
        }

        // Get the quiz for this lesson
        $quiz = DB::table('quizzes')->where('lesson_id', $lessonId)->first();
        if (!$quiz) {
            return response()->json(['error' => 'No quiz found for this lesson'], 404);
        }

        // Get all quiz attempts for this lesson across all students
        // We need to find the attempt number for each student's best score
        $rankings = DB::table('quiz_attempts as qa')
            ->join('students as s', 'qa.student_id', '=', 's.student_id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('qa.quiz_id', $quiz->quiz_id)
            ->where('qa.status', 'completed')
            ->select(
                's.student_id',
                's.first_name',
                's.last_name',
                'u.username',
                DB::raw('MAX(qa.percentage) as best_score'),
                DB::raw('COUNT(qa.attempt_id) as total_attempts'),
                DB::raw('MAX(qa.xp_earned) as xp_earned')
            )
            ->groupBy('s.student_id', 's.first_name', 's.last_name', 'u.username')
            ->get();

        // Now for each student, find the attempt number where they achieved their best score
        $rankedList = $rankings->map(function($item) use ($quiz, $student) {
            // Find the first attempt (lowest attempt count) that achieved the best score
            $bestAttempt = DB::table('quiz_attempts')
                ->where('student_id', $item->student_id)
                ->where('quiz_id', $quiz->quiz_id)
                ->where('percentage', $item->best_score)
                ->where('status', 'completed')
                ->orderBy('created_at', 'asc')  // Get the earliest attempt with this score
                ->first();

            // Count how many attempts the student made BEFORE this best attempt
            $attemptsBeforeBest = DB::table('quiz_attempts')
                ->where('student_id', $item->student_id)
                ->where('quiz_id', $quiz->quiz_id)
                ->where('status', 'completed')
                ->where('created_at', '<', $bestAttempt->created_at)
                ->count();

            // The attempt number that achieved the best score (1-indexed)
            $attemptsToAchieve = $attemptsBeforeBest + 1;

            return [
                'student_id' => $item->student_id,
                'name' => $item->first_name . ' ' . $item->last_name,
                'username' => $item->username,
                'best_score' => (int) $item->best_score,
                'total_attempts' => (int) $item->total_attempts,
                'attempts_to_achieve' => $attemptsToAchieve,
                'is_me' => $item->student_id === $student->student_id,
                'initials' => strtoupper(substr($item->first_name, 0, 1) . substr($item->last_name, 0, 1)),
                'xp_earned' => (int) $item->xp_earned,
            ];
        })
        ->sort(function($a, $b) {
            // Sort by: best_score DESC, then attempts_to_achieve ASC
            if ($a['best_score'] !== $b['best_score']) {
                return $b['best_score'] - $a['best_score']; // Higher score first
            }
            return $a['attempts_to_achieve'] - $b['attempts_to_achieve']; // Fewer attempts first
        })
        ->values();

        // Add rank numbers
        $rankedList = $rankedList->map(function($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });

        // Find current user's rank
        $userRank = null;
        foreach ($rankedList as $r) {
            if ($r['is_me']) {
                $userRank = $r['rank'];
                break;
            }
        }

        return response()->json([
            'success' => true,
            'rankings' => $rankedList,
            'user_rank' => $userRank,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Sync lesson lock status based on completion
 */
private function syncLessonLocks($student)
{
    $assignments = LessonAssignment::where('student_id', $student->student_id)
        ->with('lesson')
        ->get();
    
    // First, process all lessons to determine their status based on highest score
    foreach ($assignments as $assignment) {
        $lesson = $assignment->lesson;
        
        if (!$lesson || !$lesson->module) {
            continue;
        }
        
        // Get highest score from quiz_attempts
        $highestScore = DB::table('quiz_attempts as qa')
            ->join('quizzes as q', 'qa.quiz_id', '=', 'q.quiz_id')
            ->where('qa.student_id', $student->student_id)
            ->where('q.lesson_id', $lesson->lesson_id)
            ->where('qa.status', 'completed')
            ->max('qa.percentage');
        
        if ($highestScore === null) {
            $progress = DB::table('student_lesson_progress')
                ->where('student_id', $student->student_id)
                ->where('lesson_id', $lesson->lesson_id)
                ->first();
            $highestScore = $progress ? $progress->quiz_score : null;
        }
        
        $hasPassed = $highestScore !== null && $highestScore >= 60;
        $hasFailed = $highestScore !== null && $highestScore < 60;
        
        if ($hasPassed) {
            $assignment->status = 'completed';
            $assignment->score = $highestScore;
            $assignment->is_locked = false; // Completed = unlocked (show checkmark)
            $assignment->save();
        } elseif ($hasFailed) {
            $assignment->status = 'failed';
            $assignment->score = $highestScore;
            // Failed lessons are locked UNLESS they are the first lesson
            $firstLesson = Lesson::where('module_id', $lesson->module->module_id)
                ->where('status', 'published')
                ->orderBy('module_order', 'asc')
                ->first();
            
            if ($firstLesson && $firstLesson->lesson_id === $lesson->lesson_id) {
                $assignment->is_locked = false; // First lesson always unlocked
            } else {
                $assignment->is_locked = true; // Other failed lessons are locked
            }
            $assignment->save();
        } else {
            // No attempts yet
            $firstLesson = Lesson::where('module_id', $lesson->module->module_id)
                ->where('status', 'published')
                ->orderBy('module_order', 'asc')
                ->first();
            
            if ($firstLesson && $firstLesson->lesson_id === $lesson->lesson_id) {
                $assignment->is_locked = false; // First lesson always unlocked
            } else {
                $assignment->is_locked = true; // Other lessons locked by default
            }
            $assignment->save();
        }
    }
    
  // 🔥 SECOND PASS: Unlock the next lesson if previous is completed
foreach ($assignments as $assignment) {
    $lesson = $assignment->lesson;
    
    if (!$lesson || !$lesson->module) {
        continue;
    }
    
    // If this lesson is completed, unlock the next one
    if ($assignment->status === 'completed' && $assignment->score >= 60) {
        $nextLesson = Lesson::where('module_id', $lesson->module->module_id)
            ->where('module_order', '>', $lesson->module_order)
            ->orderBy('module_order', 'asc')
            ->first();
        
        if ($nextLesson) {
            $nextAssignment = LessonAssignment::where('student_id', $student->student_id)
                ->where('lesson_id', $nextLesson->lesson_id)
                ->first();
            
            if ($nextAssignment) {
                // 🔥 FIX: Always unlock the next lesson, even if it was failed
                // (so the student can retry it)
                $nextAssignment->is_locked = false;
                $nextAssignment->save();
            }
        }
    }
}
    }

    /**
 * Save student's gesture performance from the mobile app
 * This handles practice sessions where students can practice anytime
 */
public function saveGesturePerformance(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'module_name' => 'required|string|in:alphabet_part1,alphabet_part2,numbers',
            'letter_performances' => 'required|array',
            'letter_performances.*.letter' => 'required|string',
            'letter_performances.*.attempts' => 'required|integer|min:0',
            'letter_performances.*.wrong_attempts' => 'required|integer|min:0',
            'letter_performances.*.success_count' => 'required|integer|min:0',
            'letter_performances.*.consecutive_wrong' => 'nullable|integer|min:0',
            'session_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the module
        $module = GestureModule::where('name', $request->module_name)->first();
        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        $savedPerformances = [];
        $totalAttempts = 0;
        $totalCorrect = 0;
        $totalWrong = 0;

        foreach ($request->letter_performances as $letterData) {
            // Find the gesture by name and module
            $gesture = Gesture::where('name', $letterData['letter'])
                             ->where('module_id', $module->module_id)
                             ->first();

            if (!$gesture) {
                // Skip if gesture not found (shouldn't happen if data is correct)
                continue;
            }

            // Update or create performance record
            $performance = GesturePerformance::updateOrCreatePerformance(
                $student->student_id,
                $gesture->gesture_id,
                $module->module_id,
                [
                    'attempts' => $letterData['attempts'],
                    'successful_attempts' => $letterData['success_count'],
                    'wrong_attempts' => $letterData['wrong_attempts'],
                    'consecutive_wrong' => $letterData['consecutive_wrong'] ?? 0,
                    'session_id' => $request->session_id,
                ]
            );

            $savedPerformances[] = [
                'letter' => $letterData['letter'],
                'gesture_id' => $gesture->gesture_id,
                'performance_id' => $performance->performance_id,
                'attempts' => $performance->attempts,
                'successful_attempts' => $performance->successful_attempts,
                'wrong_attempts' => $performance->wrong_attempts,
                'is_mastered' => $performance->is_mastered,
                'mastery_level' => $performance->mastery_level,
            ];

            $totalAttempts += $letterData['attempts'];
            $totalCorrect += $letterData['success_count'];
            $totalWrong += $letterData['wrong_attempts'];
        }

        // Get module progress summary
        $progress = GesturePerformance::getModuleProgress(
            $student->student_id,
            $module->module_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Gesture performance saved successfully',
            'module' => [
                'name' => $module->name,
                'display_name' => $module->display_name,
            ],
            'session_summary' => [
                'total_attempts' => $totalAttempts,
                'total_correct' => $totalCorrect,
                'total_wrong' => $totalWrong,
                'accuracy' => $totalAttempts > 0 
                    ? round(($totalCorrect / $totalAttempts) * 100) 
                    : 0,
            ],
            'progress_summary' => $progress,
            'performances' => $savedPerformances,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Get student's gesture performance for a specific module
 */
public function getGesturePerformance(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $moduleName = $request->query('module_name', 'alphabet_part1');
        
        // Find the module
        $module = GestureModule::where('name', $moduleName)
                              ->with('gestures')
                              ->first();

        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Get all performances for this student in this module
        $gestureIds = $module->gestures->pluck('gesture_id');
        $performances = GesturePerformance::where('student_id', $student->student_id)
                                          ->whereIn('gesture_id', $gestureIds)
                                          ->get()
                                          ->keyBy('gesture_id');

        // Build response with all gestures and their performance
        $result = $module->gestures->map(function($gesture) use ($performances) {
            $performance = $performances->get($gesture->gesture_id);
            
            return [
                'gesture_id' => $gesture->gesture_id,
                'name' => $gesture->name,
                'display_name' => $gesture->display_name,
                'model_file' => $gesture->model_file,
                'difficulty' => $gesture->difficulty,
                'performance' => $performance ? [
                    'attempts' => $performance->attempts,
                    'successful_attempts' => $performance->successful_attempts,
                    'wrong_attempts' => $performance->wrong_attempts,
                    'consecutive_wrong' => $performance->consecutive_wrong,
                    'is_mastered' => $performance->is_mastered,
                    'mastery_level' => $performance->mastery_level,
                    'first_attempt_at' => $performance->first_attempt_at,
                    'last_attempt_at' => $performance->last_attempt_at,
                    'mastered_at' => $performance->mastered_at,
                ] : null,
            ];
        });

        // Calculate overall stats
        $totalGestures = $result->count();
        $withPerformance = $result->filter(fn($g) => $g['performance'] !== null)->count();
        $mastered = $result->filter(fn($g) => $g['performance'] && $g['performance']['is_mastered'])->count();
        $struggling = $result->filter(fn($g) => $g['performance'] && $g['performance']['mastery_level'] === 'needs_practice')->count();

        return response()->json([
            'success' => true,
            'module' => [
                'module_id' => $module->module_id,
                'name' => $module->name,
                'display_name' => $module->display_name,
                'description' => $module->description,
                'difficulty' => $module->difficulty,
                'total_gestures' => $totalGestures,
            ],
            'summary' => [
                'gestures_with_performance' => $withPerformance,
                'mastered' => $mastered,
                'struggling' => $struggling,
                'mastery_percentage' => $totalGestures > 0 
                    ? round(($mastered / $totalGestures) * 100) 
                    : 0,
            ],
            'gestures' => $result,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Get struggling letters for a student (for recommendations)
 */
public function getStrugglingLetters(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $moduleName = $request->query('module_name');
        
        $module = null;
        if ($moduleName) {
            $module = GestureModule::where('name', $moduleName)->first();
            if (!$module) {
                return response()->json(['error' => 'Module not found'], 404);
            }
        }

        // Get struggling letters
        $struggling = GesturePerformance::getStrugglingLetters(
            $student->student_id,
            $module ? $module->module_id : null
        );

        $result = $struggling->map(function($performance) {
            return [
                'letter' => $performance->gesture->name,
                'display_name' => $performance->gesture->display_name,
                'attempts' => $performance->attempts,
                'wrong_attempts' => $performance->wrong_attempts,
                'successful_attempts' => $performance->successful_attempts,
                'mastery_level' => $performance->mastery_level,
                'module' => $performance->module ? $performance->module->display_name : null,
            ];
        });

        return response()->json([
            'success' => true,
            'struggling_letters' => $result,
            'count' => $result->count(),
            'recommendation' => $result->count() > 0 
                ? 'Practice these letters: ' . $result->pluck('letter')->implode(', ')
                : 'Great job! No struggling letters found.',
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function getGestureProgress(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Get all gesture modules
        $modules = GestureModule::where('is_active', true)
                               ->orderBy('order', 'asc')
                               ->get();

        $moduleProgress = [];

        foreach ($modules as $module) {
            // Get all gestures in this module
            $gestureIds = $module->gestures->pluck('gesture_id');
            
            // Get student's performances for these gestures
            $performances = GesturePerformance::where('student_id', $student->student_id)
                                             ->whereIn('gesture_id', $gestureIds)
                                             ->get();

            $totalGestures = $gestureIds->count();
            
            // ✅ FIX: Count mastered AND proficient as "completed"
            $completedCount = $performances->whereIn('mastery_level', ['mastered', 'proficient'])->count();
            
            // Calculate progress percentage
            $progress = $totalGestures > 0 
                ? round(($completedCount / $totalGestures) * 100) 
                : 0;

            // Check if module is completed (all gestures mastered OR proficient)
            $isCompleted = $completedCount === $totalGestures && $totalGestures > 0;

            // Get XP for this module (default 40)
            $xpAvailable = 40;
            
            // Check if module is locked
            $isLocked = $this->isModuleLocked($student, $module);

            $moduleProgress[] = [
                'module_id' => $module->module_id,
                'name' => $module->name,
                'display_name' => $module->display_name,
                'description' => $module->description,
                'difficulty' => $module->difficulty,
                'total_gestures' => $totalGestures,
                'completed_count' => $completedCount,
                'progress' => $progress,
                'is_completed' => $isCompleted,
                'xp_available' => $xpAvailable,
                'is_locked' => $isLocked,
            ];
        }

        // Get total XP from student
        $totalXp = $student->total_xp ?? 0;

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->student_id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'total_xp' => $totalXp,
                'level' => $student->level ?? 1,
            ],
            'modules' => $moduleProgress,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Award XP for completing a module based on star rating
 * - 1 star = 0 XP
 * - 2 stars = 20 XP
 * - 3 stars = 40 XP (or 20 if they already got 20 from 2-star attempt)
 * - Max XP per module = 40
 */
public function awardModuleXp(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'module_name' => 'required|string|exists:gesture_modules,name',
            'star_rating' => 'required|integer|in:1,2,3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        $module = GestureModule::where('name', $request->module_name)->first();
        $starRating = $request->star_rating;

        // ─── CHECK TOTAL XP EARNED FOR THIS MODULE ──────────────────────
        $totalXpEarned = DB::table('xp_log')
            ->where('student_id', $student->student_id)
            ->where('action', 'module_completed')
            ->where('reason', 'LIKE', "%{$module->display_name}%")
            ->sum('xp_amount');

        // Max XP per module is 40
        $maxXpPerModule = 40;

        // If already earned max XP, return 0
        if ($totalXpEarned >= $maxXpPerModule) {
            return response()->json([
                'success' => true,
                'message' => 'Already earned max XP for this module! 🎉',
                'star_rating' => $starRating,
                'xp_earned' => 0,
                'total_xp' => $student->total_xp,
                'level' => $student->level,
                'xp_message' => "🏆 Max XP ({$maxXpPerModule} XP) already earned for {$module->display_name}!",
                'max_xp_reached' => true,
            ]);
        }

        // Calculate XP based on stars
        $xpEarned = 0;
        $xpMessage = '';

        if ($starRating === 1) {
            $xpEarned = 0;
            $xpMessage = "⭐ 1 star! Keep practicing! No XP earned.";
        } elseif ($starRating === 2) {
            // Check remaining XP
            $remainingXp = $maxXpPerModule - $totalXpEarned;
            $xpEarned = min(20, $remainingXp);
            $xpMessage = "⭐⭐ 2 stars! Great job! +{$xpEarned} XP";
        } elseif ($starRating === 3) {
            // Check remaining XP
            $remainingXp = $maxXpPerModule - $totalXpEarned;
            
            if ($totalXpEarned > 0) {
                // Already got some XP, give remaining up to 20
                $xpEarned = min(20, $remainingXp);
                $xpMessage = "⭐⭐⭐ AMAZING! 3 stars! +{$xpEarned} XP bonus! (Total: " . ($totalXpEarned + $xpEarned) . " XP)";
            } else {
                // First time getting 3 stars = full 40 XP
                $xpEarned = min(40, $remainingXp);
                $xpMessage = "⭐⭐⭐ PERFECT! 3 stars on first try! +{$xpEarned} XP!";
            }
        }

        // Award XP if earned
        if ($xpEarned > 0) {
            $xpService = new XPService();
            $xpService->awardXp(
                $student,
                $xpEarned,
                'module_completed',
                null,
                null,
                "{$xpMessage} - {$module->display_name} module"
            );
        }

        $student->refresh();

        return response()->json([
            'success' => true,
            'message' => $xpEarned > 0 ? 'XP awarded successfully' : 'No XP earned this time',
            'star_rating' => $starRating,
            'xp_earned' => $xpEarned,
            'total_xp' => $student->total_xp,
            'level' => $student->level,
            'xp_message' => $xpMessage,
            'max_xp_reached' => $totalXpEarned >= $maxXpPerModule,
            'total_xp_earned_for_module' => $totalXpEarned + $xpEarned,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Check if a module is locked for a student
 */
private function isModuleLocked($student, $module)
{
    // First module is always unlocked
    if ($module->order === 1) {
        return false;
    }

    // Check if previous module is completed
    $previousModule = GestureModule::where('order', '<', $module->order)
                                  ->orderBy('order', 'desc')
                                  ->first();

    if (!$previousModule) {
        return false;
    }

    // Get previous module's gestures
    $gestureIds = $previousModule->gestures->pluck('gesture_id');
    
    // Count mastered AND proficient as "completed"
    $completedCount = GesturePerformance::where('student_id', $student->student_id)
                                       ->whereIn('gesture_id', $gestureIds)
                                       ->whereIn('mastery_level', ['mastered', 'proficient'])
                                       ->count();

    // Count how many are still "developing" or "needs_practice"
    $developingCount = GesturePerformance::where('student_id', $student->student_id)
                                        ->whereIn('gesture_id', $gestureIds)
                                        ->whereIn('mastery_level', ['developing', 'needs_practice'])
                                        ->count();

    $totalGestures = $gestureIds->count();

    // 🎯 UNLOCK when 10 out of 13 letters are mastered/proficient
    // OR when there are 3 or fewer letters still developing
    $unlockThreshold = 0.70; // 70%
    $requiredCount = ceil($totalGestures * $unlockThreshold);
    $maxDeveloping = 3; // Allow up to 3 letters that need more practice
    
    $isUnlocked = ($completedCount >= $requiredCount) || ($developingCount <= $maxDeveloping);
    
    // Module is locked if not unlocked
    return !$isUnlocked;
}
}
