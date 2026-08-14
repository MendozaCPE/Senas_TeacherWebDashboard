<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Use raw ALTER TABLE to avoid doctrine/dbal dependency on PHP 8.0
        DB::statement('ALTER TABLE gesture_hint_usage MODIFY COLUMN letter VARCHAR(50) NOT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE gesture_hint_usage MODIFY COLUMN letter VARCHAR(5) NOT NULL');
    }
};
