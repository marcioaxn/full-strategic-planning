<?php

/*
 * Regressão das correções da auditoria de segurança de 03/10/2026
 * (docs/security-audit, fora do git). Cada teste chama o caminho que a tela
 * usa — o método público Livewire, a rota — e confere o efeito no banco ou no
 * HTML. O identificador do achado está no nome do teste.
 */

use App\Livewire\ActionPlan\ListarPlanos;
use App\Livewire\Organization\DetalharOrganizacao;
use App\Livewire\PerformanceIndicators\LancarEvolucao;
use App\Livewire\PerformanceIndicators\ListarIndicadores;
use App\Livewire\RiskManagement\ListarRiscos;
use App\Livewire\Shared\SeletorOrganizacao;
use App\Livewire\Shared\StrategicAlertsBell;
use App\Livewire\UserManagement\ListarUsuarios;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\EntregaAnexo;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\RiskManagement\Risco;
use App\Models\StrategicAlert;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\AI\AiServiceFactory;
use App\Support\TextoSeguro;
use Database\Seeders\OrganizacaoRaizSeeder;
use Database\Seeders\SuperAdministradorSeeder;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/** Unidades A e B no mesmo ciclo, com um objetivo e uma iniciativa em cada. */
function audCenario(): array
{
    $orgA = Organization::create(['nom_organizacao' => 'Unidade A', 'sgl_organizacao' => 'UA']);
    $orgB = Organization::create(['nom_organizacao' => 'Unidade B', 'sgl_organizacao' => 'UB']);
    $pei = PEI::create(['dsc_pei' => 'Ciclo Auditoria', 'num_ano_inicio_pei' => (int) date('Y'), 'num_ano_fim_pei' => (int) date('Y') + 3]);
    $perspectiva = Perspectiva::create(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'Processos', 'num_nivel_hierarquico_apresentacao' => 1]);
    $objetivo = Objetivo::create([
        'cod_perspectiva' => $perspectiva->cod_perspectiva, 'nom_objetivo' => 'Objetivo',
        'dsc_objetivo' => 'Objetivo do teste.', 'num_nivel_hierarquico_apresentacao' => 1,
    ]);
    $planoA = audPlano($objetivo, $orgA, 'Iniciativa A');
    $planoB = audPlano($objetivo, $orgB, 'Iniciativa B');
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return compact('orgA', 'orgB', 'pei', 'objetivo', 'planoA', 'planoB');
}

function audPlano(Objetivo $objetivo, Organization $org, string $nome): PlanoDeAcao
{
    $plano = PlanoDeAcao::create([
        'cod_objetivo' => $objetivo->cod_objetivo, 'cod_organizacao' => $org->cod_organizacao,
        'cod_tipo_execucao' => TipoExecucao::ACAO, 'dsc_plano_de_acao' => $nome,
        'num_nivel_hierarquico_apresentacao' => 1, 'bln_status' => 'Em Andamento',
        'dte_inicio' => now()->startOfYear()->toDateString(), 'dte_fim' => now()->endOfYear()->toDateString(),
    ]);
    $plano->organizacoes()->sync([$org->cod_organizacao]);

    return $plano;
}

function audUsuario(string $perfil, Organization $org, ?PlanoDeAcao $plano = null, array $atributos = []): User
{
    $user = User::factory()->create(array_merge(['ativo' => true], $atributos));
    $user->perfisAcesso()->attach($perfil, ['cod_organizacao' => $org->cod_organizacao, 'cod_plano_de_acao' => $plano?->cod_plano_de_acao]);
    $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');
    Session::put('organizacao_selecionada_id', $org->cod_organizacao);

    return $user;
}

// ── IDR-01 ──────────────────────────────────────────────────────────────────

test('IDR-01: com o valor que a TELA envia ("Plano"), o indicador não é ligado à iniciativa de outra unidade', function () {
    $c = audCenario();
    $adminA = audUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['orgA']);

    Livewire::actingAs($adminA)->test(ListarIndicadores::class)
        ->set('form.nom_indicador', 'Indicador plantado')
        ->set('form.dsc_tipo', 'Plano')
        ->set('form.cod_plano_de_acao', $c['planoB']->cod_plano_de_acao)
        ->set('form.organizacoes_ids', [$c['orgA']->cod_organizacao])
        ->call('save')
        ->assertForbidden();

    expect(Indicador::where('cod_plano_de_acao', $c['planoB']->cod_plano_de_acao)->exists())->toBeFalse();
});

