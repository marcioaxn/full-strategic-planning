<?php

/*
 * Correções da revisão adversária de 04/10/2026 no módulo RAE, pelo caminho
 * da tela (métodos públicos de GerenciarRae).
 */

use App\Livewire\StrategicPlanning\GerenciarRae;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Rae;
use App\Models\StrategicPlanning\RaeCausaRaiz;
use App\Models\StrategicPlanning\RaeEncaminhamento;
use App\Models\User;
use Livewire\Livewire;

/** @return array{user: User, org: Organization, pei: PEI, rae: Rae} */
function cenarioRaeRevisao(): array
{
    $pei = PEI::create(['dsc_pei' => 'Ciclo RAE', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027]);
    $org = Organization::create(['nom_organizacao' => 'Org RAE', 'sgl_organizacao' => 'ORAE', 'cod_organizacao_pai' => null]);

    $user = User::factory()->create(['ativo' => true]);
    $user->organizacoes()->attach($org->cod_organizacao);
    $user->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $org->cod_organizacao]);

    session(['pei_selecionado_id' => $pei->cod_pei, 'organizacao_selecionada_id' => $org->cod_organizacao]);

    $rae = Rae::create([
        'cod_pei' => $pei->cod_pei,
        'cod_organizacao' => $org->cod_organizacao,
        'dte_referencia' => '2026-10-15',
        'dsc_tipo_reuniao' => 'RAE',
    ]);

    return compact('user', 'org', 'pei', 'rae');
}

test('progresso geral 0 é gravado como 0, não como vazio', function () {
    $c = cenarioRaeRevisao();

    Livewire::actingAs($c['user'])->test(GerenciarRae::class)
        ->call('editarRae', $c['rae']->cod_rae)
        ->set('form.num_progresso_geral', '0')
        ->call('salvarRae')
        ->assertHasNoErrors();

    expect($c['rae']->fresh()->num_progresso_geral)->not->toBeNull()
        ->and((float) $c['rae']->fresh()->num_progresso_geral)->toBe(0.0);
});

test('progresso geral em branco continua gravando vazio', function () {
    $c = cenarioRaeRevisao();
    $c['rae']->update(['num_progresso_geral' => 40]);

    Livewire::actingAs($c['user'])->test(GerenciarRae::class)
        ->call('editarRae', $c['rae']->cod_rae)
        ->set('form.num_progresso_geral', '')
        ->call('salvarRae')
        ->assertHasNoErrors();

    expect($c['rae']->fresh()->num_progresso_geral)->toBeNull();
});

test('editar causa ligada a encaminhamento excluído salva, e o vínculo vira "nenhum"', function () {
    $c = cenarioRaeRevisao();
    $enc = RaeEncaminhamento::create(['cod_rae' => $c['rae']->cod_rae, 'dsc_tipo' => 'Outro', 'txt_descricao' => 'E', 'dsc_status' => 'Pendente']);
    $causa = RaeCausaRaiz::create([
        'cod_rae' => $c['rae']->cod_rae, 'dsc_problema' => 'Problema', 'json_cinco_porques' => ['a'],
        'cod_encaminhamento_vinculado' => $enc->cod_encaminhamento,
    ]);
    $enc->delete();

    Livewire::actingAs($c['user'])->test(GerenciarRae::class)
        ->call('editarCausa', $causa->cod_causa)
        ->assertSet('causaForm.cod_encaminhamento_vinculado', '')
        ->set('causaForm.dsc_problema', 'Problema revisto')
        ->call('salvarCausa')
        ->assertHasNoErrors()
        ->assertStatus(200);

    expect($causa->fresh()->dsc_problema)->toBe('Problema revisto')
        ->and($causa->fresh()->cod_encaminhamento_vinculado)->toBeNull();
});

test('salvar causa apontando para encaminhamento excluído (estado antigo da tela) não dá 403', function () {
    $c = cenarioRaeRevisao();
    $enc = RaeEncaminhamento::create(['cod_rae' => $c['rae']->cod_rae, 'dsc_tipo' => 'Outro', 'txt_descricao' => 'E', 'dsc_status' => 'Pendente']);

    $tela = Livewire::actingAs($c['user'])->test(GerenciarRae::class)
        ->call('novaCausa', $c['rae']->cod_rae)
        ->set('causaForm.dsc_problema', 'Problema')
        ->set('causaForm.cod_encaminhamento_vinculado', $enc->cod_encaminhamento);

    $enc->delete(); // excluído em outra aba enquanto o modal estava aberto

    $tela->call('salvarCausa')->assertHasNoErrors()->assertStatus(200);

    expect(RaeCausaRaiz::where('cod_rae', $c['rae']->cod_rae)->sole()->cod_encaminhamento_vinculado)->toBeNull();
});

test('encaminhamento de OUTRA RAE continua recusado ao vincular causa', function () {
    $c = cenarioRaeRevisao();
    $outraRae = Rae::create(['cod_pei' => $c['pei']->cod_pei, 'cod_organizacao' => $c['org']->cod_organizacao, 'dte_referencia' => '2026-09-01', 'dsc_tipo_reuniao' => 'RAE']);
    $encAlheio = RaeEncaminhamento::create(['cod_rae' => $outraRae->cod_rae, 'dsc_tipo' => 'Outro', 'txt_descricao' => 'X', 'dsc_status' => 'Pendente']);

    Livewire::actingAs($c['user'])->test(GerenciarRae::class)
        ->call('novaCausa', $c['rae']->cod_rae)
        ->set('causaForm.dsc_problema', 'Problema')
        ->set('causaForm.cod_encaminhamento_vinculado', $encAlheio->cod_encaminhamento)
        ->call('salvarCausa')
        ->assertForbidden();
});

test('a lixeira da causa raiz pede confirmação (wire:confirm) antes de excluir', function () {
    $c = cenarioRaeRevisao();
    RaeCausaRaiz::create(['cod_rae' => $c['rae']->cod_rae, 'dsc_problema' => 'Problema', 'json_cinco_porques' => []]);

    $html = Livewire::actingAs($c['user'])->test(GerenciarRae::class)
        ->call('toggleCausas', $c['rae']->cod_rae)
        ->html();

    expect($html)->toMatch('/wire:click="excluirCausa\([^"]*\)"[^>]*wire:confirm="[^"]+"/');
});

test('o mês de referência sai em português', function () {
    $c = cenarioRaeRevisao();

    Livewire::actingAs($c['user'])->test(GerenciarRae::class)
        ->assertSee('Ref.: Out/2026')
        ->assertDontSee('Oct/2026');
});

test('confirmar exclusão de encaminhamento exige permissão de excluir', function () {
    $c = cenarioRaeRevisao();
    $enc = RaeEncaminhamento::create(['cod_rae' => $c['rae']->cod_rae, 'dsc_tipo' => 'Outro', 'txt_descricao' => 'E', 'dsc_status' => 'Pendente']);

    $intruso = User::factory()->create(['ativo' => true]);
    $outra = Organization::create(['nom_organizacao' => 'Outra', 'sgl_organizacao' => 'OUT', 'cod_organizacao_pai' => null]);
    $intruso->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $outra->cod_organizacao]);
    session(['organizacao_selecionada_id' => $outra->cod_organizacao]);

    Livewire::actingAs($intruso)->test(GerenciarRae::class)
        ->call('confirmarExclusaoEnc', $enc->cod_encaminhamento)
        ->assertForbidden();
});
