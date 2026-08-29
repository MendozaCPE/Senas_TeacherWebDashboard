<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\StudentPromotion;
use App\Models\TeacherNotification;
use App\Models\StudentNotification;
use App\Models\CheckpointExamAssignment;
use App\Models\CheckpointExam;
use App\Models\Module;  // ✅ ADD THIS LINE
use App\Models\LessonAssignment; 
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator; 
use Carbon\Carbon;

class StudentsController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        $totalStudents = 0;
        $newThisWeek   = 0;
        $students      = collect();

        $availableSchoolYears     = collect();
        $sidebarStudents          = collect();
        $teacherLessons           = collect();
        $promotionReadyCounts     = ['Beginner' => 0, 'Intermediate' => 0, 'Advanced' => 0];
        $promotionReadyStudentIds = ['Beginner' => [], 'Intermediate' => [], 'Advanced' => []];
        $allReadyStudentIds       = [];
        $activePromotableLevel    = '';

        if ($teacher) {
            $totalStudents = Student::where('teacher_id', $teacher->id)->count();
            $newThisWeek   = Student::where('teacher_id', $teacher->id)
                                    ->where('created_at', '>=', Carbon::now()->subWeek())
                                    ->count();

            // ── Sidebar: all active students with lesson scores (not paginated) ──
            $sidebarStudents = Student::where('teacher_id', $teacher->id)
                                      ->where('status', 'active')
                                      ->with(['assignments' => function ($q) {
                                          $q->select('student_id', 'lesson_id', 'score', 'status');
                                      }])
                                      ->get(['student_id', 'first_name', 'last_name', 'fsl_mastery_level', 'total_xp']);

            // ── Sidebar: Promotion Thresholds — how many active students at
            // each level have completed all lessons assigned to them at that
            // level (same rule as the individual student "Promotion" card).
            $promotionMap = ['Beginner' => 'Intermediate', 'Intermediate' => 'Advanced', 'Advanced' => 'Completed'];
            foreach ($sidebarStudents as $sbStudent) {
                $sbLevel = $sbStudent->fsl_mastery_level;
                if (!array_key_exists($sbLevel, $promotionReadyCounts)) {
                    continue;
                }
                $readiness = $this->computeLessonReadiness(
                    $sbStudent->student_id,
                    $sbLevel,
                    $promotionMap[$sbLevel] ?? null
                );
                if ($readiness['ready']) {
                    $promotionReadyCounts[$sbLevel]++;
                    $promotionReadyStudentIds[$sbLevel][] = $sbStudent->student_id;
                    $allReadyStudentIds[] = $sbStudent->student_id;
                }
            }

            // ── Sidebar: published lessons belonging to this teacher ──
            $teacherLessons = Lesson::where('teacher_id', $teacher->id)
                                    ->where('status', 'published')
                                    ->whereNull('deleted_at')
                                    ->orderBy('module_order')
                                    ->get(['lesson_id', 'title']);

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
                $activePromotableLevel = $filters['promotable_level'] ?? '';
            } else {
                $filters = session('students_filters', []);
                $search  = $filters['search']  ?? '';
                $level   = $filters['level']   ?? '';
                $program = $filters['program'] ?? '';
                $status  = $filters['status']  ?? 'active';
                $schoolYear = $filters['school_year'] ?? '';
                $activePromotableLevel = $filters['promotable_level'] ?? '';
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

            // Filter by promotable level if selected via sidebar boxes
            if (!empty($activePromotableLevel)) {
                if ($activePromotableLevel === 'all') {
                    $query->whereIn('student_id', !empty($allReadyStudentIds) ? $allReadyStudentIds : [-1]);
                } elseif (isset($promotionReadyStudentIds[$activePromotableLevel])) {
                    $ids = $promotionReadyStudentIds[$activePromotableLevel];
                    $query->whereIn('student_id', !empty($ids) ? $ids : [-1]);
                }
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

        return view('students', compact(
            'totalStudents', 'newThisWeek', 'students', 'availableSchoolYears',
            'sidebarStudents', 'teacherLessons', 'promotionReadyCounts',
            'promotionReadyStudentIds', 'allReadyStudentIds', 'activePromotableLevel'
        ));
    }

    /**
     * Accept filters via POST, store them in the session, then redirect to
     * the clean /students URL (no visible query string in the browser).
     */
    public function applyFilter(Request $request)
    {
        $validated = $request->validate([
            'search'           => ['nullable', 'string', 'max:100'],
            'level'            => ['nullable', 'string', 'in:Beginner,Intermediate,Advanced,Completed,'],
            'program'          => ['nullable', 'string', 'in:Regular,Inclusion,SPED,Self-contained,Transition,'],
            'status'           => ['nullable', 'string', 'in:active,inactive,all,'],
            'school_year'      => ['nullable', 'string', 'max:50'],
            'promotable_level' => ['nullable', 'string', 'in:Beginner,Intermediate,Advanced,all,'],
        ]);

        // Clear filter if user submitted an empty/reset form
        if (($validated['search'] ?? '') === ''
            && ($validated['level'] ?? '') === ''
            && ($validated['program'] ?? '') === ''
            && ($validated['school_year'] ?? '') === ''
            && ($validated['promotable_level'] ?? '') === ''
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

            if ($student->status === 'inactive') {
                return response()->json(['exists' => true, 'status' => 'inactive']);
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
        'program_type' => 'required|in:Regular,Inclusion,Transition,Self-contained',
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
        'lesson_ids' => 'nullable|array',
        'lesson_ids.*' => 'exists:lessons,lesson_id',
    ]);

    // Split full name
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
            if ($existingStudent->status !== 'inactive') {
                $otherTeacher = $existingStudent->teacher;
                $teacherName = $otherTeacher
                    ? trim($otherTeacher->first_name . ' ' . $otherTeacher->last_name)
                    : 'another teacher';

                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'lrn' => ["This student is already enrolled to Teacher {$teacherName}."]
                    ]
                ], 422);
            }

            $existingStudent->teacher_id = $teacher->id;
            $existingStudent->school_id = $teacher->school_id;
            $existingStudent->status = 'active';
            $existingStudent->save();

            return response()->json([
                'success' => true,
                'message' => 'Student has been successfully enrolled in your class.',
                'student' => $existingStudent
            ]);
        }
    }

    $pin = substr($lrn, -4);
    $username = $this->generateUniqueUsername($firstName, $lastName);

    DB::beginTransaction();
    try {
        $user = User::create([
            'name' => trim($firstName . ' ' . $lastName),
            'username' => $username,
            'email' => $lrn,
            'password' => Hash::make(substr($lrn, -4)), // Default password = last 4 digits of LRN
            'role' => 'student',
            'status' => 'active',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'teacher_id' => $teacher->id,
            'school_id' => $teacher->school_id,
            'lrn' => $lrn,
            'pin' => Hash::make($pin), // Store PIN as bcrypt hash
            'first_name' => $firstName,
            'last_name' => $lastName,
            'age' => $request->age,
            'grade_level' => $showGradeSection ? $request->grade_level : null,
            'section' => $showGradeSection ? $request->section : null,
            'school_year' => $request->school_year,
            'fsl_mastery_level' => $request->fsl_mastery_level,
            'program_type' => $programType,
            'status' => 'active',
        ]);

 // 🔥 FIX: Filter out invalid IDs
    $lessonIds = $request->input('lesson_ids', []);
    $lessonIds = array_filter($lessonIds, function($id) {
        return $id !== null && $id !== '' && $id !== '0' && $id !== 0 && $id !== 'NaN';
    });
    $lessonIds = array_map('strval', $lessonIds);
    
    $lessonIdsOnly = [];
    $examIdsOnly = [];
    
    foreach ($lessonIds as $id) {
        if (strpos($id, 'exam_') === 0) {
            $examId = (int) str_replace('exam_', '', $id);
            if ($examId > 0) {
                $examIdsOnly[] = $examId;
            }
        } else {
            $lessonId = (int) $id;
            if ($lessonId > 0) {
                $lessonIdsOnly[] = $lessonId;
            }
        }
    }

// Assign lessons
if (!empty($lessonIdsOnly)) {
    foreach ($lessonIdsOnly as $lessonId) {
                // Only create assignment if it doesn't exist
                $exists = LessonAssignment::where('student_id', $student->student_id)
                    ->where('lesson_id', $lessonId)
                    ->exists();

                if (!$exists) {
                    $lesson = Lesson::find($lessonId);
                    $isLocked = true;

                    if ($lesson) {
                        $firstLesson = Lesson::where('module_id', $lesson->module_id)
                            ->where('status', 'published')
                            ->whereNull('deleted_at')
                            ->orderBy('module_order', 'asc')
                            ->first();

                        if ($firstLesson && $firstLesson->lesson_id == $lessonId) {
                            $isLocked = false;
                        }
                    }

                    LessonAssignment::create([
                        'lesson_id' => $lessonId,
                        'student_id' => $student->student_id,
                        'assigned_at' => now(),
                        'status' => 'pending',
                        'is_locked' => $isLocked,
                        'notified' => false,
                    ]);
                }
            }

            $this->createLessonAssignmentNotifications($student, $lessonIdsOnly);

        }

        // Assign checkpoint exams
if (!empty($examIdsOnly)) {
    foreach ($examIdsOnly as $examId) {
        $exists = CheckpointExamAssignment::where('student_id', $student->student_id)
            ->where('exam_id', $examId)
            ->exists();

        if (!$exists) {
            CheckpointExamAssignment::create([
                'exam_id' => $examId,
                'student_id' => $student->student_id,
                'assigned_at' => now(),
                'status' => 'pending',
                'is_locked' => true,
                'notified' => false,
            ]);
        }
    }
    $this->createExamAssignmentNotifications($student, $examIdsOnly);
}

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Student added successfully!',
            'student' => $student,
            'assigned_count' => count($lessonIds),
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
        // Brand-new student records only (excludes transfers of existing students) —
        // these are the ones the teacher still needs to assign lessons to.
        $createdStudents = [];

        // Valid values for enumerated fields
        $validPrograms  = ['Regular', 'Inclusion', 'Transition', 'Self-contained'];
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
                    } elseif ($existingStudent->status !== 'inactive') {
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
                    'password' => Hash::make(substr($lrn, -4)), // Default password = last 4 digits of LRN
                    'role'     => 'student',
                    'status'   => 'active',
                ]);

                $newStudent = Student::create([
                    'user_id'          => $user->id,
                    'teacher_id'       => $teacher->id,
                    'school_id'        => $teacher->school_id,
                    'lrn'              => $lrn,
                    'pin'              => Hash::make($pin), // Store PIN as bcrypt hash
                    'first_name'       => $firstName,
                    'last_name'        => $lastName,
                    'age'              => $age,
                    'grade_level'      => $gradeLevel,
                    'section'          => $section,
                    'school_year'      => $schoolYear,
                    'fsl_mastery_level'=> $masteryLevel,
                    'program_type'     => $programType,
                    'status'           => 'active',
                ]);

                $imported++;
                $createdStudents[] = [
                    'student_id' => $newStudent->student_id,
                    'full_name'  => trim($firstName . ' ' . $lastName),
                ];
            }

            DB::commit();

            return response()->json([
                'success'          => true,
                'total'            => count($studentsData),
                'imported'         => $imported,
                'skipped'          => $skipped,
                'transfers'        => count($transfers),
                'errors'           => $errors,
                'created_students' => $createdStudents,
                'message'          => "Successfully imported {$imported} students. Skipped {$skipped} records.",
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
            'program_type'      => 'required|in:Regular,Inclusion,Self-contained,Transition',
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
    /**
     * Lesson-completion-based promotion eligibility for one student, at
     * their current mastery level. Shared by the student-details "Promotion"
     * card and the sidebar "Promotion Thresholds" widget so both agree on
     * what "ready" means.
     *
     * @return array{total:int, completed:int, ready:bool}
     */
    private function computeLessonReadiness(int $studentId, ?string $currentLevel, ?string $promoteTo): array
    {
        $lessonsTotal     = 0;
        $lessonsCompleted = 0;
        $lessonsReady     = false;

        if ($promoteTo !== null && $currentLevel !== 'Completed') {
            // Get all lessons assigned to this student at this level
            $assignedLessonIds = LessonAssignment::where('student_id', $studentId)
                ->whereHas('lesson', function($query) use ($currentLevel) {
                    $query->where('status', 'published')
                        ->whereNull('deleted_at')
                        ->whereHas('module', function($q) use ($currentLevel) {
                            $q->where('mastery_level', $currentLevel);
                        });
                })
                ->pluck('lesson_id')
                ->toArray();

            $lessonsTotal = count($assignedLessonIds);

            // If no assigned lessons, the student is "lesson-ready" (no lessons to complete)
            if ($lessonsTotal === 0) {
                $lessonsReady = true;
            } else {
                // Get completed lesson IDs from student_lesson_progress
                $completedIds = \App\Models\StudentLessonProgress::where('student_id', $studentId)
                    ->where('lesson_completed', true)
                    ->pluck('lesson_id')
                    ->toArray();

                // Count only assigned lessons that are completed
                $lessonsCompleted = count(array_intersect($assignedLessonIds, $completedIds));

                $lessonsReady = $lessonsCompleted >= $lessonsTotal;
            }
        }

        return [
            'total'     => $lessonsTotal,
            'completed' => $lessonsCompleted,
            'ready'     => $lessonsReady,
        ];
    }

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
    $readiness        = $this->computeLessonReadiness($student->student_id, $lvl, $promoteTo);
    $lessonsTotal     = $readiness['total'];
    $lessonsCompleted = $readiness['completed'];
    $lessonsReady     = $readiness['ready'];

    return [
        'student_id'        => $student->student_id,
        'first_name'        => $student->first_name,
        'last_name'         => $student->last_name,
        'full_name'         => $student->first_name . ' ' . $student->last_name,
        'lrn'               => $student->lrn,
        'pin'               => '****', // Never expose raw or hashed PIN to the UI
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

    // ── Lesson-completion check (ONLY assigned lessons) ────────────────
    $assignedLessonIds = LessonAssignment::where('student_id', $student->student_id)
        ->whereHas('lesson', function($query) use ($currentLvl) {
            $query->where('status', 'published')
                ->whereNull('deleted_at')
                ->whereHas('module', function($q) use ($currentLvl) {
                    $q->where('mastery_level', $currentLvl);
                });
        })
        ->pluck('lesson_id')
        ->toArray();

    $lessonsTotal = count($assignedLessonIds);

    // 🔥 If no assigned lessons, the student is lesson-ready
    if ($lessonsTotal === 0) {
        $meetsLessons = true;
        $lessonsCompleted = 0;
    } else {
        $completedIds = \App\Models\StudentLessonProgress::where('student_id', $student->student_id)
            ->where('lesson_completed', true)
            ->pluck('lesson_id')
            ->toArray();

        $lessonsCompleted = count(array_intersect($assignedLessonIds, $completedIds));
        $meetsLessons = ($lessonsCompleted >= $lessonsTotal);
    }

    // If lessons not completed and not forcing, reject
    if (!$meetsLessons && !$force) {
        return response()->json([
            'success'           => false,
            'message'           => "Student has only completed {$lessonsCompleted}/{$lessonsTotal} assigned lessons for {$currentLvl}.",
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


/**
 * Get published lessons for the teacher, for use when adding a brand-new
 * student — before the student record exists, so nothing can be marked
 * as already assigned.
 * GET /students/lessons-for-new-student
 */
public function getLessonsForNewStudent()
{
    $teacher = Auth::user()->teacher;
    if (!$teacher) {
        return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
    }

    // Get modules with lessons for THIS teacher
    $modules = Module::where('teacher_id', $teacher->id)
        ->with(['lessons' => function($query) use ($teacher) {
            $query->where('status', 'published')
                ->whereNull('deleted_at')
                ->where('teacher_id', $teacher->id)
                ->orderBy('module_order');
        }])
        ->orderBy('module_order')
        ->get();

    // 🔥 FIX: Get ALL checkpoint exams for THIS teacher, grouped by module_id
    $checkpointExams = CheckpointExam::where('teacher_id', $teacher->id)
        ->where('status', 'published')
        ->get()
        ->groupBy('module_id');  // ← Changed from keyBy to groupBy

    $modulesData = $modules->map(function($module) use ($checkpointExams) {
        $lessons = $module->lessons->map(function($lesson) {
            return [
                'lesson_id' => $lesson->lesson_id,
                'title' => $lesson->title,
                'description' => $lesson->description,
                'difficulty' => $lesson->difficulty,
                'is_assigned' => false,
                'type' => 'lesson',
            ];
        })->toArray();

        // 🔥 FIX: Add ALL checkpoint exams for this module
        if (isset($checkpointExams[$module->module_id])) {
            foreach ($checkpointExams[$module->module_id] as $exam) {
                $lessons[] = [
                    'lesson_id' => 'exam_' . $exam->exam_id,
                    'exam_id' => $exam->exam_id,
                    'title' => $exam->title,
                    'description' => $exam->description ?? 'Checkpoint Exam',
                    'is_assigned' => false,
                    'type' => 'checkpoint_exam',
                    'total_points' => $exam->total_points,
                    'passing_score' => $exam->passing_score,
                ];
            }
        }

        return [
            'module_id' => $module->module_id,
            'module_title' => $module->title,
            'module_order' => $module->module_order,
            'lessons' => $lessons,
            'all_assigned' => false,
        ];
    })->filter(function($module) {
        return !empty($module['lessons']);
    })->values();

    return response()->json([
        'success' => true,
        'modules' => $modulesData,
        'assigned_count' => 0,
    ]);
}

public function getAvailableLessons($id)
{
    $teacher = Auth::user()->teacher;
    if (!$teacher) {
        return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
    }

    $student = Student::where('student_id', $id)
        ->where('teacher_id', $teacher->id)
        ->firstOrFail();

    // Get modules with lessons for THIS teacher
    $modules = Module::where('teacher_id', $teacher->id)
        ->with(['lessons' => function($query) use ($teacher) {
            $query->where('status', 'published')
                ->whereNull('deleted_at')
                ->where('teacher_id', $teacher->id)
                ->orderBy('module_order');
        }])
        ->orderBy('module_order')
        ->get();

    // 🔥 FIX: Get ALL checkpoint exams for THIS teacher, grouped by module_id
    $checkpointExams = CheckpointExam::where('teacher_id', $teacher->id)
        ->where('status', 'published')
        ->get()
        ->groupBy('module_id');  // ← Changed from keyBy to groupBy

    // Get currently assigned lesson IDs
    $assignedLessonIds = LessonAssignment::where('student_id', $student->student_id)
        ->pluck('lesson_id')
        ->toArray();

    // Get currently assigned checkpoint exam IDs
    $assignedExamIds = CheckpointExamAssignment::where('student_id', $student->student_id)
        ->pluck('exam_id')
        ->toArray();

    $modulesData = $modules->map(function($module) use ($assignedLessonIds, $assignedExamIds, $checkpointExams) {
        $lessons = $module->lessons->map(function($lesson) use ($assignedLessonIds) {
            return [
                'lesson_id' => $lesson->lesson_id,
                'title' => $lesson->title,
                'description' => $lesson->description,
                'difficulty' => $lesson->difficulty,
                'is_assigned' => in_array($lesson->lesson_id, $assignedLessonIds),
                'type' => 'lesson',
            ];
        })->toArray();

        // 🔥 FIX: Add ALL checkpoint exams for this module
        if (isset($checkpointExams[$module->module_id])) {
            foreach ($checkpointExams[$module->module_id] as $exam) {
                $lessons[] = [
                    'lesson_id' => 'exam_' . $exam->exam_id,
                    'exam_id' => $exam->exam_id,
                    'title' => $exam->title,
                    'description' => $exam->description ?? 'Checkpoint Exam',
                    'is_assigned' => in_array($exam->exam_id, $assignedExamIds),
                    'type' => 'checkpoint_exam',
                    'total_points' => $exam->total_points,
                    'passing_score' => $exam->passing_score,
                ];
            }
        }

        return [
            'module_id' => $module->module_id,
            'module_title' => $module->title,
            'module_order' => $module->module_order,
            'lessons' => $lessons,
            'all_assigned' => collect($lessons)->every(fn($l) => $l['is_assigned']),
        ];
    })->filter(function($module) {
        return !empty($module['lessons']);
    })->values();

    return response()->json([
        'success' => true,
        'student' => [
            'id' => $student->student_id,
            'name' => $student->first_name . ' ' . $student->last_name,
        ],
        'modules' => $modulesData,
        'assigned_count' => count($assignedLessonIds) + count($assignedExamIds),
    ]);
}
/**
 * Update lesson assignments for a student
 * POST /students/{id}/assign-lessons
 */
public function assignLessons(Request $request, $id)
{
    $teacher = Auth::user()->teacher;
    if (!$teacher) {
        return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
    }

    $student = Student::where('student_id', $id)
        ->where('teacher_id', $teacher->id)
        ->firstOrFail();

    $validator = Validator::make($request->all(), [
        'lesson_ids' => 'required|array',
        'lesson_ids.*' => 'string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Invalid data',
            'errors' => $validator->errors()
        ], 422);
    }

    // 🔥 FIX: Filter out invalid IDs and convert to strings
    $lessonIds = array_filter($request->lesson_ids, function($id) {
        // Filter out empty, null, NaN, or '0' values
        return $id !== null && $id !== '' && $id !== '0' && $id !== 0 && $id !== 'NaN';
    });
    
    // Convert all valid lesson_ids to strings
    $lessonIds = array_map('strval', $lessonIds);

    // 🔥 FIX: If no valid lesson IDs, return early
    if (empty($lessonIds)) {
        return response()->json([
            'success' => true,
            'message' => 'No valid lessons selected.',
            'assigned_count' => 0,
        ]);
    }

    $currentLessonAssignments = LessonAssignment::where('student_id', $student->student_id)
        ->pluck('lesson_id')
        ->toArray();
    
    $currentExamAssignments = CheckpointExamAssignment::where('student_id', $student->student_id)
        ->pluck('exam_id')
        ->toArray();

    DB::beginTransaction();
    try {
        // Separate lesson IDs from exam IDs
        $lessonIdsOnly = [];
        $examIdsOnly = [];
        foreach ($lessonIds as $id) {
            // Skip any invalid IDs
            if (empty($id) || $id === 'NaN' || $id === '0') {
                continue;
            }
            
            if (strpos($id, 'exam_') === 0) {
                $examId = (int) str_replace('exam_', '', $id);
                if ($examId > 0) {
                    $examIdsOnly[] = $examId;
                }
            } else {
                $lessonId = (int) $id;
                if ($lessonId > 0) {
                    $lessonIdsOnly[] = $lessonId;
                }
            }
        }

        // ── Update Lesson Assignments ──────────────────────────────────
        $toRemoveLessons = array_diff($currentLessonAssignments, $lessonIdsOnly);
        if (!empty($toRemoveLessons)) {
            LessonAssignment::where('student_id', $student->student_id)
                ->whereIn('lesson_id', $toRemoveLessons)
                ->delete();
        }

        $toAddLessons = array_diff($lessonIdsOnly, $currentLessonAssignments);
        foreach ($toAddLessons as $lessonId) {
            $lesson = Lesson::find($lessonId);
            if (!$lesson) continue; // Skip if lesson doesn't exist
            
            $isLocked = true;
            $firstLesson = Lesson::where('module_id', $lesson->module_id)
                ->where('status', 'published')
                ->whereNull('deleted_at')
                ->orderBy('module_order', 'asc')
                ->first();
            
            if ($firstLesson && $firstLesson->lesson_id == $lessonId) {
                $isLocked = false;
            }

            LessonAssignment::create([
                'lesson_id' => $lessonId,
                'student_id' => $student->student_id,
                'assigned_at' => now(),
                'status' => 'pending',
                'is_locked' => $isLocked,
                'notified' => false,
            ]);
        }

        // ── Update Checkpoint Exam Assignments ──────────────────────
        $toRemoveExams = array_diff($currentExamAssignments, $examIdsOnly);
        if (!empty($toRemoveExams)) {
            CheckpointExamAssignment::where('student_id', $student->student_id)
                ->whereIn('exam_id', $toRemoveExams)
                ->delete();
        }

        $toAddExams = array_diff($examIdsOnly, $currentExamAssignments);
        foreach ($toAddExams as $examId) {
            $exam = CheckpointExam::find($examId);
            if ($exam) {
                CheckpointExamAssignment::create([
                    'exam_id' => $examId,
                    'student_id' => $student->student_id,
                    'assigned_at' => now(),
                    'status' => 'pending',
                    'is_locked' => true,
                    'notified' => false,
                ]);
            }
        }

        // If student is active, notify them about new lessons
        if ($student->status === 'active' && (!empty($toAddLessons) || !empty($toAddExams))) {
            if (!empty($toAddLessons)) {
                $this->createLessonAssignmentNotifications($student, $toAddLessons);
            }
            if (!empty($toAddExams)) {
                $this->createExamAssignmentNotifications($student, $toAddExams);
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Lesson assignments updated successfully!',
            'assigned_count' => count($lessonIdsOnly) + count($examIdsOnly),
            'added_lessons' => count($toAddLessons),
            'added_exams' => count($toAddExams),
            'removed' => count($toRemoveLessons) + count($toRemoveExams),
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to update assignments: ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * Create notifications for newly assigned checkpoint exams
 */
private function createExamAssignmentNotifications($student, $examIds)
{
    $exams = CheckpointExam::whereIn('exam_id', $examIds)->get();
    
    foreach ($exams as $exam) {
        $exists = StudentNotification::where('student_id', $student->student_id)
            ->where('type', 'checkpoint_exam')
            ->where('data->exam_id', $exam->exam_id)
            ->exists();

        if (!$exists) {
            StudentNotification::create([
                'student_id' => $student->student_id,
                'type' => 'checkpoint_exam',
                'title' => '📝 Checkpoint Exam Assigned!',
                'message' => "\"{$exam->title}\" is ready for you to take! 🎯",
                'icon' => 'trophy',
                'color' => '#8B5CF6',
                'data' => ['exam_id' => $exam->exam_id, 'exam_title' => $exam->title],
                'action_url' => '/checkpoint-exams',
                'is_read' => false,
            ]);
        }
    }
}

/**
 * Create notifications for newly assigned lessons
 */
private function createLessonAssignmentNotifications($student, $lessonIds)
{
    $lessons = Lesson::whereIn('lesson_id', $lessonIds)->get();
    
    foreach ($lessons as $lesson) {
        $exists = StudentNotification::where('student_id', $student->student_id)
            ->where('type', 'lesson')
            ->where('data->lesson_id', $lesson->lesson_id)
            ->exists();

        if (!$exists) {
            StudentNotification::create([
                'student_id' => $student->student_id,
                'type' => 'lesson',
                'title' => '📚 New Lesson Assigned!',
                'message' => "\"{$lesson->title}\" has been assigned to you. Start learning today! 🎓",
                'icon' => 'book',
                'color' => '#3B82F6',
                'data' => ['lesson_id' => $lesson->lesson_id, 'lesson_title' => $lesson->title],
                'action_url' => '/lessons',
                'is_read' => false,
            ]);
        }
    }
}

/**
 * Modified enroll method to show assignment modal
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

    // Return the student data with a flag that assignments need to be managed
    return response()->json([
        'success' => true,
        'message' => $student->first_name . ' ' . $student->last_name . ' has been enrolled successfully.',
        'student' => $this->getStudentDetailsResponse($student),
        'show_assignment_modal' => true, // Flag to trigger the assignment modal
    ]);
}


}