test('IDR-01 (controle): o Administrador liga o indicador à iniciativa da própria unidade', function () {
    $c = audCenario();
    $adminA = audUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['orgA']);

    Livewire::actingAs($adminA)->test(ListarIndicadores::class)
        ->set('form.nom_indicador', 'Indicador legítimo')
        ->set('form.dsc_indicador', 'Descrição')
        ->set('form.dsc_tipo', 'Iniciativa')
        ->set('form.cod_plano_de_acao', $c['planoA']->cod_plano_de_acao)
        ->set('form.organizacoes_ids', [$c['orgA']->cod_organizacao])
        ->call('save')
        ->assertHasNoErrors();

    expect(Indicador::where('cod_plano_de_acao', $c['planoA']->cod_plano_de_acao)->value('nom_indicador'))->toBe('Indicador legítimo');
});

// ── IDR-02 ──────────────────────────────────────────────────────────────────

test('IDR-02: editar o risco não transfere para outra unidade por chave injetada no formulário', function () {
    $c = audCenario();
    $adminA = audUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['orgA']);
    $risco = Risco::create([
        'cod_pei' => $c['pei']->cod_pei, 'cod_organizacao' => $c['orgA']->cod_organizacao,
        'dsc_titulo' => 'Risco A', 'txt_descricao' => 'Descrição do risco', 'dsc_categoria' => 'Operacional', 'dsc_status' => 'Identificado',
        'num_probabilidade' => 3, 'num_impacto' => 3, 'cod_responsavel_monitoramento' => $adminA->id,
    ]);

    Livewire::actingAs($adminA)->test(ListarRiscos::class)
        ->call('edit', $risco->cod_risco)
        ->set('form.cod_organizacao', $c['orgB']->cod_organizacao)
        ->set('form.dsc_titulo', 'Risco A (revisado)')
        ->call('save');

    expect($risco->fresh()->cod_organizacao)->toBe($c['orgA']->cod_organizacao)
        ->and($risco->fresh()->dsc_titulo)->toBe('Risco A (revisado)');
});

// ── IDR-04 ──────────────────────────────────────────────────────────────────

test('IDR-04: o Gestor não tira a iniciativa de uma das unidades dela', function () {
    $c = audCenario();
    $c['planoA']->organizacoes()->sync([$c['orgA']->cod_organizacao, $c['orgB']->cod_organizacao]);
    $gestor = audUsuario(PerfilAcesso::GESTOR_RESPONSAVEL, $c['orgA'], $c['planoA']);

    Livewire::actingAs($gestor)->test(ListarPlanos::class)
        ->call('edit', $c['planoA']->cod_plano_de_acao)
        ->set('organizacoes_ids', [$c['orgA']->cod_organizacao])
        ->call('save')
        ->assertForbidden();

    expect($c['planoA']->organizacoes()->count())->toBe(2);
});

// ── IDR-03 ──────────────────────────────────────────────────────────────────

test('IDR-03: o anexo de entrega fica no disco privado e só sai pela rota autorizada', function () {
    Storage::fake('local');
    Storage::fake('public');
    $c = audCenario();
    $entrega = Entrega::create(['cod_plano_de_acao' => $c['planoA']->cod_plano_de_acao, 'dsc_entrega' => 'Entrega', 'bln_status' => 'Não Iniciado', 'num_nivel_hierarquico_apresentacao' => 1]);
    Storage::disk('local')->put('entregas/anexos/contrato.pdf', '%PDF-1.4 teste');
    $anexo = EntregaAnexo::create([
        'cod_entrega' => $entrega->cod_entrega, 'cod_usuario' => User::factory()->create()->id, 'dsc_nome_arquivo' => 'contrato.pdf',
        'dsc_caminho' => 'entregas/anexos/contrato.pdf', 'dsc_mime_type' => 'application/pdf', 'num_tamanho_bytes' => 14,
    ]);

    expect($anexo->getUrl())->not->toContain('/storage/');

    $this->get($anexo->getUrl())->assertRedirect(); // visitante: vai para o login

    $deOutraUnidade = audUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['orgB']);
    // Negado: volta ao painel (tratamento padrão de acesso negado), sem o arquivo.
    $this->actingAs($deOutraUnidade)->get($anexo->getUrl())
        ->assertRedirect(route('dashboard'))
        ->assertDontSee('%PDF-1.4 teste', false);

    $daUnidade = audUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['orgA']);
    $this->actingAs($daUnidade)->get($anexo->getUrl())->assertOk();
});

