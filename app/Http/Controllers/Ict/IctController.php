<?php

namespace App\Http\Controllers\Ict;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * IctController
 *
 * Handles the three tabs of the School ICT Coordinator portal:
 *   - dashboard  : system adoption KPIs + login/usage trend
 *   - accounts   : school-scoped account management (teachers + grade_leaders)
 *   - settings   : school profile + personal profile + password change
 *
 * Every query is strictly scoped to the ICT coordinator's assigned school_id.
 * No access to other schools, global settings, or academic data.
 */
class IctController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Shared helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function schoolId(): int
    {
        // ICT Coordinators are linked to a school via the teachers table
        // (same mechanism as grade_leader) — role is 'ict', not 'teacher'
        return (int) (Auth::user()->teacher->school_id ?? 0);
    }

    private function school(): ?\App\Models\School
    {
        return Auth::user()->teacher?->school;
    }

    /** Teacher IDs (role=teacher only) — excludes grade_leader, ict, admin, and system accounts. */
    private function schoolTeacherIds(int $schoolId): \Illuminate\Support\Collection
    {
        return Teacher::where('school_id', $schoolId)
            ->whereHas('user', fn ($q) => $q
                ->where('role', 'teacher')
                ->where('is_system', false)
            )
            ->pluck('id');
    }

    /** User IDs for teachers + grade_leaders only (not ict, not admin, not system) — used for account management table. */
    private function schoolUserIds(int $schoolId): \Illuminate\Support\Collection
    {
        return Teacher::where('school_id', $schoolId)
            ->whereHas('user', fn ($q) => $q
                ->whereIn('role', ['teacher', 'grade_leader'])
                ->where('is_system', false)
            )
            ->pluck('user_id');
    }

    /** All user IDs in the school regardless of role — used for dashboard adoption metrics. */
    private function allSchoolUserIds(int $schoolId): \Illuminate\Support\Collection
    {
        return Teacher::where('school_id', $schoolId)->pluck('user_id');
    }

    /** All student IDs belonging to this school. */
    private function schoolStudentIds(int $schoolId): \Illuminate\Support\Collection
    {
        return Student::where('school_id', $schoolId)->pluck('student_id');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. DASHBOARD — System Adoption & Popularity
    // ─────────────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $schoolId  = $this->schoolId();
        $school    = $this->school();
        $allUserIds = $this->allSchoolUserIds($schoolId);  // all roles — for adoption metrics
        $userIds    = $this->schoolUserIds($schoolId);      // teachers+grade_leaders only — for account mgmt
        $studentIds = $this->schoolStudentIds($schoolId);

        // ── KPI counts ───────────────────────────────────────────────────────
        $totalTeachers       = User::whereIn('id', $allUserIds)->where('role', 'teacher')->count();
        $totalTeacherLeaders = User::whereIn('id', $allUserIds)->where('role', 'grade_leader')->count();
        $totalIct            = User::whereIn('id', $allUserIds)->where('role', 'ict')->count();
        $totalStudents       = $studentIds->count();

        // Active users (last 7 days): teachers active = updated_at proxy
        $activeTeachers = User::whereIn('id', $allUserIds)
            ->whereIn('role', ['teacher', 'grade_leader', 'ict'])
            ->where('updated_at', '>=', Carbon::now()->subDays(7))
            ->count();
        $activeStudents = Student::whereIn('student_id', $studentIds)
            ->where('last_activity_date', '>=', Carbon::now()->subDays(7))
            ->count();

        // ── Login trend: lesson completions + student activity as usage proxy ─
        $usageTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $usageTrend[] = [
                'label'    => Carbon::now()->subDays($i)->format('M j'),
                'teachers' => User::whereIn('id', $allUserIds)
                    ->whereIn('role', ['teacher', 'grade_leader', 'ict'])
                    ->whereDate('updated_at', $date)
                    ->count(),
                'students' => Student::whereIn('student_id', $studentIds)
                    ->whereDate('last_activity_date', $date)
                    ->count(),
            ];
        }

        // ── Weekly Active Users sparkline (7 days) ────────────────────────
        $sparkDates    = [];
        $sparkTeachers = [];
        $sparkStudents = [];
        for ($i = 6; $i >= 0; $i--) {
            $day  = Carbon::now()->subDays($i);
            $date = $day->toDateString();
            $sparkDates[]    = ['short' => $day->format('M j'), 'day' => $i === 0 ? 'Today' : ($i === 1 ? 'Yesterday' : $day->format('l')), 'date' => $day->format('M j, Y')];
            $sparkTeachers[] = User::whereIn('id', $allUserIds)
                ->whereIn('role', ['teacher', 'grade_leader', 'ict'])
                ->whereDate('updated_at', $date)->count();
            $sparkStudents[] = Student::whereIn('student_id', $studentIds)
                ->whereDate('last_activity_date', $date)->count();
        }

        // ── System adoption rate: active/total ────────────────────────────
        $totalUsers   = $allUserIds->count() + $totalStudents;
        $activeUsers  = $activeTeachers + $activeStudents;
        $adoptionRate = $totalUsers > 0 ? round($activeUsers / $totalUsers * 100, 1) : 0;

        // ── Lesson completion activity in the school ─────────────────────
        $totalLessonsDone = DB::table('lesson_assignments')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->count();

        // ── Recently active staff (teachers + grade_leaders only) ──────
        $recentTeachers = User::whereIn('id', $allUserIds)
            ->whereIn('role', ['teacher', 'grade_leader'])
            ->with('teacher')
            ->latest('updated_at')
            ->limit(6)
            ->get();

        return view('ict.dashboard', compact(
            'school', 'schoolId',
            'totalTeachers', 'totalTeacherLeaders', 'totalIct', 'totalStudents',
            'activeTeachers', 'activeStudents',
            'adoptionRate', 'totalLessonsDone',
            'usageTrend', 'sparkDates', 'sparkTeachers', 'sparkStudents',
            'recentTeachers'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. ACCOUNT MANAGEMENT — School-scoped teachers + grade_leaders
    // ─────────────────────────────────────────────────────────────────────────

    public function accounts(Request $request)
    {
        $schoolId   = $this->schoolId();
        $school     = $this->school();
        $userIds    = $this->schoolUserIds($schoolId);

        $search       = trim($request->get('search', ''));
        $roleFilter   = $request->get('role', 'all');
        $statusFilter = $request->get('status', 'all');

        $query = User::with('teacher.school')
            ->whereIn('id', $userIds)
            ->whereIn('role', ['teacher', 'grade_leader']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name',     'like', "%{$search}%")
                  ->orWhere('email',    'like', "%{$search}%")
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

        // Stats (school-scoped)
        $totalAccounts       = User::whereIn('id', $userIds)->whereIn('role', ['teacher', 'grade_leader'])->count();
        $teacherCount        = User::whereIn('id', $userIds)->where('role', 'teacher')->count();
        $teacherLeaderCount  = User::whereIn('id', $userIds)->where('role', 'grade_leader')->count();
        $activeCount         = User::whereIn('id', $userIds)->whereIn('role', ['teacher', 'grade_leader'])->where('status', 'active')->count();
        $inactiveCount       = User::whereIn('id', $userIds)->whereIn('role', ['teacher', 'grade_leader'])->where('status', 'inactive')->count();

        return view('ict.accounts', compact(
            'school', 'accounts',
            'search', 'roleFilter', 'statusFilter',
            'totalAccounts', 'teacherCount', 'teacherLeaderCount',
            'activeCount', 'inactiveCount'
        ));
    }

    /**
     * POST /ict/accounts/add
     * Create a new teacher or grade_leader in this school.
     */
    public function addAccount(Request $request)
    {
        $schoolId = $this->schoolId();

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'role'       => 'required|in:teacher,grade_leader',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        $username = Str::slug($validated['first_name'] . '.' . $validated['last_name'] . rand(100, 999));

        $user = User::create([
            'username'          => $username,
            'name'              => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'email'             => $validated['email'],
            'email_verified_at' => now(),
            'password'          => Hash::make($validated['password']),
            'role'              => $validated['role'],
            'status'            => 'active',
        ]);

        Teacher::create([
            'user_id'        => $user->id,
            'school_id'      => $schoolId,
            'first_name'     => $validated['first_name'],
            'last_name'      => $validated['last_name'],
            'specialization' => 'Regular',
        ]);

        AuditLog::record(
            action:      'create_account',
            module:      'ict_accounts',
            description: "ICT Coordinator created account for {$user->name} ({$user->email}) with role {$user->role}",
            userId:      Auth::id(),
            userName:    Auth::user()->name,
            userRole:    Auth::user()->role,
            subjectType: User::class,
            subjectId:   $user->id,
        );

        return response()->json(['success' => true, 'message' => 'Account created successfully.']);
    }

    /**
     * PATCH /ict/accounts/{id}/status
     */
    public function updateStatus(Request $request, int $id)
    {
        $user = $this->findScopedUser($id);

        $validated = $request->validate(['status' => 'required|in:active,inactive']);

        $old = $user->status;
        $user->update(['status' => $validated['status']]);

        AuditLog::record(
            action:      'update_account_status',
            module:      'ict_accounts',
            description: "Status changed from '{$old}' to '{$validated['status']}' for {$user->name}",
            userId:      Auth::id(),
            userName:    Auth::user()->name,
            userRole:    Auth::user()->role,
            subjectType: User::class,
            subjectId:   $user->id,
            oldValues:   ['status' => $old],
            newValues:   ['status' => $validated['status']],
        );

        return response()->json(['success' => true, 'status' => $user->status]);
    }

    /**
     * PATCH /ict/accounts/{id}/role
     */
    public function updateRole(Request $request, int $id)
    {
        if ($id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You cannot change your own role.'], 403);
        }

        $user = $this->findScopedUser($id);

        $validated = $request->validate(['role' => 'required|in:teacher,grade_leader']);

        $old = $user->role;
        $user->update(['role' => $validated['role']]);

        AuditLog::record(
            action:      'update_account_role',
            module:      'ict_accounts',
            description: "Role changed from '{$old}' to '{$validated['role']}' for {$user->name}",
            userId:      Auth::id(),
            userName:    Auth::user()->name,
            userRole:    Auth::user()->role,
            subjectType: User::class,
            subjectId:   $user->id,
            oldValues:   ['role' => $old],
            newValues:   ['role' => $validated['role']],
        );

        return response()->json(['success' => true, 'role' => $user->role]);
    }

    /**
     * POST /ict/accounts/{id}/reset-password
     */
    public function resetPassword(Request $request, int $id)
    {
        $user = $this->findScopedUser($id);

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        AuditLog::record(
            action:      'reset_password',
            module:      'ict_accounts',
            description: "Password reset for {$user->name} ({$user->email}) by ICT Coordinator",
            userId:      Auth::id(),
            userName:    Auth::user()->name,
            userRole:    Auth::user()->role,
            subjectType: User::class,
            subjectId:   $user->id,
        );

        return response()->json(['success' => true]);
    }

    /**
     * Resolve user and enforce school-scope — aborts 403 if outside school.
     */
    private function findScopedUser(int $userId): User
    {
        $schoolId = $this->schoolId();
        $userIds  = $this->schoolUserIds($schoolId);

        $user = User::findOrFail($userId);

        if (! $userIds->contains($user->id)) {
            abort(403, 'This account does not belong to your school.');
        }

        // Extra guard: never touch admins or ict/system accounts
        if (! in_array($user->role, ['teacher', 'grade_leader'])) {
            abort(403, 'You can only manage teacher and Grade Leader accounts.');
        }

        return $user;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. SETTINGS — School profile + personal profile + password
    // ─────────────────────────────────────────────────────────────────────────

    public function settings()
    {
        $user       = Auth::user();
        $ictProfile = $user->teacher;        // teachers row — holds school_id + first/last name for the ICT coordinator
        $school     = $ictProfile?->school;

        return view('ict.settings', compact('user', 'ictProfile', 'school'));
    }

    /** PATCH /ict/settings/profile */
    public function updateProfile(Request $request)
    {
        $user       = Auth::user();
        $ictProfile = $user->teacher;

        $validated = $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'profile_photo' => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        if ($request->hasFile('profile_photo') && $request->file('profile_photo')->isValid()) {
            if ($user->profile_photo && ! str_starts_with($user->profile_photo, 'http')) {
                \Storage::disk('public')->delete($user->profile_photo);
            }
            $file     = $request->file('profile_photo');
            $filename = 'profile_' . $user->id . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $path     = $file->storeAs('profile_photos', $filename, 'public');
            $user->profile_photo = $path;
        }

        $user->name = trim($validated['first_name'] . ' ' . $validated['last_name']);
        $user->save();

        // Update the name fields in the teachers row (used for display)
        if ($ictProfile) {
            $ictProfile->first_name = $validated['first_name'];
            $ictProfile->last_name  = $validated['last_name'];
            $ictProfile->save();
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /** PATCH /ict/settings/school */
    public function updateSchool(Request $request)
    {
        $ictProfile = Auth::user()->teacher;

        if (! $ictProfile) {
            return back()->with('error', 'ICT Coordinator profile record not found.');
        }

        $validated = $request->validate([
            'school_name'    => 'required|string|max:255',
            'school_address' => 'nullable|string|max:255',
            'region'         => 'nullable|string|max:50',
            'division'       => 'nullable|string|max:100',
        ]);

        $school = $ictProfile->school;
        if ($school) {
            $school->name     = $validated['school_name'];
            $school->address  = $validated['school_address'] ?? $school->address;
            $school->region   = $validated['region']         ?? $school->region;
            $school->division = $validated['division']       ?? $school->division;
            $school->save();

            AuditLog::record(
                action:      'update_school',
                module:      'ict_settings',
                description: "School profile updated by ICT Coordinator: {$school->name}",
                userId:      Auth::id(),
                userName:    Auth::user()->name,
                userRole:    Auth::user()->role,
            );
        }

        return back()->with('success', 'School details updated successfully.');
    }

    /** PATCH /ict/settings/password */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        if ($user->google_id && empty($user->password)) {
            return back()->with('error', 'Google Sign-In accounts manage passwords through Google.');
        }

        $validated = $request->validate([
            'current_password' => 'required',
            'password'         => [
                'required', 'string', 'min:10', 'max:100', 'confirmed',
                'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'password.min'       => 'Password must be at least 10 characters.',
            'password.regex'     => 'Must include uppercase, lowercase, a number, and a special character.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        if (Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'New password cannot be the same as your current password.'])->withInput();
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }
}

