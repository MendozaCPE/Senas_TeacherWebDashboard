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
use App\Models\Gesture;
use App\Models\GestureModule;
use App\Models\CheckpointExam;
use App\Models\CheckpointExamQuestion;
use App\Models\CheckpointExamAssignment;
use App\Services\AiService;
use App\Services\DeepSeekService;
use App\Services\GestureMediaResolver;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        // ✅ Get ALL lessons including archived ones (withTrashed)
        $lessons = Lesson::withTrashed()
            ->where('teacher_id', $teacher->id)
            ->orderBy('module_order')
            ->get();
        
        // ✅ Get modules with lessons (including archived)
        $modules = Module::where('teacher_id', $teacher->id)
            ->with(['lessons' => function($query) {
                $query->withTrashed() // Include archived lessons
                    ->orderBy('module_order');
            }])
            ->with(['checkpointExams' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->orderBy('module_order')
            ->get();
        
        // ✅ Get orphaned lessons (including archived)
        $orphanedLessons = Lesson::withTrashed()
            ->where('teacher_id', $teacher->id)
            ->whereNull('module_id')
            ->orderBy('module_order')
            ->get();


            // Get all checkpoint exam questions for used lesson IDs check
            $usedLessonIds = CheckpointExamQuestion::whereHas('exam', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })->distinct('source_lesson_id')->pluck('source_lesson_id')->toArray();

            // Add computed properties to each module
            foreach ($modules as $module) {
                // Get available lessons for checkpoint exam creation (lessons not yet in a checkpoint exam for this module)
                $moduleUsedLessonIds = CheckpointExamQuestion::whereHas('exam', function($q) use ($module) {
                    $q->where('module_id', $module->module_id);
                })->distinct('source_lesson_id')->pluck('source_lesson_id')->toArray();

                $availableLessons = $module->lessons->filter(function($lesson) use ($moduleUsedLessonIds) {
                    return !in_array($lesson->lesson_id, $moduleUsedLessonIds) 
                        && $lesson->status === 'published' 
                        && $lesson->quiz 
                        && $lesson->quiz->questions->count() > 0;
                });
                
                $module->availableLessonsCount = $availableLessons->count();
                $module->canCreateExam = $module->availableLessonsCount >= 2;
                $module->checkpointExamsCount = $module->checkpointExams->count();
            }

        } else {
            $lessons = collect();
            $modules = collect();
            $orphanedLessons = collect();
            $usedLessonIds = [];
        }

        return view('lessons', compact('lessons', 'modules', 'orphanedLessons', 'usedLessonIds'));
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
    
    // Add this line to load gesture modules
    $gestureModules = GestureModule::where('is_active', true)
        ->orderBy('order')
        ->get();

    return view('lessons.create', compact('modules', 'gestureModules'));
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
            'num_dd'       => 'nullable|integer|min:0|max:15',
            'num_gt'       => 'nullable|integer|min:0|max:15',
            'instructions' => 'nullable|string|max:1000',
        ]);

        $totalQuestions = (int)$request->input('num_mc') + (int)$request->input('num_tf') + (int)$request->input('num_dd', 0) + (int)$request->input('num_gt', 0);
        if ($totalQuestions < 1) {
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

            $aiService = AiService::make();
            $lesson   = $aiService->generateFromPdfText($pdfText, [
                'difficulty'      => $request->input('difficulty'),
                'lesson_type'     => $request->input('lesson_type'),
                'num_slides'      => (int) $request->input('num_slides'),
                'num_mc'          => (int) $request->input('num_mc', 3),
                'num_tf'          => (int) $request->input('num_tf', 2),
                'num_dd'          => (int) $request->input('num_dd', 0),
                'num_gt'          => (int) $request->input('num_gt', 0),
                'instructions'    => $request->input('instructions', ''),
                'gesture_catalog' => $this->buildGestureCatalog(),
            ]);

            if (isset($lesson['quiz'])) {
                $lesson['quiz'] = $this->resolveGestureQuestions($lesson['quiz']);
            }

            $resolver = new GestureMediaResolver();
            $lesson   = $resolver->resolve($lesson, $this->resolveTeacherId());

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
            'lesson_type'          => 'required|in:gesture,text,interactive,video',
            'num_slides'           => 'required|integer|min:3|max:30',
            'num_mc'               => 'required|integer|min:0|max:15',
            'num_tf'               => 'required|integer|min:0|max:15',
            'num_dd'               => 'required|integer|min:0|max:15',
            'num_gt'               => 'required|integer|min:0|max:15',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        if ((int)$validated['num_mc'] + (int)$validated['num_tf'] + (int)$validated['num_dd'] + (int)$validated['num_gt'] < 1) {
            return response()->json(['message' => 'Please generate at least 1 quiz question.'], 422);
        }

        try {
            $validated['gesture_catalog'] = $this->buildGestureCatalog();

            $aiService = AiService::make();
            $lesson   = $aiService->generate($validated);

            if (isset($lesson['quiz'])) {
                $lesson['quiz'] = $this->resolveGestureQuestions($lesson['quiz']);
            }

            $resolver = new GestureMediaResolver();
            $lesson   = $resolver->resolve($lesson, $this->resolveTeacherId());

            if (!empty($lesson['contents']) && is_array($lesson['contents'])) {
                foreach ($lesson['contents'] as &$slide) {
                    if (($slide['content_type'] ?? '') === 'youtube_video') {
                        $slide['media_missing'] = false;
                    }
                }
                unset($slide);
            }

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
     * Build a compact gesture catalog from the database to inject into AI prompts.
     * Groups gesture names by their module so the AI can pick real, matching names.
     *
     * @return array  e.g. [['module' => 'Alphabet', 'gestures' => ['letter_a','letter_b',...]],...]
     */
    private function buildGestureCatalog(): array
    {
        $modules = GestureModule::where('is_active', true)
            ->orderBy('order')
            ->with(['gestures' => function ($q) {
                $q->select('gesture_id', 'module_id', 'name', 'display_name');
            }])
            ->get(['module_id', 'name', 'display_name']);

        $catalog = [];
        foreach ($modules as $module) {
            $names = $module->gestures->pluck('name')->filter()->values()->toArray();
            if (!empty($names)) {
                $catalog[] = [
                    'module'   => $module->display_name ?? $module->name,
                    'gestures' => $names,
                ];
            }
        }

        return $catalog;
    }

    /**
     * AI Quiz Generator from manual lesson content — POST /lessons/ai-generate-quiz
     * Takes lesson content text typed by teacher, returns quiz questions only.
     */
    public function aiGenerateQuiz(Request $request)
    {
        $validated = $request->validate([
            'content_text' => 'required|string|min:20|max:10000',
            'num_mc'       => 'required|integer|min:0|max:15',
            'num_tf'       => 'required|integer|min:0|max:15',
            'num_dd'       => 'required|integer|min:0|max:15',
            'num_gt'       => 'required|integer|min:0|max:15',
        ]);

        if ((int)$validated['num_mc'] + (int)$validated['num_tf'] + (int)$validated['num_dd'] + (int)$validated['num_gt'] < 1) {
            return response()->json(['message' => 'Please request at least 1 quiz question.'], 422);
        }

        try {
            $aiService = AiService::make();
            $questions = $aiService->generateQuizOnly(
                $validated['content_text'],
                (int) $validated['num_mc'],
                (int) $validated['num_tf'],
                (int) $validated['num_dd'],
                (int) $validated['num_gt']
            );

            $questions = $this->resolveGestureQuestions($questions);

            return response()->json(['quiz' => $questions]);
        } catch (\Throwable $e) {
            \Log::error('AI Quiz Generate failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'AI quiz generation failed: ' . $e->getMessage()], 500);
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
     // DEBUG: Log all quiz data
    \Log::info('Full request data:', $request->all());
    \Log::info('Quiz data:', $request->input('quiz', []));
    
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'lesson_type' => 'required|in:text,video,interactive,gesture',
        'difficulty' => 'required|in:beginner,intermediate,advanced',
        'module_action' => 'nullable|in:none,existing,new',
        'module_id' => 'nullable|required_if:module_action,existing|exists:modules,module_id',
        'new_module.title' => 'nullable|required_if:module_action,new|string|max:255',
        'new_module.description' => 'nullable|string|max:1000',
        'contents' => 'nullable|array',
        'contents.*.media' => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv,webm|max:51200', // ✅ Already correct
        'quiz' => 'nullable|array',
        'quiz.*.media' => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv,webm|max:51200', // ✅ Already correct
        'quiz.*.options.*.image' => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv,webm|max:51200', // ✅ Already correct
        'contents.*.existing_media'   => 'nullable|string',
        'quiz.*.existing_media'       => 'nullable|string',
        'quiz.*.options.*.existing_image' => 'nullable|string',
        'contents.*.youtube_url'      => 'nullable|string|max:500',
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
            'is_template'  => $this->isAdminTemplateContext(),
        ]);

        $this->persistLessonContents($request, $lesson, $request->input('contents', []));

        $quizInput = $request->input('quiz', []);
        $this->persistQuizForLesson($request, $lesson, $quizInput);

        return $lesson;
    });

    // After saving, redirect based on which button was clicked
    if ($buttonAction === 'published') {
        return redirect()->route('lessons.publish.config', $lesson->hash_id)
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
    try {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv,webm|max:51200',
            'context' => 'nullable|string|in:lesson_media,quiz_media,quiz_option_media,temp_preview',
        ]);

        $file = $request->file('file');
        $directory = $request->input('context', 'lesson_media');

        $path = $this->uploadPublicFile($file, $directory);

        if (!$path) {
            return response()->json(['message' => 'Upload failed.'], 500);
        }

        return response()->json([
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    } catch (\Exception $e) {
        \Log::error('Upload error: ' . $e->getMessage());
        return response()->json(['message' => 'Upload failed: ' . $e->getMessage()], 500);
    }
}

    /**
     * List the sign-language media library "folders" (Alphabets, Numbers, Greetings, Survival).
     * These map 1:1 to the sub-directories under public/storage/sign_language_media.
     * Note: alphabet gesture modules (module_id 1 & 2) are split in the `modules` table,
     * but on disk they share a single "Alphabets" folder — this endpoint reads straight
     * from disk so that split is irrelevant here.
     * GET /lessons/media-library
     */
    public function mediaLibraryFolders()
    {
        $base = 'sign_language_media';
        $disk = Storage::disk('public');
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'avi', 'mkv'];

        // Preferred order + friendly labels. Any other folder found on disk is appended after these.
        $labels = [
            'Alphabets' => 'Alphabets',
            'Numbers'   => 'Numbers',
            'Greetings' => 'Greetings',
            'Survival'  => 'Survival / Conversation',
        ];

        $folders = [];
        if ($disk->exists($base)) {
            foreach ($disk->directories($base) as $dir) {
                $key = basename($dir);
                $count = count(array_filter($disk->files($dir), function ($f) use ($allowedExt) {
                    return in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $allowedExt);
                }));

                $folders[$key] = [
                    'key'   => $key,
                    'label' => $labels[$key] ?? $key,
                    'count' => $count,
                ];
            }
        }

        // Sort: known folders first (in the order above), then any extras alphabetically.
        $ordered = [];
        foreach (array_keys($labels) as $key) {
            if (isset($folders[$key])) {
                $ordered[] = $folders[$key];
                unset($folders[$key]);
            }
        }
        foreach ($folders as $remaining) {
            $ordered[] = $remaining;
        }

        return response()->json(['folders' => $ordered]);
    }

    /**
     * List the files inside one sign-language media library folder.
     * GET /lessons/media-library/{folder}
     */
    public function mediaLibraryFiles(string $folder)
    {
        // basename() strips any "../" traversal attempts down to a bare folder name.
        $folder = basename($folder);
        $base = "sign_language_media/{$folder}";
        $disk = Storage::disk('public');

        if (! $disk->exists($base)) {
            return response()->json(['files' => []]);
        }

        $videoExt = ['mp4', 'mov', 'avi', 'mkv'];
        $imageExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $files = [];
        foreach ($disk->files($base) as $path) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (! in_array($ext, array_merge($videoExt, $imageExt))) {
                continue;
            }

            $files[] = [
                'file_name' => basename($path),
                'path'      => $path,
                'url'       => asset('storage/' . $path),
                'type'      => in_array($ext, $videoExt) ? 'video' : 'image',
                'size'      => $disk->size($path),
            ];
        }

        usort($files, fn ($a, $b) => strnatcasecmp($a['file_name'], $b['file_name']));

        return response()->json(['files' => $files]);
    }

    /**
     * List the current teacher's uploaded media files.
     * GET /lessons/my-uploads
     */
    public function mediaLibraryMyUploads()
    {
        $teacher = $this->resolveTeacherId();

        $items = \App\Models\TeacherMedia::where('teacher_id', $teacher)
            ->orderBy('created_at', 'desc')
            ->get();

        $videoExt = ['mp4', 'mov', 'avi', 'mkv'];

        $files = $items->map(function ($item) use ($videoExt) {
            $ext = strtolower(pathinfo($item->file_name, PATHINFO_EXTENSION));
            return [
                'file_name' => $item->file_name,
                'title'     => $item->title,
                'path'      => $item->file_path,
                'url'       => asset('storage/' . $item->file_path),
                'type'      => in_array($ext, $videoExt) ? 'video' : 'image',
                'size'      => $item->file_size,
            ];
        })->values()->toArray();

        return response()->json(['files' => $files]);
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
                // Allow a youtube_video slide that only has a URL and no other text
                if (($c['content_type'] ?? '') !== 'youtube_video' || empty($c['youtube_url'])) {
                    continue;
                }
            }

            $mediaUrl = null;
            $isTemp = false;

            $contentFile = $this->getContentUploadedFile($request, $index);
            if ($contentFile) {
                $mediaUrl = $this->uploadPublicFile($contentFile, 'temp_preview');
                $isTemp = true;
            } elseif (! empty($c['existing_media'])) {
                $mediaUrl = $this->normalizeStoredMediaPath($c['existing_media']);
            } elseif (($c['content_type'] ?? '') === 'youtube_video' && !empty($c['youtube_url'])) {
                // For youtube_video, normalise the URL into a canonical watch URL
                $ytId = \App\Models\LessonContent::extractYoutubeId($c['youtube_url']);
                if ($ytId) {
                    $mediaUrl = 'https://www.youtube.com/watch?v=' . $ytId;
                }
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
                $qMedia = $this->normalizeStoredMediaPath($q['existing_media']);
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

            $quizType = $q['type'] ?? 'multiple_choice';

            $processedPairs = [];
            if ($quizType === 'drag_drop') {
                $processedPairs = $this->normalizeDragDropPairs($q['drag_drop_pairs'] ?? []);
            }

            $gestureDetails = [];
            $gestureModuleId = null;
            if ($quizType === 'gesture') {
                $gestureModuleId = $q['gesture_module_id'] ?? null;
                $gestureIds = array_filter((array) ($q['gesture_ids'] ?? []));
                $gestureDetails = $this->buildGestureDetails($gestureIds);
            }

            $lessonData['quiz'][] = [
                'question' => $q['question'],
                'type' => $quizType,
                'media' => $qMedia,
                'options' => $processedOptions,
                'correct' => $q['correct'] ?? 0,
                'is_temp' => $isTemp,
                'drag_drop_pairs' => $processedPairs,
                'gesture_module_id' => $gestureModuleId,
                'gesture_details' => $gestureDetails,
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
    $realId = \App\Support\UrlObfuscator::decode($id) ?? $id;
    $lesson = Lesson::with(['contents', 'quiz.questions.options'])->findOrFail($realId);

    if ($id !== $lesson->hash_id) {
        return redirect()->route('lessons.view', $lesson->hash_id);
    }

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

   if ($lesson->quiz) {
        foreach ($lesson->quiz->questions as $question) {
            // Handle gesture_data (decode if double-encoded)
            $gestureData = $question->gesture_data;
            if (is_string($gestureData)) {
                $gestureData = json_decode($gestureData, true) ?? [];
            }
            $gestureIds = $gestureData['gesture_ids'] ?? [];
            $isFingerspelling = $gestureData['is_fingerspelling'] ?? false;
            $words = $gestureData['words'] ?? [];

            $lessonData['quiz'][] = [
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
                'drag_drop_pairs' => $this->normalizeDragDropPairs($question->drag_drop_pairs ?? []),
                'gesture_module_id' => $gestureData['module_id'] ?? null,
                'gesture_details' => $this->buildGestureDetails($gestureIds),
                // 🆕 Add these lines
                'is_fingerspelling' => $isFingerspelling,
                'fingerspelling_words' => $words,
            ];
        }
    }

    $totalSlides = count($lessonData['contents']) + count($lessonData['quiz']);

    return response()->view('lessons.preview', compact('lessonData', 'totalSlides'));
}

/**
 * Return the preview partial for a lesson (used by the modal overlay on the lessons index).
 */
public function previewModal($id)
{
    $realId = \App\Support\UrlObfuscator::decode($id) ?? $id;
    $lesson = Lesson::with(['contents', 'quiz.questions.options'])->findOrFail($realId);

    if ($id !== $lesson->hash_id) {
        return redirect()->route('lessons.preview-modal', $lesson->hash_id);
    }

    $lessonData = [
        'title'       => $lesson->title,
        'description' => $lesson->description,
        'lesson_type' => $lesson->lesson_type,
        'difficulty'  => $lesson->difficulty,
        'contents'    => $lesson->contents->map(function ($content) {
            return [
                'content_type' => $content->content_type,
                'title'        => $content->title,
                'content_text' => $content->content_text,
                'media'        => $content->media_url,
                'gesture_name' => $content->gesture_name,
            ];
        })->toArray(),
        'quiz' => [],
    ];

    if ($lesson->quiz) {
        foreach ($lesson->quiz->questions as $question) {
            $gestureData = $question->gesture_data;
            if (is_string($gestureData)) {
                $gestureData = json_decode($gestureData, true) ?? [];
            }
            $gestureIds = $gestureData['gesture_ids'] ?? [];
            $isFingerspelling = $gestureData['is_fingerspelling'] ?? false;
            $words = $gestureData['words'] ?? [];

            $lessonData['quiz'][] = [
                'question'         => $question->question_text,
                'type'             => $question->question_type,
                'media'            => $question->media_url,
                'options'          => $question->options->map(function ($opt) {
                    return [
                        'text'  => $opt->option_text,
                        'image' => $opt->option_media_url,
                    ];
                })->toArray(),
                'correct'          => $question->options->search(fn ($opt) => $opt->is_correct),
                'drag_drop_pairs'  => $this->normalizeDragDropPairs($question->drag_drop_pairs ?? []),
                'gesture_module_id' => $gestureData['module_id'] ?? null,
                'gesture_details'  => $this->buildGestureDetails($gestureIds),
                // 🆕 Add these lines
                'is_fingerspelling' => $isFingerspelling,
                'fingerspelling_words' => $words,
            ];
        }
    }

    $totalSlides = count($lessonData['contents']) + count($lessonData['quiz']);

    // Return as a bare partial — no layout wrapper
    return response()->view('lessons.preview', compact('lessonData', 'totalSlides'));
}

/**
 * Show the edit form for a lesson
 */
public function edit($id)
{
    $realId = \App\Support\UrlObfuscator::decode($id) ?? $id;
    $lesson = Lesson::with(['contents', 'quiz.questions.options'])->findOrFail($realId);

    if ($id !== $lesson->hash_id) {
        if ($this->isAdminTemplateContext()) {
            return redirect()->route('admin.lesson-templates.edit', $lesson->hash_id);
        }
        return redirect()->route('lessons.edit', $lesson->hash_id);
    }

    
    // Get modules for the dropdown
    $teacherId = $this->resolveTeacherId();
    $modules = Module::where('teacher_id', $teacherId)
        ->orderBy('module_order')
        ->get();

    $gestureModules = GestureModule::where('is_active', true)
        ->orderBy('order')
        ->get();

    // Format data for the edit form
    $lessonData = [
        'lesson_id' => $lesson->lesson_id,
        'hash_id' => $lesson->hash_id,
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
                'drag_drop_pairs' => $question->drag_drop_pairs ?? [],
                'gesture_module_id' => $question->gesture_data['module_id'] ?? null,
                'gesture_ids' => $question->gesture_data['gesture_ids'] ?? [],
            ];
        }
    }

   return view('lessons.edit', compact('lessonData', 'modules', 'gestureModules'));
}