// ── PRM-09 ──────────────────────────────────────────────────────────────────

test('PRM-09: mês e ano da evolução são validados no servidor', function () {
    $c = audCenario();
    $gestor = audUsuario(PerfilAcesso::GESTOR_RESPONSAVEL, $c['orgA'], $c['planoA']);
    $indicador = Indicador::create([
        'nom_indicador' => 'Ind', 'dsc_indicador' => 'Ind', 'bln_acumulado' => 'Não', 'dsc_periodo_medicao' => 'Mensal',
        'dsc_tipo' => 'Iniciativa', 'cod_plano_de_acao' => $c['planoA']->cod_plano_de_acao, 'dsc_unidade_medida' => 'Percentual (%)',
    ]);
    $indicador->organizacoes()->sync([$c['orgA']->cod_organizacao]);

    Livewire::actingAs($gestor)->test(LancarEvolucao::class, ['indicadorId' => $indicador->cod_indicador])
        ->set('mes', 13)
        ->set('vlr_realizado', '10')
        ->call('salvar')
        ->assertHasErrors(['mes']);

    expect($indicador->evolucoes()->count())->toBe(0);
});

// ── PRM-02 ──────────────────────────────────────────────────────────────────

test('PRM-02: a lista de e-mails da unidade não abre trocando propriedade no navegador', function () {
    $c = audCenario();
    $consulta = audUsuario(PerfilAcesso::CONSULTA, $c['orgA']);
    $colega = audUsuario(PerfilAcesso::GESTOR_RESPONSAVEL, $c['orgA'], null, ['email' => 'colega.sigiloso@orgao.gov.br']);

    $tela = Livewire::actingAs($consulta)->test(DetalharOrganizacao::class, ['id' => $c['orgA']->cod_organizacao]);
    $tela->assertDontSee('colega.sigiloso@orgao.gov.br');

    // A permissão não é mais propriedade pública: não há o que o navegador troque.
    expect(property_exists(DetalharOrganizacao::class, 'podeVerUsuarios'))->toBeFalse();
});

// ── PRM-04 ──────────────────────────────────────────────────────────────────

test('PRM-04: IA desligada no servidor não chama o provedor, e há limite por usuário', function () {
    SystemSetting::setValue('ai_api_key', 'chave-de-teste');
    SystemSetting::setValue('ai_enabled', false);
    expect(AiServiceFactory::make())->toBeNull();

    SystemSetting::setValue('ai_enabled', true);
    $this->actingAs(User::factory()->create());
    $feitas = collect(range(1, AiServiceFactory::LIMITE_POR_MINUTO + 1))->map(fn () => AiServiceFactory::make())->filter();
    expect($feitas)->toHaveCount(AiServiceFactory::LIMITE_POR_MINUTO);
});

// ── PRM-05 ──────────────────────────────────────────────────────────────────

test('PRM-05: conta de autocadastro sem e-mail confirmado não recebe perfil', function () {
    $c = audCenario();
    $super = audUsuario(PerfilAcesso::SUPER_ADMIN, $c['orgA']);
    $impostor = User::factory()->create(['email' => 'diretor@orgao.gov.br', 'email_verified_at' => null]);

    Livewire::actingAs($super)->test(ListarUsuarios::class)
        ->call('edit', $impostor->id)
        ->set('form.vinculos', [['org_id' => $c['orgA']->cod_organizacao, 'perfil_id' => PerfilAcesso::ADMIN_UNIDADE, 'org_label' => 'UA', 'perfil_label' => 'Administrador da Unidade']])
        ->call('save')
        ->assertHasErrors(['form.vinculos']);

    expect($impostor->fresh()->temPerfilDeAcesso())->toBeFalse();
});

