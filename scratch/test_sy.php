<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$teacherId = 2;
$teacherLessonIds = App\Models\Lesson::where('teacher_id', $teacherId)->pluck('lesson_id');

echo "--- Students in DB ---\n";
foreach (App\Models\Student::where('teacher_id', $teacherId)->get() as $s) {
    echo "ID: {$s->student_id} | Name: {$s->first_name} {$s->last_name} | SY: {$s->school_year} | is_enrolled: " . ($s->is_enrolled ? '1' : '0') . " | status: {$s->status}\n";
}

echo "\n--- Lesson Assignments per SY ID ---\n";
$las = DB::table('lesson_assignments')
    ->join('students', 'students.student_id', '=', 'lesson_assignments.student_id')
    ->where('students.teacher_id', $teacherId)
    ->select('lesson_assignments.school_year_id', 'students.student_id', 'students.first_name')
    ->distinct()
    ->get();
foreach ($las as $la) {
    echo "SY ID: {$la->school_year_id} | Student: {$la->student_id} ({$la->first_name})\n";
}

echo "\n--- Dashboard query results per SY ---\n";
foreach (App\Models\SchoolYear::all() as $sy) {
    $q = App\Models\Student::where('teacher_id', $teacherId);
    if ($sy->status === 'archived') {
        $syId = $sy->id;
        $q->where(function ($subQ) use ($sy, $syId, $teacherLessonIds) {
            $subQ->where('school_year', $sy->name)
                 ->orWhereExists(function ($s) use ($syId, $teacherLessonIds) {
                     $s->select(DB::raw(1))
                       ->from('lesson_assignments')
                       ->whereColumn('lesson_assignments.student_id', 'students.student_id')
                       ->where('lesson_assignments.school_year_id', $syId)
                       ->whereIn('lesson_assignments.lesson_id', $teacherLessonIds);
                 });
        });
    } else {
        $q->where('school_year', $sy->name)->where('is_enrolled', true);
    }
    echo "SY: {$sy->name} (id={$sy->id}, {$sy->status}) => " . $q->pluck('first_name')->implode(', ') . "\n";
}
