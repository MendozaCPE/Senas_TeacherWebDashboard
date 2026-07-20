<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_promotions', function (Blueprint $table) {
            $table->boolean('is_viewed')->default(false)->after('promoted_at');
            $table->timestamp('viewed_at')->nullable()->after('is_viewed');
        });
    }

    public function down()
    {
        Schema::table('student_promotions', function (Blueprint $table) {
            $table->dropColumn(['is_viewed', 'viewed_at']);
        });
    }
};