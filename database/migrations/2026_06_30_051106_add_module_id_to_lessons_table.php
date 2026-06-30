<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check if the column doesn't exist before adding
        if (!Schema::hasColumn('lessons', 'module_id')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->foreignId('module_id')->nullable()->after('teacher_id');
                $table->foreign('module_id')->references('module_id')->on('modules')->onDelete('set null');
            });

            // Create a default module for existing lessons
            $existingLessons = DB::table('lessons')->count();
            
            if ($existingLessons > 0) {
                $firstTeacher = DB::table('teachers')->first();
                
                if ($firstTeacher) {
                    // Check if a default module already exists
                    $existingModule = DB::table('modules')
                        ->where('title', 'General Lessons')
                        ->where('teacher_id', $firstTeacher->id)
                        ->first();
                    
                    if (!$existingModule) {
                        $moduleId = DB::table('modules')->insertGetId([
                            'teacher_id' => $firstTeacher->id,
                            'title' => 'General Lessons',
                            'description' => 'Default module for existing lessons',
                            'module_order' => 1,
                            'status' => 'published',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        DB::table('lessons')->update(['module_id' => $moduleId]);
                    }
                }
            }
        }
    }

    public function down()
    {
        if (Schema::hasColumn('lessons', 'module_id')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->dropForeign(['module_id']);
                $table->dropColumn('module_id');
            });
        }
    }
};