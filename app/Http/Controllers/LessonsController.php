<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\LessonContent;  // <-- ADD THIS LINE
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
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
        $user = Auth::user();
        $teacher = $user ? $user->teacher : null;
        
        if ($teacher) {
            $lessons = Lesson::where('teacher_id', $teacher->id)
                ->orderBy('module_order')
                ->get();
        } else {
            $lessons = collect();
        }

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
 *   - "Publish Lesson" -> save as draft first, then redirect to publish config
 */
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'lesson_type' => 'required|in:text,video,interactive,gesture',
        'difficulty' => 'required|in:beginner,intermediate,advanced',
        'contents' => 'nullable|array',
        'quiz' => 'nullable|array',
    ]);

    // Get the button that was clicked
    $buttonAction = $request->input('status', 'draft');

    // If "Publish Lesson" was clicked, we save as draft first
    $status = 'draft'; // Always save as draft initially
    if (! in_array($status, ['draft', 'published', 'archived'])) {
        $status = 'draft';
    }

    // Get the teacher_id
    $teacherId = null;

    if (Auth::check()) {
        $user = Auth::user();
        if (isset($user->teacher) && $user->teacher) {
            $teacherId = $user->teacher->teacher_id;
        } else {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $teacherId = $teacher->teacher_id;
            } else {
                try {
                    $teacher = Teacher::create([
                        'user_id' => $user->id,
                        'first_name' => $user->name ?? 'Teacher',
                        'last_name' => '',
                        'school_id' => 1,
                        'specialization' => 'General',
                    ]);
                    $teacherId = $teacher->teacher_id;
                } catch (\Exception $e) {
                    \Log::error('Failed to create teacher: '.$e->getMessage());
                }
            }
        }
    }

    if (empty($teacherId)) {
        $firstTeacher = Teacher::first();
        if ($firstTeacher) {
            $teacherId = $firstTeacher->teacher_id;
        } else {
            try {
                $teacher = Teacher::create([
                    'user_id' => 1,
                    'first_name' => 'Default',
                    'last_name' => 'Teacher',
                    'school_id' => 1,
                    'specialization' => 'General',
                ]);
                $teacherId = $teacher->teacher_id;
            } catch (\Exception $e) {
                $teacherId = 1;
            }
        }
    }

    if (empty($teacherId)) {
        $teacherId = 1;
    }

    $nextOrder = (int) Lesson::max('module_order') + 1;

    $lesson = DB::transaction(function () use ($request, $validated, $status, $teacherId, $nextOrder) {
        $lesson = Lesson::create([
            'teacher_id' => $teacherId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'lesson_type' => $validated['lesson_type'],
            'difficulty' => $validated['difficulty'],
            'module_order' => $nextOrder,
            'status' => $status,
        ]);

        // 2) Lesson contents - FIXED for file uploads
        $contents = $request->input('contents', []);
        $step = 1;
        foreach ($contents as $i => $c) {
            if (empty($c['title']) && empty($c['content_text']) && empty($c['gesture_name'])) {
                continue;
            }

            $mediaUrl = null;

            // Check if a file was uploaded for this content
            if ($request->hasFile("contents.$i.media")) {
                $file = $request->file("contents.$i.media");

                // Generate a unique filename
                $filename = time().'_'.$file->getClientOriginalName();

                // Store the file and get the path
                $path = $file->storeAs('lesson_media', $filename, 'public');

                // The path returned from storeAs is relative to storage/app/public
                // So we store it as is - it will be 'lesson_media/filename.jpg'
                $mediaUrl = $path;

                \Log::info('File uploaded for content '.$i.': '.$mediaUrl);
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

        // 3) Quiz - FIXED for file uploads
        $quizInput = $request->input('quiz', []);
        $validQuestions = array_filter($quizInput, fn ($q) => ! empty($q['question']));

        if (! empty($validQuestions)) {
            $totalPoints = count($validQuestions);

            // Calculate passing score (e.g., 60% of total points, minimum 1)
            $passingScore = max(1, round($totalPoints * 0.6));

            $quiz = Quiz::create([
                'lesson_id' => $lesson->lesson_id,
                'title' => 'Quiz: '.$lesson->title,
                'description' => 'Auto-generated quiz for '.$lesson->title,
                'total_points' => $totalPoints,
                'passing_score' => $passingScore,
            ]);

            $qNum = 1;
            foreach ($quizInput as $qi => $q) {
                if (empty($q['question'])) {
                    continue;
                }

                $qMedia = null;
                if ($request->hasFile("quiz.$qi.media")) {
                    $file = $request->file("quiz.$qi.media");
                    $filename = time().'_'.$file->getClientOriginalName();
                    $qMedia = $file->storeAs('quiz_media', $filename, 'public');
                    \Log::info('Quiz media uploaded: '.$qMedia);
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
                        if (trim((string) $text) === '') {
                            continue;
                        }
                        QuizOption::create([
                            'question_id' => $question->question_id,
                            'option_text' => $text,
                            'is_correct' => $idx === $correctIndex,
                        ]);
                    }
                }
            }
        }

        return $lesson;
    });

    // After saving the lesson, check which button was clicked
    if ($buttonAction === 'published') {
        // "Publish Lesson" was clicked - redirect to publish config
        return redirect()->route('lessons.publish.config', $lesson->lesson_id)
            ->with('success', 'Lesson saved as draft. Now configure who should receive this lesson.');
    }

    // "Save Draft" was clicked
    return redirect()->route('lessons.index')->with('success', 'Draft saved successfully!');
}

    /**
     * Live preview - render the mobile preview from posted form data
     * WITHOUT touching the database.
     */
    public function preview(Request $request)
    {
        $lessonData = [
            'title' => $request->input('title', 'Untitled Lesson'),
            'description' => $request->input('description', ''),
            'lesson_type' => $request->input('lesson_type', 'gesture'),
            'difficulty' => $request->input('difficulty', 'beginner'),
            'contents' => [],
            'quiz' => [],
        ];

        // Process contents with file handling (both new and existing)
        $contents = $request->input('contents', []);
        foreach ($contents as $index => $c) {
            if (empty($c['title']) && empty($c['content_text']) && empty($c['gesture_name'])) {
                continue;
            }

            $mediaUrl = null;
            $isTemp = false;

            // Check if a new file was uploaded
            if ($request->hasFile("contents.$index.media")) {
                $file = $request->file("contents.$index.media");
                $tempPath = $file->store('temp_preview', 'public');
                $mediaUrl = $tempPath;
                $isTemp = true;
            }
            // Check if there's existing media from the database
            elseif (isset($c['existing_media']) && $c['existing_media']) {
                $mediaUrl = $c['existing_media'];
                $isTemp = false;
            }

            $lessonData['contents'][] = [
                'content_type' => $c['content_type'] ?? 'text',
                'title' => $c['title'] ?? null,
                'content_text' => $c['content_text'] ?? null,
                'media' => $mediaUrl,
                'gesture_name' => $c['gesture_name'] ?? null,
                'is_temp' => $isTemp,
            ];
        }

        // Process quiz with file handling (both new and existing)
        $quizInput = $request->input('quiz', []);
        foreach ($quizInput as $index => $q) {
            if (empty($q['question'])) {
                continue;
            }

            $qMedia = null;
            $isTemp = false;

            // Check if a new file was uploaded
            if ($request->hasFile("quiz.$index.media")) {
                $file = $request->file("quiz.$index.media");
                $tempPath = $file->store('temp_preview', 'public');
                $qMedia = $tempPath;
                $isTemp = true;
            }
            // Check if there's existing media from the database
            elseif (isset($q['existing_media']) && $q['existing_media']) {
                $qMedia = $q['existing_media'];
                $isTemp = false;
            }

            $lessonData['quiz'][] = [
                'question' => $q['question'],
                'type' => $q['type'] ?? 'multiple_choice',
                'media' => $qMedia,
                'options' => $q['options'] ?? ['Option A', 'Option B'],
                'correct' => $q['correct'] ?? 0,
                'is_temp' => $isTemp,
            ];
        }

        // Calculate total slides including both contents AND quiz
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
        'contents' => $lesson->contents->map(function ($content) {
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
                'correct' => $question->options->search(fn ($opt) => $opt->is_correct),
            ];
        }
    }

    // FIX: Include both contents AND quiz in total slides
    $totalSlides = count($lessonData['contents']) + count($lessonData['quiz']);

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
        'contents' => $lesson->contents->map(function ($content) {
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
                'correct' => $question->options->search(fn ($opt) => $opt->is_correct),
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
    if (! in_array($status, ['draft', 'published', 'archived'])) {
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
            $lesson->quiz->questions()->each(function ($question) {
                $question->options()->delete();
            });
            $lesson->quiz->questions()->delete();
            $lesson->quiz()->delete();
        }

        $quizInput = $request->input('quiz', []);
        $validQuestions = array_filter($quizInput, fn ($q) => ! empty($q['question']));

        if (! empty($validQuestions)) {
            $totalPoints = count($validQuestions);

            // Calculate passing score (e.g., 60% of total points, minimum 1)
            $passingScore = max(1, round($totalPoints * 0.6));

            $quiz = Quiz::create([
                'lesson_id' => $lesson->lesson_id,
                'title' => 'Quiz: '.$lesson->title,
                'description' => 'Auto-generated quiz for '.$lesson->title,
                'total_points' => $totalPoints,
                'passing_score' => $passingScore,
            ]);

            $qNum = 1;
            foreach ($quizInput as $qi => $q) {
                if (empty($q['question'])) {
                    continue;
                }

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
                        if (trim((string) $text) === '') {
                            continue;
                        }
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

/**
 * Show the publish configuration page
 */
/**
 * Show the publish configuration page
 */
public function showPublishConfig($id)
{
    $lesson = Lesson::findOrFail($id);

    // Get all students with their important information
    // Remove 'email' since it doesn't exist in your students table
    $students = DB::table('students')
        ->select('student_id', 'first_name', 'last_name',
            'lrn',  // Add LRN for identification
            'fsl_mastery_level as mastery_level',   // Map to mastery_level for display
            'program_type as program',              // Map to program for display
            'grade_level', 'section', 'status')
        ->where('status', 'active')
        ->orderBy('last_name')
        ->get();

    // Group students by program and mastery level for filtering
    $programs = $students->pluck('program')->unique()->filter()->values();
    $masteryLevels = $students->pluck('mastery_level')->unique()->filter()->values();

    return view('lessons.publish-config', compact('lesson', 'students', 'programs', 'masteryLevels'));
}

/**
 * Publish the lesson to selected students
 */
/**
 * Publish the lesson to selected students
 */
public function publishLesson(Request $request, $id)
{
    $validated = $request->validate([
        'publish_option' => 'required|in:all,program,mastery,selected',
        'program' => 'required_if:publish_option,program|nullable|string',
        'mastery_level' => 'required_if:publish_option,mastery|nullable|string',
        'students' => 'required_if:publish_option,selected|nullable|array',
        'students.*' => 'exists:students,student_id',
        'notify_students' => 'boolean',
        'send_reminder' => 'boolean',
    ]);

    $lesson = Lesson::findOrFail($id);

    // Determine which students to publish to
    $studentIds = [];

    switch ($validated['publish_option']) {
        case 'all':
            $studentIds = DB::table('students')
                ->where('status', 'active')
                ->pluck('student_id')
                ->toArray();
            break;

        case 'program':
            $studentIds = DB::table('students')
                ->where('status', 'active')
                ->where('program_type', $validated['program']) // Use program_type from database
                ->pluck('student_id')
                ->toArray();
            break;

        case 'mastery':
            $studentIds = DB::table('students')
                ->where('status', 'active')
                ->where('fsl_mastery_level', $validated['mastery_level']) // Use fsl_mastery_level from database
                ->pluck('student_id')
                ->toArray();
            break;

        case 'selected':
            $studentIds = $validated['students'];
            break;
    }

    // Update the lesson status to published
    $lesson->update([
        'status' => 'published',
        'published_at' => now(),
    ]);

    // Create records in lesson_assignments table
    $assignedCount = 0;
    foreach ($studentIds as $studentId) {
        $exists = LessonAssignment::where('lesson_id', $lesson->lesson_id)
                                  ->where('student_id', $studentId)
                                  ->exists();

        if (! $exists) {
            LessonAssignment::create([
                'lesson_id' => $lesson->lesson_id,
                'student_id' => $studentId,
                'assigned_at' => now(),
                'status' => 'pending',
                'notified' => $request->input('notify_students', false),
            ]);
            $assignedCount++;
        }
    }

    $message = "Lesson published successfully to {$assignedCount} students!";

    if ($request->input('notify_students')) {
        $message .= ' Students will be notified.';
    }

    return redirect()->route('lessons.index')->with('success', $message);
}
}
