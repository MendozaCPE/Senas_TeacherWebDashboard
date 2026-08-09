<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * OLD ENUM VALUES:  Alphabet_Numbers, Greetings, Classroom_Words, Everything
 * NEW ENUM VALUES:  Alphabet_Numbers, Fingerspelling, Greetings_FSL_Words, Everything
 *
 * This migration safely transitions all existing data and prevents errors
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ============================================================
        // STEP 1: Check if the column exists
        // ============================================================
        if (!Schema::hasColumn('learning_paths', 'learning_goal')) {
            // Column doesn't exist, create it with new enum
            DB::statement("ALTER TABLE learning_paths ADD COLUMN learning_goal ENUM(
                'Alphabet_Numbers','Fingerspelling','Greetings_FSL_Words','Everything'
            ) NULL");
            return;
        }

        // ============================================================
        // STEP 2: Widen the enum to hold old + new values simultaneously
        // ============================================================
        DB::statement("ALTER TABLE learning_paths MODIFY learning_goal ENUM(
            'Alphabet_Numbers','Greetings','Classroom_Words','Everything',
            'Fingerspelling','Greetings_FSL_Words'
        ) NULL");

        // ============================================================
        // STEP 3: Update existing records to new values
        // ============================================================
        
        // 3a: Map 'Greetings' -> 'Greetings_FSL_Words'
        $updatedGreetings = DB::table('learning_paths')
            ->where('learning_goal', 'Greetings')
            ->update(['learning_goal' => 'Greetings_FSL_Words']);
        
        // 3b: Map 'Classroom_Words' -> 'Greetings_FSL_Words'
        $updatedClassroom = DB::table('learning_paths')
            ->where('learning_goal', 'Classroom_Words')
            ->update(['learning_goal' => 'Greetings_FSL_Words']);
        
        // 3c: 'Alphabet_Numbers' stays the same
        // 3d: 'Everything' stays the same
        
        // 3e: Any NULL values stay NULL

        // ============================================================
        // STEP 4: Log the migration results
        // ============================================================
        \Log::info('Learning path goals migration completed', [
            'greetings_mapped_to_greetings_fsl' => $updatedGreetings,
            'classroom_words_mapped_to_greetings_fsl' => $updatedClassroom,
            'total_updated' => ($updatedGreetings + $updatedClassroom),
            'new_enum_values' => ['Alphabet_Numbers', 'Fingerspelling', 'Greetings_FSL_Words', 'Everything'],
        ]);

        // ============================================================
        // STEP 5: Narrow the enum down to only the final, current set
        // ============================================================
        DB::statement("ALTER TABLE learning_paths MODIFY learning_goal ENUM(
            'Alphabet_Numbers','Fingerspelling','Greetings_FSL_Words','Everything'
        ) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ============================================================
        // ROLLBACK: Revert to old enum values
        // ============================================================
        
        if (!Schema::hasColumn('learning_paths', 'learning_goal')) {
            return;
        }

        // Widen again so we can move data back
        DB::statement("ALTER TABLE learning_paths MODIFY learning_goal ENUM(
            'Alphabet_Numbers','Greetings','Classroom_Words','Everything',
            'Fingerspelling','Greetings_FSL_Words'
        ) NULL");

        // Map back: 'Greetings_FSL_Words' -> 'Greetings' (most common original)
        DB::table('learning_paths')
            ->where('learning_goal', 'Greetings_FSL_Words')
            ->update(['learning_goal' => 'Greetings']);

        // 'Fingerspelling' didn't exist before, map to 'Alphabet_Numbers'
        DB::table('learning_paths')
            ->where('learning_goal', 'Fingerspelling')
            ->update(['learning_goal' => 'Alphabet_Numbers']);

        // Restore the original enum
        DB::statement("ALTER TABLE learning_paths MODIFY learning_goal ENUM(
            'Alphabet_Numbers','Greetings','Classroom_Words','Everything'
        ) NULL");
    }
};