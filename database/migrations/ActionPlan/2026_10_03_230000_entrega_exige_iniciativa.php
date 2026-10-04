<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Entrega só existe dentro de uma iniciativa (regra do BSC). A coluna nasceu
 * anulável, e a tela /entregas cadastrava na iniciativa que o sistema escolhia
 * sozinho. O código passou a exigir a iniciativa; o banco agora também recusa
 * entrega sem ela.
 *
 * Medido antes de escrever (03/10/2026, banco de dev): 0 entregas sem iniciativa.
 * Se a base do cliente tiver alguma, a migration para com a contagem em vez de
 * apagar ou inventar vínculo — a decisão sobre esses registros é do gestor.
 *
 * ALTER COLUMN ... SET NOT NULL é compatível com PostgreSQL 9.3.
 */
return new class extends Migration
{
    public function up(): void
    {
        $orfas = (int) DB::table('action_plan.tab_entregas')->whereNull('cod_plano_de_acao')->count();

        if ($orfas > 0) {
            throw new RuntimeException(
                "Há {$orfas} entrega(s) sem iniciativa em action_plan.tab_entregas. "
                .'Vincule-as a uma iniciativa (ou exclua) antes de rodar esta migration.'
            );
        }

        DB::statement('ALTER TABLE action_plan.tab_entregas ALTER COLUMN cod_plano_de_acao SET NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE action_plan.tab_entregas ALTER COLUMN cod_plano_de_acao DROP NOT NULL');
    }
};
