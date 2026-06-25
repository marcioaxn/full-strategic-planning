<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            CREATE TABLE strategic_planning.tab_rae_encaminhamento (
                cod_encaminhamento  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                cod_rae             UUID NOT NULL
                    REFERENCES strategic_planning.tab_rae(cod_rae) ON DELETE CASCADE,
                cod_responsavel     UUID NULL
                    REFERENCES pei.users(id) ON DELETE SET NULL,
                cod_plano_vinculado UUID NULL,
                dsc_tipo            VARCHAR(30) NOT NULL DEFAULT \'Outro\'
                    CHECK (dsc_tipo IN (\'Novo Plano\', \'Revisão de Meta\', \'Revisão de Objetivo\', \'Revisão de Risco\', \'Outro\')),
                txt_descricao       TEXT NOT NULL,
                dsc_status          VARCHAR(20) NOT NULL DEFAULT \'Pendente\'
                    CHECK (dsc_status IN (\'Pendente\', \'Em Execução\', \'Concluído\')),
                dte_prazo           DATE NULL,
                created_at          TIMESTAMP NULL,
                updated_at          TIMESTAMP NULL,
                deleted_at          TIMESTAMP NULL
            )
        ');

        DB::statement('CREATE INDEX idx_rae_enc_cod_rae ON strategic_planning.tab_rae_encaminhamento(cod_rae)');
        DB::statement('CREATE INDEX idx_rae_enc_responsavel ON strategic_planning.tab_rae_encaminhamento(cod_responsavel)');
        DB::statement('CREATE INDEX idx_rae_enc_status ON strategic_planning.tab_rae_encaminhamento(dsc_status)');
        DB::statement('CREATE INDEX idx_rae_enc_prazo ON strategic_planning.tab_rae_encaminhamento(dte_prazo)');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS strategic_planning.tab_rae_encaminhamento CASCADE');
    }
};
