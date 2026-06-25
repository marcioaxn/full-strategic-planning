<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            CREATE TABLE IF NOT EXISTS performance_indicators.rel_indicador_plano_de_acao (
                cod_indicador      UUID NOT NULL REFERENCES performance_indicators.tab_indicador(cod_indicador) ON DELETE CASCADE,
                cod_plano_de_acao  UUID NOT NULL REFERENCES action_plan.tab_plano_de_acao(cod_plano_de_acao) ON DELETE CASCADE,
                txt_justificativa  TEXT,
                created_at         TIMESTAMPTZ DEFAULT NOW(),
                PRIMARY KEY (cod_indicador, cod_plano_de_acao)
            )
        ');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS performance_indicators.rel_indicador_plano_de_acao');
    }
};
