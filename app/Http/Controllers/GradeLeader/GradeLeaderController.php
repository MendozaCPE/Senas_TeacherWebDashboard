<?php

namespace App\Http\Controllers\GradeLeader;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CheckpointExam;
use App\Models\CheckpointExamAssignment;
use App\Models\GestureMedia;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentLessonProgress;
use App\Models\StudentYearEnrollment;
use App\Models\Teacher;
use App\Models\TeacherMedia;
use App\Models\TeacherNotification;
use App\Models\User;
use App\Services\LessonTemplateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * GradeLeaderController
 *
 * Handles all tabs of the Grade Leader portal:
 *   - dashboard  : school-wide KPIs + performance widgets
 *   - lessons    : read-only view of default curriculum templates
 *   - media      : read-only view of system gesture media
 *   - analytics  : school-scoped academic charts & breakdowns
 *
 * All data is strictly filtered by the Grade Leader's assigned school_id.
 * No create / edit / delete actions are exposed.
 */
class GradeLeaderController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve the current teacher_leader's school_id.
     * Falls back to 0 so queries still run without null errors.
     */
    private function schoolId(): int
    {
        return (int) (Auth::user()->teacher->school_id ?? 0);
    }

    private function schoolTeacherIds(int $schoolId): \Illuminate\Support\Collection
    {
        return Teacher::where('school_id', $schoolId)
            ->whereHas('user', fn ($q) => $q
                ->where('role', 'teacher')
                ->where('is_system', false)
            )
            ->pluck('id');
    }

    /**
     * Collect the IDs of every student in this school.
     */
    private function schoolStudentIds(int $schoolId): \Illuminate\Support\Collection
    {
        return Student::where('school_id', $schoolId)->pluck('student_id');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. DASHBOARD — School Academic Performance
    // ─────────────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $schoolId   = $this->schoolId();
        $school     = Auth::user()->teacher->school ?? null;

        $teacherIds = $this->schoolTeacherIds($schoolId);
        $studentIds = $this->schoolStudentIds($schoolId);

        // ── KPI Counts ───────────────────────────────────────────────────────
        $totalTeachers = $teacherIds->count();
        $totalStudents = $studentIds->count();

        // Lesson completion rate for the school
        $lessonTotals = DB::table('lesson_assignments')
            ->whereIn('student_id', $studentIds)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
            ->first();

        $totalAssigned  = (int) ($lessonTotals->total ?? 0);
        $totalCompleted = (int) ($lessonTotals->completed ?? 0);
        $completionRate = $totalAssigned > 0
            ? round(($totalCompleted / $totalAssigned) * 100, 1)
            : 0;

        // Average quiz score across the school
        $avgQuizScore = round((float) DB::table('quiz_attempts')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->avg('percentage') ?? 0, 1);

        // Active students in last 7 days
        $activeStudents = Student::whereIn('student_id', $studentIds)
            ->where('last_activity_date', '>=', Carbon::now()->subDays(7))
            ->count();

        // ── 14-Day Activity Trend ─────────────────────────────────────────────
        $activityTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $completionsCount = DB::table('lesson_assignments')
                ->whereIn('student_id', $studentIds)
                ->where('status', 'completed')
                ->whereDate('updated_at', $date)
                ->count();

            $activeStudentsOnDay = DB::table('lesson_assignments')
                ->whereIn('student_id', $studentIds)
                ->whereDate('updated_at', $date)
                ->distinct()
                ->count('student_id');

            $activityTrend[] = [
                'label'       => Carbon::now()->subDays($i)->format('M j'),
                'day'         => $i === 0 ? 'Today' : ($i === 1 ? 'Yesterday' : Carbon::now()->subDays($i)->format('l')),
                'date'        => Carbon::now()->subDays($i)->format('M j, Y'),
                'completions' => $completionsCount,
                'students'    => $activeStudentsOnDay,
            ];
        }

        // ── Top Classes (teachers by student avg quiz score) ──────────────────
        $topClasses = Teacher::whereIn('id', $teacherIds)
            ->with('user')
            ->withCount('students')
            ->get()
            ->map(function ($teacher) {
                $studentIds = $teacher->students()->pluck('student_id');
                $avg = DB::table('quiz_attempts')
                    ->whereIn('student_id', $studentIds)
                    ->where('status', 'completed')
                    ->avg('percentage') ?? 0;
                return [
                    'teacher'       => $teacher,
                    'avg_score'     => round((float) $avg, 1),
                    'student_count' => $teacher->students_count,
                ];
            })
            ->sortByDesc('avg_score')
            ->values()
            ->take(5);

        // ── More Data Needed (teachers with no quiz attempts yet) ────────────
        $moreDataNeeded = Teacher::whereIn('id', $teacherIds)
            ->with('user')
            ->withCount('students')
            ->get()
            ->map(function ($teacher) {
                $tStudentIds = $teacher->students()->pluck('student_id');
                $attemptCount = DB::table('quiz_attempts')
                    ->whereIn('student_id', $tStudentIds)
                    ->where('status', 'completed')
                    ->count();
                $assignedCount = DB::table('lesson_assignments')
                    ->whereIn('student_id', $tStudentIds)
                    ->count();
                return [
                    'teacher'        => $teacher,
                    'student_count'  => $teacher->students_count,
                    'attempt_count'  => $attemptCount,
                    'assigned_count' => $assignedCount,
                ];
            })
            ->filter(fn ($c) => $c['student_count'] > 0 && $c['attempt_count'] === 0)
            ->sortByDesc('student_count')
            ->values()
            ->take(5);

        // ── School Year Transition Context ──────────────────────────────────
        $activeSy = SchoolYear::activeForSchool($schoolId);
        $activeSyName = $activeSy ? $activeSy->name : SchoolYear::currentDepEdLabel();
        [$startY, $endY] = explode('-', $activeSyName);
        $targetSyName = ((int)$endY) . '-' . (((int)$endY) + 1);

        $transitionNotifs = TeacherNotification::whereIn('teacher_id', $teacherIds)
            ->where('type', 'new_school_year')
            ->orderByDesc('id')
            ->get()
            ->groupBy('teacher_id');

        // ── Recently active teachers in the school ───────────────────────────
        $recentTeachers = Teacher::whereIn('id', $teacherIds)
            ->with(['user', 'students'])
            ->get()
            ->map(function ($teacher) use ($studentIds, $transitionNotifs) {
                // Per-teacher stats
                $tStudentIds = $teacher->students()->pluck('student_id');
                $avgScore = DB::table('quiz_attempts')
                    ->whereIn('student_id', $tStudentIds)
                    ->where('status', 'completed')
                    ->avg('percentage') ?? 0;
                $completedLessons = DB::table('lesson_assignments')
                    ->whereIn('student_id', $tStudentIds)
                    ->where('status', 'completed')
                    ->count();
                $activeStudentsCount = Student::whereIn('student_id', $tStudentIds)
                    ->where('last_activity_date', '>=', Carbon::now()->subDays(7))
                    ->count();

                $tNotif = $transitionNotifs->get($teacher->id)?->first();
                $transitionStatus = 'none';
                if ($tNotif) {
                    $transitionStatus = $tNotif->action_status ?: 'pending';
                }

                return [
                    'teacher'           => $teacher,
                    'user'              => $teacher->user,
                    'student_count'     => $tStudentIds->count(),
                    'avg_score'         => round((float) $avgScore, 1),
                    'lessons_done'      => $completedLessons,
                    'active_students'   => $activeStudentsCount,
                    'last_active'       => $teacher->user?->updated_at,
                    'transition_status' => $transitionStatus,
                    'transition_notif'  => $tNotif,
                ];
            })
            ->sortByDesc('last_active')
            ->values()
            ->take(12);

        // ── Sparklines (7 days) ───────────────────────────────────────────────
        $sparkDates      = [];
        $sparkStudents   = [];
        $sparkLessons    = [];
        $sparkTeachers   = [];
        $sparkQuizScores = [];
        for ($i = 6; $i >= 0; $i--) {
            $day  = Carbon::now()->subDays($i);
            $date = $day->toDateString();
            $sparkDates[]    = ['short' => $day->format('M j'), 'day' => $i === 0 ? 'Today' : ($i === 1 ? 'Yesterday' : $day->format('l')), 'date' => $day->format('M j, Y')];
            $sparkStudents[] = DB::table('lesson_assignments')->whereIn('student_id', $studentIds)->whereDate('updated_at', $date)->distinct()->count('student_id');
            $sparkLessons[]  = DB::table('lesson_assignments')->whereIn('student_id', $studentIds)->where('status', 'completed')->whereDate('updated_at', $date)->count();
            $sparkTeachers[] = User::whereIn('id', Teacher::whereIn('id', $teacherIds)->pluck('user_id'))->whereDate('updated_at', '<=', $date)->count();
            $dayAvg = DB::table('quiz_attempts')
                ->whereIn('student_id', $studentIds)
                ->where('status', 'completed')
                ->whereDate('updated_at', $date)
                ->avg('percentage');
            $sparkQuizScores[] = $dayAvg !== null ? (int) round($dayAvg) : 0;
        }

        // ── Program Type Breakdown (for donut chart) ─────────────────────────
        $programTypes = [
            ['label' => 'Regular',       'color' => '#0d326b', 'grad_from' => '#1e4b8f', 'grad_to' => '#071c3f', 'grad_id' => 'ptGradRegular'],
            ['label' => 'Inclusion',     'color' => '#1a6fd4', 'grad_from' => '#3b82f6', 'grad_to' => '#1a6fd4', 'grad_id' => 'ptGradInclusion'],
            ['label' => 'Transition',    'color' => '#3b82f6', 'grad_from' => '#60a5fa', 'grad_to' => '#3b82f6', 'grad_id' => 'ptGradTransition'],
            ['label' => 'Self-contained','color' => '#93c5fd', 'grad_from' => '#bfdbfe', 'grad_to' => '#93c5fd', 'grad_id' => 'ptGradSelf'],
        ];
        $programTypeCounts = Student::whereIn('student_id', $studentIds)
            ->selectRaw('program_type, COUNT(*) as count')
            ->groupBy('program_type')
            ->pluck('count', 'program_type');

        $programDonut = collect($programTypes)->map(function ($pt) use ($programTypeCounts, $totalStudents) {
            $count = (int) ($programTypeCounts[$pt['label']] ?? 0);
            return array_merge($pt, [
                'count' => $count,
                'pct'   => $totalStudents > 0 ? round($count / $totalStudents * 100) : 0,
            ]);
        })->filter(fn ($pt) => $pt['count'] > 0)->values();

        // ── FSL Mastery Distribution (School-wide) ───────────────────────────
        $masteryCounts = Student::whereIn('student_id', $studentIds)
            ->selectRaw('fsl_mastery_level, COUNT(*) as count')
            ->groupBy('fsl_mastery_level')
            ->pluck('count', 'fsl_mastery_level');

        $fslMasteryTiers = [
            [
                'key'         => 'beginner',
                'label'       => 'Beginner',
                'sublabel'    => 'Alphabet, Greetings & Basic Gestures',
                'color'       => '#3b82f6',
                'bar_from'    => '#60a5fa',
                'bar_to'      => '#3b82f6',
                'badge_bg'    => '#eff6ff',
                'badge_text'  => '#1a6fd4',
                'dot'         => '#3b82f6',
                'icon'        => 'school',
            ],
            [
                'key'         => 'intermediate',
                'label'       => 'Intermediate',
                'sublabel'    => 'Numbers, Common Signs & Phrases',
                'color'       => '#1a6fd4',
                'bar_from'    => '#3b82f6',
                'bar_to'      => '#1a6fd4',
                'badge_bg'    => '#dbeafe',
                'badge_text'  => '#1e4b8f',
                'dot'         => '#1a6fd4',
                'icon'        => 'psychology',
            ],
            [
                'key'         => 'advanced',
                'label'       => 'Advanced',
                'sublabel'    => 'Expressive & Conversational FSL',
                'color'       => '#0d326b',
                'bar_from'    => '#1e4b8f',
                'bar_to'      => '#0d326b',
                'badge_bg'    => '#0d326b',
                'badge_text'  => '#ffffff',
                'dot'         => '#0d326b',
                'icon'        => 'verified',
            ],
        ];

        $fslMasteryData = collect($fslMasteryTiers)->map(function ($tier) use ($masteryCounts, $totalStudents) {
            $cnt = (int) ($masteryCounts[$tier['label']] ?? 0);
            return array_merge($tier, [
                'count' => $cnt,
                'pct'   => $totalStudents > 0 ? round(($cnt / $totalStudents) * 100) : 0,
            ]);
        });

        return view('grade-leader.dashboard', compact(
            'school', 'schoolId',
            'totalTeachers', 'totalStudents',
            'completionRate', 'totalAssigned', 'totalCompleted',
            'avgQuizScore', 'activeStudents',
            'activityTrend', 'topClasses', 'moreDataNeeded', 'recentTeachers',
            'sparkDates', 'sparkStudents', 'sparkLessons', 'sparkTeachers', 'sparkQuizScores',
            'programDonut', 'fslMasteryData',
            'activeSyName', 'targetSyName'
        ));
    }

    /**
     * Notify teachers in the Grade Leader's assigned school to transition to the new school year.
     * Prevents duplicate pending transition notifications for the same teacher & target school year.
     */
    public function notifyTransition(Request $request)
    {
        $schoolId = $this->schoolId();
        if (!$schoolId) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not linked to any school.',
            ], 403);
        }

        $activeSy = SchoolYear::activeForSchool($schoolId);
        $activeSyName = $activeSy ? $activeSy->name : SchoolYear::currentDepEdLabel();
        [$startY, $endY] = explode('-', $activeSyName);
        $targetSy = ((int)$endY) . '-' . (((int)$endY) + 1);

        $all = $request->boolean('all');
        $teacherId = $request->input('teacher_id');

        $teacherQuery = Teacher::where('school_id', $schoolId)
            ->whereHas('user', fn ($q) => $q->where('role', 'teacher')->where('is_system', false));

        if (!$all && $teacherId) {
            $teacherQuery->where('id', $teacherId);
        }

        $teachers = $teacherQuery->get();

        if ($teachers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No eligible teachers found in your school.',
            ], 404);
        }

        $notifiedCount = 0;
        $alreadyPendingCount = 0;
        $currentUser = Auth::user();
        $leaderName  = $currentUser ? ($currentUser->teacher?->first_name . ' ' . $currentUser->teacher?->last_name) : 'Grade Leader';

        foreach ($teachers as $teacher) {
            // Check for existing pending notification for this teacher & target school year
            $existingPending = TeacherNotification::where('teacher_id', $teacher->id)
                ->where('type', 'new_school_year')
                ->where('action_status', 'pending')
                ->whereJsonContains('data->target_school_year', $targetSy)
                ->exists();

            if ($existingPending) {
                $alreadyPendingCount++;
                continue;
            }

            TeacherNotification::create([
                'teacher_id'    => $teacher->id,
                'type'          => 'new_school_year',
                'title'         => 'New School Year Transition: ' . $targetSy,
                'message'       => 'Grade Leader ' . $leaderName . ' has notified your school to begin transition to School Year ' . $targetSy . '. Please review and confirm your classroom transition.',
                'action_status' => 'pending',
                'action_url'    => route('notifications.index'),
                'data'          => [
                    'from_school_year'   => $activeSyName,
                    'target_school_year' => $targetSy,
                    'initiated_by_user'  => Auth::id(),
                    'initiated_by_name'  => $leaderName,
                ],
                'icon'          => 'calendar_month',
                'color'         => '#4F46E5',
                'is_read'       => false,
            ]);

            AuditLog::record(
                action:      'SCHOOL_YEAR_TRANSITION_NOTIFIED',
                module:      'school_year',
                description: "Grade Leader {$leaderName} notified teacher {$teacher->first_name} {$teacher->last_name} to transition to School Year {$targetSy}.",
                userId:      Auth::id(),
                userName:    $leaderName,
                userRole:    'grade_leader',
                subjectType: Teacher::class,
                subjectId:   $teacher->id,
                newValues:   [
                    'target_school_year' => $targetSy,
                    'from_school_year'   => $activeSyName,
                ]
            );

            $notifiedCount++;
        }

        $message = $notifiedCount > 0
            ? "Successfully notified {$notifiedCount} teacher(s) for School Year {$targetSy}."
            : "The selected teacher(s) already have a pending transition notification for School Year {$targetSy}.";

        return response()->json([
            'success'         => true,
            'notified_count'  => $notifiedCount,
            'skipped_count'   => $alreadyPendingCount,
            'target_year'     => $targetSy,
            'message'         => $message,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. DEFAULT LESSONS — Read-only curriculum catalog
    // ─────────────────────────────────────────────────────────────────────────

    public function lessons(Request $request)
    {
        $systemTeacherId = app(LessonTemplateService::class)->templateTeacherId();

        $modules = Module::where('teacher_id', $systemTeacherId)
            ->where('is_template', true)
            ->with([
                'lessons' => function ($q) {
                    $q->whereNull('deleted_at')
                      ->orderBy('module_order')
                      ->with('quiz.questions.options', 'contents');
                },
                'checkpointExams' => function ($q) {
                    $q->with('questions')->orderBy('created_at', 'desc');
                },
            ])
            ->orderBy('module_order')
            ->get();

        $totalLessons  = $modules->sum(fn ($m) => $m->lessons->count());
        $totalModules  = $modules->count();

        return view('grade-leader.lessons', compact('modules', 'totalLessons', 'totalModules'));
    }

    /**
     * Return the rendered lessons.preview partial for the fullscreen overlay modal
     * — same data shape as LessonsController::previewModal so the same blade view is reused.
     */
    public function lessonPreviewPage(int $lessonId)
    {
        $systemTeacherId = app(LessonTemplateService::class)->templateTeacherId();

        $lesson = \App\Models\Lesson::with(['contents', 'quiz.questions.options'])
            ->where('teacher_id', $systemTeacherId)
            ->where('is_template', true)
            ->whereNull('deleted_at')
            ->findOrFail($lessonId);

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
                $gestureIds       = $gestureData['gesture_ids']        ?? [];
                $isFingerspelling = $gestureData['is_fingerspelling']   ?? false;
                $words            = $gestureData['words']               ?? [];

                // Normalize drag-drop pairs
                $pairs = $question->drag_drop_pairs ?? [];
                if (is_string($pairs)) { $pairs = json_decode($pairs, true) ?? []; }
                $normalizedPairs = [];
                foreach (array_values((array) $pairs) as $idx => $pair) {
                    if (!is_array($pair)) { continue; }
                    $lt = $pair['left_text']  ?? $pair['left']  ?? '';
                    $rt = $pair['right_text'] ?? $pair['right'] ?? '';
                    $li = $pair['left_image']  ?? '';
                    $ri = $pair['right_image'] ?? '';
                    if (trim((string)$lt) === '' && trim((string)$rt) === '' && empty($li) && empty($ri)) { continue; }
                    $normalizedPairs[] = ['left_text'=>$lt,'right_text'=>$rt,'left_image'=>$li?:null,'right_image'=>$ri?:null,'match_id'=>$pair['match_id']??$idx];
                }

                // Gesture details
                $gestureDetails = [];
                $filteredIds = array_values(array_filter($gestureIds, fn($id) => $id !== null && $id !== ''));
                if (!empty($filteredIds)) {
                    $gestureDetails = \App\Models\Gesture::whereIn('gesture_id', $filteredIds)
                        ->get()
                        ->map(fn($g) => [
                            'id'        => $g->gesture_id,
                            'name'      => $g->display_name ?? $g->name,
                            'image_url' => $g->image_url,
                            'video_url' => $g->video_url,
                        ])
                        ->values()
                        ->toArray();
                }

                $lessonData['quiz'][] = [
                    'question'             => $question->question_text,
                    'type'                 => $question->question_type,
                    'media'                => $question->media_url,
                    'options'              => $question->options->map(fn($opt) => [
                        'text'  => $opt->option_text,
                        'image' => $opt->option_media_url,
                    ])->toArray(),
                    'correct'              => $question->options->search(fn($opt) => $opt->is_correct),
                    'drag_drop_pairs'      => $normalizedPairs,
                    'gesture_module_id'    => $gestureData['module_id'] ?? null,
                    'gesture_details'      => $gestureDetails,
                    'is_fingerspelling'    => $isFingerspelling,
                    'fingerspelling_words' => $words,
                ];
            }
        }

        $totalSlides = count($lessonData['contents']) + count($lessonData['quiz']);

        return response()->view('lessons.preview', compact('lessonData', 'totalSlides'));
    }

    /**
     * Read-only view for a default checkpoint exam.
     * GET /grade-leader/checkpoint-exam/{id}
     */
    public function showCheckpointExam($id)
    {
        $realId = \App\Support\UrlObfuscator::decode($id) ?? $id;
        $exam = CheckpointExam::with(['questions', 'module'])
            ->findOrFail($realId);

        // Format questions for display
        $questions = $exam->questions->map(function ($question) {
            $dragDropPairs = $question->drag_drop_pairs;
            if (is_string($dragDropPairs)) {
                $dragDropPairs = json_decode($dragDropPairs, true) ?? [];
            }
            if (!is_array($dragDropPairs)) {
                $dragDropPairs = [];
            }

            $gestureData = $question->gesture_data;
            if (is_string($gestureData)) {
                $gestureData = json_decode($gestureData, true) ?? [];
            }
            if (!is_array($gestureData)) {
                $gestureData = [];
            }

            $optionsData = $question->options_data;
            if (is_string($optionsData)) {
                $optionsData = json_decode($optionsData, true) ?? [];
            }
            if (!is_array($optionsData)) {
                $optionsData = [];
            }

            return [
                'question_id'     => $question->question_id,
                'question_number' => $question->question_number,
                'question_text'   => $question->question_text,
                'question_type'   => $question->question_type,
                'media_url'       => $question->media_url,
                'points'          => $question->points,
                'options'         => $optionsData,
                'drag_drop_pairs' => $dragDropPairs,
                'gesture_data'    => $gestureData,
                'correct_answer'  => $question->correct_answer,
            ];
        });

        return view('lessons.checkpoint-exam.show', compact('exam', 'questions'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. SYSTEM MEDIA — Read-only gallery (System Media only, no teacher uploads)
    // ─────────────────────────────────────────────────────────────────────────

    public function media(Request $request)
    {
        // 1. Strictly system media from gesture_media table (no teacher uploads)
        $systemMedia = GestureMedia::with(['gesture', 'module'])
            ->orderBy('order')
            ->orderBy('media_id')
            ->get();

        // Build unified collection
        $allMedia = $systemMedia->map(function ($item) {
            return [
                'id'         => 'sys_' . $item->media_id,
                'source'     => 'system',
                'title'      => $item->gesture ? ($item->gesture->display_name ?? $item->gesture->name) : ($item->display_name ?: $item->file_name),
                'file_name'  => $item->file_name,
                'file_path'  => $item->file_path,
                'url'        => asset('storage/' . $item->file_path),
                'media_type' => $this->resolveMediaType($item->mime_type, $item->media_type),
                'mime_type'  => $item->mime_type,
                'file_size'  => $item->file_size,
                'module'     => $item->module ? ($item->module->display_name ?? $item->module->name) : ($item->gesture?->module?->display_name ?? null),
                'owner'      => 'System',
                'created_at' => $item->created_at,
            ];
        });

        // Stats
        $stats = [
            'total'    => $allMedia->count(),
            'images'   => $allMedia->where('media_type', 'image')->count(),
            'videos'   => $allMedia->where('media_type', 'video')->count(),
            'gifs'     => $allMedia->where('media_type', 'gif')->count(),
        ];

        // Build JS-safe data for instant client-side filtering & preview
        $mediaJs = $allMedia->values()->map(function ($item, $index) {
            return [
                'index'       => $index,
                'id'          => $item['id'],
                'source'      => 'system',
                'title'       => $item['title'],
                'file_name'   => $item['file_name'],
                'url'         => $item['url'],
                'media_type'  => $item['media_type'],
                'mime_type'   => $item['mime_type'],
                'module'      => $item['module'],
                'owner'       => 'System',
                'created_at'  => $item['created_at']
                                    ? \Carbon\Carbon::parse($item['created_at'])->format('M j, Y')
                                    : null,
                'file_size'   => $item['file_size'] ? round($item['file_size'] / 1024, 1) . ' KB' : null,
            ];
        })->values()->toArray();

        return view('grade-leader.media', compact('allMedia', 'stats', 'mediaJs'));
    }

    private function resolveMediaType(?string $mimeType, string $dbType): string
    {
        if ($mimeType === 'image/gif') {
            return 'gif';
        }
        return $dbType;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. ANALYTICS — School Academic Insights
    // ─────────────────────────────────────────────────────────────────────────

    public function analytics(Request $request)
    {
        $schoolId   = $this->schoolId();
        $school     = Auth::user()->teacher->school ?? null;
        $teacherIds = $this->schoolTeacherIds($schoolId);
        $studentIds = $this->schoolStudentIds($schoolId);

        $period = $request->get('period', 'weekly');
        $year   = (int) $request->get('year', date('Y'));
        $month  = (int) $request->get('month', date('n'));

        // ── Date window ───────────────────────────────────────────────────────
        [$startDate, $endDate] = $this->buildDateWindow($period, $year, $month);

        // ── Top-level KPIs ────────────────────────────────────────────────────
        $avgQuizScore = round((float) DB::table('quiz_attempts')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->avg('percentage') ?? 0, 1);

        $quizPassRate = $studentIds->isNotEmpty()
            ? round((float) DB::table('quiz_attempts')
                ->whereIn('student_id', $studentIds)
                ->where('status', 'completed')
                ->where('percentage', '>=', 75)
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->count() /
              max(1, DB::table('quiz_attempts')
                ->whereIn('student_id', $studentIds)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->count()) * 100, 1)
            : 0;

        $completionRate = 0; // not used in new analytics layout

        $activeStudentsCount = Student::whereIn('student_id', $studentIds)
            ->whereBetween('last_activity_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->count();

        // ── CLASS PERFORMANCE: active students, quiz pass rate, checkpoint pass rate per teacher ──
        $classPerformance = Teacher::whereIn('id', $teacherIds)
            ->with('user')
            ->get()
            ->map(function ($teacher) use ($startDate, $endDate) {
                $tStudentIds = $teacher->students()->pluck('student_id');
                $total       = $tStudentIds->count();

                // Active: last_activity_date within period
                $active = Student::whereIn('student_id', $tStudentIds)
                    ->whereBetween('last_activity_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->count();

                // Quiz: total completed attempts & those that passed (≥75%)
                $quizTotal  = DB::table('quiz_attempts')
                    ->whereIn('student_id', $tStudentIds)
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$startDate, $endDate])
                    ->count();
                $quizPassed = DB::table('quiz_attempts')
                    ->whereIn('student_id', $tStudentIds)
                    ->where('status', 'completed')
                    ->where('percentage', '>=', 75)
                    ->whereBetween('completed_at', [$startDate, $endDate])
                    ->count();

                // Checkpoint exam: completed attempts & those that passed (≥75%)
                $ckTotal  = DB::table('checkpoint_exam_attempts')
                    ->whereIn('student_id', $tStudentIds)
                    ->whereIn('status', ['completed', 'failed'])
                    ->whereBetween('completed_at', [$startDate, $endDate])
                    ->count();
                $ckPassed = DB::table('checkpoint_exam_attempts')
                    ->whereIn('student_id', $tStudentIds)
                    ->where('status', 'completed')
                    ->where('percentage', '>=', 75)
                    ->whereBetween('completed_at', [$startDate, $endDate])
                    ->count();

                $avgScore = round((float) (DB::table('quiz_attempts')
                    ->whereIn('student_id', $tStudentIds)
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$startDate, $endDate])
                    ->avg('percentage') ?? 0), 1);

                return [
                    'teacher_id'        => $teacher->id,
                    'name'              => trim($teacher->first_name . ' ' . $teacher->last_name),
                    'avatar'            => $teacher->user?->avatarUrl() ?? '',
                    'total_students'    => $total,
                    'active_students'   => $active,
                    'inactive_students' => max(0, $total - $active),
                    'active_pct'        => $total > 0 ? round($active / $total * 100, 1) : 0,
                    'avg_quiz_score'    => $avgScore,
                    'quiz_total'        => $quizTotal,
                    'quiz_passed'       => $quizPassed,
                    'quiz_pass_rate'    => $quizTotal > 0 ? round($quizPassed / $quizTotal * 100, 1) : 0,
                    'ck_total'          => $ckTotal,
                    'ck_passed'         => $ckPassed,
                    'ck_pass_rate'      => $ckTotal > 0 ? round($ckPassed / $ckTotal * 100, 1) : 0,
                    'status'            => $avgScore >= 75 ? 'on_track' : ($avgScore >= 50 ? 'needs_attention' : 'needs_support'),
                ];
            })
            ->sortByDesc('avg_quiz_score')
            ->values();

        // ── ENROLLMENT BY DEPED SCHOOL YEAR ──────────────────────────────────
        // DepEd SY: July (month 7) of year Y to June (month 6) of year Y+1
        // e.g. July 2024 → June 2025 = "2024-2025"
        // Determine current DepEd school year based on today's month
        $nowMonth       = (int) Carbon::now()->format('n');
        $nowYear        = (int) Carbon::now()->format('Y');
        $currentDepEdSY = $nowMonth >= 7
            ? $nowYear . '-' . ($nowYear + 1)
            : ($nowYear - 1) . '-' . $nowYear;

        // Always show exactly 5 years ending at the current SY
        [$curStart] = explode('-', $currentDepEdSY);
        $curStart   = (int) $curStart;
        $allDepEdSYs = [];
        for ($offset = 4; $offset >= 0; $offset--) {
            $allDepEdSYs[] = ($curStart - $offset) . '-' . ($curStart - $offset + 1);
        }

        // Program types (canonical order)
        $programTypes = ['Regular', 'Inclusion', 'Transition', 'Self-contained'];

        // Count students per DepEd school year by program type.
        // Queries student_year_enrollments — an immutable log written at enrollment
        // time — so past-year counts are never lost when students.school_year is
        // overwritten during a school-year transition.
        $enrollmentBySY = [];
        foreach ($allDepEdSYs as $sy) {
            $byProgram = [];
            $total     = 0;
            foreach ($programTypes as $pt) {
                $cnt = StudentYearEnrollment::whereIn('student_id', $studentIds)
                    ->where('school_year_name', $sy)
                    ->where('program_type', $pt)
                    ->count();
                $byProgram[$pt] = $cnt;
                $total += $cnt;
            }
            $enrollmentBySY[$sy] = [
                'total'    => $total,
                'programs' => $byProgram,
            ];
        }

        // ── ACTIVE vs INACTIVE for current DepEd school year (pie chart) ─────
        // Only counts students whose school_year field is exactly the current SY.
        $activeInactivePie = [
            'school_year' => $currentDepEdSY,
            'active'      => Student::whereIn('student_id', $studentIds)
                ->where('school_year', $currentDepEdSY)
                ->where('status', 'active')
                ->count(),
            'inactive'    => Student::whereIn('student_id', $studentIds)
                ->where('school_year', $currentDepEdSY)
                ->whereIn('status', ['inactive', 'archived'])
                ->count(),
        ];
        $activeInactivePie['total'] = $activeInactivePie['active'] + $activeInactivePie['inactive'];

        return view('grade-leader.analytics', compact(
            'school', 'period', 'year', 'month',
            'startDate', 'endDate',
            'avgQuizScore', 'quizPassRate', 'completionRate', 'activeStudentsCount',
            'classPerformance',
            'enrollmentBySY', 'allDepEdSYs', 'currentDepEdSY', 'programTypes',
            'activeInactivePie'
        ));
    }

    /**
     * POST /grade-leader/analytics/filter
     * Store filters in session → redirect (PRG pattern).
     */
    public function analyticsFilter(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'string', 'in:weekly,monthly,quarterly,yearly'],
            'year'   => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month'  => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        if (empty(array_filter($validated))) {
            session()->forget('tl_analytics_filters');
        } else {
            session(['tl_analytics_filters' => $validated]);
        }

        return redirect()->route('grade-leader.analytics');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. REPORTS — Per-teacher class performance with per-student drill-down
    // ─────────────────────────────────────────────────────────────────────────

    public function reports(Request $request)
    {
        $schoolId   = $this->schoolId();
        $school     = Auth::user()->teacher->school ?? null;
        $teacherIds = $this->schoolTeacherIds($schoolId);

        // Filter: which teacher are we drilling into?
        $filterTeacherId = (int) $request->get('teacher_id', 0);
        if ($filterTeacherId && ! $teacherIds->contains($filterTeacherId)) {
            $filterTeacherId = 0; // out-of-school teacher — silently reset
        }

        // Load all teachers with basic student + quiz summary
        $teachers = Teacher::whereIn('id', $teacherIds)
            ->with(['user', 'students' => fn ($q) => $q->where('status', 'active')])
            ->get()
            ->map(function ($teacher) {
                $studentIds = $teacher->students->pluck('student_id');
                $totalStudents = $studentIds->count();

                $avgScore = round((float) DB::table('quiz_attempts')
                    ->whereIn('student_id', $studentIds)
                    ->where('status', 'completed')
                    ->avg('percentage') ?? 0, 1);

                $assigned  = DB::table('lesson_assignments')
                    ->whereIn('student_id', $studentIds)->count();
                $completed = DB::table('lesson_assignments')
                    ->whereIn('student_id', $studentIds)
                    ->where('status', 'completed')->count();
                $completionRate = $assigned > 0 ? round($completed / $assigned * 100, 1) : 0;

                $activeStudents = Student::whereIn('student_id', $studentIds)
                    ->where('last_activity_date', '>=', Carbon::now()->subDays(7))
                    ->count();

                return [
                    'teacher'         => $teacher,
                    'total_students'  => $totalStudents,
                    'avg_score'       => $avgScore,
                    'completion_rate' => $completionRate,
                    'active_students' => $activeStudents,
                    'status'          => $avgScore >= 75 ? 'on_track' : ($avgScore >= 50 ? 'needs_attention' : 'needs_support'),
                ];
            })
            ->sortByDesc('avg_score')
            ->values();

        // If drilling into a specific teacher, build the per-student report
        $selectedTeacher    = null;
        $studentReports     = collect();
        $lessons            = collect();
        $checkpointExams    = collect();

        if ($filterTeacherId) {
            $selectedTeacher = Teacher::with(['user', 'school'])->find($filterTeacherId);

            if ($selectedTeacher) {
                $teacherId  = $selectedTeacher->id;
                $students   = Student::where('teacher_id', $teacherId)
                    ->where('status', 'active')
                    ->orderBy('first_name')
                    ->get();

                $modules          = Module::where('teacher_id', $teacherId)->orderBy('module_order')->get();
                $teacherModuleIds = $modules->pluck('module_id');

                $lessons = Lesson::where('teacher_id', $teacherId)
                    ->where('status', 'published')
                    ->whereNull('deleted_at')
                    ->whereIn('module_id', $teacherModuleIds)
                    ->with(['module', 'quiz'])
                    ->orderBy('module_order')
                    ->get();

                $checkpointExams = CheckpointExam::where('teacher_id', $teacherId)
                    ->where('status', 'published')
                    ->whereIn('module_id', $teacherModuleIds)
                    ->with(['module', 'questions'])
                    ->orderBy('module_id')
                    ->get();

                $studentIds = $students->pluck('student_id');
                $lessonIds  = $lessons->pluck('lesson_id');
                $totalSteps = 7;

                // Bulk StudentLessonProgress
                $allRows = StudentLessonProgress::whereIn('student_id', $studentIds)
                    ->whereIn('lesson_id', $lessonIds)
                    ->get()
                    ->groupBy('student_id');

                // Bulk gesture performance
                $gestureByStudent = DB::table('gesture_performances as gp')
                    ->join('gestures as g', 'gp.gesture_id', '=', 'g.gesture_id')
                    ->whereIn('gp.student_id', $studentIds)
                    ->where('gp.attempts', '>', 0)
                    ->select('gp.student_id', 'g.name as g_name', 'g.display_name as g_display_name',
                        'gp.attempts', 'gp.successful_attempts', 'gp.wrong_attempts',
                        'gp.mastery_level', 'gp.is_mastered', 'gp.last_attempt_at')
                    ->get()
                    ->map(fn ($row) => [
                        'student_id'         => $row->student_id,
                        'gestureName'        => $row->g_display_name ?: $row->g_name,
                        'attempts'           => (int) $row->attempts,
                        'successfulAttempts' => (int) $row->successful_attempts,
                        'wrongAttempts'      => (int) $row->wrong_attempts,
                        'accuracy'           => $row->attempts > 0 ? round($row->successful_attempts / $row->attempts * 100, 1) : 0,
                        'masteryLevel'       => $row->mastery_level ?? 'needs_practice',
                        'isMastered'         => (bool) $row->is_mastered,
                        'lastAttemptAt'      => $row->last_attempt_at ? Carbon::parse($row->last_attempt_at)->format('M d, Y') : '—',
                    ])
                    ->groupBy('student_id');

                // Bulk checkpoint data
                $checkpointAssignments = DB::table('checkpoint_exam_assignments')
                    ->whereIn('student_id', $studentIds)
                    ->whereIn('exam_id', $checkpointExams->pluck('exam_id'))
                    ->get()->groupBy('student_id');

                $checkpointAttempts = DB::table('checkpoint_exam_attempts')
                    ->whereIn('student_id', $studentIds)
                    ->whereIn('exam_id', $checkpointExams->pluck('exam_id'))
                    ->whereIn('status', ['completed', 'failed'])
                    ->get()->groupBy('student_id');

                $studentReports = $students->map(function ($student) use (
                    $allRows, $lessons, $checkpointExams,
                    $gestureByStudent, $checkpointAssignments, $checkpointAttempts, $totalSteps
                ) {
                    $rows        = $allRows->get($student->student_id) ?? collect();
                    $progressMap = $rows->keyBy('lesson_id');

                    // Lesson breakdown
                    $lessonBreakdown = $lessons->map(function ($lesson) use ($progressMap, $totalSteps) {
                        $row     = $progressMap->get($lesson->lesson_id);
                        $started = $row !== null;
                        return [
                            'lessonTitle'   => $lesson->title,
                            'difficulty'    => $lesson->difficulty ?? '—',
                            'lessonType'    => $lesson->lesson_type ?? '',
                            'is_exam'       => false,
                            'moduleTitle'   => $lesson->module?->title ?? 'Unassigned',
                            'module_id'     => $lesson->module_id,
                            'ai_generated'  => (bool) $lesson->ai_generated,
                            'started'       => $started,
                            'stepPct'       => $started && $totalSteps > 0 ? min(100, round($row->current_step / $totalSteps * 100)) : 0,
                            'completed'     => $started && (bool) $row->lesson_completed,
                            'quizCompleted' => $started && (bool) $row->quiz_completed,
                            'quizScore'     => $started ? $row->quiz_score : null,
                            'lastAccessed'  => $started && $row->last_accessed_at ? Carbon::parse($row->last_accessed_at)->diffForHumans() : '—',
                        ];
                    })->values();

                    // Checkpoint breakdown
                    $stAssignments = $checkpointAssignments->get($student->student_id) ?? collect();
                    $stAttempts    = $checkpointAttempts->get($student->student_id) ?? collect();

                    $checkpointBreakdown = $checkpointExams->map(function ($exam) use ($stAssignments, $stAttempts) {
                        $assign      = $stAssignments->firstWhere('exam_id', $exam->exam_id);
                        $examAttempts = $stAttempts->where('exam_id', $exam->exam_id);
                        $best        = $examAttempts->sortByDesc('percentage')->first();
                        $latest      = $examAttempts->sortByDesc('completed_at')->first();
                        $started     = ($assign && $assign->status !== 'pending') || $examAttempts->isNotEmpty();
                        $completed   = ($assign && $assign->status === 'completed') || ($best && $best->percentage >= ($exam->passing_score ?? 60));
                        $score       = $best ? round($best->percentage, 1) : ($assign?->score !== null ? round($assign->score, 1) : null);
                        $last        = $latest?->completed_at ? Carbon::parse($latest->completed_at)->diffForHumans() : ($assign?->updated_at ? Carbon::parse($assign->updated_at)->diffForHumans() : '—');
                        return [
                            'lessonTitle'   => $exam->title,
                            'difficulty'    => 'Exam',
                            'lessonType'    => 'checkpoint_exam',
                            'is_exam'       => true,
                            'exam_id'       => $exam->exam_id,
                            'moduleTitle'   => $exam->module?->title ?? 'Unassigned',
                            'module_id'     => $exam->module_id,
                            'ai_generated'  => false,
                            'started'       => $started,
                            'stepPct'       => $completed ? 100 : ($started ? 50 : 0),
                            'completed'     => $completed,
                            'failed'        => !$completed && $examAttempts->isNotEmpty(),
                            'quizCompleted' => $best !== null,
                            'quizScore'     => $score,
                            'lastAccessed'  => $last,
                        ];
                    })->values();

                    $allContent   = $lessonBreakdown->concat($checkpointBreakdown);
                    $totalLessons = $lessons->count() + $checkpointExams->count();
                    $doneCount    = $rows->where('lesson_completed', 1)->count() + $checkpointBreakdown->where('completed', true)->count();
                    $quizDone     = $rows->where('quiz_completed', 1)->count() + $checkpointBreakdown->where('quizCompleted', true)->count();
                    $avgScore     = $quizDone > 0 ? round(($rows->where('quiz_completed', 1)->sum('quiz_score') + $checkpointBreakdown->where('quizCompleted', true)->whereNotNull('quizScore')->sum('quizScore')) / $quizDone, 1) : 0;
                    $overallPct   = $totalLessons > 0 ? round($doneCount / $totalLessons * 100) : 0;

                    $gRows       = $gestureByStudent->get($student->student_id) ?? collect();
                    $totAttempts = (int) $gRows->sum('attempts');
                    $totSuccess  = (int) $gRows->sum('successfulAttempts');
                    $gAccuracy   = $totAttempts > 0 ? round($totSuccess / $totAttempts * 100, 1) : 0;

                    return [
                        'student_id'       => $student->student_id,
                        'studentName'      => trim($student->first_name . ' ' . $student->last_name),
                        'gradeLevel'       => $student->grade_level ?? 'N/A',
                        'initials'         => $student->initials,
                        'avatar_url'       => $student->avatarUrl(),
                        'totalLessons'     => $totalLessons,
                        'completedLessons' => $doneCount,
                        'quizzesTaken'     => $quizDone,
                        'quizzesPassed'    => $checkpointBreakdown->where('completed', true)->count() + $rows->where('quiz_completed', 1)->count(),
                        'quizPassRate'     => $quizDone > 0 ? round($doneCount / $quizDone * 100, 1) : 0,
                        'avgScore'         => $avgScore,
                        'overallPct'       => $overallPct,
                        'fslMasteryLevel'  => $student->fsl_mastery_level ?? 'Beginner',
                        'gestureAccuracy'  => $gAccuracy,
                        'gestureAttempts'  => $totAttempts,
                        'gestureSuccess'   => $totSuccess,
                        'gestureWrong'     => (int) $gRows->sum('wrongAttempts'),
                        'gesturesMastered' => (int) $gRows->where('isMastered', true)->count(),
                        'gestureBreakdown' => $gRows->values(),
                        'lastAccessed'     => $rows->isNotEmpty() ? Carbon::parse($rows->sortByDesc('last_accessed_at')->first()->last_accessed_at)->diffForHumans() : '—',
                        'lessons'          => $allContent,
                    ];
                })
                ->sortBy('studentName')
                ->values();
            }
        }

        return view('grade-leader.reports', compact(
            'school', 'teachers',
            'filterTeacherId', 'selectedTeacher',
            'studentReports', 'lessons', 'checkpointExams'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function buildDateWindow(string $period, int $year, int $month): array
    {
        if ($period === 'monthly') {
            return [
                Carbon::create($year, $month, 1)->startOfMonth()->startOfDay(),
                Carbon::create($year, $month, 1)->endOfMonth()->endOfDay(),
            ];
        }
        if ($period === 'quarterly') {
            $qStartMonth = (int) (ceil($month / 3) - 1) * 3 + 1;
            return [
                Carbon::create($year, $qStartMonth, 1)->startOfMonth()->startOfDay(),
                Carbon::create($year, $qStartMonth + 2, 1)->endOfMonth()->endOfDay(),
            ];
        }
        if ($period === 'yearly') {
            return [
                Carbon::create($year, 1, 1)->startOfYear()->startOfDay(),
                Carbon::create($year, 12, 31)->endOfYear()->endOfDay(),
            ];
        }
        // Default: weekly — last 8 weeks
        return [
            Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(7)->startOfDay(),
            Carbon::now()->endOfWeek(Carbon::SUNDAY)->endOfDay(),
        ];
    }

    private function buildCompletionTrend(string $period, int $year, int $month, $studentIds, Carbon $start, Carbon $end): array
    {
        $trend = [];

        if ($period === 'weekly') {
            for ($w = 7; $w >= 0; $w--) {
                $ws = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks($w);
                $we = $ws->copy()->endOfWeek(Carbon::SUNDAY);
                $trend[] = [
                    'label' => $ws->format('M d'),
                    'count' => DB::table('lesson_assignments')
                        ->whereIn('student_id', $studentIds)
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$ws->startOfDay(), $we->endOfDay()])
                        ->count(),
                ];
            }
        } elseif ($period === 'monthly') {
            $mStart = Carbon::create($year, $month, 1)->startOfMonth();
            for ($i = 0; $i < 4; $i++) {
                $ds = $mStart->copy()->addDays($i * 7);
                $de = $i === 3 ? $mStart->copy()->endOfMonth() : $mStart->copy()->addDays(($i + 1) * 7 - 1);
                $trend[] = [
                    'label' => 'Week ' . ($i + 1),
                    'count' => DB::table('lesson_assignments')
                        ->whereIn('student_id', $studentIds)
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$ds->startOfDay(), $de->endOfDay()])
                        ->count(),
                ];
            }
        } elseif ($period === 'quarterly') {
            for ($m = 0; $m < 3; $m++) {
                $mDate = $start->copy()->addMonths($m);
                $trend[] = [
                    'label' => $mDate->format('M Y'),
                    'count' => DB::table('lesson_assignments')
                        ->whereIn('student_id', $studentIds)
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$mDate->copy()->startOfMonth(), $mDate->copy()->endOfMonth()])
                        ->count(),
                ];
            }
        } else { // yearly
            for ($m = 1; $m <= 12; $m++) {
                $mDate = Carbon::create($year, $m, 1);
                $trend[] = [
                    'label' => $mDate->format('M'),
                    'count' => DB::table('lesson_assignments')
                        ->whereIn('student_id', $studentIds)
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$mDate->copy()->startOfMonth(), $mDate->copy()->endOfMonth()])
                        ->count(),
                ];
            }
        }

        return $trend;
    }
}
