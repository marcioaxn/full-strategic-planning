<?php

/*
 * A área pública de transparência é SÓ LEITURA.
 *
 * 🔴 O middleware `transparencia` barra POST/PUT/DELETE nas ROTAS públicas —
 * e só nelas. Uma ação de componente Livewire não passa por lá: ela vai para
 * /livewire/update, que é uma rota própria do pacote. Esconder o botão no
 * Blade não protege nada: todo método público de um componente Livewire é
 * invocável direto do navegador, com o snapshot do componente na mão.
 *
 * Estes testes chamam os métodos de escrita COMO VISITANTE ANÔNIMO, que é
 * exatamente o que a área pública tornou possível.
 */

use App\Livewire\ActionPlan\ListarPlanos;
use App\Livewire\PerformanceIndicators\ListarIndicadores;
use App\Livewire\StrategicPlanning\DetalharObjetivo;
use App\Livewire\StrategicPlanning\ListarObjetivos;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\PerformanceIndicators\MetaPorAno;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\ObjetivoComentario;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use Livewire\Livewire;

function cenarioSoLeitura(): array
{
    $org = Organization::create([
        'nom_organizacao' => 'Órgão da Transparência',
        'sgl_organizacao' => 'OTR',
    ]);

    $pei = PEI::create([
        'dsc_pei' => 'PEI Público',
        'num_ano_inicio_pei' => (int) date('Y'),
        'num_ano_fim_pei' => (int) date('Y') + 3,
    ]);

    $perspectiva = Perspectiva::create([
        'cod_pei' => $pei->cod_pei,
        'dsc_perspectiva' => 'Resultados',
        'num_nivel_hierarquico_apresentacao' => 1,
    ]);

    $objetivo = Objetivo::create([
        'cod_perspectiva' => $perspectiva->cod_perspectiva,
        'nom_objetivo' => 'Objetivo público',
        'dsc_objetivo' => 'Visível a qualquer cidadão.',
        'num_nivel_hierarquico_apresentacao' => 1,
        'num_nivel_desdobramento' => 1,
    ]);

    return [$org, $pei, $objetivo];
}

test('visitante anônimo não comenta em um objetivo pela página pública', function () {
    [, , $objetivo] = cenarioSoLeitura();

    Livewire::test(DetalharObjetivo::class, ['id' => $objetivo->cod_objetivo])
        ->set('novoComentario', 'comentário de quem não entrou no sistema')
        ->call('postarComentario')
        ->assertForbidden();

    expect(ObjetivoComentario::count())->toBe(0);
});

test('visitante anônimo não remove comentário de ninguém', function () {
    [$org, , $objetivo] = cenarioSoLeitura();

    $autor = User::factory()->create();
    $autor->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $org->cod_organizacao]);

    $comentario = ObjetivoComentario::create([
        'cod_objetivo' => $objetivo->cod_objetivo,
        'user_id' => $autor->id,
        'dsc_comentario' => 'comentário legítimo',
    ]);

    Livewire::test(DetalharObjetivo::class, ['id' => $objetivo->cod_objetivo])
        ->call('removerComentario', $comentario->cod_objetivo_comentario)
        ->assertForbidden();

    expect(ObjetivoComentario::count())->toBe(1);
});

test('visitante anônimo não cria iniciativa pela tela pública de iniciativas', function () {
    // Achado de 03/10/2026: save() não chamava authorize. A rota /planos está
    // no grupo público e /livewire/update não passa pelo middleware.
    [$org, , $objetivo] = cenarioSoLeitura();

    Livewire::test(ListarPlanos::class)
        ->set('dsc_plano_de_acao', 'Iniciativa criada por visitante')
        ->set('cod_objetivo', $objetivo->cod_objetivo)
        ->set('organizacoes_ids', [$org->cod_organizacao])
        ->call('save')
        ->assertForbidden();

    expect(PlanoDeAcao::count())->toBe(0);
});

test('visitante anônimo não aciona a IA nas telas públicas', function () {
    Livewire::test(ListarPlanos::class)->call('pedirAjudaIA')->assertForbidden();
    Livewire::test(ListarObjetivos::class)->call('pedirAjudaIA')->assertForbidden();
});

test('meta de indicador não é salva nem apagada sem autorização', function () {
    [$org, , $objetivo] = cenarioSoLeitura();

    $indicador = Indicador::create([
        'cod_objetivo' => $objetivo->cod_objetivo,
        'cod_organizacao' => $org->cod_organizacao,
        'nom_indicador' => 'Indicador público',
        'dsc_unidade_medida' => 'Percentual (%)',
        'dsc_polaridade' => 'Maior melhor',
        'dsc_tipo' => 'Estratégico',
        'dsc_indicador' => 'Indicador de teste da área pública.',
        'bln_acumulado' => false,
        'dsc_periodo_medicao' => 'Anual',
    ]);

    $meta = MetaPorAno::create([
        'cod_indicador' => $indicador->cod_indicador,
        'num_ano' => (int) date('Y'),
        'meta' => 100,
    ]);

    // Sem indicador selecionado, o método não pode sequer começar: era por aqui
    // que uma chamada direta chegava ao banco.
    Livewire::test(ListarIndicadores::class)
        ->call('excluirMeta', $meta->cod_meta_por_ano)
        ->assertForbidden();

    expect(MetaPorAno::count())->toBe(1);
});
