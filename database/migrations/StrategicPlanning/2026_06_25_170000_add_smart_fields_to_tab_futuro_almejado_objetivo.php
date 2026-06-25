<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            ALTER TABLE strategic_planning.tab_futuro_almejado_objetivo
                ADD COLUMN IF NOT EXISTS dsc_situacao_atual       TEXT,
                ADD COLUMN IF NOT EXISTS dsc_indicador_referencia TEXT,
                ADD COLUMN IF NOT EXISTS vlr_referencia_meta      NUMERIC(15,4),
                ADD COLUMN IF NOT EXISTS dte_horizonte            DATE
        ');
    }

    public function down(): void
    {
        DB::statement('
            ALTER TABLE strategic_planning.tab_futuro_almejado_objetivo
                DROP COLUMN IF EXISTS dsc_situacao_atual,
                DROP COLUMN IF EXISTS dsc_indicador_referencia,
                DROP COLUMN IF EXISTS vlr_referencia_meta,
                DROP COLUMN IF EXISTS dte_horizonte
        ');
    }
};
