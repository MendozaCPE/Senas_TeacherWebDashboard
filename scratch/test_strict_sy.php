<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$teacherId = 2;
foreach (App\Models\SchoolYear::all() as $sy) {
    $q = App\Models\Student::where('teacher_id', $teacherId)->where('school_year', $sy->name);
    if ($sy->status === 'active') {
        $q->where('is_enrolled', true);
    }
    echo "SY {$sy->name} ({$sy->status}) => " . $q->pluck('first_name')->implode(', ') . " (Total: " . $q->count() . ")\n";
}
