<?php

/*
 * Achados da revisão adversária de 03/10/2026 nas telas de administração,
 * relatórios e painel — cada teste vai pelo caminho que a tela usa (rota HTTP
 * ou o componente Livewire), não pela regra isolada.
 */

use App\Livewire\Organization\ListarOrganizacoes;
use App\Livewire\UserManagement\ListarUsuarios;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\Reports\RelatorioAgendado;
use App\Models\Reports\RelatorioGerado;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function orgAdmin(string $nome, string $sigla, ?string $pai = null): Organization
{
    return Organization::create(['nom_organizacao' => $nome, 'sgl_organizacao' => $sigla, 'rel_cod_organizacao' => $pai]);
}

function usuarioComPerfil(string $perfil, Organization $org, array $atributos = []): User
{
    $user = User::factory()->create($atributos);
    $user->perfisAcesso()->attach($perfil, ['cod_organizacao' => $org->cod_organizacao]);
    $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso')->unsetRelation('organizacoes');

    return $user;
}

test('conta sem perfil (autocadastro) é levada à página de acesso pendente', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertRedirect(route('acesso.pendente'));
});

test('Gestor e Consulta não abrem o diretório de usuários', function (string $perfil) {
    $org = orgAdmin('Órgão A', 'ORGA');

    // Negação de acesso nesta aplicação volta ao Dashboard com aviso
    // (bootstrap/app.php, AccessDeniedHttpException) — não abre a tela.
    $this->actingAs(usuarioComPerfil($perfil, $org))
        ->get(route('usuarios.index'))
        ->assertRedirect(route('dashboard'));
})->with([
    'Gestor Responsável' => PerfilAcesso::GESTOR_RESPONSAVEL,
    'Consulta' => PerfilAcesso::CONSULTA,
]);

test('detalhe de unidade de fora do escopo é negado e o da própria não expõe e-mails a Gestor/Consulta', function (string $perfil) {
    $orgA = orgAdmin('Órgão A', 'ORGA');
    $orgB = orgAdmin('Órgão B', 'ORGB');
    usuarioComPerfil(PerfilAcesso::ADMIN_UNIDADE, $orgA, ['email' => 'colega.da.unidade@exemplo.gov.br']);
    $user = usuarioComPerfil($perfil, $orgA);

    $this->actingAs($user)->get(route('organizacoes.detalhes', $orgB->cod_organizacao))
        ->assertRedirect(route('dashboard'));

    $this->actingAs($user)->get(route('organizacoes.detalhes', $orgA->cod_organizacao))
        ->assertOk()
        ->assertDontSee('colega.da.unidade@exemplo.gov.br');
})->with([
    'Gestor Responsável' => PerfilAcesso::GESTOR_RESPONSAVEL,
    'Consulta' => PerfilAcesso::CONSULTA,
]);

test('Admin da unidade A lista os usuários de A e não os de B', function () {
    $orgA = orgAdmin('Órgão A', 'ORGA');
    $orgB = orgAdmin('Órgão B', 'ORGB');
    $admin = usuarioComPerfil(PerfilAcesso::ADMIN_UNIDADE, $orgA);
    usuarioComPerfil(PerfilAcesso::CONSULTA, $orgA, ['email' => 'servidor.a@exemplo.gov.br']);
    usuarioComPerfil(PerfilAcesso::CONSULTA, $orgB, ['email' => 'servidor.b@exemplo.gov.br']);

    session(['organizacao_selecionada_id' => $orgA->cod_organizacao]);

    Livewire::actingAs($admin)->test(ListarUsuarios::class)
        ->assertSee('servidor.a@exemplo.gov.br')
        ->assertDontSee('servidor.b@exemplo.gov.br');
});

