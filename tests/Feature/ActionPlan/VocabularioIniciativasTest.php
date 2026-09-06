<?php

/*
 * Trava a renomeação do RÓTULO "Plano de Ação" → "Iniciativas".
 *
 * A demanda não é preferência de vocabulário: o Relatório de Gestão 2025 da
 * Presidência da República — o modelo que o cliente já usa — traz "Iniciativas"
 * no cabeçalho das tabelas 2.2.1 (p. 28) e 2.2.2 (p. 34-37). Sem isto, o
 * relatório que o sistema gera diverge do modelo já na primeira tabela.
 *
 * Este teste guarda as DUAS pontas:
 *   1. o rótulo visível não regride para o termo antigo;
 *   2. os IDENTIFICADORES DE SISTEMA (rota, tabela, coluna, classe, módulo da
 *      MATRIZ) continuam com o nome antigo — renomeá-los quebraria URL, query,
 *      Policy e o histórico de auditoria.
 */

use App\Models\ActionPlan\PlanoDeAcao;
use App\Services\Authorization\CapacidadeResolver;
use Illuminate\Support\Facades\Route;

function arquivosDeInterface(): array
{
    $encontrados = [];

    foreach ([base_path('resources/views'), base_path('app'), base_path('routes')] as $raiz) {
        $iterador = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($raiz));

        foreach ($iterador as $arquivo) {
            if ($arquivo->isDir() || ! in_array($arquivo->getExtension(), ['php'], true)) {
                continue;
            }

            $encontrados[] = $arquivo->getPathname();
        }
    }

    return $encontrados;
}

/**
 * O texto que o navegador de fato recebe.
 *
 * 🔴 POR QUE ISTO EXISTE
 * Estes testes se chamam "nenhum RÓTULO VISÍVEL diz X". Comentário não é
 * rótulo visível: o Blade descarta {{-- --}} na compilação e o PHP descarta
 * // e /* *​/ no parser. Nenhum dos dois chega ao cliente.
 *
 * Sem esta limpeza, o guarda proibia explicar a própria regra: um comentário
 * dizendo "por que a renomeação aconteceu" era acusado de ser o rótulo antigo.
 * Guarda que impede documentar a decisão vira ruído, e ruído se desliga.
 *
 * A limpeza ENDURECE o guarda, não o afrouxa: o que sobra é só o que o
 * usuário lê.
 */
function textoVisivel(string $caminho): string
{
    $conteudo = file_get_contents($caminho);

    // Comentário Blade: some na compilação da view.
    $conteudo = preg_replace('/\{\{--.*?--\}\}/s', '', $conteudo);

    // Comentário PHP: some no parser. token_get_all lida com arquivo misto
    // (HTML + PHP), que é exatamente o caso de um .blade.php.
    $limpo = '';
    foreach (@token_get_all($conteudo) as $token) {
        if (is_array($token) && in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
            // Preserva as quebras de linha para a numeração continuar honesta.
            $limpo .= str_repeat("\n", substr_count($token[1], "\n"));

            continue;
        }

        $limpo .= is_array($token) ? $token[1] : $token;
    }

    return $limpo;
}
test('nenhum rótulo visível diz "Plano de Ação"', function () {
    $padrao = '/plano[s]?\s+de\s+a[çc][ãa]o/iu';

    $ocorrencias = [];

    foreach (arquivosDeInterface() as $caminho) {
        if (preg_match($padrao, textoVisivel($caminho))) {
            $ocorrencias[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $caminho);
        }
    }

    expect($ocorrencias)->toBe([]);
});

test('nenhum erro de concordância de gênero após a troca', function () {
    // "Plano" era masculino; "Iniciativa" é feminino. Substituição cega produz
    // "O iniciativa foi excluído" — foi o que aconteceu na primeira passada.
    $determinanteMasculino = '/\b(?:o|do|ao|no|um|novo|este|esse|pelo|seu|os|dos|aos|nos|todos)\s+iniciativas?\b/iu';
    $participioMasculino = '/\biniciativas?\s+\p{L}*(?:ado|ados|ido|idos)\b/iu';

    $erros = [];

    foreach (arquivosDeInterface() as $caminho) {
        $conteudo = file_get_contents($caminho);

        foreach ([$determinanteMasculino, $participioMasculino] as $padrao) {
            if (preg_match_all($padrao, $conteudo, $m)) {
                foreach ($m[0] as $trecho) {
                    // "o que são Iniciativas" é correto: "o" pertence a "o que".
                    if (preg_match('/\bo\s+que\b/iu', $trecho)) {
                        continue;
                    }

                    $erros[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $caminho).': '.$trecho;
                }
            }
        }
    }

    expect($erros)->toBe([]);
});

