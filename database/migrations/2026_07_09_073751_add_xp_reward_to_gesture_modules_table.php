// database/migrations/2026_07_09_xxxxxx_add_xp_reward_to_gesture_modules_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('gesture_modules', function (Blueprint $table) {
            $table->integer('xp_reward')->default(40)->after('order');
        });
    }

    public function down()
    {
        Schema::table('gesture_modules', function (Blueprint $table) {
            $table->dropColumn('xp_reward');
        });
    }
};