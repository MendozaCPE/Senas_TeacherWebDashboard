<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->integer('total_xp')->default(0)->after('fsl_mastery_level');
            $table->integer('level')->default(1)->after('total_xp');
            $table->integer('streak_days')->default(0)->after('level');
            $table->date('last_activity_date')->nullable()->after('streak_days');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['total_xp', 'level', 'streak_days', 'last_activity_date']);
        });
    }
};