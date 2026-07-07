<?php

namespace Database\Seeders;

use App\Models\LessonAssignment;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class UpdateLessonAssignmentsLockStatusSeeder extends Seeder
{
    public function run()
    {
        $assignments = LessonAssignment::with('lesson')->get();
        $count = 0;

        foreach ($assignments as $assignment) {
            if ($assignment->lesson && $assignment->lesson->module) {
                $moduleLessons = Lesson::where('module_id', $assignment->lesson->module->module_id)
                    ->where('status', 'published')
                    ->where('lesson_id', '!=', $assignment->lesson->lesson_id)
                    ->count();
                
                if ($moduleLessons === 0) {
                    $assignment->is_locked = false;
                    $assignment->save();
                    $count++;
                }
            } else {
                $assignment->is_locked = false;
                $assignment->save();
                $count++;
            }
        }

        $this->command->info("Updated {$count} assignments to unlocked state.");
    }
}