// database/migrations/xxxx_xx_xx_add_streak_milestone_columns_to_students.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->integer('last_streak_milestone')->default(0)->after('streak_days');
            $table->integer('last_keep_going_notification')->default(0)->after('last_streak_milestone');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['last_streak_milestone', 'last_keep_going_notification']);
        });
    }
};