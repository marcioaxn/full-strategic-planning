<?php

/*
 * Instalação nova precisa dos 18 ODS: sem eles a Agenda 2030 (painel, aderência
 * do ciclo e vínculo nos objetivos) fica vazia. Achado em 05/10/2026.
 */

use Database\Seeders\OdsSeeder;
use Illuminate\Support\Facades\DB;

test('o seeder cria os 18 ODS e pode rodar de novo sem duplicar nem apagar', function () {
    DB::table('strategic_planning.tab_ods')->delete();

    $this->seed(OdsSeeder::class);
    expect(DB::table('strategic_planning.tab_ods')->count())->toBe(18);

    // Nome alterado à mão é corrigido; nada é duplicado.
    DB::table('strategic_planning.tab_ods')->where('num_ods', 3)->update(['nom_ods' => 'Errado']);
    $this->seed(OdsSeeder::class);

    expect(DB::table('strategic_planning.tab_ods')->count())->toBe(18)
        ->and(DB::table('strategic_planning.tab_ods')->where('num_ods', 3)->value('nom_ods'))->toBe('Saúde e Bem-Estar')
        ->and(DB::table('strategic_planning.tab_ods')->where('num_ods', 18)->value('nom_ods'))->toContain('Étnico');
});
