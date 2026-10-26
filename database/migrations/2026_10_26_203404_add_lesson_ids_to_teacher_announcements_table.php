<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('teacher_announcements') && !Schema::hasColumn('teacher_announcements', 'lesson_ids')) {
            Schema::table('teacher_announcements', function (Blueprint $table) {
                $table->json('lesson_ids')->nullable()->after('lesson_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('teacher_announcements') && Schema::hasColumn('teacher_announcements', 'lesson_ids')) {
            Schema::table('teacher_announcements', function (Blueprint $table) {
                $table->dropColumn('lesson_ids');
            });
        }
    }
};
