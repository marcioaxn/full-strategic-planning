<?php

/*
 * Aba "Atividade" do sino (pedido de 04/10/2026): o que OUTRAS pessoas fizeram
 * no sistema, em níveis, respeitando a unidade.
 *
 * As asserções passam pelo caminho da tela — o componente do sino via
 * Livewire::test — e, para a unidade de cada registro, pela linha que o
 * resolvedor grava em pei.audits ao salvar um model real.
 */

use App\Livewire\Shared\StrategicAlertsBell;
use App\Models\AtividadeLeitura;
use App\Models\Documento;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\RiskManagement\Risco;
use App\Models\RiskManagement\RiscoMitigacao;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\Valor;
use App\Models\User;
use App\Support\Auditoria\UnidadeDaAuditoria;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use OwenIt\Auditing\Models\Audit;

/**
 * Órgão raiz com duas unidades irmãs (A e B) e um leitor de cada perfil.
 *
 * @return array<string, mixed>
 */
function cenarioDoFeed(): array
{
    // Na suíte o app roda em console, e a auditoria vem desligada para console.
    config(['audit.console' => true]);

    $raiz = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $a = Organization::create(['nom_organizacao' => 'Unidade A', 'sgl_organizacao' => 'UA', 'rel_cod_organizacao' => $raiz->cod_organizacao]);
    $b = Organization::create(['nom_organizacao' => 'Unidade B', 'sgl_organizacao' => 'UB', 'rel_cod_organizacao' => $raiz->cod_organizacao]);
    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027]);

    $usuario = function (string $nome, string $perfil, Organization $org): User {
        $u = User::factory()->create(['name' => $nome, 'trocarsenha' => 0]);
        $u->perfisAcesso()->attach($perfil, ['cod_organizacao' => $org->cod_organizacao]);
        $u->unsetRelation('perfisAcesso');

        return $u;
    };

    return [
        'raiz' => $raiz, 'a' => $a, 'b' => $b, 'pei' => $pei,
        'autor' => $usuario('Fulana Autora', PerfilAcesso::ADMIN_UNIDADE, $raiz),
        'leitorA' => $usuario('Leitor A', PerfilAcesso::CONSULTA, $a),
        'adminA' => $usuario('Admin A', PerfilAcesso::ADMIN_UNIDADE, $a),
        'leitorB' => $usuario('Leitor B', PerfilAcesso::CONSULTA, $b),
        'super' => $usuario('Super', PerfilAcesso::SUPER_ADMIN, $raiz),
    ];
}

function riscoDoFeed(array $c, Organization $org, string $titulo): Risco
{
    return Risco::create([
        'cod_pei' => $c['pei']->cod_pei, 'cod_organizacao' => $org->cod_organizacao, 'num_codigo_risco' => random_int(1, 9999),
        'dsc_titulo' => $titulo, 'txt_descricao' => 'Descrição', 'dsc_categoria' => 'Operacional', 'dsc_status' => 'Identificado',
        'num_probabilidade' => 2, 'num_impacto' => 3, 'num_nivel_risco' => 6,
    ]);
}

/** Um registro de auditoria gravado direto, para cenários que nenhuma tela produz sob demanda. */
function auditoriaDoFeed(User $autor, string $tipo, array $antes, array $depois, ?string $org, array $extra = []): Audit
{
    return Audit::create(array_merge([
        'user_type' => User::class,
        'user_id' => $autor->id,
        'event' => 'updated',
        'auditable_type' => $tipo,
        'auditable_id' => (string) Str::uuid(),
        'old_values' => $antes,
        'new_values' => $depois,
        'cod_organizacao' => $org,
        'bln_institucional' => false,
    ], $extra));
}

function feedDe(User $leitor)
{
    return Livewire::actingAs($leitor)->test(StrategicAlertsBell::class)->call('abrirAtividade');
}

