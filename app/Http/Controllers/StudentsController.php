<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\StudentPromotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class StudentsController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        $totalStudents = 0;
        $newThisWeek   = 0;
        $students      = collect();

        if ($teacher) {
            $totalStudents = Student::where('teacher_id', $teacher->id)->count();
            $newThisWeek   = Student::where('teacher_id', $teacher->id)
                                    ->where('created_at', '>=', Carbon::now()->subWeek())
                                    ->count();

            $query = Student::where('teacher_id', $teacher->id)->with('promotions');

            // Read filters from session (set by applyFilter POST, never from the URL)
            $filters = session('students_filters', []);
            $search  = $filters['search']  ?? '';
            $level   = $filters['level']   ?? '';
            $program = $filters['program'] ?? '';
            $status  = $filters['status']  ?? 'all';

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                      ->orWhere('lrn', 'like', "%{$search}%");
                });
            }

            if (!empty($level)) {
                $query->where('fsl_mastery_level', $level);
            }

            if (!empty($program)) {
                $query->where('program_type', $program);
            }

            if (!empty($status) && $status !== 'all') {
                $query->where('status', $status);
            }

            $students = $query->orderBy('created_at', 'desc')->paginate(10);
        }

        return view('students', compact('totalStudents', 'newThisWeek', 'students'));
    }

    /**
     * Accept filters via POST, store them in the session, then redirect to
     * the clean /students URL (no visible query string in the browser).
     */
    public function applyFilter(Request $request)
    {
        $validated = $request->validate([
            'search'  => ['nullable', 'string', 'max:100'],
            'level'   => ['nullable', 'string', 'in:Beginner,Intermediate,Advanced,Completed,'],
            'program' => ['nullable', 'string', 'in:Regular,Inclusion,SPED,'],
            'status'  => ['nullable', 'string', 'in:active,inactive,all,'],
        ]);

        // Clear filter if user submitted an empty/reset form
        if (($validated['search'] ?? '') === ''
            && ($validated['level'] ?? '') === ''
            && ($validated['program'] ?? '') === ''
            && (($validated['status'] ?? 'all') === 'all')
        ) {
            session()->forget('students_filters');
        } else {
            session(['students_filters' => $validated]);
        }

        return redirect()->route('students');
    }

    /**
     * Check if an LRN already exists in the database.
     */
    public function checkLrn(Request $request)
    {
        $request->validate([
            'lrn' => 'required|numeric|digits:12',
        ]);

        $exists = Student::where('lrn', $request->lrn)->exists();

        return response()->json(['exists' => $exists]);
    }

    /**
     * Store a newly created student manually.
     */
    public function store(Request $request)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only registered teachers can add students.'
            ], 403);
        }

        $programType = $request->input('program_type', 'Regular');
        $showGradeSection = in_array($programType, ['Regular', 'Inclusion'], true);

        $request->validate([
            'lrn' => 'required|numeric|digits:12|unique:students,lrn',
            'full_name' => 'required|string|max:255',
            'program_type' => 'required|in:Regular,Inclusion,Transition,Self-contained',
            'grade_level' => 'nullable|string|max:255',
            'age' => 'required|integer|min:1|max:120',
            'section' => 'nullable|string|max:255',
            'fsl_mastery_level' => 'required|in:Beginner,Intermediate,Advanced',
        ], [
            'lrn.unique' => 'LRN already exists.',
        ]);

        // Split full name (expecting "Last Name, First Name" or fallback to space splitting)
        $fullName = trim($request->full_name);
        if (str_contains($fullName, ',')) {
            $parts = explode(',', $fullName, 2);
            $lastName = trim($parts[0]);
            $firstName = trim($parts[1]);
        } else {
            $parts = explode(' ', $fullName);
            if (count($parts) > 1) {
                $lastName = array_pop($parts);
                $firstName = implode(' ', $parts);
            } else {
                $firstName = $fullName;
                $lastName = '';
            }
        }

        $lrn = $request->lrn;
        $pin = substr($lrn, -4);

        // Generate unique username from first name and last name
        $username = $this->generateUniqueUsername($firstName, $lastName);

        DB::beginTransaction();
        try {
            // Create user account for student
            $user = User::create([
                'name' => trim($firstName . ' ' . $lastName),
                'username' => $username,
                'email' => $lrn,
                'password' => Hash::make($lrn), // Temporary password using LRN
                'role' => 'student',
                'status' => 'active',
            ]);

            // Create student record
            $student = Student::create([
                'user_id' => $user->id,
                'teacher_id' => $teacher->id,
                'school_id' => $teacher->school_id,
                'lrn' => $lrn,
                'pin' => $pin,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'age' => $request->age,
                'grade_level' => $showGradeSection ? $request->grade_level : null,
                'section' => $showGradeSection ? $request->section : null,
                'fsl_mastery_level' => $request->fsl_mastery_level,
                'program_type' => $programType,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student added successfully!',
                'student' => $student
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import multiple students from parsed Excel JSON.
     */
    public function import(Request $request)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only registered teachers can import students.'
            ], 403);
        }

        $request->validate([
            'students' => 'required|array',
            'students.*.lrn' => 'required|numeric|digits:12',
            'students.*.full_name' => 'required|string|max:255',
            'students.*.grade_level' => 'nullable|string|max:255',
            'students.*.age' => 'required|integer|min:1|max:120',
            'students.*.section' => 'nullable|string|max:255',
            'students.*.fsl_mastery_level' => 'required|in:Beginner,Intermediate,Advanced',
            'auto_pin' => 'nullable|boolean',
        ]);

        $studentsData = $request->students;
        $autoPin = $request->auto_pin;

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($studentsData as $index => $data) {
                $lrn = trim($data['lrn']);

                // Skip if student with this LRN already exists
                if (Student::where('lrn', $lrn)->exists()) {
                    $skipped++;
                    continue;
                }

                // Process name
                $fullName = trim($data['full_name']);
                if (str_contains($fullName, ',')) {
                    $parts = explode(',', $fullName, 2);
                    $lastName = trim($parts[0]);
                    $firstName = trim($parts[1]);
                } else {
                    $parts = explode(' ', $fullName);
                    if (count($parts) > 1) {
                        $lastName = array_pop($parts);
                        $firstName = implode(' ', $parts);
                    } else {
                        $firstName = $fullName;
                        $lastName = '';
                    }
                }

                // Auto PIN is 6-digit code derived from LRN if checked. Otherwise, default is 4-digit derived from LRN.
                $pin = $autoPin ? substr($lrn, -6) : substr($lrn, -4);

                // Generate unique username
                $username = $this->generateUniqueUsername($firstName, $lastName);

                // Create user
                $user = User::create([
                    'name' => trim($firstName . ' ' . $lastName),
                    'username' => $username,
                    'email' => $lrn,
                    'password' => Hash::make($lrn), // Temporary password using LRN
                    'role' => 'student',
                    'status' => 'active',
                ]);

                // Create student
                Student::create([
                    'user_id' => $user->id,
                    'teacher_id' => $teacher->id,
                    'school_id' => $teacher->school_id,
                    'lrn' => $lrn,
                    'pin' => $pin,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'age' => $data['age'],
                    'grade_level' => $data['grade_level'] ?? null,
                    'section' => $data['section'] ?? null,
                    'fsl_mastery_level' => $data['fsl_mastery_level'],
                    'program_type' => 'Regular',
                ]);

                $imported++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped,
                'message' => "Successfully imported {$imported} students. Skipped {$skipped} existing records."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Bulk import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Promote a student to the next FSL mastery level.
     */
    public function promote(Request $request, $id)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'target_level' => 'required|in:Intermediate,Advanced,Completed',
            'force'        => 'nullable|boolean',
        ]);

        $student = Student::where('student_id', $id)
                          ->where('teacher_id', $teacher->id)
                          ->firstOrFail();

        $xp         = $student->total_xp ?? 0;
        $currentLvl = $student->fsl_mastery_level;
        $targetLvl  = $request->target_level;
        $force      = (bool) $request->input('force', false);

        // Validate the promotion path is logical
        $allowedPaths = [
            'Beginner'     => 'Intermediate',
            'Intermediate' => 'Advanced',
            'Advanced'     => 'Completed',
        ];

        if (!isset($allowedPaths[$currentLvl]) || $allowedPaths[$currentLvl] !== $targetLvl) {
            return response()->json([
                'success' => false,
                'message' => "Invalid promotion path: {$currentLvl} → {$targetLvl}."
            ], 422);
        }

        // XP thresholds
        $xpRequired = ['Beginner' => 300, 'Intermediate' => 600, 'Advanced' => 1000];
        $requiredXp = $xpRequired[$currentLvl] ?? 0;
        $meetsXp    = $xp >= $requiredXp;

        // If XP not met and not forcing, reject
        if (!$meetsXp && !$force) {
            return response()->json([
                'success'  => false,
                'message'  => "Student needs {$requiredXp} XP to promote. Currently has {$xp} XP.",
                'needs_xp' => $requiredXp,
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update mastery level
            $student->update(['fsl_mastery_level' => $targetLvl]);

            // Record promotion history
            StudentPromotion::create([
                'student_id'      => $student->student_id,
                'from_level'      => $currentLvl,
                'to_level'        => $targetLvl,
                'xp_at_promotion' => $xp,
                'promoted_by'     => Auth::id(),
                'was_forced'      => !$meetsXp,
                'promoted_at'     => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$student->first_name} {$student->last_name} has been promoted to {$targetLvl}!",
                'new_level' => $targetLvl,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Promotion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Demote a student to the previous FSL mastery level.
     */
    public function demote(Request $request, $id)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'target_level' => 'required|in:Beginner,Intermediate,Advanced',
        ]);

        $student = Student::where('student_id', $id)
                          ->where('teacher_id', $teacher->id)
                          ->firstOrFail();

        $xp         = $student->total_xp ?? 0;
        $currentLvl = $student->fsl_mastery_level;
        $targetLvl  = $request->target_level;

        $allowedDemotions = [
            'Intermediate' => 'Beginner',
            'Advanced'     => 'Intermediate',
            'Completed'    => 'Advanced',
        ];

        if (!isset($allowedDemotions[$currentLvl]) || $allowedDemotions[$currentLvl] !== $targetLvl) {
            return response()->json([
                'success' => false,
                'message' => "Invalid demotion path: {$currentLvl} → {$targetLvl}."
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update mastery level
            $student->update(['fsl_mastery_level' => $targetLvl]);

            // Record promotion/demotion history
            StudentPromotion::create([
                'student_id'      => $student->student_id,
                'from_level'      => $currentLvl,
                'to_level'        => $targetLvl,
                'xp_at_promotion' => $xp,
                'promoted_by'     => Auth::id(),
                'was_forced'      => true,
                'promoted_at'     => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$student->first_name} {$student->last_name} has been demoted to {$targetLvl}.",
                'new_level' => $targetLvl,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Demotion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a unique lowercase username based on student name.
     */
    private function generateUniqueUsername($firstName, $lastName)
    {
        $base = preg_replace('/[^a-z0-9]/', '', strtolower($firstName . $lastName));
        if (empty($base)) {
            $base = 'student';
        }

        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }

    /**
 * Get student's current streak
 * GET /api/student/streak
 */
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

}
