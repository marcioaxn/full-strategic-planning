<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Garante que rel_indicador_objetivo_organizacao esteja em performance_indicators.
     * Em bancos novos a tabela já é criada lá diretamente; em migrações legadas (v1)
     * ela podia estar no schema pei — nesse caso move-a para o schema correto.
     */
    public function up(): void
    {
        $inPei = DB::select("
            SELECT EXISTS (
                SELECT FROM information_schema.tables
                WHERE table_schema = 'pei'
                AND table_name = 'rel_indicador_objetivo_organizacao'
            )
        ")[0]->exists;

        if ($inPei) {
            DB::statement('ALTER TABLE pei.rel_indicador_objetivo_organizacao SET SCHEMA performance_indicators');
        }
        // Já em performance_indicators → no-op intencional.
    }

    /**
     * Reverte apenas se up() realmente moveu a tabela de pei → performance_indicators.
     * Em bancos novos a tabela nunca esteve em pei, portanto down() é no-op aqui;
     * a migration de criação (drop) se encarregará de removê-la ao continuar o rollback.
     */
    public function down(): void
    {
        // Nada a fazer: se up() foi no-op (banco novo), a tabela deve permanecer em
        // performance_indicators para que a migration anterior de criação possa removê-la.
        // Se up() moveu de pei → performance_indicators (banco legado), não reverter aqui
        // pois a migration de criação (down) também apaga de performance_indicators.
    }
};
