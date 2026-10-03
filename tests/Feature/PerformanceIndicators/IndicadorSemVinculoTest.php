<?php

/*
 * Achado na varredura real de 03/10/2026: um indicador sem objetivo nem
 * iniciativa derrubava a ficha com 500 ("dsc_plano_de_acao" on null).
 */

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

test('a ficha e a evolução de um indicador sem vínculo abrem sem erro', function () {
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $admin = User::factory()->create();
    $admin->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);

    $id = (string) Str::uuid();
    DB::table('performance_indicators.tab_indicador')->insert([
        'cod_indicador' => $id, 'nom_indicador' => 'Indicador solto', 'dsc_tipo' => 'Objetivo',
        'dsc_indicador' => 'x', 'dsc_unidade_medida' => '%', 'bln_acumulado' => 'Não', 'dsc_periodo_medicao' => 'Mensal',
    ]);

    $this->actingAs($admin)->get(route('indicadores.detalhes', $id))
        ->assertOk()
        ->assertSee('Sem vínculo com objetivo ou iniciativa');
    $this->actingAs($admin)->get('/indicadores/'.$id.'/evolucao')->assertStatus(200);
});
