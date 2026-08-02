<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\GesturePerformance;
use App\Models\StudentSkillMastery;
use App\Services\MasteryService;

class CacheStudentMastery extends Command
{
    protected $signature = 'mastery:cache {--student= : Specific student ID}';
    protected $description = 'Cache student mastery data for performance';

    public function handle()
    {
        $masteryService = new MasteryService();
        
        $students = $this->option('student') 
            ? Student::where('student_id', $this->option('student'))->get()
            : Student::all();

        $this->info('Caching mastery for ' . $students->count() . ' students...');

        foreach ($students as $student) {
            $this->info("Processing student: {$student->first_name} {$student->last_name}");
            
            $performances = GesturePerformance::where('student_id', $student->student_id)
                ->with('gesture')
                ->get();

            foreach ($performances as $perf) {
                $mastery = ($perf->successful_attempts + 1) / ($perf->attempts + 2);
                $level = $masteryService->getMasteryLevel($mastery);
                
                StudentSkillMastery::updateOrCreate(
                    [
                        'student_id' => $student->student_id,
                        'gesture_id' => $perf->gesture_id,
                    ],
                    [
                        'mastery_probability' => $mastery,
                        'attempts' => $perf->attempts,
                        'successes' => $perf->successful_attempts,
                        'mastery_level' => $level,
                        'last_practiced_at' => $perf->last_attempt_at,
                    ]
                );
            }
            
            $this->info("  ✓ Cached " . $performances->count() . " skills");
        }

        $this->info('✅ Mastery cache completed!');
    }
}