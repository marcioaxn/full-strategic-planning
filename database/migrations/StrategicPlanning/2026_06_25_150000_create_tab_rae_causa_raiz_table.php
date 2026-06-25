<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS strategic_planning.tab_rae_causa_raiz (
                cod_causa                    UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                cod_rae                      UUID NOT NULL REFERENCES strategic_planning.tab_rae(cod_rae) ON DELETE CASCADE,
                dsc_problema                 TEXT NOT NULL,
                json_cinco_porques           JSONB DEFAULT '[]',
                dsc_causa_raiz               TEXT,
                dsc_categoria_ishikawa       VARCHAR(20) CHECK (dsc_categoria_ishikawa IN ('Método','Máquina','Mão de Obra','Material','Medida','Meio Ambiente')),
                cod_encaminhamento_vinculado UUID REFERENCES strategic_planning.tab_rae_encaminhamento(cod_encaminhamento) ON DELETE SET NULL,
                created_at                   TIMESTAMPTZ DEFAULT NOW(),
                updated_at                   TIMESTAMPTZ DEFAULT NOW()
            )
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS strategic_planning.tab_rae_causa_raiz');
    }
};