// ── PRM-10 ──────────────────────────────────────────────────────────────────

test('PRM-10: Super Admin não assume a identidade de outro Super Admin', function () {
    $c = audCenario();
    $super = audUsuario(PerfilAcesso::SUPER_ADMIN, $c['orgA']);
    $outro = audUsuario(PerfilAcesso::SUPER_ADMIN, $c['orgA']);

    $this->actingAs($super)->post(route('impersonate.start', $outro->id))->assertRedirect(route('admin.perfis'));

    expect(session()->has('impersonator_id'))->toBeFalse();
});

// ── ISO-02 ──────────────────────────────────────────────────────────────────

test('ISO-02: o diretório só lista pessoas das unidades que o Administrador administra', function () {
    $c = audCenario();
    $admin = audUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['orgA']);
    $admin->perfisAcesso()->attach(PerfilAcesso::CONSULTA, ['cod_organizacao' => $c['orgB']->cod_organizacao]);
    $admin->unsetRelation('perfisAcesso');
    audUsuario(PerfilAcesso::GESTOR_RESPONSAVEL, $c['orgB'], null, ['email' => 'pessoa.da.b@orgao.gov.br']);
    audUsuario(PerfilAcesso::GESTOR_RESPONSAVEL, $c['orgA'], null, ['email' => 'pessoa.da.a@orgao.gov.br']);
    Session::put('organizacao_selecionada_id', $c['orgA']->cod_organizacao);

    Livewire::actingAs($admin)->test(ListarUsuarios::class)
        ->assertSee('pessoa.da.a@orgao.gov.br')
        ->assertDontSee('pessoa.da.b@orgao.gov.br');
});

// ── XSS-01 / XSS-02 ─────────────────────────────────────────────────────────

test('XSS-01: nome de unidade com HTML sai escapado no seletor', function () {
    $c = audCenario();
    // Raiz auto-referenciada, para entrar na árvore do seletor.
    $c['orgA']->update(['nom_organizacao' => '<img src=x onerror=alert(1)>', 'rel_cod_organizacao' => $c['orgA']->cod_organizacao]);
    $super = audUsuario(PerfilAcesso::SUPER_ADMIN, $c['orgA']);

    Livewire::actingAs($super)->test(SeletorOrganizacao::class)
        ->assertDontSeeHtml('<img src=x onerror=alert(1)>')
        ->assertSeeHtml('&lt;img src=x onerror=alert(1)&gt;');
});

test('XSS-02: mensagem de alerta com HTML sai escapada no sino', function () {
    $c = audCenario();
    $user = audUsuario(PerfilAcesso::ADMIN_UNIDADE, $c['orgA']);
    StrategicAlert::create([
        'user_id' => $user->id, 'cod_organizacao' => null, 'title' => 'Alerta',
        'message' => 'Polaridade <img src=x onerror=alert(1)>', 'icon' => 'bi-bell', 'type' => 'warning',
    ]);

    Livewire::actingAs($user)->test(StrategicAlertsBell::class)
        ->assertDontSeeHtml('<img src=x onerror=alert(1)>');
});

// ── XSS-05 / XSS-08 ─────────────────────────────────────────────────────────

test('XSS-05 e XSS-08: célula de planilha e Markdown de e-mail neutralizados', function () {
    expect(TextoSeguro::celula('=HYPERLINK("https://x")'))->toBe('\'=HYPERLINK("https://x")')
        ->and(TextoSeguro::celula('@SUM(A1)'))->toBe("'@SUM(A1)")
        ->and(TextoSeguro::celula('-15'))->toBe('-15')
        ->and(TextoSeguro::celula('Texto comum'))->toBe('Texto comum')
        ->and(TextoSeguro::markdownLiteral('[Clique](https://phish)'))->toBe('\[Clique\]\(https://phish\)');
});

// ── SEC-06 ──────────────────────────────────────────────────────────────────

test('SEC-06: o administrador semeado nasce com troca de senha obrigatória', function () {
    $this->seed(OrganizacaoRaizSeeder::class);
    $this->seed(SuperAdministradorSeeder::class);

    expect(User::where('email', SuperAdministradorSeeder::EMAIL)->first()->deveTrocarSenha())->toBeTrue();
});