test('editar o usuário preserva o vínculo de gestor de iniciativa', function () {
    $org = orgAdmin('Órgão A', 'ORGA');
    $super = usuarioComPerfil(PerfilAcesso::SUPER_ADMIN, $org);

    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => (int) date('Y'), 'num_ano_fim_pei' => (int) date('Y') + 3]);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'P', 'num_nivel_hierarquico_apresentacao' => 1]);
    $objetivo = Objetivo::create(['cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'O', 'dsc_objetivo' => 'O', 'num_nivel_hierarquico_apresentacao' => 1]);
    $plano = PlanoDeAcao::create([
        'cod_objetivo' => $objetivo->cod_objetivo, 'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO, 'dsc_plano_de_acao' => 'Iniciativa',
        'num_nivel_hierarquico_apresentacao' => 1, 'dte_inicio' => now()->toDateString(),
        'dte_fim' => now()->addYear()->toDateString(), 'bln_status' => 'Em Andamento',
    ]);

    $gestor = usuarioComPerfil(PerfilAcesso::CONSULTA, $org);
    $gestor->perfisAcesso()->attach(PerfilAcesso::GESTOR_RESPONSAVEL, [
        'cod_organizacao' => $org->cod_organizacao,
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
    ]);

    Livewire::actingAs($super)->test(ListarUsuarios::class)
        ->call('edit', $gestor->id)
        ->set('form.name', 'Nome Corrigido')
        ->call('save')
        ->assertHasNoErrors();

    expect($gestor->fresh()->name)->toBe('Nome Corrigido')
        ->and(DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')
            ->where('user_id', $gestor->id)
            ->where('cod_plano_de_acao', $plano->cod_plano_de_acao)
            ->exists())->toBeTrue();
});

test('a unidade não pode ser posta abaixo de uma subordinada a ela', function () {
    $raiz = orgAdmin('Raiz', 'RAIZ');
    $filha = orgAdmin('Filha', 'FILHA', $raiz->cod_organizacao);
    $super = usuarioComPerfil(PerfilAcesso::SUPER_ADMIN, $raiz);

    Livewire::actingAs($super)->test(ListarOrganizacoes::class)
        ->call('edit', $raiz->cod_organizacao)
        ->set('form.rel_cod_organizacao', $filha->cod_organizacao)
        ->call('save')
        ->assertHasErrors(['form.rel_cod_organizacao']);

    expect($raiz->fresh()->rel_cod_organizacao)->not->toBe($filha->cod_organizacao);
});

test('Relatório de Gestão sem organização sai da unidade de quem pede, não de todas', function () {
    $orgA = orgAdmin('Órgão Alfa do Teste de Escopo', 'ALFA');
    $orgB = orgAdmin('Órgão B', 'ORGB');
    $admin = usuarioComPerfil(PerfilAcesso::ADMIN_UNIDADE, $orgA);

    $this->actingAs($admin)
        ->get(route('relatorios.gestao.pdf', ['organizacao_id' => $orgB->cod_organizacao, 'ano' => (int) date('Y')]))
        ->assertForbidden();

    $resposta = $this->actingAs($admin)
        ->get(route('relatorios.gestao.docx', ['ano' => (int) date('Y')]));

    $resposta->assertOk();

    $arquivo = tempnam(sys_get_temp_dir(), 'docx');
    file_put_contents($arquivo, $resposta->streamedContent());
    $zip = new ZipArchive;
    $zip->open($arquivo);
    $texto = strip_tags((string) $zip->getFromName('word/document.xml'));
    $zip->close();
    @unlink($arquivo);

    expect($texto)->toContain('Órgão Alfa do Teste de Escopo');
});

test('agendamento de usuário inativo não roda e é desativado', function () {
    $org = orgAdmin('Órgão A', 'ORGA');
    $inativo = usuarioComPerfil(PerfilAcesso::ADMIN_UNIDADE, $org, ['ativo' => false]);

    $agendamento = RelatorioAgendado::create([
        'user_id' => $inativo->id,
        'dsc_tipo_relatorio' => 'riscos',
        'dsc_frequencia' => 'mensal',
        'txt_filtros' => ['organizacao_id' => $org->cod_organizacao],
        'dte_proxima_execucao' => now()->subHour(),
        'bln_ativo' => true,
    ]);

    $this->artisan('reports:process-scheduled')->assertSuccessful();

    expect($agendamento->fresh()->bln_ativo)->toBeFalse()
        ->and(RelatorioGerado::where('user_id', $inativo->id)->exists())->toBeFalse();
});
