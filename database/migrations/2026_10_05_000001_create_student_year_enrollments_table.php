<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Creates the student_year_enrollments table.
 *
 * Purpose: Immutable, append-only record of every school-year a student
 * was enrolled in under a given teacher. Written at three events:
 *   1. New student created (StudentsController@store)
 *   2. Manual re-enroll   (StudentsController@enroll)
 *   3. School-year transition (SchoolYearTransitionService — for all students
 *      that carry forward into the new year)
 *
 * This table is the authoritative source for "how many students were
 * enrolled each school year" so the Dashboard and Grade Leader analytics
 * charts no longer depend on the mutable students.school_year column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_year_enrollments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('school_year_id')->nullable(); // FK to school_years; nullable for legacy rows

            // Denormalised for fast GROUP BY without joins
            $table->string('school_year_name', 20);   // e.g. "2025-2026"
            $table->string('program_type', 30)->nullable();
            $table->string('grade_level', 50)->nullable();
            $table->string('section', 100)->nullable();

            // When the enrollment record was created (= date enrolled into this year)
            $table->timestamp('enrolled_at')->useCurrent();

            $table->timestamps();

            // ── Indexes ─────────────────────────────────────────────────────
            // Unique: one row per student per school year (prevents duplicates
            // if enroll is called more than once for the same year).
            $table->unique(['student_id', 'school_year_name'], 'sye_student_year_unique');

            $table->index('teacher_id');
            $table->index('school_id');
            $table->index('school_year_id');
            $table->index('school_year_name');

            // ── Foreign keys ─────────────────────────────────────────────────
            $table->foreign('student_id')
                ->references('student_id')
                ->on('students')
                ->onDelete('cascade');

            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
                ->onDelete('cascade');

            $table->foreign('school_id')
                ->references('id')
                ->on('schools')
                ->onDelete('set null');

            $table->foreign('school_year_id')
                ->references('id')
                ->on('school_years')
                ->onDelete('set null');
        });

        // ── Backfill existing students ────────────────────────────────────────
        // For every student that already has a non-null school_year value,
        // seed one enrollment record using the current value of students.school_year.
        // This covers all data that existed before this migration ran.
        $students = DB::table('students')
            ->whereNotNull('school_year')
            ->where('school_year', '!=', '')
            ->select('student_id', 'teacher_id', 'school_id', 'school_year', 'program_type', 'grade_level', 'section', 'created_at')
            ->get();

        foreach ($students as $s) {
            // Resolve the school_year_id if a matching school_years row exists.
            $syId = DB::table('school_years')
                ->where('name', $s->school_year)
                ->when($s->school_id, fn ($q) => $q->where('school_id', $s->school_id))
                ->value('id');

            DB::table('student_year_enrollments')->insertOrIgnore([
                'student_id'       => $s->student_id,
                'teacher_id'       => $s->teacher_id,
                'school_id'        => $s->school_id,
                'school_year_id'   => $syId,
                'school_year_name' => $s->school_year,
                'program_type'     => $s->program_type,
                'grade_level'      => $s->grade_level,
                'section'          => $s->section,
                'enrolled_at'      => $s->created_at ?? now(),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_year_enrollments');
    }
};
