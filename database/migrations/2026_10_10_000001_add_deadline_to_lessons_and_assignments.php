<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('lessons') && !Schema::hasColumn('lessons', 'deadline')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->timestamp('deadline')->nullable()->after('published_at');
            });
        }

        if (Schema::hasTable('lesson_assignments')) {
            Schema::table('lesson_assignments', function (Blueprint $table) {
                if (!Schema::hasColumn('lesson_assignments', 'deadline')) {
                    $table->timestamp('deadline')->nullable()->after('assigned_at');
                }
                if (!Schema::hasColumn('lesson_assignments', 'first_completed_at')) {
                    $table->timestamp('first_completed_at')->nullable()->after('completed_at');
                }
                if (!Schema::hasColumn('lesson_assignments', 'is_late')) {
                    $table->boolean('is_late')->default(false)->after('first_completed_at');
                }
            });
        }

        if (Schema::hasTable('quiz_attempts') && !Schema::hasColumn('quiz_attempts', 'is_late')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                $table->boolean('is_late')->default(false)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('lessons') && Schema::hasColumn('lessons', 'deadline')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->dropColumn('deadline');
            });
        }

        if (Schema::hasTable('lesson_assignments')) {
            Schema::table('lesson_assignments', function (Blueprint $table) {
                $dropCols = [];
                if (Schema::hasColumn('lesson_assignments', 'deadline')) {
                    $dropCols[] = 'deadline';
                }
                if (Schema::hasColumn('lesson_assignments', 'first_completed_at')) {
                    $dropCols[] = 'first_completed_at';
                }
                if (Schema::hasColumn('lesson_assignments', 'is_late')) {
                    $dropCols[] = 'is_late';
                }
                if (!empty($dropCols)) {
                    $table->dropColumn($dropCols);
                }
            });
        }

        if (Schema::hasTable('quiz_attempts') && Schema::hasColumn('quiz_attempts', 'is_late')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                $table->dropColumn('is_late');
            });
        }
    }
};