test('ao salvar um model real, a auditoria grava a unidade a que o registro pertence', function () {
    $c = cenarioDoFeed();
    $this->actingAs($c['autor']);

    $valor = Valor::create(['cod_pei' => $c['pei']->cod_pei, 'cod_organizacao' => $c['a']->cod_organizacao, 'nom_valor' => 'Valor da Unidade A', 'dsc_valor' => 'Texto']);
    $risco = riscoDoFeed($c, $c['b'], 'Risco da Unidade B');
    $mitigacao = RiscoMitigacao::create(['cod_risco' => $risco->cod_risco, 'dsc_tipo' => 'Preventiva', 'txt_descricao' => 'Plano', 'dsc_status' => 'Pendente', 'dte_prazo' => '2026-11-30']);
    $perspectiva = Perspectiva::create(['dsc_perspectiva' => 'Sociedade', 'num_nivel_hierarquico_apresentacao' => 1, 'cod_pei' => $c['pei']->cod_pei]);
    $objetivo = Objetivo::create(['nom_objetivo' => 'Objetivo institucional', 'dsc_objetivo' => 'Desc', 'num_nivel_hierarquico_apresentacao' => 1, 'cod_perspectiva' => $perspectiva->cod_perspectiva]);

    $linha = fn ($model) => Audit::where('auditable_id', $model->getKey())->where('event', 'created')->firstOrFail();

    expect($linha($valor)->cod_organizacao)->toBe($c['a']->cod_organizacao)
        ->and((bool) $linha($valor)->bln_institucional)->toBeFalse()
        // Pela relação natural: mitigação → risco → unidade.
        ->and($linha($mitigacao)->cod_organizacao)->toBe($c['b']->cod_organizacao)
        // Institucional do ciclo: sem unidade, marcado como visível a todos.
        ->and($linha($objetivo)->cod_organizacao)->toBeNull()
        ->and((bool) $linha($objetivo)->bln_institucional)->toBeTrue();

    // Usuário (dado pessoal): a unidade do primeiro vínculo de perfil, nunca institucional.
    expect(UnidadeDaAuditoria::unidadeDe($c['leitorA']))->toBe($c['a']->cod_organizacao)
        ->and(UnidadeDaAuditoria::institucional($c['leitorA']))->toBeFalse();
});

test('cada unidade vê só a própria atividade e a institucional; o Super Admin vê tudo', function () {
    $c = cenarioDoFeed();
    $this->actingAs($c['autor']);

    Valor::create(['cod_pei' => $c['pei']->cod_pei, 'cod_organizacao' => $c['a']->cod_organizacao, 'nom_valor' => 'Valor da Unidade A', 'dsc_valor' => 'Texto']);
    riscoDoFeed($c, $c['b'], 'Risco da Unidade B');
    $perspectiva = Perspectiva::create(['dsc_perspectiva' => 'Sociedade', 'num_nivel_hierarquico_apresentacao' => 1, 'cod_pei' => $c['pei']->cod_pei]);
    Objetivo::create(['nom_objetivo' => 'Objetivo institucional', 'dsc_objetivo' => 'Desc', 'num_nivel_hierarquico_apresentacao' => 1, 'cod_perspectiva' => $perspectiva->cod_perspectiva]);

    // Registro anterior à coluna: sem unidade e sem a marca de institucional.
    auditoriaDoFeed($c['autor'], Documento::class, [], ['nom_documento' => 'Registro antigo'], null, ['event' => 'created', 'bln_institucional' => null]);

    feedDe($c['leitorA'])
        ->assertSee('Valor da Unidade A')
        ->assertSee('Objetivo institucional')
        ->assertDontSee('Risco da Unidade B')
        ->assertDontSee('Registro antigo');

    feedDe($c['leitorB'])
        ->assertSee('Risco da Unidade B')
        ->assertSee('Objetivo institucional')
        ->assertDontSee('Valor da Unidade A');

    feedDe($c['super'])
        ->assertSee('Valor da Unidade A')
        ->assertSee('Risco da Unidade B')
        ->assertSee('Registro antigo');
});

