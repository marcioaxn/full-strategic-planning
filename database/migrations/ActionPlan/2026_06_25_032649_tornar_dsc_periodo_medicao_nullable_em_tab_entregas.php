<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE action_plan.tab_entregas ALTER COLUMN dsc_periodo_medicao DROP NOT NULL');
        DB::statement("UPDATE action_plan.tab_entregas SET dsc_periodo_medicao = NULL WHERE dsc_periodo_medicao = ''");
    }

    public function down(): void
    {
        DB::statement("UPDATE action_plan.tab_entregas SET dsc_periodo_medicao = '' WHERE dsc_periodo_medicao IS NULL");
        DB::statement('ALTER TABLE action_plan.tab_entregas ALTER COLUMN dsc_periodo_medicao SET NOT NULL');
    }
};
