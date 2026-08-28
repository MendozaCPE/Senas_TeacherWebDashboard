<?php

namespace App\Http\Controllers;

use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\Student;
use App\Models\StudentPromotion; 
use App\Models\User;
use App\Models\StudentSetting;
use App\Services\XPService; 
use App\Services\AchievementService; 
use App\Services\DailyChallengeService;  
use App\Models\Gesture;
use App\Models\GestureModule;
use App\Models\GesturePerformance;
use App\Models\ModuleQuizResult;
use App\Models\Module;
use App\Models\Achievement;
use App\Models\StudentAchievement;
use App\Models\StudentNotification;
use App\Models\DailyChallenge; 
use App\Models\ChallengeGoalProgress; 
use App\Models\CheckpointExam;
use App\Models\CheckpointExamAssignment;
use App\Models\CheckpointExamQuestion;
use App\Models\CheckpointExamAttempt;
use App\Models\HelpRequest;
use App\Models\TeacherNotification;
use App\Models\GestureHintUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;  
use Carbon\Carbon; 

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

        // Verify PIN (plain text comparison — PINs are stored unhashed in DB)
        if ($request->pin !== $student->pin) {
            return response()->json(['message' => 'Invalid PIN'], 401);
        }

        // Get user data
        $user = User::find($student->user_id);

        if (! $user) {
            return response()->json(['message' => 'User account not found'], 404);
        }

         // Get or create settings
    $settings = $student->settings;
    if (!$settings) {
        $settings = StudentSetting::create([
            'student_id' => $student->student_id,
            'sound_enabled' => true,
            'notifications_enabled' => true,
        ]);
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
                'profile_picture' => $student->profile_picture ?? 'senya',
                'teacher_id' => $student->teacher_id, // ✅ ADD THIS LINE
            ],
            'settings' => [
                'sound_enabled' => $settings->sound_enabled,
                'notifications_enabled' => $settings->notifications_enabled,
            ],
        ],
    ]);
}

    /**
 * Get student settings
 * GET /api/student/settings
 */