/**
 * Update a lesson
 */
public function update(Request $request, $id)
{
    $id = \App\Support\UrlObfuscator::decode($id) ?? $id;
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
        'contents.*.media'            => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv,webm|max:51200',
        'quiz'                        => 'nullable|array',
        'quiz.*.media'                => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv,webm|max:51200',
        'quiz.*.options.*.image'      => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv,webm|max:51200',
        'contents.*.existing_media'   => 'nullable|string',
        'quiz.*.existing_media'       => 'nullable|string',
        'quiz.*.options.*.existing_image' => 'nullable|string',
        'contents.*.youtube_url'      => 'nullable|string|max:500',
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
public function showPublishConfig($id)
{
    $realId = \App\Support\UrlObfuscator::decode($id) ?? $id;
    $lesson = Lesson::findOrFail($realId);

    if ($id !== $lesson->hash_id) {
        return redirect()->route('lessons.publish.config', $lesson->hash_id);
    }

    $teacherId = $this->resolveTeacherId();
    $modules = Module::where('teacher_id', $teacherId)
        ->orderBy('module_order')
        ->get();

    // ✅ FIX: Only show students belonging to this teacher
    $students = DB::table('students')
        ->select('student_id', 'first_name', 'last_name',
            'lrn',
            'fsl_mastery_level as mastery_level',
            'program_type as program',
            'grade_level', 'section', 'status')
        ->where('status', 'active')
        ->where('teacher_id', $teacherId)  // ✅ ADD THIS LINE
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
    $id = \App\Support\UrlObfuscator::decode($id) ?? $id;
    $validated = $request->validate([
        'module_action' => 'required|in:existing,new',
        'module_id' => 'nullable|required_if:module_action,existing|exists:modules,module_id',
        'new_module.title' => 'nullable|required_if:module_action,new|string|max:255',
        'new_module.description' => 'nullable|string|max:1000',
        'new_module.mastery_level' => 'nullable|in:beginner,intermediate,advanced', // ← ADD THIS
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
            
            // ✅ ADD: Create notification for this student
            $this->createLessonNotification($studentId, $lesson);
        }
    }

    $message = "Lesson published successfully to {$assignedCount} students!";

    if ($request->input('notify_students')) {
        $message .= ' Students will be notified.';
    }

    return redirect()->route('lessons.index')->with('success', $message);
}


/**
 * ✅ NEW: Create notification for a new lesson
 */
protected function createLessonNotification($studentId, $lesson)
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

    // Check if notification already exists for this lesson and student
    $exists = \App\Models\StudentNotification::where('student_id', $studentId)
        ->where('type', 'lesson')
        ->where('data->lesson_id', $lesson->lesson_id)
        ->exists();

    if ($exists) {
        return; // Skip duplicate
    }

    \App\Models\StudentNotification::create([
        'student_id' => $studentId,
        'type' => 'lesson',
        'title' => '📚 New Lesson Available!',
        'message' => "\"{$lesson->title}\" is ready for you to start! 🎓",
        'icon' => $iconMap['lesson'],
        'color' => $colorMap['lesson'],
        'data' => ['lesson_id' => $lesson->lesson_id, 'lesson_title' => $lesson->title],
        'action_url' => '/lessons',
        'is_read' => false,
    ]);
}

 /**
 * Soft delete a lesson (archive - keeps student data for analytics)
 * POST /lessons/{id}/soft-delete
 */
