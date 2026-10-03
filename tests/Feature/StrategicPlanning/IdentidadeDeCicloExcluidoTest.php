<?php

/*
 * Achado na varredura real de 03/10/2026: a ficha da Identidade de um PEI já
 * excluído dava 500 ("dsc_pei" on null). Registro de ciclo excluído não existe
 * mais para o usuário: 404.
 */

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\PEI;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

test('a ficha da identidade de um ciclo excluído responde 404, sem erro', function () {
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $admin = User::factory()->create();
    $admin->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);

    $pei = PEI::create(['dsc_pei' => 'Excluído', 'num_ano_inicio_pei' => 2026, 'num_ano_fim_pei' => 2029]);
    $id = (string) Str::uuid();
    DB::table('strategic_planning.tab_missao_visao_valores')->insert([
        'cod_missao_visao_valores' => $id, 'cod_pei' => $pei->cod_pei, 'cod_organizacao' => $org->cod_organizacao,
        'dsc_missao' => 'Missão', 'dsc_visao' => 'Visão', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($admin)->get(route('pei.identidade.detalhes', $id))->assertOk();

    $pei->delete();
    $this->actingAs($admin)->get(route('pei.identidade.detalhes', $id))->assertNotFound();
});
