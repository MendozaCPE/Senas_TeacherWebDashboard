<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('gesture_hint_usage', function (Blueprint $table) {
            // Change letter column to varchar(50) to support greetings
            $table->string('letter', 50)->change();
        });
    }

    public function down()
    {
        Schema::table('gesture_hint_usage', function (Blueprint $table) {
            $table->string('letter', 5)->change();
        });
    }
};