public function softDelete($id)
{
    $id = \App\Support\UrlObfuscator::decode($id) ?? $id;
    $lesson = Lesson::findOrFail($id);

    // Soft delete - just marks deleted_at
    $lesson->delete();

    return redirect()->route('lessons.index')
        ->with('success', "Lesson '{$lesson->title}' has been archived. Student data is preserved for analytics.");
}

/**
 * Permanently delete a lesson AND all related student data
 * POST /lessons/{id}/hard-delete
 */
/**
 * Permanently delete a lesson AND all related student data
 * POST /lessons/{id}/hard-delete
 */
public function hardDelete($id)
{
    $id = \App\Support\UrlObfuscator::decode($id) ?? $id;
    $lesson = Lesson::with(['quiz', 'contents', 'assignments'])->findOrFail($id);
    $lessonTitle = $lesson->title;

    \Log::info("🔥 HARD DELETING lesson #{$lesson->lesson_id} - {$lessonTitle}");

    try {
        DB::transaction(function () use ($lesson) {
            $lessonId = $lesson->lesson_id;

            // 1. Delete quiz attempts & student answers
            if ($lesson->quiz) {
                $quizId = $lesson->quiz->quiz_id;

                DB::table('student_answers')
                    ->whereIn('attempt_id', function($query) use ($quizId) {
                        $query->select('attempt_id')
                            ->from('quiz_attempts')
                            ->where('quiz_id', $quizId);
                    })
                    ->delete();

                DB::table('quiz_attempts')
                    ->where('quiz_id', $quizId)
                    ->delete();

                $lesson->quiz->questions()->each(function ($question) {
                    $question->options()->delete();
                });
                $lesson->quiz->questions()->delete();
                $lesson->quiz()->delete();
            }

            // 2. Delete student lesson progress
            DB::table('student_lesson_progress')
                ->where('lesson_id', $lessonId)
                ->delete();

            // 3. Delete lesson assignments
            DB::table('lesson_assignments')
                ->where('lesson_id', $lessonId)
                ->delete();

            // 4. Delete checkpoint exam data
            DB::table('checkpoint_exam_attempts')
                ->whereIn('exam_id', function($query) use ($lessonId) {
                    $query->select('exam_id')
                        ->from('checkpoint_exam_questions')
                        ->where('source_lesson_id', $lessonId);
                })
                ->delete();

            DB::table('checkpoint_exam_questions')
                ->where('source_lesson_id', $lessonId)
                ->delete();

            DB::table('checkpoint_exam_assignments')
                ->whereIn('exam_id', function($query) use ($lessonId) {
                    $query->select('exam_id')
                        ->from('checkpoint_exam_questions')
                        ->where('source_lesson_id', $lessonId);
                })
                ->delete();

            // 5. Delete lesson contents
            $lesson->contents()->delete();

            // 6. Force delete (not soft delete)
            $lesson->forceDelete();
        });

        // Check if it's an AJAX request
        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Lesson '{$lessonTitle}' has been PERMANENTLY deleted. All student data has been removed.",
                'redirect' => route('lessons.index')
            ]);
        }

        return redirect()->route('lessons.index')
            ->with('success', "Lesson '{$lessonTitle}' has been PERMANENTLY deleted. All student data has been removed.");

    } catch (\Exception $e) {
        \Log::error('Hard delete failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        
        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete lesson: ' . $e->getMessage()
            ], 500);
        }
        
        return back()->with('error', 'Failed to delete lesson: ' . $e->getMessage());
    }
}

