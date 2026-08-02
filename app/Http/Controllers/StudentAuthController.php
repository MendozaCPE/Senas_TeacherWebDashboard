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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // Verify PIN
        if ($student->pin !== $request->pin) {
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
    'profile_picture' => $student->profile_picture ?? 'senya', // ← ADD THIS
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
        // 2. BUILD ADAPTIVE RECOMMENDATIONS
        // ============================================================
        
        $recommendedLessons = [];
        $recommendationReasons = [];
        $lessonIdsAdded = [];
        
        // Get ALL published lessons with their content and quiz
        $allLessons = Lesson::where('status', 'published')
            ->with(['contents', 'quiz.questions.options', 'module'])
            ->get();

        // ============================================================
        // SMART CONCEPT MATCHING
        // ============================================================
        
        foreach ($allLessons as $lesson) {
            // Skip if already added
            if (in_array($lesson->lesson_id, $lessonIdsAdded)) {
                continue;
            }
            
            // Extract ALL gesture names/concepts this lesson teaches
            $lessonConcepts = $this->extractLessonConcepts($lesson);
            
            // Skip if lesson doesn't teach any specific gestures
            if (empty($lessonConcepts)) {
                continue;
            }
            
            // Find which weak skills match the lesson's concepts
            $coveredSkills = [];
            $skillIdsFound = [];
            
            foreach ($lessonConcepts as $concept) {
                // Check if this concept is a weak skill
                foreach ($weakSkills as $weak) {
                    $weakName = strtoupper($weak['gesture_name'] ?? '');
                    $conceptName = strtoupper($concept);
                    
                    // Exact match or concept contains the weak skill name
                    if ($conceptName === $weakName || 
                        strpos($conceptName, $weakName) !== false ||
                        strpos($weakName, $conceptName) !== false) {
                        if (!in_array($weak['gesture_id'], $skillIdsFound)) {
                            $coveredSkills[] = $weak;
                            $skillIdsFound[] = $weak['gesture_id'];
                        }
                    }
                }
            }

            // Only add if it covers at least one weak skill
            if (!empty($coveredSkills)) {
                $recommendedLessons[] = $lesson;
                $lessonIdsAdded[] = $lesson->lesson_id;
                $recommendationReasons[$lesson->lesson_id] = [
                    'type' => 'weak_skill_practice',
                    'reason' => "Practice " . implode(', ', array_column($coveredSkills, 'display_name')),
                    'covered_skills' => $coveredSkills,
                    'priority' => count($coveredSkills),
                ];
            }
        }

        // CASE B: If we have fewer than 5 recommendations, add new skills based on learning goal
        if (count($recommendedLessons) < 5) {
            $neverPracticed = $masteryService->getNeverPracticedSkills($student->student_id, 10);
            $newSkillNames = array_column($neverPracticed, 'gesture_name');
            
            if (!empty($newSkillNames)) {
                foreach ($allLessons as $lesson) {
                    if (in_array($lesson->lesson_id, $lessonIdsAdded)) {
                        continue;
                    }
                    
                    $lessonConcepts = $this->extractLessonConcepts($lesson);
                    $matchesNewSkill = false;
                    $matchedSkills = [];
                    
                    foreach ($lessonConcepts as $concept) {
                        foreach ($neverPracticed as $skill) {
                            if (strtoupper($concept) === strtoupper($skill['gesture_name']) ||
                                strpos(strtoupper($concept), strtoupper($skill['gesture_name'])) !== false) {
                                $matchesNewSkill = true;
                                $matchedSkills[] = $skill;
                            }
                        }
                    }
                    
                    if ($matchesNewSkill) {
                        $recommendedLessons[] = $lesson;
                        $lessonIdsAdded[] = $lesson->lesson_id;
                        $recommendationReasons[$lesson->lesson_id] = [
                            'type' => 'new_skill',
                            'reason' => 'New skill to learn: ' . implode(', ', array_column($matchedSkills, 'display_name')),
                            'covered_skills' => $matchedSkills,
                            'priority' => count($matchedSkills),
                        ];
                        
                        if (count($recommendedLessons) >= 5) {
                            break;
                        }
                    }
                }
            }
        }

        // CASE C: Fallback - recommended by module order (only if still empty)
        if (empty($recommendedLessons)) {
            $completedLessonIds = LessonAssignment::where('student_id', $student->student_id)
                ->where('status', 'completed')
                ->pluck('lesson_id')
                ->toArray();

            $fallbackLessons = Lesson::where('status', 'published')
                ->whereNotIn('lesson_id', $completedLessonIds)
                ->whereNotIn('lesson_id', $lessonIdsAdded)
                ->with(['contents', 'quiz', 'module'])
                ->orderBy('module_order', 'asc')
                ->limit(10)
                ->get();

            foreach ($fallbackLessons as $lesson) {
                if (!in_array($lesson->lesson_id, $lessonIdsAdded)) {
                    $recommendedLessons[] = $lesson;
                    $lessonIdsAdded[] = $lesson->lesson_id;
                    $recommendationReasons[$lesson->lesson_id] = [
                        'type' => 'next_in_path',
                        'reason' => 'Next in your learning path',
                        'covered_skills' => [],
                        'priority' => 0,
                    ];
                }
            }
        }
// ============================================================
// 3. FORMAT RESPONSE WITH SEQUENTIAL LOCKING
// ============================================================
$formattedLessons = [];
$index = 0;

// First, get all lessons and determine their completion status
$lessonData = [];
foreach ($recommendedLessons as $lesson) {
    $assignment = LessonAssignment::where('student_id', $student->student_id)
        ->where('lesson_id', $lesson->lesson_id)
        ->first();

    $progress = DB::table('student_lesson_progress')
        ->where('student_id', $student->student_id)
        ->where('lesson_id', $lesson->lesson_id)
        ->first();

    $highestScore = DB::table('quiz_attempts as qa')
        ->join('quizzes as q', 'qa.quiz_id', '=', 'q.quiz_id')
        ->where('qa.student_id', $student->student_id)
        ->where('q.lesson_id', $lesson->lesson_id)
        ->where('qa.status', 'completed')
        ->max('qa.percentage');

    if ($highestScore === null && $progress) {
        $highestScore = $progress->quiz_score;
    }

    $isDone = $highestScore !== null && $highestScore >= 60;
    $isInProgress = !$isDone && $progress && (
        ($progress->current_step ?? 0) > 0 || $progress->lesson_completed
    );

    $reason = $recommendationReasons[$lesson->lesson_id] ?? [
        'type' => 'recommended',
        'reason' => 'Recommended for you',
        'covered_skills' => [],
        'priority' => 0,
    ];

    // Determine if this lesson matches the student's learning goal
    $lessonConcepts = $this->extractLessonConcepts($lesson);
    $isAlphabetLesson = false;
    $isNumberLesson = false;
    $isGreetingLesson = false;
    
    $alphabetLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    $numbers = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];
    $greetings = ['HELLO', 'GOOD MORNING', 'GOOD AFTERNOON', 'GOOD NIGHT', 'GOODBYE', 'THANK YOU', 'SEE YOU TOMORROW', 'HOW ARE YOU', 'NICE TO MEET YOU'];
    
    foreach ($lessonConcepts as $concept) {
        $conceptUpper = strtoupper($concept);
        if (in_array($conceptUpper, $alphabetLetters)) {
            $isAlphabetLesson = true;
        }
        if (in_array($conceptUpper, $numbers)) {
            $isNumberLesson = true;
        }
        if (in_array($conceptUpper, $greetings)) {
            $isGreetingLesson = true;
        }
    }

    $lessonData[] = [
        'lesson' => $lesson,
        'assignment' => $assignment,
        'progress' => $progress,
        'highestScore' => $highestScore,
        'isDone' => $isDone,
        'isInProgress' => $isInProgress,
        'reason' => $reason,
        'isAlphabetLesson' => $isAlphabetLesson,
        'isNumberLesson' => $isNumberLesson,
        'isGreetingLesson' => $isGreetingLesson,
        'lessonConcepts' => $lessonConcepts,
    ];
}

