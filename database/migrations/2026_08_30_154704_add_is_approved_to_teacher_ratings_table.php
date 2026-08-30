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
        Schema::table('teacher_ratings', function (Blueprint $table) {
            // Ratings must be approved by an admin before they can be
            // surfaced on the landing page. Defaults to false so that
            // both new and re-submitted ratings start out pending.
            $table->boolean('is_approved')->default(false)->after('feedback');

            $table->index('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_ratings', function (Blueprint $table) {
            $table->dropIndex(['is_approved']);
            $table->dropColumn('is_approved');
        });
    }
};