/**
 * Restore a soft-deleted lesson
 * POST /lessons/{id}/restore
 */
public function restore($id)
{
    $id = \App\Support\UrlObfuscator::decode($id) ?? $id;
    $lesson = Lesson::withTrashed()->findOrFail($id);

    if (!$lesson->trashed()) {
        return redirect()->route('lessons.index')
            ->with('error', 'This lesson is not archived.');
    }

    $lesson->restore();

    return redirect()->route('lessons.index')
        ->with('success', "Lesson '{$lesson->title}' has been restored.");
}

/**
 * Modified destroy method - now routes to soft delete
 * DELETE /lessons/{id}
 */
public function destroy($id)
{
    // This now does a soft delete by default
    return $this->softDelete($id);
}
   /**
 * Return JSON list of all active students + which ones are assigned to this lesson.
 * Used by the inline "Edit Students" modal (GET).
 */
/**
 * Return JSON list of all active students + which ones are assigned to this lesson.
 * Used by the inline "Edit Students" modal (GET).
 */
public function manageStudents($id)
{
    $id = \App\Support\UrlObfuscator::decode($id) ?? $id;
    $lesson = Lesson::findOrFail($id);
    
    // ✅ Get the current teacher's ID
    $teacherId = $this->resolveTeacherId();

    $assignedIds = LessonAssignment::where('lesson_id', $lesson->lesson_id)
        ->pluck('student_id')
        ->map(fn ($v) => (int) $v)
        ->toArray();

    // ✅ FILTER: Only show students belonging to this teacher
    $students = DB::table('students')
        ->select('student_id', 'first_name', 'last_name', 'lrn',
            'fsl_mastery_level as mastery_level',
            'program_type as program',
            'grade_level', 'section')
        ->where('status', 'active')
        ->where('teacher_id', $teacherId)  // ✅ ADD THIS LINE
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
        $id = \App\Support\UrlObfuscator::decode($id) ?? $id;
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

    private function isAdminTemplateContext(): bool
    {
        return request()->routeIs('admin.lesson-templates.*');
    }

    private function resolveTeacherId(): int
    {
        // Admin editing the default curriculum: every create/store/edit/
        // update/preview call in this controller funnels through here, so
        // this one check is enough to make all of them operate on the
        // template owner's rows instead of a real teacher's.
        if (request()->routeIs('admin.lesson-templates.*')) {
            return app(\App\Services\LessonTemplateService::class)->templateTeacherId();
        }

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
            'new_module.mastery_level' => 'nullable|in:beginner,intermediate,advanced', // ← ADD THIS
        ]);

        $nextOrder = (int) Module::where('teacher_id', $teacherId)->max('module_order') + 1;

        return Module::create([
            'teacher_id' => $teacherId,
            'title' => $request->input('new_module.title'),
            'description' => $request->input('new_module.description'),
            'mastery_level' => $request->input('new_module.mastery_level', 'beginner'), // ← ADD THIS
            'module_order' => $nextOrder,
            'status' => 'draft',
            'is_template' => $this->isAdminTemplateContext(),
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
                // Also allow a youtube_video slide with just a URL
                if (($c['content_type'] ?? '') !== 'youtube_video' || empty($c['youtube_url'])) {
                    continue;
                }
            }

            $contentType = $c['content_type'] ?? 'text';
            $mediaUrl    = null;

            if ($contentType === 'youtube_video') {
                // Validate and normalise the YouTube URL — store the standard watch URL
                $rawYtUrl = trim($c['youtube_url'] ?? '');
                $ytId = \App\Models\LessonContent::extractYoutubeId($rawYtUrl);
                if ($ytId) {
                    // Store as the canonical watch URL so it can be reformatted later
                    $mediaUrl = 'https://www.youtube.com/watch?v=' . $ytId;
                }
                // If invalid URL just skip media (shouldn't happen if client-side validated)
            } else {
                $uploadedFile = $this->getContentUploadedFile($request, $i);
                $mediaUrl = $this->uploadPublicFile($uploadedFile, 'lesson_media');
                if (!$mediaUrl && !empty($c['existing_media'])) {
                    $mediaUrl = $this->normalizeStoredMediaPath($c['existing_media']);
                }
            }

            // Auto-clear media_missing when a file has been successfully uploaded
            $mediaMissing = isset($c['media_missing']) ? (int) $c['media_missing'] : 0;
            if ($contentType !== 'youtube_video' && isset($uploadedFile) && $uploadedFile && $mediaUrl) {
                $mediaMissing = 0;
            }

            LessonContent::create([
                'lesson_id'    => $lesson->lesson_id,
                'step_number'  => $step++,
                'content_type' => $contentType,
                'title'        => $c['title'] ?? null,
                'content_text' => $c['content_text'] ?? null,
                'media_url'    => $mediaUrl,
                'gesture_name' => $c['gesture_name'] ?? null,
                'media_missing'=> $mediaMissing,
            ]);
        }
    }

    private function normalizeStoredMediaPath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $path = trim($path);

        // If it's a YouTube URL or external URL, keep as is
        if (\App\Models\LessonContent::extractYoutubeId($path) || preg_match('/^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)/i', $path)) {
            return $path;
        }

        // If it contains /storage/, extract everything after /storage/
        if (preg_match('#/storage/(.+)$#i', $path, $matches)) {
            return ltrim($matches[1], '/');
        }

        // If it starts with storage/, strip "storage/"
        if (preg_match('#^storage/(.+)$#i', $path, $matches)) {
            return ltrim($matches[1], '/');
        }

        return ltrim($path, '/');
    }

    private function uploadPublicFile(?UploadedFile $file, string $directory): ?string
{
    if (! $file || ! $file->isValid()) {
        return null;
    }

    // ✅ Log the upload
    \Log::info('Uploading file:', [
        'name' => $file->getClientOriginalName(),
        'type' => $file->getMimeType(),
        'size' => $file->getSize(),
        'directory' => $directory,
    ]);

    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
    $filename = time().'_'.uniqid().'_'.$safeName;

    // ✅ Store the file
    $path = $file->storeAs($directory, $filename, 'public');
    
    \Log::info('File stored at:', ['path' => $path]);
    
    return $path;
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

    /**
     * Normalize raw drag_drop_pairs input (from the form or from the DB) into a
     * consistent shape: [['left_text','right_text','left_image','right_image','match_id'], ...].
     * Accepts both the ['left'=>..,'right'=>..] and ['left_text'=>..,'right_text'=>..] shapes,
     * and skips pairs that have no content at all.
     */
    private function normalizeDragDropPairs($pairsInput): array
    {
        // Handle double-encoded JSON (old records saved with json_encode on a cast column)
        if (is_string($pairsInput)) {
            $decoded = json_decode($pairsInput, true);
            if (is_array($decoded)) {
                $pairsInput = $decoded;
            } else {
                return [];
            }
        }

        if (empty($pairsInput) || !is_array($pairsInput)) {
            return [];
        }

        $normalized = [];
        foreach (array_values($pairsInput) as $pairIndex => $pair) {
            if (!is_array($pair)) {
                continue;
            }

            $leftText  = $pair['left_text']  ?? $pair['left']  ?? '';
            $rightText = $pair['right_text'] ?? $pair['right'] ?? '';
            $leftImage  = $pair['left_image']  ?? '';
            $rightImage = $pair['right_image'] ?? '';

            if (trim((string) $leftText) === '' && trim((string) $rightText) === ''
                && empty($leftImage) && empty($rightImage)) {
                continue;
            }

            $normalized[] = [
                'left_text'   => $leftText,
                'right_text'  => $rightText,
                'left_image'  => $leftImage ?: null,
                'right_image' => $rightImage ?: null,
                'match_id'    => $pair['match_id'] ?? $pairIndex,
            ];
        }

        return $normalized;
    }

    /**
     * Fetch display info (name + media) for a set of gesture IDs so the preview can
     * show the students what gesture(s) they need to perform, without requiring the
     * blade view to query the database directly.
     */
    private function buildGestureDetails(array $gestureIds): array
    {
        $gestureIds = array_values(array_filter($gestureIds, fn ($id) => $id !== null && $id !== ''));
        if (empty($gestureIds)) {
            return [];
        }

        return Gesture::whereIn('gesture_id', $gestureIds)
            ->get()
            ->map(function ($gesture) {
                return [
                    'id' => $gesture->gesture_id,
                    'name' => $gesture->display_name ?? $gesture->name,
                    'image_url' => $gesture->image_url,
                    'video_url' => $gesture->video_url,
                ];
            })
            ->values()
            ->toArray();
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
            $qMedia = $this->normalizeStoredMediaPath($q['existing_media']);
        }

        $questionData = [
            'quiz_id' => $quiz->quiz_id,
            'question_number' => $qNum++,
            'question_type' => $q['type'] ?? 'multiple_choice',
            'question_text' => $q['question'],
            'media_url' => $qMedia,
            'gesture_required' => $q['gesture_required'] ?? null,
            'points' => 1,
        ];

        // ==========================================
        // Handle drag and drop data
        // ==========================================
        if (($q['type'] ?? '') === 'drag_drop') {
            if (!empty($q['drag_drop_pairs'])) {
                $processedPairs = [];
                foreach ($q['drag_drop_pairs'] as $pairIndex => $pair) {
                    $leftText = $pair['left'] ?? $pair['left_text'] ?? '';
                    $rightText = $pair['right'] ?? $pair['right_text'] ?? '';
                    $leftImage = !empty($pair['left_image']) ? $this->normalizeStoredMediaPath($pair['left_image']) : '';
                    $rightImage = !empty($pair['right_image']) ? $this->normalizeStoredMediaPath($pair['right_image']) : '';
                    
                    if (!empty($leftText) || !empty($rightText) || !empty($leftImage) || !empty($rightImage)) {
                        $processedPairs[] = [
                            'left_text' => $leftText,
                            'right_text' => $rightText,
                            'left_image' => $leftImage,
                            'right_image' => $rightImage,
                            'match_id' => $pairIndex,
                        ];
                    }
                }
                
                if (!empty($processedPairs)) {
                    $questionData['drag_drop_pairs'] = $processedPairs;
                }
            }
        }

        // ==========================================
        // Handle gesture recognition data - FIXED
        // ==========================================
        if (($q['type'] ?? '') === 'gesture') {
            $isFingerspelling = ($q['gesture_module_id'] ?? '') == '6';
            
            if ($isFingerspelling) {
                // ✅ FIX: Get the words from the request directly
                // The data is in $q['fingerspelling_words'] as a string with newlines
                $wordsText = $q['fingerspelling_words'] ?? '';
                
                // ✅ If it's empty, try to get it from the raw request (just in case)
                if (empty($wordsText)) {
                    $wordsText = $request->input("quiz.{$qi}.fingerspelling_words", '');
                }
                
                \Log::info('📝 FINGERSPELLING WORDS TEXT:', [
                    'words_text' => $wordsText,
                    'type' => gettype($wordsText),
                    'length' => strlen($wordsText),
                    'raw' => $wordsText
                ]);
                
                // ✅ Process the words - split by newline and clean
                $words = [];
                if (!empty($wordsText) && is_string($wordsText)) {
                    // Split by newline (both \n and \r\n)
                    $lines = preg_split('/\r\n|\r|\n/', $wordsText);
                    foreach ($lines as $line) {
                        $word = trim($line);
                        // Remove any non-letter characters and convert to uppercase
                        $word = preg_replace('/[^A-Z]/', '', strtoupper($word));
                        if (strlen($word) > 0) {
                            $words[] = $word;
                        }
                    }
                }
                
                \Log::info('📝 PROCESSED WORDS:', ['words' => $words]);
                
                // Get all unique gesture IDs from all words
                $allGestureIds = [];
                foreach ($words as $word) {
                    foreach (str_split($word) as $letter) {
                        $id = $this->letterToGestureId($letter);
                        if ($id) {
                            $allGestureIds[] = $id;
                        }
                    }
                }
                $allGestureIds = array_values(array_unique($allGestureIds));
                
                \Log::info('📝 GESTURE IDS:', ['ids' => $allGestureIds]);
                
                // ✅ Store with full fingerspelling data
                $gestureData = [
                    'module_id' => '6',
                    'gesture_ids' => $allGestureIds,
                    'is_fingerspelling' => true,
                    'words' => $words, // Store the array of words
                ];
                $questionData['gesture_data'] = $gestureData;
                
                // ✅ Update question text to show the words being tested
                if (!empty($words)) {
                    $questionData['question_text'] = "Fingerspell: " . implode(' → ', $words);
                }
            } else {
                // Regular gesture
                $gestureData = [
                    'module_id' => $q['gesture_module_id'] ?? null,
                    'gesture_ids' => $q['gesture_ids'] ?? [],
                ];
                $questionData['gesture_data'] = $gestureData;
            }
        }

        $question = QuizQuestion::create($questionData);

        // Skip creating options for drag_drop and gesture types
        if (($q['type'] ?? '') === 'drag_drop' || ($q['type'] ?? '') === 'gesture') {
            continue;
        }

        // ==========================================
        // Handle true_false questions
        // ==========================================
        if (($q['type'] ?? '') === 'true_false') {
            $correctIndex = isset($q['correct']) ? (int) $q['correct'] : 0;
            
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

        // ==========================================
        // Handle multiple choice questions
        // ==========================================
        $optionInput = is_array($q['options'] ?? null) ? $q['options'] : [];
        $optionFiles = [];
        if (isset($quizFiles[$qi]['options']) && is_array($quizFiles[$qi]['options'])) {
            $optionFiles = $quizFiles[$qi]['options'];
        }

        $correctIndex = isset($q['correct']) ? (int) $q['correct'] : 0;

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
                $optImagePath = $this->normalizeStoredMediaPath($optData['existing_image']);
            }

            if (trim((string) $optText) === '' && empty($optImagePath)) {
                continue;
            }

            if (trim((string) $optText) === '') {
                $optText = 'Option '.chr(65 + (int) $idx);
            }

            QuizOption::create([
                'question_id'      => $question->question_id,
                'option_text'      => $optText,
                'option_media_url' => $optImagePath,
                'is_correct'       => (int) $idx === $correctIndex,
            ]);
        }
    }
}


