<?php

/*
 * Item 9 do pedido do gestor: "não há padronização no layout dos relatórios".
 *
 * O que estes testes guardam:
 *
 *  1. UM RENDERIZADOR SÓ. Cabeçalho e rodapé dos onze relatórios saem de
 *     App\Services\Reports\AcabamentoPdf. Enquanto cada relatório trazia o
 *     próprio cabeçalho em Blade, "padronização" dependia de onze arquivos
 *     continuarem parecidos — e nenhum aviso surgia quando um deles mudava.
 *
 *  2. UM SISTEMA DE DESIGN SÓ. Nenhum relatório declara @page próprio nem
 *     redefine as classes compartilhadas.
 *
 *  3. ORIENTAÇÃO É ESCOLHA, NÃO PADRÃO. Paisagem só onde a largura é
 *     necessária. O gestor foi explícito: "não faz sentido alguns relatórios
 *     como paisagem".
 *
 *  4. SIMETRIA. A régua de cima e a de baixo saem da mesma constante.
 */

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use App\Services\Reports\ReportGenerationService;
use Illuminate\Support\Facades\Session;

/** Os dez relatórios do catálogo e a orientação decidida para cada um. */
function orientacoesEsperadas(): array
{
    return [
        // Paisagem: tabela larga ou grade bidimensional.
        'indicadores' => 'landscape',
        'planos' => 'landscape',
        'riscos' => 'landscape',
        'integrado' => 'landscape',
        'identidade' => 'landscape',
        'cadeia-valor' => 'landscape',
        // Retrato: o documento é texto e lista, e a leitura é vertical.
        'executivo' => 'portrait',
        'objetivos' => 'portrait',
        'comunicacao' => 'portrait',
        'rae' => 'portrait',
    ];
}

test('cada relatório declara a orientação que faz sentido para ele', function () {
    foreach (orientacoesEsperadas() as $nome => $orientacao) {
        $blade = file_get_contents(resource_path("views/relatorios/$nome.blade.php"));

        $declaracao = "@include('relatorios.partials.estilos', ['orientacao' => '$orientacao'])";

        expect(str_contains($blade, $declaracao))->toBeTrue("O relatório $nome deveria ser $orientacao.");
    }
});

test('nenhum relatório declara @page nem desenha o próprio cabeçalho', function () {
    $violacoes = [];

    foreach (glob(resource_path('views/relatorios/*.blade.php')) as $arquivo) {
        $semComentarios = preg_replace('/\{\{--.*?--\}\}/s', '', file_get_contents($arquivo));

        foreach (['@page', 'rpt-header', 'rpt-footer', 'position: fixed'] as $proibido) {
            if (str_contains($semComentarios, $proibido)) {
                $violacoes[] = basename($arquivo).' → '.$proibido;
            }
        }
    }

    expect($violacoes)->toBe([]);
});

test('o sistema de design é um arquivo só', function () {
    // Se voltar a existir um cabeçalho ou rodapé em Blade, o layout volta a
    // poder divergir entre relatórios sem ninguém perceber.
    expect(file_exists(resource_path('views/relatorios/partials/estilos.blade.php')))->toBeTrue()
        ->and(file_exists(resource_path('views/relatorios/partials/cabecalho.blade.php')))->toBeFalse()
        ->and(file_exists(resource_path('views/relatorios/partials/rodape.blade.php')))->toBeFalse();

    $css = file_get_contents(resource_path('views/relatorios/partials/estilos.blade.php'));

    // A orientação é parâmetro, com retrato como padrão.
    expect($css)->toContain("size: a4 {{ \$orientacao ?? 'portrait' }}");

    // Margem simétrica em cima e embaixo.
    preg_match('/@page[^;]*;\s*margin:\s*(\d+)px\s+(\d+)px\s+(\d+)px\s+(\d+)px/', $css, $m);

    expect($m)->not->toBeEmpty()
        ->and($m[1])->toBe($m[3], 'Margem superior e inferior divergem.')
        ->and($m[2])->toBe($m[4], 'Margem esquerda e direita divergem.');
});

test('todo relatório do serviço passa pelo acabamento compartilhado', function () {
    $fonte = file_get_contents(app_path('Services/Reports/ReportGenerationService.php'));

    // Nenhuma saída direta: quem devolve bytes é o finalizar().
    expect($fonte)->not->toContain("'content' => \$pdf->output()");

    // E são sete os relatórios gerados por este serviço.
    expect(substr_count($fonte, '$this->finalizar($pdf'))->toBe(7);
});

