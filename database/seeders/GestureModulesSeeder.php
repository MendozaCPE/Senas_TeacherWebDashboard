<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GestureModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get current timestamp
        $now = Carbon::now();

        // ============================================
        // 1. UPDATE module with ID: 3 to be Level 1 Numbers
        // ============================================
        DB::table('gesture_modules')
            ->where('module_id', 3)
            ->update([
                'name' => 'level1_numbers',
                'display_name' => 'Level 1 Numbers',
                'description' => 'Learn numbers 1 to 10 in Filipino Sign Language. Master the fundamental number signs for counting, telling time, and basic mathematics.',
                'difficulty' => 'Beginner',
                'model_file' => 'models/level3_numbers_tfjs_model/model.json',
                'is_active' => 1,
                'order' => 1,
                'xp_reward' => 40,
                'updated_at' => $now
            ]);

        // ============================================
        // 2. INSERT Level 2 Greetings module
        // ============================================
        DB::table('gesture_modules')->insert([
            'name' => 'level2_greetings',
            'display_name' => 'Level 2 Greetings',
            'description' => 'Learn basic greetings and common phrases in Filipino Sign Language. This module covers essential everyday expressions to help you start conversations.',
            'difficulty' => 'Intermediate',  // Beginner to Intermediate
            'model_file' => 'models/level1_greetings_tfjs_model/model.json',
            'is_active' => 1,
            'order' => 2,
            'xp_reward' => 50,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        // Get the module_id for Level 2 Greetings
        $level2ModuleId = DB::table('gesture_modules')
            ->where('name', 'level2_greetings')
            ->value('module_id');

        // ============================================
        // 3. INSERT Level 3 Survival module
        // ============================================
        DB::table('gesture_modules')->insert([
            'name' => 'level3_survival',
            'display_name' => 'Level 3 Survival',
            'description' => 'Essential survival phrases for everyday situations. Learn how to express understanding, ask for clarification, and communicate effectively in daily interactions.',
            'difficulty' => 'Intermediate',
            'model_file' => 'models/level2_survival_tfjs_model/model.json',
            'is_active' => 1,
            'order' => 3,
            'xp_reward' => 60,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        // Get the module_id for Level 3 Survival
        $level3ModuleId = DB::table('gesture_modules')
            ->where('name', 'level3_survival')
            ->value('module_id');

        // ============================================
        // 4. UPDATE existing gestures for Level 1 Numbers
        //    (gestures already exist with names '1'-'10', just update descriptions)
        // ============================================
        $level1ModuleId = 3; // The updated module ID

        $numberUpdates = [
            '1'  => ['display_name' => 'One',   'description' => 'Sign for number 1 in Filipino Sign Language.'],
            '2'  => ['display_name' => 'Two',   'description' => 'Sign for number 2 in Filipino Sign Language.'],
            '3'  => ['display_name' => 'Three', 'description' => 'Sign for number 3 in Filipino Sign Language.'],
            '4'  => ['display_name' => 'Four',  'description' => 'Sign for number 4 in Filipino Sign Language.'],
            '5'  => ['display_name' => 'Five',  'description' => 'Sign for number 5 in Filipino Sign Language.'],
            '6'  => ['display_name' => 'Six',   'description' => 'Sign for number 6 in Filipino Sign Language.'],
            '7'  => ['display_name' => 'Seven', 'description' => 'Sign for number 7 in Filipino Sign Language.'],
            '8'  => ['display_name' => 'Eight', 'description' => 'Sign for number 8 in Filipino Sign Language.'],
            '9'  => ['display_name' => 'Nine',  'description' => 'Sign for number 9 in Filipino Sign Language.'],
            '10' => ['display_name' => 'Ten',   'description' => 'Sign for number 10 in Filipino Sign Language.'],
        ];

        foreach ($numberUpdates as $name => $data) {
            DB::table('gestures')
                ->where('module_id', $level1ModuleId)
                ->where('name', $name)
                ->update([
                    'display_name' => $data['display_name'],
                    'description'  => $data['description'],
                    'difficulty'   => 'beginner',
                    'updated_at'   => $now,
                ]);
        }

        // ============================================
        // 5. INSERT gestures for Level 2 Greetings
        // ============================================
        $greetings = [
            [
                'name' => 'HELLO',
                'display_name' => 'Hello',
                'description' => 'Greet someone with a friendly "Hello" in Filipino Sign Language.',
                'difficulty' => 'beginner'
            ],
            [
                'name' => 'THANK YOU',
                'display_name' => 'Thank You',
                'description' => 'Express gratitude with the "Thank You" sign in Filipino Sign Language.',
                'difficulty' => 'beginner'
            ],
            [
                'name' => 'SEE YOU TOMORROW',
                'display_name' => 'See You Tomorrow',
                'description' => 'Say goodbye with the promise to meet again tomorrow in Filipino Sign Language.',
                'difficulty' => 'intermediate'
            ],
            [
                'name' => 'HOW ARE YOU',
                'display_name' => 'How Are You',
                'description' => 'Ask someone how they are feeling in Filipino Sign Language.',
                'difficulty' => 'intermediate'
            ],
            [
                'name' => 'NICE TO MEET YOU',
                'display_name' => 'Nice To Meet You',
                'description' => 'Politely express pleasure in meeting someone for the first time in Filipino Sign Language.',
                'difficulty' => 'intermediate'
            ],
        ];

        foreach ($greetings as $gesture) {
            DB::table('gestures')->insert([
                'module_id' => $level2ModuleId,
                'name' => $gesture['name'],
                'display_name' => $gesture['display_name'],
                'description' => $gesture['description'],
                'image_url' => null,
                'video_url' => null,
                'model_file' => null,
                'difficulty' => $gesture['difficulty'],
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        // ============================================
        // 6. INSERT gestures for Level 3 Survival
        // ============================================
        $survivalGestures = [
            [
                'name' => 'UNDERSTAND',
                'display_name' => 'Understand',
                'description' => 'Express understanding or comprehension in Filipino Sign Language.',
                'difficulty' => 'beginner'
            ],
            [
                'name' => "DON'T UNDERSTAND",
                'display_name' => "Don't Understand",
                'description' => 'Communicate that you do not understand in Filipino Sign Language.',
                'difficulty' => 'beginner'
            ],
            [
                'name' => 'KNOW',
                'display_name' => 'Know',
                'description' => 'Indicate that you know something in Filipino Sign Language.',
                'difficulty' => 'beginner'
            ],
            [
                'name' => "DON'T KNOW",
                'display_name' => "Don't Know",
                'description' => 'Indicate that you do not know something in Filipino Sign Language.',
                'difficulty' => 'beginner'
            ],
            [
                'name' => 'NO',
                'display_name' => 'No',
                'description' => 'Express negation or disagreement in Filipino Sign Language.',
                'difficulty' => 'beginner'
            ],
            [
                'name' => 'YES',
                'display_name' => 'Yes',
                'description' => 'Express affirmation or agreement in Filipino Sign Language.',
                'difficulty' => 'beginner'
            ],
            [
                'name' => 'WRONG',
                'display_name' => 'Wrong',
                'description' => 'Indicate that something is incorrect in Filipino Sign Language.',
                'difficulty' => 'intermediate'
            ],
            [
                'name' => 'CORRECT',
                'display_name' => 'Correct',
                'description' => 'Indicate that something is correct in Filipino Sign Language.',
                'difficulty' => 'intermediate'
            ],
            [
                'name' => 'SLOW',
                'display_name' => 'Slow',
                'description' => 'Request someone to slow down or indicate slowness in Filipino Sign Language.',
                'difficulty' => 'intermediate'
            ],
            [
                'name' => 'FAST',
                'display_name' => 'Fast',
                'description' => 'Request someone to speed up or indicate speed in Filipino Sign Language.',
                'difficulty' => 'intermediate'
            ],
        ];

        foreach ($survivalGestures as $gesture) {
            DB::table('gestures')->insert([
                'module_id' => $level3ModuleId,
                'name' => $gesture['name'],
                'display_name' => $gesture['display_name'],
                'description' => $gesture['description'],
                'image_url' => null,
                'video_url' => null,
                'model_file' => null,
                'difficulty' => $gesture['difficulty'],
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        // ============================================
        // 7. UPDATE existing alphabet modules with proper order
        // ============================================
        $modulesToUpdate = [
            1 => ['order' => 4, 'xp_reward' => 40], // alphabet_part1
            2 => ['order' => 5, 'xp_reward' => 40], // alphabet_part2
        ];

        foreach ($modulesToUpdate as $moduleId => $data) {
            DB::table('gesture_modules')
                ->where('module_id', $moduleId)
                ->update([
                    'order' => $data['order'],
                    'xp_reward' => $data['xp_reward'],
                    'updated_at' => $now
                ]);
        }

        // ============================================
        // 8. Delete fingerspelling module if exists
        // ============================================
        DB::table('gesture_modules')
            ->where('name', 'fingerspelling')
            ->delete();

        // ============================================
        // Output summary
        // ============================================
        $this->command->info('✅ Gesture modules and gestures seeded successfully!');
        $this->command->info('📊 Learning Path (from easiest to most challenging):');
        $this->command->info('   🟢 Level 1: Numbers (Beginner) - 10 signs');
        $this->command->info('   🟡 Level 2: Greetings (Beginner-Intermediate) - 5 phrases');
        $this->command->info('   🟠 Level 3: Survival (Intermediate) - 10 phrases');
        $this->command->info('   🔵 Level 4: Alphabet Part 1 (A-M) - 13 letters');
        $this->command->info('   🔵 Level 5: Alphabet Part 2 (N-Z) - 13 letters');
        $this->command->info('');
        $this->command->info('📈 Total gestures: 51 (updated existing 10 numbers, added 15 new)');
        $this->command->info('   - Numbers: 10 (updated display names)');
        $this->command->info('   - Greetings: 5 (new)');
        $this->command->info('   - Survival: 10 (new)');
        $this->command->info('   - Alphabet: 26 (unchanged)');
        $this->command->info('   - Deleted: fingerspelling module');
    }
}