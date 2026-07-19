<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupGestureModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // ============================================
        // 1. DELETE duplicate number gestures (IDs 37-46)
        // These are the newly created ones without model files
        // ============================================
        $duplicateIds = range(37, 46);
        DB::table('gestures')
            ->whereIn('gesture_id', $duplicateIds)
            ->delete();

        $this->command->info('🗑️ Deleted duplicate number gestures (IDs 37-46)');

        // ============================================
        // 2. UPDATE the original number gestures (IDs 27-36)
        // with proper model file
        // ============================================
        DB::table('gestures')
            ->whereIn('gesture_id', range(27, 36))
            ->update([
                'model_file' => 'models/level3_numbers_tfjs_model/model.json',
                'updated_at' => $now
            ]);

        $this->command->info('✅ Updated original number gestures with model file');

        // ============================================
        // 3. UPDATE Level 2 Greetings gestures (IDs 47-51)
        // with proper model file
        // ============================================
        $greetingIds = range(47, 51);
        DB::table('gestures')
            ->whereIn('gesture_id', $greetingIds)
            ->update([
                'model_file' => 'models/level1_greetings_tfjs_model/model.json',
                'updated_at' => $now
            ]);

        $this->command->info('✅ Updated Level 2 Greetings gestures with model file');

        // ============================================
        // 4. UPDATE Level 3 Survival gestures (IDs 52-61)
        // with proper model file
        // ============================================
        $survivalIds = range(52, 61);
        DB::table('gestures')
            ->whereIn('gesture_id', $survivalIds)
            ->update([
                'model_file' => 'models/level2_survival_tfjs_model/model.json',
                'updated_at' => $now
            ]);

        $this->command->info('✅ Updated Level 3 Survival gestures with model file');

        // ============================================
        // 5. UPDATE alphabet gestures with proper model file
        // ============================================
        // Alphabet Part 1 (module_id = 1)
        DB::table('gestures')
            ->where('module_id', 1)
            ->update([
                'model_file' => 'models/mobilenetv2_alphabet.tflite',
                'updated_at' => $now
            ]);

        // Alphabet Part 2 (module_id = 2)
        DB::table('gestures')
            ->where('module_id', 2)
            ->update([
                'model_file' => 'models/mobilenetv2_alphabet.tflite',
                'updated_at' => $now
            ]);

        $this->command->info('✅ Updated alphabet gestures with model file');

        // ============================================
        // 6. Summary
        // ============================================
        $this->command->info('');
        $this->command->info('📊 Cleanup Summary:');
        $this->command->info('   🗑️  Deleted: 10 duplicate number gestures');
        $this->command->info('   📝  Updated: All remaining gestures with model files');
        $this->command->info('');
        $this->command->info('📁 Model Files Assigned:');
        $this->command->info('   🔢 Level 1 Numbers: models/level3_numbers_tfjs_model/model.json');
        $this->command->info('   👋 Level 2 Greetings: models/level1_greetings_tfjs_model/model.json');
        $this->command->info('   🆘 Level 3 Survival: models/level2_survival_tfjs_model/model.json');
        $this->command->info('   🔤 Alphabet (All): models/mobilenetv2_alphabet.tflite');
    }
}