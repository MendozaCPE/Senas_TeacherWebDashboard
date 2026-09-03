<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\HelpRequest;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\School;
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

        // Help requests summary
        $pendingReports  = HelpRequest::where('status', 'pending')->count();
        $resolvedReports = HelpRequest::where('status', 'resolved')->count();
        $totalReports    = HelpRequest::count();

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

        // Top 5 most active teachers (by students)
        $topTeachers = Teacher::withCount('students')
            ->with('user')
            ->orderByDesc('students_count')
            ->limit(5)
            ->get();

        // Recent help requests
        $recentReports = HelpRequest::with('student')
            ->latest()
            ->limit(5)
            ->get();

        // Sparklines (last 7 days) for KPI cards
        $sparkTeachers = [];
        $sparkStudents = [];
        $sparkLessons  = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $sparkTeachers[] = User::where('role', 'teacher')->whereDate('created_at', '<=', $date)->count();
            $sparkStudents[] = Student::whereDate('created_at', '<=', $date)->count();
            $sparkLessons[]  = DB::table('lesson_assignments')
                ->where('status', 'completed')
                ->whereDate('updated_at', $date)
                ->count();
        }

        // School distribution
        $schoolStats = DB::table('schools')
            ->leftJoin('teachers', 'schools.id', '=', 'teachers.school_id')
            ->selectRaw('schools.name, COUNT(teachers.id) as teacher_count')
            ->groupBy('schools.id', 'schools.name')
            ->orderByDesc('teacher_count')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalTeachers', 'totalStudents', 'totalLessons', 'publishedLessons',
            'totalModules', 'activeTeachers', 'activeStudents',
            'pendingReports', 'resolvedReports', 'totalReports',
            'totalLessonsCompleted', 'totalQuizAttempts',
            'newTeachersWeek', 'newStudentsWeek',
            'activityTrend', 'topTeachers', 'recentReports',
            'sparkTeachers', 'sparkStudents', 'sparkLessons',
            'schoolStats'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // ANALYTICS
    // ─────────────────────────────────────────────────────────────────────

    public function analytics(Request $request)
    {
        $period = $request->get('period', 'weekly');
        $year   = (int) $request->get('year', date('Y'));
        $month  = (int) $request->get('month', date('n'));

        // ── Platform-wide KPIs ──────────────────────────────────────────
        $totalUsers    = User::whereIn('role', ['teacher', 'student'])->count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalStudents = Student::count();
        $activeStudents = Student::where('last_activity_date', '>=', Carbon::now()->subDays(7))->count();
        $activeTeachersCount = User::where('role', 'teacher')
            ->where('updated_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $totalLessonsCompleted = DB::table('lesson_assignments')->where('status', 'completed')->count();
        $totalQuizAttempts     = DB::table('quiz_attempts')->where('status', 'completed')->count();
        $avgQuizScore = DB::table('quiz_attempts')
            ->where('status', 'completed')
            ->avg('percentage') ?? 0;

        $totalGestureAttempts  = DB::table('gesture_performances')->where('attempts', '>', 0)->sum('attempts');
        $totalGestureMastered  = DB::table('gesture_performances')->where('is_mastered', 1)->count();

        // ── Usage trend (completions per day/week) ──────────────────────
        if ($period === 'weekly') {
            $trendPoints = [];
            for ($w = 7; $w >= 0; $w--) {
                $wStart = Carbon::now()->startOfWeek()->subWeeks($w);
                $wEnd   = $wStart->copy()->endOfWeek();
                $trendPoints[] = [
                    'label'       => $wStart->format('M d'),
                    'completions' => DB::table('lesson_assignments')
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$wStart->startOfDay(), $wEnd->endOfDay()])
                        ->count(),
                    'quiz_attempts' => DB::table('quiz_attempts')
                        ->where('status', 'completed')
                        ->whereBetween('completed_at', [$wStart->startOfDay(), $wEnd->endOfDay()])
                        ->count(),
                    'active_students' => DB::table('students')
                        ->whereBetween('last_activity_date', [$wStart->toDateString(), $wEnd->toDateString()])
                        ->count(),
                ];
            }
        } elseif ($period === 'monthly') {
            $trendPoints = [];
            $mStart = Carbon::create($year, $month, 1)->startOfMonth();
            for ($i = 0; $i < 4; $i++) {
                $dStart = $mStart->copy()->addDays($i * 7);
                $dEnd   = $i === 3 ? $mStart->copy()->endOfMonth() : $mStart->copy()->addDays(($i + 1) * 7 - 1);
                $trendPoints[] = [
                    'label' => $dStart->format('M d'),
                    'completions' => DB::table('lesson_assignments')
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$dStart->startOfDay(), $dEnd->endOfDay()])
                        ->count(),
                    'quiz_attempts' => DB::table('quiz_attempts')
                        ->where('status', 'completed')
                        ->whereBetween('completed_at', [$dStart->startOfDay(), $dEnd->endOfDay()])
                        ->count(),
                    'active_students' => DB::table('students')
                        ->whereBetween('last_activity_date', [$dStart->toDateString(), $dEnd->toDateString()])
                        ->count(),
                ];
            }
        } elseif ($period === 'yearly') {
            $trendPoints = [];
            for ($m = 1; $m <= 12; $m++) {
                $curM = Carbon::create($year, $m, 1);
                $trendPoints[] = [
                    'label' => $curM->format('M'),
                    'completions' => DB::table('lesson_assignments')
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$curM->copy()->startOfMonth()->startOfDay(), $curM->copy()->endOfMonth()->endOfDay()])
                        ->count(),
                    'quiz_attempts' => DB::table('quiz_attempts')
                        ->where('status', 'completed')
                        ->whereBetween('completed_at', [$curM->copy()->startOfMonth()->startOfDay(), $curM->copy()->endOfMonth()->endOfDay()])
                        ->count(),
                    'active_students' => DB::table('students')
                        ->whereBetween('last_activity_date', [$curM->copy()->startOfMonth()->toDateString(), $curM->copy()->endOfMonth()->toDateString()])
                        ->count(),
                ];
            }
        } else {
            // quarterly
            $qStartMonth = (ceil($month / 3) - 1) * 3 + 1;
            $trendPoints = [];
            for ($m = 0; $m < 3; $m++) {
                $curM = Carbon::create($year, $qStartMonth + $m, 1);
                $trendPoints[] = [
                    'label' => $curM->format('M Y'),
                    'completions' => DB::table('lesson_assignments')
                        ->where('status', 'completed')
                        ->whereBetween('updated_at', [$curM->copy()->startOfMonth()->startOfDay(), $curM->copy()->endOfMonth()->endOfDay()])
                        ->count(),
                    'quiz_attempts' => DB::table('quiz_attempts')
                        ->where('status', 'completed')
                        ->whereBetween('completed_at', [$curM->copy()->startOfMonth()->startOfDay(), $curM->copy()->endOfMonth()->endOfDay()])
                        ->count(),
                    'active_students' => DB::table('students')
                        ->whereBetween('last_activity_date', [$curM->copy()->startOfMonth()->toDateString(), $curM->copy()->endOfMonth()->toDateString()])
                        ->count(),
                ];
            }
        }

        // ── Teacher Activity Ranking ────────────────────────────────────
        $teacherActivity = Teacher::with('user')
            ->withCount([
                'students',
                'students as active_students_count' => function ($q) {
                    $q->where('last_activity_date', '>=', Carbon::now()->subDays(7));
                },
            ])
            ->get()
            ->map(function ($teacher) {
                $lessonCount = Lesson::where('teacher_id', $teacher->id)
                    ->where('status', 'published')
                    ->whereNull('deleted_at')
                    ->count();
                $completions = DB::table('lesson_assignments')
                    ->whereIn('student_id', $teacher->students->pluck('student_id'))
                    ->where('status', 'completed')
                    ->count();
                return [
                    'name'            => trim($teacher->first_name . ' ' . $teacher->last_name),
                    'email'           => $teacher->user->email ?? '—',
                    'students'        => $teacher->students_count,
                    'active_students' => $teacher->active_students_count,
                    'lessons'         => $lessonCount,
                    'completions'     => $completions,
                ];
            })
            ->sortByDesc('completions')
            ->values()
            ->take(10);

        // ── Most/Least Completed Lessons ────────────────────────────────
        $mostCompletedLessons = DB::table('lesson_assignments')
            ->join('lessons', 'lesson_assignments.lesson_id', '=', 'lessons.lesson_id')
            ->where('lesson_assignments.status', 'completed')
            ->whereNull('lessons.deleted_at')
            ->selectRaw('lessons.title, COUNT(*) as completions, AVG(lesson_assignments.score) as avg_score')
            ->groupBy('lessons.lesson_id', 'lessons.title')
            ->orderByDesc('completions')
            ->limit(5)
            ->get();

        $leastCompletedLessons = DB::table('lesson_assignments')
            ->join('lessons', 'lesson_assignments.lesson_id', '=', 'lessons.lesson_id')
            ->whereNull('lessons.deleted_at')
            ->where('lessons.status', 'published')
            ->selectRaw('lessons.title, COUNT(*) as total, SUM(CASE WHEN lesson_assignments.status = "completed" THEN 1 ELSE 0 END) as completions')
            ->groupBy('lessons.lesson_id', 'lessons.title')
            ->having('total', '>', 0)
            ->orderBy('completions')
            ->limit(5)
            ->get();

        // ── Help Request Trend ──────────────────────────────────────────
        $reportTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $reportTrend[] = [
                'label'    => Carbon::now()->subDays($i)->format('M j'),
                'pending'  => HelpRequest::where('status', 'pending')->whereDate('created_at', $date)->count(),
                'resolved' => HelpRequest::where('status', 'resolved')->whereDate('updated_at', $date)->count(),
            ];
        }

        // ── Gesture Performance System-wide ────────────────────────────
        $gestureStats = DB::table('gesture_performances')
            ->selectRaw('
                COUNT(DISTINCT student_id) as students_who_practiced,
                SUM(attempts) as total_attempts,
                SUM(successful_attempts) as total_successful,
                SUM(CASE WHEN is_mastered = 1 THEN 1 ELSE 0 END) as total_mastered
            ')
            ->first();

        // ── Grade Level Distribution ────────────────────────────────────
        $gradeDistribution = Student::selectRaw('grade_level, COUNT(*) as count')
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->get();

        // ── Program type distribution ───────────────────────────────────
        $programDistribution = Student::selectRaw('program_type, COUNT(*) as count')
            ->whereNotNull('program_type')
            ->groupBy('program_type')
            ->get();

        return view('admin.analytics', compact(
            'period', 'year', 'month',
            'totalUsers', 'totalTeachers', 'totalStudents',
            'activeStudents', 'activeTeachersCount',
            'totalLessonsCompleted', 'totalQuizAttempts', 'avgQuizScore',
            'totalGestureAttempts', 'totalGestureMastered',
            'trendPoints', 'teacherActivity',
            'mostCompletedLessons', 'leastCompletedLessons',
            'reportTrend', 'gestureStats',
            'gradeDistribution', 'programDistribution'
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

        // Admin only sees escalated (and closed) reports — student → teacher → admin workflow
        $query = HelpRequest::with(['student', 'resolver', 'teacher.user', 'escalator'])
            ->whereIn('status', ['escalated', 'closed'])
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

        // Stats — only escalated/closed scope
        $totalReports    = HelpRequest::whereIn('status', ['escalated', 'closed'])->count();
        $pendingReports  = HelpRequest::where('status', 'escalated')->count();   // "pending admin review"
        $inProgressCount = 0; // not used in new workflow, kept for view compat
        $resolvedCount   = HelpRequest::where('status', 'closed')->count();
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