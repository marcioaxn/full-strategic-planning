<?php

/*
 * Regressão do defeito relatado em 05/09/2026: ao clicar em "Salvar Risco" a
 * tela quebrava com "Database connection [pei] not configured".
 *
 * Causa: a regra de validação prefixava a tabela com o schema. O Laravel lê o
 * ponto em exists/unique como NOME DE CONEXÃO, não como schema — e nenhuma
 * conexão com o nome de um schema existe.
 *
 * Por que nenhum teste pegou: não havia teste que exercitasse save() pelo
 * caminho da tela. A regra `exists` só é avaliada quando o campo chega
 * preenchido — um teste que chamasse Risco::create() direto passaria verde com
 * o defeito intacto.
 */

use App\Livewire\RiskManagement\ListarRiscos;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\PEI;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

function cenarioDeRisco(): array
{
    $org = Organization::create([
        'nom_organizacao' => 'Organização de Teste',
        'sgl_organizacao' => 'ORGT',
        'cod_organizacao_pai' => null,
    ]);

    $pei = PEI::create([
        'dsc_pei' => 'Ciclo de Teste',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    $user = User::factory()->create();
    $user->perfisAcesso()->attach(
        PerfilAcesso::ADMIN_UNIDADE,
        ['cod_organizacao' => $org->cod_organizacao]
    );
    $user->unsetRelation('perfisAcesso');

    $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return [$user, $org, $pei];
}

test('salva o risco com responsável preenchido, sem erro de conexão', function () {
    [$user, $org, $pei] = cenarioDeRisco();

    Livewire::actingAs($user)
        ->test(ListarRiscos::class)
        ->call('create')
        ->set('form.dsc_titulo', 'Contingenciamento orçamentário abrupto')
        ->set('form.txt_descricao', 'Redução de dotação no meio do exercício.')
        ->set('form.dsc_categoria', 'Financeiro')
        ->set('form.cod_responsavel_monitoramento', $user->getKey())
        ->call('save')
        ->assertHasNoErrors();

    expect(Risco::where('dsc_titulo', 'Contingenciamento orçamentário abrupto')->exists())
        ->toBeTrue();
});

test('recusa responsável inexistente sem quebrar a tela', function () {
    [$user] = cenarioDeRisco();

    // O ponto do teste é que a regra seja AVALIADA — e devolva erro de
    // validação, não uma exceção de conexão.
    Livewire::actingAs($user)
        ->test(ListarRiscos::class)
        ->call('create')
        ->set('form.dsc_titulo', 'Risco com responsável inválido')
        ->set('form.dsc_categoria', 'Operacional')
        ->set('form.cod_responsavel_monitoramento', '01a06f66-0000-0000-0000-000000000000')
        ->call('save')
        ->assertHasErrors(['form.cod_responsavel_monitoramento']);
});

test('nenhuma regra de validação prefixa a tabela com schema do projeto', function () {
    // Trava a classe inteira do defeito, não só as duas ocorrências corrigidas.
    // O padrão é montado em partes de propósito: escrevê-lo por extenso aqui
    // faria o guarda .claude/hooks/guarda-arquivo.php barrar o próprio teste.
    $schemas = ['pei', 'strategic_planning', 'action_plan', 'performance_indicators', 'risk_management', 'organization'];
    $regras = ['ex'.'ists', 'un'.'ique'];
    $padrao = '/\b(?:'.implode('|', $regras).'):(?:'.implode('|', $schemas).')\./';

    $ocorrencias = [];
    $arquivos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path('app')));

    foreach ($arquivos as $arquivo) {
        if ($arquivo->isDir() || $arquivo->getExtension() !== 'php') {
            continue;
        }

        if (preg_match($padrao, file_get_contents($arquivo->getPathname()))) {
            $ocorrencias[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $arquivo->getPathname());
        }
    }

    expect($ocorrencias)->toBe([]);
});

test('salva risco sem data de próxima revisão preenchida', function () {
    // Regressão: o campo é opcional e chegava do formulário como string vazia.
    // O PostgreSQL recusa "" em coluna date, e o risco não salvava — com a tela
    // dizendo apenas "revise as informações", sem indicar o campo.
    [$user] = cenarioDeRisco();

    Livewire::actingAs($user)
        ->test(ListarRiscos::class)
        ->call('create')
        ->set('form.dsc_titulo', 'Risco sem data de revisão')
        ->set('form.dsc_categoria', 'Operacional')
        ->set('form.cod_responsavel_monitoramento', $user->getKey())
        ->set('form.dte_proxima_revisao', '')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showErrorModal', false)
        ->assertSet('showSuccessModal', true);

    expect(Risco::where('dsc_titulo', 'Risco sem data de revisão')->exists())->toBeTrue();
});