// 🆕 SORT: In-progress first, then pending (sorted by goal/priority), then completed
usort($lessonData, function($a, $b) use ($goal) {
    // 1. In-progress lessons go to the VERY TOP
    if ($a['isInProgress'] && !$b['isInProgress']) {
        return -1;
    }
    if (!$a['isInProgress'] && $b['isInProgress']) {
        return 1;
    }
    
    // 2. Completed lessons go to the VERY BOTTOM
    if ($a['isDone'] && !$b['isDone']) {
        return 1;
    }
    if (!$a['isDone'] && $b['isDone']) {
        return -1;
    }
    
    // 3. Both are pending (not done, not in progress) - sort by learning goal
    if ($goal === 'Alphabet_Numbers') {
        if ($a['isAlphabetLesson'] && !$b['isAlphabetLesson']) return -1;
        if (!$a['isAlphabetLesson'] && $b['isAlphabetLesson']) return 1;
        if ($a['isNumberLesson'] && !$b['isNumberLesson']) return -1;
        if (!$a['isNumberLesson'] && $b['isNumberLesson']) return 1;
    }
    
    if ($goal === 'Greetings') {
        if ($a['isGreetingLesson'] && !$b['isGreetingLesson']) return -1;
        if (!$a['isGreetingLesson'] && $b['isGreetingLesson']) return 1;
    }
    
    // 4. Then by priority (weak skills covered)
    if ($a['reason']['priority'] !== $b['reason']['priority']) {
        return $b['reason']['priority'] - $a['reason']['priority'];
    }
    
    // 5. Finally by module order
    return $a['lesson']->module_order - $b['lesson']->module_order;
});

