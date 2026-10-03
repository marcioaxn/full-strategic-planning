<?php

/*
 * Regressão do defeito encontrado no navegador em 03/10/2026: salvar um plano
 * de mitigação quebrava com "column dsc_tipo does not exist", e registrar
 * ocorrência quebrava do mesmo jeito. O Model gravava em colunas que a tabela
 * não tinha. Corrigido pela migration 2026_10_03_120000.
 *
 * Os testes passam pelo método que a TELA chama (save), não por um create()
 * direto no Model — que é exatamente o caminho que nenhum teste exercitava.
 */

use App\Livewire\RiskManagement\GerenciarMitigacoes;
use App\Livewire\RiskManagement\RegistrarOcorrencias;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\RiskManagement\Risco;
use App\Models\RiskManagement\RiscoMitigacao;
use App\Models\RiskManagement\RiscoOcorrencia;
use App\Models\StrategicPlanning\PEI;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

function cenarioRiscoComTela(): array
{
    $org = Organization::create(['nom_organizacao' => 'Órgão do Risco', 'sgl_organizacao' => 'ORIS']);

    $pei = PEI::create([
        'dsc_pei' => 'Ciclo do Risco',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    $user = User::factory()->create();
    $user->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $org->cod_organizacao]);
    $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    $risco = Risco::create([
        'cod_pei' => $pei->cod_pei,
        'cod_organizacao' => $org->cod_organizacao,
        'dsc_titulo' => 'Risco de teste',
        'txt_descricao' => 'Descrição do risco.',
        'dsc_categoria' => 'Operacional',
        'dsc_status' => 'Identificado',
        'num_probabilidade' => 3,
        'num_impacto' => 3,
        'cod_responsavel_monitoramento' => $user->getKey(),
    ]);

    return [$user, $risco];
}

test('salva o plano de mitigação pela tela', function () {
    [$user, $risco] = cenarioRiscoComTela();

    Livewire::actingAs($user)
        ->test(GerenciarMitigacoes::class, ['riscoId' => $risco->cod_risco])
        ->call('create')
        ->set('form.dsc_tipo', 'Prevenção')
        ->set('form.txt_descricao', 'Revisar contratos críticos.')
        ->set('form.cod_responsavel', $user->getKey())
        ->set('form.dte_prazo', now()->addMonth()->format('Y-m-d'))
        ->set('form.vlr_custo_estimado', 2500)
        ->call('save')
        ->assertHasNoErrors();

    $mitigacao = RiscoMitigacao::where('cod_risco', $risco->cod_risco)->first();

    expect($mitigacao)->not->toBeNull()
        ->and($mitigacao->txt_descricao)->toBe('Revisar contratos críticos.')
        ->and((float) $mitigacao->vlr_custo_estimado)->toBe(2500.0);
});

test('registra a ocorrência do risco pela tela', function () {
    [$user, $risco] = cenarioRiscoComTela();

    Livewire::actingAs($user)
        ->test(RegistrarOcorrencias::class, ['riscoId' => $risco->cod_risco])
        ->call('create')
        ->set('form.dte_ocorrencia', now()->format('Y-m-d'))
        ->set('form.txt_descricao', 'Atraso na entrega do fornecedor.')
        ->set('form.num_impacto_real', 4)
        ->call('save')
        ->assertHasNoErrors();

    $ocorrencia = RiscoOcorrencia::where('cod_risco', $risco->cod_risco)->first();

    expect($ocorrencia)->not->toBeNull()
        ->and($ocorrencia->txt_descricao)->toBe('Atraso na entrega do fornecedor.')
        ->and($ocorrencia->num_impacto_real)->toBe(4);
});
