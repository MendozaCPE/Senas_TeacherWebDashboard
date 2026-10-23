<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Some installations already have this table from an earlier schema.
        // Preserve its existing columns and data; only add the field this UI needs.
        if (Schema::hasTable('teacher_announcements')) {
            if (!Schema::hasColumn('teacher_announcements', 'recipient_count')) {
                Schema::table('teacher_announcements', function (Blueprint $table) {
                    $table->unsignedInteger('recipient_count')->default(0);
                });
            }

            return;
        }

        Schema::create('teacher_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->unsignedBigInteger('lesson_id')->nullable();
            $table->unsignedBigInteger('module_id')->nullable();
            $table->string('title')->default('Announcement');
            $table->text('message');
            $table->string('type')->default('lesson');
            $table->unsignedBigInteger('school_year_id')->nullable();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->timestamps();
            $table->index(['teacher_id', 'created_at']);
        });
    }

    public function down(): void
    {
        // This table may predate this migration. Keep announcement data intact
        // during rollback rather than risking deletion of existing records.
    }
};
