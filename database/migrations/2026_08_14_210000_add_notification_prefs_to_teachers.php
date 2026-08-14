<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            // JSON column storing notification preferences.
            // Default: email_alerts on, weekly_digest off.
            $table->json('notification_prefs')->nullable()->after('specialization');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('notification_prefs');
        });
    }
};