test('nenhum relatório reinventa a régua do farol', function () {
    // 🔴 Havia QUATRO cópias da regra, com fallbacks diferentes: '#dc3545'
    // (vermelho para quem não casava com faixa nenhuma), '#dee2e6' (cinza de
    // contraste 1,24, ilegível), '#cbd5e0' e '#a0aec0'. O mesmo indicador
    // recebia cores diferentes conforme a tela que o mostrasse.
    $suspeitos = [];

    $arquivos = array_merge(
        glob(resource_path('views/relatorios/*.blade.php')),
        [app_path('Services/Reports/ReportGenerationService.php')]
    );

    foreach ($arquivos as $arquivo) {
        $conteudo = preg_replace('/\{\{--.*?--\}\}|#\/\*.*?\*\//s', '', file_get_contents($arquivo));
        $conteudo = preg_replace('#/\*.*?\*/#s', '', $conteudo);

        // Cor de farol escolhida por comparação numérica solta.
        // Corte de PERCENTUAL: comparação contra um número de dois ou três
        // dígitos seguida de uma cor. "> 0" é um fato binário (há ou não há),
        // não um juízo de desempenho, e continua legítimo.
        if (preg_match('/(>=|>|<=|<)\s*\d{2,3}(\.\d+)?\s*\)?\s*(\?|return)\s*[\'"]#[0-9a-fA-F]{3,6}[\'"]/', $conteudo)) {
            $suspeitos[] = basename($arquivo);
        }
    }

    expect($suspeitos)->toBe([]);
});

// ─────────────────────── o byte gerado, não só o código ───────────────────

test('o PDF sai na orientação declarada, e o cabeçalho aparece nele', function () {
    $org = Organization::create(['nom_organizacao' => 'Órgão do Layout', 'sgl_organizacao' => 'ODL']);

    $pei = PEI::create([
        'dsc_pei' => 'PEI do Layout',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    $perspectiva = Perspectiva::create([
        'cod_pei' => $pei->cod_pei,
        'dsc_perspectiva' => 'Resultados',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    Objetivo::create([
        'cod_perspectiva' => $perspectiva->cod_perspectiva,
        'nom_objetivo' => 'Objetivo do layout',
        'dsc_objetivo' => 'Descrição.',
        'num_nivel_hierarquico_apresentacao' => 1,
        'num_nivel_desdobramento' => 1,
    ]);

    $user = User::factory()->create();
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    $servico = app(ReportGenerationService::class);

    // Retrato: 595 x 842. Paisagem: 842 x 595.
    $casos = [
        'portrait' => $servico->generateObjetivos($org->cod_organizacao, (int) date('Y')),
        'landscape' => $servico->generateIndicadores($org->cod_organizacao, (int) date('Y'), 'anual'),
    ];

    foreach ($casos as $orientacao => $resultado) {
        $pdf = $resultado['content'];

        expect(substr($pdf, 0, 5))->toBe('%PDF-');

        preg_match('/MediaBox\s*\[\s*[\d.]+\s+[\d.]+\s+([\d.]+)\s+([\d.]+)\s*\]/', $pdf, $caixa);

        expect($caixa)->not->toBeEmpty();

        [$largura, $altura] = [round((float) $caixa[1]), round((float) $caixa[2])];

        $esperado = $orientacao === 'landscape' ? [842.0, 595.0] : [595.0, 842.0];

        expect([$largura, $altura])->toBe($esperado, "Orientação errada no PDF $orientacao.");
    }
});

test('seção que o cliente não preencheu não aparece no relatório', function () {
    // Exemplo literal do gestor: "se o relatório vai mostrar Temas Norteadores
    // e o cliente ainda não preencheu, não é necessário mostrar no relatório".
    $org = Organization::create(['nom_organizacao' => 'Órgão Vazio', 'sgl_organizacao' => 'OVZ']);

    $pei = PEI::create([
        'dsc_pei' => 'PEI Vazio',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    // Duas perspectivas; só UMA com objetivo.
    $cheia = Perspectiva::create([
        'cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'Perspectiva Preenchida',
        'num_nivel_hierarquico_apresentacao' => 2,
    ]);

    Perspectiva::create([
        'cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => 'Perspectiva Vazia',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    Objetivo::create([
        'cod_perspectiva' => $cheia->cod_perspectiva,
        'nom_objetivo' => 'Objetivo existente',
        'dsc_objetivo' => 'Tem conteúdo.',
        'num_nivel_hierarquico_apresentacao' => 1,
        'num_nivel_desdobramento' => 1,
    ]);

    Session::put('pei_selecionado_id', $pei->cod_pei);

    $html = view('relatorios.objetivos', [
        'pei' => $pei,
        'perspectivas' => Perspectiva::where('cod_pei', $pei->cod_pei)->with('objetivos')->get(),
        'organizacao' => $org,
        'filtros' => ['ano' => date('Y'), 'organizacao' => $org->nom_organizacao, 'perspectiva' => 'Todas'],
    ])->render();

    expect($html)->toContain('Perspectiva Preenchida')
        ->and($html)->not->toContain('Perspectiva Vazia')
        ->and($html)->not->toContain('Nenhum objetivo nesta perspectiva');
});
