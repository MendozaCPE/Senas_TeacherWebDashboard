<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('help_requests', function (Blueprint $table) {
            // Teacher who owns the student that submitted the report
            $table->unsignedBigInteger('teacher_id')->nullable()->after('student_id');

            // Teacher review fields
            $table->text('teacher_note')->nullable()->after('admin_response');
            $table->text('teacher_response')->nullable()->after('teacher_note');
            $table->timestamp('teacher_responded_at')->nullable()->after('teacher_response');
            $table->unsignedBigInteger('teacher_responded_by')->nullable()->after('teacher_responded_at');

            // Escalation fields
            $table->timestamp('escalated_at')->nullable()->after('teacher_responded_by');
            $table->unsignedBigInteger('escalated_by')->nullable()->after('escalated_at');
            $table->text('escalation_reason')->nullable()->after('escalated_by');

            $table->index('teacher_id');
        });

        // Backfill teacher_id for existing records by joining through students
        DB::statement('
            UPDATE help_requests hr
            JOIN students s ON s.student_id = hr.student_id
            SET hr.teacher_id = s.teacher_id
            WHERE hr.teacher_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('help_requests', function (Blueprint $table) {
            $table->dropIndex(['teacher_id']);
            $table->dropColumn([
                'teacher_id',
                'teacher_note',
                'teacher_response',
                'teacher_responded_at',
                'teacher_responded_by',
                'escalated_at',
                'escalated_by',
                'escalation_reason',
            ]);
        });
    }
};
