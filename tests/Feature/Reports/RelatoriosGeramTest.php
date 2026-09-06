<?php

/*
 * Teste de fumaça dos relatórios em PDF.
 *
 * Reconstrução de UI é onde mais se perde conteúdo sem perceber: o Blade
 * compila, a rota responde, e o PDF sai vazio ou explode na página 12. Estes
 * testes GERAM o PDF de verdade e conferem o byte de saída.
 *
 * Cobre também o pedido do gestor: seção sem dado não aparece no relatório.
 */

use App\Livewire\StrategicPlanning\CadeiaDeValor;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\AtividadeCadeiaValor;
use App\Models\StrategicPlanning\PEI;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

function cenarioRelatorio(): array
{
    $org = Organization::create([
        'nom_organizacao' => 'Org Relatório',
        'sgl_organizacao' => 'ORGREL2',
        'cod_organizacao_pai' => null,
    ]);

    $pei = PEI::create([
        'dsc_pei' => 'Ciclo de Relatório',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    $user = User::factory()->create();
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    return [$user, $pei, $org];
}

test('o PDF da Cadeia de Valor é gerado e é um PDF de verdade', function () {
    [$user, $pei] = cenarioRelatorio();

    AtividadeCadeiaValor::create([
        'cod_pei' => $pei->cod_pei,
        'dsc_atividade' => 'Formular políticas públicas',
        'dsc_tipo' => 'Finalística',
        'num_ordem' => 1,
    ]);

    $resposta = Livewire::actingAs($user)
        ->test(CadeiaDeValor::class)
        ->call('gerarPdf');

    // Não basta "não deu erro": o conteúdo tem de começar com a assinatura de
    // um PDF. Blade quebrado gera HTML de erro com status 200.
    $conteudo = $resposta->effects['download']['content'] ?? null;

    if ($conteudo === null) {
        // streamDownload: captura pelo buffer da resposta.
        ob_start();
        $resposta->response->sendContent();
        $conteudo = ob_get_clean();
    } else {
        $conteudo = base64_decode($conteudo);
    }

    expect($conteudo)->toStartWith('%PDF')
        ->and(strlen($conteudo))->toBeGreaterThan(1000);
});

test('SEÇÃO VAZIA não aparece no relatório da Cadeia de Valor', function () {
    // O pedido literal do gestor: "se o relatório vai mostrar Temas Norteadores
    // mas o cliente ainda não preencheu, não é necessário mostrar". Um relatório
    // que anuncia o que falta constrange quem o apresenta.
    [$user, $pei] = cenarioRelatorio();

    AtividadeCadeiaValor::create([
        'cod_pei' => $pei->cod_pei,
        'dsc_atividade' => 'Só uma atividade finalística',
        'dsc_tipo' => 'Finalística',
        'num_ordem' => 1,
    ]);

    $html = view('relatorios.cadeia-valor', [
        'pei' => $pei,
        'grupos' => [
            ['tipo' => 'Finalística', 'titulo' => 'Atividades Finalísticas', 'ajuda' => '', 'cor' => 'primary', 'icone' => 'bi-x',
                'itens' => AtividadeCadeiaValor::where('cod_pei', $pei->cod_pei)->with('processos', 'perspectiva')->get()],
            ['tipo' => 'Suporte', 'titulo' => 'Atividades de Suporte', 'ajuda' => '', 'cor' => 'secondary', 'icone' => 'bi-x',
                'itens' => collect()],
            ['tipo' => 'Valores públicos', 'titulo' => 'Valores Públicos', 'ajuda' => '', 'cor' => 'success', 'icone' => 'bi-x',
                'itens' => collect()],
        ],
        'data' => now()->format('d/m/Y'),
        'orientacao' => 'landscape',
    ])->render();

    expect($html)->toContain('Atividades Finalísticas')
        ->and($html)->not->toContain('Atividades de Suporte')
        ->and($html)->not->toContain('Valores Públicos');
});

test('nenhum relatório declara @page próprio', function () {
    // O @page vive só no sistema de design. Antes, quatro relatórios traziam o
    // seu, um deles INCLUINDO o sistema e sobrescrevendo-o logo depois: o
    // cabeçalho fixo era posicionado para uma margem e desenhado sobre outra.
    $violacoes = [];

    foreach (glob(resource_path('views/relatorios/*.blade.php')) as $arquivo) {
        $conteudo = file_get_contents($arquivo);

        // Ignora menção em comentário Blade.
        $semComentarios = preg_replace('/\{\{--.*?--\}\}/s', '', $conteudo);

        if (str_contains($semComentarios, '@page')) {
            $violacoes[] = basename($arquivo);
        }
    }

    expect($violacoes)->toBe([]);
});

test('as margens do relatório são simétricas em cima e embaixo', function () {
    // A queixa literal do gestor: "sempre optar pela simetria entre linha
    // superior e inferior". Eram 110px no topo e 70px na base.
    $estilos = file_get_contents(resource_path('views/relatorios/partials/estilos.blade.php'));

    // A regra @page contém uma expressão Blade com chaves; casar por [^}] pararia
    // cedo demais. Busca a declaração de margin diretamente.
    preg_match('/@page\b.*?margin:\s*(\d+)px\s+(\d+)px\s+(\d+)px\s+(\d+)px/s', $estilos, $m);

    expect($m)->not->toBeEmpty('não achei a regra @page com margens em px');

    [, $topo, $direita, $base, $esquerda] = $m;

    expect($topo)->toBe($base, "topo ({$topo}px) e base ({$base}px) precisam ser iguais")
        ->and($direita)->toBe($esquerda);
});

test('cabeçalho e rodapé do relatório têm a mesma altura', function () {
    // É o que TORNA a simetria possível: as margens eram assimétricas porque o
    // cabeçalho ocupava 78px e o rodapé 38px.
    $estilos = file_get_contents(resource_path('views/relatorios/partials/estilos.blade.php'));

    preg_match('/\.rpt-header \{[^}]*height:\s*(\d+)px/', $estilos, $h);
    preg_match('/\.rpt-footer \{[^}]*height:\s*(\d+)px/', $estilos, $f);

    expect($h[1] ?? null)->toBe($f[1] ?? null);
});

test('o relatório integrado não se chama mais "Dossiê"', function () {
    // Em Brasília, "dossiê" evoca investigação, não prestação de contas. É a
    // primeira palavra que o ministro lê.
    $violacoes = [];

    foreach ([resource_path('views'), app_path()] as $raiz) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($raiz)) as $arquivo) {
            if ($arquivo->isDir() || $arquivo->getExtension() !== 'php') {
                continue;
            }

            if (str_contains(file_get_contents($arquivo->getPathname()), 'Dossi')) {
                $violacoes[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $arquivo->getPathname());
            }
        }
    }

    expect($violacoes)->toBe([]);
});