// 🆕 APPLY SEQUENTIAL LOCKING
$unlockedNext = true; // First lesson is always unlocked
$formattedLessons = [];

foreach ($lessonData as $data) {
    $lesson = $data['lesson'];
    $assignment = $data['assignment'];
    $isDone = $data['isDone'];
    $isInProgress = $data['isInProgress'];
    $reason = $data['reason'];
    
    // Determine lock status based on sequential logic
    $isLocked = false;
    $isActive = false;
    
    if ($isDone) {
        // Completed lessons are unlocked (show checkmark)
        $isLocked = false;
        $isActive = false;
    } elseif ($unlockedNext) {
        // This is the first not-done lesson → UNLOCKED and ACTIVE
        $isLocked = false;
        $isActive = true;
        $unlockedNext = false; // After this, all subsequent lessons are locked
    } else {
        // All lessons after the active one are LOCKED
        $isLocked = true;
        $isActive = false;
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
        'status' => $isDone ? 'completed' : ($isInProgress ? 'in_progress' : 'pending'),
        'score' => $data['highestScore'],
        'total_steps' => $lesson->contents->count() + ($lesson->quiz ? 1 : 0),
        'has_quiz' => $lesson->quiz ? true : false,
        'done' => $isDone,
        'in_progress' => $isInProgress,
        'locked' => $isLocked,
        'active' => $isActive,
        'recommendation_reason' => $reason['reason'],
        'recommendation_type' => $reason['type'],
        'covered_skills' => $reason['covered_skills'] ?? [],
        'priority' => $reason['priority'] ?? 0,
        'is_alphabet_lesson' => $data['isAlphabetLesson'],
        'is_number_lesson' => $data['isNumberLesson'],
        'is_greeting_lesson' => $data['isGreetingLesson'],
        'lesson_concepts' => $data['lessonConcepts'],
    ];
}

// Limit to 20 lessons
$formattedLessons = array_slice($formattedLessons, 0, 20);

// 🆕 SMART SORTING: Prioritize based on learning goal
usort($formattedLessons, function($a, $b) use ($goal) {
    // Completed lessons go to the end (they're already done)
    if ($a['done'] !== $b['done']) {
        return $a['done'] ? 1 : -1;
    }
    
    // 🆕 If learning goal is Alphabet_Numbers
    if ($goal === 'Alphabet_Numbers') {
        // Alphabet lessons first
        if ($a['is_alphabet_lesson'] && !$b['is_alphabet_lesson']) {
            return -1;
        }
        if (!$a['is_alphabet_lesson'] && $b['is_alphabet_lesson']) {
            return 1;
        }
        // Then number lessons
        if ($a['is_number_lesson'] && !$b['is_number_lesson']) {
            return -1;
        }
        if (!$a['is_number_lesson'] && $b['is_number_lesson']) {
            return 1;
        }
    }
    
    // 🆕 If learning goal is Greetings
    if ($goal === 'Greetings') {
        // Greeting lessons first
        if ($a['is_greeting_lesson'] && !$b['is_greeting_lesson']) {
            return -1;
        }
        if (!$a['is_greeting_lesson'] && $b['is_greeting_lesson']) {
            return 1;
        }
    }
    
    // Then sort by priority (weak skills covered)
    if ($a['priority'] !== $b['priority']) {
        return $b['priority'] - $a['priority'];
    }
    
    // Finally by module order
    return $a['module_order'] - $b['module_order'];
});