/**
 * Convert a letter to its gesture ID (A=1, B=2, ..., Z=26)
 */
private function letterToGestureId(string $letter): ?int
{
    $letter = strtoupper(trim($letter));
    if (strlen($letter) !== 1 || $letter < 'A' || $letter > 'Z') {
        return null;
    }
    return ord($letter) - 64; // A=1, B=2, ...
}

    /**
     * Fetch all gesture names available in the database.
     * Used to constrain the AI so it only recommends gestures that actually exist.
     */
    private function getAvailableGestureNames(): array
    {
        return DB::table('gestures')
            ->pluck('name')
            ->map(fn($name) => strtoupper(trim($name)))
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

private function resolveGestureQuestions(array $quiz): array
{
    foreach ($quiz as &$question) {
        if (isset($question['type']) && $question['type'] === 'gesture') {
            $question['gesture_warning'] = false;
            
            // 🆕 Check if this is fingerspelling (has fingerspelling_words)
            if (!empty($question['fingerspelling_words'])) {
                $wordsText = $question['fingerspelling_words'];
                
                // 🔥 FIX: Split by newline ONLY
                $words = array_filter(
                    array_map('trim', explode("\n", $wordsText)),
                    fn($w) => strlen($w) > 0
                );
                $words = array_map(
                    fn($w) => preg_replace('/[^A-Z]/', '', strtoupper($w)),
                    $words
                );
                $words = array_values(array_filter($words, fn($w) => strlen($w) > 0));
                
                $allGestureIds = [];
                foreach ($words as $word) {
                    foreach (str_split($word) as $letter) {
                        $id = $this->letterToGestureId($letter);
                        if ($id) {
                            $allGestureIds[] = $id;
                        }
                    }
                }
                $allGestureIds = array_values(array_unique($allGestureIds));
                
                $question['gesture_module_id'] = '6';
                $question['gesture_ids'] = $allGestureIds;
                $question['gesture_data'] = [
                    'module_id' => '6',
                    'gesture_ids' => $allGestureIds,
                    'is_fingerspelling' => true,
                    'words' => $words, // ✅ Array of words
                ];
                $question['gesture_warning'] = empty($allGestureIds);
                continue;
            }
                if (!empty($question['gesture_names'])) {
                    $names      = array_map('trim', $question['gesture_names']);
                    $namesUpper = array_map('strtoupper', $names);

                    // Fetch all matches, preferring rows with a non-null module_id
                    $gestures = DB::table('gestures')
                        ->whereIn(DB::raw('UPPER(name)'), $namesUpper)
                        ->orderByRaw('CASE WHEN module_id IS NULL THEN 1 ELSE 0 END ASC')
                        ->get();

                    // Deduplicate: keep the best match per uppercased name (module_id wins)
                    $best = collect();
                    $seen = [];
                    foreach ($gestures as $g) {
                        $key = strtoupper($g->name);
                        if (!isset($seen[$key])) {
                            $seen[$key] = true;
                            $best->push($g);
                        }
                    }
                    $gestures = $best;

                    if ($gestures->isNotEmpty()) {
                        // Use the module of the first gesture that has a module_id
                        $withModule = $gestures->firstWhere('module_id', '!=', null);
                        $moduleId   = $withModule ? $withModule->module_id : $gestures->first()->module_id;
                        $gestureIds = $gestures->pluck('gesture_id')->toArray();

                        $question['gesture_module_id'] = $moduleId;
                        $question['gesture_ids']        = $gestureIds;
                        $question['gesture_names']      = $gestures->pluck('name')->toArray();
                        $question['gesture_data']       = [
                            'module_id'   => $moduleId,
                            'gesture_ids' => $gestureIds,
                        ];
                    } else {
                        // Warn user there is no matching gesture module/data in DB
                        $question['gesture_module_id'] = null;
                        $question['gesture_ids']        = [];
                        $question['gesture_data']       = null;
                        $question['gesture_warning']   = true;
                    }
                } else {
                    $question['gesture_module_id'] = null;
                    $question['gesture_ids']        = [];
                    $question['gesture_data']       = null;
                    $question['gesture_warning']   = true;
                }
            }
        }

        return $quiz;
    }

    /**
 * Show the checkpoint exam creation form.
 * GET /lessons/checkpoint-exam/create
 */
public function createCheckpointExam(Request $request)
{
    $teacherId = $this->resolveTeacherId();
    $moduleId = $request->query('module_id');

    if (!$moduleId) {
        $indexRoute = $this->isAdminTemplateContext() ? 'admin.lesson-templates.index' : 'lessons.index';
        return redirect()->route($indexRoute)->with('error', 'Please select a module first.');
    }

    // Verify module belongs to teacher
    $module = Module::where('module_id', $moduleId)
        ->where('teacher_id', $teacherId)
        ->firstOrFail();

    // Get all lessons in this module with their quiz questions
    $lessons = Lesson::where('module_id', $moduleId)
        ->where('teacher_id', $teacherId)
        ->where('status', 'published')
        ->with(['quiz.questions' => function($query) {
            $query->orderBy('question_number');
        }])
        ->orderBy('module_order')
        ->get();

    // Filter to only lessons that have quiz questions
    $lessonsWithQuizzes = $lessons->filter(function($lesson) {
        return $lesson->quiz && $lesson->quiz->questions->count() > 0;
    });

    // Check if there are at least 2 lessons with quizzes
    if ($lessonsWithQuizzes->count() < 2) {
        return redirect()->route('lessons.index')
            ->with('error', 'You need at least 2 published lessons with quizzes in this module to create a checkpoint exam.');
    }

    // Check if there are lessons that haven't been used in a checkpoint exam yet
    $usedLessonIds = CheckpointExamQuestion::whereHas('exam', function($query) use ($moduleId) {
        $query->where('module_id', $moduleId);
    })->distinct('source_lesson_id')->pluck('source_lesson_id')->toArray();

    $availableLessons = $lessonsWithQuizzes->filter(function($lesson) use ($usedLessonIds) {
        return !in_array($lesson->lesson_id, $usedLessonIds);
    });

    if ($availableLessons->count() < 2) {
        return redirect()->route('lessons.index')
            ->with('error', 'All lessons in this module have already been used in checkpoint exams. Please add new lessons first.');
    }

    // Get all quiz questions from available lessons
    $availableQuestions = [];
    foreach ($availableLessons as $lesson) {
        foreach ($lesson->quiz->questions as $question) {
            $availableQuestions[] = [
                'question_id' => $question->question_id,
                'lesson_id' => $lesson->lesson_id,
                'lesson_title' => $lesson->title,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'media_url' => $question->media_url,
                'options' => $question->options->map(function($opt) {
                    return [
                        'text' => $opt->option_text,
                        'image' => $opt->option_media_url,
                        'is_correct' => $opt->is_correct,
                    ];
                })->toArray(),
                'drag_drop_pairs' => $question->drag_drop_pairs ?? [],
                'gesture_data' => $question->gesture_data ?? [],
                'correct_answer' => $question->options->where('is_correct', true)->first()->option_text ?? null,
            ];
        }
    }

    return view('lessons.checkpoint-exam.create', compact('module', 'availableLessons', 'availableQuestions'));
}

/**
 * Store a checkpoint exam.
 * POST /lessons/checkpoint-exam
 */
public function storeCheckpointExam(Request $request)
{
    $validated = $request->validate([
        'module_id' => 'required|exists:modules,module_id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'questions' => 'required|array|min:1',
        'questions.*.source_question_id' => 'required|exists:quiz_questions,question_id',
        'questions.*.points' => 'required|integer|min:1|max:10',
        'passing_score_percentage' => 'nullable|integer|min:1|max:100',
        'time_limit_minutes' => 'nullable|integer|min:1|max:180',
    ]);

    $teacherId = $this->resolveTeacherId();
    $module = Module::where('module_id', $validated['module_id'])
        ->where('teacher_id', $teacherId)
        ->firstOrFail();

    $totalPoints = array_sum(array_column($validated['questions'], 'points'));
    $passingPercentage = $validated['passing_score_percentage'] ?? 60;
    $passingScore = max(1, round(($passingPercentage / 100) * $totalPoints));

    $exam = DB::transaction(function () use ($validated, $teacherId, $module, $totalPoints, $passingScore) {
        $exam = CheckpointExam::create([
            'module_id' => $validated['module_id'],
            'teacher_id' => $teacherId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'total_points' => $totalPoints,
            'passing_score' => $passingScore,
            'time_limit_minutes' => $validated['time_limit_minutes'] ?? 60,
            'status' => 'draft',
            'is_template' => $this->isAdminTemplateContext(),
        ]);

        $questionNumber = 1;
        foreach ($validated['questions'] as $questionData) {
            $sourceQuestion = QuizQuestion::with('options')->findOrFail($questionData['source_question_id']);

            // Get the correct answer
            $correctAnswer = null;
            if ($sourceQuestion->question_type === 'multiple_choice' || $sourceQuestion->question_type === 'true_false') {
                $correctOption = $sourceQuestion->options->where('is_correct', true)->first();
                $correctAnswer = $correctOption ? $correctOption->option_text : null;
            }

            // 🔥 FIX: Properly extract drag_drop_pairs
            $dragDropPairs = null;
            if ($sourceQuestion->question_type === 'drag_drop') {
                $dragDropPairs = $sourceQuestion->drag_drop_pairs;
                // If it's a string, decode it
                if (is_string($dragDropPairs)) {
                    $dragDropPairs = json_decode($dragDropPairs, true);
                }
                // Ensure it's an array
                if (!is_array($dragDropPairs)) {
                    $dragDropPairs = [];
                }
                // If empty, set to null so it stores as NULL in database
                if (empty($dragDropPairs)) {
                    $dragDropPairs = null;
                }
            }

            // 🔥 FIX: Properly extract gesture_data
            $gestureData = null;
            if ($sourceQuestion->question_type === 'gesture') {
                $gestureData = $sourceQuestion->gesture_data;
                if (is_string($gestureData)) {
                    $gestureData = json_decode($gestureData, true);
                }
                if (!is_array($gestureData)) {
                    $gestureData = [];
                }
                if (empty($gestureData)) {
                    $gestureData = null;
                }
            }

            // 🔥 FIX: Properly extract options_data
            $optionsData = null;
            if (in_array($sourceQuestion->question_type, ['multiple_choice', 'true_false'])) {
                $optionsData = $sourceQuestion->options->map(function($opt) {
                    return [
                        'text' => $opt->option_text,
                        'image' => $opt->option_media_url,
                        'is_correct' => $opt->is_correct,
                    ];
                })->toArray();
                if (empty($optionsData)) {
                    $optionsData = null;
                }
            }

            CheckpointExamQuestion::create([
                'exam_id' => $exam->exam_id,
                'source_lesson_id' => $sourceQuestion->quiz->lesson_id,
                'source_question_id' => $sourceQuestion->question_id,
                'question_number' => $questionNumber++,
                'question_text' => $sourceQuestion->question_text,
                'question_type' => $sourceQuestion->question_type,
                'media_url' => $sourceQuestion->media_url,
                'points' => $questionData['points'],
                'options_data' => $optionsData,
                'drag_drop_pairs' => $dragDropPairs,
                'gesture_data' => $gestureData,
                'correct_answer' => $correctAnswer,
            ]);
        }

        return $exam;
    });

    $showRoute = $this->isAdminTemplateContext()
        ? 'admin.lesson-templates.checkpoint-exam.show'
        : 'lessons.checkpoint-exam.show';

    return redirect()->route($showRoute, $exam->hash_id)
        ->with('success', 'Checkpoint exam created successfully! You can now publish it to students.');
}

/**
 * Show a checkpoint exam.
 * GET /lessons/checkpoint-exam/{id}
 */
public function showCheckpointExam($id)
{
    $realId = \App\Support\UrlObfuscator::decode($id) ?? $id;
    $exam = CheckpointExam::with(['questions', 'module'])
        ->findOrFail($realId);

    if ($id !== $exam->hash_id) {
        return redirect()->route('lessons.checkpoint-exam.show', $exam->hash_id);
    }

    // Format questions for display
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

        return [
            'question_id' => $question->question_id,
            'question_number' => $question->question_number,
            'question_text' => $question->question_text,
            'question_type' => $question->question_type,
            'media_url' => $question->media_url,
            'points' => $question->points,
            'options' => $optionsData,
            'drag_drop_pairs' => $dragDropPairs,
            'gesture_data' => $gestureData,
            'correct_answer' => $question->correct_answer,
        ];
    });

    return view('lessons.checkpoint-exam.show', compact('exam', 'questions'));
}

