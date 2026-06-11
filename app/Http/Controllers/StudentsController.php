<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class StudentsController extends Controller
{
    public function index()
    {
        $teacher = Auth::user()->teacher;
        
        $totalStudents = 0;
        $newThisWeek = 0;
        $students = collect();
        
        if ($teacher) {
            $totalStudents = Student::where('teacher_id', $teacher->id)->count();
            $newThisWeek = Student::where('teacher_id', $teacher->id)
                                  ->where('created_at', '>=', Carbon::now()->subWeek())
                                  ->count();
                                  
            // Get all students for this teacher, ideally paginate them
            $students = Student::where('teacher_id', $teacher->id)
                               ->orderBy('created_at', 'desc')
                               ->paginate(10);
        }
        
        return view('students', compact('totalStudents', 'newThisWeek', 'students'));
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

        $request->validate([
            'lrn' => 'required|numeric|digits:12|unique:students,lrn',
            'full_name' => 'required|string|max:255',
            'grade_level' => 'nullable|string|max:255',
            'age' => 'required|integer|min:1|max:120',
            'section' => 'nullable|string|max:255',
            'fsl_mastery_level' => 'required|in:Beginner,Intermediate,Advanced',
            'auto_pin' => 'nullable|boolean',
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

        // Generate auto-pin if requested (random 4-digit), otherwise last 4 digits of LRN
        $lrn = $request->lrn;
        $pin = $request->auto_pin ? (string) mt_rand(1000, 9999) : substr($lrn, -4);

        // Generate unique username from first name and last name
        $username = $this->generateUniqueUsername($firstName, $lastName);

        DB::beginTransaction();
        try {
            // Create user account for student
            $user = User::create([
                'name' => trim($firstName . ' ' . $lastName),
                'username' => $username,
                'email' => null,
                'password' => null, // Students log in with LRN and PIN
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
                'grade_level' => $request->grade_level,
                'section' => $request->section,
                'fsl_mastery_level' => $request->fsl_mastery_level,
                'program_type' => 'Regular', // Default program type
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
                    'email' => null,
                    'password' => null,
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
}