test('alteração de dado pessoal só aparece ao Administrador da unidade e ao Super Admin, sem senha', function () {
    $c = cenarioDoFeed();
    $pessoa = User::factory()->create(['name' => 'Pessoa Monitorada']);

    auditoriaDoFeed($c['autor'], User::class,
        ['email' => 'antigo@orgao.gov.br', 'password' => '$2y$12$hashAntigoDaSenha'],
        ['email' => 'novo@orgao.gov.br', 'password' => '$2y$12$hashNovoDaSenha'],
        $c['a']->cod_organizacao,
        ['auditable_id' => $pessoa->id]
    );

    feedDe($c['leitorA'])->assertDontSee('Pessoa Monitorada');
    feedDe($c['leitorB'])->assertDontSee('Pessoa Monitorada');

    feedDe($c['adminA'])
        ->assertSee('Pessoa Monitorada')
        ->assertDontSee('hashNovoDaSenha')
        ->assertDontSee('hashAntigoDaSenha');

    feedDe($c['super'])->assertSee('Pessoa Monitorada')->assertDontSee('hashNovoDaSenha');
});

test('o feed não mostra o que o próprio usuário fez', function () {
    $c = cenarioDoFeed();

    auditoriaDoFeed($c['autor'], Documento::class, [], ['nom_documento' => 'Feito pela autora'], $c['a']->cod_organizacao, ['event' => 'created']);
    auditoriaDoFeed($c['adminA'], Documento::class, [], ['nom_documento' => 'Feito pelo admin'], $c['a']->cod_organizacao, ['event' => 'created']);

    feedDe($c['autor'])->assertSee('Feito pelo admin')->assertDontSee('Feito pela autora');
    feedDe($c['adminA'])->assertSee('Feito pela autora')->assertDontSee('Feito pelo admin');
});

test('edições seguidas do mesmo autor no mesmo registro em até 10 minutos viram uma linha', function () {
    $c = cenarioDoFeed();
    $id = (string) Str::uuid();
    $base = Carbon::parse('2026-10-04 09:00:00');
    Carbon::setTestNow($base->copy()->addHour());

    $edicao = fn (string $de, string $para, Carbon $quando) => auditoriaDoFeed($c['autor'], Documento::class,
        ['dsc_origem' => $de], ['dsc_origem' => $para], $c['a']->cod_organizacao,
        ['auditable_id' => $id, 'created_at' => $quando, 'updated_at' => $quando]);

    $edicao('Origem zero', 'Origem um', $base->copy()->subMinutes(30)); // fora da janela
    $edicao('Origem um', 'Origem dois', $base);
    $edicao('Origem dois', 'Origem tres', $base->copy()->addMinutes(3));
    $edicao('Origem tres', 'Origem quatro', $base->copy()->addMinutes(8));

    feedDe($c['leitorA'])
        ->assertViewHas('atividades', fn ($itens) => count($itens) === 2 && $itens[0]['edicoes'] === 3 && $itens[1]['edicoes'] === 1)
        ->assertSee('3 edições')
        // O antes da primeira e o depois da última.
        ->assertSee('de Origem um para Origem quatro')
        ->assertSee('de Origem zero para Origem um');

    Carbon::setTestNow();
});

test('níveis: exclusão é crítica, prazo é atenção, criação comum é informativa — com texto humano', function () {
    $c = cenarioDoFeed();
    $this->actingAs($c['autor']);

    $risco = riscoDoFeed($c, $c['b'], 'Risco excluído depois');
    $mitigacao = RiscoMitigacao::create(['cod_risco' => $risco->cod_risco, 'dsc_tipo' => 'Preventiva', 'txt_descricao' => 'Plano', 'dsc_status' => 'Pendente', 'dte_prazo' => '2026-11-30']);
    $mitigacao->update(['dte_prazo' => '2026-12-15']);
    // Mudar a probabilidade (e com ela o nível) do risco também pede atenção.
    $outro = riscoDoFeed($c, $c['b'], 'Risco que piorou');
    $outro->update(['num_probabilidade' => 5]);
    $risco->delete();

    $niveis = fn (string $evento, string $tipo) => fn ($itens) => collect($itens)->first(fn ($i) => $i['evento'] === $evento && $i['tipo'] === $tipo)['nivel'] ?? null;

    feedDe($c['leitorB'])
        ->assertViewHas('atividades', fn ($itens) => $niveis('deleted', Risco::class)($itens) === 'critico')
        ->assertViewHas('atividades', fn ($itens) => $niveis('updated', RiscoMitigacao::class)($itens) === 'atencao')
        ->assertViewHas('atividades', fn ($itens) => $niveis('updated', Risco::class)($itens) === 'atencao')
        ->assertViewHas('atividades', fn ($itens) => $niveis('created', RiscoMitigacao::class)($itens) === 'informativo')
        ->assertSee('Fulana Autora alterou a data de prazo da mitigação de risco de 30/11/2026 para 15/12/2026')
        ->assertSee('Fulana Autora excluiu o risco «Risco excluído depois»')
        // O nível também por texto, não só por cor.
        ->assertSee('Crítico')
        ->assertSee('Atenção');
});