/**
 * Publish a checkpoint exam.
 * POST /lessons/checkpoint-exam/{id}/publish
 */
public function publishCheckpointExam(Request $request, $id)
{
    $realId = \App\Support\UrlObfuscator::decode($id) ?? $id;
    $exam = CheckpointExam::findOrFail($realId);

    $validated = $request->validate([
        'publish_option' => 'required|in:all,program,mastery,selected',
        'program' => 'required_if:publish_option,program|nullable|string',
        'mastery_level' => 'required_if:publish_option,mastery|nullable|string',
        'students' => 'required_if:publish_option,selected|nullable|array|min:1',
        'students.*' => 'exists:students,student_id',
        'notify_students' => 'boolean',
    ]);

    $teacherId = $this->resolveTeacherId();

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
                ->where('program_type', $validated['program'])
                ->pluck('student_id')
                ->toArray();
            break;
        case 'mastery':
            $studentIds = DB::table('students')
                ->where('status', 'active')
                ->where('fsl_mastery_level', $validated['mastery_level'])
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

    // Publish the exam
    $exam->update([
        'status' => 'published',
        'published_at' => now(),
    ]);

    // Create assignments
    $assignedCount = 0;
    foreach ($studentIds as $studentId) {
        $exists = CheckpointExamAssignment::where('exam_id', $exam->exam_id)
            ->where('student_id', $studentId)
            ->exists();

        if (!$exists) {
            CheckpointExamAssignment::create([
                'exam_id' => $exam->exam_id,
                'student_id' => $studentId,
                'assigned_at' => now(),
                'status' => 'pending',
                'is_locked' => true,
                'notified' => $request->input('notify_students', false),
            ]);
            $assignedCount++;

            // Create notification
            $this->createCheckpointExamNotification($studentId, $exam);
        }
    }

    $message = "Checkpoint exam published successfully to {$assignedCount} students!";

    if ($request->input('notify_students')) {
        $message .= ' Students will be notified.';
    }

    return redirect()->route('lessons.index')->with('success', $message);
}

