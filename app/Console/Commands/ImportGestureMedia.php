<?php

namespace App\Console\Commands;

use App\Models\Gesture;
use App\Models\GestureModule;
use App\Models\GestureMedia;
use Illuminate\Console\Command;

class ImportGestureMedia extends Command
{
    protected $signature = 'gesture:import-media';
    protected $description = 'Import gesture media from sign_language_media folder';

    public function handle()
    {
        $this->info('📁 Importing gesture media...');
        
        // Define module mappings (folder name => display name)
        $moduleMappings = [
            'Alphabets' => 'Alphabets',
            'Numbers' => 'Numbers',
            'Greetings' => 'Greetings',
            'Survival' => 'Survival',
        ];

        // Base path where your media is stored
        $basePath = storage_path('app/public/sign_language_media');
        
        if (!is_dir($basePath)) {
            $this->error("❌ Directory not found: {$basePath}");
            $this->info("Please make sure you've copied your files to:");
            $this->info("storage/app/public/sign_language_media/");
            return 1;
        }

        $totalImported = 0;
        $totalSkipped = 0;

        foreach ($moduleMappings as $moduleName => $displayName) {
            $this->info("\n📂 Processing {$moduleName}...");
            
            // Get or create module
            $module = GestureModule::firstOrCreate(
                ['name' => $moduleName],
                [
                    'display_name' => $displayName,
                    'description' => "ASL {$displayName} module",
                    'difficulty' => 'beginner',
                    'is_active' => true,
                    'order' => array_search($moduleName, array_keys($moduleMappings)) + 1,
                ]
            );

            $modulePath = $basePath . '/' . $moduleName;
            
            if (!is_dir($modulePath)) {
                $this->warn("⚠️  Directory not found: {$modulePath}");
                continue;
            }

            // Get all files (images and videos)
            $files = glob($modulePath . '/*.{png,jpg,jpeg,mp4,mov}', GLOB_BRACE);
            $this->info("   Found " . count($files) . " files");
            
            foreach ($files as $filePath) {
                $fileName = basename($filePath);
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                
                // Parse filename: "1_A.png" -> order=1, name=A
                // Handle "53_Don'tUnderstand.mp4" -> order=53, name=Don'tUnderstand
                preg_match('/^(\d+)_(.+)\.' . $extension . '$/', $fileName, $matches);
                
                if (empty($matches)) {
                    $this->warn("   ⚠️  Skipping file with unexpected format: {$fileName}");
                    $totalSkipped++;
                    continue;
                }

                $order = (int) $matches[1];
                $gestureName = $matches[2];
                
                // Determine media type
                $mediaType = in_array($extension, ['mp4', 'mov', 'avi']) ? 'video' : 'image';
                
                // Find or create gesture
                $gesture = Gesture::firstOrCreate(
                    ['name' => $gestureName],
                    [
                        'display_name' => $gestureName,
                        'description' => "ASL sign for {$gestureName}",
                        'difficulty' => $module->difficulty,
                        'module_id' => $module->module_id,
                        'model_file' => 'models/mobilenetv2_alphabet.tflite',
                    ]
                );

                // Update module if needed
                if ($gesture->module_id !== $module->module_id) {
                    $gesture->module_id = $module->module_id;
                    $gesture->save();
                }

                // Check if media already exists for this gesture
                $existingMedia = GestureMedia::where('gesture_id', $gesture->gesture_id)
                    ->where('file_name', $fileName)
                    ->first();

                if (!$existingMedia) {
                    // Determine if this is primary (first image in order)
                    $isPrimary = $order === 1 && $mediaType === 'image';
                    
                    // If this is a primary image, unset any existing primary images
                    if ($mediaType === 'image' && $isPrimary) {
                        GestureMedia::where('gesture_id', $gesture->gesture_id)
                            ->where('media_type', 'image')
                            ->update(['is_primary' => false]);
                    }
                    
                    // Store the path relative to storage/app/public/
                    $relativePath = "sign_language_media/{$moduleName}/{$fileName}";
                    
                    // Get file info
                    $mimeType = mime_content_type($filePath);
                    $fileSize = filesize($filePath);
                    
                    GestureMedia::create([
                        'gesture_id' => $gesture->gesture_id,
                        'module_id' => $module->module_id,
                        'media_type' => $mediaType,
                        'file_path' => $relativePath,
                        'file_name' => $fileName,
                        'mime_type' => $mimeType,
                        'file_size' => $fileSize,
                        'is_primary' => $isPrimary,
                        'order' => $order,
                    ]);
                    
                    $totalImported++;
                    $this->line("   ✅ Imported: {$fileName} → {$gestureName}");
                } else {
                    $totalSkipped++;
                    $this->line("   ⏭️  Skipped (already exists): {$fileName}");
                }
            }
        }

        $this->newLine();
        $this->info("🎉 Import completed!");
        $this->info("📊 Summary:");
        $this->info("   - Imported: {$totalImported} files");
        $this->info("   - Skipped: {$totalSkipped} files");
        $this->info("   - Total gestures: " . Gesture::count());
        $this->info("   - Total media records: " . GestureMedia::count());
        
        // Show breakdown by module
        $this->info("\n📚 By Module:");
        foreach ($moduleMappings as $moduleName => $displayName) {
            $count = GestureMedia::whereHas('module', function($q) use ($moduleName) {
                $q->where('name', $moduleName);
            })->count();
            $this->info("   - {$displayName}: {$count} files");
        }
        
        return 0;
    }
}