public function getSettings(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $settings = $student->settings;
        if (!$settings) {
            $settings = StudentSetting::create([
                'student_id' => $student->student_id,
                'sound_enabled' => true,
                'notifications_enabled' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'settings' => [
                'sound_enabled' => (bool) $settings->sound_enabled,
                'notifications_enabled' => (bool) $settings->notifications_enabled,
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
 * Update student settings
 * POST /api/student/settings
 */
public function updateSettings(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'sound_enabled' => 'sometimes|boolean',
            'notifications_enabled' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create settings
        $settings = $student->settings;
        if (!$settings) {
            $settings = StudentSetting::create([
                'student_id' => $student->student_id,
                'sound_enabled' => true,
                'notifications_enabled' => true,
            ]);
        }

        // Update only provided fields
        if ($request->has('sound_enabled')) {
            $settings->sound_enabled = $request->sound_enabled;
        }
        if ($request->has('notifications_enabled')) {
            $settings->notifications_enabled = $request->notifications_enabled;
        }
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'settings' => [
                'sound_enabled' => (bool) $settings->sound_enabled,
                'notifications_enabled' => (bool) $settings->notifications_enabled,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}


    public function profile(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $xpService = new XPService();
        $xpService->updateStreak($student);

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
                'total_xp' => $student->total_xp ?? 0,
                'today_xp' => $xpService->getTodayXp($student),
                'streak_days' => $student->streak_days ?? 0,
                'level' => $student->level ?? 1,
                'level_name' => $xpService->getLevelName($student->level ?? 1),
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
        'learning_goal' => 'required|string|in:Alphabet_Numbers,Fingerspelling,Greetings_FSL_Words,Everything',
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

    //🎯 ADAPTIVE Learning Path - Get personalized lesson recommendations
    //This version uses actual student performance data to recommend lessons
    // Uses SMART CONCEPT MATCHING:
    // - Extracts gesture names from lesson content (not just text matching)
    // - Matches weak skills to what the lesson ACTUALLY teaches
    // - Understands context (Greetings lesson vs Alphabet lesson)
    public function getRecommendedLessons(Request $request)
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

            // ============================================================
            // 1. GET ADAPTIVE DATA
            // ============================================================
            $masteryService = new \App\Services\MasteryService();

            $weakSkills = $masteryService->getWeakSkills($student->student_id, 0.6, 20);
            $overallMastery = $masteryService->getOverallMastery($student->student_id);

            $learningPath = LearningPath::where('student_id', $student->student_id)->first();
            $goal = $learningPath->learning_goal ?? 'Everything';
            $level = $learningPath->fsl_level ?? ($student->fsl_mastery_level ?? 'Beginner');

            // ============================================================
            // 2. DETERMINE ACCESSIBLE MODULES & LESSONS
            // ============================================================
            $studentLevel = strtolower($level);
            $accessibleLevels = $this->getAccessibleModuleLevels($studentLevel);
            $allModules = Module::where('teacher_id', $student->teacher_id)
                ->orderBy('module_order')
                ->get();

            $accessibleModuleIds = [];
            $lockedModuleNames = [];
            $previousModuleCompleted = true;
            $lastModuleLevel = null;

            $allAssignments = LessonAssignment::where('student_id', $student->student_id)
                ->whereHas('lesson', function ($query) {
                    $query->where('status', 'published')->whereNull('deleted_at');
                })
                ->with(['lesson'])
                ->get();

            foreach ($allModules as $mod) {
                $modId = $mod->module_id;
                $modLevel = strtolower($mod->mastery_level ?? 'beginner');

                $isLevelAccessible = in_array($modLevel, $accessibleLevels);

                if ($lastModuleLevel !== null && $modLevel !== $lastModuleLevel) {
                    if ($isLevelAccessible && $studentLevel === $modLevel) {
                        $previousModuleCompleted = true;
                    }
                }
                $lastModuleLevel = $modLevel;

                $isAccessible = $isLevelAccessible && $previousModuleCompleted;

                if ($isAccessible) {
                    $accessibleModuleIds[] = $modId;
                } else {
                    $lockedModuleNames[] = $mod->title;
                }

                // Check 100% completion of this module
                $modLessonsAssignments = $allAssignments->filter(fn($a) => $a->lesson && $a->lesson->module_id === $modId);
                $totalInMod = $modLessonsAssignments->count();
                $completedInMod = 0;

                foreach ($modLessonsAssignments as $a) {
                    $scoreVal = DB::table('quiz_attempts as qa')
                        ->join('quizzes as q', 'qa.quiz_id', '=', 'q.quiz_id')
                        ->where('qa.student_id', $student->student_id)
                        ->where('q.lesson_id', $a->lesson->lesson_id)
                        ->where('qa.status', 'completed')
                        ->max('qa.percentage');

                    if ($scoreVal === null && $a->status === 'completed' && $a->score >= 60) {
                        $scoreVal = $a->score;
                    }

                    if ($scoreVal !== null && $scoreVal >= 60) {
                        $completedInMod++;
                    }
                }

                $previousModuleCompleted = ($totalInMod > 0) && ($completedInMod === $totalInMod);
            }

            // 🔥 ONLY FETCH LESSONS FROM ACCESSIBLE / UNLOCKED MODULES
            $allLessons = Lesson::where('status', 'published')
                ->whereNull('deleted_at')
                ->whereIn('module_id', $accessibleModuleIds)
                ->with(['contents', 'quiz.questions.options', 'module'])
                ->get();

            // Sort lessons strictly by module_order ASC, then lesson module_order ASC, then lesson_id ASC
            $allLessons = $allLessons->sort(function ($a, $b) {
                $modOrderA = $a->module->module_order ?? 0;
                $modOrderB = $b->module->module_order ?? 0;
                if ($modOrderA !== $modOrderB) {
                    return $modOrderA - $modOrderB;
                }
                $lesOrderA = $a->module_order ?? 0;
                $lesOrderB = $b->module_order ?? 0;
                if ($lesOrderA !== $lesOrderB) {
                    return $lesOrderA - $lesOrderB;
                }
                return $a->lesson_id - $b->lesson_id;
            })->values();

            // Get completed & in-progress statuses
            $completedAssignments = LessonAssignment::where('student_id', $student->student_id)
                ->where('status', 'completed')
                ->where('score', '>=', 60)
                ->whereHas('lesson', function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->pluck('score', 'lesson_id')
                ->toArray();

            $inProgressLessonIds = LessonAssignment::where('student_id', $student->student_id)
                ->where('status', 'in_progress')
                ->whereHas('lesson', function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->pluck('lesson_id')
                ->toArray();

            // ============================================================
            // 3. CLASSIFY LESSONS INTO TOPIC CATEGORIES
            // ============================================================
            $categorizedLessons = [];
            foreach ($allLessons as $lesson) {
                $concepts = $this->extractLessonConcepts($lesson);
                $flags = $this->classifyLessonGoalFlags($concepts);

                $titleLower = strtolower($lesson->title ?? '');
                $descLower = strtolower($lesson->description ?? '');

                $isIntro = str_contains($titleLower, 'welcome') ||
                           str_contains($titleLower, 'intro') ||
                           (($lesson->module->module_order ?? 0) === 1 && ($lesson->module_order ?? 0) === 1);

                $isFingerspelling = str_contains($titleLower, 'fingerspelling') || str_contains($descLower, 'fingerspelling');

                if ($isIntro) {
                    $category = 'intro';
                } elseif ($isFingerspelling) {
                    $category = 'fingerspelling';
                } elseif ($flags['is_alphabet']) {
                    $category = 'alphabet';
                } elseif ($flags['is_number']) {
                    $category = 'numbers';
                } elseif ($flags['is_greeting']) {
                    $category = 'greetings';
                } else {
                    $category = 'other';
                }

                $categorizedLessons[] = [
                    'lesson' => $lesson,
                    'category' => $category,
                    'concepts' => $concepts,
                    'flags' => $flags,
                ];
            }

            // ============================================================
            // 4. ORDER TOPICS BY STUDENT'S LEARNING GOAL
            // ============================================================
            switch ($goal) {
                case 'Alphabet_Numbers':
                    $groupOrder = ['intro', 'alphabet', 'numbers', 'fingerspelling', 'greetings', 'other'];
                    break;
                case 'Fingerspelling':
                    // Alphabets must come first as prerequisite!
                    $groupOrder = ['intro', 'alphabet', 'fingerspelling', 'numbers', 'greetings', 'other'];
                    break;
                case 'Greetings_FSL_Words':
                    $groupOrder = ['intro', 'greetings', 'other', 'alphabet', 'numbers', 'fingerspelling'];
                    break;
                case 'Everything':
                default:
                    $groupOrder = ['intro', 'alphabet', 'numbers', 'fingerspelling', 'greetings', 'other'];
                    break;
            }

            $buckets = array_fill_keys(['intro', 'alphabet', 'numbers', 'fingerspelling', 'greetings', 'other'], []);
            foreach ($categorizedLessons as $item) {
                $buckets[$item['category']][] = $item;
            }

            $orderedItems = [];
            if ($goal === 'Everything') {
                $orderedItems = $categorizedLessons;
            } else {
                foreach ($groupOrder as $catKey) {
                    if (!empty($buckets[$catKey])) {
                        foreach ($buckets[$catKey] as $item) {
                            $orderedItems[] = $item;
                        }
                    }
                }
            }

            // ============================================================
            // 5. FORMAT RESPONSE WITH STRICT SEQUENTIAL LOCKING
            // ============================================================
            $formattedLessons = [];
            $unlockedNext = true;

            foreach ($orderedItems as $item) {
                $lesson = $item['lesson'];
                $lessonId = $lesson->lesson_id;

                $assignment = LessonAssignment::where('student_id', $student->student_id)
                    ->where('lesson_id', $lessonId)
                    ->first();

                $progress = DB::table('student_lesson_progress')
                    ->where('student_id', $student->student_id)
                    ->where('lesson_id', $lessonId)
                    ->first();

                $highestScore = DB::table('quiz_attempts as qa')
                    ->join('quizzes as q', 'qa.quiz_id', '=', 'q.quiz_id')
                    ->where('qa.student_id', $student->student_id)
                    ->where('q.lesson_id', $lessonId)
                    ->where('qa.status', 'completed')
                    ->max('qa.percentage');

                if ($highestScore === null && $progress) {
                    $highestScore = $progress->quiz_score;
                }
                if ($highestScore === null && isset($completedAssignments[$lessonId])) {
                    $highestScore = $completedAssignments[$lessonId];
                }

                $isDone = ($highestScore !== null && $highestScore >= 60) || isset($completedAssignments[$lessonId]);
                $isInProgress = !$isDone && (in_array($lessonId, $inProgressLessonIds) || ($progress && (($progress->current_step ?? 0) > 0 || $progress->lesson_completed)));

                if ($isDone) {
                    $isLocked = false;
                    $isActive = false;
                    $statusStr = 'completed';
                } elseif ($unlockedNext) {
                    $isLocked = false;
                    $isActive = true;
                    $unlockedNext = false;
                    $statusStr = $isInProgress ? 'in_progress' : 'pending';
                } else {
                    $isLocked = true;
                    $isActive = false;
                    $statusStr = 'pending';
                }

                // Check for covered weak skills
                $coveredSkills = [];
                $skillIdsFound = [];
                if (!empty($item['concepts']) && !empty($weakSkills)) {
                    foreach ($item['concepts'] as $concept) {
                        foreach ($weakSkills as $weak) {
                            $weakName = strtoupper($weak['gesture_name'] ?? '');
                            if (strtoupper($concept) === $weakName) {
                                if (!in_array($weak['gesture_id'], $skillIdsFound)) {
                                    $coveredSkills[] = $weak;
                                    $skillIdsFound[] = $weak['gesture_id'];
                                }
                            }
                        }
                    }
                }

                $goalDisplay = str_replace('_', ' ', $goal);
                // Clean up concept names for human-readable display
                $cleanConcepts = array_map(function($c) {
                    $c = preg_replace('/^\d+_/', '', $c);
                    return ucwords(strtolower(str_replace('_', ' ', $c)));
                }, $item['concepts']);
                $conceptsStr = !empty($cleanConcepts) ? implode(', ', array_slice($cleanConcepts, 0, 3)) : '';

                if ($isDone) {
                    $recType = 'completed';
                    $recReason = "✅ You completed this lesson with {$highestScore}%! Great job! 🎉";
                    $detReason = "You've already mastered this lesson. Review it anytime to reinforce your skills.";
                } elseif ($isInProgress) {
                    $recType = 'in_progress';
                    $recReason = "📖 Continue where you left off";
                    $detReason = "You have progress saved in this lesson. Tap to resume!";
                } elseif (!empty($coveredSkills)) {
                    $recType = 'weak_skill_practice';
                    $displaySkills = implode(', ', array_column($coveredSkills, 'display_name'));
                    $recReason = "⚡ Practice these weak signs: {$displaySkills}";
                    $detReason = "This lesson will help you practice weak skills ({$displaySkills}) to build mastery.";
                } else {
                    $recType = 'goal_match';
                    if ($conceptsStr) {
                        $recReason = "🎯 Step to master {$goalDisplay}: Teaches {$conceptsStr}";
                        $detReason = "This lesson covers {$conceptsStr} to help you achieve your {$goalDisplay} goal.";
                    } else {
                        $recReason = "🎯 Recommended for your goal: {$goalDisplay}";
                        $detReason = "This lesson is recommended as part of your {$goalDisplay} learning path.";
                    }
                }

                $formattedLessons[] = [
                    'assignment_id' => $assignment->id ?? null,
                    'lesson_id' => $lesson->lesson_id,
                    'title' => $lesson->title,
                    'description' => $lesson->description,
                    'difficulty' => $lesson->difficulty,
                    'module_id' => $lesson->module_id,
                    'module_title' => $lesson->module->title ?? null,
                    'module_order' => $lesson->module_order ?? 0,
                    'status' => $statusStr,
                    'score' => $highestScore,
                    'total_steps' => $lesson->contents->count() + ($lesson->quiz ? 1 : 0),
                    'has_quiz' => $lesson->quiz ? true : false,
                    'done' => $isDone,
                    'in_progress' => $isInProgress,
                    'locked' => $isLocked,
                    'active' => $isActive,
                    'recommendation_reason' => $recReason,
                    'recommendation_type' => $recType,
                    'detailed_reason' => $detReason,
                    'covered_skills' => $coveredSkills,
                    'priority' => count($coveredSkills),
                    'is_alphabet_lesson' => $item['flags']['is_alphabet'],
                    'is_number_lesson' => $item['flags']['is_number'],
                    'is_greeting_lesson' => $item['flags']['is_greeting'],
                    'lesson_concepts' => $item['concepts'],
                ];
            }

            // Limit to 20 lessons
            $formattedLessons = array_slice($formattedLessons, 0, 20);

            // ============================================================
            // 6. GET DAILY CHALLENGE INFO & XP
            // ============================================================
            $xpService = new XPService();
            $xpService->updateStreak($student);

            $dailyChallenge = null;
            try {
                $challengeService = new DailyChallengeService($xpService);
                $challenge = $challengeService->generateDailyChallenge($student);
                if ($challenge) {
                    $dailyChallenge = [
                        'id' => $challenge->challenge_id,
                        'theme' => $challenge->theme,
                        'is_completed' => (bool) $challenge->is_completed,
                    ];
                }
            } catch (\Exception $e) {
                // Ignore challenge error
            }

            $goalDisplay = str_replace('_', ' ', $goal);
            $unlockMessage = null;
            if (!empty($lockedModuleNames)) {
                $unlockMessage = "Complete current accessible modules to unlock more lessons in your learning goal: {$goalDisplay}";
            }

            return response()->json([
                'success' => true,
                'learning_path' => [
                    'fsl_level' => $level,
                    'learning_goal' => $goal,
                    'goal_mastered' => false,
                    'unlock_message' => $unlockMessage,
                ],
                'mastery_summary' => $overallMastery,
                'weak_skills' => $weakSkills,
                'lessons' => $formattedLessons,
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
                'daily_challenge' => $dailyChallenge,
            ]);

        } catch (\Exception $e) {
            \Log::error('Adaptive recommendations failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
/**
 * Classify a lesson's extracted concepts against the four goal categories.
 * Single source of truth for this — previously duplicated inline in the
 * format step, and CASE A2 needs the exact same classification to decide
 * whether a lesson matches the student's stated goal.
 */
private function classifyLessonGoalFlags(array $lessonConcepts): array
{
    $alphabetLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    $numbers = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];
    $greetings = ['HELLO', 'GOOD MORNING', 'GOOD AFTERNOON', 'GOOD NIGHT', 'GOODBYE', 'THANK YOU', 'SEE YOU TOMORROW', 'HOW ARE YOU', 'NICE TO MEET YOU'];

    $isAlphabet = false;
    $isNumber = false;
    $isGreeting = false;

    foreach ($lessonConcepts as $concept) {
        $conceptUpper = strtoupper($concept);
        if (in_array($conceptUpper, $alphabetLetters, true)) {
            $isAlphabet = true;
        }
        if (in_array($conceptUpper, $numbers, true)) {
            $isNumber = true;
        }
        if (in_array($conceptUpper, $greetings, true)) {
            $isGreeting = true;
        }
    }

    // Proxy for the "Classroom_Words" goal: any lesson that teaches a
    // real, recognized concept but isn't alphabet/number/greeting. This is
    // a stopgap — the clean fix is tagging classroom-word gestures in the
    // `gestures` table with their own category so this can be a direct
    // lookup instead of an exclusion.
    $isOther = !empty($lessonConcepts) && !$isAlphabet && !$isNumber && !$isGreeting;

    return [
        'is_alphabet' => $isAlphabet,
        'is_number' => $isNumber,
        'is_greeting' => $isGreeting,
        'is_other' => $isOther,
    ];
}

/**
 * Does a lesson (via its already-classified goal flags) match the
 * student's stated learning goal?
 */
private function lessonMatchesGoal(string $goal, array $flags): bool
{
    switch ($goal) {
        case 'Alphabet_Numbers':
            return $flags['is_alphabet'] || $flags['is_number'];
        case 'Fingerspelling':
            // Fingerspelling requires alphabet mastery + practice
            return $flags['is_alphabet'];
        case 'Greetings_FSL_Words':
            // Greetings + conversational words
            return $flags['is_greeting'] || $flags['is_other'];
        default: // 'Everything'
            return true;
    }
}
/**
 * Extract all gesture concepts from a lesson
 * ONLY extracts from RELIABLE sources:
 * - gesture_demo content (gesture_name field)
 * - gesture type quiz questions (gesture_data)
 * - drag_drop pairs (which contain gesture names)
 * 
 * Does NOT scan random text/titles/descriptions to avoid false positives
 */
private function extractLessonConcepts($lesson): array
{
    $concepts = [];
    
    // 1. ✅ HIGHEST CONFIDENCE: gesture_demo content
    foreach ($lesson->contents as $content) {
        if ($content->content_type === 'gesture_demo' && !empty($content->gesture_name)) {
            $name = strtoupper(trim($content->gesture_name));
            $concepts[] = $name;
        }
    }
    
    // 2. ✅ HIGH CONFIDENCE: gesture type quiz questions
    if ($lesson->quiz) {
        foreach ($lesson->quiz->questions as $question) {
            // Gesture type questions
            if ($question->question_type === 'gesture' && !empty($question->gesture_data)) {
                $gestureData = is_string($question->gesture_data) 
                    ? json_decode($question->gesture_data, true) 
                    : $question->gesture_data;
                
                if (!empty($gestureData['gesture_ids'])) {
                    $idToGesture = $this->getIdToGestureMap();
                    foreach ($gestureData['gesture_ids'] as $id) {
                        if (isset($idToGesture[$id])) {
                            $concepts[] = strtoupper($idToGesture[$id]);
                        }
                    }
                }
            }
            
            // 3. ✅ MEDIUM CONFIDENCE: drag_drop pairs (they contain actual gesture names)
            if ($question->question_type === 'drag_drop' && !empty($question->drag_drop_pairs)) {
                $pairs = is_string($question->drag_drop_pairs) 
                    ? json_decode($question->drag_drop_pairs, true) 
                    : $question->drag_drop_pairs;
                
                if (is_array($pairs)) {
                    foreach ($pairs as $pair) {
                        $left = strtoupper(trim($pair['left_text'] ?? $pair['left'] ?? ''));
                        $right = strtoupper(trim($pair['right_text'] ?? $pair['right'] ?? ''));
                        if ($left && $this->isValidConcept($left)) $concepts[] = $left;
                        if ($right && $this->isValidConcept($right)) $concepts[] = $right;
                    }
                }
            }
        }
    }
    
    // Remove duplicates and empty values
    $concepts = array_unique(array_filter($concepts));
    
    return $concepts;
}

/**
 * Validate if a concept is a valid FSL gesture concept
 * This prevents matching random text like "dvd" as "D"
 */
private function isValidConcept($concept): bool
{
    $conceptUpper = strtoupper(trim($concept));
    return in_array($conceptUpper, $this->getGestureVocabulary(), true);
}

/**
 * The set of every real gesture name in the database, uppercased. This
 * replaces a hardcoded whitelist that (a) silently dropped anything not
 * on the list — including every classroom-word gesture, since none were
 * ever added — and (b) had to be hand-maintained every time a teacher
 * added a new gesture. Cached per-request since it's read in a loop.
 */
private function getGestureVocabulary(): array
{
    static $vocabulary = null;
    if ($vocabulary === null) {
        $vocabulary = DB::table('gestures')
            ->pluck('name')
            ->map(fn($name) => strtoupper(trim($name)))
            ->unique()
            ->values()
            ->toArray();
    }
    return $vocabulary;
}

/**
 * Map gesture IDs to their display names, read straight from the
 * gestures table so it can never drift out of sync with real IDs the
 * way a hardcoded 1-61 map can (e.g. a newly added gesture with id 62
 * was previously invisible to concept extraction until someone
 * remembered to hand-edit this array).
 */
private function getIdToGestureMap(): array
{
    static $map = null;
    if ($map === null) {
        $map = DB::table('gestures')
            ->pluck('name', 'gesture_id')
            ->map(fn($name) => strtoupper(trim($name)))
            ->toArray();
    }
    return $map;
}

/**
 * GET /api/student/mastery
 * Get detailed mastery data for the student
 */
public function getMasteryData(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $masteryService = new \App\Services\MasteryService();
        
        $weakSkills = $masteryService->getWeakSkills($student->student_id, 0.6);
        $neverPracticed = $masteryService->getNeverPracticedSkills($student->student_id, 20);
        $overallMastery = $masteryService->getOverallMastery($student->student_id);
        
        // Get detailed skill performance
        $performances = GesturePerformance::where('student_id', $student->student_id)
            ->with('gesture')
            ->get();

        $skillDetails = $performances->map(function ($perf) use ($masteryService) {
            $mastery = ($perf->successful_attempts + 1) / ($perf->attempts + 2);
            return [
                'gesture_id' => $perf->gesture_id,
                'name' => $perf->gesture->name ?? 'Unknown',
                'display_name' => $perf->gesture->display_name ?? $perf->gesture->name,
                'mastery' => round($mastery, 2),
                'mastery_level' => $masteryService->getMasteryLevel($mastery),
                'attempts' => $perf->attempts,
                'successes' => $perf->successful_attempts,
                'wrong_attempts' => $perf->wrong_attempts,
                'accuracy' => $perf->attempts > 0 
                    ? round(($perf->successful_attempts / $perf->attempts) * 100, 1)
                    : 0,
            ];
        });

        return response()->json([
            'success' => true,
            'overall' => $overallMastery,
            'weak_skills' => $weakSkills,
            'never_practiced' => $neverPracticed,
            'all_skills' => $skillDetails,
            'recommendations' => [
                'next_skill' => $masteryService->getNextRecommendedSkill(
                    $student->student_id,
                    $student->learningPath->learning_goal ?? 'Everything'
                ),
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
 * POST /api/student/mastery/update
 * Update mastery after a practice session (real-time)
 */
public function updateMasteryAfterPractice(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'gesture_id' => 'required|exists:gestures,gesture_id',
            'attempts' => 'required|integer|min:1',
            'successes' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update or create performance
        $performance = GesturePerformance::updateOrCreate(
            [
                'student_id' => $student->student_id,
                'gesture_id' => $request->gesture_id,
            ],
            [
                'attempts' => DB::raw('attempts + ' . $request->attempts),
                'successful_attempts' => DB::raw('successful_attempts + ' . $request->successes),
                'wrong_attempts' => DB::raw('wrong_attempts + (' . $request->attempts . ' - ' . $request->successes . ')'),
                'last_attempt_at' => now(),
            ]
        );

        // Refresh to get updated values
        $performance->refresh();

        $masteryService = new \App\Services\MasteryService();
        $masteryData = $masteryService->updateMasteryAfterPractice(
            $student->student_id,
            $request->gesture_id
        );

        // Check if this unlocks anything
        $unlockedLessons = [];
        if ($masteryData['mastery'] >= 0.8) {
            // Check if there are any lessons that require this gesture
            $nextLessons = Lesson::where('status', 'published')
            ->whereNull('deleted_at')  // ✅ EXCLUDE SOFT DELETED LESSONS
            ->whereHas('contents', function($query) use ($request) {
                $query->where('gesture_name', $request->gesture_name)
                      ->where('content_type', 'gesture_demo');
            })
            ->whereNotIn('lesson_id', function($q) use ($student) {
                $q->select('lesson_id')
                  ->from('lesson_assignments')
                  ->where('student_id', $student->student_id)
                  ->where('status', 'completed');
            })
            ->limit(2)
            ->get();
            foreach ($nextLessons as $lesson) {
                $assignment = LessonAssignment::where('student_id', $student->student_id)
                    ->where('lesson_id', $lesson->lesson_id)
                    ->first();
                
                if ($assignment && $assignment->is_locked) {
                    $assignment->is_locked = false;
                    $assignment->save();
                    $unlockedLessons[] = $lesson->title;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Mastery updated successfully',
            'gesture' => $masteryData,
            'unlocked_lessons' => $unlockedLessons,
            'total_xp' => $student->total_xp ?? 0,
            'level' => $student->level ?? 1,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Builds the searchable text for a lesson: its own title/description,
 * every content block's title/content_text, PLUS every quiz question's
 * text — AND, where available, the REAL category resolved from the
 * gestures/gesture_modules tables (via lesson_contents.gesture_name and
 * quiz_questions.gesture_data->module_id). That resolved category is a
 * proper tag the teacher already assigned, not a guess — so it's
 * weighted the same as any other haystack text but is far more reliable
 * than scanning a lesson's own (often generic) title.
 */
private function buildLessonSearchText($lesson, $module, $gesturesByName = null, $gestureModulesById = null): string
{
    $gesturesByName = $gesturesByName ?? collect();
    $gestureModulesById = $gestureModulesById ?? collect();

    $parts = [
        $module->title ?? '',
        $lesson->title ?? '',
        $lesson->description ?? '',
    ];

    if ($lesson->relationLoaded('contents') && $lesson->contents) {
        foreach ($lesson->contents as $content) {
            $parts[] = $content->title ?? '';
            $parts[] = $content->content_text ?? '';
            $parts[] = $content->gesture_name ?? '';

            if ($content->content_type === 'gesture_demo' && !empty($content->gesture_name)) {
                $gesture = $gesturesByName->get(strtolower($content->gesture_name));
                if ($gesture) {
                    $gestureModule = $gestureModulesById->get($gesture->module_id);
                    if ($gestureModule) {
                        $parts[] = $gestureModule->name ?? '';
                        $parts[] = $gestureModule->display_name ?? '';
                    }
                }
            }
        }
    }

    if ($lesson->relationLoaded('quiz') && $lesson->quiz && $lesson->quiz->relationLoaded('questions') && $lesson->quiz->questions) {
        foreach ($lesson->quiz->questions as $question) {
            $parts[] = $question->question_text ?? '';

            if ($question->question_type === 'gesture' && !empty($question->gesture_data)) {
                $gestureData = is_string($question->gesture_data)
                    ? json_decode($question->gesture_data, true)
                    : $question->gesture_data;
                $moduleId = $gestureData['module_id'] ?? null;
                if ($moduleId) {
                    $gestureModule = $gestureModulesById->get($moduleId);
                    if ($gestureModule) {
                        $parts[] = $gestureModule->name ?? '';
                        $parts[] = $gestureModule->display_name ?? '';
                    }
                }
            }
        }
    }

    return strtolower(implode(' ', $parts));
}

/**
 * Loose keyword match between a student's learning_goal and everything
 * a lesson actually contains — title, description, content blocks, quiz
 * question text, AND the real gesture category resolved from the
 * gestures/gesture_modules tables. Mirrors the same category-guessing
 * convention already used on the frontend (getCategoryIcon in
 * lessons.tsx), just with a wider, more reliable net.
 */
private function lessonMatchesLearningGoal($goal, $lesson, $module, $gesturesByName = null, $gestureModulesById = null): bool
{
    if ($goal === 'Everything' || !$goal) {
        return true;
    }

    $haystack = $this->buildLessonSearchText($lesson, $module, $gesturesByName, $gestureModulesById);

    $keywordMap = [
        'Alphabet_Numbers' => ['alphabet', 'letter', 'number', 'count'],
        'Greetings' => ['greet', 'hello', 'goodbye', 'good morning', 'good evening', 'welcome', 'nice to meet'],
        'Classroom_Words' => ['classroom', 'school', 'class', 'teacher', 'student'],
    ];

    $keywords = $keywordMap[$goal] ?? [];
    foreach ($keywords as $keyword) {
        if (str_contains($haystack, $keyword)) {
            return true;
        }
    }
    return false;
}

/**
 * Get accessible module levels based on student's mastery level
 */
private function getAccessibleModuleLevels(string $studentLevel): array
{
    $normalizedLevel = strtolower($studentLevel);
    
    // 🔥 FIX: If student is "completed" or "graduated", all levels are accessible
    if (in_array($normalizedLevel, ['completed', 'graduated'])) {
        return ['beginner', 'intermediate', 'advanced'];
    }
    
    $levelMap = [
        'beginner' => ['beginner'],
        'intermediate' => ['beginner', 'intermediate'],
        'advanced' => ['beginner', 'intermediate', 'advanced'],
    ];

    return $levelMap[$normalizedLevel] ?? ['beginner'];
}
/**
 * Check if a student can access a specific module based on their level
 */
private function canAccessModule($studentLevel, $moduleLevel): bool
{
    $accessibleLevels = $this->getAccessibleModuleLevels($studentLevel);
    return in_array(strtolower($moduleLevel), $accessibleLevels);
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

        // 🔥 GET STUDENT'S MASTERY LEVEL
        $studentLevel = strtolower($student->fsl_mastery_level ?? 'beginner');

        // 🔥 Determine which module levels are accessible
        $accessibleLevels = $this->getAccessibleModuleLevels($studentLevel);

        // 🔥 GET ALL MODULES
        $allModules = Module::where('teacher_id', $student->teacher_id)
            ->orderBy('module_order')
            ->get();

        // 🔥 ADD: Get all lesson assignments for THIS student
        $assignments = LessonAssignment::where('student_id', $student->student_id)
            ->whereHas('lesson', function($query) {
                $query->where('status', 'published')
                      ->whereNull('deleted_at');
            })
            ->with(['lesson' => function ($query) {
                $query->where('status', 'published')
                      ->whereNull('deleted_at')
                      ->with(['contents', 'quiz', 'module']);
            }])
            ->get();

        // Remove any assignments where lesson is null (safety check)
        $assignments = $assignments->filter(function($assignment) {
            return $assignment->lesson !== null;
        });

        // Group lessons by module
        $modulesMap = [];
        $previousModuleCompleted = true;
        $lastModuleLevel = null;
        
        // First, add ALL modules with their lessons
        foreach ($allModules as $module) {
            $moduleId = $module->module_id;
            $moduleLevel = strtolower($module->mastery_level ?? 'beginner');
            
            // Check if this module level is accessible based on student's mastery level
            $isLevelAccessible = in_array($moduleLevel, $accessibleLevels);
            
            // If starting a new accessible level that matches student's assessment starting level, unlock its first module
            if ($lastModuleLevel !== null && $moduleLevel !== $lastModuleLevel) {
                if ($isLevelAccessible && strtolower($studentLevel) === $moduleLevel) {
                    $previousModuleCompleted = true;
                }
            }
            $lastModuleLevel = $moduleLevel;
            
            $isAccessible = $isLevelAccessible && $previousModuleCompleted;
            
            // Get lessons for this module from assignments
            $moduleLessons = $assignments->filter(function($assignment) use ($moduleId) {
                return $assignment->lesson && $assignment->lesson->module_id === $moduleId;
            })->sortBy(function($assignment) {
                return $assignment->lesson->module_order ?? 0;
            })->values();

            // Calculate module completion status (all published lessons passed >= 60%)
            $totalLessonsInModule = $moduleLessons->count();
            $completedLessonsInModule = 0;

            foreach ($moduleLessons as $assignment) {
                $lessonItem = $assignment->lesson;
                if (!$lessonItem) continue;

                $scoreVal = DB::table('quiz_attempts as qa')
                    ->join('quizzes as q', 'qa.quiz_id', '=', 'q.quiz_id')
                    ->where('qa.student_id', $student->student_id)
                    ->where('q.lesson_id', $lessonItem->lesson_id)
                    ->where('qa.status', 'completed')
                    ->max('qa.percentage');

                if ($scoreVal === null) {
                    $prog = DB::table('student_lesson_progress')
                        ->where('student_id', $student->student_id)
                        ->where('lesson_id', $lessonItem->lesson_id)
                        ->first();
                    $scoreVal = $prog ? $prog->quiz_score : null;
                }

                if ($scoreVal !== null && $scoreVal >= 60) {
                    $completedLessonsInModule++;
                }
            }

            $isCurrentModuleComplete = ($totalLessonsInModule > 0) && ($completedLessonsInModule === $totalLessonsInModule);
            $previousModuleCompleted = $isCurrentModuleComplete;
            
            // Get quiz result for this module
            $quizResult = null;
            if ($moduleId) {
                $quizResult = DB::table('module_quiz_results')
                    ->where('student_id', $student->student_id)
                    ->where('module_id', $moduleId)
                    ->orderByDesc('percentage')
                    ->first();
            }
            
            $modulesMap[$moduleId] = [
                'module_id' => $moduleId,
                'title' => $module->title,
                'description' => $module->description,
                'mastery_level' => $module->mastery_level,
                'is_locked' => !$isAccessible,  // 🔥 TRUE if student can't access
                'requires_level' => $moduleLevel,  // What level is required
                'student_level' => $studentLevel,  // Student's current level
                'quiz_passed' => $quizResult ? (bool) $quizResult->passed : false,
                'quiz_score' => $quizResult ? $quizResult->percentage : null,
                'lessons' => [],
                'module_order' => $module->module_order ?? 0,
            ];
            
            // Add lessons to the module
            foreach ($moduleLessons as $assignment) {
                $lesson = $assignment->lesson;
                
                if (!$lesson) {
                    continue;
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
                
                // 🔥 CRITICAL FIX: If module is locked, ALL lessons are locked
                $isLessonLocked = !$isAccessible || (bool) $assignment->is_locked;
                
                 // 🔥 ADD THIS: Determine lock reason
    $lockReason = null;
    $lockReasonData = null;
    
    if ($isLessonLocked) {
        if (!$isAccessible) {
            $lockReason = 'module_locked';
        } else {
            // Check if previous lesson exists and is not completed
            // We need to find the previous lesson in this module
            $previousLesson = null;
            $lessonOrder = $lesson->module_order ?? 0;
            
            // Find the previous lesson by module_order
            $previousLesson = Lesson::where('module_id', $lesson->module_id)
                ->where('module_order', '<', $lessonOrder)
                ->where('status', 'published')
                ->orderBy('module_order', 'desc')
                ->first();
            
            if ($previousLesson) {
                // Check if previous lesson is completed
                $prevAssignment = LessonAssignment::where('student_id', $student->student_id)
                    ->where('lesson_id', $previousLesson->lesson_id)
                    ->first();
                
                $prevScore = DB::table('quiz_attempts as qa')
                    ->join('quizzes as q', 'qa.quiz_id', '=', 'q.quiz_id')
                    ->where('qa.student_id', $student->student_id)
                    ->where('q.lesson_id', $previousLesson->lesson_id)
                    ->where('qa.status', 'completed')
                    ->max('qa.percentage');
                
                $isPrevCompleted = $prevScore !== null && $prevScore >= 60;
                $isPrevCompleted = $isPrevCompleted || ($prevAssignment && $prevAssignment->status === 'completed' && $prevAssignment->score >= 60);
                
                if (!$isPrevCompleted) {
                    $lockReason = 'previous_lesson_required';
                    $lockReasonData = ['previous_lesson_title' => $previousLesson->title];
                }
            }
            
            if (!$lockReason) {
                $lockReason = 'unknown';
            }
        }
    }
    
                $modulesMap[$moduleId]['lessons'][] = [
                    'is_checkpoint_exam' => false,
                    'assignment_id' => $assignment->id,
                    'lesson_id' => $lesson->lesson_id,
                    'title' => $lesson->title,
                    'description' => $lesson->description,
                    'lesson_type' => $lesson->lesson_type,
                    'difficulty' => $lesson->difficulty,
                    'status' => $status,
                    'score' => $highestScore,
                    'is_locked' => $isLessonLocked,
                    'assigned_at' => $assignment->assigned_at,
                    'module_order' => $lesson->module_order ?? 0,
                    'total_steps' => $lesson->contents->count() + ($lesson->quiz ? 1 : 0),
                    'has_quiz' => $lesson->quiz ? true : false,
                ];
            }
            
            // If no lessons from assignments, but module has lessons, add them
            if (empty($modulesMap[$moduleId]['lessons'])) {
                $moduleLessons = Lesson::where('module_id', $moduleId)
                    ->where('status', 'published')
                    ->whereNull('deleted_at')
                    ->orderBy('module_order')
                    ->get();
                
                foreach ($moduleLessons as $lesson) {
                    $modulesMap[$moduleId]['lessons'][] = [
                        'is_checkpoint_exam' => false,
                        'assignment_id' => null,
                        'lesson_id' => $lesson->lesson_id,
                        'title' => $lesson->title,
                        'description' => $lesson->description,
                        'lesson_type' => $lesson->lesson_type,
                        'difficulty' => $lesson->difficulty,
                        'status' => !$isAccessible ? 'locked' : 'pending',
                        'score' => null,
                        'is_locked' => !$isAccessible, // 🔥 Locked if module is locked
                        'assigned_at' => null,
                        'module_order' => $lesson->module_order ?? 0,
                        'total_steps' => $lesson->contents->count() + ($lesson->quiz ? 1 : 0),
                        'has_quiz' => $lesson->quiz ? true : false,
                    ];
                }
            }
        }

        // 🔥 Attach published Checkpoint Exams assigned to this student for each module
     foreach ($modulesMap as $mId => &$moduleData) {
    if (!$mId) continue;
    
    $isModuleLocked = $moduleData['is_locked'] ?? true;

    $checkpointExams = CheckpointExam::where('module_id', $mId)
        ->where('status', 'published')
        ->whereHas('assignments', function($query) use ($student) {
            $query->where('student_id', $student->student_id);
        })
        ->with(['questions'])
        ->get();

    foreach ($checkpointExams as $exam) {
        $sourceLessonIds = $exam->questions->pluck('source_lesson_id')->filter()->unique();
        
        // Get the max module order from source lessons
        $maxSourceOrder = Lesson::whereIn('lesson_id', $sourceLessonIds)->max('module_order');

        if ($maxSourceOrder === null) {
            $maxSourceOrder = Lesson::where('module_id', $mId)->where('status', 'published')->max('module_order') ?? 0;
        }
        $examOrder = (float)$maxSourceOrder + 0.5;

        $bestAttempt = DB::table('checkpoint_exam_attempts')
            ->where('exam_id', $exam->exam_id)
            ->where('student_id', $student->student_id)
            ->where('status', 'completed')
            ->orderByDesc('percentage')
            ->first();

        $examAssignment = CheckpointExamAssignment::where('exam_id', $exam->exam_id)
            ->where('student_id', $student->student_id)
            ->first();

        $isPassed = $bestAttempt && $bestAttempt->percentage >= 60;

        // 🔥 FIX: Default to LOCKED
        $isExamLocked = true;

        // 1. If module is locked, exam is locked
        if ($isModuleLocked) {
            $isExamLocked = true;
        }
        // 2. If already passed, unlock
        elseif ($isPassed) {
            $isExamLocked = false;
        }
        // 3. If assignment explicitly says it's unlocked, keep it unlocked
        elseif ($examAssignment && !$examAssignment->is_locked) {
            $isExamLocked = false;
        }
        // 4. Check if ALL source lessons are completed with ≥60%
        else {
            $totalSourceLessons = $sourceLessonIds->count();
            
            if ($totalSourceLessons > 0) {
                $completedCount = LessonAssignment::where('student_id', $student->student_id)
                    ->whereIn('lesson_id', $sourceLessonIds)
                    ->where('status', 'completed')
                    ->where('score', '>=', 60)  // 🔥 ADD THIS: Must pass with ≥60%
                    ->count();
                
                // 🔥 UNLOCK ONLY IF ALL source lessons are completed
                if ($completedCount >= $totalSourceLessons) {
                    $isExamLocked = false;
                    if ($examAssignment && $examAssignment->is_locked) {
                        $examAssignment->is_locked = false;
                        $examAssignment->save();
                    }
                }
            } else {
                // No source lessons? Use the current lesson status in the module
                // Check if all lessons in the module are completed
                $allLessonsCompleted = true;
                $moduleLessons = Lesson::where('module_id', $mId)
                    ->where('status', 'published')
                    ->pluck('lesson_id');
                
                foreach ($moduleLessons as $lessonId) {
                    $assignment = LessonAssignment::where('student_id', $student->student_id)
                        ->where('lesson_id', $lessonId)
                        ->where('status', 'completed')
                        ->where('score', '>=', 60)
                        ->first();
                    if (!$assignment) {
                        $allLessonsCompleted = false;
                        break;
                    }
                }
                
                if ($allLessonsCompleted && $moduleLessons->count() > 0) {
                    $isExamLocked = false;
                    if ($examAssignment && $examAssignment->is_locked) {
                        $examAssignment->is_locked = false;
                        $examAssignment->save();
                    }
                }
            }
        }

        $examStatus = $isPassed ? 'completed' : ($bestAttempt ? 'failed' : ($examAssignment->status ?? 'pending'));

      

        // 🔥 Update the assignment in the database if needed
        if ($examAssignment) {
            if ($isExamLocked && !$examAssignment->is_locked) {
                $examAssignment->is_locked = true;
               
                $examAssignment->save();
            } elseif (!$isExamLocked && $examAssignment->is_locked) {
                $examAssignment->is_locked = false;
                $examAssignment->save();
            }
        }

        $moduleData['lessons'][] = [
            'is_checkpoint_exam' => true,
            'assignment_id' => $examAssignment->assignment_id ?? null,
            'lesson_id' => 'exam_' . $exam->exam_id,
            'exam_id' => $exam->exam_id,
            'title' => $exam->title,
            'description' => $exam->description ?? 'Checkpoint Exam for this module',
            'lesson_type' => 'checkpoint_exam',
            'difficulty' => 'Exam',
            'status' => $examStatus,
            'score' => $bestAttempt ? $bestAttempt->percentage : null,
            'is_locked' => (bool)$isExamLocked,  // ← This is what the frontend reads
            'assigned_at' => $examAssignment->assigned_at ?? $exam->published_at,
            'module_order' => $examOrder,
            'total_steps' => $exam->questions->count(),
            'total_points' => $exam->total_points,
            'passing_score' => $exam->passing_score,
            'total_questions' => $exam->questions->count(),
            'has_quiz' => true,
        ];
    }
}
        unset($moduleData);
        
        // Sort lessons & checkpoint exams within each module by module_order
        foreach ($modulesMap as &$module) {
            usort($module['lessons'], function($a, $b) {
                $orderA = (float)($a['module_order'] ?? 0);
                $orderB = (float)($b['module_order'] ?? 0);
                if ($orderA == $orderB) return 0;
                return ($orderA < $orderB) ? -1 : 1;
            });
        }
        unset($module);
        
        // Sort modules by module_order (ASCENDING)
        $modulesMap = collect($modulesMap)->sortBy('module_order')->toArray();
        
        // Convert map to array
        $modules = array_values($modulesMap);

        $xpService = new XPService();
        $xpService->updateStreak($student);

        return response()->json([
            'success' => true,
            'modules' => $modules,
            'student' => [
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'fsl_mastery_level' => $student->fsl_mastery_level,
                'total_xp' => $student->total_xp ?? 0,
                'today_xp' => $xpService->getTodayXp($student),
                'level' => $student->level ?? 1,
                'streak_days' => $student->streak_days ?? 0,
                'teacher_id' => $student->teacher_id,
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

        // 🔥 ADD: Get all lesson assignments for this student ONLY for lessons that exist and are not deleted
        $assignments = LessonAssignment::where('student_id', $student->student_id)
            ->whereHas('lesson', function($query) {
                $query->where('status', 'published')
                      ->whereNull('deleted_at');  // ✅ EXCLUDE SOFT DELETED LESSONS
            })
            ->with(['lesson' => function ($query) {
                $query->where('status', 'published')
                      ->whereNull('deleted_at')  // ✅ EXCLUDE SOFT DELETED LESSONS
                      ->with(['contents', 'quiz', 'module']);
            }])
            ->orderBy('assigned_at', 'desc')
            ->get();

        // Remove any assignments where lesson is null (safety check)
        $assignments = $assignments->filter(function($assignment) {
            return $assignment->lesson !== null;
        })->sortBy(function($assignment) {
            $modOrder = $assignment->lesson->module->module_order ?? 9999;
            $lesOrder = $assignment->lesson->module_order ?? 9999;
            return sprintf('%05d-%05d', $modOrder, $lesOrder);
        })->values();

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
        $xpService->updateStreak($student);

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
            ->whereHas('lesson', function($query) {
                $query->whereNull('deleted_at');  // ✅ EXCLUDE SOFT DELETED LESSONS
            })
            ->first();

        if (! $assignment) {
            return response()->json(['error' => 'You do not have access to this lesson'], 403);
        }

        // Get the lesson with all content
        $lesson = Lesson::with(['contents', 'quiz.questions.options'])
            ->where('lesson_id', $lessonId)
            ->where('status', 'published')
            ->whereNull('deleted_at')  // ✅ EXCLUDE SOFT DELETED LESSONS
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

        // Format quiz - FIX: Include gesture_data and drag_drop_pairs
        $quiz = null;
        if ($lesson->quiz) {
            $questions = $lesson->quiz->questions->map(function ($question) {
                $questionData = [
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

                // ✅ ADD THIS: Include gesture_data if it exists
                if ($question->question_type === 'gesture' && !empty($question->gesture_data)) {
                    // If gesture_data is stored as JSON string, decode it
                    if (is_string($question->gesture_data)) {
                        $questionData['gesture_data'] = json_decode($question->gesture_data, true);
                    } else {
                        $questionData['gesture_data'] = $question->gesture_data;
                    }
                }

                // ✅ ADD THIS: Include drag_drop_pairs if it exists
                if ($question->question_type === 'drag_drop' && !empty($question->drag_drop_pairs)) {
                    if (is_string($question->drag_drop_pairs)) {
                        $questionData['drag_drop_pairs'] = json_decode($question->drag_drop_pairs, true);
                    } else {
                        $questionData['drag_drop_pairs'] = $question->drag_drop_pairs;
                    }
                }

                return $questionData;
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

                // 🎯 Finishing a lesson today counts toward today's
                // "Complete a Lesson" goal — even if this exact lesson was
                // already completed on a previous day.
                $challengeService = new DailyChallengeService(new XPService());
                $challengeService->recordProgressByType($student, 'lesson_completion', 1);
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
        $xpService->updateStreak($student);
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

            // 🎯 Passing a lesson's quiz today completes today's "Complete a
            // Lesson" goal too — regardless of whether this lesson was
            // completed on a previous day.
            if ($status === 'completed') {
                $challengeService = new DailyChallengeService($xpService);
                $challengeService->recordProgressByType($student, 'lesson_completion', 1);
            }
        }

        // 🎯 A 100% score today counts toward today's "Perfect Score" goal,
        // even if this quiz was already aced on a previous day.
        if ($isPerfect) {
            $challengeService = new DailyChallengeService($xpService);
            $challengeService->recordProgressByType($student, 'quiz_attempt', 1);
        }

        $student->refresh();

        // ─── 🔔 NOTIFY TEACHER ──────────────────────────────────────────
        try {
            $lesson     = Lesson::find($lessonId);
            $lessonName = $lesson ? $lesson->title : "Lesson #{$lessonId}";
            $studentName = $student->first_name . ' ' . $student->last_name;
            $pct         = (int) round($request->percentage);
            $emoji       = $pct === 100 ? '🏆' : ($pct >= 80 ? '🎯' : ($pct >= 60 ? '✅' : '❌'));
            $statusLabel = $status === 'completed' ? 'passed' : 'failed';

            $this->notifyTeacher(
                student:   $student,
                type:      'quiz_answered',
                title:     "{$emoji} {$studentName} answered a quiz",
                message:   "Scored {$pct}% on \"{$lessonName}\" ({$statusLabel}) — Attempt #{$attemptNumber}",
                data:      [
                    'student_id'     => $student->student_id,
                    'lesson_id'      => $lessonId,
                    'lesson_title'   => $lessonName,
                    'score'          => $request->score,
                    'percentage'     => $pct,
                    'status'         => $status,
                    'attempt_number' => $attemptNumber,
                    'xp_earned'      => $xpEarned,
                ],
                actionUrl: '/students/' . $student->student_id,
            );
        } catch (\Exception $e) {
            // never break the student flow
        }

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

        $xpService->updateStreak($student);

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

        // First, verify the lesson exists and is active/published
        $lesson = Lesson::where('lesson_id', $lessonId)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->first();
        if (!$lesson) {
            return response()->json(['error' => 'Lesson not found or not active'], 404);
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
        ->whereHas('lesson', function($query) {
            $query->whereNull('deleted_at');  // ✅ ONLY SYNC FOR EXISTING LESSONS
        })
        ->with('lesson')
        ->get();
    
    // Remove any assignments where lesson is null
    $assignments = $assignments->filter(function($assignment) {
        return $assignment->lesson !== null;
    });
    
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
 * Now with optional hint usage tracking
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
            'module_name' => 'required|string|in:alphabet_part1,alphabet_part2,numbers,level1_numbers,level2_greetings,level3_survival',
            'letter_performances' => 'required|array',
            'letter_performances.*.letter' => 'required|string',
            'letter_performances.*.attempts' => 'required|integer|min:0',
            'letter_performances.*.wrong_attempts' => 'required|integer|min:0',
            'letter_performances.*.success_count' => 'required|integer|min:0',
            'letter_performances.*.consecutive_wrong' => 'nullable|integer|min:0',
            'session_id' => 'nullable|string',
            'hint_usage' => 'nullable|array',
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

        // ─── SAVE HINT USAGE (if provided) ──────────────────────────────
        $hintUsageData = [];
        if ($request->has('hint_usage') && !empty($request->hint_usage)) {
            // ✅ AGGREGATE HINTS BY LETTER
            $aggregatedHints = [];
            foreach ($request->hint_usage as $letter => $count) {
                if ($count > 0) {
                    if (!isset($aggregatedHints[$letter])) {
                        $aggregatedHints[$letter] = 0;
                    }
                    $aggregatedHints[$letter] += $count;
                }
            }

            // Save aggregated data (one row per letter per session)
            foreach ($aggregatedHints as $letter => $totalCount) {
                $hintRecord = GestureHintUsage::updateOrCreate(
                    [
                        'student_id' => $student->student_id,
                        'module_name' => $request->module_name,
                        'letter' => $letter,
                        'session_id' => $request->session_id ?? 'session_' . time(),
                    ],
                    [
                        'hint_count' => $totalCount,
                    ]
                );
                
                $hintUsageData[] = [
                    'letter' => $letter,
                    'count' => $totalCount,
                ];
            }
        }

        // ─── CHECK IF MODULE IS COMPLETE (has data for ALL letters) ──────
        // The module is complete when the student has performance data for ALL letters,
        // NOT when all letters are mastered.
        $moduleLetters = $this->getModuleLetters($request->module_name);

        // Get letters that have performance data (attempts > 0)
        $lettersWithData = collect($savedPerformances)
            ->filter(fn($p) => ($p['attempts'] ?? 0) > 0)
            ->pluck('letter')
            ->toArray();

        // Get mastered letters (is_mastered = true) for the summary
        $masteredLetters = collect($savedPerformances)
            ->where('is_mastered', true)
            ->pluck('letter')
            ->toArray();

        $hasDataForAllLetters = count(array_intersect($moduleLetters, $lettersWithData)) === count($moduleLetters);
        $masteredCount = count($masteredLetters);
        $totalLetters = count($moduleLetters);

        // ✅ TRIGGER NOTIFICATION when ALL letters have data (not just mastered)
        if ($hasDataForAllLetters && count($savedPerformances) > 0) {
            \Log::info('🎉 Module COMPLETE! (all letters have performance data)', [
                'student_id' => $student->student_id,
                'module' => $request->module_name,
                'letters_with_data' => $lettersWithData,
                'mastered_count' => $masteredCount,
                'total_letters' => $totalLetters,
            ]);
            
            $this->notifyTeacherAboutModuleCompletion(
                $student,
                $request->module_name,
                $savedPerformances,
                $hintUsageData,
                $lettersWithData,
                $masteredLetters,
                $totalAttempts,
                $totalCorrect,
                $totalWrong
            );
        }

        // Get module progress summary
        $progress = GesturePerformance::getModuleProgress(
            $student->student_id,
            $module->module_id
        );

        // XP and streak updates
        $xpService = new XPService();
        $xpService->updateStreak($student); 

        if (count($savedPerformances) > 0) {
            $challengeService = new DailyChallengeService($xpService);
            $challengeService->recordProgressByType($student, 'gesture_practice', count($savedPerformances));
        }

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
            'hint_usage' => $hintUsageData,
            'is_module_complete' => $hasDataForAllLetters,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Notify teacher that a student has completed a gesture module
 * (has data for all letters, regardless of mastery)
 */
private function notifyTeacherAboutModuleCompletion($student, $moduleName, $savedPerformances, $hintUsageData, $lettersWithData, $masteredLetters, $totalAttempts, $totalCorrect, $totalWrong)
{
    try {
        if (!$student->teacher_id) {
            \Log::info('No teacher_id for student ' . $student->student_id . ', skipping notification');
            return;
        }

        $studentName = $student->first_name . ' ' . $student->last_name;
        $displayModule = ucwords(str_replace('_', ' ', $moduleName));
        
        $totalLetters = count($lettersWithData);
        $masteredCount = count($masteredLetters);
        
        // Get struggling letters (letters with more wrong attempts than correct)
        $strugglingLetters = collect($savedPerformances)
            ->filter(fn($p) => ($p['wrong_attempts'] ?? 0) > ($p['successful_attempts'] ?? 0))
            ->pluck('letter')
            ->toArray();
        
        // ─── BUILD SIMPLE, CLEAN MESSAGE ──────────────────────────────
        $title = "📊 {$studentName} completed {$displayModule}";
        
        // Mastered letters
        $masteredDisplay = !empty($masteredLetters) 
            ? implode(' ', $masteredLetters) 
            : 'None';
        
        // Struggling letters
        $strugglingDisplay = !empty($strugglingLetters) 
            ? implode(' ', array_slice($strugglingLetters, 0, 5)) 
            : 'None';
        
        // Hints used
        $hintDisplay = '';
        $totalHints = 0;
        if (!empty($hintUsageData)) {
            $hintParts = [];
            foreach ($hintUsageData as $hint) {
                if ($hint['count'] > 0) {
                    $hintParts[] = "{$hint['letter']} ({$hint['count']}x)";
                    $totalHints += $hint['count'];
                }
            }
            if (!empty($hintParts)) {
                $hintDisplay = 'Hints used: ' . implode(', ', $hintParts);
            }
        }
        
        // Build the final message
        $message = "Completed {$displayModule}. ";
        $message .= "Letters Mastered: {$masteredDisplay}. ";
        
        if (!empty($hintDisplay)) {
            $message .= "{$hintDisplay}. ";
        }
        
        if (!empty($strugglingLetters)) {
            $message .= "Needs practice on: {$strugglingDisplay}";
        }

        // Create the notification with correct keys for the view
        TeacherNotification::createForTeacher(
            teacherId: $student->teacher_id,
            type: 'module_completed',
            title: $title,
            message: $message,
            data: [
                'student_id' => $student->student_id,
                'module_name' => $moduleName,
                'letters_mastered' => $masteredLetters,  // ✅ View uses this key
                'needs_practice' => $strugglingLetters,   // ✅ View uses this key
                'mastered_count' => $masteredCount,
                'total_letters' => $totalLetters,
                'hint_usage' => $hintUsageData,
                'hint_count' => $totalHints,
                'is_perfect' => ($masteredCount === $totalLetters),
                'session_id' => null,
                'completed_at' => now()->toISOString(),
            ],
            actionUrl: '/students/' . $student->student_id,
        );
        
        \Log::info("✅ Teacher notification sent for module completion", [
            'student' => $studentName,
            'module' => $moduleName,
            'letters' => $totalLetters,
            'mastered' => $masteredCount,
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Failed to send module completion notification: ' . $e->getMessage());
    }
}
/**
 * Helper method to get module letters
 * This makes the code more maintainable
 */
private function getModuleLetters(string $moduleName): array
{
    $letterMap = [
        'alphabet_part1' => ['A','B','C','D','E','F','G','H','I','J','K','L','M'],
        'alphabet_part2' => ['N','O','P','Q','R','S','T','U','V','W','X','Y','Z'],
        'numbers' => ['1','2','3','4','5','6','7','8','9','10'],
        'level1_numbers' => ['1','2','3','4','5','6','7','8','9','10'],
        'level2_greetings' => ['HELLO','THANK YOU','SEE YOU TOMORROW','HOW ARE YOU','NICE TO MEET YOU'],
        'level3_survival' => ['UNDERSTAND',"DON'T UNDERSTAND",'KNOW',"DON'T KNOW",'NO','YES','WRONG','CORRECT','SLOW','FAST'], // ✅ Fix this!
    ];
    
    return $letterMap[$moduleName] ?? [];
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
/**
 * Get gesture progress for the mobile app
 * GET /api/student/gesture-progress
 */
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
        
        // Get student's mastery level
        $studentLevel = strtolower($student->fsl_mastery_level ?? 'beginner');
        
        // Define which module levels are accessible based on student level
        $accessibleLevels = $this->getAccessibleModuleLevels($studentLevel);

        foreach ($modules as $module) {
            // Get all gestures in this module
            $gestureIds = $module->gestures->pluck('gesture_id');
            
            // Get student's performances for these gestures
            $performances = GesturePerformance::where('student_id', $student->student_id)
                                             ->whereIn('gesture_id', $gestureIds)
                                             ->get();

            $totalGestures = $gestureIds->count();
            
            // Count mastered AND proficient as "completed"
            $completedCount = $performances->whereIn('mastery_level', ['mastered', 'proficient'])->count();
            
            // Calculate progress percentage
            $progress = $totalGestures > 0 
                ? round(($completedCount / $totalGestures) * 100) 
                : 0;

            // Check if module is completed
            $isCompleted = $completedCount === $totalGestures && $totalGestures > 0;

            // 🔥 Check locking conditions with reasons
            $moduleLevel = strtolower($module->difficulty ?? 'beginner');
            $isLockedByLevel = !in_array($moduleLevel, $accessibleLevels);
            $isLockedByProgress = $this->isPreviousModuleLocked($student, $module);
            
            // Determine lock reason
            $lockReason = null;
            if ($isLockedByLevel) {
                $lockReason = [
                    'type' => 'level',
                    'message' => "Requires " . ucfirst($module->difficulty) . " level to unlock this module!",
                    'required_level' => ucfirst($module->difficulty),
                    'current_level' => ucfirst($student->fsl_mastery_level ?? 'beginner'),
                ];
            } elseif ($isLockedByProgress) {
                $previousModule = GestureModule::where('order', '<', $module->order)
                                              ->where('is_active', true)
                                              ->orderBy('order', 'desc')
                                              ->first();
                $lockReason = [
                    'type' => 'progress',
                    'message' => "Achieve higher performance on previous module to unlock!",
                    'previous_module' => $previousModule ? $previousModule->display_name : 'previous module',
                    'required_progress' => '40%',
                    'current_progress' => $progress,
                ];
            }
            
            // Module is locked if EITHER condition is true
            $isLocked = $isLockedByLevel || $isLockedByProgress;

            // Get XP for this module
            $xpAvailable = $module->xp_reward ?? 40;

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
                'requires_level' => $module->difficulty,
                'student_level' => $student->fsl_mastery_level,
                'lock_reason' => $lockReason, // 🔥 NEW: Include lock reason
                'mastery_breakdown' => [
                    'mastered' => $performances->where('mastery_level', 'mastered')->count(),
                    'proficient' => $performances->where('mastery_level', 'proficient')->count(),
                    'developing' => $performances->where('mastery_level', 'developing')->count(),
                    'needs_practice' => $performances->where('mastery_level', 'needs_practice')->count(),
                ],
            ];
        }

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->student_id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'total_xp' => $student->total_xp ?? 0,
                'level' => $student->level ?? 1,
                'fsl_mastery_level' => $student->fsl_mastery_level,
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
            $xpService->updateStreak($student);
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
 * Check if previous module is locked
 * 🔥 Uses 40% threshold (matches your model's "mastered" criteria)
 */
private function isPreviousModuleLocked($student, $module)
{
    // First module is always unlocked by progress
    if ($module->order === 1) {
        return false;
    }

    // Check if previous module is completed
    $previousModule = GestureModule::where('order', '<', $module->order)
                                  ->where('is_active', true)
                                  ->orderBy('order', 'desc')
                                  ->first();

    if (!$previousModule) {
        return false;
    }

    // Get previous module's gestures
    $gestureIds = $previousModule->gestures->pluck('gesture_id');
    $totalGestures = $gestureIds->count();
    
    if ($totalGestures === 0) {
        return false;
    }
    
    // Get all performances for these gestures
    $performances = GesturePerformance::where('student_id', $student->student_id)
                                     ->whereIn('gesture_id', $gestureIds)
                                     ->get()
                                     ->keyBy('gesture_id');
    
    // 🔥 Count mastered AND proficient as "completed" (matches your model)
    $completedCount = 0;
    $masteredCount = 0;
    $proficientCount = 0;
    
    foreach ($gestureIds as $gestureId) {
        $perf = $performances->get($gestureId);
        
        if ($perf && in_array($perf->mastery_level, ['mastered', 'proficient'])) {
            $completedCount++;
            if ($perf->mastery_level === 'mastered') {
                $masteredCount++;
            } else {
                $proficientCount++;
            }
        }
    }
    
    // 🎯 UNLOCK when 40% of gestures are "completed" (mastered OR proficient)
    // This matches your model's 40% threshold for "mastered"
    $unlockThreshold = 0.40; // 40% - matches your model
    
    // Calculate required count
    $requiredCompleted = max(1, ceil($totalGestures * $unlockThreshold));
    
    $isUnlocked = $completedCount >= $requiredCompleted;
    
    // Log for debugging if needed
    \Log::debug('Module unlock check', [
        'module' => $module->name,
        'total_gestures' => $totalGestures,
        'completed_count' => $completedCount,
        'mastered_count' => $masteredCount,
        'proficient_count' => $proficientCount,
        'required' => $requiredCompleted,
        'is_unlocked' => $isUnlocked,
    ]);
    
    return !$isUnlocked;
}

/**
 * Check if a module is locked for a student (DEPRECATED - use isPreviousModuleLocked)
 */
private function isModuleLocked($student, $module)
{
    // This is now handled by the combination of:
    // 1. Student's mastery level check in getGestureProgress()
    // 2. Previous module completion check in isPreviousModuleLocked()
    // 
    // Keeping this for backward compatibility but it's no longer the primary logic
    return $this->isPreviousModuleLocked($student, $module);
}
    // ─────────────────────────────────────────────────────────────────────────
    // MODULE CHECKPOINT QUIZ
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /student/module/{moduleId}/quiz
     * Returns 10 randomly-selected questions from all lesson quizzes inside
     * this module. Mix of multiple_choice and true_false.
     */
    public function getModuleQuiz(Request $request, $moduleId)
    {
        try {
            $user = Auth::user();
            $student = Student::where('user_id', $user->id)->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            // Verify module exists
            $module = Module::find($moduleId);
            if (!$module) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            // Get all published lesson IDs in this module that are assigned to student
           $lessonIds = LessonAssignment::where('student_id', $student->student_id)
            ->whereHas('lesson', function($query) use ($moduleId) {
                $query->where('module_id', $moduleId)
                      ->where('status', 'published')
                      ->whereNull('deleted_at');  // ✅ EXCLUDE SOFT DELETED LESSONS
            })
            ->join('lessons as l', 'lesson_assignments.lesson_id', '=', 'l.lesson_id')
            ->where('l.module_id', $moduleId)
            ->where('l.status', 'published')
            ->whereNull('l.deleted_at')  // ✅ EXCLUDE SOFT DELETED LESSONS
            ->pluck('l.lesson_id');

            if ($lessonIds->isEmpty()) {
                return response()->json(['error' => 'No lessons found for this module'], 404);
            }

            // Gather all quiz questions from those lessons
            $allQuestions = DB::table('quiz_questions as qq')
                ->join('quizzes as qz', 'qq.quiz_id', '=', 'qz.quiz_id')
                ->whereIn('qz.lesson_id', $lessonIds)
                ->whereIn('qq.question_type', ['multiple_choice', 'true_false'])
                ->select('qq.question_id', 'qq.question_type', 'qq.question_text', 'qq.points')
                ->get()
                ->shuffle();

            // Take up to 10 questions
            $selected = $allQuestions->take(10);

            if ($selected->isEmpty()) {
                return response()->json(['error' => 'No quiz questions available for this module'], 404);
            }

            // Load options for each selected question
            $questionIds = $selected->pluck('question_id');
            $optionsByQuestion = DB::table('quiz_options')
                ->whereIn('question_id', $questionIds)
                ->get()
                ->groupBy('question_id');

            $questions = $selected->map(function ($q) use ($optionsByQuestion) {
                $options = collect($optionsByQuestion->get($q->question_id, []))->map(function ($opt) {
                    return [
                        'option_id'   => $opt->option_id,
                        'option_text' => $opt->option_text,
                        'is_correct'  => (bool) $opt->is_correct,
                    ];
                })->shuffle()->values();

                return [
                    'question_id'   => $q->question_id,
                    'question_type' => $q->question_type,
                    'question_text' => $q->question_text,
                    'points'        => $q->points,
                    'options'       => $options,
                ];
            })->values();

            // Best previous score for this module
            $bestResult = ModuleQuizResult::where('student_id', $student->student_id)
                ->where('module_id', $moduleId)
                ->orderByDesc('percentage')
                ->first();

            // Attempt history for this module
            $attempts = DB::table('module_quiz_results')
                ->where('student_id', $student->student_id)
                ->where('module_id', $moduleId)
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($att) {
                    return [
                        'percentage' => (float)$att->percentage,
                        'passed'     => (bool)$att->passed,
                        'created_at' => $att->created_at,
                    ];
                });

            return response()->json([
                'success'     => true,
                'module_id'   => (int) $moduleId,
                'module_title'=> $module->title,
                'questions'   => $questions,
                'total'       => $questions->count(),
                'best_score'  => $bestResult ? $bestResult->percentage : null,
                'quiz_passed' => $bestResult ? $bestResult->passed : false,
                'attempts'    => $attempts,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cleanOrphanedAssignments()
{
    // Delete assignments for lessons that no longer exist
    $orphaned = LessonAssignment::whereDoesntHave('lesson')->delete();
    
    // Delete assignments for soft-deleted lessons
    LessonAssignment::whereHas('lesson', function($query) {
        $query->whereNotNull('deleted_at');
    })->delete();
    
    // Clean up student progress for deleted lessons
    DB::table('student_lesson_progress')
        ->whereDoesntHave('lesson')
        ->delete();
}


    /**
     * POST /student/module/{moduleId}/quiz/submit
     * Accepts { answers: [{ question_id, selected_option_id }] }
     * Saves the result and, if passed (≥60%), unlocks the first lesson of the
     * next module.
     */
    public function submitModuleQuiz(Request $request, $moduleId)
    {
        try {
            $user = Auth::user();
            $student = Student::where('user_id', $user->id)->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            $module = Module::find($moduleId);
            if (!$module) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            $answers = $request->input('answers', []);
            if (empty($answers)) {
                return response()->json(['error' => 'No answers provided'], 422);
            }

            // Evaluate each answer
            $correct = 0;
            $total   = count($answers);
            $details = [];

            foreach ($answers as $ans) {
                $questionId      = $ans['question_id'] ?? null;
                $selectedOptionId = $ans['selected_option_id'] ?? null;

                if (!$questionId || !$selectedOptionId) {
                    $details[] = ['question_id' => $questionId, 'is_correct' => false];
                    continue;
                }

                $isCorrect = DB::table('quiz_options')
                    ->where('question_id', $questionId)
                    ->where('option_id', $selectedOptionId)
                    ->where('is_correct', true)
                    ->exists();

                if ($isCorrect) $correct++;
                $details[] = ['question_id' => $questionId, 'is_correct' => $isCorrect];
            }

            $percentage = $total > 0 ? round(($correct / $total) * 100, 2) : 0;
            $passed     = $percentage >= 60;

            // Count previous attempts
            $attemptNumber = ModuleQuizResult::where('student_id', $student->student_id)
                ->where('module_id', $moduleId)
                ->count() + 1;

            // Save result
            ModuleQuizResult::create([
                'student_id'     => $student->student_id,
                'module_id'      => $moduleId,
                'score'          => $correct,
                'percentage'     => $percentage,
                'passed'         => $passed,
                'attempt_number' => $attemptNumber,
            ]);

            // If passed, unlock the first lesson of the NEXT module
            if ($passed) {
                // Find the next module (by module_order)
                $currentModule = Module::find($moduleId);
                $nextModule = Module::where('module_order', '>', $currentModule->module_order)
                    ->where('status', 'published')
                    ->orderBy('module_order', 'asc')
                    ->first();

                if ($nextModule) {
                    // Find the first lesson of that next module
                    $firstLesson = Lesson::where('module_id', $nextModule->module_id)
                        ->where('status', 'published')
                        ->orderBy('module_order', 'asc')
                        ->first();

                    if ($firstLesson) {
                        LessonAssignment::where('student_id', $student->student_id)
                            ->where('lesson_id', $firstLesson->lesson_id)
                            ->update(['is_locked' => false]);
                    }
                }
            }

            // XP award: 30 XP for passing
            $xpEarned = 0;
            if ($passed) {
                $xpEarned = 30;
                $xpService = new \App\Services\XPService();
                $xpService->awardXp(
                    $student,
                    $xpEarned,
                    'quiz_completed',
                    null, // dummy or no attempt_id
                    null, // no lesson_id
                    "Completed module checkpoint quiz"
                );
                $xpService->updateStreak($student);
            }

            // 🎯 A 100% score on a module quiz counts toward today's
            // "Perfect Score" goal, just like a lesson quiz would.
            if ($percentage == 100) {
                $challengeService = new DailyChallengeService(new \App\Services\XPService());
                $challengeService->recordProgressByType($student, 'quiz_attempt', 1);
            }

            $student->refresh();

            // ─── 🔔 NOTIFY TEACHER (module quiz) ────────────────────────
            if ($passed) {
                try {
                    $mod         = Module::find($moduleId);
                    $modName     = $mod ? $mod->name : "Module #{$moduleId}";
                    $studentName = $student->first_name . ' ' . $student->last_name;
                    $pct         = (int) round($percentage);

                    $this->notifyTeacher(
                        student:   $student,
                        type:      'module_passed',
                        title:     "🏅 {$studentName} passed a module quiz",
                        message:   "Scored {$pct}% on the \"{$modName}\" checkpoint quiz — Attempt #{$attemptNumber}",
                        data: [
                            'student_id'     => $student->student_id,
                            'module_id'      => $moduleId,
                            'module_name'    => $modName,
                            'percentage'     => $pct,
                            'attempt_number' => $attemptNumber,
                            'xp_earned'      => $xpEarned,
                        ],
                        actionUrl: '/students/' . $student->student_id,
                    );
                } catch (\Exception $e) { /* never break the flow */ }
            }

            return response()->json([
                'success'       => true,
                'score'         => $correct,
                'total'         => $total,
                'percentage'    => $percentage,
                'passed'        => $passed,
                'attempt_number'=> $attemptNumber,
                'xp_earned'     => $xpEarned,
                'total_xp'      => $student->total_xp ?? 0,
                'level'         => $student->level ?? 1,
                'streak_days'   => $student->streak_days ?? 0,
                'details'       => $details,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
 * Get weak signs for a student (mastery_level != 'mastered')
 */
public function getWeakSigns(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $moduleName = $request->query('module_name', 'alphabet_part1');
        
        // Find the module
        $module = GestureModule::where('name', $moduleName)->first();
        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Get all gestures in this module
        $gestureIds = $module->gestures->pluck('gesture_id');
        
        // Get performances where mastery_level is NOT 'mastered'
        $weakPerformances = GesturePerformance::where('student_id', $student->student_id)
            ->whereIn('gesture_id', $gestureIds)
            ->where('mastery_level', '!=', 'mastered')
            ->get();

        // Get the gesture names for these IDs
        $weakSigns = [];
        foreach ($weakPerformances as $perf) {
            $gesture = Gesture::find($perf->gesture_id);
            if ($gesture) {
                $weakSigns[] = [
                    'gesture_id' => $perf->gesture_id,
                    'name' => $gesture->name,
                    'display_name' => $gesture->display_name,
                    'mastery_level' => $perf->mastery_level,
                    'is_mastered' => $perf->is_mastered,
                    'attempts' => $perf->attempts,
                    'successful_attempts' => $perf->successful_attempts,
                    'wrong_attempts' => $perf->wrong_attempts,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'module' => [
                'module_id' => $module->module_id,
                'name' => $module->name,
                'display_name' => $module->display_name,
            ],
            'weak_signs' => $weakSigns,  // ← This is what the frontend expects
            'count' => count($weakSigns),
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}


/**
 * Award XP for challenge mode (no cap)
 */
public function awardChallengeXp(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'module_name' => 'required|string|exists:gesture_modules,name',
            'xp_earned' => 'required|integer|min:0',
            'star_rating' => 'required|integer|in:1,2,3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        $module = GestureModule::where('name', $request->module_name)->first();
        $xpEarned = $request->xp_earned;
        $starRating = $request->star_rating;

        // 🔥 NO CAP FOR CHALLENGE MODE - award full XP
        $xpService = new XPService();
        $xpService->updateStreak($student);
        
        // Create reason message
        $starEmojis = $starRating === 3 ? '⭐⭐⭐' : ($starRating === 2 ? '⭐⭐' : '⭐');
        $reason = "🏆 Challenge Mode: {$starEmojis} {$module->display_name} - +{$xpEarned} XP";
        
        // Award XP with no cap
        $xpService->awardXp(
            $student,
            $xpEarned,
            'challenge_completed',
            null,
            null,
            $reason
        );
        $xpService->updateStreak($student);

        // 🎯 Finishing a full Challenge Mode run today counts as today's
        // "Master Gestures" goal, even if this module was already challenged
        // (or mastered) on a previous day. Increment by a large number —
        // recordProgressByType clamps to the goal's target either way.
        $challengeService = new DailyChallengeService($xpService);
        $challengeService->recordProgressByType($student, 'gesture_practice', 999);

        $student->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Challenge XP awarded successfully! 🎉',
            'module' => [
                'name' => $module->name,
                'display_name' => $module->display_name,
            ],
            'star_rating' => $starRating,
            'xp_earned' => $xpEarned,
            'total_xp' => $student->total_xp,
            'level' => $student->level,
            'xp_message' => $reason,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}


/**
 * Award XP for custom/input mode (1 XP per letter, no cap)
 */
public function awardCustomXp(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'module_name' => 'required|string|exists:gesture_modules,name',
            'xp_earned' => 'required|integer|min:0',
            'star_rating' => 'required|integer|in:1,2,3',
            'mode' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        $module = GestureModule::where('name', $request->module_name)->first();
        $xpEarned = $request->xp_earned;
        $starRating = $request->star_rating;
        $mode = $request->mode ?? 'custom';

        // 🔥 NO CAP - award full XP (1 XP per letter)
        $xpService = new XPService();
        $xpService->updateStreak($student);
        
        $reason = "📝 Custom Words: {$xpEarned} letters signed - +{$xpEarned} XP";
        
        $xpService->awardXp(
            $student,
            $xpEarned,
            'custom_completed',
            null,
            null,
            $reason
        );
        $xpService->updateStreak($student);

        $student->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Custom XP awarded successfully! 📝',
            'module' => [
                'name' => $module->name,
                'display_name' => $module->display_name,
            ],
            'star_rating' => $starRating,
            'xp_earned' => $xpEarned,
            'total_xp' => $student->total_xp,
            'level' => $student->level,
            'xp_message' => $reason,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
     * Check if student has a pending promotion (for mobile app)
     * GET /api/student/promotion
     */
    public function checkPromotion(Request $request)
    {
        try {
            $user = Auth::user();
            $student = Student::where('user_id', $user->id)->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            // Check for unviewed promotion
            $promotion = StudentPromotion::where('student_id', $student->student_id)
                ->where('is_viewed', false)
                ->orderBy('promoted_at', 'desc')
                ->first();

            if (!$promotion) {
                return response()->json([
                    'has_promotion' => false,
                ]);
            }
            $this->createPromotionNotification($student, $promotion);

            // Get the promotion data with celebration messages
            $fromLevel = $promotion->from_level;
            $toLevel = $promotion->to_level;
            $promotionDate = $promotion->promoted_at;

            // Celebration messages based on promotion path
            $celebrationMessages = [
                'Beginner' => [
                    'Intermediate' => [
                        'title' => '🎉 You\'ve been promoted!',
                        'subtitle' => 'Beginner → Intermediate',
                        'message' => "Congratulations! You've mastered the basics and are now an Intermediate signer! Keep up the great work! 🌟",
                        'badge_icon' => '📚',
                        'gradient' => ['#0f3172', '#1a4f8a', '#2563eb'],
                    ],
                ],
                'Intermediate' => [
                    'Advanced' => [
                        'title' => '🚀 Outstanding Achievement!',
                        'subtitle' => 'Intermediate → Advanced',
                        'message' => "Your hard work has paid off! You're now an Advanced signer! You're becoming fluent in FSL! 💪",
                        'badge_icon' => '⭐',
                        'gradient' => ['#1a1a2e', '#16213e', '#0f3460'],
                    ],
                    'Graduated' => [
                        'title' => '🎓 GRADUATION DAY! 🎓',
                        'subtitle' => 'Intermediate → Graduated',
                        'message' => "CONGRATULATIONS, GRADUATE! You've completed your FSL journey! You're now officially a certified FSL signer! 🌟🎉🎊",
                        'badge_icon' => '🎓',
                        'gradient' => ['#1a1a2e', '#16213e', '#0f3460'],
                    ],
                ],
                'Advanced' => [
                    'Graduated' => [
                        'title' => '🏅 YOU DID IT! 🏅',
                        'subtitle' => 'Advanced → Graduated',
                        'message' => "AMAZING! You've reached the pinnacle of FSL mastery! You're now officially GRADUATED! Your certificate awaits! 🎓🌟",
                        'badge_icon' => '🏅',
                        'gradient' => ['#1a1a2e', '#16213e', '#0f3460'],
                    ],
                ],
            ];

            // Get message for this promotion
            $messages = $celebrationMessages[$fromLevel] ?? [];
            $promotionData = $messages[$toLevel] ?? [
                'title' => '🎉 Congratulations!',
                'subtitle' => "{$fromLevel} → {$toLevel}",
                'message' => "You've been promoted! Keep up the great work! 🌟",
                'badge_icon' => '🌟',
                'gradient' => ['#0f3172', '#1a4f8a', '#2563eb'],
            ];

            // Get performance summary
            $summary = $this->getPerformanceSummary($student);

            return response()->json([
                'has_promotion' => true,
                'promotion' => [
                    'id' => $promotion->id,
                    'from_level' => $fromLevel,
                    'to_level' => $toLevel,
                    'promotion_date' => $promotionDate,
                    'title' => $promotionData['title'],
                    'subtitle' => $promotionData['subtitle'],
                    'message' => $promotionData['message'],
                    'badge_icon' => $promotionData['badge_icon'],
                    'gradient' => $promotionData['gradient'] ?? ['#0f3172', '#1a4f8a', '#2563eb'],
                    'was_forced' => (bool) $promotion->was_forced,
                    'summary' => $summary,
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
 * ✅ NEW: Create notification for a promotion
 * Call this when a student is promoted
 */
protected function createPromotionNotification(Student $student, $promotion)
{
    $fromLevel = $promotion->from_level;
    $toLevel = $promotion->to_level;
    
    // Check if notification already exists for this promotion
    $exists = StudentNotification::where('student_id', $student->student_id)
        ->where('type', 'promotion')
        ->where('data->promotion_id', $promotion->id)
        ->exists();

    if ($exists) {
        return; // Skip duplicate
    }

    // Get celebration message
    $celebrationMessages = [
        'Beginner' => [
            'Intermediate' => [
                'title' => '🎉 You\'ve been promoted!',
                'message' => "Congratulations! You've mastered the basics and are now an Intermediate signer! Keep up the great work! 🌟",
            ],
        ],
        'Intermediate' => [
            'Advanced' => [
                'title' => '🚀 Outstanding Achievement!',
                'message' => "Your hard work has paid off! You're now an Advanced signer! You're becoming fluent in FSL! 💪",
            ],
            'Graduated' => [
                'title' => '🎓 GRADUATION DAY! 🎓',
                'message' => "CONGRATULATIONS, GRADUATE! You've completed your FSL journey! You're now officially a certified FSL signer! 🌟🎉🎊",
            ],
        ],
        'Advanced' => [
            'Graduated' => [
                'title' => '🏅 YOU DID IT! 🏅',
                'message' => "AMAZING! You've reached the pinnacle of FSL mastery! You're now officially GRADUATED! Your certificate awaits! 🎓🌟",
            ],
        ],
    ];

    $messages = $celebrationMessages[$fromLevel][$toLevel] ?? [
        'title' => '🎉 Congratulations!',
        'message' => "You've been promoted from {$fromLevel} to {$toLevel}! Keep up the great work! 🌟",
    ];

    StudentNotification::create([
        'student_id' => $student->student_id,
        'type' => 'promotion',
        'title' => $messages['title'],
        'message' => $messages['message'],
        'icon' => 'star',
        'color' => '#8B5CF6',
        'data' => [
            'promotion_id' => $promotion->id,
            'from_level' => $fromLevel,
            'to_level' => $toLevel,
        ],
        'action_url' => '/(tabs)/profile',
        'is_read' => false,
    ]);
}


    /**
     * Mark promotion as viewed (for mobile app)
     * POST /api/student/promotion/{id}/viewed
     */
    public function markPromotionViewed(Request $request, $promotionId)
    {
        try {
            $user = Auth::user();
            $student = Student::where('user_id', $user->id)->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            $promotion = StudentPromotion::where('id', $promotionId)
                ->where('student_id', $student->student_id)
                ->first();

            if (!$promotion) {
                return response()->json(['error' => 'Promotion not found'], 404);
            }

            $promotion->is_viewed = true;
            $promotion->viewed_at = now();
            $promotion->save();

            return response()->json([
                'success' => true,
                'message' => 'Promotion marked as viewed',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get performance summary for promotion
     */
    private function getPerformanceSummary($student)
    {
        // Get quiz performance (only from published, active lessons)
        $quizStats = DB::table('quiz_attempts')
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
            ->join('lessons', 'quizzes.lesson_id', '=', 'lessons.lesson_id')
            ->where('quiz_attempts.student_id', $student->student_id)
            ->where('quiz_attempts.status', 'completed')
            ->where('lessons.status', 'published')
            ->whereNull('lessons.deleted_at')
            ->select(
                DB::raw('COUNT(DISTINCT quiz_attempts.quiz_id) as quizzes_taken'),
                DB::raw('AVG(quiz_attempts.percentage) as avg_score'),
                DB::raw('COUNT(DISTINCT CASE WHEN quiz_attempts.percentage >= 60 THEN quiz_attempts.quiz_id END) as quizzes_passed')
            )
            ->first();

        // Get gesture practice stats
        $gestureStats = DB::table('gesture_performances')
            ->where('student_id', $student->student_id)
            ->select(
                DB::raw('COUNT(DISTINCT gesture_id) as gestures_attempted'),
                DB::raw('SUM(attempts) as total_attempts'),
                DB::raw('SUM(successful_attempts) as total_successful')
            )
            ->first();

        // Get lessons completed (only from published, active lessons)
        $lessonsCompleted = DB::table('student_lesson_progress')
            ->join('lessons', 'student_lesson_progress.lesson_id', '=', 'lessons.lesson_id')
            ->where('student_lesson_progress.student_id', $student->student_id)
            ->where('student_lesson_progress.lesson_completed', true)
            ->where('lessons.status', 'published')
            ->whereNull('lessons.deleted_at')
            ->count();

        // Get total XP
        $totalXp = $student->total_xp ?? 0;

        return [
            'quizzes_taken' => (int) ($quizStats->quizzes_taken ?? 0),
            'quizzes_passed' => (int) ($quizStats->quizzes_passed ?? 0),
            'avg_quiz_score' => round($quizStats->avg_score ?? 0, 1),
            'lessons_completed' => (int) $lessonsCompleted,
            'gestures_attempted' => (int) ($gestureStats->gestures_attempted ?? 0),
            'total_xp' => (int) $totalXp,
            'accuracy' => ($gestureStats->total_attempts ?? 0) > 0 
                ? round((($gestureStats->total_successful ?? 0) / ($gestureStats->total_attempts ?? 1)) * 100, 1)
                : 0,
        ];
    }

    /**
     * Check if student has pending promotion (lightweight version for dashboard)
     * This can be called alongside getAllLessons
     */
    public function hasPendingPromotion(Request $request)
    {
        try {
            $user = Auth::user();
            $student = Student::where('user_id', $user->id)->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            $hasPending = StudentPromotion::where('student_id', $student->student_id)
                ->where('is_viewed', false)
                ->exists();

            return response()->json([
                'has_pending_promotion' => $hasPending,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get promotion history (for achievement section)
     */
    public function getPromotionHistory(Request $request)
    {
        try {
            $user = Auth::user();
            $student = Student::where('user_id', $user->id)->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            $history = StudentPromotion::where('student_id', $student->student_id)
                ->orderBy('promoted_at', 'desc')
                ->get()
                ->map(function ($promotion) {
                    return [
                        'id' => $promotion->id,
                        'from_level' => $promotion->from_level,
                        'to_level' => $promotion->to_level,
                        'xp_at_promotion' => $promotion->xp_at_promotion,
                        'promoted_at' => $promotion->promoted_at,
                        'was_forced' => (bool) $promotion->was_forced,
                    ];
                });

            return response()->json([
                'success' => true,
                'history' => $history,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
 * Get promotion details by ID (for viewing historical promotions)
 * GET /api/student/promotion/{id}
 */
public function getPromotionDetails(Request $request, $promotionId)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $promotion = StudentPromotion::where('id', $promotionId)
            ->where('student_id', $student->student_id)
            ->first();

        if (!$promotion) {
            return response()->json(['error' => 'Promotion not found'], 404);
        }

        // Get performance summary for this student
        $summary = $this->getPerformanceSummary($student);

        // Get celebration messages
        $fromLevel = $promotion->from_level;
        $toLevel = $promotion->to_level;
        
        $celebrationMessages = [
            'Beginner' => [
                'Intermediate' => [
                    'title' => '🎉 You\'ve been promoted!',
                    'subtitle' => 'Beginner → Intermediate',
                    'message' => "Congratulations! You've mastered the basics and are now an Intermediate signer! Keep up the great work! 🌟",
                    'badge_icon' => '📚',
                    'gradient' => ['#0f3172', '#1a4f8a', '#2563eb'],
                ],
            ],
            'Intermediate' => [
                'Advanced' => [
                    'title' => '🚀 Outstanding Achievement!',
                    'subtitle' => 'Intermediate → Advanced',
                    'message' => "Your hard work has paid off! You're now an Advanced signer! You're becoming fluent in FSL! 💪",
                    'badge_icon' => '⭐',
                    'gradient' => ['#1a1a2e', '#16213e', '#0f3460'],
                ],
                'Graduated' => [
                    'title' => '🎓 GRADUATION DAY! 🎓',
                    'subtitle' => 'Intermediate → Graduated',
                    'message' => "CONGRATULATIONS, GRADUATE! You've completed your FSL journey! You're now officially a certified FSL signer! 🌟🎉🎊",
                    'badge_icon' => '🎓',
                    'gradient' => ['#1a1a2e', '#16213e', '#0f3460'],
                ],
            ],
            'Advanced' => [
                'Graduated' => [
                    'title' => '🏅 YOU DID IT! 🏅',
                    'subtitle' => 'Advanced → Graduated',
                    'message' => "AMAZING! You've reached the pinnacle of FSL mastery! You're now officially GRADUATED! Your certificate awaits! 🎓🌟",
                    'badge_icon' => '🏅',
                    'gradient' => ['#1a1a2e', '#16213e', '#0f3460'],
                ],
            ],
        ];

        $messages = $celebrationMessages[$fromLevel] ?? [];
        $promotionData = $messages[$toLevel] ?? [
            'title' => '🎉 Congratulations!',
            'subtitle' => "{$fromLevel} → {$toLevel}",
            'message' => "You've been promoted! Keep up the great work! 🌟",
            'badge_icon' => '🌟',
            'gradient' => ['#0f3172', '#1a4f8a', '#2563eb'],
        ];

        return response()->json([
            'success' => true,
            'promotion' => [
                'id' => $promotion->id,
                'from_level' => $fromLevel,
                'to_level' => $toLevel,
                'promotion_date' => $promotion->promoted_at,
                'title' => $promotionData['title'],
                'subtitle' => $promotionData['subtitle'],
                'message' => $promotionData['message'],
                'badge_icon' => $promotionData['badge_icon'],
                'gradient' => $promotionData['gradient'] ?? ['#0f3172', '#1a4f8a', '#2563eb'],
                'was_forced' => (bool) $promotion->was_forced,
                'summary' => $summary,
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
 * Get all achievements with unlock status for a student
 */
public function getAchievements(Request $request)
{
    try {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $achievementService = new AchievementService(new XPService());
        
        // Get all achievements
        $achievements = Achievement::orderBy('order')->get();
        
        // Get student's unlocked achievements
        $unlockedIds = StudentAchievement::where('student_id', $student->student_id)
            ->where('is_unlocked', true)
            ->pluck('achievement_id')
            ->toArray();
        
        $result = $achievements->map(function ($achievement) use ($unlockedIds, $student, $achievementService) {
            $isUnlocked = in_array($achievement->id, $unlockedIds);
            
            // Get progress if not unlocked
            $progress = null;
            if (!$isUnlocked) {
                $criteria = $achievement->criteria;
                if (!empty($criteria)) {
                    $current = $achievementService->getCriterionValue($student, $criteria[0]['type'], $criteria[0]['filters'] ?? []);
                    $target = $criteria[0]['threshold'] ?? 0;
                    $progress = [
                        'current' => $current,
                        'target' => $target,
                        'percentage' => $target > 0 ? min(100, round(($current / $target) * 100)) : 0,
                    ];
                }
            }
            
            return [
                'id' => $achievement->id,
                'code' => $achievement->code,
                'name' => $achievement->name,
                'description' => $achievement->description,
                'category' => $achievement->category,
                'icon' => $achievement->icon,
                'color' => $achievement->color,
                'is_unlocked' => $isUnlocked,
                'unlocked_at' => $isUnlocked ? StudentAchievement::where('student_id', $student->student_id)
                    ->where('achievement_id', $achievement->id)
                    ->value('unlocked_at') : null,
                'progress' => $progress,
            ];
        });
        
        return response()->json([
            'success' => true,
            'achievements' => $result,
            'summary' => [
                'total' => $achievements->count(),
                'unlocked' => count($unlockedIds),
                'locked' => $achievements->count() - count($unlockedIds),
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
 * Get only unlocked achievements
 */
public function getUnlockedAchievements(Request $request)
{
    try {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $unlocked = StudentAchievement::where('student_id', $student->student_id)
            ->where('is_unlocked', true)
            ->with('achievement')
            ->orderBy('unlocked_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'unlocked_achievements' => $unlocked->map(function ($record) {
                return [
                    'id' => $record->achievement->id,
                    'code' => $record->achievement->code,
                    'name' => $record->achievement->name,
                    'description' => $record->achievement->description,
                    'icon' => $record->achievement->icon,
                    'color' => $record->achievement->color,
                    'unlocked_at' => $record->unlocked_at,
                ];
            }),
            'count' => $unlocked->count(),
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Manually trigger achievement check (e.g., after level up)
 */
public function checkAchievements(Request $request)
{
    try {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $achievementService = new AchievementService(new XPService());
        $newlyUnlocked = $achievementService->checkAndUnlockAchievements($student);
        
        // ✅ FIX: Convert array to collection first, or use array_map
        $newlyUnlocked = collect($newlyUnlocked);  // Convert to Collection
        
        return response()->json([
            'success' => true,
            'newly_unlocked' => $newlyUnlocked->map(function ($achievement) {
                return [
                    'id' => $achievement->id,
                    'code' => $achievement->code,
                    'name' => $achievement->name,
                    'icon' => $achievement->icon,
                ];
            }),
            'count' => $newlyUnlocked->count(),
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function getStreak(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        return response()->json([
            'success' => true,
            'streak_days' => $student->streak_days ?? 0,
            'last_activity_date' => $student->last_activity_date,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Update student's profile picture
 * POST /api/student/update-profile-picture
 */
public function updateProfilePicture(Request $request)
{
    try {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|string|in:senya,boy,girl,catto',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid profile picture',
                'errors' => $validator->errors()
            ], 422);
        }

        $student->profile_picture = $request->profile_picture;
        $student->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully',
            'profile_picture' => $student->profile_picture,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Get all notifications for the authenticated student
 * GET /api/student/notifications
 */
public function getNotifications(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $notifications = StudentNotification::where('student_id', $student->student_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'icon' => $notification->icon,
                    'color' => $notification->color,
                    'data' => $notification->data,
                    'action_url' => $notification->action_url,
                    'is_read' => (bool) $notification->is_read,
                    'created_at' => $notification->created_at->toISOString(),
                    'read_at' => $notification->read_at ? $notification->read_at->toISOString() : null,
                ];
            });

        $unreadCount = StudentNotification::where('student_id', $student->student_id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Mark a notification as read
 * POST /api/student/notifications/{id}/read
 */
public function markNotificationRead(Request $request, $notificationId)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $notification = StudentNotification::where('id', $notificationId)
            ->where('student_id', $student->student_id)
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Mark all notifications as read
 * POST /api/student/notifications/read-all
 */
public function markAllNotificationsRead(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        StudentNotification::where('student_id', $student->student_id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Create a notification for a student (helper method)
 */
private function createNotification($studentId, $type, $title, $message, $data = null, $actionUrl = null)
{
    $iconMap = [
        'achievement' => 'trophy',
        'promotion' => 'star',
        'lesson' => 'book',
        'streak' => 'flame',
        'system' => 'notifications',
    ];

    $colorMap = [
        'achievement' => '#F59E0B',
        'promotion' => '#8B5CF6',
        'lesson' => '#3B82F6',
        'streak' => '#EF4444',
        'system' => '#6B7280',
    ];

    return StudentNotification::create([
        'student_id' => $studentId,
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'icon' => $iconMap[$type] ?? 'notifications',
        'color' => $colorMap[$type] ?? '#6B7280',
        'data' => $data,
        'action_url' => $actionUrl,
        'is_read' => false,
    ]);
}

/**
 * Save multiple notifications for the student (with duplicate checking)
 * POST /api/student/notifications/save
 */
public function saveNotifications(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'notifications' => 'required|array',
            'notifications.*.type' => 'required|string|in:achievement,promotion,lesson,streak,system',
            'notifications.*.title' => 'required|string|max:255',
            'notifications.*.message' => 'required|string',
            'notifications.*.data' => 'nullable',
            'notifications.*.action_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        $saved = [];
        $skipped = 0;

        foreach ($request->notifications as $notif) {
            // Build a unique key based on the notification content
            $type = $notif['type'];
            $title = $notif['title'];
            $message = $notif['message'];
            
            // Create a hash from the content to detect duplicates
            $contentHash = md5($type . $title . $message);
            
            // Check if a notification with this exact content already exists
            // for this student TODAY. Scoped to today (not all-time) because
            // recurring daily notifications — like the practice reminder —
            // legitimately reuse the same type/title/message every day; an
            // all-time check would treat every day after the first as a
            // duplicate of that first one and silently stop sending forever.
            $exists = StudentNotification::where('student_id', $student->student_id)
                ->where('type', $type)
                ->where('title', $title)
                ->where('message', $message)
                ->whereDate('created_at', Carbon::today())
                ->exists();
            
            // Also check by content hash if the above doesn't catch it
            if (!$exists) {
                // Check by data hash if data is present
                $dataHash = isset($notif['data']) ? md5(json_encode($notif['data'])) : null;
                
                if ($dataHash) {
                    $exists = StudentNotification::where('student_id', $student->student_id)
                        ->where('type', $type)
                        ->whereRaw('JSON_EXTRACT(data, "$") IS NOT NULL')
                        ->whereRaw('MD5(JSON_EXTRACT(data, "$")) = ?', [$dataHash])
                        ->whereDate('created_at', Carbon::today())
                        ->exists();
                }
            }

            if ($exists) {
                $skipped++;
                continue; // Skip duplicate
            }

            // Create new notification
            $notification = $this->createNotification(
                $student->student_id,
                $type,
                $title,
                $message,
                $notif['data'] ?? null,
                $notif['action_url'] ?? null
            );
            $saved[] = $notification;
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifications saved successfully',
            'saved' => count($saved),
            'skipped' => $skipped,
            'total' => count($saved) + $skipped,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Get today's daily challenge
 * GET /api/student/daily-challenge
 */
public function getDailyChallenge(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }
        
        $challengeService = new DailyChallengeService(new XPService());
        $challenge = $challengeService->generateDailyChallenge($student);
        
        // Get progress for each goal
        $progress = DB::table('challenge_goal_progress')
            ->where('challenge_id', $challenge->challenge_id)
            ->get()
            ->keyBy('goal_key');
        
        $goalsWithProgress = collect($challenge->goals)->map(function ($goal) use ($progress) {
            $p = $progress->get($goal['id']);
            return [
                'id' => $goal['id'],
                'type' => $goal['type'],
                'title' => $goal['title'],
                'description' => $goal['description'],
                'target' => $goal['target'],
                'xp_reward' => $goal['xp_reward'],
                'icon' => $goal['icon'],
                'current' => $p ? $p->current_value : 0,
                'is_completed' => $p ? (bool) $p->is_completed : false,
                'completed_at' => $p ? $p->completed_at : null,
            ];
        });
        
        $completedCount = $goalsWithProgress->filter(fn($g) => $g['is_completed'])->count();
        $totalGoals = $goalsWithProgress->count();
        $allCompleted = $completedCount === $totalGoals;
        $bonusXp = $allCompleted ? 50 : 0;
        
        return response()->json([
            'success' => true,
            'challenge' => [
                'id' => $challenge->challenge_id,
                'date' => $challenge->challenge_date,
                'theme' => $challenge->theme,
                'is_completed' => (bool) $challenge->is_completed,
                'completed_at' => $challenge->completed_at,
                'goals' => $goalsWithProgress,
                'summary' => [
                    'completed' => $completedCount,
                    'total' => $totalGoals,
                    'progress_percentage' => $totalGoals > 0 ? round(($completedCount / $totalGoals) * 100) : 0,
                    'xp_earned_so_far' => $challenge->total_xp_rewarded,
                    'bonus_xp_available' => $allCompleted ? $bonusXp : 0,
                ],
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
 * Update a goal's progress
 * POST /api/student/daily-challenge/progress
 */
public function updateChallengeProgress(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'goal_id' => 'required|string',
            'increment_by' => 'required|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 422);
        }
        
        $challenge = DailyChallenge::where('student_id', $student->student_id)
            ->whereDate('challenge_date', Carbon::today())
            ->first();
            
        if (!$challenge) {
            return response()->json(['error' => 'No challenge found for today'], 404);
        }
        
        // Update progress
        $progress = DB::table('challenge_goal_progress')
            ->where('challenge_id', $challenge->challenge_id)
            ->where('goal_key', $request->goal_id)
            ->first();
            
        if (!$progress) {
            return response()->json(['error' => 'Goal not found'], 404);
        }
        
        $newValue = min($progress->current_value + $request->increment_by, $progress->target_value);
        $isCompleted = $newValue >= $progress->target_value;
        
        DB::table('challenge_goal_progress')
            ->where('progress_id', $progress->progress_id)
            ->update([
                'current_value' => $newValue,
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? now() : null,
                'updated_at' => now(),
            ]);
            
        // If goal is completed, award XP
        $xpEarned = 0;
        if ($isCompleted && !$progress->is_completed) {
            $goals = collect($challenge->goals);
            $goal = $goals->firstWhere('id', $request->goal_id);
            if ($goal) {
                $xpEarned = $goal['xp_reward'];
                $xpService = new XPService();
                $xpService->awardXp(
                    $student,
                    $xpEarned,
                    'challenge_goal_completed',
                    null,
                    null,
                    "🎯 Daily Challenge: {$goal['title']} - +{$xpEarned} XP"
                );
                $xpService->updateStreak($student);
                
                // Update total xp rewarded
                $challenge->total_xp_rewarded += $xpEarned;
                $challenge->save();
            }
        }
        
        // Check if all goals are completed
        $allProgress = DB::table('challenge_goal_progress')
            ->where('challenge_id', $challenge->challenge_id)
            ->get();
            
        $allCompleted = $allProgress->every(fn($p) => (bool) $p->is_completed);
        
        // If all completed, award bonus XP
        $bonusXp = 0;
        if ($allCompleted && !$challenge->is_completed) {
            $bonusXp = 50;
            $xpService = new XPService();
            $xpService->awardXp(
                $student,
                $bonusXp,
                'challenge_completed',
                null,
                null,
                "🏆 Daily Challenge COMPLETE! +{$bonusXp} XP Bonus!"
            );
            $xpService->updateStreak($student);
            
            $challenge->is_completed = true;
            $challenge->completed_at = now();
            $challenge->total_xp_rewarded += $bonusXp;
            $challenge->save();
        }
        
        $student->refresh();
        
        return response()->json([
            'success' => true,
            'message' => 'Progress updated successfully',
            'goal_id' => $request->goal_id,
            'current_value' => $newValue,
            'target_value' => $progress->target_value,
            'is_completed' => $isCompleted,
            'xp_earned' => $xpEarned,
            'bonus_xp' => $bonusXp,
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

/**
 * Track time spent in gesture practice (for time goal)
 * POST /api/student/daily-challenge/track-time
 */
public function trackChallengeTime(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'minutes_spent' => 'required|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 422);
        }
        
        // Find the time goal
        $challenge = DailyChallenge::where('student_id', $student->student_id)
            ->whereDate('challenge_date', Carbon::today())
            ->first();
            
        if (!$challenge) {
            return response()->json(['error' => 'No challenge found for today'], 404);
        }
        
        $goals = collect($challenge->goals);
        $timeGoal = $goals->firstWhere('type', 'time_spent');
        
        if (!$timeGoal) {
            return response()->json(['error' => 'Time goal not found'], 404);
        }
        
        // "Extra Practice" (bonus_practice — "Practice any gesture for N
        // minutes") measures the exact same activity as the time goal, but
        // is a separate goal type, so it never got credited from here.
        // Apply the same minutes to it too, if today's challenge has one.
        $challengeService = new DailyChallengeService(new XPService());
        $challengeService->recordProgressByType($student, 'bonus_practice', $request->minutes_spent);
        
        // Update progress (increment by minutes spent)
        return $this->updateChallengeProgress(new Request([
            'goal_id' => $timeGoal['id'],
            'increment_by' => $request->minutes_spent,
        ]));
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Get all checkpoint exams for a student (for the lessons map)
 * GET /api/student/checkpoint-exams
 */
public function getCheckpointExams(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Get all published checkpoint exams assigned to this student
        $exams = CheckpointExam::where('status', 'published')
            ->whereHas('assignments', function($query) use ($student) {
                $query->where('student_id', $student->student_id);
            })
            ->with(['module', 'questions'])
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedExams = $exams->map(function($exam) use ($student) {
            // Get the student's best attempt
            $bestAttempt = DB::table('checkpoint_exam_attempts')
                ->where('exam_id', $exam->exam_id)
                ->where('student_id', $student->student_id)
                ->where('status', 'completed')
                ->orderByDesc('percentage')
                ->first();

            // Get the assignment status
            $assignment = CheckpointExamAssignment::where('exam_id', $exam->exam_id)
                ->where('student_id', $student->student_id)
                ->first();

            return [
                'exam_id' => $exam->exam_id,
                'title' => $exam->title,
                'description' => $exam->description,
                'module_id' => $exam->module_id,
                'module_title' => $exam->module->title ?? null,
                'total_points' => $exam->total_points,
                'passing_score' => $exam->passing_score,
        'time_limit_minutes' => $exam->time_limit_minutes ?? 60, // ✅ ADD THIS
        'total_questions' => $exam->questions->count(),
                'status' => $bestAttempt ? 'completed' : ($assignment->status ?? 'pending'),
                'score' => $bestAttempt ? $bestAttempt->percentage : null,
                'is_passed' => $bestAttempt ? $bestAttempt->percentage >= 60 : null,
                'is_locked' => $assignment ? (bool) $assignment->is_locked : true,
                'created_at' => $exam->created_at,
                'published_at' => $exam->published_at,
            ];
        });

        return response()->json([
            'success' => true,
            'exams' => $formattedExams,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Get a specific checkpoint exam with all questions
 * GET /api/student/checkpoint-exam/{examId}
 */
public function getCheckpointExamById(Request $request, $examId)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Check if student has access to this exam
        $assignment = CheckpointExamAssignment::where('exam_id', $examId)
            ->where('student_id', $student->student_id)
            ->first();

        if (!$assignment) {
            return response()->json(['error' => 'You do not have access to this exam'], 403);
        }

        // Get the exam with questions
        $exam = CheckpointExam::with(['questions', 'module'])
            ->where('exam_id', $examId)
            ->where('status', 'published')
            ->first();

        if (!$exam) {
            return response()->json(['error' => 'Exam not found'], 404);
        }

        // Format questions (without revealing correct answers)
        $questions = $exam->questions->map(function($question) {
            // 🔥 Ensure drag_drop_pairs is always an array
            $dragDropPairs = $question->drag_drop_pairs;
            if (is_string($dragDropPairs)) {
                $dragDropPairs = json_decode($dragDropPairs, true) ?? [];
            }
            if (!is_array($dragDropPairs)) {
                $dragDropPairs = [];
            }

            // 🔥 Ensure gesture_data is always an array
            $gestureData = $question->gesture_data;
            if (is_string($gestureData)) {
                $gestureData = json_decode($gestureData, true) ?? [];
            }
            if (!is_array($gestureData)) {
                $gestureData = [];
            }

            // 🔥 Ensure options_data is always an array
            $optionsData = $question->options_data;
            if (is_string($optionsData)) {
                $optionsData = json_decode($optionsData, true) ?? [];
            }
            if (!is_array($optionsData)) {
                $optionsData = [];
            }

            $questionData = [
                'question_id' => $question->question_id,
                'question_number' => $question->question_number,
                'question_type' => $question->question_type,
                'question_text' => $question->question_text,
                'media_url' => $question->media_url,
                'points' => $question->points,
                'drag_drop_pairs' => $dragDropPairs,
                'gesture_data' => $gestureData,
            ];

            // For multiple choice and true/false, include options (without correct flag)
            if (in_array($question->question_type, ['multiple_choice', 'true_false'])) {
                $questionData['options'] = array_map(function($opt) {
                    return [
                        'text' => $opt['text'] ?? '',
                        'image' => $opt['image'] ?? null,
                    ];
                }, $optionsData);
            }

            return $questionData;
        });

        // Get previous attempts
        $attempts = DB::table('checkpoint_exam_attempts')
            ->where('exam_id', $examId)
            ->where('student_id', $student->student_id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function($attempt) {
                return [
                    'attempt_id' => $attempt->attempt_id,
                    'score' => $attempt->score,
                    'total_points' => $attempt->total_points,
                    'percentage' => $attempt->percentage,
                    'xp_earned' => $attempt->xp_earned,
                    'status' => $attempt->status,
                    'created_at' => $attempt->created_at,
                ];
            });

        // Check if the exam is locked
        $isLocked = (bool) $assignment->is_locked;
        $assignmentStatus = $assignment->status;

        return response()->json([
            'success' => true,
            'exam' => [
                'exam_id' => $exam->exam_id,
                'title' => $exam->title,
                'description' => $exam->description,
                'module_id' => $exam->module_id,
                'module_title' => $exam->module->title ?? null,
                'total_points' => $exam->total_points,
                'passing_score' => $exam->passing_score,
                'time_limit_minutes' => $exam->time_limit_minutes ?? 60,
                'total_questions' => $exam->questions->count(),
                'questions' => $questions,
                'is_locked' => $isLocked,
                'status' => $assignmentStatus,
            ],
            'attempts' => $attempts,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}


/**
 * Submit a checkpoint exam
 * POST /api/student/checkpoint-exam/{examId}/submit
 */
public function submitCheckpointExam(Request $request, $examId)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // 🔥 UPDATE THIS VALIDATOR SECTION
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:checkpoint_exam_questions,question_id',
            'answers.*.selected_option_id' => 'nullable|integer',
            'answers.*.selected_option_text' => 'nullable|string',
            'answers.*.gesture_success' => 'nullable|boolean',
            'answers.*.drag_drop_success' => 'nullable|boolean',   // ← WAS 'drag_drop_matches' (array)
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }



        // Get the exam
        $exam = CheckpointExam::with('questions')->findOrFail($examId);

        // Check if student has access
        $assignment = CheckpointExamAssignment::where('exam_id', $examId)
            ->where('student_id', $student->student_id)
            ->first();

        if (!$assignment) {
            return response()->json(['error' => 'You do not have access to this exam'], 403);
        }

        // Get all questions
        $questions = $exam->questions;

        // Calculate score
        $totalPoints = $exam->total_points;
        $earnedPoints = 0;
        $questionResults = [];

        foreach ($questions as $question) {
            $answer = collect($request->answers)->firstWhere('question_id', $question->question_id);
            $isCorrect = false;

            if (!$answer) {
                $questionResults[] = [
                    'question_id' => $question->question_id,
                    'is_correct' => false,
                    'points_earned' => 0,
                ];
                continue;
            }

            // Check based on question type
          switch ($question->question_type) {
            case 'multiple_choice':
            case 'true_false':
                $options = is_array($question->options_data) ? $question->options_data : [];
                $selectedText = $answer['selected_option_text'] ?? '';
                
                foreach ($options as $opt) {
                    if (isset($opt['text']) && $opt['text'] === $selectedText && isset($opt['is_correct']) && $opt['is_correct']) {
                        $isCorrect = true;
                        break;
                    }
                }
                break;

            case 'gesture':
                $isCorrect = isset($answer['gesture_success']) && $answer['gesture_success'] === true;
                break;

            case 'drag_drop':
                // 🔥 REPLACE THIS ENTIRE CASE
                // Trust the client-computed result, same as gesture questions
                $isCorrect = isset($answer['drag_drop_success']) && $answer['drag_drop_success'] === true;
                break;

            default:
                $isCorrect = false;
        }

            if ($isCorrect) {
                $earnedPoints += $question->points;
            }

            $questionResults[] = [
                'question_id' => $question->question_id,
                'is_correct' => $isCorrect,
                'points_earned' => $isCorrect ? $question->points : 0,
            ];
        }

        $percentage = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0;
        $passed = $percentage >= 60;

        // Count previous attempts
        $attemptNumber = DB::table('checkpoint_exam_attempts')
            ->where('student_id', $student->student_id)
            ->where('exam_id', $examId)
            ->count() + 1;

        // Calculate XP based on percentage
   $xpEarned = $this->calculateCheckpointXp($percentage, $attemptNumber, $student, $examId);
        // Save the attempt
        $attemptId = DB::table('checkpoint_exam_attempts')->insertGetId([
            'student_id' => $student->student_id,
            'exam_id' => $examId,
            'score' => $earnedPoints,
            'total_points' => $totalPoints,
            'percentage' => $percentage,
            'status' => $passed ? 'completed' : 'failed',
            'attempt_number' => $attemptNumber,
            'xp_earned' => $xpEarned,
            'answers_data' => json_encode($request->answers),
            'question_results' => json_encode($questionResults),
            'started_at' => now(),
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update assignment
        $assignment->status = $passed ? 'completed' : 'failed';
        $assignment->completed_at = now();
        $assignment->score = $percentage;
        $assignment->attempt_count = $attemptNumber;
        $assignment->save();

        // Award XP
        if ($xpEarned > 0) {
            $xpService = new XPService();
            $xpService->awardXp(
                $student,
                $xpEarned,
                'checkpoint_exam_completed',
                $attemptId,
                null,
                "📝 Checkpoint Exam: {$exam->title} - {$percentage}% (+{$xpEarned} XP)"
            );
            $xpService->updateStreak($student);
        }

        // Update lesson lock statuses in the module
        if ($passed) {
            $this->unlockNextModuleContent($student, $exam->module_id);
        }

        $student->refresh();

        // Determine star rating
        $stars = $this->getStarRating($percentage);

        // ─── 🔔 NOTIFY TEACHER (checkpoint exam) ────────────────────────
        try {
            $studentName = $student->first_name . ' ' . $student->last_name;
            $pct         = (int) round($percentage);
            $emoji       = $passed ? ($pct === 100 ? '🏆' : '✅') : '❌';

            $this->notifyTeacher(
                student:   $student,
                type:      'checkpoint_passed',
                title:     "{$emoji} {$studentName} took a checkpoint exam",
                message:   "Scored {$pct}% on \"{$exam->title}\" — " . ($passed ? 'Passed' : 'Failed') . " — Attempt #{$attemptNumber}",
                data: [
                    'student_id'     => $student->student_id,
                    'exam_id'        => $examId,
                    'exam_title'     => $exam->title,
                    'percentage'     => $pct,
                    'passed'         => $passed,
                    'attempt_number' => $attemptNumber,
                    'xp_earned'      => $xpEarned,
                ],
                actionUrl: '/students/' . $student->student_id,
            );
        } catch (\Exception $e) { /* never break the flow */ }

        return response()->json([
            'success' => true,
            'message' => $passed ? 'Exam completed successfully!' : 'Exam completed. Keep practicing!',
            'attempt_id' => $attemptId,
            'score' => $earnedPoints,
            'total_points' => $totalPoints,
            'percentage' => $percentage,
            'passed' => $passed,
            'stars' => $stars,
            'xp_earned' => $xpEarned,
            'attempt_number' => $attemptNumber,
            'total_xp' => $student->total_xp,
            'level' => $student->level,
            'streak_days' => $student->streak_days,
            'question_results' => $questionResults,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Calculate XP for checkpoint exam with cap (max 50 XP per exam)
 * 
 * - First attempt perfect = 50 XP (bonus!)
 * - First attempt passing (60-99%) = 30 XP
 * - First attempt failing (<60%) = 5 XP (participation)
 * - Subsequent attempts = only XP if score improves
 * - Max XP per exam = 50
 */
private function calculateCheckpointXp($percentage, $attemptNumber, $student, $examId)
{
    // ─── 1. GET TOTAL XP EARNED SO FAR FOR THIS EXAM ──────────────
    $totalXpEarned = DB::table('checkpoint_exam_attempts')
        ->where('student_id', $student->student_id)
        ->where('exam_id', $examId)
        ->sum('xp_earned');
    
    // Max XP per exam is 50
    $maxXpPerExam = 50;
    
    // If already earned max XP, return 0
    if ($totalXpEarned >= $maxXpPerExam) {
        return 0;
    }
    
    // ─── 2. GET PREVIOUS BEST SCORE ──────────────────────────────
    $previousBest = DB::table('checkpoint_exam_attempts')
        ->where('student_id', $student->student_id)
        ->where('exam_id', $examId)
        ->where('status', 'completed')
        ->max('percentage');
    
    // ─── 3. FIRST ATTEMPT PERFECT = 50 XP ──────────────────────
    if ($attemptNumber === 1 && $percentage >= 100) {
        $xpEarned = min(50, $maxXpPerExam - $totalXpEarned);
        return max(0, $xpEarned);
    }
    
    // ─── 4. FIRST ATTEMPT PASSING (60-99%) = 30 XP ─────────────
    if ($attemptNumber === 1 && $percentage >= 60) {
        $xpEarned = min(30, $maxXpPerExam - $totalXpEarned);
        return max(0, $xpEarned);
    }
    
    // ─── 5. FIRST ATTEMPT FAILING = 5 XP (participation) ──────
    if ($attemptNumber === 1 && $percentage < 60) {
        // Cap at remaining XP
        $remainingXp = $maxXpPerExam - $totalXpEarned;
        return min(5, $remainingXp);
    }
    
    // ─── 6. SUBSEQUENT ATTEMPTS - ONLY GET XP FOR IMPROVEMENT ──
    if ($previousBest !== null) {
        // Only award XP if they improved their score
        if ($percentage > $previousBest) {
            $improvement = $percentage - $previousBest;
            
            // Base XP: 1 XP per 5% improvement
            $xpEarned = floor($improvement / 5);
            
            // Bonus for reaching perfect score (only if not already perfect)
            if ($percentage >= 100 && $previousBest < 100) {
                $xpEarned += 5;
            }
            
            // Cap at remaining XP
            $remainingXp = $maxXpPerExam - $totalXpEarned;
            $xpEarned = min($xpEarned, $remainingXp);
            
            // If improvement is less than 5%, give at least 1 XP
            if ($xpEarned < 1 && $improvement > 0) {
                $xpEarned = 1;
            }
            
            return max(0, $xpEarned);
        }
        
        // No improvement = 0 XP
        return 0;
    }
    
    // ─── 7. FALLBACK: 5 XP for participation ─────────────────────
    return 5;
}

/**
 * Get star rating based on percentage
 */
private function getStarRating($percentage)
{
    if ($percentage >= 90) return 3;
    if ($percentage >= 60) return 2;
    if ($percentage >= 30) return 1;
    return 0;
}

/**
 * Check drag-drop matches
 */
private function checkDragDropMatches($pairs, $matches)
{
    if (empty($pairs) || empty($matches)) {
        return false;
    }

    // Count correct matches
    $correctMatches = 0;
    foreach ($pairs as $index => $pair) {
        $leftText = $pair['left_text'] ?? '';
        $rightText = $pair['right_text'] ?? '';
        
        foreach ($matches as $match) {
            if (isset($match['left']) && isset($match['right']) &&
                $match['left'] === $leftText && $match['right'] === $rightText) {
                $correctMatches++;
                break;
            }
        }
    }

    // All pairs must match
    return $correctMatches === count($pairs);
}

/**
 * Unlock the next module content (lesson or checkpoint exam)
 */
private function unlockNextModuleContent($student, $moduleId)
{
    // First, check if there's a checkpoint exam for this module
    $nextExam = CheckpointExam::where('module_id', $moduleId)
        ->where('status', 'published')
        ->whereDoesntHave('assignments', function($query) use ($student) {
            $query->where('student_id', $student->student_id)
                ->where('status', 'completed');
        })
        ->orderBy('created_at', 'asc')
        ->first();

    if ($nextExam) {
        $assignment = CheckpointExamAssignment::where('exam_id', $nextExam->exam_id)
            ->where('student_id', $student->student_id)
            ->first();
        
        if ($assignment) {
            $assignment->is_locked = false;
            $assignment->save();
        }
        return;
    }

    // If no exam, unlock the next lesson
    $nextLesson = Lesson::where('module_id', $moduleId)
        ->where('status', 'published')
        ->whereDoesntHave('assignments', function($query) use ($student) {
            $query->where('student_id', $student->student_id)
                ->where('status', 'completed');
        })
        ->orderBy('module_order', 'asc')
        ->first();

    if ($nextLesson) {
        $assignment = LessonAssignment::where('lesson_id', $nextLesson->lesson_id)
            ->where('student_id', $student->student_id)
            ->first();
        
        if ($assignment) {
            $assignment->is_locked = false;
            $assignment->save();
        }
    }
}


/**
 * Get leaderboard for a checkpoint exam
 * GET /api/student/checkpoint-exam/{examId}/leaderboard
 */
public function getCheckpointExamLeaderboard(Request $request, $examId)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $exam = CheckpointExam::find($examId);
        if (!$exam) {
            return response()->json(['error' => 'Checkpoint exam not found'], 404);
        }

        $rankings = DB::table('checkpoint_exam_attempts as cea')
            ->join('students as s', 'cea.student_id', '=', 's.student_id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('cea.exam_id', $examId)
            ->where('cea.status', 'completed')
            ->select(
                's.student_id',
                's.first_name',
                's.last_name',
                'u.username',
                DB::raw('MAX(cea.percentage) as best_score'),
                DB::raw('COUNT(cea.attempt_id) as total_attempts'),
                DB::raw('MAX(cea.xp_earned) as xp_earned')
            )
            ->groupBy('s.student_id', 's.first_name', 's.last_name', 'u.username')
            ->get();

        $rankedList = $rankings->map(function ($item) use ($examId, $student) {
            $bestAttempt = DB::table('checkpoint_exam_attempts')
                ->where('student_id', $item->student_id)
                ->where('exam_id', $examId)
                ->where('percentage', $item->best_score)
                ->where('status', 'completed')
                ->orderBy('created_at', 'asc')
                ->first();

            $attemptsBeforeBest = DB::table('checkpoint_exam_attempts')
                ->where('student_id', $item->student_id)
                ->where('exam_id', $examId)
                ->where('status', 'completed')
                ->where('created_at', '<', $bestAttempt->created_at)
                ->count();

            $attemptsToAchieve = $attemptsBeforeBest + 1;

            return [
                'student_id' => $item->student_id,
                'name' => $item->first_name . ' ' . $item->last_name,
                'username' => $item->username,
                'best_score' => (int) $item->best_score,
                'attempts' => (int) $item->total_attempts,
                'attempts_to_achieve' => $attemptsToAchieve,
                'is_me' => $item->student_id === $student->student_id,
                'initials' => strtoupper(substr($item->first_name, 0, 1) . substr($item->last_name, 0, 1)),
                'xp_earned' => (int) $item->xp_earned,
            ];
        })
        ->sort(function ($a, $b) {
            if ($a['best_score'] !== $b['best_score']) {
                return $b['best_score'] - $a['best_score'];
            }
            return $a['attempts_to_achieve'] - $b['attempts_to_achieve'];
        })
        ->values();

        $rankedList = $rankedList->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });

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
 * Get teacher by ID
 * GET /api/teacher/{teacherId}
 */
public function getTeacher(Request $request, $teacherId)
{
    try {
        $teacher = \App\Models\Teacher::with('user')->find($teacherId);
        
        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found'], 404);
        }
        
        return response()->json([
            'success' => true,
            'teacher' => [
                'id' => $teacher->teacher_id,
                'first_name' => $teacher->first_name,
                'last_name' => $teacher->last_name,
                'email' => $teacher->user->email ?? null,
                'profile_photo' => $teacher->user->profile_photo ?? null, // ✅ ADD THIS
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
 * Get unlocked gesture modules for the student
 * GET /api/student/unlocked-modules
 */
public function getUnlockedModules(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Get student's mastery level
        $studentLevel = strtolower($student->fsl_mastery_level ?? 'beginner');
        $accessibleLevels = $this->getAccessibleModuleLevels($studentLevel);

        // Get all gesture modules
        $modules = GestureModule::where('is_active', true)
                               ->orderBy('order', 'asc')
                               ->get();

        $unlockedModules = [];

        foreach ($modules as $module) {
            $moduleLevel = strtolower($module->difficulty ?? 'beginner');
            
            // Check if module is accessible by level
            $isAccessibleByLevel = in_array($moduleLevel, $accessibleLevels);
            
            // Check if previous module is completed (if not first)
            $isPreviousModuleReady = true;
            if ($module->order > 1) {
                $previousModule = GestureModule::where('order', '<', $module->order)
                                              ->where('is_active', true)
                                              ->orderBy('order', 'desc')
                                              ->first();
                
                if ($previousModule) {
                    // Check if student has enough progress (40% threshold)
                    $gestureIds = $previousModule->gestures->pluck('gesture_id');
                    $totalGestures = $gestureIds->count();
                    
                    if ($totalGestures > 0) {
                        $performances = GesturePerformance::where('student_id', $student->student_id)
                                                         ->whereIn('gesture_id', $gestureIds)
                                                         ->get()
                                                         ->keyBy('gesture_id');
                        
                        $completedCount = 0;
                        foreach ($gestureIds as $gestureId) {
                            $perf = $performances->get($gestureId);
                            if ($perf && in_array($perf->mastery_level, ['mastered', 'proficient'])) {
                                $completedCount++;
                            }
                        }
                        
                        $requiredCompleted = ceil($totalGestures * 0.40);
                        $isPreviousModuleReady = $completedCount >= $requiredCompleted;
                    }
                }
            }
            
            // Module is unlocked if accessible by level AND previous module is ready
            $isUnlocked = $isAccessibleByLevel && $isPreviousModuleReady;
            
            if ($isUnlocked) {
                $unlockedModules[] = [
                    'module_id' => $module->module_id,
                    'name' => $module->name,
                    'display_name' => $module->display_name,
                    'difficulty' => $module->difficulty,
                    'order' => $module->order,
                    // Map to frontend module type
                    'type' => $this->mapModuleNameToType($module->name),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'unlocked_modules' => $unlockedModules,
            'student_level' => $student->fsl_mastery_level,
            'accessible_levels' => $accessibleLevels,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Map database module name to frontend type
 */
private function mapModuleNameToType(string $moduleName): string
{
    $map = [
        'alphabet_part1' => 'alphabet',
        'alphabet_part2' => 'alphabet',
        'level1_numbers' => 'numbers',
        'level2_greetings' => 'greetings',
        'level3_survival' => 'survival',
    ];
    
    return $map[$moduleName] ?? $moduleName;
}
/**
 * Submit a help request
 * POST /api/student/help-request
 */
public function submitHelpRequest(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:5|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        $helpRequest = HelpRequest::create([
            'student_id' => $student->student_id,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        // Notify admins (optional)
        $this->notifyAdminsOfHelpRequest($helpRequest);

        // ─── 🔔 NOTIFY TEACHER (help request) ───────────────────────────
        $this->notifyTeacher(
            student:   $student,
            type:      'help_request',
            title:     '🆘 ' . $student->first_name . ' ' . $student->last_name . ' sent a help request',
            message:   strlen($request->message) > 100
                           ? substr($request->message, 0, 100) . '…'
                           : $request->message,
            data: [
                'student_id'      => $student->student_id,
                'help_request_id' => $helpRequest->help_request_id,
            ],
            actionUrl: '/students/' . $student->student_id,
        );

        return response()->json([
            'success' => true,
            'message' => 'Help request submitted successfully. We\'ll get back to you within 24 hours.',
            'help_request' => [
                'id' => $helpRequest->help_request_id,
                'status' => $helpRequest->status,
                'created_at' => $helpRequest->created_at,
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
 * Get student's help request history
 * GET /api/student/help-requests
 */
public function getHelpRequests(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        $helpRequests = HelpRequest::forStudent($student->student_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->help_request_id,
                    'subject' => $request->subject,
                    'message' => $request->message,
                    'status' => $request->status,
                    'admin_response' => $request->admin_response,
                    'created_at' => $request->created_at->toISOString(),
                    'resolved_at' => $request->resolved_at ? $request->resolved_at->toISOString() : null,
                    'responded_at' => $request->responded_at ? $request->responded_at->toISOString() : null,
                ];
            });

        return response()->json([
            'success' => true,
            'help_requests' => $helpRequests,
            'count' => $helpRequests->count(),
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Get a specific help request
 * GET /api/student/help-request/{id}
 */
public function getHelpRequestById(Request $request, $id)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $helpRequest = HelpRequest::forStudent($student->student_id)
            ->where('help_request_id', $id)
            ->first();

        if (!$helpRequest) {
            return response()->json(['error' => 'Help request not found'], 404);
        }

        return response()->json([
            'success' => true,
            'help_request' => [
                'id' => $helpRequest->help_request_id,
                'message' => $helpRequest->message,
                'status' => $helpRequest->status,
                'admin_response' => $helpRequest->admin_response,
                'created_at' => $helpRequest->created_at->toISOString(),
                'resolved_at' => $helpRequest->resolved_at ? $helpRequest->resolved_at->toISOString() : null,
                'responded_at' => $helpRequest->responded_at ? $helpRequest->responded_at->toISOString() : null,
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
 * Notify admins (helper method)
 */
private function notifyAdminsOfHelpRequest($helpRequest)
{
    try {
        \Log::info('New help request submitted', [
            'help_request_id' => $helpRequest->help_request_id,
            'student_id' => $helpRequest->student_id,
            'created_at' => $helpRequest->created_at,
        ]);
    } catch (\Exception $e) {
        \Log::error('Failed to log help request: ' . $e->getMessage());
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// 🔔 TEACHER NOTIFICATION HELPER
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Fire a teacher notification for a given student event.
 * Silently swallows exceptions so it never breaks the student flow.
 */
private function notifyTeacher(Student $student, string $type, string $title, string $message, ?array $data = null, ?string $actionUrl = null): void
{
    try {
        if (!$student->teacher_id) return;

        // Always redirect to the student's report modal
        $resolvedUrl = '/reports?open_student=' . $student->student_id;

        TeacherNotification::createForTeacher(
            teacherId: $student->teacher_id,
            type:      $type,
            title:     $title,
            message:   $message,
            data:      $data,
            actionUrl: $resolvedUrl,
        );
    } catch (\Exception $e) {
        \Log::warning('Teacher notification failed: ' . $e->getMessage());
    }
}

/**
 * Admin: Get all help requests
 * GET /api/admin/help-requests
 */
public function adminGetHelpRequests(Request $request)
{
    try {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'teacher'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = HelpRequest::with(['student']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $helpRequests = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        $transformed = $helpRequests->map(function ($request) {
            return [
                'id' => $request->help_request_id,
                'student_name' => $request->student ? $request->student->first_name . ' ' . $request->student->last_name : 'Unknown',
                'student_lrn' => $request->student ? $request->student->lrn : null,
                'message' => $request->message,
                'status' => $request->status,
                'admin_response' => $request->admin_response,
                'created_at' => $request->created_at->toISOString(),
                'resolved_at' => $request->resolved_at ? $request->resolved_at->toISOString() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'help_requests' => $transformed,
            'pagination' => [
                'total' => $helpRequests->total(),
                'per_page' => $helpRequests->perPage(),
                'current_page' => $helpRequests->currentPage(),
                'last_page' => $helpRequests->lastPage(),
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
 * Admin: Respond to help request
 * POST /api/admin/help-request/{id}/respond
 */
public function adminRespondToHelpRequest(Request $request, $id)
{
    try {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'teacher'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $helpRequest = HelpRequest::find($id);
        if (!$helpRequest) {
            return response()->json(['error' => 'Help request not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'response' => 'required|string|min:5',
            'status' => 'nullable|in:in_progress,resolved,closed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        $helpRequest->admin_response = $request->response;
        $helpRequest->responded_at = now();

        if ($request->has('status')) {
            $helpRequest->status = $request->status;
            if ($request->status === 'resolved') {
                $helpRequest->resolved_at = now();
                $helpRequest->resolved_by = $user->id;
            }
        } else {
            $helpRequest->status = 'in_progress';
        }

        $helpRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Response sent successfully',
            'help_request' => [
                'id' => $helpRequest->help_request_id,
                'status' => $helpRequest->status,
                'admin_response' => $helpRequest->admin_response,
                'responded_at' => $helpRequest->responded_at,
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
 * Create notification for help response
 */
private function createHelpResponseNotification($helpRequest)
{
    if (!$helpRequest->student_id) {
        return;
    }

    $student = Student::find($helpRequest->student_id);
    if (!$student) {
        return;
    }

    $this->createNotification(
        $student->student_id,
        'system',
        '📬 Support Response Received',
        "A support team member has responded to your help request: \"{$helpRequest->subject}\". Please check your email for more details.",
        [
            'help_request_id' => $helpRequest->help_request_id,
            'action_url' => '/help-requests/' . $helpRequest->help_request_id,
        ],
        '/help-requests'
    );
}

/**
 * Notify teacher about module completion with hint usage
 */
private function notifyTeacherAboutHintUsage($student, $moduleName, $hintUsageData, $totalLetters)
{
    try {
        if (!$student->teacher_id) {
            return;
        }

        // ✅ AGGREGATE HINT USAGE
        $aggregatedHints = [];
        foreach ($hintUsageData as $hint) {
            $letter = $hint['letter'];
            if (!isset($aggregatedHints[$letter])) {
                $aggregatedHints[$letter] = 0;
            }
            $aggregatedHints[$letter] += $hint['count'];
        }

        // Format for display
        $hintLetters = [];
        $totalHintCount = 0;
        foreach ($aggregatedHints as $letter => $count) {
            if ($count > 0) {
                $hintLetters[] = "{$letter} ({$count}x)";
                $totalHintCount += $count;
            }
        }

        $studentName = $student->first_name . ' ' . $student->last_name;
        $displayModule = ucwords(str_replace('_', ' ', $moduleName));
        
        $hintString = empty($hintLetters) ? 'None' : implode(', ', $hintLetters);
        
        $title = "📊 {$studentName} completed {$displayModule}";
        
        if ($totalHintCount > 0) {
            $message = "Completed with {$totalHintCount} hint(s) used on letters: {$hintString}";
        } else {
            $message = "Completed without using any hints! 🎉";
        }

        // Create aggregated hint usage for notification data
        $aggregatedHintData = [];
        foreach ($aggregatedHints as $letter => $count) {
            $aggregatedHintData[] = ['letter' => $letter, 'count' => $count];
        }

        TeacherNotification::createForTeacher(
            teacherId: $student->teacher_id,
            type: 'module_completed',
            title: $title,
            message: $message,
            data: [
                'student_id' => $student->student_id,
                'module_name' => $moduleName,
                'total_letters' => $totalLetters,
                'hint_count' => $totalHintCount,
                'hint_usage' => $aggregatedHintData, // ✅ USE AGGREGATED DATA
                'completed_at' => now()->toISOString(),
            ],
            actionUrl: '/students/' . $student->student_id,
        );
        
    } catch (\Exception $e) {
        \Log::error('Failed to send hint usage notification: ' . $e->getMessage());
    }
}

/**
 * Save challenge mode completion and notify teacher
 * POST /api/student/challenge/complete
 */
public function completeChallenge(Request $request)
{
    try {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'module_name' => 'required|string|exists:gesture_modules,name',
            'mode' => 'required|string|in:master,infinite',
            'signs_mastered' => 'nullable|array',
            'signs_mastered.*' => 'string',
            'signs_practiced' => 'nullable|array',
            'signs_practiced.*' => 'string',
            'total_attempts' => 'nullable|integer',
            'star_rating' => 'nullable|integer|min:0|max:3',
            'xp_earned' => 'nullable|integer',
            'time_spent_seconds' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        $moduleName = $request->module_name;
        $mode = $request->mode;
        $signsMastered = $request->signs_mastered ?? [];
        $signsPracticed = $request->signs_practiced ?? [];
        $totalAttempts = $request->total_attempts ?? 0;
        $starRating = $request->star_rating ?? 0;
        $xpEarned = $request->xp_earned ?? 0;
        $timeSpent = $request->time_spent_seconds ?? 0;

        // Get module display name
        $module = GestureModule::where('name', $moduleName)->first();
        $displayModule = $module ? $module->display_name : ucwords(str_replace('_', ' ', $moduleName));

        // Format time
        $minutes = floor($timeSpent / 60);
        $seconds = $timeSpent % 60;
        $timeDisplay = $minutes > 0 ? "{$minutes}m {$seconds}s" : "{$seconds}s";

        // Star emojis
        $starEmojis = $starRating === 3 ? '⭐⭐⭐' : ($starRating === 2 ? '⭐⭐' : ($starRating === 1 ? '⭐' : ''));

        // Mode label
        $modeLabel = $mode === 'master' ? 'Master Challenge' : 'Infinite Practice';
        $modeEmoji = $mode === 'master' ? '🏆' : '♾️';

        // Student name
        $studentName = $student->first_name . ' ' . $student->last_name;

        // Build title
        $title = "{$modeEmoji} {$studentName} completed {$modeLabel}";

        // Build message with details
        $messageParts = [];
        
        if (!empty($signsMastered)) {
            $masteredDisplay = implode(' ', array_slice($signsMastered, 0, 10));
            if (count($signsMastered) > 10) {
                $masteredDisplay .= " +" . (count($signsMastered) - 10) . " more";
            }
            $messageParts[] = "Mastered: {$masteredDisplay}";
        }
        
        if (!empty($signsPracticed) && $mode === 'infinite') {
            $practicedDisplay = implode(' ', array_slice($signsPracticed, 0, 10));
            if (count($signsPracticed) > 10) {
                $practicedDisplay .= " +" . (count($signsPracticed) - 10) . " more";
            }
            $messageParts[] = "Practiced: {$practicedDisplay}";
        }
        
        if ($totalAttempts > 0) {
            $messageParts[] = "{$totalAttempts} attempts";
        }
        
        if ($starRating > 0) {
            $messageParts[] = $starEmojis;
        }
        
        if ($xpEarned > 0) {
            $messageParts[] = "+{$xpEarned} XP";
        }
        
        if ($timeSpent > 0) {
            $messageParts[] = "⏱️ {$timeDisplay}";
        }

        $message = implode(' • ', $messageParts);

        // If no details, use a default message
        if (empty($messageParts)) {
            $message = "Completed {$displayModule} in {$modeLabel} mode";
        }

        // ─── SAVE TO TEACHER NOTIFICATIONS ──────────────────────────────
        TeacherNotification::createForTeacher(
            teacherId: $student->teacher_id,
            type: 'challenge_completed',
            title: $title,
            message: $message,
            data: [
                'student_id' => $student->student_id,
                'module_name' => $moduleName,
                'module_display_name' => $displayModule,
                'mode' => $mode,
                'signs_mastered' => $signsMastered,
                'signs_practiced' => $signsPracticed,
                'total_attempts' => $totalAttempts,
                'star_rating' => $starRating,
                'xp_earned' => $xpEarned,
                'time_spent_seconds' => $timeSpent,
                'completed_at' => now()->toISOString(),
            ],
            actionUrl: '/students/' . $student->student_id,
        );

        \Log::info("✅ Challenge notification sent for teacher", [
            'student' => $studentName,
            'mode' => $mode,
            'module' => $moduleName,
            'xp' => $xpEarned,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Challenge completion saved and teacher notified',
        ]);

    } catch (\Exception $e) {
        \Log::error('Failed to save challenge completion: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}



}