test('as rotas do módulo continuam com o nome antigo', function () {
    // URL em uso pelo cliente. Renomear rota quebra link salvo e favorito.
    foreach (['planos.index', 'planos.detalhes', 'planos.entregas', 'planos.responsaveis'] as $nome) {
        expect(Route::has($nome))->toBeTrue("rota [{$nome}] sumiu");
    }
});

test('a tabela e a coluna de chave estrangeira continuam com o nome antigo', function () {
    // Renomear tabela é DDL de risco em N instalações de cliente, e quebraria
    // toda FK — indicadores, entregas, RACI e riscos apontam para ela.
    // O schema foi qualificado (correto: o search_path começa em "pei" e uma
    // query sem schema resolve por sorte). O que NÃO pode mudar é o nome da
    // tabela em si nem o da coluna de chave.
    expect((new PlanoDeAcao)->getTable())->toBe('action_plan.tab_plano_de_acao')
        ->and((new PlanoDeAcao)->getKeyName())->toBe('cod_plano_de_acao');
});

test('o módulo na MATRIZ de capacidades continua com o nome antigo', function () {
    // Renomear a chave não gera erro: gera acesso negado SILENCIOSO para todo
    // perfil que não seja Super Admin.
    $matriz = (new ReflectionClass(CapacidadeResolver::class))->getConstant('MATRIZ');

    expect($matriz)->toHaveKey('planos-de-acao');
});

test('o menu principal exibe "Iniciativas"', function () {
    $menu = file_get_contents(base_path('resources/views/layouts/app.blade.php'));

    expect($menu)->toContain("'label' => 'Iniciativas'")
        ->and($menu)->toContain("'route' => 'planos.index'");
});

test('nenhum rótulo visível diz "Plano" sozinho para o módulo', function () {
    // A primeira passada trocou só "Plano de Ação" e ficou pela metade: sobrou
    // "Novo Plano", "Legenda Status (Planos)", "Descrição do Plano" na tela.
    //
    // "Plano" nomeia CINCO conceitos aqui, e quatro NÃO são o módulo — por isso
    // a asserção lista o que é legítimo em vez de proibir a palavra.
    $conceitosLegitimos = [
        'Plano Estratégico Institucional',
        'Planos Estratégicos Institucionais',
        'Plano de Comunicação',
        'Plano de Mitigação',
        'Planos de Mitigação',
        'Plano Setorial',
        'Planos Setoriais',
        'Plano Plurianual',
        'Tipo de Plano',      // tipo do plano de MITIGAÇÃO
        'Salvar Plano',       // botão do plano de MITIGAÇÃO
        'Novo Plano',         // novo plano de MITIGAÇÃO
        'plano de mitigação',
        'Lista de Planos',   // em gerenciar-mitigacoes: planos de MITIGAÇÃO
    ];

    $violacoes = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views'))) as $arquivo) {
        if ($arquivo->isDir() || ! str_ends_with($arquivo->getFilename(), '.blade.php')) {
            continue;
        }

        $linhas = explode('
', textoVisivel($arquivo->getPathname()));

        foreach ($linhas as $n => $linha) {
            if (! preg_match('/\bPlanos?\b/u', $linha)) {
                continue;
            }

            // Remove os conceitos legítimos e os identificadores de sistema.
            $resto = $linha;
            foreach ($conceitosLegitimos as $c) {
                $resto = str_ireplace($c, '', $resto);
            }
            foreach (['planos.index', 'planos.detalhes', 'planos.entregas', 'planos.responsaveis',
                'relatorios.planos', 'plano-acao', 'planosAcao', 'planoDeAcao', 'PlanoDeAcao',
                'cod_plano', 'dsc_plano', 'num_plano', 'planos_acao', 'planos_count', 'totalPlanos',
                'filtroPlano', 'planoId', 'listar-planos', 'detalhar-plano', 'plano_de_acao',
                'planosConcluidos', 'planosAtrasados', 'chartPlanos', 'legenda-status-planos',
                // value="Plano" é gravado em dsc_tipo e comparado no código:
                // é IDENTIFICADOR, não rótulo. O texto ao lado já diz "Iniciativa".
                'value="Plano"'] as $id) {
                $resto = str_replace($id, '', $resto);
            }

            if (preg_match('/\bPlanos?\b/u', $resto)) {
                $violacoes[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $arquivo->getPathname())
                    .':'.($n + 1).' → '.trim($linha);
            }
        }
    }

    expect($violacoes)->toBe([]);
});
