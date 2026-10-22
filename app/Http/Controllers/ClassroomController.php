<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassroomController extends Controller
{
    private function getTeacher()
    {
        return Auth::user()->teacher ?? abort(403, 'Teacher profile not found.');
    }

    /**
     * Display Class Management view
     */
    public function index(Request $request)
    {
        $teacher = $this->getTeacher();
        $classrooms = Classroom::where('teacher_id', $teacher->id)
            ->withCount(['students' => function ($q) {
                $q->where('is_enrolled', true);
            }])
            ->with(['students' => function ($q) {
                $q->where('is_enrolled', true)->orderBy('first_name');
            }])
            ->orderBy('name')
            ->get();

        $activeClassroomId = session('active_classroom_id');

        $allStudents = Student::where('teacher_id', $teacher->id)
            ->where('is_enrolled', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $unassignedStudents = $allStudents->whereNull('classroom_id');

        return view('classes.index', compact('classrooms', 'activeClassroomId', 'allStudents', 'unassignedStudents'));
    }

    /**
     * Create a new classroom
     */
    public function store(Request $request)
    {
        $teacher = $this->getTeacher();

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'section'     => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,student_id',
        ]);

        $classroom = Classroom::create([
            'teacher_id'  => $teacher->id,
            'school_id'   => $teacher->school_id,
            'name'        => trim($validated['name']),
            'section'     => isset($validated['section']) ? trim($validated['section']) : null,
            'description' => $validated['description'] ?? null,
            'is_active'   => true,
        ]);

        if (!empty($validated['student_ids'])) {
            Student::where('teacher_id', $teacher->id)
                ->whereIn('student_id', $validated['student_ids'])
                ->update(['classroom_id' => $classroom->id]);

            if (\Illuminate\Support\Facades\Schema::hasTable('student_year_enrollments')) {
                \Illuminate\Support\Facades\DB::table('student_year_enrollments')
                    ->where('teacher_id', $teacher->id)
                    ->whereIn('student_id', $validated['student_ids'])
                    ->update(['classroom_id' => $classroom->id]);
            }
        }

        // Set the newly created class as active in session
        session(['active_classroom_id' => $classroom->id]);

        return redirect()->back()->with('success', "Class '{$classroom->name}' created successfully!");
    }

    /**
     * Update an existing classroom
     */
    public function update(Request $request, Classroom $classroom)
    {
        $teacher = $this->getTeacher();
        abort_unless((int) $classroom->teacher_id === (int) $teacher->id, 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'section'     => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $classroom->update([
            'name'        => trim($validated['name']),
            'section'     => isset($validated['section']) ? trim($validated['section']) : null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->back()->with('success', "Class '{$classroom->name}' updated successfully!");
    }

    /**
     * Delete a classroom
     */
    public function destroy(Classroom $classroom)
    {
        $teacher = $this->getTeacher();
        abort_unless((int) $classroom->teacher_id === (int) $teacher->id, 403);

        // Reset student assignments
        Student::where('classroom_id', $classroom->id)->update(['classroom_id' => null]);
        if (\Illuminate\Support\Facades\Schema::hasTable('student_year_enrollments')) {
            \Illuminate\Support\Facades\DB::table('student_year_enrollments')
                ->where('classroom_id', $classroom->id)
                ->update(['classroom_id' => null]);
        }

        if (session('active_classroom_id') == $classroom->id) {
            session()->forget('active_classroom_id');
        }

        $className = $classroom->name;
        $classroom->delete();

        return redirect()->back()->with('success', "Class '{$className}' removed.");
    }

    /**
     * Switch active classroom in session
     */
    public function switchClassroom(Request $request, $id = null)
    {
        $teacher = $this->getTeacher();

        if (empty($id) || $id === 'all' || $id == '0') {
            session()->forget('active_classroom_id');
        } else {
            $classroom = Classroom::where('id', $id)
                ->where('teacher_id', $teacher->id)
                ->firstOrFail();
            session(['active_classroom_id' => $classroom->id]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'active_classroom_id' => session('active_classroom_id'),
            ]);
        }

        return redirect()->back();
    }

    /**
     * Assign or move students to a classroom
     */
    public function assignStudents(Request $request, Classroom $classroom)
    {
        $teacher = $this->getTeacher();
        abort_unless((int) $classroom->teacher_id === (int) $teacher->id, 403);

        $validated = $request->validate([
            'student_ids'   => 'nullable|array',
            'student_ids.*' => 'exists:students,student_id',
        ]);

        $selectedIds = $validated['student_ids'] ?? [];

        // 1. Unassign students removed from this classroom
        Student::where('teacher_id', $teacher->id)
            ->where('classroom_id', $classroom->id)
            ->whereNotIn('student_id', $selectedIds)
            ->update(['classroom_id' => null]);

        if (\Illuminate\Support\Facades\Schema::hasTable('student_year_enrollments')) {
            \Illuminate\Support\Facades\DB::table('student_year_enrollments')
                ->where('teacher_id', $teacher->id)
                ->where('classroom_id', $classroom->id)
                ->whereNotIn('student_id', $selectedIds)
                ->update(['classroom_id' => null]);
        }

        // 2. Assign selected to this classroom
        if (!empty($selectedIds)) {
            Student::where('teacher_id', $teacher->id)
                ->whereIn('student_id', $selectedIds)
                ->update(['classroom_id' => $classroom->id]);

            if (\Illuminate\Support\Facades\Schema::hasTable('student_year_enrollments')) {
                \Illuminate\Support\Facades\DB::table('student_year_enrollments')
                    ->where('teacher_id', $teacher->id)
                    ->whereIn('student_id', $selectedIds)
                    ->update(['classroom_id' => $classroom->id]);
            }
        }

        return redirect()->back()->with('success', "Roster updated for '{$classroom->name}'!");
    }
}
