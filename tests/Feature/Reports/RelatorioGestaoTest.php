<?php

/*
 * Relatório de Gestão — o documento que o órgão presta à sociedade.
 *
 * O que estes testes protegem, e por quê:
 *
 *  1. O ARQUIVO SAI. PDF que começa com %PDF e DOCX que abre como zip com o
 *     document.xml dentro. "A rota respondeu 200" não prova nada: streamDownload
 *     devolve 200 com corpo quebrado.
 *
 *  2. OS CABEÇALHOS DAS TABELAS 2.2.1 E 2.2.2 SÃO OS DO MODELO OFICIAL. Este
 *     documento é comparado lado a lado com o Relatório de Gestão 2025 da
 *     Presidência. Cabeçalho diferente é divergência visível na primeira
 *     tabela — e é justamente onde a renomeação "Plano de Ação" → "Iniciativas"
 *     tinha de ter chegado.
 *
 *  3. AS DUAS VARIANTES CUMPREM O QUE PROMETEM. A réplica traz o esqueleto
 *     completo do modelo com toda seção externa nomeando sua fonte; a autoral
 *     não traz UMA seção vazia. Uma autoral com seção vazia é exatamente o
 *     documento que o gestor não quer entregar.
 *
 *  4. PEI INCOMPLETO NÃO QUEBRA. O cliente gera relatório no meio do
 *     preenchimento do ciclo, com objetivo sem indicador e iniciativa sem
 *     entrega. Explodir aí é o modo de falha mais provável em produção.
 *
 *  5. O ANO É O EXERCÍCIO RELATADO, não o ano corrente; o CICLO é o
 *     selecionado no topo (decisão de 04/10/2026). Relatório de 2025 emitido
 *     em 2026: seleciona-se o ciclo de 2025.
 */

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\Reports\RelatorioGerado;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use App\Services\Reports\RelatorioGestao\EstruturaRelatorioGestao;
use Illuminate\Support\Facades\Session;

function cenarioGestao(int $anoInicio = 2024, int $anoFim = 2027): array
{
    $org = Organization::create([
        'nom_organizacao' => 'Órgão de Teste da Gestão',
        'sgl_organizacao' => 'OTG',
    ]);

    $pei = PEI::create([
        'dsc_pei' => "PEI $anoInicio-$anoFim",
        'num_ano_inicio_pei' => $anoInicio,
        'num_ano_fim_pei' => $anoFim,
    ]);

    $user = User::factory()->create();
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return [$user, $pei, $org];
}