// Limit to 20 lessons max (don't show all)
$formattedLessons = array_slice($formattedLessons, 0, 20);

        // ============================================================
        // 4. GET DAILY CHALLENGE INFO
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
            // Don't fail the whole request if challenge fails
        }

        return response()->json([
            'success' => true,
            'learning_path' => [
                'fsl_level' => $level,
                'learning_goal' => $goal,
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
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
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
    $validConcepts = [
        // Letters
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
        'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        // Numbers
        '1', '2', '3', '4', '5', '6', '7', '8', '9', '10',
        // Greetings
        'HELLO', 'GOOD MORNING', 'GOOD AFTERNOON', 'GOOD NIGHT', 'GOODBYE',
        'THANK YOU', 'SEE YOU TOMORROW', 'HOW ARE YOU', 'NICE TO MEET YOU',
        // Survival
        'UNDERSTAND', "DON'T UNDERSTAND", 'KNOW', "DON'T KNOW",
        'NO', 'YES', 'WRONG', 'CORRECT', 'SLOW', 'FAST'
    ];
    
    $conceptUpper = strtoupper(trim($concept));
    return in_array($conceptUpper, $validConcepts);
}

/**
 * Map gesture IDs to their display names
 */
private function getIdToGestureMap(): array
{
    return [
        '1' => 'A', '2' => 'B', '3' => 'C', '4' => 'D', '5' => 'E',
        '6' => 'F', '7' => 'G', '8' => 'H', '9' => 'I', '10' => 'J',
        '11' => 'K', '12' => 'L', '13' => 'M', '14' => 'N', '15' => 'O',
        '16' => 'P', '17' => 'Q', '18' => 'R', '19' => 'S', '20' => 'T',
        '21' => 'U', '22' => 'V', '23' => 'W', '24' => 'X', '25' => 'Y',
        '26' => 'Z',
        '27' => '1', '28' => '2', '29' => '3', '30' => '4', '31' => '5',
        '32' => '6', '33' => '7', '34' => '8', '35' => '9', '36' => '10',
        '47' => 'HELLO', '48' => 'THANK YOU', '49' => 'SEE YOU TOMORROW',
        '50' => 'HOW ARE YOU', '51' => 'NICE TO MEET YOU', '52' => 'UNDERSTAND',
        "53" => "DON'T UNDERSTAND", '54' => 'KNOW', "55" => "DON'T KNOW",
        '56' => 'NO', '57' => 'YES', '58' => 'WRONG', '59' => 'CORRECT',
        '60' => 'SLOW', '61' => 'FAST'
    ];
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
                    'title' => $moduleTitle,
                    'description' => $moduleDescription,
                    'quiz_passed' => $quizResult ? (bool) $quizResult->passed : false,
                    'quiz_score' => $quizResult ? $quizResult->percentage : null,
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
        $xpService->updateStreak($student);

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
            'module_name' => 'required|string|in:alphabet_part1,alphabet_part2,numbers,level1_numbers,level2_greetings,level3_survival',
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

        // 🔥 FIX: Instantiate XPService and update streak
        $xpService = new XPService();
        $xpService->updateStreak($student); 

        // 🎯 Count this session's practiced gestures toward today's daily
        // challenge "Master Gestures" goal — counts every time, regardless
        // of whether these gestures were practiced/mastered on a prior day.
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
 * Check if a module is locked for a student
 * 🔥 FIXED: Uses the stored mastery_level from the database
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
    $totalGestures = $gestureIds->count();
    
    if ($totalGestures === 0) {
        return false;
    }
    
    // Get all performances for these gestures
    $performances = GesturePerformance::where('student_id', $student->student_id)
                                     ->whereIn('gesture_id', $gestureIds)
                                     ->get()
                                     ->keyBy('gesture_id');
    
    // 🔥 USE THE STORED MASTERY LEVEL from the database
    $completedCount = 0;
    $needsPracticeCount = 0;
    
    foreach ($gestureIds as $gestureId) {
        $perf = $performances->get($gestureId);
        
        if (!$perf || $perf->attempts === 0) {
            $needsPracticeCount++;
            continue;
        }
        
        // ✅ Use the mastery_level that was already calculated and saved
        if (in_array($perf->mastery_level, ['mastered', 'proficient'])) {
            $completedCount++;
        } else {
            $needsPracticeCount++;
        }
    }
    
    // 🎯 UNLOCK when 70% of gestures are "completed" (mastered OR proficient)
    $unlockThreshold = 0.50; // 70% - change to 0.60 if you want 60%
    $requiredCompleted = ceil($totalGestures * $unlockThreshold);
    $maxNeedsPractice = 3;
    
    $isUnlocked = ($completedCount >= $requiredCompleted) || ($needsPracticeCount <= $maxNeedsPractice);
    
    return !$isUnlocked;
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
                ->join('lessons as l', 'lesson_assignments.lesson_id', '=', 'l.lesson_id')
                ->where('l.module_id', $moduleId)
                ->where('l.status', 'published')
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
            'weak_signs' => $weakSigns,
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
        // Get quiz performance
        $quizStats = DB::table('quiz_attempts')
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.quiz_id')
            ->where('quiz_attempts.student_id', $student->student_id)
            ->where('quiz_attempts.status', 'completed')
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

        // Get lessons completed
        $lessonsCompleted = DB::table('student_lesson_progress')
            ->where('student_id', $student->student_id)
            ->where('lesson_completed', true)
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


}