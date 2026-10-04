<?php

/*
 * M2: a evidência anexada ao lançamento não tinha como ser aberta. Agora sai
 * por rota autenticada, conferida pela IndicadorPolicy (view), do disco
 * privado. Mesmo padrão de entregas.anexo.
 */

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\StrategicPlanning\Arquivo;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\PerformanceIndicators\CenarioIndicador;

function evidenciaDoCenario(array $c, string $caminho = 'pei/evidencias/relatorio.pdf'): Arquivo
{
    $ev = EvolucaoIndicador::create([
        'cod_indicador' => $c['indicador']->cod_indicador,
        'num_ano' => (int) date('Y'), 'num_mes' => 1, 'vlr_realizado' => 5, 'bln_atualizado' => 'Sim',
    ]);

    return Arquivo::create([
        'cod_evolucao_indicador' => $ev->cod_evolucao_indicador,
        'txt_assunto' => 'relatorio.pdf',
        'data' => now()->format('Y-m-d'),
        'dsc_nome_arquivo' => $caminho,
        'dsc_tipo' => 'pdf',
    ]);
}

test('M2: quem pode ver o indicador abre a evidência', function () {
    Storage::fake('local');
    Storage::disk('local')->put('pei/evidencias/relatorio.pdf', '%PDF-conteudo');
    $c = CenarioIndicador::criar();
    $arquivo = evidenciaDoCenario($c);

    $resposta = $this->actingAs($c['user'])->get(route('indicadores.evidencia', $arquivo->cod_arquivo));

    $resposta->assertOk();
    expect($resposta->streamedContent())->toBe('%PDF-conteudo');
});

test('M2: quem não alcança a unidade do indicador é barrado (403 → volta ao painel com aviso)', function () {
    Storage::fake('local');
    Storage::disk('local')->put('pei/evidencias/relatorio.pdf', '%PDF-conteudo');
    $c = CenarioIndicador::criar();
    $arquivo = evidenciaDoCenario($c);

    $outra = Organization::create(['nom_organizacao' => 'Outra', 'sgl_organizacao' => 'OUT', 'cod_organizacao_pai' => null]);
    $estranho = User::factory()->create(['ativo' => true]);
    $estranho->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $outra->cod_organizacao]);

    // O handler de 403 (bootstrap/app.php) devolve o usuário ao painel com a
    // mensagem de acesso negado; o que importa é o arquivo não sair.
    $this->actingAs($estranho)
        ->get(route('indicadores.evidencia', $arquivo->cod_arquivo))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('error');
});

test('M2: visitante sem login não abre a evidência', function () {
    $c = CenarioIndicador::criar();
    $arquivo = evidenciaDoCenario($c);
    auth()->logout();

    $this->get(route('indicadores.evidencia', $arquivo->cod_arquivo))->assertRedirect();
});

test('M2: caminho fora da pasta de evidências não é servido', function () {
    Storage::fake('local');
    Storage::disk('local')->put('outra/pasta/segredo.txt', 'x');
    $c = CenarioIndicador::criar();
    $arquivo = evidenciaDoCenario($c, 'outra/pasta/segredo.txt');

    $this->actingAs($c['user'])
        ->get(route('indicadores.evidencia', $arquivo->cod_arquivo))
        ->assertNotFound();
});
