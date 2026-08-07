<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->enum('mastery_level', ['beginner', 'intermediate', 'advanced'])
                  ->default('beginner')
                  ->after('description');
        });
    }

    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('mastery_level');
        });
    }
};