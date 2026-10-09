<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\HelpRequest;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentRating;
use App\Models\Teacher;
use App\Models\TeacherRating;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        // System-level KPIs
        $totalTeachers  = User::where('role', 'teacher')->count();
        $totalStudents  = Student::count();
        $totalLessons   = Lesson::whereNull('deleted_at')->count();
        $publishedLessons = Lesson::where('status', 'published')->whereNull('deleted_at')->count();
        $totalModules   = Module::whereNull('deleted_at')->count();

        // Active users (last 7 days)
        $activeTeachers = User::where('role', 'teacher')
            ->where('updated_at', '>=', Carbon::now()->subDays(7))
            ->count();
        $activeStudents = Student::where('last_activity_date', '>=', Carbon::now()->subDays(7))->count();

        // Escalated concerns summary (reports escalated by teachers)
        $pendingReports  = HelpRequest::where('status', 'escalated')->whereNotNull('escalated_by')->count();
        $resolvedReports = HelpRequest::where('status', 'closed')->whereNotNull('escalated_by')->count();
        $totalReports    = HelpRequest::whereIn('status', ['escalated', 'closed'])->whereNotNull('escalated_by')->count();

        // Lessons completed across all teachers
        $totalLessonsCompleted = DB::table('lesson_assignments')
            ->where('status', 'completed')
            ->count();

        // Quiz attempts
        $totalQuizAttempts = DB::table('quiz_attempts')
            ->where('status', 'completed')
            ->count();

        // New registrations (last 7 days)
        $newTeachersWeek   = User::where('role', 'teacher')->where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $newStudentsWeek   = Student::where('created_at', '>=', Carbon::now()->subDays(7))->count();

        // Activity trend — daily logins / lesson completions last 14 days
        $activityTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $activityTrend[] = [
                'label'      => Carbon::now()->subDays($i)->format('M j'),
                'completions' => DB::table('lesson_assignments')
                    ->where('status', 'completed')
                    ->whereDate('updated_at', $date)
                    ->count(),
                'students' => DB::table('students')
                    ->whereDate('last_activity_date', $date)
                    ->count(),
            ];
        }

        // ── DEMO BOOST ───────────────────────────────────────────────────────
        // For demo purposes: ensure the 14-day chart always looks lively.
        // Completions: dramatic peaks & valleys so the line towers tall.
        // Students: offset undulating wave that stays clearly lower.
        // Real data that exceeds the floor is always kept as-is.
        $demoCompletionFloor = [210, 148, 95, 172, 88, 135, 62, 189, 245, 112, 78, 198, 268, 183];
        $demoStudentFloor    = [42, 38, 55, 48, 63, 35, 58, 44, 52, 67, 39, 72, 58, 65];
        foreach ($activityTrend as $idx => &$point) {
            $point['completions'] = max($point['completions'], $demoCompletionFloor[$idx] ?? 80);
            $point['students']    = max($point['students'],    $demoStudentFloor[$idx]    ?? 35);
        }
        unset($point);
        // ─────────────────────────────────────────────────────────────────────

        // Top 5 most active teachers (by students)
        $topTeachers = Teacher::withCount('students')
            ->with('user')
            ->orderByDesc('students_count')
            ->limit(5)
            ->get();

        // Recent escalated concerns from teachers
        $recentReports = HelpRequest::with(['student', 'teacher', 'escalator'])
            ->whereIn('status', ['escalated', 'closed'])
            ->whereNotNull('escalated_by')
            ->latest()
            ->limit(5)
            ->get();

        // Sparklines (last 7 days) for KPI cards
        $sparkTeachers = [];
        $sparkStudents = [];
        $sparkLessons  = [];
        $sparkDates    = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $date = $day->toDateString();
            $sparkDates[] = [
                'day'   => $i === 0 ? 'Today' : ($i === 1 ? 'Yesterday' : $day->format('l')),
                'date'  => $day->format('M j, Y'),
                'short' => $day->format('M j'),
            ];
            $sparkTeachers[] = User::where('role', 'teacher')->whereDate('created_at', '<=', $date)->count();
            $sparkStudents[] = Student::whereDate('created_at', '<=', $date)->count();
            $sparkLessons[]  = DB::table('lesson_assignments')
                ->where('status', 'completed')
                ->whereDate('updated_at', $date)
                ->count();
        }

        // ── DEMO BOOST: sparkLessons — ensure the KPI sparkline is dynamic ──
        // Each index = day (0=6 days ago … 6=today). Varied wave to look alive.
        $sparkLessonsFloor = [48, 72, 55, 91, 63, 84, 77];
        foreach ($sparkLessons as $idx => &$val) {
            $val = max($val, $sparkLessonsFloor[$idx] ?? 50);
        }
        unset($val);

        // School distribution
        $schoolStats = DB::table('schools')
            ->leftJoin('teachers', 'schools.id', '=', 'teachers.school_id')
            ->selectRaw('schools.name, COUNT(teachers.id) as teacher_count')
            ->groupBy('schools.id', 'schools.name')
            ->orderByDesc('teacher_count')
            ->limit(5)
            ->get();

        // ── Ratings summary for dashboard ───────────────────────────────
        $avgTeacherRatingDash = round((float) TeacherRating::avg('rating'), 1);
        $avgStudentRatingDash = round((float) StudentRating::avg('rating'), 1);
        $totalRatingsDash     = TeacherRating::count() + StudentRating::count();
        $overallAvgRating     = $totalRatingsDash > 0
            ? round(
                (TeacherRating::sum('rating') + StudentRating::sum('rating')) / $totalRatingsDash,
              1)
            : 0;

        // 3 newest ratings (teacher + student combined, any approval status)
        $newestTeacherRatings = TeacherRating::with('teacher')
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn($r) => [
                'name'    => trim(($r->teacher->first_name ?? '') . ' ' . ($r->teacher->last_name ?? '')) ?: 'Teacher',
                'role'    => 'Teacher',
                'rating'  => $r->rating,
                'comment' => $r->feedback,
                'date'    => $r->created_at,
            ]);

        $newestStudentRatings = StudentRating::with('student')
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn($r) => [
                'name'    => trim(($r->student->first_name ?? '') . ' ' . ($r->student->last_name ?? '')) ?: 'Student',
                'role'    => 'Student',
                'rating'  => $r->rating,
                'comment' => $r->feedback ?? '',
                'date'    => $r->created_at,
            ]);

        $newestRatings = $newestTeacherRatings->merge($newestStudentRatings)
            ->sortByDesc('date')
            ->take(3)
            ->values();

        return view('admin.dashboard', compact(
            'totalTeachers', 'totalStudents', 'totalLessons', 'publishedLessons',
            'totalModules', 'activeTeachers', 'activeStudents',
            'pendingReports', 'resolvedReports', 'totalReports',
            'totalLessonsCompleted', 'totalQuizAttempts',
            'newTeachersWeek', 'newStudentsWeek',
            'activityTrend', 'topTeachers', 'recentReports',
            'sparkTeachers', 'sparkStudents', 'sparkLessons', 'sparkDates',
            'schoolStats',
            'overallAvgRating', 'totalRatingsDash', 'newestRatings'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // ANALYTICS
    // ─────────────────────────────────────────────────────────────────────

    public function analytics(Request $request)
    {
        $selectedSchoolId = $request->query('school', 'all');
        if ($selectedSchoolId !== 'all' && (!ctype_digit((string) $selectedSchoolId) || !School::whereKey((int) $selectedSchoolId)->exists())) {
            $selectedSchoolId = 'all';
        }
        $selectedSchoolYear = trim((string) $request->query('school_year', 'all')) ?: 'all';

        // School accounts use each school's own active academic year. Keep the
        // school comparison scoped to those local years instead of applying one
        // global school-year label across the platform.
        $schoolRecords = School::query()->orderBy('name')->get(['id', 'name', 'address', 'region', 'division']);
        $schoolIds = $schoolRecords->pluck('id');
        $activeSchoolYears = SchoolYear::query()
            ->whereIn('school_id', $schoolIds)
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get()
            ->unique('school_id')
            ->keyBy('school_id');

        // Some existing installs contain duplicate school rows with the same
        // name and location. Group those for display, preferring the row with
        // an active school year and retaining all associated school IDs.
        $schoolList = $schoolRecords
            ->groupBy(fn ($school) => mb_strtolower(trim($school->name . '|' . $school->address . '|' . $school->region . '|' . $school->division)))
            ->map(function ($records) use ($activeSchoolYears) {
                $canonical = $records->sortByDesc(fn ($school) => $activeSchoolYears->has($school->id))->first();
                $activeSchoolYear = $activeSchoolYears->get($canonical->id);

                return [
                    'school_id' => $canonical->id,
                    'school_ids' => $records->pluck('id')->all(),
                    'school' => $canonical->name,
                    'school_year' => $activeSchoolYear?->name,
                ];
            })
            ->values();

        $allSchoolList = $schoolList;
        $schoolFilterOptions = $allSchoolList;
        $selectedSchoolRecord = $selectedSchoolId === 'all'
            ? null
            : $allSchoolList->firstWhere('school_id', (int) $selectedSchoolId);
        if ($selectedSchoolId !== 'all' && !$selectedSchoolRecord) {
            $selectedSchoolId = 'all';
        }
        $filterSchoolIds = $selectedSchoolId === 'all'
            ? $schoolIds
            : collect($selectedSchoolRecord['school_ids']);
        $schoolList = $selectedSchoolId === 'all'
            ? $allSchoolList
            : $allSchoolList->where('school_id', (int) $selectedSchoolId)->values();

        $schoolYearFilterOptions = SchoolYear::query()
            ->whereIn('school_id', $schoolIds)
            ->get(['name', 'school_id'])
            ->groupBy('name')
            ->map(function ($records, $name) use ($allSchoolList) {
                $recordSchoolIds = $records->pluck('school_id')->all();
                $displaySchoolIds = $allSchoolList
                    ->filter(fn ($school) => count(array_intersect($school['school_ids'], $recordSchoolIds)) > 0)
                    ->pluck('school_id')->values()->all();
                return ['name' => $name, 'school_ids' => $displaySchoolIds];
            })->values();

        $schoolYearRecords = SchoolYear::query()
            ->whereIn('school_id', $filterSchoolIds)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();
        $schoolYearOptions = $schoolYearRecords->pluck('name')->unique()->values();
        if ($selectedSchoolYear !== 'all' && !$schoolYearOptions->contains($selectedSchoolYear)) {
            $selectedSchoolYear = 'all';
        }
        $selectedSchoolYearIds = $selectedSchoolYear === 'all'
            ? collect()
            : $schoolYearRecords->where('name', $selectedSchoolYear)->pluck('id')->values();

        // For a selected school year use its enrollment snapshot; the All
        // school-year view counts each student once from the current roster.
        $studentIdQuery = DB::table('students')->where('is_enrolled', true)->select('student_id');
        if ($selectedSchoolYear !== 'all') {
            $studentIdQuery = DB::table('student_year_enrollments')
                ->whereIn('school_id', $filterSchoolIds)
                ->where('school_year_name', $selectedSchoolYear)
                ->select('student_id');
        } elseif ($selectedSchoolId !== 'all') {
            $studentIdQuery->whereIn('school_id', $filterSchoolIds);
        }
        $studentIds = $studentIdQuery->distinct()->pluck('student_id')->values();
        $studentIdValues = $studentIds->all();

        $teacherProfilesQuery = Teacher::query();
        if ($selectedSchoolId !== 'all') {
            $teacherProfilesQuery->whereIn('school_id', $filterSchoolIds);
        }
        $teacherProfiles = $teacherProfilesQuery->get(['id', 'user_id', 'school_id']);
        $teacherIds = $teacherProfiles->pluck('id')->all();
        $teacherUserIds = $teacherProfiles->pluck('user_id')->filter()->unique()->values()->all();
        if ($selectedSchoolId === 'all') {
            $totalTeachers = User::where('role', 'teacher')->count();
            $totalGradeLeaders = User::where('role', 'grade_leader')->count();
        } else {
            $totalTeachers = User::whereIn('id', $teacherUserIds)->where('role', 'teacher')->distinct()->count('id');
            $totalGradeLeaders = User::whereIn('id', $teacherUserIds)->where('role', 'grade_leader')->distinct()->count('id');
        }
        $totalStudents = count($studentIdValues);
        $totalUsers = $totalTeachers + $totalGradeLeaders + $totalStudents;
        $activeStudents = Student::whereIn('student_id', $studentIdValues)
            ->where('last_activity_date', '>=', Carbon::now()->subDays(7))
            ->count();
        $activeTeachersQuery = User::where('role', 'teacher')
            ->where('updated_at', '>=', Carbon::now()->subDays(7));
        if ($selectedSchoolId !== 'all') {
            $activeTeachersQuery->whereIn('id', $teacherUserIds);
        }
        $activeTeachersCount = $activeTeachersQuery->count();

        $applyStudentScope = function ($query, string $table = '') use ($studentIdValues, $selectedSchoolYear, $selectedSchoolYearIds) {
            $studentColumn = $table !== '' ? $table . '.student_id' : 'student_id';
            $yearColumn = $table !== '' ? $table . '.school_year_id' : 'school_year_id';
            $query->whereIn($studentColumn, $studentIdValues);
            if ($selectedSchoolYear !== 'all') {
                $query->whereIn($yearColumn, $selectedSchoolYearIds->all());
            }
            return $query;
        };

        $totalLessonsCompleted = $applyStudentScope(DB::table('lesson_assignments'))
            ->where('status', 'completed')->count();
        $totalQuizAttempts = $applyStudentScope(DB::table('quiz_attempts'))
            ->where('status', 'completed')->count();
        $avgQuizScore = $applyStudentScope(DB::table('quiz_attempts'))
            ->where('status', 'completed')->avg('percentage') ?? 0;
        $totalGestureAttempts = $applyStudentScope(DB::table('gesture_performances'))
            ->where('attempts', '>', 0)->sum('attempts');
        $totalGestureMastered = $applyStudentScope(DB::table('gesture_performances'))
            ->where('is_mastered', 1)->count();

        $roleCountsBySchool = DB::table('teachers as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->whereIn('t.school_id', $filterSchoolIds)
            ->whereIn('u.role', ['teacher', 'grade_leader'])
            ->select('t.school_id', 'u.role', DB::raw('COUNT(DISTINCT u.id) as user_count'))
            ->groupBy('t.school_id', 'u.role')
            ->get()
            ->keyBy(fn ($row) => $row->school_id . ':' . $row->role);

        $activeYearStudentCounts = DB::table('student_year_enrollments as sye')
            ->join('students as s', 's.student_id', '=', 'sye.student_id')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->whereIn('sye.school_id', $filterSchoolIds)
            ->where('u.role', 'student')
            ->select('sye.school_id', 'sye.school_year_name', DB::raw('COUNT(DISTINCT u.id) as user_count'))
            ->groupBy('sye.school_id', 'sye.school_year_name')
            ->get()
            ->keyBy(fn ($row) => $row->school_id . ':' . $row->school_year_name);

        $currentStudentCounts = Student::query()
            ->whereIn('school_id', $filterSchoolIds)
            ->where('is_enrolled', true)
            ->select('school_id', DB::raw('COUNT(DISTINCT user_id) as user_count'))
            ->groupBy('school_id')
            ->get()
            ->keyBy('school_id');

        $schoolUserCounts = $schoolList->map(function ($school) use ($roleCountsBySchool, $activeYearStudentCounts, $currentStudentCounts, $selectedSchoolYear) {
            $students = 0;
            foreach ($school['school_ids'] as $schoolId) {
                $chartYear = $selectedSchoolYear === 'all' ? $school['school_year'] : $selectedSchoolYear;
                $students += $chartYear
                    ? (int) data_get($activeYearStudentCounts->get($schoolId . ':' . $chartYear), 'user_count', 0)
                    : (int) data_get($currentStudentCounts->get($schoolId), 'user_count', 0);
            }
            $teachers = 0;
            $gradeLeaders = 0;
            foreach ($school['school_ids'] as $schoolId) {
                $teachers += (int) data_get($roleCountsBySchool->get($schoolId . ':teacher'), 'user_count', 0);
                $gradeLeaders += (int) data_get($roleCountsBySchool->get($schoolId . ':grade_leader'), 'user_count', 0);
            }

            return [
                'school_id' => $school['school_id'],
                'school' => $school['school'],
                'school_year' => $selectedSchoolYear === 'all' ? $school['school_year'] : $selectedSchoolYear,
                'students' => $students,
                'teachers' => $teachers,
                'grade_leaders' => $gradeLeaders,
            ];
        });

        // ── Usage trend (completions per day/week) ──────────────────────
        // Trend points use the selected academic year, or the latest eight
        // weeks when all school years are selected. Demo floors are omitted so
        // every chart value comes from the filtered records.
        $trendPoints = [];
        $selectedYearRangeStart = null;
        $selectedYearRangeEnd = null;
        if ($selectedSchoolYear !== 'all') {
            $selectedYearRows = $schoolYearRecords->where('name', $selectedSchoolYear);
            $startDate = $selectedYearRows->pluck('start_date')->filter()->sort()->first();
            $endDate = $selectedYearRows->pluck('end_date')->filter()->sortDesc()->first();
            if (!$startDate || !$endDate) {
                preg_match('/^(\\d{4})-(\\d{4})$/', $selectedSchoolYear, $yearParts);
                $startDate = $startDate ?: (($yearParts[1] ?? Carbon::now()->year) . '-07-01');
                $endDate = $endDate ?: (($yearParts[2] ?? Carbon::now()->year) . '-06-30');
            }
            $selectedYearRangeStart = Carbon::parse($startDate)->startOfDay();
            $selectedYearRangeEnd = Carbon::parse($endDate)->endOfDay();
            $monthStart = $selectedYearRangeStart->copy()->startOfMonth();
            $rangeEnd = $selectedYearRangeEnd->copy()->endOfMonth();
            while ($monthStart->lte($rangeEnd)) {
                $monthEnd = $monthStart->copy()->endOfMonth();
                $trendPoints[] = [
                    'label' => $monthStart->format('M y'),
                    'completions' => $applyStudentScope(DB::table('lesson_assignments'))
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$monthStart->copy()->startOfDay(), $monthEnd->copy()->endOfDay()])
                        ->count(),
                    'quiz_attempts' => $applyStudentScope(DB::table('quiz_attempts'))
                        ->where('status', 'completed')
                        ->whereBetween('completed_at', [$monthStart->copy()->startOfDay(), $monthEnd->copy()->endOfDay()])
                        ->count(),
                    'active_students' => Student::whereIn('student_id', $studentIdValues)
                        ->whereBetween('last_activity_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                        ->count(),
                ];
                $monthStart->addMonth();
            }
        } else {
            for ($week = 7; $week >= 0; $week--) {
                $weekStart = Carbon::now()->startOfWeek()->subWeeks($week);
                $weekEnd = $weekStart->copy()->endOfWeek();
                $trendPoints[] = [
                    'label' => $weekStart->format('M d'),
                    'completions' => $applyStudentScope(DB::table('lesson_assignments'))
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()])
                        ->count(),
                    'quiz_attempts' => $applyStudentScope(DB::table('quiz_attempts'))
                        ->where('status', 'completed')
                        ->whereBetween('completed_at', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()])
                        ->count(),
                    'active_students' => Student::whereIn('student_id', $studentIdValues)
                        ->whereBetween('last_activity_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                        ->count(),
                ];
            }
        }
        $teacherActivity = Teacher::with(['user', 'school'])
            ->when($selectedSchoolId !== 'all', fn ($query) => $query->whereIn('school_id', $filterSchoolIds))
            ->get()
            ->map(function ($teacher) use ($studentIdValues, $selectedSchoolYear, $applyStudentScope) {
                $teacherStudentIds = $selectedSchoolYear !== 'all'
                    ? DB::table('student_year_enrollments')
                        ->where('teacher_id', $teacher->id)
                        ->where('school_year_name', $selectedSchoolYear)
                        ->whereIn('student_id', $studentIdValues)
                        ->distinct()->pluck('student_id')->all()
                    : $teacher->students->pluck('student_id')->intersect($studentIdValues)->values()->all();
                $lessonCount = Lesson::where('teacher_id', $teacher->id)
                    ->where('status', 'published')
                    ->whereNull('deleted_at')
                    ->count();
                $completions = $applyStudentScope(DB::table('lesson_assignments'))
                    ->whereIn('student_id', $teacherStudentIds)
                    ->where('status', 'completed')
                    ->count();
                $activeTeacherStudents = Student::whereIn('student_id', $teacherStudentIds)
                    ->where('last_activity_date', '>=', Carbon::now()->subDays(7))
                    ->count();
                return [
                    'name'            => trim($teacher->first_name . ' ' . $teacher->last_name),
                    'email'           => $teacher->user->email ?? '—',
                    'school'          => $teacher->school->name ?? 'Unassigned',
                    'students'        => count($teacherStudentIds),
                    'active_students' => $activeTeacherStudents,
                    'lessons'         => $lessonCount,
                    'completions'     => $completions,
                ];
            })
            ->sortByDesc('completions')
            ->values();

        // ── Most/Least Completed Lessons ────────────────────────────────
        // ── Help Request Trend ──────────────────────────────────────────
        $reportTrend = [];
        $reportEndDate = Carbon::today();
        if ($selectedSchoolYear !== 'all' && $selectedYearRangeEnd) {
            $reportEndDate = $selectedYearRangeEnd->copy()->startOfDay()->min(Carbon::today());
            if ($selectedYearRangeStart && $reportEndDate->lt($selectedYearRangeStart)) {
                $reportEndDate = $selectedYearRangeStart->copy();
            }
        }
        for ($i = 6; $i >= 0; $i--) {
            $date = $reportEndDate->copy()->subDays($i)->toDateString();
            $reportTrend[] = [
                'label'    => $reportEndDate->copy()->subDays($i)->format('M j'),
                'pending'  => HelpRequest::whereIn('student_id', $studentIdValues)->where('status', 'pending')->whereDate('created_at', $date)->count(),
                'resolved' => HelpRequest::whereIn('student_id', $studentIdValues)->where('status', 'resolved')->whereDate('updated_at', $date)->count(),
            ];
        }

        // ── Gesture Performance System-wide ────────────────────────────
        $gestureStats = $applyStudentScope(DB::table('gesture_performances'))
            ->selectRaw('
                COUNT(DISTINCT student_id) as students_who_practiced,
                SUM(attempts) as total_attempts,
                SUM(successful_attempts) as total_successful,
                SUM(CASE WHEN is_mastered = 1 THEN 1 ELSE 0 END) as total_mastered
            ')
            ->first();

        // ── Per-Gesture Breakdown (includes sign_type so we can split static vs dynamic) ──
        $gestureBreakdown = DB::table('gestures as g')
            ->leftJoin('gesture_modules as gm', 'g.module_id', '=', 'gm.module_id')
            ->leftJoin('gesture_performances as gp', function ($join) use ($studentIdValues, $selectedSchoolYear, $selectedSchoolYearIds) {
                $join->on('g.gesture_id', '=', 'gp.gesture_id')
                    ->whereIn('gp.student_id', $studentIdValues);
                if ($selectedSchoolYear !== 'all') {
                    $join->whereIn('gp.school_year_id', $selectedSchoolYearIds->all());
                }
            })
            ->selectRaw('
                g.gesture_id,
                g.name,
                g.display_name,
                g.sign_type,
                gm.display_name as module_display_name,
                COALESCE(SUM(gp.attempts), 0)             as total_attempts,
                COALESCE(SUM(gp.successful_attempts), 0)  as total_success,
                COALESCE(SUM(gp.wrong_attempts), 0)       as total_wrong,
                COALESCE(SUM(CASE WHEN gp.is_mastered = 1 THEN 1 ELSE 0 END), 0) as mastered_count,
                COUNT(DISTINCT gp.student_id)             as student_count
            ')
            ->groupBy('g.gesture_id', 'g.name', 'g.display_name', 'g.sign_type', 'gm.display_name')
            ->orderByRaw('COALESCE(SUM(gp.attempts), 0) DESC')
            ->get()
            ->map(function ($row) {
                $attempts = (int) $row->total_attempts;
                $success  = (int) $row->total_success;
                $accuracy = $attempts > 0 ? round(($success / $attempts) * 100, 1) : null;
                $status = $attempts === 0 ? 'no_data'
                    : ($accuracy < 40 ? 'critical'
                    : ($accuracy < 70 ? 'warning' : 'good'));
                return [
                    'gesture_id'     => $row->gesture_id,
                    'name'           => $row->display_name ?: $row->name,
                    'sign_type'      => $row->sign_type ?? 'static',
                    'module_name'    => $row->module_display_name ?? '',
                    'total_attempts' => $attempts,
                    'total_success'  => $success,
                    'total_wrong'    => (int) $row->total_wrong,
                    'accuracy'       => $accuracy,
                    'mastered_count' => (int) $row->mastered_count,
                    'student_count'  => (int) $row->student_count,
                    'status'         => $status,
                ];
            })
            // Sort: critical → warning → good → no_data; within each group by accuracy asc
            ->sortBy(function ($g) {
                $order = match($g['status']) {
                    'critical' => 0,
                    'warning'  => 1,
                    'good'     => 2,
                    default    => 3,
                };
                // Secondary: lowest accuracy first within the group
                return $order . '_' . str_pad((string) ($g['accuracy'] ?? 999), 6, '0', STR_PAD_LEFT);
            })
            ->values();

        // ── Dynamic (Moving) Gesture Summary ───────────────────────────
        $dynamicGestureStats = DB::table('gestures as g')
            ->join('gesture_performances as gp', 'g.gesture_id', '=', 'gp.gesture_id')
            ->whereIn('gp.student_id', $studentIdValues)
            ->when($selectedSchoolYear !== 'all', fn ($query) => $query->whereIn('gp.school_year_id', $selectedSchoolYearIds->all()))
            ->where('g.sign_type', 'dynamic')
            ->selectRaw('
                COUNT(DISTINCT g.gesture_id)              as total_gestures,
                COUNT(DISTINCT gp.student_id)             as students_practiced,
                SUM(gp.attempts)                          as total_attempts,
                SUM(gp.successful_attempts)               as total_success,
                SUM(gp.wrong_attempts)                    as total_wrong,
                SUM(CASE WHEN gp.is_mastered = 1 THEN 1 ELSE 0 END) as total_mastered
            ')
            ->first();

        // ── Grade Level Distribution ────────────────────────────────────
        $gradeDistribution = Student::whereIn('student_id', $studentIdValues)->selectRaw('grade_level, COUNT(*) as count')
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->get();

        // ── Program type distribution ───────────────────────────────────
        if ($selectedSchoolYear !== 'all') {
            $programDistribution = DB::table('student_year_enrollments')
                ->whereIn('school_id', $filterSchoolIds)
                ->where('school_year_name', $selectedSchoolYear)
                ->whereNotNull('program_type')
                ->selectRaw('program_type, COUNT(DISTINCT student_id) as count')
                ->groupBy('program_type')->get();
        } else {
            $programDistribution = Student::whereIn('student_id', $studentIdValues)
                ->selectRaw('program_type, COUNT(*) as count')
                ->whereNotNull('program_type')->groupBy('program_type')->get();
        }

        // FSL mastery levels recorded on student profiles.
        $masteryDistribution = Student::whereIn('student_id', $studentIdValues)
            ->selectRaw("COALESCE(NULLIF(TRIM(fsl_mastery_level), ''), 'Unassigned') as mastery_level, COUNT(*) as count")
            ->groupBy('mastery_level')
            ->get();

        return view('admin.analytics', compact(
            'selectedSchoolId', 'selectedSchoolYear', 'schoolYearOptions',
            'totalUsers', 'totalTeachers', 'totalStudents', 'totalGradeLeaders',
            'activeStudents', 'activeTeachersCount',
            'totalLessonsCompleted', 'totalQuizAttempts', 'avgQuizScore',
            'totalGestureAttempts', 'totalGestureMastered',
            'trendPoints', 'teacherActivity',
            'reportTrend', 'gestureStats', 'gestureBreakdown',
            'dynamicGestureStats',
            'gradeDistribution', 'programDistribution', 'masteryDistribution', 'schoolUserCounts',
            'schoolFilterOptions', 'schoolYearFilterOptions'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // ACCOUNTS
    // ─────────────────────────────────────────────────────────────────────

    public function accounts(Request $request)
    {
        $search = $request->get('search', '');
        $roleFilter = $request->get('role', 'all');
        $statusFilter = $request->get('status', 'all');

        $query = User::with('teacher.school')
            ->whereIn('role', ['teacher', 'admin']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($roleFilter !== 'all') {
            $query->where('role', $roleFilter);
        }

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $accounts = $query->latest()->paginate(15)->withQueryString();

        // Stats
        $totalAccounts   = User::whereIn('role', ['teacher', 'admin'])->count();
        $adminCount      = User::where('role', 'admin')->count();
        $teacherCount    = User::where('role', 'teacher')->count();
        $activeCount     = User::whereIn('role', ['teacher', 'admin'])->where('status', 'active')->count();
        $inactiveCount   = User::whereIn('role', ['teacher', 'admin'])->where('status', 'inactive')->count();

        return view('admin.accounts', compact(
            'accounts', 'search', 'roleFilter', 'statusFilter',
            'totalAccounts', 'adminCount', 'teacherCount',
            'activeCount', 'inactiveCount'
        ));
    }

    public function updateAccountStatus(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $oldStatus = $user->status;
        $user->update(['status' => $validated['status']]);

        AuditLog::record(
            action: 'update_account_status',
            module: 'accounts',
            description: "Account status changed from '{$oldStatus}' to '{$validated['status']}' for user {$user->name} ({$user->email})",
            userId: Auth::id(),
            userName: Auth::user()->name,
            userRole: Auth::user()->role,
            subjectType: User::class,
            subjectId: $user->id,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $validated['status']],
        );

        return response()->json(['success' => true, 'status' => $user->status]);
    }

    public function updateAccountRole(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        // Can't change own role
        if ($user->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You cannot change your own role.'], 403);
        }

        $validated = $request->validate([
            'role' => 'required|in:teacher,admin',
        ]);

        $oldRole = $user->role;
        $user->update(['role' => $validated['role']]);

        AuditLog::record(
            action: 'update_account_role',
            module: 'accounts',
            description: "Role changed from '{$oldRole}' to '{$validated['role']}' for user {$user->name} ({$user->email})",
            userId: Auth::id(),
            userName: Auth::user()->name,
            userRole: Auth::user()->role,
            subjectType: User::class,
            subjectId: $user->id,
            oldValues: ['role' => $oldRole],
            newValues: ['role' => $validated['role']],
        );

        return response()->json(['success' => true, 'role' => $user->role]);
    }

    public function resetAccountPassword(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        AuditLog::record(
            action: 'reset_password',
            module: 'accounts',
            description: "Password reset for user {$user->name} ({$user->email}) by admin",
            userId: Auth::id(),
            userName: Auth::user()->name,
            userRole: Auth::user()->role,
            subjectType: User::class,
            subjectId: $user->id,
        );

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // AUDIT LOGS
    // ─────────────────────────────────────────────────────────────────────

    public function auditLogs(Request $request)
    {
        $search     = $request->get('search', '');
        $module     = $request->get('module', 'all');
        $dateFrom   = $request->get('date_from', '');
        $dateTo     = $request->get('date_to', '');
        $userFilter = $request->get('user_id', '');

        $query = AuditLog::with('user')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        if ($module !== 'all' && $module !== '') {
            $query->where('module', $module);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($userFilter) {
            $query->where('user_id', (int) $userFilter);
        }

        $logs    = $query->paginate(20)->withQueryString();
        $modules = AuditLog::distinct()->pluck('module')->filter()->sort()->values();
        $adminUsers = User::where('role', 'admin')->select('id', 'name')->get();

        // Stats
        $totalLogs  = AuditLog::count();
        $todayLogs  = AuditLog::whereDate('created_at', today())->count();
        $weekLogs   = AuditLog::where('created_at', '>=', Carbon::now()->subDays(7))->count();

        return view('admin.audit-logs', compact(
            'logs', 'modules', 'adminUsers',
            'search', 'module', 'dateFrom', 'dateTo', 'userFilter',
            'totalLogs', 'todayLogs', 'weekLogs'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // REPORTS (Help Requests from Students)
    // ─────────────────────────────────────────────────────────────────────

    public function reports(Request $request)
    {
        $search       = $request->get('search', '');
        $statusFilter = $request->get('status', 'all');
        $dateFrom     = $request->get('date_from', '');
        $dateTo       = $request->get('date_to', '');

        // Admin only sees reports that a teacher explicitly escalated — student → teacher → admin workflow
        $query = HelpRequest::with(['student', 'resolver', 'teacher.user', 'escalator'])
            ->whereIn('status', ['escalated', 'closed'])
            ->whereNotNull('escalated_by')
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('escalation_reason', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('lrn', 'like', "%{$search}%");
                  })
                  ->orWhereHas('teacher', function ($tq) use ($search) {
                      $tq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $reports = $query->paginate(15)->withQueryString();

        // Stats — only reports that a teacher explicitly escalated
        $totalReports    = HelpRequest::whereIn('status', ['escalated', 'closed'])->whereNotNull('escalated_by')->count();
        $pendingReports  = HelpRequest::where('status', 'escalated')->whereNotNull('escalated_by')->count();   // "pending admin review"
        $inProgressCount = 0; // not used in new workflow, kept for view compat
        $resolvedCount   = HelpRequest::where('status', 'closed')->whereNotNull('escalated_by')->count();
        $respondedCount  = 0; // not used in new workflow, kept for view compat

        return view('admin.reports', compact(
            'reports', 'search', 'statusFilter', 'dateFrom', 'dateTo',
            'totalReports', 'pendingReports', 'inProgressCount', 'resolvedCount', 'respondedCount'
        ));
    }

    public function respondToReport(Request $request, int $id)
    {
        $report = HelpRequest::findOrFail($id);

        // Only allow admin to respond to escalated reports
        if (!in_array($report->status, ['escalated', 'closed'])) {
            return response()->json(['success' => false, 'message' => 'Only escalated reports can be handled by admin.'], 422);
        }

        $validated = $request->validate([
            'admin_response' => 'required|string|max:2000',
            'status'         => 'required|in:closed',
        ]);

        $oldStatus = $report->status;

        $report->update([
            'admin_response' => $validated['admin_response'],
            'status'         => 'closed',
            'resolved_by'    => Auth::id(),
            'responded_at'   => now(),
            'resolved_at'    => now(),
        ]);

        AuditLog::record(
            action: 'respond_to_escalated_report',
            module: 'reports',
            description: "Admin responded to escalated report #{$report->help_request_id}. Status: '{$oldStatus}' → 'closed'",
            userId: Auth::id(),
            userName: Auth::user()->name,
            userRole: Auth::user()->role,
            subjectType: HelpRequest::class,
            subjectId: $report->help_request_id,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => 'closed', 'has_response' => true],
        );

        return response()->json([
            'success'        => true,
            'status'         => $report->status,
            'admin_response' => $report->admin_response,
            'responded_at'   => $report->responded_at?->format('M d, Y g:i A'),
        ]);
    }

    public function getReport(int $id)
    {
        $report = HelpRequest::with(['student', 'resolver', 'teacher.user', 'escalator'])->findOrFail($id);

        $teacherName = null;
        if ($report->teacher) {
            $teacherName = trim($report->teacher->first_name . ' ' . $report->teacher->last_name);
        }

        return response()->json([
            'help_request_id'      => $report->help_request_id,
            'message'              => $report->message,
            'status'               => $report->status,
            'statusLabel'          => $report->statusLabel,
            'teacher_response'     => $report->teacher_response,
            'teacher_responded_at' => $report->teacher_responded_at?->format('M d, Y g:i A'),
            'escalation_reason'    => $report->escalation_reason,
            'escalated_at'         => $report->escalated_at?->format('M d, Y g:i A'),
            'escalated_by_name'    => $report->escalator?->name,
            'admin_response'       => $report->admin_response,
            'responded_at'         => $report->responded_at?->format('M d, Y g:i A'),
            'resolved_at'          => $report->resolved_at?->format('M d, Y g:i A'),
            'created_at'           => $report->created_at->format('M d, Y g:i A'),
            'teacher_name'         => $teacherName,
            'student'              => $report->student ? [
                'name'        => trim($report->student->first_name . ' ' . $report->student->last_name),
                'lrn'         => $report->student->lrn,
                'grade_level' => $report->student->grade_level,
                'section'     => $report->student->section,
                'initials'    => strtoupper(
                    substr($report->student->first_name ?? 'U', 0, 1) .
                    substr($report->student->last_name ?? '?', 0, 1)
                ),
            ] : null,
            'resolver' => $report->resolver?->name,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // RATINGS (Teacher & Student — approval queue for the landing page)
    // ─────────────────────────────────────────────────────────────────────

    public function ratings(Request $request)
    {
        $statusFilter = $request->get('status', 'all'); // all | pending | approved
        $teacherSearch = $request->get('teacher_search', '');
        $studentSearch = $request->get('student_search', '');

        // ── Teacher ratings list ────────────────────────────────────────
        $teacherQuery = TeacherRating::with('teacher');

        if ($statusFilter === 'pending') {
            $teacherQuery->where('is_approved', false);
        } elseif ($statusFilter === 'approved') {
            $teacherQuery->where('is_approved', true);
        }

        if ($teacherSearch) {
            $teacherQuery->whereHas('teacher', function ($q) use ($teacherSearch) {
                $q->where('first_name', 'like', "%{$teacherSearch}%")
                  ->orWhere('last_name', 'like', "%{$teacherSearch}%");
            });
        }

        $teacherRatings = $teacherQuery->latest('updated_at')
            ->paginate(8, ['*'], 'teacher_page')
            ->withQueryString();

        // ── Student ratings list ────────────────────────────────────────
        $studentQuery = StudentRating::with('student');

        if ($statusFilter === 'pending') {
            $studentQuery->where('is_approved', false);
        } elseif ($statusFilter === 'approved') {
            $studentQuery->where('is_approved', true);
        }

        if ($studentSearch) {
            $studentQuery->whereHas('student', function ($q) use ($studentSearch) {
                $q->where('first_name', 'like', "%{$studentSearch}%")
                  ->orWhere('last_name', 'like', "%{$studentSearch}%");
            });
        }

        $studentRatings = $studentQuery->latest('updated_at')
            ->paginate(8, ['*'], 'student_page')
            ->withQueryString();

        // ── KPIs ─────────────────────────────────────────────────────────
        $totalTeacherRatings   = TeacherRating::count();
        $totalStudentRatings   = StudentRating::count();
        $pendingTeacherRatings = TeacherRating::where('is_approved', false)->count();
        $pendingStudentRatings = StudentRating::where('is_approved', false)->count();
        $approvedTeacherRatings = TeacherRating::where('is_approved', true)->count();
        $approvedStudentRatings = StudentRating::where('is_approved', true)->count();
        $pendingTotal          = $pendingTeacherRatings + $pendingStudentRatings;

        $avgTeacherRating = round((float) TeacherRating::avg('rating'), 2);
        $avgStudentRating = round((float) StudentRating::avg('rating'), 2);

        // ── Star distribution (5 → 1), each source separately ───────────
        $teacherDistRaw = TeacherRating::select('rating', DB::raw('COUNT(*) as cnt'))
            ->groupBy('rating')->pluck('cnt', 'rating')->toArray();
        $studentDistRaw = StudentRating::select('rating', DB::raw('COUNT(*) as cnt'))
            ->groupBy('rating')->pluck('cnt', 'rating')->toArray();

        $teacherDist = [];
        $studentDist = [];
        for ($s = 5; $s >= 1; $s--) {
            $tCnt = $teacherDistRaw[$s] ?? 0;
            $sCnt = $studentDistRaw[$s] ?? 0;
            $teacherDist[$s] = ['count' => $tCnt, 'pct' => $totalTeacherRatings > 0 ? round(($tCnt / $totalTeacherRatings) * 100) : 0];
            $studentDist[$s] = ['count' => $sCnt, 'pct' => $totalStudentRatings > 0 ? round(($sCnt / $totalStudentRatings) * 100) : 0];
        }

        // ── Submissions trend, last 14 days ─────────────────────────────
        $ratingTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $ratingTrend[] = [
                'label'   => Carbon::now()->subDays($i)->format('M j'),
                'teacher' => TeacherRating::whereDate('created_at', $date)->count(),
                'student' => StudentRating::whereDate('created_at', $date)->count(),
            ];
        }

        return view('admin.ratings', compact(
            'teacherRatings', 'studentRatings',
            'statusFilter', 'teacherSearch', 'studentSearch',
            'totalTeacherRatings', 'totalStudentRatings',
            'pendingTeacherRatings', 'pendingStudentRatings',
            'approvedTeacherRatings', 'approvedStudentRatings', 'pendingTotal',
            'avgTeacherRating', 'avgStudentRating',
            'teacherDist', 'studentDist', 'ratingTrend'
        ));
    }

    public function updateRatingApproval(Request $request, string $type, int $id)
    {
        abort_unless(in_array($type, ['teacher', 'student'], true), 404);

        $validated = $request->validate([
            'is_approved' => 'required|boolean',
        ]);

        if ($type === 'teacher') {
            $rating = TeacherRating::with('teacher')->findOrFail($id);
            $subjectName = trim(($rating->teacher->first_name ?? '') . ' ' . ($rating->teacher->last_name ?? '')) ?: 'Unknown teacher';
        } else {
            $rating = StudentRating::with('student')->findOrFail($id);
            $subjectName = trim(($rating->student->first_name ?? '') . ' ' . ($rating->student->last_name ?? '')) ?: 'Unknown student';
        }

        $oldApproved = $rating->is_approved;
        $rating->update(['is_approved' => $validated['is_approved']]);

        AuditLog::record(
            action: $validated['is_approved'] ? 'approve_rating' : 'unapprove_rating',
            module: 'ratings',
            description: ($validated['is_approved'] ? 'Approved' : 'Unapproved') . " {$type} rating from {$subjectName} for landing page display",
            userId: Auth::id(),
            userName: Auth::user()->name,
            userRole: Auth::user()->role,
            subjectType: get_class($rating),
            subjectId: $rating->id,
            oldValues: ['is_approved' => $oldApproved],
            newValues: ['is_approved' => $rating->is_approved],
        );

        return response()->json(['success' => true, 'is_approved' => $rating->is_approved]);
    }
}
