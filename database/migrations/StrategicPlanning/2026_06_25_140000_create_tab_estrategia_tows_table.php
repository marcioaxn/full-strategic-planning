<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS strategic_planning.tab_estrategia_tows (
                cod_estrategia         UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                cod_pei                UUID NOT NULL REFERENCES strategic_planning.tab_pei(cod_pei) ON DELETE CASCADE,
                cod_organizacao        UUID NOT NULL REFERENCES organization.tab_organizacoes(cod_organizacao) ON DELETE CASCADE,
                dsc_tipo               VARCHAR(2) NOT NULL CHECK (dsc_tipo IN ('SO','ST','WO','WT')),
                dsc_estrategia         TEXT NOT NULL,
                txt_fundamentacao      TEXT,
                cod_objetivo_vinculado UUID REFERENCES strategic_planning.tab_objetivo(cod_objetivo) ON DELETE SET NULL,
                created_at             TIMESTAMPTZ DEFAULT NOW(),
                updated_at             TIMESTAMPTZ DEFAULT NOW(),
                deleted_at             TIMESTAMPTZ
            )
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS strategic_planning.tab_estrategia_tows');
    }
};
