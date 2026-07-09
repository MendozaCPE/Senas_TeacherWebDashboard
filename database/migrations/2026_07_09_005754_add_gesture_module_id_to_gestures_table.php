//database/migrations/2026_07_09_005754_add_gesture_module_id_to_gestures_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('gestures', function (Blueprint $table) {
            // Add module_id foreign key
            $table->unsignedBigInteger('module_id')->nullable()->after('gesture_id');
            $table->foreign('module_id')
                  ->references('module_id')
                  ->on('gesture_modules')
                  ->onDelete('set null');
            
            // Keep model_file for individual gesture models (if different from module model)
            // The module model_file is for the main model
        });
    }

    public function down()
    {
        Schema::table('gestures', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropColumn('module_id');
        });
    }
};