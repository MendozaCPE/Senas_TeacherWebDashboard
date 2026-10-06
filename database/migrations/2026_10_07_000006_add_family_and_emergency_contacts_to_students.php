<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('mother_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_number', 40)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'mother_name',
                'father_name',
                'emergency_contact_name',
                'emergency_contact_number',
            ]);
        });
    }
};
