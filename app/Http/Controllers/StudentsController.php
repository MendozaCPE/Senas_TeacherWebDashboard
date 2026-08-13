<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\StudentPromotion;
use App\Models\TeacherNotification;
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

        $availableSchoolYears = collect();

        if ($teacher) {
            $totalStudents = Student::where('teacher_id', $teacher->id)->count();
            $newThisWeek   = Student::where('teacher_id', $teacher->id)
                                    ->where('created_at', '>=', Carbon::now()->subWeek())
                                    ->count();

            $query = Student::where('teacher_id', $teacher->id)->with('promotions');

            // Read filters from GET query or session fallback
            if ($request->has('search')) {
                $search = trim($request->input('search'));
                session(['students_filters' => array_merge(session('students_filters', []), ['search' => $search])]);
                $filters = session('students_filters', []);
                $level   = $filters['level']   ?? '';
                $program = $filters['program'] ?? '';
                $status  = $filters['status']  ?? 'active';
                $schoolYear = $filters['school_year'] ?? '';
            } else {
                $filters = session('students_filters', []);
                $search  = $filters['search']  ?? '';
                $level   = $filters['level']   ?? '';
                $program = $filters['program'] ?? '';
                $status  = $filters['status']  ?? 'active';
                $schoolYear = $filters['school_year'] ?? '';
            }

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

            if (!empty($schoolYear)) {
                $query->where('school_year', $schoolYear);
            }

            $students = $query->orderBy('created_at', 'desc')->paginate(10);

            $availableSchoolYears = Student::where('teacher_id', $teacher->id)
                                           ->whereNotNull('school_year')
                                           ->where('school_year', '!=', '')
                                           ->select('school_year')
                                           ->distinct()
                                           ->orderBy('school_year', 'desc')
                                           ->pluck('school_year');
        }

        return view('students', compact('totalStudents', 'newThisWeek', 'students', 'availableSchoolYears'));
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
            'program' => ['nullable', 'string', 'in:Regular,Inclusion,SPED,Self-contained,Transition,'],
            'status'  => ['nullable', 'string', 'in:active,inactive,all,'],
            'school_year' => ['nullable', 'string', 'max:50'],
        ]);

        // Clear filter if user submitted an empty/reset form
        if (($validated['search'] ?? '') === ''
            && ($validated['level'] ?? '') === ''
            && ($validated['program'] ?? '') === ''
            && ($validated['school_year'] ?? '') === ''
            && (($validated['status'] ?? 'active') === 'active')
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

        $teacher = Auth::user()->teacher;
        $student = Student::where('lrn', $request->lrn)->with('teacher')->first();

        if ($student) {
            if ($teacher && $student->teacher_id == $teacher->id) {
                return response()->json(['exists' => true, 'status' => 'own']);
            }

            if ($student->status === 'unenrolled') {
                return response()->json(['exists' => true, 'status' => 'unenrolled']);
            }

            // Enrolled under a different teacher — return their name
            $otherTeacher = $student->teacher;
            $teacherName  = $otherTeacher
                ? trim($otherTeacher->first_name . ' ' . $otherTeacher->last_name)
                : 'another teacher';

            return response()->json([
                'exists'       => true,
                'status'       => 'other',
                'teacher_name' => $teacherName,
            ]);
        }

        return response()->json(['exists' => false]);
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
            'lrn' => 'required|numeric|digits:12',
            'full_name' => 'required|string|max:255',
            'program_type' => 'required|in:Regular,Inclusion,Transition,Self-contained,SPED,Home-based',
            'grade_level' => 'nullable|string|max:255',
            'age' => 'required|integer|min:1|max:120',
            'section' => 'nullable|string|max:255',
            'school_year' => ['nullable', 'string', 'max:20',
                function ($attribute, $value, $fail) {
                    if (empty($value)) return;
                    if (!preg_match('/^(\d{4})-(\d{4})$/', $value, $m)) {
                        $fail('School year must be in YYYY-YYYY format (e.g. 2024-2025).');
                        return;
                    }
                    if ((int)$m[2] !== (int)$m[1] + 1) {
                        $fail("School year is invalid — the second year must be exactly one year after the first (e.g. {$m[1]}-" . ((int)$m[1]+1) . ').');
                    }
                    if ((int)$m[1] < 2000 || (int)$m[1] > (int)date('Y') + 2) {
                        $fail("School year {$value} is out of a reasonable range.");
                    }
                },
            ],
            'fsl_mastery_level' => 'required|in:Beginner,Intermediate,Advanced',
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

        // Check for existing student
        $existingStudent = Student::where('lrn', $lrn)->with('teacher')->first();
        if ($existingStudent) {
            if ($existingStudent->teacher_id == $teacher->id) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'lrn' => ['Student already exists in your class.']
                    ]
                ], 422);
            } else {
                // Block enrollment if the student is still enrolled under another teacher
                if ($existingStudent->status !== 'unenrolled') {
                    $otherTeacher = $existingStudent->teacher;
                    $teacherName  = $otherTeacher
                        ? trim($otherTeacher->first_name . ' ' . $otherTeacher->last_name)
                        : 'another teacher';

                    return response()->json([
                        'message' => 'The given data was invalid.',
                        'errors'  => [
                            'lrn' => ["This student is already enrolled to Teacher {$teacherName}."]
                        ]
                    ], 422);
                }

                // Student is unenrolled — transfer to this teacher
                $existingStudent->teacher_id = $teacher->id;
                $existingStudent->school_id  = $teacher->school_id;
                $existingStudent->status     = 'active';
                $existingStudent->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Student has been successfully enrolled in your class.',
                    'student' => $existingStudent
                ]);
            }
        }

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
                'school_year' => $request->school_year,
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
     *
     * Every row is validated for all required fields before any DB writes happen.
     * Rows with missing/invalid required fields are rejected and returned in the
     * errors list — no partial / NULL records are ever inserted.
     *
     * Required fields: lrn, full_name, program_type, fsl_mastery_level
     * Optional fields: age, grade_level, section, school_year
     *   (grade_level and section are required when program_type is Regular or Inclusion)
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
            'students'  => 'required|array|min:1',
            'auto_pin'  => 'nullable|boolean',
        ]);

        $studentsData = $request->students;
        // auto_pin flag is accepted for backwards-compatibility but PIN is always last 4 digits of LRN

        $imported  = 0;
        $skipped   = 0;
        $errors    = [];
        $transfers = [];

        // Valid values for enumerated fields
        $validPrograms  = ['Regular', 'Inclusion', 'SPED', 'Home-based'];
        $validMastery   = ['Beginner', 'Intermediate', 'Advanced'];
        $needGradeSection = ['Regular', 'Inclusion'];

        DB::beginTransaction();
        try {
            foreach ($studentsData as $index => $data) {
                $rowNumber  = $index + 2; // row 1 is the header
                $displayName = trim($data['full_name'] ?? '');
                if ($displayName === '') {
                    $displayName = "Row {$rowNumber} (name unknown)";
                }

                // ── 1. Collect all missing/invalid required fields ────────────
                $missing = [];

                $lrn = trim((string) ($data['lrn'] ?? ''));
                if ($lrn === '') {
                    $missing[] = 'LRN';
                } elseif (!preg_match('/^\d{12}$/', $lrn)) {
                    $missing[] = 'LRN (must be exactly 12 digits)';
                }

                $fullName = trim((string) ($data['full_name'] ?? ''));
                if ($fullName === '') {
                    $missing[] = 'Student Name';
                }

                $programType = trim((string) ($data['program_type'] ?? ''));
                if ($programType === '') {
                    $missing[] = 'Program';
                } elseif (!in_array($programType, $validPrograms, true)) {
                    $missing[] = "Program (invalid value \"{$programType}\"; allowed: " . implode(', ', $validPrograms) . ')';
                }

                $masteryLevel = trim((string) ($data['fsl_mastery_level'] ?? ''));
                if ($masteryLevel === '') {
                    $missing[] = 'FSL Mastery Level';
                } elseif (!in_array($masteryLevel, $validMastery, true)) {
                    // Tolerate minor casing issues from the client normalisation
                    $normalised = ucfirst(strtolower($masteryLevel));
                    if (in_array($normalised, $validMastery, true)) {
                        $masteryLevel = $normalised;
                    } else {
                        $missing[] = "FSL Mastery Level (invalid value \"{$masteryLevel}\")";
                    }
                }

                // Grade Level + Section are required for Regular / Inclusion programs
                if (in_array($programType, $needGradeSection, true)) {
                    if (trim((string) ($data['grade_level'] ?? '')) === '') {
                        $missing[] = 'Grade Level';
                    }
                    if (trim((string) ($data['section'] ?? '')) === '') {
                        $missing[] = 'Section';
                    }
                }

                // Age is optional but must be sane if provided
                $age = null;
                if (isset($data['age']) && $data['age'] !== '' && $data['age'] !== null) {
                    $ageVal = filter_var($data['age'], FILTER_VALIDATE_INT);
                    if ($ageVal === false || $ageVal < 1 || $ageVal > 120) {
                        $missing[] = 'Age (must be a number between 1 and 120)';
                    } else {
                        $age = $ageVal;
                    }
                }

                // ── 2. Reject row if anything is missing ─────────────────────
                if (!empty($missing)) {
                    $skipped++;
                    $errors[] = [
                        'row'     => $rowNumber,
                        'name'    => $displayName,
                        'missing' => $missing,
                        'reason'  => 'Missing: ' . implode(', ', $missing),
                    ];
                    continue;
                }

                // ── 3. Duplicate / transfer handling ─────────────────────────
                $existingStudent = Student::where('lrn', $lrn)->with('teacher')->first();
                if ($existingStudent) {
                    if ($existingStudent->teacher_id == $teacher->id) {
                        $skipped++;
                        $errors[] = [
                            'row'     => $rowNumber,
                            'name'    => $displayName,
                            'missing' => [],
                            'reason'  => 'Student already exists in your class.',
                        ];
                    } elseif ($existingStudent->status !== 'unenrolled') {
                        // Blocked — still enrolled under another teacher
                        $otherTeacher = $existingStudent->teacher;
                        $teacherName  = $otherTeacher
                            ? trim($otherTeacher->first_name . ' ' . $otherTeacher->last_name)
                            : 'another teacher';
                        $skipped++;
                        $errors[] = [
                            'row'     => $rowNumber,
                            'name'    => $displayName,
                            'missing' => [],
                            'reason'  => "Student is already enrolled to Teacher {$teacherName}.",
                        ];
                    } else {
                        // Student is unenrolled — transfer to this teacher's class
                        $existingStudent->teacher_id = $teacher->id;
                        $existingStudent->school_id  = $teacher->school_id;
                        $existingStudent->status     = 'active';
                        $existingStudent->save();
                        $imported++;
                        $transfers[] = $displayName;
                    }
                    continue;
                }

                // ── 4. Parse name (Last, First  OR  First Last) ───────────────
                if (str_contains($fullName, ',')) {
                    [$lastName, $firstName] = array_map('trim', explode(',', $fullName, 2));
                } else {
                    $parts     = explode(' ', $fullName);
                    $lastName  = count($parts) > 1 ? array_pop($parts) : '';
                    $firstName = implode(' ', $parts);
                }

                // ── 5. PIN — always last 4 digits of LRN ─────────────────────
                $pin = substr($lrn, -4);

                // ── 6. Determine nullable fields ──────────────────────────────
                $gradeLevel = in_array($programType, $needGradeSection)
                    ? trim((string) ($data['grade_level'] ?? ''))
                    : (trim((string) ($data['grade_level'] ?? '')) ?: null);

                $section = in_array($programType, $needGradeSection)
                    ? trim((string) ($data['section'] ?? ''))
                    : (trim((string) ($data['section'] ?? '')) ?: null);

                $schoolYear = trim((string) ($data['school_year'] ?? '')) ?: null;

                // ── 7. Create User + Student ──────────────────────────────────
                $username = $this->generateUniqueUsername($firstName, $lastName);

                $user = User::create([
                    'name'     => trim($firstName . ' ' . $lastName),
                    'username' => $username,
                    'email'    => $lrn,
                    'password' => Hash::make($lrn),
                    'role'     => 'student',
                    'status'   => 'active',
                ]);

                Student::create([
                    'user_id'          => $user->id,
                    'teacher_id'       => $teacher->id,
                    'school_id'        => $teacher->school_id,
                    'lrn'              => $lrn,
                    'pin'              => $pin,
                    'first_name'       => $firstName,
                    'last_name'        => $lastName,
                    'age'              => $age,
                    'grade_level'      => $gradeLevel,
                    'section'          => $section,
                    'school_year'      => $schoolYear,
                    'fsl_mastery_level'=> $masteryLevel,
                    'program_type'     => $programType,
                ]);

                $imported++;
            }

            DB::commit();

            return response()->json([
                'success'   => true,
                'total'     => count($studentsData),
                'imported'  => $imported,
                'skipped'   => $skipped,
                'transfers' => count($transfers),
                'errors'    => $errors,
                'message'   => "Successfully imported {$imported} students. Skipped {$skipped} records.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Bulk import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update editable student fields.
     * School year must be in YYYY-YYYY format where the second year = first + 1.
     */
    public function update(Request $request, $id)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $student = Student::where('student_id', $id)
                          ->where('teacher_id', $teacher->id)
                          ->firstOrFail();

        $validated = $request->validate([
            'full_name'         => 'required|string|max:255',
            'age'               => 'nullable|integer|min:1|max:120',
            'program_type'      => 'required|in:Regular,Inclusion,SPED,Home-based,Self-contained,Transition',
            'grade_level'       => 'nullable|string|max:50',
            'section'           => 'nullable|string|max:100',
            'lrn'               => ['nullable', 'digits:12',
                function ($attribute, $value, $fail) use ($student) {
                    if (empty($value)) return;
                    $exists = Student::where('lrn', $value)
                                     ->where('student_id', '!=', $student->student_id)
                                     ->exists();
                    if ($exists) $fail('This LRN is already assigned to another student.');
                },
            ],
            'school_year'       => ['nullable', 'string', 'max:20',
                function ($attribute, $value, $fail) {
                    if (empty($value)) return;
                    // Must be YYYY-YYYY
                    if (!preg_match('/^(\d{4})-(\d{4})$/', $value, $m)) {
                        $fail('School year must be in YYYY-YYYY format (e.g. 2024-2025).');
                        return;
                    }
                    $y1 = (int) $m[1];
                    $y2 = (int) $m[2];
                    if ($y2 !== $y1 + 1) {
                        $fail("School year is invalid — the second year must be exactly one year after the first (e.g. {$y1}-" . ($y1 + 1) . ').');
                    }
                    $currentYear = (int) date('Y');
                    if ($y1 < 2000 || $y1 > $currentYear + 2) {
                        $fail("School year {$value} is out of a reasonable range.");
                    }
                },
            ],
            'fsl_mastery_level' => 'nullable|in:Beginner,Intermediate,Advanced',
        ]);

        // Parse name (Last, First  OR  First Last)
        $fullName = trim($validated['full_name']);
        if (str_contains($fullName, ',')) {
            [$lastName, $firstName] = array_map('trim', explode(',', $fullName, 2));
        } else {
            $parts    = explode(' ', $fullName);
            $lastName = count($parts) > 1 ? array_pop($parts) : '';
            $firstName = implode(' ', $parts);
        }

        // Grade / section only required for Regular + Inclusion
        $needGradeSection = in_array($validated['program_type'], ['Regular', 'Inclusion']);

        $student->update([
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'age'               => $validated['age'] ?? $student->age,
            'program_type'      => $validated['program_type'],
            'grade_level'       => $needGradeSection ? ($validated['grade_level'] ?? null) : ($validated['grade_level'] ?? null),
            'section'           => $validated['section'] ?? null,
            'school_year'       => $validated['school_year'] ?? null,
            'lrn'               => $validated['lrn'] ?? $student->lrn,
            'fsl_mastery_level' => $validated['fsl_mastery_level'] ?? $student->fsl_mastery_level,
        ]);

        // Also update the linked user's display name and email (LRN) if changed
        if ($student->user_id) {
            $userUpdate = ['name' => trim($firstName . ' ' . $lastName)];
            if (!empty($validated['lrn'])) {
                $userUpdate['email'] = $validated['lrn'];
            }
            \App\Models\User::where('id', $student->user_id)->update($userUpdate);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student details updated successfully.',
        ]);
    }

    /**
     * Return full student details as JSON for the Student Details modal.
     */
    public function show($id)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $student = Student::where('student_id', $id)
                          ->where('teacher_id', $teacher->id)
                          ->with(['promotions'])
                          ->firstOrFail();

        return response()->json([
            'success' => true,
            'student' => $this->getStudentDetailsResponse($student),
        ]);
    }

    /**
     * Format student details response.
     */
    private function getStudentDetailsResponse(Student $student)
    {
        $user = \App\Models\User::find($student->user_id);

        $xp  = $student->total_xp ?? 0;
        $lvl = $student->fsl_mastery_level;

        $xpThresholds = ['Beginner' => 300, 'Intermediate' => 600, 'Advanced' => 1000];
        $promotionMap = ['Beginner' => 'Intermediate', 'Intermediate' => 'Advanced', 'Advanced' => 'Completed'];
        $demotionMap  = ['Intermediate' => 'Beginner', 'Advanced' => 'Intermediate', 'Completed' => 'Advanced'];

        $promoteTo  = $promotionMap[$lvl] ?? null;
        $requiredXp = $xpThresholds[$lvl] ?? 0;
        $enoughXp   = $xp >= $requiredXp;
        $demoteTo   = $demotionMap[$lvl] ?? null;

        // ── Lesson-progress-based promotion eligibility ─────────────────
        // Count all published lessons belonging to modules at the student's current mastery level
        $lessonsTotal = 0;
        $lessonsCompleted = 0;
        $lessonsReady = false;

        if ($promoteTo !== null && $lvl !== 'Completed') {
            $lessonsTotal = \App\Models\Lesson::where('status', 'published')
                ->whereNull('deleted_at')
                ->whereHas('module', function ($q) use ($lvl) {
                    $q->where('mastery_level', $lvl);
                })
                ->count();

            if ($lessonsTotal > 0) {
                $completedIds = \App\Models\StudentLessonProgress::where('student_id', $student->student_id)
                    ->where('lesson_completed', true)
                    ->pluck('lesson_id')
                    ->toArray();

                // Only count completed lessons that belong to the student's current level modules
                $lessonsCompleted = \App\Models\Lesson::where('status', 'published')
                    ->whereNull('deleted_at')
                    ->whereIn('lesson_id', $completedIds)
                    ->whereHas('module', function ($q) use ($lvl) {
                        $q->where('mastery_level', $lvl);
                    })
                    ->count();

                $lessonsReady = $lessonsCompleted >= $lessonsTotal;
            }
        }

        return [
            'student_id'        => $student->student_id,
            'first_name'        => $student->first_name,
            'last_name'         => $student->last_name,
            'full_name'         => $student->first_name . ' ' . $student->last_name,
            'lrn'               => $student->lrn,
            'pin'               => $student->pin,
            'age'               => $student->age,
            'grade_level'       => $student->grade_level,
            'section'           => $student->section,
            'school_year'       => $student->school_year,
            'program_type'      => $student->program_type,
            'fsl_mastery_level' => $lvl,
            'status'            => $student->status,
            'total_xp'          => $xp,
            'level'             => $student->level ?? 1,
            'streak_days'       => $student->streak_days ?? 0,
            'last_activity_date'=> $student->last_activity_date,
            'created_at'        => $student->created_at?->format('M d, Y'),
            'updated_at'        => $student->updated_at?->format('M d, Y'),
            'email'             => $user?->email,
            'username'          => $user?->username,
            'avatar_url'        => 'https://ui-avatars.com/api/?name=' . urlencode($student->first_name . ' ' . $student->last_name) . '&background=0d326b&color=fff&rounded=true&size=128',
            'promote_to'        => $promoteTo,
            'demote_to'         => $demoteTo,
            'required_xp'       => $requiredXp,
            'enough_xp'         => $enoughXp,
            'xp_bar_pct'        => $requiredXp > 0 ? min(100, round($xp / $requiredXp * 100)) : 100,
            'lessons_total'     => $lessonsTotal,
            'lessons_completed' => $lessonsCompleted,
            'lessons_ready'     => $lessonsReady,
            'promotions'        => $student->promotions->map(fn($p) => [
                'from'   => $p->from_level,
                'to'     => $p->to_level,
                'xp'     => $p->xp_at_promotion,
                'date'   => $p->promoted_at?->format('M d, Y'),
                'forced' => (bool) $p->was_forced,
            ])->toArray(),
        ];
    }

    /**
     * Enroll (set status = active) a student.
     */
    public function enroll($id)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $student = Student::where('student_id', $id)
                          ->where('teacher_id', $teacher->id)
                          ->firstOrFail();

        if ($student->status === 'active') {
            return response()->json(['success' => false, 'message' => 'Student is already enrolled.'], 422);
        }

        $student->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => $student->first_name . ' ' . $student->last_name . ' has been enrolled successfully.',
            'student' => $this->getStudentDetailsResponse($student),
        ]);
    }

    /**
     * Unenroll (set status = inactive) a student.
     */
    public function unenroll($id)
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $student = Student::where('student_id', $id)
                          ->where('teacher_id', $teacher->id)
                          ->firstOrFail();

        if ($student->status === 'inactive') {
            return response()->json(['success' => false, 'message' => 'Student is already unenrolled.'], 422);
        }

        $student->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => $student->first_name . ' ' . $student->last_name . ' has been unenrolled.',
            'student' => $this->getStudentDetailsResponse($student),
        ]);
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

        // ── Lesson-completion check (primary eligibility) ────────────────
        $lessonsTotal = \App\Models\Lesson::where('status', 'published')
            ->whereNull('deleted_at')
            ->whereHas('module', fn($q) => $q->where('mastery_level', $currentLvl))
            ->count();

        $lessonsCompleted = 0;
        if ($lessonsTotal > 0) {
            $completedIds = \App\Models\StudentLessonProgress::where('student_id', $student->student_id)
                ->where('lesson_completed', true)
                ->pluck('lesson_id')
                ->toArray();
            $lessonsCompleted = \App\Models\Lesson::where('status', 'published')
                ->whereNull('deleted_at')
                ->whereIn('lesson_id', $completedIds)
                ->whereHas('module', fn($q) => $q->where('mastery_level', $currentLvl))
                ->count();
        }
        $meetsLessons = ($lessonsTotal === 0) || ($lessonsCompleted >= $lessonsTotal);

        // If lessons not completed and not forcing, reject
        if (!$meetsLessons && !$force) {
            return response()->json([
                'success'           => false,
                'message'           => "Student has only completed {$lessonsCompleted}/{$lessonsTotal} lessons for {$currentLvl}.",
                'lessons_total'     => $lessonsTotal,
                'lessons_completed' => $lessonsCompleted,
            ], 422);
        }

        $wasForced = !$meetsLessons;

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
                'was_forced'      => $wasForced,
                'promoted_at'     => now(),
            ]);

            // ─── 🔔 NOTIFY TEACHER (mastery promoted) ───────────────────
            try {
                $studentName = $student->first_name . ' ' . $student->last_name;
                TeacherNotification::createForTeacher(
                    teacherId: $teacher->id,
                    type:      'mastery_promoted',
                    title:     "⬆️ You promoted {$studentName}",
                    message:   "{$studentName} has been promoted from {$currentLvl} to {$targetLvl}" . ($meetsLessons ? '' : ' (manually forced)'),
                    data: [
                        'student_id' => $student->student_id,
                        'from_level' => $currentLvl,
                        'to_level'   => $targetLvl,
                        'xp'         => $xp,
                        'forced'     => $wasForced,
                    ],
                    actionUrl: '/reports?open_student=' . $student->student_id,
                );
            } catch (\Exception $e) { /* silent */ }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$student->first_name} {$student->last_name} has been promoted to {$targetLvl}!",
                'student' => $this->getStudentDetailsResponse($student),
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
                'student' => $this->getStudentDetailsResponse($student),
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
