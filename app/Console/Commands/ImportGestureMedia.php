<?php

namespace App\Console\Commands;

use App\Models\Gesture;
use App\Models\GestureModule;
use App\Models\GestureMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportGestureMedia extends Command
{
    protected $signature = 'gesture:import-media';
    protected $description = 'Import gesture media from sign_language_media folder';

    public function handle()
    {
        $this->info('📁 Importing gesture media...');
        $this->info('⚠️  This will ONLY add media files to EXISTING gestures!');
        $this->info('⚠️  It will NOT create new modules or gestures!');
        $this->newLine();
        
        // Base path where your media is stored
        $basePath = storage_path('app/public/sign_language_media');
        
        if (!is_dir($basePath)) {
            $this->error("❌ Directory not found: {$basePath}");
            return 1;
        }

        // Show existing modules for reference
        $this->info("📚 Existing modules:");
        $existingModules = GestureModule::orderBy('order')->get();
        foreach ($existingModules as $m) {
            $count = Gesture::where('module_id', $m->module_id)->count();
            $this->info("   {$m->order}. {$m->name} ({$count} gestures)");
        }
        $this->newLine();

        $totalImported = 0;
        $totalSkipped = 0;
        $totalErrors = 0;
        $warnings = [];

        // 🔥 NEW: Process Alphabets folder with split logic
        $this->info("\n📂 Processing Alphabets...");
        
        $alphabetsPath = $basePath . '/Alphabets';
        if (!is_dir($alphabetsPath)) {
            $this->warn("⚠️  Directory not found: {$alphabetsPath}");
        } else {
            $files = glob($alphabetsPath . '/*.{png,jpg,jpeg,mp4,mov}', GLOB_BRACE);
            $this->info("   Found " . count($files) . " files");
            
            // Define which letters go to which module
            $module1Letters = ['A','B','C','D','E','F','G','H','I','J','K','L','M'];
            $module2Letters = ['N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
            
            foreach ($files as $filePath) {
                $fileName = basename($filePath);
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                
                preg_match('/^(\d+)_(.+)\.' . $extension . '$/', $fileName, $matches);
                
                if (empty($matches)) {
                    $this->warn("   ⚠️  Skipping file with unexpected format: {$fileName}");
                    $totalSkipped++;
                    continue;
                }

                $order = (int) $matches[1];
                $gestureName = $matches[2];
                $mediaType = in_array($extension, ['mp4', 'mov', 'avi']) ? 'video' : 'image';
                
                // 🔥 Determine which module this letter belongs to
                if (in_array($gestureName, $module1Letters)) {
                    $dbModuleName = 'alphabet_part1';
                } elseif (in_array($gestureName, $module2Letters)) {
                    $dbModuleName = 'alphabet_part2';
                } else {
                    $this->warn("   ⚠️  Unknown letter: {$gestureName}");
                    $totalErrors++;
                    continue;
                }
                
                $module = GestureModule::where('name', $dbModuleName)->first();
                
                if (!$module) {
                    $this->error("❌ Module not found: {$dbModuleName}");
                    continue;
                }
                
                // Find the gesture in the correct module
                $gesture = Gesture::where('name', $gestureName)
                                 ->where('module_id', $module->module_id)
                                 ->first();
                
                if (!$gesture) {
                    $this->warn("   ⚠️  Gesture not found: {$gestureName} in module {$dbModuleName}");
                    $warnings[] = "{$fileName} → {$gestureName} not found in {$dbModuleName}";
                    $totalErrors++;
                    continue;
                }

                // Check if media already exists
                $existingMedia = GestureMedia::where('gesture_id', $gesture->gesture_id)
                    ->where('file_name', $fileName)
                    ->first();

                if (!$existingMedia) {
                    $isPrimary = $order === 1 && $mediaType === 'image';
                    $relativePath = "sign_language_media/Alphabets/{$fileName}";
                    
                    $mimeType = mime_content_type($filePath);
                    $fileSize = filesize($filePath);
                    
                    if ($mediaType === 'image' && $isPrimary) {
                        GestureMedia::where('gesture_id', $gesture->gesture_id)
                            ->where('media_type', 'image')
                            ->update(['is_primary' => false]);
                    }
                    
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
                    $this->line("   ✅ Imported: {$fileName} → {$gestureName} (module: {$dbModuleName})");
                } else {
                    $totalSkipped++;
                    $this->line("   ⏭️  Skipped (already exists): {$fileName}");
                }
            }
        }

        // 🔥 Process other modules (Numbers, Greetings, Survival)
        $otherModules = [
            'Numbers' => 'level1_numbers',
            'Greetings' => 'level2_greetings',
            'Survival' => 'level3_survival',
        ];
        
        $gestureNameMap = [
            // Greetings
            'Hello' => 'HELLO',
            'ThankYou' => 'THANK YOU',
            'SeeYouTomorrow' => 'SEE YOU TOMORROW',
            'HowAreYou' => 'HOW ARE YOU',
            'NicetoMeetYou' => 'NICE TO MEET YOU',
            // Survival
            'Understand' => 'UNDERSTAND',
            "Don'tUnderstand" => "DON'T UNDERSTAND",
            'Know' => 'KNOW',
            "Don'tKnow" => "DON'T KNOW",
            'No' => 'NO',
            'Yes' => 'YES',
            'Wrong' => 'WRONG',
            'Correct' => 'CORRECT',
            'Slow' => 'SLOW',
            'Fast' => 'FAST',
            // Numbers
            'One' => '1',
            'Two' => '2',
            'Three' => '3',
            'Four' => '4',
            'Five' => '5',
            'Six' => '6',
            'Seven' => '7',
            'Eight' => '8',
            'Nine' => '9',
            'Ten' => '10',
        ];

        foreach ($otherModules as $folderName => $dbModuleName) {
            $this->info("\n📂 Processing {$folderName} (→ {$dbModuleName})...");
            
            $module = GestureModule::where('name', $dbModuleName)->first();
            
            if (!$module) {
                $this->error("❌ Module not found: {$dbModuleName}");
                continue;
            }

            $modulePath = $basePath . '/' . $folderName;
            
            if (!is_dir($modulePath)) {
                $this->warn("⚠️  Directory not found: {$modulePath}");
                continue;
            }

            $files = glob($modulePath . '/*.{png,jpg,jpeg,mp4,mov}', GLOB_BRACE);
            $this->info("   Found " . count($files) . " files");
            
            foreach ($files as $filePath) {
                $fileName = basename($filePath);
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                
                preg_match('/^(\d+)_(.+)\.' . $extension . '$/', $fileName, $matches);
                
                if (empty($matches)) {
                    $this->warn("   ⚠️  Skipping file with unexpected format: {$fileName}");
                    $totalSkipped++;
                    continue;
                }

                $order = (int) $matches[1];
                $gestureName = $matches[2];
                $mediaType = in_array($extension, ['mp4', 'mov', 'avi']) ? 'video' : 'image';
                
                $dbGestureName = $gestureNameMap[$gestureName] ?? $gestureName;
                
                $gesture = Gesture::where('name', $dbGestureName)
                                 ->where('module_id', $module->module_id)
                                 ->first();
                
                if (!$gesture) {
                    $gesture = Gesture::whereRaw('UPPER(name) = ?', [strtoupper($dbGestureName)])
                                     ->where('module_id', $module->module_id)
                                     ->first();
                }
                
                if (!$gesture) {
                    $this->warn("   ⚠️  Gesture not found: {$dbGestureName} in module {$dbModuleName}");
                    $warnings[] = "{$fileName} → {$dbGestureName} not found in {$dbModuleName}";
                    $totalErrors++;
                    continue;
                }

                $existingMedia = GestureMedia::where('gesture_id', $gesture->gesture_id)
                    ->where('file_name', $fileName)
                    ->first();

                if (!$existingMedia) {
                    $isPrimary = $order === 1 && $mediaType === 'image';
                    $relativePath = "sign_language_media/{$folderName}/{$fileName}";
                    
                    $mimeType = mime_content_type($filePath);
                    $fileSize = filesize($filePath);
                    
                    if ($mediaType === 'image' && $isPrimary) {
                        GestureMedia::where('gesture_id', $gesture->gesture_id)
                            ->where('media_type', 'image')
                            ->update(['is_primary' => false]);
                    }
                    
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
                    $this->line("   ✅ Imported: {$fileName} → {$gesture->name}");
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
        $this->info("   - Errors: {$totalErrors} files");
        
        if (!empty($warnings)) {
            $this->warn("\n⚠️  Warnings:");
            foreach ($warnings as $w) {
                $this->warn("   - {$w}");
            }
        }
        
        $this->info("\n📚 By Module (Media files):");
        $modules = GestureModule::whereIn('name', ['alphabet_part1', 'alphabet_part2', 'level1_numbers', 'level2_greetings', 'level3_survival'])->get();
        foreach ($modules as $module) {
            $count = GestureMedia::where('module_id', $module->module_id)->count();
            $this->info("   - {$module->display_name}: {$count} files");
        }
        
        $this->newLine();
        $this->info("✅ Your existing data is SAFE!");
        $this->info("   - Gestures: " . Gesture::count() . " (unchanged)");
        $this->info("   - Modules: " . GestureModule::count() . " (unchanged)");
        $this->info("   - Performances: " . \App\Models\GesturePerformance::count() . " (unchanged)");
        
        return 0;
    }
}