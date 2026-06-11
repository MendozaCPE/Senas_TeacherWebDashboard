<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Alter pin length to 6 and program_type to be nullable using raw SQL statements
        DB::statement("ALTER TABLE students MODIFY COLUMN pin VARCHAR(6) NOT NULL");
        DB::statement("ALTER TABLE students MODIFY COLUMN program_type ENUM('Regular', 'Inclusion', 'Transition', 'Self-contained') NULL");

        Schema::table('students', function (Blueprint $table) {
            // Add new column
            $table->enum('fsl_mastery_level', ['Beginner', 'Intermediate', 'Advanced'])->default('Beginner')->after('program_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('fsl_mastery_level');
        });

        DB::statement("ALTER TABLE students MODIFY COLUMN pin VARCHAR(4) NOT NULL");
        DB::statement("ALTER TABLE students MODIFY COLUMN program_type ENUM('Regular', 'Inclusion', 'Transition', 'Self-contained') NOT NULL");
    }
};