function objetivoDeTeste(PEI $pei, Organization $org, string $nome): Objetivo
{
    $perspectiva = Perspectiva::create([
        'cod_pei' => $pei->cod_pei,
        'dsc_perspectiva' => 'Resultados para a Sociedade',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    return Objetivo::create([
        'cod_perspectiva' => $perspectiva->cod_perspectiva,
        'nom_objetivo' => $nome,
        'dsc_objetivo' => 'Descrição do objetivo para o Relatório de Gestão.',
        'num_nivel_hierarquico_apresentacao' => 1,
        'num_nivel_desdobramento' => 1,
    ]);
}

// ------------------------------------------------------------------ o arquivo

test('o PDF do Relatório de Gestão é gerado e é um PDF de verdade', function () {
    [$user, $pei, $org] = cenarioGestao();
    objetivoDeTeste($pei, $org, 'Ampliar a entrega de políticas públicas');

    $resposta = $this->actingAs($user)->get(route('relatorios.gestao.pdf', [
        'organizacao_id' => $org->cod_organizacao,
        'ano' => 2026,
        'variante' => EstruturaRelatorioGestao::VARIANTE_REPLICA,
    ]));

    $resposta->assertOk();

    $conteudo = $resposta->streamedContent();

    expect(substr($conteudo, 0, 5))->toBe('%PDF-')
        ->and(strlen($conteudo))->toBeGreaterThan(5000);
});

test('o DOCX do Relatório de Gestão abre como documento do Word', function () {
    [$user, $pei, $org] = cenarioGestao();
    objetivoDeTeste($pei, $org, 'Ampliar a entrega de políticas públicas');

    $resposta = $this->actingAs($user)->get(route('relatorios.gestao.docx', [
        'organizacao_id' => $org->cod_organizacao,
        'ano' => 2026,
        'variante' => EstruturaRelatorioGestao::VARIANTE_REPLICA,
    ]));

    $resposta->assertOk();

    $caminho = tempnam(sys_get_temp_dir(), 'rgtest_').'.docx';
    file_put_contents($caminho, $resposta->streamedContent());

    // Um .docx é um zip com word/document.xml dentro. Se o PHPWord tivesse
    // gerado lixo, o zip nem abriria — e o cliente descobriria no Word.
    $zip = new ZipArchive;

    expect($zip->open($caminho))->toBeTrue();

    $documento = $zip->getFromName('word/document.xml');
    $zip->close();
    @unlink($caminho);

    expect($documento)->toBeString()
        ->and($documento)->toContain('Relatório de Gestão');
});

test('texto com & e < vira texto no DOCX, não XML do Word', function () {
    // Auditoria de segurança (XSS-03): o PhpWord vem com o escape desligado.
    // "P&D" bastava para corromper o arquivo; "</w:t>" injetava WordprocessingML.
    [$user, $pei, $org] = cenarioGestao();
    objetivoDeTeste($pei, $org, 'P&D <x> </w:t></w:r><w:r><w:t>INJETADO');

    $resposta = $this->actingAs($user)->get(route('relatorios.gestao.docx', [
        'organizacao_id' => $org->cod_organizacao,
        'ano' => 2026,
        'variante' => EstruturaRelatorioGestao::VARIANTE_REPLICA,
    ]))->assertOk();

    $caminho = tempnam(sys_get_temp_dir(), 'rgtest_').'.docx';
    file_put_contents($caminho, $resposta->streamedContent());
    $zip = new ZipArchive;
    $zip->open($caminho);
    $documento = (string) $zip->getFromName('word/document.xml');
    $zip->close();
    @unlink($caminho);

    libxml_use_internal_errors(true);
    expect(simplexml_load_string($documento))->not->toBeFalse()
        ->and($documento)->toContain('P&amp;D &lt;x&gt;')
        ->and($documento)->not->toContain('<w:t>INJETADO');
});

// -------------------------------------------------- fidelidade ao modelo

test('as tabelas 2.2.1 e 2.2.2 usam os cabeçalhos do modelo oficial', function () {
    [$user, $pei, $org] = cenarioGestao();
    objetivoDeTeste($pei, $org, 'Ampliar a entrega de políticas públicas');

    $dados = (new EstruturaRelatorioGestao(EstruturaRelatorioGestao::VARIANTE_REPLICA))
        ->montar($org->cod_organizacao, 2026);

    // A asserção é sobre o HTML que vira PDF, não sobre o array: é o Blade que
    // escreve o cabeçalho, e é ele que pode divergir do modelo.
    $html = view('relatorios.gestao.relatorio', ['dados' => $dados])->render();

    // Tabela 2.2.1 (página 28 do modelo)
    expect($html)->toContain('Identificador')
        ->and($html)->toContain('Objetivo Estratégico')
        ->and($html)->toContain('Descrição (resumida)')
        ->and($html)->toContain('Principais Iniciativas');

    // Tabela 2.2.2 (páginas 34-37 do modelo)
    expect($html)->toContain('Resultados, Objetivos Estratégicos e Prioridades da Gestão');

    // 🔴 O documento oficial do cliente diz "Iniciativas". Se a renomeação
    // regredir, o relatório volta a divergir do modelo já na primeira tabela.
    expect($html)->not->toContain('Principais Planos de Ação');
});

test('a variante réplica traz o esqueleto completo do modelo, com a fonte de cada seção externa', function () {
    [, $pei, $org] = cenarioGestao();

    $dados = (new EstruturaRelatorioGestao(EstruturaRelatorioGestao::VARIANTE_REPLICA))
        ->montar($org->cod_organizacao, 2026);

    expect($dados['capitulos'])->toHaveCount(5);

    $numeros = [];
    foreach ($dados['capitulos'] as $capitulo) {
        foreach ($capitulo['secoes'] as $secao) {
            $numeros[] = $secao['numero'];

            // Seção externa SEM fonte é uma seção que ninguém sabe quem preenche.
            if ($secao['externa']) {
                expect($secao['fonte'])->toBeString()
                    ->and(trim($secao['fonte']))->not->toBe('');
            }
        }
    }

    // Numeração conferida contra o sumário do modelo (páginas 3 a 6 do PDF).
    expect($numeros)->toContain('1.1', '1.2', '1.6', '1.8')
        ->and($numeros)->toContain('2.1', '2.2', '2.3')
        ->and($numeros)->toContain('3.1', '3.9')
        ->and($numeros)->toContain('4.1', '4.7')
        ->and($numeros)->toContain('5.1', '5.14');
});

test('a variante autoral não traz nenhuma seção vazia nem marcação de fonte externa', function () {
    [, $pei, $org] = cenarioGestao();
    objetivoDeTeste($pei, $org, 'Ampliar a entrega de políticas públicas');

    $dados = (new EstruturaRelatorioGestao(EstruturaRelatorioGestao::VARIANTE_AUTORAL))
        ->montar($org->cod_organizacao, 2026);

    foreach ($dados['capitulos'] as $capitulo) {
        expect($capitulo['secoes'])->not->toBeEmpty(
            "O capítulo {$capitulo['numero']} ficou sem seção e mesmo assim entrou no documento."
        );

        foreach ($capitulo['secoes'] as $secao) {
            expect($secao['externa'])->toBeFalse(
                "A seção {$secao['numero']} é de fonte externa e não devia aparecer na variante autoral."
            );
        }
    }

    $html = view('relatorios.gestao.relatorio', ['dados' => $dados])->render();

    expect($html)->not->toContain('a preencher pela unidade');
});

// ----------------------------------------------------------------- robustez

test('o ciclo é o selecionado no topo: relatar 2022 exige selecionar o ciclo de 2022', function () {
    // Decisão de 04/10/2026: o ciclo de todo relatório é PEI::doContexto().
    // Antes, o ciclo era buscado pelo ano com first() sem ORDER BY — com dois
    // ciclos sobrepostos, saía um qualquer (RelatoriosCicloEFiltrosTest).
    [, , $org] = cenarioGestao(2024, 2027);

    $antigo = PEI::create([
        'dsc_pei' => 'PEI 2020-2023',
        'num_ano_inicio_pei' => 2020,
        'num_ano_fim_pei' => 2023,
    ]);

    $comAtual = (new EstruturaRelatorioGestao)->montar($org->cod_organizacao, 2026);

    Session::put('pei_selecionado_id', $antigo->cod_pei);
    $comAntigo = (new EstruturaRelatorioGestao)->montar($org->cod_organizacao, 2022);

    expect($comAtual['capa']['ciclo'])->toBe('PEI 2024-2027')
        ->and($comAntigo['capa']['ciclo'])->toBe('PEI 2020-2023');
});

test('o relatório é gerado mesmo com o PEI ainda pela metade', function () {
    [$user, $pei, $org] = cenarioGestao();

    // Objetivo sem indicador, sem iniciativa, sem risco, sem cadeia de valor:
    // o estado real de quem está preenchendo o ciclo.
    objetivoDeTeste($pei, $org, 'Objetivo recém-cadastrado');

    $resposta = $this->actingAs($user)->get(route('relatorios.gestao.pdf', [
        'organizacao_id' => $org->cod_organizacao,
        'ano' => 2026,
        'variante' => EstruturaRelatorioGestao::VARIANTE_AUTORAL,
    ]));

    $resposta->assertOk();
    expect(substr($resposta->streamedContent(), 0, 5))->toBe('%PDF-');
});

test('o relatório é gerado mesmo sem nenhum ciclo cadastrado', function () {
    $org = Organization::create([
        'nom_organizacao' => 'Órgão sem ciclo',
        'sgl_organizacao' => 'OSC',
    ]);

    $user = User::factory()->create();
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    $resposta = $this->actingAs($user)->get(route('relatorios.gestao.pdf', [
        'organizacao_id' => $org->cod_organizacao,
        'ano' => 2026,
        'variante' => EstruturaRelatorioGestao::VARIANTE_REPLICA,
    ]));

    $resposta->assertOk();
    expect(substr($resposta->streamedContent(), 0, 5))->toBe('%PDF-');
});

// ------------------------------------------------------------- autorização

test('quem não tem a capacidade de exportar relatórios não gera o Relatório de Gestão', function () {
    $org = Organization::create([
        'nom_organizacao' => 'Órgão sem exportação',
        'sgl_organizacao' => 'OSE',
    ]);

    // Usuário autenticado, mas SEM perfil algum: nem chega à área restrita —
    // vai para a página de acesso pendente.
    $user = User::factory()->create();

    foreach (['relatorios.gestao.pdf', 'relatorios.gestao.docx'] as $rota) {
        $resposta = $this->actingAs($user)
            ->get(route($rota, ['organizacao_id' => $org->cod_organizacao, 'ano' => 2026]));

        $resposta->assertRedirect(route('acesso.pendente'));

        expect($resposta->getContent())->not->toContain('%PDF-');
    }

    // E nada foi registrado no histórico: quem não pode gerar não gerou.
    expect(RelatorioGerado::where('user_id', $user->id)->count())->toBe(0);
});

test('a geração fica registrada no histórico de relatórios', function () {
    [$user, $pei, $org] = cenarioGestao();
    objetivoDeTeste($pei, $org, 'Objetivo qualquer');

    $this->actingAs($user)
        ->get(route('relatorios.gestao.docx', [
            'organizacao_id' => $org->cod_organizacao,
            'ano' => 2026,
        ]))
        ->streamedContent();

    $registro = RelatorioGerado::where('user_id', $user->id)->latest()->first();

    expect($registro)->not->toBeNull()
        ->and($registro->dsc_tipo_relatorio)->toContain('Relatório de Gestão')
        ->and($registro->dsc_formato)->toBe('docx');
});

// ------------------------------------------------------- sistema de design

test('só o sistema de design do Relatório de Gestão declara @page', function () {
    // O Relatório de Gestão tem grid próprio — margem, cabeçalho e rodapé
    // medidos do modelo oficial — e por isso um segundo @page, em
    // relatorios/gestao/estilos.blade.php. A regra do projeto continua valendo
    // dentro dele: UM lugar declara a página. Seção que declarasse o seu
    // reposicionaria o cabeçalho fixo sobre uma margem que não é a dela.
    $violacoes = [];

    $arquivos = array_merge(
        glob(resource_path('views/relatorios/gestao/*.blade.php')),
        glob(resource_path('views/relatorios/gestao/secoes/*.blade.php'))
    );

    foreach ($arquivos as $arquivo) {
        if (basename($arquivo) === 'estilos.blade.php') {
            continue;
        }

        $semComentarios = preg_replace('/\{\{--.*?--\}\}/s', '', file_get_contents($arquivo));

        if (str_contains($semComentarios, '@page')) {
            $violacoes[] = basename($arquivo);
        }
    }

    expect($violacoes)->toBe([]);
});

test('as margens do Relatório de Gestão são simétricas em cima e embaixo', function () {
    // Pedido explícito do gestor sobre a reconstrução dos relatórios:
    // "margens com simetria entre a linha de cima e a de baixo".
    $css = file_get_contents(resource_path('views/relatorios/gestao/estilos.blade.php'));

    preg_match('/@page\s*\{[^}]*margin:\s*(\d+)px\s+(\d+)px\s+(\d+)px\s+(\d+)px/', $css, $margem);

    expect($margem)->not->toBeEmpty('O @page do Relatório de Gestão não declara as quatro margens.');
    expect($margem[1])->toBe($margem[3], 'Margem superior e inferior divergem.');
    expect($margem[2])->toBe($margem[4], 'Margem esquerda e direita divergem.');
});

test('a régua do cabeçalho e a do rodapé saem da MESMA constante', function () {
    // As duas réguas verdes são desenhadas no canvas, não em CSS. Simetria
    // sustentada por dois números iguais em lugares diferentes dura até alguém
    // mexer num deles — por isso a distância até a borda é uma constante só,
    // usada nas duas pontas. Este teste guarda esse desenho.
    $fonte = file_get_contents(app_path('Services/Reports/AcabamentoPdf.php'));

    expect($fonte)->toMatch('/private const REGUA = [\d.]+;/');

    // Cabeçalho: a régua sai da constante crua. Rodapé: da altura menos ela.
    expect($fonte)->toContain('$canvas->line($esquerda, self::REGUA, $direita, self::REGUA')
        ->and($fonte)->toContain('$y = $this->altura - self::REGUA;');

    // E nenhuma coordenada de régua escrita à mão sobrou no arquivo.
    expect($fonte)->not->toMatch('/->line\([^)]*\b46\.5\b/');
});
// ------------------------------------------- PDF e DOCX não podem divergir

test('seção do modelo sem dado avisa o que falta, em PDF e em DOCX', function () {
    // Ciclo criado e nada preenchido: o estado do cliente no primeiro dia.
    [$user, , $org] = cenarioGestao();

    $mensagens = [
        'Missão, visão, valores e objetivos ainda não foram cadastrados neste ciclo.',
        'Nenhum objetivo estratégico foi cadastrado neste ciclo, e por isso não há resultados a apurar.',
        'A cadeia de valor ainda não foi cadastrada neste ciclo do Planejamento Estratégico Integrado.',
        'Nenhuma análise SWOT ou PESTEL foi registrada neste ciclo.',
        'Nenhum risco foi registrado para este ciclo no módulo de Gestão de Riscos.',
    ];

    // --- PDF (o HTML que o DomPDF converte) ---
    $dados = (new EstruturaRelatorioGestao(EstruturaRelatorioGestao::VARIANTE_REPLICA))
        ->montar($org->cod_organizacao, 2026);

    $html = view('relatorios.gestao.relatorio', ['dados' => $dados])->render();

    foreach ($mensagens as $mensagem) {
        expect($html)->toContain($mensagem);
    }

    // --- DOCX (o mesmo array, outro renderizador) ---
    $resposta = $this->actingAs($user)->get(route('relatorios.gestao.docx', [
        'organizacao_id' => $org->cod_organizacao,
        'ano' => 2026,
        'variante' => EstruturaRelatorioGestao::VARIANTE_REPLICA,
    ]));

    $caminho = tempnam(sys_get_temp_dir(), 'rgtest_').'.docx';
    file_put_contents($caminho, $resposta->streamedContent());

    $zip = new ZipArchive;
    $zip->open($caminho);
    $documento = $zip->getFromName('word/document.xml');
    $zip->close();
    @unlink($caminho);

    foreach ($mensagens as $mensagem) {
        // O XML do Word escapa & e < como o HTML; as mensagens não os usam.
        expect($documento)->toContain($mensagem);
    }
});

test('PDF e DOCX trazem exatamente os mesmos títulos de seção', function () {
    // A garantia real de que os dois formatos não divergem é esta: os dois
    // consomem a MESMA estrutura, e todo título dela chega aos dois arquivos.
    [$user, $pei, $org] = cenarioGestao();
    objetivoDeTeste($pei, $org, 'Objetivo do ciclo');

    $dados = (new EstruturaRelatorioGestao(EstruturaRelatorioGestao::VARIANTE_REPLICA))
        ->montar($org->cod_organizacao, 2026);

    $html = view('relatorios.gestao.relatorio', ['dados' => $dados])->render();

    $resposta = $this->actingAs($user)->get(route('relatorios.gestao.docx', [
        'organizacao_id' => $org->cod_organizacao,
        'ano' => 2026,
        'variante' => EstruturaRelatorioGestao::VARIANTE_REPLICA,
    ]));

    $caminho = tempnam(sys_get_temp_dir(), 'rgtest_').'.docx';
    file_put_contents($caminho, $resposta->streamedContent());

    $zip = new ZipArchive;
    $zip->open($caminho);
    $docx = $zip->getFromName('word/document.xml');
    $zip->close();
    @unlink($caminho);

    $faltando = ['pdf' => [], 'docx' => []];

    foreach ($dados['capitulos'] as $capitulo) {
        foreach ($capitulo['secoes'] as $secao) {
            $titulo = $secao['numero'].' '.$secao['titulo'];

            if (! str_contains($html, $titulo)) {
                $faltando['pdf'][] = $titulo;
            }

            if (! str_contains($docx, $titulo)) {
                $faltando['docx'][] = $titulo;
            }
        }
    }

    expect($faltando['pdf'])->toBe([])
        ->and($faltando['docx'])->toBe([]);
});

// ------------------------------------------- fidelidade de LAYOUT ao modelo

test('o Relatório de Gestão sai em A4 PAISAGEM, como o modelo', function () {
    // A primeira versão saiu em retrato. O modelo oficial tem 198 páginas e
    // todas são paisagem — a orientação é a primeira coisa que se vê ao
    // comparar os dois documentos lado a lado.
    $css = file_get_contents(resource_path('views/relatorios/gestao/estilos.blade.php'));

    expect($css)->toMatch('/@page\s*\{[^}]*size:\s*a4\s+landscape/');

    [$user, $pei, $org] = cenarioGestao();
    objetivoDeTeste($pei, $org, 'Objetivo do ciclo');

    $pdf = $this->actingAs($user)->get(route('relatorios.gestao.pdf', [
        'organizacao_id' => $org->cod_organizacao,
        'ano' => 2026,
    ]))->streamedContent();

    // A caixa de mídia do PDF: A4 paisagem é 841,89 x 595,28 pontos. Assertar
    // sobre o CSS não bastaria — é o byte gerado que o cliente abre.
    //
    // A caixa é EXTRAÍDA antes de comparar: jogar 1,2 MB de PDF dentro de uma
    // asserção derruba o formatador de falhas do Pest por falta de memória, e
    // o teste morre sem dizer o que estava errado.
    preg_match('/MediaBox\s*\[\s*([\d.]+)\s+([\d.]+)\s+([\d.]+)\s+([\d.]+)\s*\]/', $pdf, $caixa);

    expect($caixa)->not->toBeEmpty('O PDF não declara MediaBox.');

    $largura = round((float) $caixa[3]);
    $altura = round((float) $caixa[4]);

    expect([$largura, $altura])->toBe([842.0, 595.0]);
});

test('a capa não repete o cabeçalho nem o rodapé das páginas internas', function () {
    // O DomPDF desenha todo elemento `position: fixed` na página 1 também. No
    // modelo a capa é uma folha limpa, de foto sangrada. AcabamentoPdf pinta a
    // página inteira por cima — se essa pintura sumir, a faixa verde volta.
    [$user, $pei, $org] = cenarioGestao();
    objetivoDeTeste($pei, $org, 'Objetivo do ciclo');

    $caminho = tempnam(sys_get_temp_dir(), 'rgcapa_').'.pdf';
    file_put_contents($caminho, $this->actingAs($user)->get(route('relatorios.gestao.pdf', [
        'organizacao_id' => $org->cod_organizacao,
        'ano' => 2026,
    ]))->streamedContent());

    $capa = paginaEmTexto($caminho, 1);

    @unlink($caminho);

    expect($capa)->not->toContain('PÁGINA 1')
        ->and($capa)->toContain('Relatório de Gestão')
        ->and($capa)->toContain('2026');
})->skip(fn () => ! temPdfToText(), 'pdftotext não está disponível nesta máquina.');

test('cada página interna nomeia o capítulo a que pertence, sem invadir o título central', function () {
    // Regressão dupla, as duas encontradas no PDF gerado e não no código:
    //
    //  1. O cabeçalho vivo saía UM CAPÍTULO ADIANTADO, porque `@page :first`
    //     zerava a margem na medição de páginas e mudava a paginação.
    //  2. O título do capítulo 4 tem 74 caracteres e era impresso POR CIMA do
    //     "Relatório de Gestão AAAA" do centro — letra sobre letra.
    [$user, $pei, $org] = cenarioGestao();
    objetivoDeTeste($pei, $org, 'Objetivo do ciclo');

    $caminho = tempnam(sys_get_temp_dir(), 'rgcab_').'.pdf';
    file_put_contents($caminho, $this->actingAs($user)->get(route('relatorios.gestao.pdf', [
        'organizacao_id' => $org->cod_organizacao,
        'ano' => 2026,
        'variante' => EstruturaRelatorioGestao::VARIANTE_REPLICA,
    ]))->streamedContent());

    $problemas = [];

    // Da página 3 em diante é conteúdo de capítulo (1 = capa, 2+ = sumário).
    for ($pagina = 3; $pagina <= 14; $pagina++) {
        $texto = paginaEmTexto($caminho, $pagina);

        if (trim($texto) === '') {
            break;
        }

        // O título do capítulo aparece no corpo só na sua primeira página; o
        // cabeçalho o repete em todas. Se houver "N. Título" no corpo, o
        // cabeçalho tem de anunciar o MESMO capítulo.
        if (preg_match('/^(\d)\.\s+\S/mu', $texto, $corpo)) {
            $numero = str_pad($corpo[1], 2, '0', STR_PAD_LEFT);

            if (! str_contains($texto, 'Capítulo '.$numero)) {
                $problemas[] = "página $pagina: corpo do capítulo {$corpo[1]}, cabeçalho não diz \"Capítulo $numero\"";
            }
        }

        // Sobreposição deixa rastro no texto extraído: o título central e o do
        // capítulo saem interpolados, e "Relatório de Gestão AAAA" deixa de
        // existir inteiro na linha.
        if (str_contains($texto, 'Capítulo ') && ! str_contains($texto, 'Relatório de Gestão 2026')) {
            $problemas[] = "página $pagina: o título central do cabeçalho saiu quebrado — sinal de texto sobreposto";
        }
    }

    @unlink($caminho);

    expect($problemas)->toBe([]);
})->skip(fn () => ! temPdfToText(), 'pdftotext não está disponível nesta máquina.');

test('o relatório não carrega marca nem endereço de outro órgão', function () {
    // Este é um produto MULTICLIENTE. O modelo é da Presidência da República;
    // o layout se copia, a identidade não. Uma menção à PR, ao Palácio do
    // Planalto ou ao gov.br/planalto no relatório de outro órgão é
    // falsificação institucional, não "inspiração no modelo".
    $proibidos = ['Presidência da República', 'planalto', 'Palácio do Planalto', 'gov.br/planalto'];

    $arquivos = array_merge(
        glob(resource_path('views/relatorios/gestao/*.blade.php')),
        glob(resource_path('views/relatorios/gestao/secoes/*.blade.php')),
        glob(app_path('Services/Reports/RelatorioGestao/*.php'))
    );

    $violacoes = [];

    foreach ($arquivos as $arquivo) {
        $conteudo = preg_replace('/\{\{--.*?--\}\}/s', '', file_get_contents($arquivo));
        $conteudo = preg_replace('#/\*.*?\*/#s', '', $conteudo);

        foreach ($proibidos as $termo) {
            if (stripos($conteudo, $termo) !== false) {
                $violacoes[] = basename($arquivo).' → '.$termo;
            }
        }
    }

    expect($violacoes)->toBe([]);
});

function temPdfToText(): bool
{
    return caminhoPdfToText() !== null;
}

/**
 * Onde está o pdftotext.
 *
 * O binário vem do poppler e mora no PATH do Git Bash — que NÃO é o PATH que
 * o PHP herda no Windows. Procurar nos lugares conhecidos evita pular um teste
 * que esta máquina consegue rodar.
 */
function caminhoPdfToText(): ?string
{
    static $caminho = false;

    if ($caminho !== false) {
        return $caminho;
    }

    $candidatos = ['pdftotext', 'C:/Program Files/Git/mingw64/bin/pdftotext.exe'];

    foreach ($candidatos as $candidato) {
        $saida = [];
        exec(escapeshellarg($candidato).' -v 2>&1', $saida);

        if (str_contains(strtolower(implode(' ', $saida)), 'pdftotext')) {
            return $caminho = $candidato;
        }
    }

    return $caminho = null;
}

/** O texto de UMA página do PDF, como o leitor a vê. */
function paginaEmTexto(string $caminho, int $pagina): string
{
    exec(
        escapeshellarg(caminhoPdfToText())
        .' -layout -enc UTF-8 -f '.$pagina.' -l '.$pagina.' '.escapeshellarg($caminho).' - 2>&1',
        $linhas
    );

    return implode("\n", $linhas);
}
