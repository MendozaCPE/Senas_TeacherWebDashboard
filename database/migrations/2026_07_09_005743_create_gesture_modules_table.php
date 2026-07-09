//database/migrations/2026_07_09_005743_create_gesture_modules_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gesture_modules', function (Blueprint $table) {
            $table->id('module_id');
            $table->string('name', 50)->unique(); // 'alphabet_part1', 'alphabet_part2', 'numbers', 'greetings'
            $table->string('display_name', 100); // 'Alphabet Part 1 (A-M)', 'Alphabet Part 2 (N-Z)', etc.
            $table->text('description')->nullable();
            $table->enum('difficulty', ['Beginner', 'Intermediate', 'Advanced'])->default('Beginner');
            $table->string('model_file')->nullable(); // The main model file for this module
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0); // For ordering modules
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gesture_modules');
    }
};