<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\Module;
use App\Models\LessonContent;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\Teacher;
use App\Services\DeepSeekService;
use App\Services\GestureMediaResolver;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
        // Get all lessons (works with existing view)
        $lessons = Lesson::where('teacher_id', $teacher->id)
            ->orderBy('module_order')
            ->get();
        
        // Get modules with their lessons (for future use)
        $modules = Module::where('teacher_id', $teacher->id)
            ->with(['lessons' => function($query) {
                $query->orderBy('module_order');
            }])
            ->orderBy('module_order')
            ->get();
        
        // Get orphaned lessons (lessons without a module)
        $orphanedLessons = Lesson::where('teacher_id', $teacher->id)
            ->whereNull('module_id')
            ->orderBy('module_order')
            ->get();
    } else {
        $lessons = collect();
        $modules = collect();
        $orphanedLessons = collect();
    }

    return view('lessons', compact('lessons', 'modules', 'orphanedLessons'));
}

    /**
     * Show the create form.
     */
    public function create()
{
    $teacherId = $this->resolveTeacherId();
    $modules = Module::where('teacher_id', $teacherId)
        ->orderBy('module_order')
        ->get();

    return view('lessons.create', compact('modules'));
}

    /**
     * AI PDF Lesson Generator — POST /lessons/ai-generate-pdf
     * Accepts a PDF file, extracts text, sends to DeepSeek, returns JSON lesson.
     */
    public function aiGeneratePdf(Request $request)
    {
        $request->validate([
            'pdf'          => 'required|file|mimes:pdf|max:20480',
            'difficulty'   => 'required|in:beginner,intermediate,advanced',
            'lesson_type'  => 'required|in:gesture,text,interactive',
            'num_slides'   => 'required|integer|min:3|max:30',
            'num_mc'       => 'required|integer|min:0|max:15',
            'num_tf'       => 'required|integer|min:0|max:15',
            'instructions' => 'nullable|string|max:1000',
        ]);

        if ((int)$request->input('num_mc') + (int)$request->input('num_tf') < 1) {
            return response()->json(['message' => 'Please generate at least 1 quiz question.'], 422);
        }

        try {
            $pdfPath = $request->file('pdf')->getRealPath();
            $pdfText = $this->extractPdfText($pdfPath);

            // If extraction yielded nothing, still try — give AI the filename as context
            if (empty(trim($pdfText))) {
                $pdfText = 'PDF filename: ' . $request->file('pdf')->getClientOriginalName()
                    . '. The document appears to be image-based. Generate an FSL lesson based on the filename topic.';
            }

            $deepSeek = new DeepSeekService();
            $lesson   = $deepSeek->generateFromPdfText($pdfText, [
                'difficulty'   => $request->input('difficulty'),
                'lesson_type'  => $request->input('lesson_type'),
                'num_slides'   => (int) $request->input('num_slides'),
                'num_mc'       => (int) $request->input('num_mc', 3),
                'num_tf'       => (int) $request->input('num_tf', 2),
                'instructions' => $request->input('instructions', ''),
            ]);

            $resolver = new GestureMediaResolver();
            $lesson   = $resolver->resolve($lesson);

            return response()->json($lesson);

        } catch (\Throwable $e) {
            \Log::error('AI PDF Generate failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'PDF generation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Extract plain text from a PDF file path.
     * Uses smalot/pdfparser if installed, otherwise basic stream extraction.
     */
    private function extractPdfText(string $pdfPath): string
    {
        // Try smalot/pdfparser first — best quality
        if (class_exists('\Smalot\PdfParser\Parser')) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf    = $parser->parseFile($pdfPath);
                $text   = $pdf->getText();
                if (!empty(trim($text))) {
                    return $text;
                }
                // Fallthrough to per-page extraction if getText() returns empty
                $pages = $pdf->getPages();
                $text  = '';
                foreach ($pages as $page) {
                    $text .= $page->getText() . "\n";
                }
                if (!empty(trim($text))) {
                    return $text;
                }
            } catch (\Throwable $e) {
                Log::warning('smalot/pdfparser failed, trying fallback: ' . $e->getMessage());
            }
        }

        // Fallback: raw PDF stream extraction
        $content = @file_get_contents($pdfPath);
        if ($content === false) return '';

        $text = '';

        // Decompress flate-encoded streams and extract text
        preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $content, $streamMatches);
        foreach ($streamMatches[1] as $stream) {
            $decoded = @gzuncompress($stream);
            if ($decoded === false) {
                $decoded = @gzinflate(substr($stream, 2));
            }
            if ($decoded !== false) {
                preg_match_all('/\(([^)]{1,})\)/', $decoded, $paren);
                foreach ($paren[1] as $p) {
                    $text .= preg_replace('/\\\\./', ' ', $p) . ' ';
                }
            }
        }

        // Direct BT/ET text blocks
        preg_match_all('/BT(.*?)ET/s', $content, $btMatches);
        foreach ($btMatches[1] as $block) {
            preg_match_all('/\(([^)]+)\)/', $block, $strMatches);
            foreach ($strMatches[1] as $str) {
                $text .= preg_replace('/\\\\./', ' ', $str) . ' ';
            }
        }

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    /**
     * AI Lesson Generator — POST /lessons/ai-generate
     * Accepts topic + settings, calls DeepSeek, resolves gesture media, returns JSON.
     */
    public function aiGenerate(Request $request)
    {
        $validated = $request->validate([
            'topic'                => 'required|string|max:200',
            'difficulty'           => 'required|in:beginner,intermediate,advanced',
            'lesson_type'          => 'required|in:gesture,text,interactive',
            'num_slides'           => 'required|integer|min:3|max:30',
            'num_mc'               => 'required|integer|min:0|max:15',
            'num_tf'               => 'required|integer|min:0|max:15',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        if ((int)$validated['num_mc'] + (int)$validated['num_tf'] < 1) {
            return response()->json(['message' => 'Please generate at least 1 quiz question.'], 422);
        }

        try {
            $deepSeek = new DeepSeekService();
            $lesson   = $deepSeek->generate($validated);

            $resolver = new GestureMediaResolver();
            $lesson   = $resolver->resolve($lesson);

            return response()->json($lesson);
        } catch (\Throwable $e) {
            \Log::error('AI Lesson Generate failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'AI generation failed: ' . $e->getMessage(),
            ], 500);
        }
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
        'title'                       => 'required|string|max:255',
        'description'                 => 'nullable|string',
        'lesson_type'                 => 'required|in:text,video,interactive,gesture',
        'difficulty'                  => 'required|in:beginner,intermediate,advanced',
        'module_action'               => 'nullable|in:none,existing,new',
        'module_id'                   => 'nullable|required_if:module_action,existing|exists:modules,module_id',
        'new_module.title'            => 'nullable|required_if:module_action,new|string|max:255',
        'new_module.description'      => 'nullable|string|max:1000',
        'contents'                    => 'nullable|array',
        'contents.*.media'            => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv|max:51200',
        'quiz'                        => 'nullable|array',
        'quiz.*.media'                => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
        'quiz.*.options.*.image'      => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
    ]);

    // Read which button was clicked: 'draft' or 'published'
    $buttonAction = $request->input('status', 'draft');
    $status = in_array($buttonAction, ['draft', 'published', 'archived']) ? $buttonAction : 'draft';

    $teacherId = $this->resolveTeacherId();
    $moduleId = $this->resolveModuleId($request, $teacherId);

    $nextOrder = (int) Lesson::max('module_order') + 1;

    $lesson = DB::transaction(function () use ($request, $validated, $status, $teacherId, $moduleId, $nextOrder) {
        $lesson = Lesson::create([
            'teacher_id'   => $teacherId,
            'module_id'    => $moduleId,
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'lesson_type'  => $validated['lesson_type'],
            'difficulty'   => $validated['difficulty'],
            'module_order' => $nextOrder,
            'status'       => $status,
        ]);

        $this->persistLessonContents($request, $lesson, $request->input('contents', []));

        $quizInput = $request->input('quiz', []);
        $this->persistQuizForLesson($request, $lesson, $quizInput);

        return $lesson;
    });

    // After saving, redirect based on which button was clicked
    if ($buttonAction === 'published') {
        return redirect()->route('lessons.publish.config', $lesson->lesson_id)
            ->with('success', 'Lesson saved as draft. Select a module, then choose who should receive this lesson.');
    }

    return redirect()->route('lessons.index')->with('success', 'Draft saved successfully!');
}

    /**
     * AJAX endpoint: upload a single media file and return its public URL.
     * POST /lessons/upload-media
     * Returns JSON: { "url": "/storage/path/to/file.jpg", "path": "path/to/file.jpg" }
     */
    public function uploadMedia(Request $request)
    {
        $request->validate([
            'file'    => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv|max:51200',
            'context' => 'nullable|string|in:lesson_media,quiz_media,quiz_option_media,temp_preview',
        ]);

        $file      = $request->file('file');
        $directory = $request->input('context', 'lesson_media');

        $path = $this->uploadPublicFile($file, $directory);

        if (! $path) {
            return response()->json(['message' => 'Upload failed.'], 500);
        }

        return response()->json([
            'path' => $path,
            'url'  => asset('storage/' . $path),
        ]);
    }

    /**
     * Live preview - render the mobile preview from posted form data
     * WITHOUT touching the database.
     */
    public function preview(Request $request)
    {
        try {
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

            $contentFile = $this->getContentUploadedFile($request, $index);
            if ($contentFile) {
                $mediaUrl = $this->uploadPublicFile($contentFile, 'temp_preview');
                $isTemp = true;
            } elseif (! empty($c['existing_media'])) {
                $mediaUrl = $c['existing_media'];
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
        $quizFiles = $request->file('quiz') ?? [];
        foreach ($quizInput as $index => $q) {
            if (empty($q['question'])) {
                continue;
            }

            $qMedia = null;
            $isTemp = false;

            $questionFile = $this->getQuizUploadedFile($request, $index, 'media');
            if ($questionFile) {
                $qMedia = $this->uploadPublicFile($questionFile, 'temp_preview');
                $isTemp = true;
            } elseif (! empty($q['existing_media'])) {
                $qMedia = $q['existing_media'];
            }

            $rawOptions = is_array($q['options'] ?? null) ? $q['options'] : [];
            $optionFiles = [];
            if (isset($quizFiles[$index]['options']) && is_array($quizFiles[$index]['options'])) {
                $optionFiles = $quizFiles[$index]['options'];
            }

            $processedOptions = [];
            foreach ($this->collectOptionIndices($rawOptions, $optionFiles) as $optIndex) {
                $optData = $rawOptions[$optIndex] ?? [];
                $optText = is_array($optData) ? ($optData['text'] ?? '') : (string) $optData;
                $optImage = null;

                $optionFile = null;
                if (isset($optionFiles[$optIndex]['image']) && $optionFiles[$optIndex]['image'] instanceof UploadedFile) {
                    $optionFile = $optionFiles[$optIndex]['image'];
                } else {
                    $optionFile = $this->getQuizUploadedFile($request, $index, "options.{$optIndex}.image");
                }

                if ($optionFile) {
                    $optImage = $this->uploadPublicFile($optionFile, 'temp_preview');
                } elseif (is_array($optData) && ! empty($optData['existing_image'])) {
                    $optImage = $optData['existing_image'];
                }

                if (trim((string) $optText) === '' && empty($optImage)) {
                    continue;
                }

                if (trim((string) $optText) === '') {
                    $optText = 'Option '.chr(65 + (int) $optIndex);
                }

                $processedOptions[] = [
                    'text' => $optText,
                    'image' => $optImage,
                ];
            }

            $lessonData['quiz'][] = [
                'question' => $q['question'],
                'type' => $q['type'] ?? 'multiple_choice',
                'media' => $qMedia,
                'options' => $processedOptions,
                'correct' => $q['correct'] ?? 0,
                'is_temp' => $isTemp,
            ];
        }

        // Calculate total slides including both contents AND quiz
        $totalSlides = count($lessonData['contents']) + count($lessonData['quiz']);

        return view('lessons.preview', compact('lessonData', 'totalSlides'));
        } catch (\Throwable $e) {
            \Log::error('Lesson preview failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response(
                '<div style="padding:40px;text-align:center;color:#fff;"><h3 style="margin-bottom:8px;">Preview unavailable</h3><p style="opacity:.85;">Something went wrong while building the preview. Please try again.</p></div>',
                500
            );
        }
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
                'media' => $content->media_url, // Keep as stored path
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
                'media' => $question->media_url, // Keep as stored path
                'options' => $question->options->map(function ($opt) {
                    return [
                        'text' => $opt->option_text,
                        'image' => $opt->option_media_url, // Keep as stored path
                    ];
                })->toArray(),
                'correct' => $question->options->search(fn ($opt) => $opt->is_correct),
            ];
        }
    }

    $totalSlides = count($lessonData['contents']) + count($lessonData['quiz']);

    return response()->view('lessons.preview', compact('lessonData', 'totalSlides'));
}

