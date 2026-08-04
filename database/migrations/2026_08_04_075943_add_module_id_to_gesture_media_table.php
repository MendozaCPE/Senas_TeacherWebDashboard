<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('gesture_media', function (Blueprint $table) {
            $table->foreignId('module_id')->nullable()->after('gesture_id')
                  ->constrained('gesture_modules', 'module_id')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('gesture_media', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropColumn('module_id');
        });
    }
};