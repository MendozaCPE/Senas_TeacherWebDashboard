<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StudentYearEnrollment
 *
 * Immutable, append-only log of every school year a student was enrolled in.
 * One row per student per school_year_name.  Written at:
 *   - Student creation  (StudentsController@store)
 *   - Manual re-enroll  (StudentsController@enroll)
 *   - SY transition     (SchoolYearTransitionService — records the new year)
 *
 * @property int         $id
 * @property int         $student_id
 * @property int         $teacher_id
 * @property int|null    $school_id
 * @property int|null    $school_year_id
 * @property string      $school_year_name   e.g. "2025-2026"
 * @property string|null $program_type
 * @property string|null $grade_level
 * @property string|null $section
 * @property \Carbon\Carbon $enrolled_at
 */
class StudentYearEnrollment extends Model
{
    protected $table = 'student_year_enrollments';

    protected $fillable = [
        'student_id',
        'teacher_id',
        'school_id',
        'school_year_id',
        'school_year_name',
        'program_type',
        'grade_level',
        'section',
        'enrolled_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id', 'id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Upsert a single enrollment record.
     * Safe to call multiple times — the unique key prevents duplicates.
     * Returns the record (whether newly created or already existing).
     */
    public static function record(
        int     $studentId,
        int     $teacherId,
        ?int    $schoolId,
        ?int    $schoolYearId,
        string  $schoolYearName,
        ?string $programType  = null,
        ?string $gradeLevel   = null,
        ?string $section      = null,
    ): self {
        return static::firstOrCreate(
            [
                'student_id'       => $studentId,
                'school_year_name' => $schoolYearName,
            ],
            [
                'teacher_id'     => $teacherId,
                'school_id'      => $schoolId,
                'school_year_id' => $schoolYearId,
                'program_type'   => $programType,
                'grade_level'    => $gradeLevel,
                'section'        => $section,
                'enrolled_at'    => now(),
            ]
        );
    }
}