/**
 * Show the edit form for a lesson
 */
public function edit($id)
{
    $lesson = Lesson::with(['contents', 'quiz.questions.options'])->findOrFail($id);
    
    // Get modules for the dropdown
    $user = Auth::user();
    $teacher = $user ? $user->teacher : null;
    $modules = Module::where('teacher_id', $teacher?->id ?? 0)
        ->orderBy('module_order')
        ->get();

    // Format data for the edit form
    $lessonData = [
        'lesson_id' => $lesson->lesson_id,
        'title' => $lesson->title,
        'description' => $lesson->description,
        'lesson_type' => $lesson->lesson_type,
        'difficulty' => $lesson->difficulty,
        'module_id' => $lesson->module_id, // ADD THIS
        'status' => $lesson->status,
        'contents' => $lesson->contents->map(function ($content) {
            return [
                'content_id' => $content->content_id,
                'content_type' => $content->content_type,
                'title' => $content->title,
                'content_text' => $content->content_text,
                'media_url' => $content->media_url,
                'gesture_name' => $content->gesture_name,
                'media_missing' => $content->media_missing ?? 0,
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
                'options' => $question->options->map(function ($opt) {
                    return [
                        'text' => $opt->option_text,
                        'image' => $opt->option_media_url,
                    ];
                })->toArray(),
                'correct' => $question->options->search(fn ($opt) => $opt->is_correct),
            ];
        }
    }

   return view('lessons.edit', compact('lessonData', 'modules'));
}

/**
 * Update a lesson
 */
public function update(Request $request, $id)
{
    $validated = $request->validate([
        'title'                       => 'required|string|max:255',
        'description'                 => 'nullable|string',
        'lesson_type'                 => 'required|in:text,video,interactive,gesture',
        'difficulty'                  => 'required|in:beginner,intermediate,advanced',
        'module_action'               => 'nullable|in:none,existing,new',
        'module_id'                   => 'nullable|required_if:module_action,existing|exists:modules,module_id',
        'new_module.title'            => 'nullable|required_if:module_action,new|string|max:255',
        'new_module.description'      => 'nullable|string|max:1000',
        'contents'                    => 'nullable|array',
        'contents.*.media'            => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv|max:51200',
        'quiz'                        => 'nullable|array',
        'quiz.*.media'                => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
        'quiz.*.options.*.image'      => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
    ]);

    $status = $request->input('status', 'draft');
    if (! in_array($status, ['draft', 'published', 'archived'])) {
        $status = 'draft';
    }

    $lesson = Lesson::findOrFail($id);
    $teacherId = $this->resolveTeacherId();
    $moduleId = $this->resolveModuleId($request, $teacherId) ?? $lesson->module_id;

    DB::transaction(function () use ($request, $validated, $status, $lesson, $moduleId) {
        // Update lesson
        $lesson->update([
    'title' => $validated['title'],
    'description' => $validated['description'] ?? null,
    'lesson_type' => $validated['lesson_type'],
    'difficulty' => $validated['difficulty'],
    'module_id' => $moduleId,
    'status' => $status,
]);

        // Delete existing contents and recreate
        $lesson->contents()->delete();
        $this->persistLessonContents($request, $lesson, $request->input('contents', []));

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
        $this->persistQuizForLesson($request, $lesson, $quizInput);
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

    $teacherId = $this->resolveTeacherId();
    $modules = Module::where('teacher_id', $teacherId)
        ->orderBy('module_order')
        ->get();

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

    return view('lessons.publish-config', compact('lesson', 'students', 'programs', 'masteryLevels', 'modules'));
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
        'module_action' => 'required|in:existing,new',
        'module_id' => 'nullable|required_if:module_action,existing|exists:modules,module_id',
        'new_module.title' => 'nullable|required_if:module_action,new|string|max:255',
        'new_module.description' => 'nullable|string|max:1000',
        'publish_option' => 'required|in:all,program,mastery,selected',
        'program' => 'required_if:publish_option,program|nullable|string',
        'mastery_level' => 'required_if:publish_option,mastery|nullable|string',
        'students' => 'required_if:publish_option,selected|nullable|array|min:1',
        'students.*' => 'exists:students,student_id',
        'notify_students' => 'boolean',
        'send_reminder' => 'boolean',
    ]);

    $lesson = Lesson::findOrFail($id);

    $teacherId = $this->resolveTeacherId();
    $moduleId = $this->resolveModuleId($request, $teacherId);
    if (! $moduleId) {
        return back()->withErrors(['module_action' => 'Please select or create a module before publishing.'])->withInput();
    }

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

    if (empty($studentIds)) {
        return back()->withErrors(['publish_option' => 'No students matched the selected publish option.'])->withInput();
    }

    // Assign module and publish the lesson
    $lesson->update([
        'module_id' => $moduleId,
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
        // Determine if this lesson should be locked
        $isLocked = true;
        
        // If it's the first lesson in the module, unlock it
        $firstLesson = Lesson::where('module_id', $lesson->module_id)
            ->where('status', 'published')
            ->orderBy('module_order', 'asc')
            ->first();
        
        if ($firstLesson && $firstLesson->lesson_id === $lesson->lesson_id) {
            $isLocked = false;
        }
        
        LessonAssignment::create([
            'lesson_id' => $lesson->lesson_id,
            'student_id' => $studentId,
            'assigned_at' => now(),
            'status' => 'pending',
            'is_locked' => $isLocked,
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

    /**
     * Delete a lesson and all its related data.
     */
    public function destroy($id)
    {
        $lesson = Lesson::findOrFail($id);

        DB::transaction(function () use ($lesson) {
            // Remove quiz options → questions → quiz
            if ($lesson->quiz) {
                $lesson->quiz->questions()->each(function ($q) {
                    $q->options()->delete();
                });
                $lesson->quiz->questions()->delete();
                $lesson->quiz()->delete();
            }

            // Remove contents, assignments, then the lesson itself
            $lesson->contents()->delete();
            $lesson->assignments()->delete();
            $lesson->delete();
        });

        return redirect()->route('lessons.index')
            ->with('success', 'Lesson deleted successfully.');
    }

    /**
     * Return JSON list of all active students + which ones are assigned to this lesson.
     * Used by the inline "Edit Students" modal (GET).
     */
    public function manageStudents($id)
    {
        $lesson = Lesson::findOrFail($id);

        $assignedIds = LessonAssignment::where('lesson_id', $lesson->lesson_id)
            ->pluck('student_id')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        $students = DB::table('students')
            ->select('student_id', 'first_name', 'last_name', 'lrn',
                'fsl_mastery_level as mastery_level',
                'program_type as program',
                'grade_level', 'section')
            ->where('status', 'active')
            ->orderBy('last_name')
            ->get()
            ->map(function ($s) use ($assignedIds) {
                $s->assigned = in_array((int) $s->student_id, $assignedIds, true);
                return $s;
            });

        return response()->json([
            'lesson_id'   => $lesson->lesson_id,
            'lesson_title'=> $lesson->title,
            'students'    => $students,
        ]);
    }

    /**
     * Replace the student assignment list for a lesson (POST).
     * Expects JSON body: { "student_ids": [1, 2, 3] }
     */
    public function updateStudents(Request $request, $id)
    {
        $lesson = Lesson::findOrFail($id);

        $validated = $request->validate([
            'student_ids'   => 'nullable|array',
            'student_ids.*' => 'integer|exists:students,student_id',
        ]);

        $newIds = $validated['student_ids'] ?? [];

        DB::transaction(function () use ($lesson, $newIds) {
            $existingIds = LessonAssignment::where('lesson_id', $lesson->lesson_id)
                ->pluck('student_id')
                ->map(fn ($v) => (int) $v)
                ->toArray();

            // Add new
            foreach (array_diff($newIds, $existingIds) as $studentId) {
                LessonAssignment::create([
                    'lesson_id'   => $lesson->lesson_id,
                    'student_id'  => $studentId,
                    'assigned_at' => now(),
                    'status'      => 'pending',
                    'notified'    => false,
                ]);
            }

            // Remove revoked
            $toRemove = array_diff($existingIds, $newIds);
            if (!empty($toRemove)) {
                LessonAssignment::where('lesson_id', $lesson->lesson_id)
                    ->whereIn('student_id', $toRemove)
                    ->delete();
            }
        });

        $count = count($newIds);

        return response()->json([
            'success' => true,
            'message' => "Student access updated — {$count} student(s) assigned.",
            'count'   => $count,
        ]);
    }

    private function resolveTeacherId(): int
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->teacher) {
                return (int) $user->teacher->id;
            }

            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                return (int) $teacher->id;
            }
        }

        $firstTeacher = Teacher::first();

        return $firstTeacher ? (int) $firstTeacher->id : 1;
    }

    private function resolveModuleId(Request $request, int $teacherId): ?int
    {
        $action = $request->input('module_action', 'none');

        if ($action === 'new') {
            $request->validate([
                'new_module.title' => 'required|string|max:255',
                'new_module.description' => 'nullable|string|max:1000',
            ]);

            $nextOrder = (int) Module::where('teacher_id', $teacherId)->max('module_order') + 1;

            return Module::create([
                'teacher_id' => $teacherId,
                'title' => $request->input('new_module.title'),
                'description' => $request->input('new_module.description'),
                'module_order' => $nextOrder,
                'status' => 'draft',
            ])->module_id;
        }

        if ($action === 'existing' && $request->filled('module_id')) {
            $moduleId = (int) $request->input('module_id');
            Module::where('module_id', $moduleId)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();

            return $moduleId;
        }

        return null;
    }

    private function getContentUploadedFile(Request $request, int|string $index): ?UploadedFile
    {
        $file = $request->file("contents.{$index}.media");
        if ($file instanceof UploadedFile && $file->isValid()) {
            return $file;
        }

        $contentFiles = $request->file('contents');
        if (! is_array($contentFiles) || ! isset($contentFiles[$index]['media'])) {
            return null;
        }

        $file = $contentFiles[$index]['media'];

        return ($file instanceof UploadedFile && $file->isValid()) ? $file : null;
    }

    private function persistLessonContents(Request $request, Lesson $lesson, array $contents): void
    {
        $step = 1;
        foreach ($contents as $i => $c) {
            if (empty($c['title']) && empty($c['content_text']) && empty($c['gesture_name'])) {
                continue;
            }

            $uploadedFile = $this->getContentUploadedFile($request, $i);
            $mediaUrl = $this->uploadPublicFile($uploadedFile, 'lesson_media');
            if (! $mediaUrl && ! empty($c['existing_media'])) {
                $mediaUrl = $c['existing_media'];
            }

            // Auto-clear media_missing when a file has been successfully uploaded
            $mediaMissing = isset($c['media_missing']) ? (int) $c['media_missing'] : 0;
            if ($uploadedFile && $mediaUrl) {
                $mediaMissing = 0;
            }

            LessonContent::create([
                'lesson_id' => $lesson->lesson_id,
                'step_number' => $step++,
                'content_type' => $c['content_type'] ?? 'text',
                'title' => $c['title'] ?? null,
                'content_text' => $c['content_text'] ?? null,
                'media_url' => $mediaUrl,
                'gesture_name' => $c['gesture_name'] ?? null,
                'media_missing' => $mediaMissing,
            ]);
        }
    }

    private function uploadPublicFile(?UploadedFile $file, string $directory): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $filename = time().'_'.uniqid().'_'.$safeName;

        return $file->storeAs($directory, $filename, 'public');
    }

    private function getQuizUploadedFile(Request $request, int|string $questionIndex, string $relativePath): ?UploadedFile
    {
        $file = $request->file("quiz.{$questionIndex}.{$relativePath}");
        if ($file instanceof UploadedFile && $file->isValid()) {
            return $file;
        }

        $quizFiles = $request->file('quiz');
        if (! is_array($quizFiles) || ! isset($quizFiles[$questionIndex])) {
            return null;
        }

        $node = $quizFiles[$questionIndex];
        foreach (explode('.', $relativePath) as $segment) {
            if (! is_array($node) || ! array_key_exists($segment, $node)) {
                return null;
            }
            $node = $node[$segment];
        }

        return ($node instanceof UploadedFile && $node->isValid()) ? $node : null;
    }

    private function collectOptionIndices(array $optionInput, array $optionFiles): array
    {
        $indices = array_unique(array_merge(array_keys($optionInput), array_keys($optionFiles)));
        usort($indices, fn ($a, $b) => (int) $a <=> (int) $b);

        return $indices;
    }

    private function persistQuizForLesson(Request $request, Lesson $lesson, array $quizInput): void
    {
        $validQuestions = array_filter($quizInput, fn ($q) => ! empty($q['question']));
        if (empty($validQuestions)) {
            return;
        }

        $totalPoints = count($validQuestions);
        $passingScore = max(1, round($totalPoints * 0.6));

        $quiz = Quiz::create([
            'lesson_id' => $lesson->lesson_id,
            'title' => 'Quiz: '.$lesson->title,
            'description' => 'Auto-generated quiz for '.$lesson->title,
            'total_points' => $totalPoints,
            'passing_score' => $passingScore,
        ]);

        $quizFiles = $request->file('quiz') ?? [];
        $qNum = 1;

        foreach ($quizInput as $qi => $q) {
            if (empty($q['question'])) {
                continue;
            }

            $questionFile = $this->getQuizUploadedFile($request, $qi, 'media');
            $qMedia = $this->uploadPublicFile($questionFile, 'quiz_media');
            if (! $qMedia && ! empty($q['existing_media'])) {
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
                foreach (['True', 'False'] as $idx => $text) {
                    QuizOption::create([
                        'question_id' => $question->question_id,
                        'option_text' => $text,
                        'option_media_url' => null,
                        'is_correct' => $idx === $correctIndex,
                    ]);
                }

                continue;
            }

            $optionInput = is_array($q['options'] ?? null) ? $q['options'] : [];
            $optionFiles = [];
            if (isset($quizFiles[$qi]['options']) && is_array($quizFiles[$qi]['options'])) {
                $optionFiles = $quizFiles[$qi]['options'];
            }

            foreach ($this->collectOptionIndices($optionInput, $optionFiles) as $idx) {
                $optData = $optionInput[$idx] ?? [];
                $optText = is_array($optData) ? ($optData['text'] ?? '') : (string) $optData;

                $optionFile = null;
                if (isset($optionFiles[$idx]['image']) && $optionFiles[$idx]['image'] instanceof UploadedFile) {
                    $optionFile = $optionFiles[$idx]['image'];
                } else {
                    $optionFile = $this->getQuizUploadedFile($request, $qi, "options.{$idx}.image");
                }

                $optImagePath = $this->uploadPublicFile($optionFile, 'quiz_option_media');
                if (! $optImagePath && is_array($optData) && ! empty($optData['existing_image'])) {
                    $optImagePath = $optData['existing_image'];
                }

                if (trim((string) $optText) === '' && empty($optImagePath)) {
                    continue;
                }

                if (trim((string) $optText) === '') {
                    $optText = 'Option '.chr(65 + (int) $idx);
                }

                QuizOption::create([
                    'question_id' => $question->question_id,
                    'option_text' => $optText,
                    'option_media_url' => $optImagePath,
                    'is_correct' => (int) $idx === $correctIndex,
                ]);
            }
        }
    }
}