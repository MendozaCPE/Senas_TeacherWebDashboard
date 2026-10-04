<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$teacherId = 2;
$teacher = App\Models\Teacher::find($teacherId);
$syFromTable = App\Models\SchoolYear::all();
$activeSchoolYear = $syFromTable->firstWhere('status', 'active');

foreach (['2024-2025', '2025-2026', '2026-2027', '2027-2028', ''] as $schoolYear) {
    $query = App\Models\Student::where('teacher_id', $teacherId);
    $status = ''; // default

    $isActiveSySelected = empty($schoolYear) || $schoolYear === ($activeSchoolYear?->name ?? '');
    $selectedSyRecord = !empty($schoolYear) ? $syFromTable->firstWhere('name', $schoolYear) : $activeSchoolYear;
    $isArchivedSy = $selectedSyRecord && $selectedSyRecord->status === 'archived';

    if (!empty($status) && $status !== 'all') {
        if ($status === 'active' || $status === 'enrolled') {
            if (!$isArchivedSy) {
                $query->where('is_enrolled', true);
            }
        } elseif ($status === 'inactive' || $status === 'unenrolled') {
            $query->where('is_enrolled', false);
        } else {
            $query->where('status', $status);
        }
    } elseif ($isActiveSySelected && !$isArchivedSy) {
        $query->where('is_enrolled', true);
    }

    if (!empty($schoolYear)) {
        $syRecord = $syFromTable->firstWhere('name', $schoolYear);
        $isThisAnArchivedYear = $syRecord && $syRecord->status === 'archived';

        if ($isThisAnArchivedYear) {
            $syId = $syRecord->id;
            $teacherLessonIds = App\Models\Lesson::where('teacher_id', $teacherId)->whereNull('deleted_at')->pluck('lesson_id');

            $query->where(function ($q) use ($syId, $schoolYear, $teacherLessonIds) {
                $q->where('school_year', $schoolYear)
                  ->orWhereExists(function ($sub) use ($syId, $teacherLessonIds) {
                      $sub->select(DB::raw(1))
                          ->from('lesson_assignments')
                          ->whereColumn('lesson_assignments.student_id', 'students.student_id')
                          ->where('lesson_assignments.school_year_id', $syId)
                          ->whereIn('lesson_assignments.lesson_id', $teacherLessonIds);
                  })
                  ->orWhereExists(function ($sub) use ($syId, $teacherLessonIds) {
                      $sub->select(DB::raw(1))
                          ->from('student_lesson_progress')
                          ->whereColumn('student_lesson_progress.student_id', 'students.student_id')
                          ->where('student_lesson_progress.school_year_id', $syId)
                          ->whereIn('student_lesson_progress.lesson_id', $teacherLessonIds);
                  });
            });
        } else {
            $query->where('school_year', $schoolYear);
        }
    }

    echo "Students Page for SY [{$schoolYear}]: " . $query->pluck('first_name')->implode(', ') . "\n";
}
