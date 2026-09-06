<?php

/*
 * Duas correções, dois grupos de teste.
 *
 * 1) ACESSO — o PeiGuidanceService manda o cliente configurar as faixas na
 *    fase 5 do ciclo e oferece o botão "Configurar Níveis". A tela devolvia
 *    403 para todo perfil que não fosse Super Admin: o sistema mandava ir a
 *    uma tela que ele mesmo proibia. O teste de coerência abaixo trava a
 *    CLASSE do defeito, não só este caso.
 *
 * 2) FAROL — 13 de 17 consultas às faixas ignoravam o ciclo. Com dois PEIs de
 *    réguas diferentes, o mesmo percentual acendia a cor errada, sem erro e
 *    sem aviso. A asserção é sobre a COR RESULTANTE, não sobre a query.
 */

use App\Livewire\StrategicPlanning\ListarGrausSatisfacao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use App\Services\PeiGuidanceService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

function orgDeGraus(): Organization
{
    return Organization::firstOrCreate(
        ['sgl_organizacao' => 'ORGGS'],
        ['nom_organizacao' => 'Org Graus', 'cod_organizacao_pai' => null]
    );
}

function usuarioDePerfil(string $perfil): User
{
    $org = orgDeGraus();
    $user = User::factory()->create();
    $user->perfisAcesso()->attach($perfil, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    return $user;
}

function ciclo(string $nome): PEI
{
    return PEI::create([
        'dsc_pei' => $nome,
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);
}

function faixa(PEI $pei, string $rotulo, float $min, float $max, string $cor): GrauSatisfacao
{
    return GrauSatisfacao::create([
        'cod_pei' => $pei->cod_pei,
        'dsc_grau_satisfacao' => $rotulo,
        'cor' => $cor,
        'vlr_minimo' => $min,
        'vlr_maximo' => $max,
    ]);
}

// ---------------------------------------------------------------- ACESSO

test('Admin de Unidade abre a tela de Graus de Satisfação', function () {
    Livewire::actingAs(usuarioDePerfil(PerfilAcesso::ADMIN_UNIDADE))
        ->test(ListarGrausSatisfacao::class)
        ->assertOk();
});

test('Gestor Responsável VÊ a régua, mas NÃO a altera', function () {
    $gestor = usuarioDePerfil(PerfilAcesso::GESTOR_RESPONSAVEL);

    Livewire::actingAs($gestor)->test(ListarGrausSatisfacao::class)->assertOk();

    // Alterar a faixa depois do resultado lançado é reescrever a nota depois
    // da prova. E método público de Livewire é chamável direto do navegador:
    // não basta esconder o botão.
    Livewire::actingAs($gestor)
        ->test(ListarGrausSatisfacao::class)
        ->call('edit', 'qualquer-id')
        ->assertForbidden();
});

test('Gestor Substituto não exclui faixa', function () {
    Livewire::actingAs(usuarioDePerfil(PerfilAcesso::GESTOR_SUBSTITUTO))
        ->test(ListarGrausSatisfacao::class)
        ->call('confirmDelete', 'qualquer-id')
        ->assertForbidden();
});

test('COERÊNCIA: a tela para onde o guia manda o cliente ABRE de verdade', function () {
    // O defeito original: o guia oferecia o botão "Configurar Níveis" e a tela
    // devolvia 403. A asserção certa não é sobre a matriz de capacidades — é
    // sobre a REQUISIÇÃO. É o caminho que a tela usa, e é o que trava a classe
    // inteira do defeito para o próximo módulo restrito que entrar no fluxo.
    $pei = ciclo('Ciclo da coerência');
    Session::put('pei_selecionado_id', $pei->cod_pei);

    $incoerencias = [];

    foreach ([
        PerfilAcesso::ADMIN_UNIDADE,
        PerfilAcesso::GESTOR_RESPONSAVEL,
        PerfilAcesso::GESTOR_SUBSTITUTO,
    ] as $perfil) {
        $user = usuarioDePerfil($perfil);

        $guia = app(PeiGuidanceService::class)->analyzeCompleteness($pei->cod_pei);
        $rota = $guia['action_route'] ?? null;

        if (! $rota || ! Route::has($rota)) {
            continue;
        }

        $status = $this->actingAs($user)->get(route($rota))->getStatusCode();

        if ($status === 403) {
            $incoerencias[] = "o guia envia o perfil {$perfil} para [{$rota}], que devolve 403";
        }
    }

    expect($incoerencias)->toBe([]);
});

// ----------------------------------------------------------------- FAROL

test('a régua de um ciclo NÃO é usada por outro', function () {
    $cicloA = ciclo('Ciclo A — régua frouxa');
    $cicloB = ciclo('Ciclo B — régua apertada');

    // 80% é "Excelente" no ciclo A e apenas "Atenção" no ciclo B.
    faixa($cicloA, 'Excelente', 70, 100, '#28a745');
    faixa($cicloB, 'Atenção', 70, 100, '#ffc107');

    expect(GrauSatisfacao::corDe(80, $cicloA->cod_pei))->toBe('#28a745')
        ->and(GrauSatisfacao::corDe(80, $cicloB->cod_pei))->toBe('#ffc107');
});

test('o farol do indicador usa a régua do ciclo do próprio indicador', function () {
    $cicloA = ciclo('A');
    $cicloB = ciclo('B');

    faixa($cicloA, 'Excelente', 0, 100, '#28a745');
    faixa($cicloB, 'Crítico', 0, 100, '#dc3545');

    $perspectivaA = Perspectiva::create([
        'cod_pei' => $cicloA->cod_pei,
        'dsc_perspectiva' => 'Resultados',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    $objetivoA = Objetivo::create([
        'cod_perspectiva' => $perspectivaA->cod_perspectiva,
        'nom_objetivo' => 'Objetivo do ciclo A',
        'dsc_objetivo' => 'Objetivo usado para conferir a régua do farol.',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    // A cor tem de vir do ciclo A, ainda que o ciclo B também tenha faixa
    // cobrindo o mesmo intervalo.
    expect($objetivoA->getCorFarolConsolidado((int) date('Y')))->toBe('#28a745');
});

test('sem régua configurada o farol não inventa cor', function () {
    $pei = ciclo('Ciclo sem faixas');

    expect(GrauSatisfacao::faixaDe(80, $pei->cod_pei))->toBeNull()
        ->and(GrauSatisfacao::corDe(80, $pei->cod_pei))->toBe('#6b7280');
});

test('faixa específica do ano tem precedência sobre a geral do ciclo', function () {
    $pei = ciclo('Ciclo com régua por ano');
    $ano = (int) date('Y');

    faixa($pei, 'Geral do ciclo', 0, 100, '#28a745');

    GrauSatisfacao::create([
        'cod_pei' => $pei->cod_pei,
        'num_ano' => $ano,
        'dsc_grau_satisfacao' => 'Régua do ano',
        'cor' => '#ffc107',
        'vlr_minimo' => 0,
        'vlr_maximo' => 100,
    ]);

    expect(GrauSatisfacao::corDe(80, $pei->cod_pei, $ano))->toBe('#ffc107');
});

test('nenhuma consulta às faixas ignora o ciclo', function () {
    // Varredura estática: qualquer GrauSatisfacao:: fora do CRUD da própria
    // tela precisa receber o ciclo.
    //
    // 🔴 A lista de métodos aceitos é DERIVADA DA ASSINATURA, não escrita à
    // mão: vale o método estático que declara um parâmetro $codPei. Lista fixa
    // se amplia para calar o aviso; esta não — um método cego ao ciclo não
    // entra nela de jeito nenhum.
    $reflexao = new ReflectionClass(GrauSatisfacao::class);

    $cientesDoCiclo = collect($reflexao->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC))
        ->filter(fn ($m) => $m->getDeclaringClass()->getName() === $reflexao->getName())
        ->filter(fn ($m) => collect($m->getParameters())->contains(fn ($par) => $par->getName() === 'codPei'))
        ->map(fn ($m) => $m->getName())
        ->all();

    expect($cientesDoCiclo)->toContain('faixaDe', 'corDe');

    // scopeDoPei vira ::doPei na chamada; o resto é Eloquent puro.
    $permitidos = array_merge($cientesDoCiclo, ['doPei', 'find', 'create', 'query', 'with', 'withTrashed', 'count']);

    $suspeitos = [];

    foreach ([base_path('app'), base_path('resources/views')] as $raiz) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($raiz)) as $arquivo) {
            if ($arquivo->isDir() || $arquivo->getExtension() !== 'php') {
                continue;
            }

            $caminho = $arquivo->getPathname();

            // O próprio Model define os escopos; a tela de CRUD administra as
            // faixas de um PEI escolhido no formulário.
            if (str_contains($caminho, 'GrauSatisfacao.php')
                || str_contains($caminho, 'ListarGrausSatisfacao.php')
                || str_contains($caminho, 'DetalharGrauSatisfacao.php')) {
                continue;
            }

            preg_match_all('/GrauSatisfacao::(\w+)/', file_get_contents($caminho), $m);

            foreach ($m[1] as $metodo) {
                if (! in_array($metodo, $permitidos, true)) {
                    $suspeitos[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $caminho).": ::{$metodo}";
                }
            }
        }
    }

    expect($suspeitos)->toBe([]);
});

// ─────────────────────────── o farol não pode mentir ───────────────────────

test('acima da melhor faixa NÃO é crítico', function () {
    // 🔴 Achado na tela: um indicador a 125% da meta caía fora de todas as
    // faixas (a última termina em 100) e era pintado de VERMELHO. Superar a
    // meta virava alarme, no Mapa Estratégico e no Portal da Transparência.
    $pei = PEI::create([
        'dsc_pei' => 'Ciclo do farol',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    GrauSatisfacao::create([
        'cod_pei' => $pei->cod_pei, 'dsc_grau_satisfacao' => 'Crítico',
        'cor' => '#dc3545', 'vlr_minimo' => 0, 'vlr_maximo' => 49.99,
    ]);
    GrauSatisfacao::create([
        'cod_pei' => $pei->cod_pei, 'dsc_grau_satisfacao' => 'No alvo',
        'cor' => '#198754', 'vlr_minimo' => 50, 'vlr_maximo' => 100,
    ]);

    expect(GrauSatisfacao::corDe(125.0, $pei->cod_pei))->toBe('#198754')
        ->and(GrauSatisfacao::rotuloDe(125.0, $pei->cod_pei))->toBe('No alvo');

    // E abaixo da primeira faixa continua sendo o pior grau, não o melhor.
    expect(GrauSatisfacao::corDe(-10.0, $pei->cod_pei))->toBe('#dc3545');
});

test('sem régua configurada o farol sai NEUTRO, nunca crítico', function () {
    // 🔴 Achado na tela: organização que ainda não configurou os graus recebia
    // '#dc3545' em tudo. O mapa inteiro em vermelho é um juízo que ninguém
    // emitiu — e é o pior momento para emiti-lo, porque é o cliente novo que
    // ainda está preenchendo o ciclo.
    $pei = PEI::create([
        'dsc_pei' => 'Ciclo sem régua',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    expect(GrauSatisfacao::temRegua($pei->cod_pei))->toBeFalse()
        ->and(GrauSatisfacao::corDe(0.0, $pei->cod_pei))->toBe(GrauSatisfacao::COR_SEM_REGUA)
        ->and(GrauSatisfacao::corDe(80.0, $pei->cod_pei))->toBe(GrauSatisfacao::COR_SEM_REGUA)
        ->and(GrauSatisfacao::rotuloDe(80.0, $pei->cod_pei))->toBeNull();
});

test('nenhuma tela decide o farol por conta própria', function () {
    // 🔴 DetalharObjetivo tinha os cortes escritos no código — 100/70/40 —
    // enquanto o Mapa usava a régua da organização. O MESMO objetivo acendia
    // de cores diferentes em duas telas da mesma plataforma.
    //
    // Esta varredura procura o padrão que produz isso: cor de farol escolhida
    // por comparação numérica solta, em vez de vir do Model.
    $suspeitos = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path('app/Livewire'))) as $arquivo) {
        if ($arquivo->isDir() || $arquivo->getExtension() !== 'php') {
            continue;
        }

        $conteudo = preg_replace('#/\*.*?\*/|//[^\n]*#s', '', file_get_contents($arquivo->getPathname()));

        // "if ($x >= 70) return '#...';" e variações.
        if (preg_match('/(>=|>|<=|<)\s*\d+(\.\d+)?\s*\)?\s*(return|\?)\s*[\'"]#[0-9a-fA-F]{3,6}[\'"]/', $conteudo)) {
            $suspeitos[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $arquivo->getPathname());
        }
    }

    expect($suspeitos)->toBe([]);
});
