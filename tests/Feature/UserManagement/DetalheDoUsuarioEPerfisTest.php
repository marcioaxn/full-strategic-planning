<?php

/*
 * Revisão de 04/10/2026, achados 19 e 20.
 *
 * 19. O Administrador da unidade A via, na ficha de um usuário, as
 *     iniciativas que ele gere na unidade B.
 * 20. Em Perfis, o selo "Admin" lia a coluna legada `adm` (não o perfil), e
 *     "Assumir" aparecia para Super Admins e contas inativas — e só falhava
 *     depois do clique.
 */

use App\Livewire\Admin\GestaoPerfis;
use App\Livewire\UserManagement\DetalharUsuario;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Livewire\Livewire;

test('o Administrador da unidade A não vê, na ficha, as iniciativas que a pessoa gere na unidade B', function () {
    $raiz = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $a = Organization::create(['nom_organizacao' => 'Unidade A', 'sgl_organizacao' => 'UA', 'rel_cod_organizacao' => $raiz->cod_organizacao]);
    $b = Organization::create(['nom_organizacao' => 'Unidade B', 'sgl_organizacao' => 'UB', 'rel_cod_organizacao' => $raiz->cod_organizacao]);

    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027]);
    $persp = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'P', 'num_nivel_hierarquico_apresentacao' => 1]);
    $obj = Objetivo::create(['cod_perspectiva' => $persp->cod_perspectiva, 'nom_objetivo' => 'O', 'dsc_objetivo' => 'O', 'num_nivel_hierarquico_apresentacao' => 1, 'num_nivel_desdobramento' => 1]);

    $plano = fn (Organization $org, string $nome) => PlanoDeAcao::create([
        'cod_objetivo' => $obj->cod_objetivo, 'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO, 'dsc_plano_de_acao' => $nome,
        'num_nivel_hierarquico_apresentacao' => 1, 'bln_status' => 'Em Andamento',
        'dte_inicio' => '2026-01-01', 'dte_fim' => '2026-12-31',
    ]);
    $daA = $plano($a, 'Iniciativa da Unidade A');
    $daB = $plano($b, 'Iniciativa da Unidade B');

    $pessoa = User::factory()->create();
    $pessoa->perfisAcesso()->attach(PerfilAcesso::GESTOR_RESPONSAVEL, ['cod_organizacao' => $a->cod_organizacao, 'cod_plano_de_acao' => $daA->cod_plano_de_acao]);
    $pessoa->perfisAcesso()->attach(PerfilAcesso::GESTOR_RESPONSAVEL, ['cod_organizacao' => $b->cod_organizacao, 'cod_plano_de_acao' => $daB->cod_plano_de_acao]);
    $pessoa->organizacoes()->sync([$a->cod_organizacao, $b->cod_organizacao]);

    $adminA = User::factory()->create();
    $adminA->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $a->cod_organizacao]);
    $adminA->organizacoes()->sync([$a->cod_organizacao]);

    Livewire::actingAs($adminA)
        ->test(DetalharUsuario::class, ['id' => $pessoa->id])
        ->assertSee('Iniciativa da Unidade A')
        ->assertDontSee('Iniciativa da Unidade B');
});

test('em Perfis, "Assumir" só aparece para quem pode ser assumido, e o selo segue o perfil', function () {
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);

    $eu = User::factory()->create();
    $eu->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);

    $outroSuper = User::factory()->create(['name' => 'Outro Super', 'adm' => false]);
    $outroSuper->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $inativo = User::factory()->create(['name' => 'Conta Inativa', 'ativo' => false]);
    $comum = User::factory()->create(['name' => 'Pessoa Comum', 'adm' => true]); // coluna legada desatualizada

    $html = Livewire::actingAs($eu)->test(GestaoPerfis::class)->html();

    expect(substr_count($html, route('impersonate.start', $comum->id)))->toBe(1)
        ->and($html)->not->toContain(route('impersonate.start', $outroSuper->id))
        ->and($html)->not->toContain(route('impersonate.start', $inativo->id));

    // O selo é do PERFIL: "Pessoa Comum" tem adm=1 legado e não é Super Admin.
    $linhaComum = str($html)->after('Pessoa Comum')->before('</tr>');
    $linhaSuper = str($html)->after('Outro Super')->before('</tr>');
    expect((string) $linhaComum)->not->toContain('Super Admin')
        ->and((string) $linhaSuper)->toContain('Super Admin');
});
