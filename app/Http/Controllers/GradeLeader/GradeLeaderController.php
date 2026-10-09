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

        // Keep every student and learning metric on this dashboard aligned to
        // the school's active academic year.
        $activeSy = SchoolYear::activeForSchool($schoolId);
        $activeSyName = $activeSy ? $activeSy->name : SchoolYear::currentDepEdLabel();
        $activeSyId = $activeSy?->id ?? SchoolYear::where('school_id', $schoolId)
            ->where('name', $activeSyName)
            ->value('id');
        $activeSyStartDate = $activeSy?->start_date
            ?? Carbon::createFromDate((int) explode('-', $activeSyName)[0], 7, 1);

        $teacherIds = $this->schoolTeacherIds($schoolId);
        $studentIds = Student::where('school_id', $schoolId)
            ->where('school_year', $activeSyName)
            ->where('is_enrolled', true)
            ->pluck('student_id');
        $currentYearTeacherIds = Student::whereIn('student_id', $studentIds)
            ->whereNotNull('teacher_id')
            ->distinct()
            ->pluck('teacher_id');
        $dashboardTeacherIds = $teacherIds->intersect($currentYearTeacherIds)->values();

        // ── KPI Counts ───────────────────────────────────────────────────────
        $activeClassrooms = $dashboardTeacherIds->count();
        $totalTeachers = $teacherIds->count();
        $totalStudents = $studentIds->count();

        // Lesson completion rate for the school
        $lessonTotals = DB::table('lesson_assignments')
            ->whereIn('student_id', $studentIds)
            ->where('school_year_id', $activeSyId)
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
            ->where('school_year_id', $activeSyId)
            ->where('status', 'completed')
            ->avg('percentage') ?? 0, 1);
        $completedQuizAttempts = DB::table('quiz_attempts')
            ->whereIn('student_id', $studentIds)
            ->where('school_year_id', $activeSyId)
            ->where('status', 'completed')
            ->count();

        // Active students in last 7 days
        $activeStudents = DB::table('lesson_assignments')
            ->whereIn('student_id', $studentIds)
            ->where('school_year_id', $activeSyId)
            ->where('updated_at', '>=', Carbon::now()->subDays(7))
            ->distinct()
            ->count('student_id');

        // ── 14-Day Activity Trend ─────────────────────────────────────────────
        $activityTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $completionsCount = DB::table('lesson_assignments')
                ->whereIn('student_id', $studentIds)
                ->where('school_year_id', $activeSyId)
                ->where('status', 'completed')
                ->whereDate('updated_at', $date)
                ->count();

            $activeStudentsOnDay = DB::table('lesson_assignments')
                ->whereIn('student_id', $studentIds)
                ->where('school_year_id', $activeSyId)
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
        $topClasses = Teacher::whereIn('id', $dashboardTeacherIds)
            ->with('user')
            ->get()
            ->map(function ($teacher) use ($activeSyName, $activeSyId) {
                $studentIds = Student::where('teacher_id', $teacher->id)
                    ->where('school_year', $activeSyName)
                    ->where('is_enrolled', true)
                    ->pluck('student_id');
                $avg = DB::table('quiz_attempts')
                    ->whereIn('student_id', $studentIds)
                    ->where('school_year_id', $activeSyId)
                    ->where('status', 'completed')
                    ->avg('percentage') ?? 0;
                return [
                    'teacher'       => $teacher,
                    'avg_score'     => round((float) $avg, 1),
                    'student_count' => $studentIds->count(),
                ];
            })
            ->sortByDesc('avg_score')
            ->values()
            ->take(5);

        // ── More Data Needed (teachers with no quiz attempts yet) ────────────
        $moreDataNeeded = Teacher::whereIn('id', $dashboardTeacherIds)
            ->with('user')
            ->get()
            ->map(function ($teacher) use ($activeSyName, $activeSyId) {
                $tStudentIds = Student::where('teacher_id', $teacher->id)
                    ->where('school_year', $activeSyName)
                    ->where('is_enrolled', true)
                    ->pluck('student_id');
                $attemptCount = DB::table('quiz_attempts')
                    ->whereIn('student_id', $tStudentIds)
                    ->where('school_year_id', $activeSyId)
                    ->where('status', 'completed')
                    ->count();
                $assignedCount = DB::table('lesson_assignments')
                    ->whereIn('student_id', $tStudentIds)
                    ->where('school_year_id', $activeSyId)
                    ->count();
                return [
                    'teacher'        => $teacher,
                    'student_count'  => $tStudentIds->count(),
                    'attempt_count'  => $attemptCount,
                    'assigned_count' => $assignedCount,
                ];
            })
            ->filter(fn ($c) => $c['student_count'] > 0 && $c['attempt_count'] === 0)
            ->sortByDesc('student_count')
            ->values()
            ->take(5);

        // ── School Year Transition Context ──────────────────────────────────
        [$startY, $endY] = explode('-', $activeSyName);
        $targetSyName = ((int)$endY) . '-' . (((int)$endY) + 1);

        $transitionNotifs = TeacherNotification::whereIn('teacher_id', $teacherIds)
            ->where('type', 'new_school_year')
            ->orderByDesc('id')
            ->get()
            ->groupBy('teacher_id');

        // ── Recently active teachers in the school ───────────────────────────
        $recentTeachers = Teacher::whereIn('id', $dashboardTeacherIds)
            ->with('user')
            ->get()
            ->map(function ($teacher) use ($studentIds, $transitionNotifs, $activeSyName, $activeSyId) {
                // Per-teacher stats
                $tStudentIds = Student::where('teacher_id', $teacher->id)
                    ->where('school_year', $activeSyName)
                    ->where('is_enrolled', true)
                    ->pluck('student_id');
                $avgScore = DB::table('quiz_attempts')
                    ->whereIn('student_id', $tStudentIds)
                    ->where('school_year_id', $activeSyId)
                    ->where('status', 'completed')
                    ->avg('percentage') ?? 0;
                $completedLessons = DB::table('lesson_assignments')
                    ->whereIn('student_id', $tStudentIds)
                    ->where('school_year_id', $activeSyId)
                    ->where('status', 'completed')
                    ->count();
                $activeStudentsCount = DB::table('lesson_assignments')
                    ->whereIn('student_id', $tStudentIds)
                    ->where('school_year_id', $activeSyId)
                    ->where('updated_at', '>=', Carbon::now()->subDays(7))
                    ->distinct()
                    ->count('student_id');

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
            $sparkStudents[] = DB::table('lesson_assignments')->whereIn('student_id', $studentIds)->where('school_year_id', $activeSyId)->whereDate('updated_at', $date)->distinct()->count('student_id');
            $sparkLessons[]  = DB::table('lesson_assignments')->whereIn('student_id', $studentIds)->where('school_year_id', $activeSyId)->where('status', 'completed')->whereDate('updated_at', $date)->count();
            $sparkTeachers[] = User::whereIn('id', Teacher::whereIn('id', $dashboardTeacherIds)->pluck('user_id'))->whereDate('updated_at', '<=', $date)->count();
            $dayAvg = DB::table('quiz_attempts')
                ->whereIn('student_id', $studentIds)
                ->where('school_year_id', $activeSyId)
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
            'totalTeachers', 'activeClassrooms', 'totalStudents',
            'completionRate', 'totalAssigned', 'totalCompleted',
            'avgQuizScore', 'completedQuizAttempts', 'activeStudents',
            'activityTrend', 'topClasses', 'moreDataNeeded', 'recentTeachers',
            'sparkDates', 'sparkStudents', 'sparkLessons', 'sparkTeachers', 'sparkQuizScores',
            'programDonut', 'fslMasteryData',
            'activeSyName', 'activeSyStartDate', 'targetSyName'
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
        if ($request->boolean('reset_filters')) {
            session()->forget('grade_leader_analytics_filters');
            return redirect()->route('grade-leader.analytics');
        }

        $schoolId   = $this->schoolId();
        $school     = Auth::user()->teacher->school ?? null;
        $teacherIds = $this->schoolTeacherIds($schoolId);
        $filters = session('grade_leader_analytics_filters', []);
        if (array_key_exists('period', $filters) || array_key_exists('year', $filters)) {
            session()->forget('grade_leader_analytics_filters');
            $filters = [];
        }
        $request->merge($filters);

        $availableSchoolYears = SchoolYear::where('school_id', $schoolId)->orderByDesc('name')->get();
        $activeSchoolYear = $availableSchoolYears->firstWhere('status', 'active') ?? $availableSchoolYears->first();
        $selectedYearName = $request->get('school_year') ?: $activeSchoolYear?->name;
        $selectedSchoolYear = $availableSchoolYears->firstWhere('name', $selectedYearName) ?? $activeSchoolYear;
        $selectedMonth = (string) $request->get('month', 'all');
        if (!in_array($selectedMonth, ['all', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'], true)) {
            $selectedMonth = 'all';
        }

        $selectedSchoolYearName = $selectedSchoolYear?->name ?? SchoolYear::currentDepEdLabel();
        if (preg_match('/^(\d{4})-(\d{4})$/', $selectedSchoolYearName, $schoolYearParts)) {
            $schoolYearStart = (int) $schoolYearParts[1];
            $schoolYearEnd = (int) $schoolYearParts[2];
        } else {
            $schoolYearStart = (int) date('Y');
            $schoolYearEnd = $schoolYearStart + 1;
        }
        $monthOptions = [['value' => 'all', 'label' => 'All Months']];
        foreach (array_merge(array_map(fn ($m) => [$m, $schoolYearStart], range(7, 12)), array_map(fn ($m) => [$m, $schoolYearEnd], range(1, 6))) as [$monthNumber, $calendarYear]) {
            $monthOptions[] = [
                'value' => (string) $monthNumber,
                'label' => Carbon::create($calendarYear, $monthNumber, 1)->format('F Y'),
            ];
        }

        $year = $selectedMonth === 'all' ? $schoolYearStart : ((int) $selectedMonth >= 7 ? $schoolYearStart : $schoolYearEnd);
        $month = $selectedMonth === 'all' ? null : (int) $selectedMonth;
        $period = $month === null ? 'yearly' : 'monthly';
        $periodDescription = $month === null
            ? 'S.Y. ' . $selectedSchoolYearName
            : Carbon::create($year, $month, 1)->format('F Y');
        if ($month === null) {
            $startDate = Carbon::create($schoolYearStart, 7, 1)->startOfDay();
            $endDate = Carbon::create($schoolYearEnd, 6, 30)->endOfDay();
        } else {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth()->startOfDay();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
        }

        if ($selectedSchoolYear?->status === 'active') {
            $studentIds = Student::where('school_id', $schoolId)
                ->where('school_year', $selectedSchoolYearName)
                ->where('is_enrolled', true)
                ->pluck('student_id')->unique()->values();
        } elseif ($selectedSchoolYear) {
            $studentIds = StudentYearEnrollment::where('school_id', $schoolId)
                ->where('school_year_name', $selectedSchoolYearName)
                ->pluck('student_id')->unique()->values();
        } else {
            $studentIds = collect();
        }

        // ── Date window ───────────────────────────────────────────────────────

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

        $activityStudentIds = StudentLessonProgress::whereIn('student_id', $studentIds)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('last_accessed_at', [$startDate, $endDate])
                    ->orWhereBetween('updated_at', [$startDate, $endDate]);
            })
            ->pluck('student_id')
            ->merge(DB::table('quiz_attempts')->whereIn('student_id', $studentIds)->where('status', 'completed')->whereBetween('completed_at', [$startDate, $endDate])->pluck('student_id'))
            ->merge(DB::table('lesson_assignments')
                ->whereIn('student_id', $studentIds)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('assigned_at', [$startDate, $endDate])
                        ->orWhereBetween('completed_at', [$startDate, $endDate])
                        ->orWhereBetween('updated_at', [$startDate, $endDate]);
                })
                ->pluck('student_id'))
            ->merge(DB::table('gesture_performances')
                ->whereIn('student_id', $studentIds)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('last_attempt_at', [$startDate, $endDate])
                        ->orWhereBetween('updated_at', [$startDate, $endDate])
                        ->orWhereBetween('created_at', [$startDate, $endDate]);
                })
                ->pluck('student_id'))
            ->merge(DB::table('checkpoint_exam_attempts')
                ->whereIn('student_id', $studentIds)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('completed_at', [$startDate, $endDate])
                        ->orWhereBetween('updated_at', [$startDate, $endDate]);
                })
                ->pluck('student_id'))
            ->unique()->values();
        $activeStudentsCount = $activityStudentIds->count();

        // ── CLASS PERFORMANCE: active students, quiz pass rate, checkpoint pass rate per teacher ──
        $classPerformance = Teacher::whereIn('id', $teacherIds)
            ->with('user')
            ->get()
            ->map(function ($teacher) use ($studentIds, $activityStudentIds, $startDate, $endDate, $selectedSchoolYear) {
                $tStudentIds = $selectedSchoolYear?->status === 'active'
                    ? Student::whereIn('student_id', $studentIds)
                        ->where('teacher_id', $teacher->id)
                        ->pluck('student_id')
                    : StudentYearEnrollment::whereIn('student_id', $studentIds)
                        ->where('teacher_id', $teacher->id)
                        ->where('school_year_name', $selectedSchoolYear?->name)
                        ->pluck('student_id');
                $total       = $tStudentIds->count();

                $active = $activityStudentIds->intersect($tStudentIds)->count();

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
            ->filter(fn ($class) => $class['total_students'] > 0)
            ->values();

        // ── ENROLLMENT BY DEPED SCHOOL YEAR ──────────────────────────────────
        // DepEd SY: July (month 7) of year Y to June (month 6) of year Y+1
        // e.g. July 2024 → June 2025 = "2024-2025"
        // Anchor the enrollment history to the selected academic year.
        $currentDepEdSY = $selectedSchoolYearName;

        // Show five years ending at the selected school year.
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
                $cnt = StudentYearEnrollment::where('school_id', $schoolId)
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

        // Active/inactive is period-scoped, matching the selected year/month filters.
        $activeInactivePie = [
            'school_year' => $selectedSchoolYearName,
            'active'      => $activeStudentsCount,
            'inactive'    => max(0, $studentIds->count() - $activeStudentsCount),
        ];
        $activeInactivePie['total'] = $studentIds->count();

        $fslMasteryCounts = [
            'Beginner' => 0,
            'Intermediate' => 0,
            'Advanced' => 0,
            'Not recorded' => 0,
        ];
        $masteryValues = Student::whereIn('student_id', $studentIds)->pluck('fsl_mastery_level');
        foreach ($masteryValues as $masteryValue) {
            $masteryKey = match (strtolower(trim((string) $masteryValue))) {
                'beginner' => 'Beginner',
                'intermediate' => 'Intermediate',
                'advanced' => 'Advanced',
                default => 'Not recorded',
            };
            $fslMasteryCounts[$masteryKey]++;
        }
        $fslMasteryCounts['Not recorded'] = max(0, $studentIds->count() - $fslMasteryCounts['Beginner'] - $fslMasteryCounts['Intermediate'] - $fslMasteryCounts['Advanced']);
        $fslMasteryDonut = collect($fslMasteryCounts)->map(fn ($count, $label) => [
            'label' => $label,
            'count' => $count,
        ])->values();

        return view('grade-leader.analytics', compact(
            'school', 'period', 'periodDescription', 'year', 'month',
            'availableSchoolYears', 'selectedSchoolYear', 'activeSchoolYear', 'selectedMonth', 'monthOptions',
            'startDate', 'endDate',
            'avgQuizScore', 'quizPassRate', 'completionRate', 'activeStudentsCount',
            'classPerformance',
            'enrollmentBySY', 'allDepEdSYs', 'currentDepEdSY', 'programTypes',
            'activeInactivePie', 'fslMasteryDonut'
        ));
    }

    /**
     * POST /grade-leader/analytics/filter
     * Store filters in session → redirect (PRG pattern).
     */
    public function analyticsFilter(Request $request)
    {
        $validated = $request->validate([
            'school_year' => ['required', 'string', 'max:20'],
            'month'       => ['required', 'string', 'in:all,1,2,3,4,5,6,7,8,9,10,11,12'],
        ]);

        $schoolId = $this->schoolId();
        abort_unless(SchoolYear::where('school_id', $schoolId)->where('name', $validated['school_year'])->exists(), 422);
        session(['grade_leader_analytics_filters' => $validated]);

        return redirect()->route('grade-leader.analytics');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. REPORTS — Per-teacher class performance with per-student drill-down
    // ─────────────────────────────────────────────────────────────────────────

    public function reports(Request $request)
    {
        $schoolId = $this->schoolId();
        $school = Auth::user()->teacher->school ?? null;
        $teacherIds = $this->schoolTeacherIds($schoolId);
        $activeSchoolYear = SchoolYear::where('school_id', $schoolId)
            ->where('status', 'active')
            ->orderByDesc('name')
            ->first() ?? SchoolYear::where('school_id', $schoolId)->orderByDesc('name')->first();
        $availableSchoolYears = SchoolYear::where('school_id', $schoolId)->orderByDesc('name')->get();
        $selectedYearName = (string) $request->query('school_year', $activeSchoolYear?->name ?? SchoolYear::currentDepEdLabel());
        $selectedSchoolYear = $availableSchoolYears->firstWhere('name', $selectedYearName) ?? $activeSchoolYear;
        $schoolYearName = $selectedSchoolYear?->name ?? SchoolYear::currentDepEdLabel();
        $selectedMonth = (string) $request->query('month', 'all');
        if (!in_array($selectedMonth, ['all', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'], true)) {
            $selectedMonth = 'all';
        }
        $yearParts = preg_match('/^(\d{4})-(\d{4})$/', $schoolYearName, $matches)
            ? [(int) $matches[1], (int) $matches[2]]
            : [(int) date('Y'), (int) date('Y') + 1];
        $monthOptions = [['value' => 'all', 'label' => 'All Months']];
        foreach (array_merge(array_map(fn ($m) => [$m, $yearParts[0]], range(7, 12)), array_map(fn ($m) => [$m, $yearParts[1]], range(1, 6))) as [$monthNumber, $calendarYear]) {
            $monthOptions[] = [
                'value' => (string) $monthNumber,
                'label' => Carbon::create($calendarYear, $monthNumber, 1)->format('F Y'),
            ];
        }
        if ($selectedMonth === 'all') {
            $startDate = Carbon::create($yearParts[0], 7, 1)->startOfDay();
            $endDate = Carbon::create($yearParts[1], 6, 30)->endOfDay();
        } else {
            $monthYear = (int) $selectedMonth >= 7 ? $yearParts[0] : $yearParts[1];
            $startDate = Carbon::create($monthYear, (int) $selectedMonth, 1)->startOfMonth()->startOfDay();
            $endDate = Carbon::create($monthYear, (int) $selectedMonth, 1)->endOfMonth()->endOfDay();
        }
        $activeSince = Carbon::now()->subDays(7)->startOfDay()->toDateString();

        $teachers = Teacher::whereIn('id', $teacherIds)
            ->with('user')
            ->get()
            ->map(function ($teacher) use ($schoolId, $schoolYearName, $selectedSchoolYear, $startDate, $endDate, $activeSince) {
                if ($selectedSchoolYear?->status === 'active') {
                    $studentIds = Student::where('school_id', $schoolId)
                        ->where('teacher_id', $teacher->id)
                        ->where('school_year', $schoolYearName)
                        ->where('is_enrolled', true)
                        ->pluck('student_id');
                } else {
                    $studentIds = StudentYearEnrollment::where('school_id', $schoolId)
                        ->where('teacher_id', $teacher->id)
                        ->where('school_year_name', $schoolYearName)
                        ->pluck('student_id');
                }

                $quizAttempts = DB::table('quiz_attempts as qa')
                    ->join('quizzes as q', 'qa.quiz_id', '=', 'q.quiz_id')
                    ->join('lessons as l', 'q.lesson_id', '=', 'l.lesson_id')
                    ->where('l.teacher_id', $teacher->id)
                    ->whereIn('qa.student_id', $studentIds)
                    ->where('qa.status', 'completed')
                    ->whereBetween('qa.completed_at', [$startDate, $endDate]);
                $quizCount = (clone $quizAttempts)->count();
                $avgScore = $quizCount > 0 ? round((float) ((clone $quizAttempts)->avg('qa.percentage') ?? 0), 1) : null;
                $passRate = $quizCount > 0
                    ? round((clone $quizAttempts)->where('qa.percentage', '>=', 75)->count() / $quizCount * 100, 1)
                    : 0;

                $lessons = Lesson::where('teacher_id', $teacher->id)
                    ->where('status', 'published')->whereNull('deleted_at')->pluck('lesson_id');
                $assignments = DB::table('lesson_assignments')
                    ->whereIn('student_id', $studentIds)->whereIn('lesson_id', $lessons)
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('assigned_at', [$startDate, $endDate])
                            ->orWhereBetween('completed_at', [$startDate, $endDate])
                            ->orWhereBetween('updated_at', [$startDate, $endDate]);
                    });
                $assignedCount = (clone $assignments)->count();
                $completedCount = (clone $assignments)->where('status', 'completed')->count();
                $completionRate = $assignedCount > 0 ? round($completedCount / $assignedCount * 100, 1) : 0;
                $activeStudents = Student::whereIn('student_id', $studentIds)
                    ->where('last_activity_date', '>=', $activeSince)->count();

                return [
                    'teacher' => $teacher,
                    'total_students' => $studentIds->count(),
                    'avg_score' => $avgScore,
                    'quiz_pass_rate' => $passRate,
                    'completion_rate' => $completionRate,
                    'active_students' => $activeStudents,
                    'status' => $avgScore === null ? 'no_data' : ($avgScore >= 75 ? 'on_track' : ($avgScore >= 50 ? 'needs_attention' : 'needs_support')),
                ];
            })
            ->sortByDesc('avg_score')
            ->values();

        return view('grade-leader.reports', [
            'school' => $school,
            'teachers' => $teachers,
            'filterTeacherId' => 0,
            'selectedTeacher' => null,
            'schoolYearName' => $schoolYearName,
            'availableSchoolYears' => $availableSchoolYears,
            'selectedSchoolYear' => $selectedSchoolYear,
            'selectedMonth' => $selectedMonth,
            'monthOptions' => $monthOptions,
        ]);
    }

    public function teacherReportModal(Request $request, Teacher $teacher)
    {
        $schoolId = $this->schoolId();
        abort_unless((int) $teacher->school_id === $schoolId, 404);

        return $this->teacherClassReport(
            $teacher->load('user'),
            Auth::user()->teacher->school ?? null,
            $schoolId,
            (string) $request->query('school_year', ''),
            (string) $request->query('month', 'all')
        );
    }

    private function teacherClassReport(Teacher $teacher, $school, int $schoolId, string $requestedSchoolYear, string $selectedMonth)
    {
        $schoolYears = SchoolYear::where('school_id', $schoolId)->orderByDesc('name')->get();
        $activeSchoolYear = $schoolYears->firstWhere('status', 'active') ?? $schoolYears->first();
        $selectedSchoolYear = $schoolYears->firstWhere('name', $requestedSchoolYear) ?? $activeSchoolYear;
        $schoolYearName = $selectedSchoolYear?->name ?? SchoolYear::currentDepEdLabel();
        if (!in_array($selectedMonth, ['all', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'], true)) {
            $selectedMonth = 'all';
        }
        if (preg_match('/^(\d{4})-(\d{4})$/', $schoolYearName, $yearParts)) {
            $yearStart = (int) $yearParts[1];
            $yearEnd = (int) $yearParts[2];
        } else {
            $yearStart = (int) date('Y');
            $yearEnd = $yearStart + 1;
        }
        if ($selectedMonth === 'all') {
            $startDate = Carbon::create($yearStart, 7, 1)->startOfDay();
            $endDate = Carbon::create($yearEnd, 6, 30)->endOfDay();
        } else {
            $monthYear = (int) $selectedMonth >= 7 ? $yearStart : $yearEnd;
            $startDate = Carbon::create($monthYear, (int) $selectedMonth, 1)->startOfMonth()->startOfDay();
            $endDate = Carbon::create($monthYear, (int) $selectedMonth, 1)->endOfMonth()->endOfDay();
        }
        $weekStart = Carbon::now()->subDays(7)->startOfDay()->toDateString();

        $studentsQuery = Student::where('school_id', $schoolId);
        if ($selectedSchoolYear?->status === 'active') {
            $studentsQuery->where('teacher_id', $teacher->id)
                ->where('school_year', $schoolYearName)
                ->where('is_enrolled', true);
        } elseif ($selectedSchoolYear) {
            $historicalIds = StudentYearEnrollment::where('school_id', $schoolId)
                ->where('teacher_id', $teacher->id)
                ->where('school_year_name', $schoolYearName)
                ->pluck('student_id');
            $studentsQuery->whereIn('student_id', $historicalIds);
        } else {
            $studentsQuery->whereRaw('1 = 0');
        }
        $students = $studentsQuery
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        $studentIds = $students->pluck('student_id');

        $programTypes = collect(['Regular', 'Inclusion', 'Transition', 'Self-contained'])
            ->mapWithKeys(fn ($type) => [$type => 0]);
        $masteryLevels = collect(['Beginner', 'Intermediate', 'Advanced'])
            ->mapWithKeys(fn ($level) => [$level => 0]);
        $historicalProgramTypes = $selectedSchoolYear?->status === 'active'
            ? collect()
            : StudentYearEnrollment::where('school_id', $schoolId)
                ->where('teacher_id', $teacher->id)
                ->where('school_year_name', $schoolYearName)
                ->whereIn('student_id', $studentIds)
                ->pluck('program_type', 'student_id');
        foreach ($students as $student) {
            $programType = $historicalProgramTypes->get($student->student_id) ?? $student->program_type;
            $programType = collect($programTypes->keys())->first(fn ($type) => strtolower((string) $type) === strtolower(trim((string) $programType)));
            if ($programType !== null) {
                $programTypes->put($programType, $programTypes->get($programType) + 1);
            }

            $masteryLevel = collect($masteryLevels->keys())->first(fn ($level) => strtolower($level) === strtolower(trim((string) $student->fsl_mastery_level)));
            if ($masteryLevel !== null) {
                $masteryLevels->put($masteryLevel, $masteryLevels->get($masteryLevel) + 1);
            }
        }

        $trendEnd = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        $trendStart = $trendEnd->copy()->subWeeks(7)->startOfWeek(Carbon::MONDAY);
        $activityByWeek = [];
        for ($week = 0; $week < 8; $week++) {
            $bucketStart = $trendStart->copy()->addWeeks($week)->startOfDay();
            $activityByWeek[$bucketStart->toDateString()] = [
                'label' => $bucketStart->format('M j'),
                'student_ids' => collect(),
            ];
        }

        if ($studentIds->isNotEmpty()) {
            $quizActivity = DB::table('quiz_attempts')
                ->join('quizzes as activity_quizzes', 'quiz_attempts.quiz_id', '=', 'activity_quizzes.quiz_id')
                ->join('lessons as activity_lessons', 'activity_quizzes.lesson_id', '=', 'activity_lessons.lesson_id')
                ->whereIn('quiz_attempts.student_id', $studentIds)
                ->where('activity_lessons.teacher_id', $teacher->id)
                ->where('quiz_attempts.status', 'completed')
                ->whereBetween('quiz_attempts.completed_at', [$trendStart, $trendEnd])
                ->get(['quiz_attempts.student_id', 'quiz_attempts.completed_at']);
            $lessonActivity = DB::table('lesson_assignments')
                ->join('lessons as activity_lessons', 'lesson_assignments.lesson_id', '=', 'activity_lessons.lesson_id')
                ->whereIn('lesson_assignments.student_id', $studentIds)
                ->where('activity_lessons.teacher_id', $teacher->id)
                ->where(function ($query) use ($trendStart, $trendEnd) {
                    $query->whereBetween('lesson_assignments.completed_at', [$trendStart, $trendEnd])
                        ->orWhereBetween('lesson_assignments.updated_at', [$trendStart, $trendEnd]);
                })
                ->get(['lesson_assignments.student_id', 'lesson_assignments.updated_at', 'lesson_assignments.completed_at']);

            foreach ($quizActivity as $event) {
                $eventDate = Carbon::parse($event->completed_at);
                $weekKey = $eventDate->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
                if (isset($activityByWeek[$weekKey])) {
                    $activityByWeek[$weekKey]['student_ids']->push((int) $event->student_id);
                }
            }
            foreach ($lessonActivity as $event) {
                $eventTimestamp = $event->completed_at ?: $event->updated_at;
                if (!$eventTimestamp) continue;
                $eventDate = Carbon::parse($eventTimestamp);
                $weekKey = $eventDate->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
                if (isset($activityByWeek[$weekKey])) {
                    $activityByWeek[$weekKey]['student_ids']->push((int) $event->student_id);
                }
            }
        }
        $activityTrend = collect($activityByWeek)->map(fn ($week) => [
            'label' => $week['label'],
            'active_count' => $week['student_ids']->unique()->count(),
        ])->values();

        $lessons = Lesson::where('teacher_id', $teacher->id)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->orderBy('module_order')
            ->get(['lesson_id', 'title', 'module_id']);
        $lessonIds = $lessons->pluck('lesson_id');

        $quizAttemptRows = DB::table('quiz_attempts as qa')
            ->join('quizzes as q', 'qa.quiz_id', '=', 'q.quiz_id')
            ->join('lessons as l', 'q.lesson_id', '=', 'l.lesson_id')
            ->where('l.teacher_id', $teacher->id)
            ->whereIn('qa.student_id', $studentIds)
            ->where('qa.status', 'completed')
            ->whereBetween('qa.completed_at', [$startDate, $endDate])
            ->get(['qa.student_id', 'qa.percentage', 'qa.completed_at']);
        $quizAttempts = $quizAttemptRows->groupBy('student_id');

        $assignmentRows = DB::table('lesson_assignments')
            ->whereIn('student_id', $studentIds)
            ->whereIn('lesson_id', $lessonIds)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('assigned_at', [$startDate, $endDate])
                    ->orWhereBetween('completed_at', [$startDate, $endDate])
                    ->orWhereBetween('updated_at', [$startDate, $endDate]);
            })
            ->get(['student_id', 'status']);
        $assignmentsByStudent = $assignmentRows->groupBy('student_id');

        $activeStudents = Student::whereIn('student_id', $studentIds)
            ->where('last_activity_date', '>=', $weekStart)
            ->pluck('student_id')->flip();
        $classAverage = $quizAttemptRows->avg('percentage');
        $quizCount = $quizAttemptRows->count();
        $passedQuizzes = $quizAttemptRows->where('percentage', '>=', 75)->count();
        $completedAssignments = $assignmentRows->where('status', 'completed')->count();
        $totalAssignments = $assignmentRows->count();
        $checkpointExams = CheckpointExam::where('teacher_id', $teacher->id)
            ->where('status', 'published')->count();

        $strugglingStudents = $students->map(function ($student) use ($quizAttempts, $assignmentsByStudent, $activeStudents) {
            $quizzes = $quizAttempts->get($student->student_id, collect());
            $assignments = $assignmentsByStudent->get($student->student_id, collect());
            $quizAverage = $quizzes->isNotEmpty() ? round((float) $quizzes->avg('percentage'), 1) : null;
            $assigned = $assignments->count();
            $completed = $assignments->where('status', 'completed')->count();
            $completionRate = $assigned > 0 ? round($completed / $assigned * 100, 1) : null;
            $isActive = $activeStudents->has($student->student_id);
            $concerns = [];
            $riskScore = 0;

            if (!$isActive) {
                $concerns[] = 'No activity in the last 7 days';
                $riskScore += 2;
            }
            if ($quizAverage === null) {
                $concerns[] = 'No completed quiz yet';
                $riskScore++;
            } elseif ($quizAverage < 75) {
                $concerns[] = 'Quiz average below 75%';
                $riskScore++;
            }
            if ($completionRate !== null && $completionRate < 50) {
                $concerns[] = 'Lesson completion below 50%';
                $riskScore++;
            }

            return [
                'student_id' => $student->student_id,
                'name' => trim($student->first_name . ' ' . $student->last_name),
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'avatar' => $student->avatarUrl(),
                'quiz_average' => $quizAverage,
                'quiz_count' => $quizzes->count(),
                'completion_rate' => $completionRate,
                'completed_assignments' => $completed,
                'total_assignments' => $assigned,
                'active' => $isActive,
                'last_activity' => $student->last_activity_date ? Carbon::parse($student->last_activity_date)->format('M j, Y') : null,
                'concerns' => $concerns,
                'risk_score' => $riskScore,
            ];
        })
            ->filter(fn ($student) => $student['risk_score'] > 0)
            ->sort(function ($left, $right) {
                $riskOrder = $right['risk_score'] <=> $left['risk_score'];
                if ($riskOrder !== 0) return $riskOrder;
                return ($left['quiz_average'] ?? -1) <=> ($right['quiz_average'] ?? -1);
            })
            ->values()
            ->map(function ($student, $index) {
                $student['rank'] = $index + 1;
                return $student;
            });

        $metrics = [
            'student_count' => $students->count(),
            'active_count' => $activeStudents->count(),
            'active_rate' => $students->isNotEmpty() ? round($activeStudents->count() / $students->count() * 100, 1) : 0,
            'avg_quiz_score' => $quizCount > 0 ? round((float) $classAverage, 1) : null,
            'quiz_count' => $quizCount,
            'passed_quizzes' => $passedQuizzes,
            'quiz_pass_rate' => $quizCount > 0 ? round($passedQuizzes / $quizCount * 100, 1) : 0,
            'completed_assignments' => $completedAssignments,
            'total_assignments' => $totalAssignments,
            'lesson_completion_rate' => $totalAssignments > 0 ? round($completedAssignments / $totalAssignments * 100, 1) : 0,
            'published_lessons' => $lessons->count(),
            'published_checkpoint_exams' => $checkpointExams,
            'students_needing_support' => $strugglingStudents->count(),
            'program_type_distribution' => $programTypes,
            'mastery_level_distribution' => $masteryLevels,
            'activity_trend' => $activityTrend,
        ];

        $periodLabel = $selectedMonth === 'all' ? 'S.Y. ' . $schoolYearName : $startDate->format('F Y');

        return view('grade-leader.partials.class-report-modal-content', compact(
            'school', 'teacher', 'schoolYearName', 'selectedMonth', 'periodLabel', 'startDate', 'endDate', 'metrics', 'strugglingStudents'
        ));
    }

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
