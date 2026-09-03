<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gestures', function (Blueprint $table) {
            // 'static' = single-frame sign (alphabet, numbers)
            // 'dynamic' = motion-based sign (hello, thank you, goodbye, etc.)
            $table->enum('sign_type', ['static', 'dynamic'])
                  ->default('static')
                  ->after('difficulty');
        });

        // Auto-classify gestures whose module name contains 'greet', 'express',
        // 'emotion', 'phrase', or 'word' as dynamic; everything else stays static.
        // This covers the most common naming conventions ("greetings", "expressions").
        DB::statement("
            UPDATE gestures
            SET sign_type = 'dynamic'
            WHERE module_id IN (
                SELECT module_id FROM gesture_modules
                WHERE name LIKE '%greet%'
                   OR name LIKE '%express%'
                   OR name LIKE '%phrase%'
                   OR name LIKE '%word%'
                   OR name LIKE '%emotion%'
                   OR display_name LIKE '%greet%'
                   OR display_name LIKE '%express%'
                   OR display_name LIKE '%phrase%'
                   OR display_name LIKE '%word%'
                   OR display_name LIKE '%emotion%'
            )
        ");
    }

    public function down(): void
    {
        Schema::table('gestures', function (Blueprint $table) {
            $table->dropColumn('sign_type');
        });
    }
};
