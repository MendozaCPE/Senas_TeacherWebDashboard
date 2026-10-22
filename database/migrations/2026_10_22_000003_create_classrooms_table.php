<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create classrooms table
        if (!Schema::hasTable('classrooms')) {
            Schema::create('classrooms', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('teacher_id')->index();
                $table->unsignedBigInteger('school_id')->nullable()->index();
                $table->string('name', 100);
                $table->string('section', 100)->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
                $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
            });
        }

        // 2. Add classroom_id to students table
        if (Schema::hasTable('students') && !Schema::hasColumn('students', 'classroom_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedBigInteger('classroom_id')->nullable()->after('teacher_id')->index();
                $table->foreign('classroom_id')->references('id')->on('classrooms')->onDelete('set null');
            });
        }

        // 3. Add classroom_id to student_year_enrollments table if it exists
        if (Schema::hasTable('student_year_enrollments') && !Schema::hasColumn('student_year_enrollments', 'classroom_id')) {
            Schema::table('student_year_enrollments', function (Blueprint $table) {
                $table->unsignedBigInteger('classroom_id')->nullable()->after('teacher_id')->index();
                $table->foreign('classroom_id')->references('id')->on('classrooms')->onDelete('set null');
            });
        }

        // 4. Backfill default classroom for existing teachers so existing data is cleanly grouped
        $teachers = DB::table('teachers')->get();
        foreach ($teachers as $teacher) {
            $defaultName = !empty($teacher->specialization) ? "{$teacher->specialization} Class" : 'SNED Class';
            $classroomId = DB::table('classrooms')->insertGetId([
                'teacher_id'  => $teacher->id,
                'school_id'   => $teacher->school_id,
                'name'        => $defaultName,
                'description' => 'Default classroom',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // Assign this teacher's existing students to their default classroom
            DB::table('students')
                ->where('teacher_id', $teacher->id)
                ->whereNull('classroom_id')
                ->update(['classroom_id' => $classroomId]);

            if (Schema::hasTable('student_year_enrollments')) {
                DB::table('student_year_enrollments')
                    ->where('teacher_id', $teacher->id)
                    ->whereNull('classroom_id')
                    ->update(['classroom_id' => $classroomId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('student_year_enrollments')) {
            Schema::table('student_year_enrollments', function (Blueprint $table) {
                $table->dropForeign(['classroom_id']);
                $table->dropColumn('classroom_id');
            });
        }

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropColumn('classroom_id');
        });

        Schema::dropIfExists('classrooms');
    }
};
