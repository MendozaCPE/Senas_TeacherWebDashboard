<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('specialization', ['SNED', 'Regular']);
            $table->timestamps();
        });
    }

    
    public function down()
    {
        Schema::dropIfExists('teachers');
    }
};
