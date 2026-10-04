<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$teacherId = 2;
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
        $query->where('school_year', $schoolYear);
    }

    echo "Students Page for SY [{$schoolYear}]: " . $query->pluck('first_name')->implode(', ') . " (Total: " . $query->count() . ")\n";
}