/**
 * Create notification for a checkpoint exam.
 */
protected function createCheckpointExamNotification($studentId, $exam)
{
    $exists = \App\Models\StudentNotification::where('student_id', $studentId)
        ->where('type', 'checkpoint_exam')
        ->where('data->exam_id', $exam->exam_id)
        ->exists();

    if ($exists) {
        return;
    }

    \App\Models\StudentNotification::create([
        'student_id' => $studentId,
        'type' => 'checkpoint_exam',
        'title' => '📝 Checkpoint Exam Available!',
        'message' => "\"{$exam->title}\" is ready for you to take! 🎯",
        'icon' => 'trophy',
        'color' => '#8B5CF6',
        'data' => ['exam_id' => $exam->exam_id, 'exam_title' => $exam->title],
        'action_url' => '/checkpoint-exams',
        'is_read' => false,
    ]);
}

/**
 * Delete a checkpoint exam.
 * DELETE /lessons/checkpoint-exam/{id}
 */
public function destroyCheckpointExam($id)
{
    $realId = \App\Support\UrlObfuscator::decode($id) ?? $id;
    $exam = CheckpointExam::findOrFail($realId);

    DB::transaction(function () use ($exam) {
        $exam->questions()->delete();
        $exam->assignments()->delete();
        $exam->delete();
    });

    return redirect()->route('lessons.index')
        ->with('success', 'Checkpoint exam deleted successfully.');
}

