<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * HashExistingPins
 *
 * One-time migration command to convert all plaintext student PINs
 * in the database to bcrypt hashes.
 *
 * Run ONCE after deploying the PIN hashing security fix:
 *   php artisan students:hash-pins
 *
 * Safe to run multiple times — already-hashed PINs are skipped.
 */
class HashExistingPins extends Command
{
    protected $signature   = 'students:hash-pins';
    protected $description = 'One-time migration: convert all plaintext student PINs to bcrypt hashes.';

    public function handle(): int
    {
        $students = Student::all();
        $total    = $students->count();
        $hashed   = 0;
        $skipped  = 0;

        $this->info("Processing {$total} student record(s)...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($students as $student) {
            // Already a bcrypt hash — skip (bcrypt hashes start with '$2y$')
            if (str_starts_with((string) $student->pin, '$2y$')) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $student->pin = Hash::make($student->pin);
            $student->save();
            $hashed++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Hashed: {$hashed} | Already hashed (skipped): {$skipped}");

        return self::SUCCESS;
    }
}
