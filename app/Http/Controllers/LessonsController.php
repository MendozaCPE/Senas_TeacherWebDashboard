<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LessonsController extends Controller
{
    /**
     * Show the list of lessons.
     */
    public function index()
    {
        $lessons = Lesson::orderBy('module_order')->get();
        return view('lessons', compact('lessons'));
    }

    /**
     * Show the create form.
     */
    public function create()
    {
        return view('lessons.create');
    }

    /**
     * Persist a new lesson + contents + quiz.
     * The submit button decides the status:
     *   - "Save Draft"     -> status=draft
     *   - "Publish Lesson" -> status=published
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'lesson_type'  => 'required|in:text,video,interactive,gesture',
            'difficulty'   => 'required|in:beginner,intermediate,advanced',
            'contents'     => 'nullable|array',
            'quiz'         => 'nullable|array',
        ]);

        $status = $request->input('status', 'draft');
        if (!in_array($status, ['draft', 'published', 'archived'])) {
            $status = 'draft';
        }

        // Get the teacher_id properly - FIXED with better fallback
        $teacherId = null;
        
        // Try to get teacher from authenticated user first
        if (Auth::check()) {
            $user = Auth::user();
            \Log::info('User authenticated:', ['user_id' => $user->id]);
            
            // Check if user has a teacher relationship
            if (isset($user->teacher) && $user->teacher) {
                $teacherId = $user->teacher->teacher_id;
                \Log::info('Teacher found via relationship:', ['teacher_id' => $teacherId]);
            } else {
                \Log::info('User does not have teacher relationship, checking if teacher exists...');
                
                // Check if a teacher record exists for this user
                $teacher = Teacher::where('user_id', $user->id)->first();
                if ($teacher) {
                    $teacherId = $teacher->teacher_id;
                    \Log::info('Teacher found via query:', ['teacher_id' => $teacherId]);
                } else {
                    // Create a new teacher record
                    \Log::info('Creating new teacher record for user: ' . $user->id);
                    try {
                        $teacher = Teacher::create([
                            'user_id' => $user->id,
                            'first_name' => $user->name ?? 'Teacher',
                            'last_name' => '',
                            'school_id' => 1, // Default school
                            'specialization' => 'General',
                        ]);
                        $teacherId = $teacher->teacher_id;
                        \Log::info('Teacher created:', ['teacher_id' => $teacherId]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to create teacher: ' . $e->getMessage());
                    }
                }
            }
        } else {
            \Log::warning('User is not authenticated');
        }

        // If still no teacher_id, use fallback
        if (empty($teacherId)) {
            \Log::warning('No teacher_id found, using fallback');
            
            // Try to get the first teacher from database
            $firstTeacher = Teacher::first();
            if ($firstTeacher) {
                $teacherId = $firstTeacher->teacher_id;
                \Log::info('Using first teacher from database:', ['teacher_id' => $teacherId]);
            } else {
                // Create a default teacher as last resort
                try {
                    $teacher = Teacher::create([
                        'user_id' => 1,
                        'first_name' => 'Default',
                        'last_name' => 'Teacher',
                        'school_id' => 1,
                        'specialization' => 'General',
                    ]);
                    $teacherId = $teacher->teacher_id;
                    \Log::info('Created default teacher:', ['teacher_id' => $teacherId]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create default teacher: ' . $e->getMessage());
                    // Ultimate fallback - hardcode to 1
                    $teacherId = 1;
                    \Log::info('Using hardcoded teacher_id: 1');
                }
            }
        }

        // Final check - if still null, hardcode to 1
        if (empty($teacherId)) {
            $teacherId = 1;
            \Log::info('Final fallback - using hardcoded teacher_id: 1');
        }

        \Log::info('Final teacher_id being used:', ['teacher_id' => $teacherId]);

        // Next module_order
        $nextOrder = (int) Lesson::max('module_order') + 1;

        $lesson = DB::transaction(function () use ($request, $validated, $status, $teacherId, $nextOrder) {

            // 1) Lesson
            $lesson = Lesson::create([
                'teacher_id'   => $teacherId,
                'title'        => $validated['title'],
                'description'  => $validated['description'] ?? null,
                'lesson_type'  => $validated['lesson_type'],
                'difficulty'   => $validated['difficulty'],
                'module_order' => $nextOrder,
                'status'       => $status,
            ]);

            // 2) Lesson contents
            $contents = $request->input('contents', []);
            $step = 1;
            foreach ($contents as $i => $c) {
                if (empty($c['title']) && empty($c['content_text']) && empty($c['gesture_name'])) {
                    continue;
                }

                $mediaUrl = null;
                if ($request->hasFile("contents.$i.media")) {
                    $mediaUrl = $request->file("contents.$i.media")
                        ->store('lesson_media', 'public');
                }

                LessonContent::create([
                    'lesson_id'    => $lesson->lesson_id,
                    'step_number'  => $step++,
                    'content_type' => $c['content_type'] ?? 'text',
                    'title'        => $c['title'] ?? null,
                    'content_text' => $c['content_text'] ?? null,
                    'media_url'    => $mediaUrl,
                    'gesture_name' => $c['gesture_name'] ?? null,
                ]);
            }

            // 3) Quiz
            $quizInput = $request->input('quiz', []);
            $validQuestions = array_filter($quizInput, fn($q) => !empty($q['question']));

            if (!empty($validQuestions)) {
                $totalPoints = count($validQuestions);

                $quiz = Quiz::create([
                    'lesson_id'     => $lesson->lesson_id,
                    'title'         => 'Quiz: ' . $lesson->title,
                    'description'   => 'Auto-generated quiz for ' . $lesson->title,
                    'total_points'  => $totalPoints,
                    'passing_score' => 60,
                ]);

                $qNum = 1;
                foreach ($quizInput as $qi => $q) {
                    if (empty($q['question'])) continue;

                    $qMedia = null;
                    if ($request->hasFile("quiz.$qi.media")) {
                        $qMedia = $request->file("quiz.$qi.media")
                            ->store('quiz_media', 'public');
                    }

                    $question = QuizQuestion::create([
                        'quiz_id'          => $quiz->quiz_id,
                        'question_number'  => $qNum++,
                        'question_type'    => $q['type'] ?? 'multiple_choice',
                        'question_text'    => $q['question'],
                        'media_url'        => $qMedia,
                        'gesture_required' => $q['gesture_required'] ?? null,
                        'points'           => 1,
                    ]);

                    $correctIndex = isset($q['correct']) ? (int) $q['correct'] : -1;

                    if (($q['type'] ?? 'multiple_choice') === 'true_false') {
                        $tfOptions = ['True', 'False'];
                        foreach ($tfOptions as $idx => $text) {
                            QuizOption::create([
                                'question_id' => $question->question_id,
                                'option_text' => $text,
                                'is_correct'  => $idx === $correctIndex,
                            ]);
                        }
                    } else {
                        $options = $q['options'] ?? [];
                        foreach ($options as $idx => $text) {
                            if (trim((string) $text) === '') continue;
                            QuizOption::create([
                                'question_id' => $question->question_id,
                                'option_text' => $text,
                                'is_correct'  => $idx === $correctIndex,
                            ]);
                        }
                    }
                }
            }

            return $lesson;
        });

        $msg = $status === 'published'
            ? 'Lesson published successfully!'
            : 'Draft saved successfully!';

        return redirect()->route('lessons.index')->with('success', $msg);
    }

    /**
     * Live preview - render the mobile preview from posted form data
     * WITHOUT touching the database.
     */
    public function preview(Request $request)
    {
        $lessonData = [
            'title'       => $request->input('title', 'Untitled Lesson'),
            'description' => $request->input('description', ''),
            'lesson_type' => $request->input('lesson_type', 'gesture'),
            'difficulty'  => $request->input('difficulty', 'beginner'),
            'contents'    => array_values(array_filter(
                $request->input('contents', []),
                fn($c) => !empty($c['title']) || !empty($c['content_text']) || !empty($c['gesture_name'])
            )),
            'quiz'        => array_values(array_filter(
                $request->input('quiz', []),
                fn($q) => !empty($q['question'])
            )),
        ];

        $totalSlides = count($lessonData['contents']) + count($lessonData['quiz']);

        return view('lessons.preview', compact('lessonData', 'totalSlides'));
    }
public function view($id)
{
    $lesson = Lesson::with(['contents', 'quiz.questions.options'])->findOrFail($id);
    
    // Format data for preview
    $lessonData = [
        'title' => $lesson->title,
        'description' => $lesson->description,
        'lesson_type' => $lesson->lesson_type,
        'difficulty' => $lesson->difficulty,
        'contents' => $lesson->contents->map(function($content) {
            return [
                'content_type' => $content->content_type,
                'title' => $content->title,
                'content_text' => $content->content_text,
                'media' => $content->media_url,
                'gesture_name' => $content->gesture_name,
            ];
        })->toArray(),
        'quiz' => [],
    ];

    // Format quiz data if exists
    if ($lesson->quiz) {
        foreach ($lesson->quiz->questions as $question) {
            $lessonData['quiz'][] = [
                'question' => $question->question_text,
                'type' => $question->question_type,
                'media' => $question->media_url,
                'options' => $question->options->pluck('option_text')->toArray(),
                'correct' => $question->options->search(fn($opt) => $opt->is_correct),
            ];
        }
    }

    $totalSlides = count($lessonData['contents']) + count($lessonData['quiz']);

    // Use response()->view() to render without layout
    return response()->view('lessons.preview', compact('lessonData', 'totalSlides'));
}
/**
 * Show the edit form for a lesson
 */
public function edit($id)
{
    $lesson = Lesson::with(['contents', 'quiz.questions.options'])->findOrFail($id);
    
    // Format data for the edit form
    $lessonData = [
        'lesson_id' => $lesson->lesson_id,
        'title' => $lesson->title,
        'description' => $lesson->description,
        'lesson_type' => $lesson->lesson_type,
        'difficulty' => $lesson->difficulty,
        'status' => $lesson->status,
        'contents' => $lesson->contents->map(function($content) {
            return [
                'content_id' => $content->content_id,
                'content_type' => $content->content_type,
                'title' => $content->title,
                'content_text' => $content->content_text,
                'media_url' => $content->media_url,
                'gesture_name' => $content->gesture_name,
            ];
        })->toArray(),
        'quiz' => [],
    ];

    // Format quiz data if exists
    if ($lesson->quiz) {
        foreach ($lesson->quiz->questions as $question) {
            $lessonData['quiz'][] = [
                'question_id' => $question->question_id,
                'question' => $question->question_text,
                'type' => $question->question_type,
                'media' => $question->media_url,
                'options' => $question->options->pluck('option_text')->toArray(),
                'correct' => $question->options->search(fn($opt) => $opt->is_correct),
            ];
        }
    }

    return view('lessons.edit', compact('lessonData'));
}

/**
 * Update a lesson
 */
public function update(Request $request, $id)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'lesson_type' => 'required|in:text,video,interactive,gesture',
        'difficulty' => 'required|in:beginner,intermediate,advanced',
        'contents' => 'nullable|array',
        'quiz' => 'nullable|array',
    ]);

    $status = $request->input('status', 'draft');
    if (!in_array($status, ['draft', 'published', 'archived'])) {
        $status = 'draft';
    }

    $lesson = Lesson::findOrFail($id);

    DB::transaction(function () use ($request, $validated, $status, $lesson) {
        // Update lesson
        $lesson->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'lesson_type' => $validated['lesson_type'],
            'difficulty' => $validated['difficulty'],
            'status' => $status,
        ]);

        // Delete existing contents and recreate
        $lesson->contents()->delete();
        $contents = $request->input('contents', []);
        $step = 1;
        foreach ($contents as $i => $c) {
            if (empty($c['title']) && empty($c['content_text']) && empty($c['gesture_name'])) {
                continue;
            }

            $mediaUrl = null;
            if ($request->hasFile("contents.$i.media")) {
                $mediaUrl = $request->file("contents.$i.media")->store('lesson_media', 'public');
            } elseif (isset($c['existing_media'])) {
                $mediaUrl = $c['existing_media'];
            }

            LessonContent::create([
                'lesson_id' => $lesson->lesson_id,
                'step_number' => $step++,
                'content_type' => $c['content_type'] ?? 'text',
                'title' => $c['title'] ?? null,
                'content_text' => $c['content_text'] ?? null,
                'media_url' => $mediaUrl,
                'gesture_name' => $c['gesture_name'] ?? null,
            ]);
        }

        // Handle quiz updates
        // Delete existing quiz if exists
        if ($lesson->quiz) {
            $lesson->quiz->questions()->each(function($question) {
                $question->options()->delete();
            });
            $lesson->quiz->questions()->delete();
            $lesson->quiz()->delete();
        }

        $quizInput = $request->input('quiz', []);
        $validQuestions = array_filter($quizInput, fn($q) => !empty($q['question']));

        if (!empty($validQuestions)) {
            $totalPoints = count($validQuestions);

            $quiz = Quiz::create([
                'lesson_id' => $lesson->lesson_id,
                'title' => 'Quiz: ' . $lesson->title,
                'description' => 'Auto-generated quiz for ' . $lesson->title,
                'total_points' => $totalPoints,
                'passing_score' => 60,
            ]);

            $qNum = 1;
            foreach ($quizInput as $qi => $q) {
                if (empty($q['question'])) continue;

                $qMedia = null;
                if ($request->hasFile("quiz.$qi.media")) {
                    $qMedia = $request->file("quiz.$qi.media")->store('quiz_media', 'public');
                } elseif (isset($q['existing_media'])) {
                    $qMedia = $q['existing_media'];
                }

                $question = QuizQuestion::create([
                    'quiz_id' => $quiz->quiz_id,
                    'question_number' => $qNum++,
                    'question_type' => $q['type'] ?? 'multiple_choice',
                    'question_text' => $q['question'],
                    'media_url' => $qMedia,
                    'gesture_required' => $q['gesture_required'] ?? null,
                    'points' => 1,
                ]);

                $correctIndex = isset($q['correct']) ? (int) $q['correct'] : -1;

                if (($q['type'] ?? 'multiple_choice') === 'true_false') {
                    $tfOptions = ['True', 'False'];
                    foreach ($tfOptions as $idx => $text) {
                        QuizOption::create([
                            'question_id' => $question->question_id,
                            'option_text' => $text,
                            'is_correct' => $idx === $correctIndex,
                        ]);
                    }
                } else {
                    $options = $q['options'] ?? [];
                    foreach ($options as $idx => $text) {
                        if (trim((string) $text) === '') continue;
                        QuizOption::create([
                            'question_id' => $question->question_id,
                            'option_text' => $text,
                            'is_correct' => $idx === $correctIndex,
                        ]);
                    }
                }
            }
        }
    });

    $msg = $status === 'published' 
        ? 'Lesson updated and published successfully!' 
        : 'Lesson draft updated successfully!';

    return redirect()->route('lessons.index')->with('success', $msg);
}
}