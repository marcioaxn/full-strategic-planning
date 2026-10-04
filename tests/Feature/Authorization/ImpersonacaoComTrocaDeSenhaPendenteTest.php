<?php

/*
 * Revisão de 04/10/2026, achados 1 e 3.
 *
 * 1. Assumir a identidade de uma conta com troca de senha pendente
 *    (trocarsenha = 1: toda conta criada com "enviar link" e todo
 *    autocadastro) prendia o Super Admin em /trocar-senha: o CheckPasswordChange
 *    redirecionava até o "Voltar". A única saída era "Sair".
 *
 * 3. O que o Super Admin fazia durante a impersonação ficava na auditoria
 *    como se fosse do usuário assumido.
 *
 * Login real e Auth::forgetGuards() entre as requisições: actingAs mascara os
 * defeitos de sessão (ver ImpersonacaoMantemSessaoTest).
 */

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Valor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Models\Audit;

function cenarioImpersonacaoSenhaPendente(): array
{
    // Na suíte o app roda em console, e a auditoria vem desligada para console.
    config(['audit.console' => true]);

    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);

    $admin = User::factory()->create(['password' => bcrypt('senha-de-teste-1'), 'trocarsenha' => 0]);
    $admin->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);

    $alvo = User::factory()->create(['password' => bcrypt('senha-do-alvo-1'), 'trocarsenha' => 1]);
    $alvo->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $org->cod_organizacao]);
    $alvo->organizacoes()->sync([$org->cod_organizacao]);

    return [$org, $admin, $alvo];
}

test('o Super Admin assume conta com troca de senha pendente, navega e volta', function () {
    [, $admin, $alvo] = cenarioImpersonacaoSenhaPendente();

    $this->post(route('login'), ['email' => $admin->email, 'password' => 'senha-de-teste-1']);
    Auth::forgetGuards();
    $this->post(route('impersonate.start', $alvo->id))->assertRedirect(route('dashboard'));

    // Não é mandado para /trocar-senha: a senha é do outro, não do admin.
    Auth::forgetGuards();
    $this->get(route('dashboard'))->assertOk()->assertSee('Modo Impersonação Ativo');

    Auth::forgetGuards();
    $this->post(route('impersonate.stop'))->assertRedirect(route('admin.perfis'));

    Auth::forgetGuards();
    $this->get(route('dashboard'))->assertOk();
    expect(Auth::guard('web')->id())->toBe($admin->id);
});

test('fora da impersonação, a conta com troca pendente continua obrigada a trocar a senha', function () {
    [, , $alvo] = cenarioImpersonacaoSenhaPendente();

    $this->post(route('login'), ['email' => $alvo->email, 'password' => 'senha-do-alvo-1']);
    Auth::forgetGuards();

    $this->get(route('dashboard'))->assertRedirect(route('auth.trocar-senha'));
});

test('o que se grava durante a impersonação fica na auditoria em nome do Super Admin, marcando quem foi assumido', function () {
    [$org, $admin, $alvo] = cenarioImpersonacaoSenhaPendente();
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027]);

    $this->post(route('login'), ['email' => $admin->email, 'password' => 'senha-de-teste-1']);
    Auth::forgetGuards();
    $this->post(route('impersonate.start', $alvo->id));
    Auth::forgetGuards();
    $this->get(route('dashboard'))->assertOk();

    // A sessão agora é a do alvo (o guard web devolve o alvo) e a escrita
    // passa pelo mesmo resolvedor de autor que qualquer tela usa.
    expect(Auth::guard('web')->id())->toBe($alvo->id);

    $valor = Valor::create([
        'cod_pei' => $pei->cod_pei,
        'cod_organizacao' => $org->cod_organizacao,
        'nom_valor' => 'Transparência',
        'dsc_valor' => 'Valor gravado durante a impersonação.',
    ]);

    $audit = Audit::where('auditable_id', $valor->getKey())->where('event', 'created')->firstOrFail();

    expect($audit->user_id)->toBe($admin->id)
        ->and((string) $audit->tags)->toContain('impersonando:'.$alvo->id);

    // E a tela da auditoria diz isso a quem lê a trilha.
    Auth::forgetGuards();
    $this->post(route('impersonate.stop'));
    Auth::forgetGuards();
    $this->get(route('audit.detalhes', $audit->id))
        ->assertOk()
        ->assertSee('Gravado assumindo a identidade de')
        ->assertSee($alvo->name);
});

test('sem impersonação, a auditoria registra o próprio usuário e nenhuma marca', function () {
    [$org, $admin] = cenarioImpersonacaoSenhaPendente();
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027]);

    $this->post(route('login'), ['email' => $admin->email, 'password' => 'senha-de-teste-1']);
    Auth::forgetGuards();
    $this->get(route('dashboard'))->assertOk();

    $valor = Valor::create([
        'cod_pei' => $pei->cod_pei,
        'cod_organizacao' => $org->cod_organizacao,
        'nom_valor' => 'Integridade',
        'dsc_valor' => 'Valor gravado sem impersonação.',
    ]);

    $audit = Audit::where('auditable_id', $valor->getKey())->where('event', 'created')->firstOrFail();

    expect($audit->user_id)->toBe($admin->id)
        ->and((string) $audit->tags)->not->toContain('impersonando');
});