test('o poll de 1 minuto só conta, no escopo da unidade, sem montar a lista nem re-renderizar', function () {
    $c = cenarioDoFeed();

    $sino = Livewire::actingAs($c['leitorA'])->test(StrategicAlertsBell::class)->assertSet('atividadeNova', 0);

    auditoriaDoFeed($c['autor'], Documento::class, [], ['nom_documento' => 'Chegou agora'], $c['a']->cod_organizacao, ['event' => 'created']);
    auditoriaDoFeed($c['autor'], Documento::class, [], ['nom_documento' => 'Da unidade B'], $c['b']->cod_organizacao, ['event' => 'created']);

    $consultas = [];
    DB::listen(function ($q) use (&$consultas) {
        $consultas[] = $q->sql;
    });

    $sino->call('atualizarContadores')->assertSet('atividadeNova', 1);

    // Nenhuma leitura da lista (lote de 150, nomes dos registros) e nenhum HTML devolvido.
    expect(collect($consultas)->filter(fn ($sql) => str_contains($sql, 'limit 150'))->all())->toBe([])
        ->and($sino->effects['html'] ?? null)->toBeNull();

    // A view tem o poll, com o modificador que pausa fora da tela.
    expect(file_get_contents(resource_path('views/livewire/shared/strategic-alerts-bell.blade.php')))
        ->toContain('wire:poll.60s.visible="atualizarContadores"');
});

test('o contador de atividade nova zera ao abrir a aba e ao marcar como lido', function () {
    $c = cenarioDoFeed();
    Carbon::setTestNow('2026-10-04 10:00:00');

    auditoriaDoFeed($c['autor'], Documento::class, [], ['nom_documento' => 'Novo 1'], $c['a']->cod_organizacao, ['event' => 'created']);
    auditoriaDoFeed($c['autor'], Documento::class, [], ['nom_documento' => 'Novo 2'], $c['a']->cod_organizacao, ['event' => 'created']);
    auditoriaDoFeed($c['autor'], Documento::class, [], ['nom_documento' => 'Da unidade B'], $c['b']->cod_organizacao, ['event' => 'created']);
    auditoriaDoFeed($c['leitorA'], Documento::class, [], ['nom_documento' => 'Meu'], $c['a']->cod_organizacao, ['event' => 'created']);

    Livewire::actingAs($c['leitorA'])->test(StrategicAlertsBell::class)
        ->assertSet('atividadeNova', 2)
        ->assertSee('2 atividades novas')
        ->call('abrirAtividade')
        ->assertSet('atividadeNova', 0);

    expect(AtividadeLeitura::find($c['leitorA']->id)?->dte_visto_ate?->toDateTimeString())->toBe('2026-10-04 10:00:00');

    Carbon::setTestNow('2026-10-04 10:05:00');
    auditoriaDoFeed($c['autor'], Documento::class, [], ['nom_documento' => 'Depois'], $c['a']->cod_organizacao, ['event' => 'created']);

    Livewire::actingAs($c['leitorA'])->test(StrategicAlertsBell::class)
        ->assertSet('atividadeNova', 1)
        ->call('marcarAtividadeComoLida')
        ->assertSet('atividadeNova', 0);

    Carbon::setTestNow();
});
