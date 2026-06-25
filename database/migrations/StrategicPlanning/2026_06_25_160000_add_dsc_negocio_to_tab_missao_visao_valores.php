<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE strategic_planning.tab_missao_visao_valores ADD COLUMN IF NOT EXISTS dsc_negocio TEXT');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE strategic_planning.tab_missao_visao_valores DROP COLUMN IF EXISTS dsc_negocio');
    }
};
