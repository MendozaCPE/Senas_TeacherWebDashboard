<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GestureModule;
use Illuminate\Support\Facades\DB;

class GestureSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Modules
        $modules = [
            [
                'name' => 'alphabet_part1',
                'display_name' => 'Alphabet Part 1 (A-M)',
                'description' => 'Learn the first half of the alphabet from A to M',
                'difficulty' => 'Beginner',
                'model_file' => 'models/mobilenetv2_alphabet.tflite',
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'alphabet_part2',
                'display_name' => 'Alphabet Part 2 (N-Z)',
                'description' => 'Learn the second half of the alphabet from N to Z',
                'difficulty' => 'Beginner',
                'model_file' => 'models/mobilenetv2_alphabet.tflite',
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'numbers',
                'display_name' => 'Numbers 1-10',
                'description' => 'Learn numbers from 1 to 10 in ASL',
                'difficulty' => 'Beginner',
                'model_file' => 'models/mobilenetv2_numbers.tflite',
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert modules and get their IDs
        $moduleIds = [];
        foreach ($modules as $moduleData) {
            $moduleId = DB::table('gesture_modules')->insertGetId($moduleData);
            $moduleIds[$moduleData['name']] = $moduleId;
        }

        // 2. Create gestures for each module
        $this->createGesturesForModules($moduleIds);
    }

    private function createGesturesForModules($moduleIds)
    {
        $allGestures = [];

        // Alphabet Part 1: A-M
        $letters1 = range('A', 'M');
        foreach ($letters1 as $letter) {
            $allGestures[] = [
                'name' => $letter,
                'display_name' => "Letter {$letter}",
                'description' => "ASL sign for letter {$letter}",
                'difficulty' => 'Beginner',
                'module_id' => $moduleIds['alphabet_part1'],
                'model_file' => $this->getModelFileForLetter($letter),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Alphabet Part 2: N-Z
        $letters2 = range('N', 'Z');
        foreach ($letters2 as $letter) {
            $allGestures[] = [
                'name' => $letter,
                'display_name' => "Letter {$letter}",
                'description' => "ASL sign for letter {$letter}",
                'difficulty' => 'Beginner',
                'module_id' => $moduleIds['alphabet_part2'],
                'model_file' => $this->getModelFileForLetter($letter),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Numbers 1-10
        $numbers = range(1, 10);
        foreach ($numbers as $number) {
            $allGestures[] = [
                'name' => (string)$number,
                'display_name' => "Number {$number}",
                'description' => "ASL sign for number {$number}",
                'difficulty' => 'Beginner',
                'module_id' => $moduleIds['numbers'],
                'model_file' => 'models/mobilenetv2_numbers.tflite',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all gestures at once (more efficient)
        if (!empty($allGestures)) {
            DB::table('gestures')->insert($allGestures);
            
            $this->command->info("✅ Added " . count($allGestures) . " gestures total");
            $this->command->info("   - Alphabet Part 1: 13 letters (A-M)");
            $this->command->info("   - Alphabet Part 2: 13 letters (N-Z)");
            $this->command->info("   - Numbers: 10 numbers (1-10)");
            $this->command->info("   - Note: J and Z use LSTM model, others use alphabet model");
        }
    }

    private function getModelFileForLetter($letter)
    {
        // J and Z use the LSTM dynamic model
        if (in_array($letter, ['J', 'Z'])) {
            return 'models/senas_lstm_dynamic.tflite';
        }
        
        // All other letters use the standard alphabet model
        return 'models/mobilenetv2_alphabet.tflite';
    }
}