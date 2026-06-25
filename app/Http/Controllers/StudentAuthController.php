<?php

namespace App\Http\Controllers;

use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\Student;
use App\Models\User;
use App\Services\XPService;  
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

        // Get all lesson assignments for this student
        $assignments = LessonAssignment::where('student_id', $student->student_id)
            ->with(['lesson' => function ($query) {
                $query->where('status', 'published')
                      ->with(['contents', 'quiz']);
            }])
            ->orderBy('assigned_at', 'desc')
            ->get();

        $lessons = $assignments->map(function ($assignment) {
            $lesson = $assignment->lesson;

            if (!$lesson) {
                return null;
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

            return [
                'assignment_id' => $assignment->id,
                'lesson_id' => $lesson->lesson_id,
                'title' => $lesson->title,
                'description' => $lesson->description,
                'lesson_type' => $lesson->lesson_type,
                'difficulty' => $lesson->difficulty,
                'status' => $status,
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

/**
     * Submit quiz attempt
     */
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

        // Create quiz attempt
        $attemptId = DB::table('quiz_attempts')->insertGetId([
            'student_id' => $student->student_id,
            'quiz_id' => $request->quiz_id,
            'score' => $request->score,
            'total_points' => $request->total_points,
            'percentage' => $request->percentage,
            'status' => $status,
            'started_at' => now(),
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Save answers
        foreach ($request->answers as $answer) {
            DB::table('student_answers')->insert([
                'attempt_id' => $attemptId,
                'question_id' => $answer['question_id'],
                'selected_option_id' => $answer['selected_option_id'] ?? null,
                'gesture_recognized' => $answer['gesture_recognized'] ?? null,
                'is_correct' => $answer['is_correct'] ?? false,
                'points_earned' => $answer['is_correct'] ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 🎯 XP LOGIC - Only award XP if:
        // 1. Student passed (>= 60%)
        // 2. It's their first completion OR they improved their score
        $xpEarned = 0;
        $isFirstCompletion = false;
        $isImproved = false;

        if ($request->percentage >= 60) {
            // Get previous best attempt
            $previousBest = DB::table('quiz_attempts')
                ->where('student_id', $student->student_id)
                ->where('quiz_id', $request->quiz_id)
                ->where('status', 'completed')
                ->where('attempt_id', '!=', $attemptId) // Exclude current attempt
                ->orderBy('percentage', 'desc')
                ->first();

            // Check if this is the first completion
            $isFirstCompletion = $previousBest === null;

            // Check if score improved
            $isImproved = $previousBest && $request->percentage > $previousBest->percentage;

            // Only award XP if first completion OR improved score
            if ($isFirstCompletion || $isImproved) {
                $xpService = new XPService();
                $xpEarned = $xpService->calculateQuizXp($request->percentage);

                // Add bonus for first completion
                if ($isFirstCompletion && $xpEarned > 0) {
                    $xpEarned += XPService::QUIZ_XP['bonus'];
                }

                // Award XP
                $xpService->awardXp(
                    $student,
                    $xpEarned,
                    'quiz_completed',
                    $attemptId,
                    $lessonId
                );
            }

            // Update streak (only if they passed)
            $xpService = $xpService ?? new XPService();
            $xpService->updateStreak($student);
        }

        // Update quiz attempt with XP info
        DB::table('quiz_attempts')
            ->where('attempt_id', $attemptId)
            ->update([
                'xp_earned' => $xpEarned,
                'is_first_completion' => $isFirstCompletion,
            ]);

        // Update progress
        DB::table('student_lesson_progress')
            ->updateOrInsert(
                [
                    'student_id' => $student->student_id,
                    'lesson_id' => $lessonId,
                ],
                [
                    'quiz_completed' => true,
                    'quiz_score' => $request->percentage,
                    'last_accessed_at' => now(),
                    'updated_at' => now(),
                ]
            );

        // Update assignment
        $assignment = LessonAssignment::where('student_id', $student->student_id)
            ->where('lesson_id', $lessonId)
            ->first();

        if ($assignment) {
            $assignment->status = 'completed';
            $assignment->completed_at = now();
            $assignment->score = $request->percentage;
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
            'is_first_completion' => $isFirstCompletion,
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
}
