<?php

/*
 * Duas telas que prometiam o que não entregam — item 10 do pedido do gestor,
 * que disse não conseguir explicá-las aos clientes.
 *
 *  • HISTÓRICO: um botão "Download" em toda linha, para registros que não têm
 *    arquivo guardado. O clique devolvia "o arquivo não está mais disponível",
 *    contradizendo o texto no topo da própria tela.
 *
 *  • AGENDAMENTO: aceitava "enviar toda segunda-feira" e confirmava, mesmo
 *    quando a tarefa do servidor que dispara os envios nunca foi configurada.
 *    O e-mail não chegava nunca, sem uma linha de aviso.
 */

use App\Models\Reports\RelatorioGerado;
use App\Models\SystemSetting;
use App\Support\AgendadorDeRelatorios;

test('registro sem arquivo oferece GERAR DE NOVO, não download', function () {
    $registro = new RelatorioGerado([
        'dsc_tipo_relatorio' => 'Indicadores',
        'dsc_rota' => 'relatorios.indicadores.pdf',
        'dsc_caminho_arquivo' => '',
        'dsc_formato' => 'pdf',
        'txt_filtros_aplicados' => ['ano' => 2026, 'periodo' => 'anual'],
    ]);

    expect($registro->temArquivoGuardado())->toBeFalse();

    $url = $registro->urlParaRegerar();

    expect($url)->toBeString()
        ->and($url)->toContain('ano=2026')
        ->and($url)->toContain('periodo=anual');
});

test('registro com arquivo continua oferecendo o download', function () {
    $registro = new RelatorioGerado([
        'dsc_tipo_relatorio' => 'Indicadores',
        'dsc_caminho_arquivo' => 'relatorios/2026/indicadores.pdf',
        'dsc_formato' => 'pdf',
    ]);

    expect($registro->temArquivoGuardado())->toBeTrue();
});

test('rota inexistente não vira link quebrado', function () {
    // Registros gravados antes da migration não têm rota; e uma rota pode ser
    // removida entre versões. Melhor não oferecer botão do que oferecer 404.
    expect((new RelatorioGerado(['dsc_rota' => null]))->urlParaRegerar())->toBeNull()
        ->and((new RelatorioGerado(['dsc_rota' => 'rota.que.nao.existe']))->urlParaRegerar())->toBeNull();
});

test('a tela do histórico não promete download para todo registro', function () {
    $blade = file_get_contents(resource_path('views/livewire/reports/historico-relatorios.blade.php'));

    // A ação é condicional ao arquivo existir, e o rótulo diz o que faz.
    expect($blade)->toContain('temArquivoGuardado()')
        ->and($blade)->toContain('Gerar de novo')
        ->and($blade)->toContain('Baixar arquivo');

    // E os filtros — que o texto da tela promete guardar — são exibidos.
    expect($blade)->toContain('Filtros aplicados');
});

test('sem pulso do agendador, o sistema avisa em vez de aceitar o agendamento', function () {
    SystemSetting::query()->where('key', AgendadorDeRelatorios::CHAVE_PULSO)->delete();

    expect(AgendadorDeRelatorios::ativo())->toBeFalse()
        ->and(AgendadorDeRelatorios::aviso())->toContain('não foi ativado neste servidor');
});

test('com pulso recente, o agendamento é oferecido', function () {
    SystemSetting::setValue(AgendadorDeRelatorios::CHAVE_PULSO, now()->subMinutes(10)->toIso8601String());

    expect(AgendadorDeRelatorios::ativo())->toBeTrue()
        ->and(AgendadorDeRelatorios::aviso())->toBeNull();
});

test('pulso velho é tratado como agendador parado, com a data', function () {
    $parouEm = now()->subDays(3);
    SystemSetting::setValue(AgendadorDeRelatorios::CHAVE_PULSO, $parouEm->toIso8601String());

    expect(AgendadorDeRelatorios::ativo())->toBeFalse()
        ->and(AgendadorDeRelatorios::aviso())->toContain('parado desde')
        ->and(AgendadorDeRelatorios::aviso())->toContain($parouEm->format('d/m/Y'));
});
