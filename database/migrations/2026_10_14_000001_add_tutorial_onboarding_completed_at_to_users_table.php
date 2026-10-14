<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('tutorial_onboarding_completed_at')->nullable();
        });

        // Existing accounts have already been using the dashboard. Only
        // accounts created after this migration should see first-time onboarding.
        DB::table('users')->update([
            'tutorial_onboarding_completed_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tutorial_onboarding_completed_at');
        });
    }
};
