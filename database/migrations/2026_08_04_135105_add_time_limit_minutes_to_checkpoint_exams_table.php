<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('checkpoint_exams', function (Blueprint $table) {
            $table->integer('time_limit_minutes')->default(60)->after('passing_score');
        });
    }

    public function down()
    {
        Schema::table('checkpoint_exams', function (Blueprint $table) {
            $table->dropColumn('time_limit_minutes');
        });
    }
};