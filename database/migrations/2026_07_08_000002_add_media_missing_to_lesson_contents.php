<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_contents', function (Blueprint $table) {
            $table->tinyInteger('media_missing')->default(0)->after('gesture_name')
                ->comment('1 if AI requested a gesture but no matching DB record was found');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_contents', function (Blueprint $table) {
            $table->dropColumn('media_missing');
        });
    }
};
