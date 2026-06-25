<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE risk_management.tab_risco
                ADD COLUMN dsc_estrategia_resposta VARCHAR(20) NULL
                    CHECK (dsc_estrategia_resposta IN ('Mitigar','Evitar','Transferir','Aceitar')),
                ADD COLUMN txt_justificativa_estrategia TEXT NULL,
                ADD COLUMN dte_proxima_revisao DATE NULL
        ");

        DB::statement('CREATE INDEX idx_risco_estrategia ON risk_management.tab_risco(dsc_estrategia_resposta)');
        DB::statement('CREATE INDEX idx_risco_revisao ON risk_management.tab_risco(dte_proxima_revisao)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS risk_management.idx_risco_estrategia');
        DB::statement('DROP INDEX IF EXISTS risk_management.idx_risco_revisao');
        DB::statement('
            ALTER TABLE risk_management.tab_risco
                DROP COLUMN IF EXISTS dsc_estrategia_resposta,
                DROP COLUMN IF EXISTS txt_justificativa_estrategia,
                DROP COLUMN IF EXISTS dte_proxima_revisao
        ');
    }
};
