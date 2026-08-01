// database/migrations/xxxx_xx_xx_create_student_settings_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_settings', function (Blueprint $table) {
            $table->id('setting_id');
            $table->unsignedBigInteger('student_id');
            $table->boolean('sound_enabled')->default(true);
            $table->boolean('notifications_enabled')->default(true);
            $table->timestamps();
            
            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('students')
                  ->onDelete('cascade');
            
            $table->unique('student_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_settings');
    }
};