/**
 * Get available lessons for checkpoint exam (AJAX).
 * GET /lessons/checkpoint-exam/available-questions
 */
public function getAvailableExamQuestions(Request $request)
{
    $moduleId = $request->query('module_id');
    $teacherId = $this->resolveTeacherId();

    if (!$moduleId) {
        return response()->json(['error' => 'Module ID required'], 400);
    }

    // Get lessons with quizzes that haven't been used in checkpoint exams
    $usedLessonIds = CheckpointExamQuestion::whereHas('exam', function($query) use ($moduleId) {
        $query->where('module_id', $moduleId);
    })->distinct('source_lesson_id')->pluck('source_lesson_id')->toArray();

    $lessons = Lesson::where('module_id', $moduleId)
        ->where('teacher_id', $teacherId)
        ->where('status', 'published')
        ->whereNotIn('lesson_id', $usedLessonIds)
        ->with(['quiz.questions.options'])
        ->orderBy('module_order')
        ->get();

    $result = [];
    foreach ($lessons as $lesson) {
        if ($lesson->quiz && $lesson->quiz->questions->count() > 0) {
            $questions = [];
            foreach ($lesson->quiz->questions as $question) {
                $questions[] = [
                    'question_id' => $question->question_id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'media_url' => $question->media_url,
                    'options' => $question->options->map(function($opt) {
                        return [
                            'text' => $opt->option_text,
                            'image' => $opt->option_media_url,
                            'is_correct' => $opt->is_correct,
                        ];
                    })->toArray(),
                    'drag_drop_pairs' => $question->drag_drop_pairs ?? [],
                    'gesture_data' => $question->gesture_data ?? [],
                ];
            }
            $result[] = [
                'lesson_id' => $lesson->lesson_id,
                'lesson_title' => $lesson->title,
                'questions' => $questions,
            ];
        }
    }

    return response()->json($result);
}

/**
 * Admin: Show publish configuration page (NO students)
 * GET /admin/lessons/{lesson}/publish-config
 */
public function showAdminPublishConfig($id)
{
    $realId = \App\Support\UrlObfuscator::decode($id) ?? $id;
    $lesson = Lesson::findOrFail($realId);

    if ($id !== $lesson->hash_id) {
        return redirect()->route('admin.lesson-templates.publish.config', $lesson->hash_id);
    }

    $teacherId = $this->resolveTeacherId();
    $modules = Module::where('teacher_id', $teacherId)
        ->orderBy('module_order')
        ->get();

    // Return admin-specific publish config view
    return view('admin.lessons.publish-config', compact('lesson', 'modules'));
}

/**
 * Admin: Publish a template lesson (NO students assigned)
 * POST /admin/lessons/{lesson}/publish
 */
public function adminPublishLesson(Request $request, $id)
{
    $id = \App\Support\UrlObfuscator::decode($id) ?? $id;
    $lesson = Lesson::findOrFail($id);

    $validated = $request->validate([
        'module_action' => 'required|in:existing,new',
        'module_id' => 'nullable|required_if:module_action,existing|exists:modules,module_id',
        'new_module.title' => 'nullable|required_if:module_action,new|string|max:255',
        'new_module.description' => 'nullable|string|max:1000',
        'new_module.mastery_level' => 'nullable|in:beginner,intermediate,advanced',
    ]);

    $teacherId = $this->resolveTeacherId();
    $moduleId = $this->resolveModuleId($request, $teacherId);
    
    if (!$moduleId) {
        return back()->withErrors(['module_action' => 'Please select or create a module before publishing.'])->withInput();
    }

    // Just publish the lesson - NO students assigned
    $lesson->update([
        'module_id' => $moduleId,
        'status' => 'published',
        'published_at' => now(),
    ]);

    return redirect()->route('admin.lesson-templates.index')
        ->with('success', "Lesson '{$lesson->title}' published successfully to the default curriculum!");
}

/**
 * Reorder lessons in a module or unassigned list.
 * POST /lessons/reorder
 */
public function reorder(Request $request)
{
    $teacherId = $this->resolveTeacherId();
    if (!$teacherId) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $validated = $request->validate([
        'module_id'   => 'nullable|integer',
        'lesson_ids'  => 'required|array',
        'lesson_ids.*'=> 'required',
    ]);

    $lessonIds = $validated['lesson_ids'];
    $moduleId  = $validated['module_id'] ?? null;

    // Decode hash IDs or integers
    $decodedIds = array_map(function($id) {
        return \App\Support\UrlObfuscator::decode($id) ?? $id;
    }, $lessonIds);

    DB::transaction(function () use ($decodedIds, $teacherId, $moduleId) {
        foreach ($decodedIds as $index => $lessonId) {
            $updateData = ['module_order' => $index + 1];
            if ($moduleId !== null) {
                $updateData['module_id'] = $moduleId;
            }
            Lesson::withTrashed()
                ->where('lesson_id', $lessonId)
                ->where('teacher_id', $teacherId)
                ->update($updateData);
        }
    });

    return response()->json([
        'success' => true,
        'message' => 'Lesson order updated successfully.',
    ]);
}

}