<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add a nullable 'action_status' column to teacher_notifications so we can
        // track whether an action-type notification (like new_school_year) is
        // still pending, was acted upon, or was dismissed — without polluting
        // other notification types.
        // Also add school_year_id so the notification references the relevant year.
        Schema::table('teacher_notifications', function (Blueprint $table) {
            $table->enum('action_status', ['pending', 'completed', 'dismissed'])
                  ->nullable()
                  ->after('is_read')
                  ->comment('Tracks action-type notification state, null for informational types');

            $table->unsignedBigInteger('related_school_year_id')
                  ->nullable()
                  ->after('action_status')
                  ->comment('School year this notification refers to (for new_school_year type)');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_notifications', function (Blueprint $table) {
            $table->dropColumn(['action_status', 'related_school_year_id']);
        });
    }
};
