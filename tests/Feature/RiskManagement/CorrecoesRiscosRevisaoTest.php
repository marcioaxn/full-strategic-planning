<?php

/*
 * Correções da revisão adversária de 04/10/2026 (Riscos, mitigação,
 * ocorrências, matriz e Excel). Cada teste vai pelo caminho que a tela usa:
 * o método público do componente Livewire ou a rota do relatório.
 */

use App\Exports\RiscosExport;
use App\Livewire\RiskManagement\GerenciarMitigacoes;
use App\Livewire\RiskManagement\ListarRiscos;
use App\Livewire\RiskManagement\MatrizRiscos;
use App\Livewire\RiskManagement\RegistrarOcorrencias;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\RiskManagement\Risco;
use App\Models\RiskManagement\RiscoMitigacao;
use App\Models\StrategicPlanning\PEI;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Unidade-mãe com uma filha, admin na mãe (o perfil alcança a filha), um
 * ciclo e um risco em cada unidade.
 *
 * @return array{admin: User, mae: Organization, filha: Organization, pei: PEI, riscoMae: Risco, riscoFilha: Risco, pessoaFilha: User}
 */
function cenarioRevisaoRiscos(): array
{
    $mae = Organization::create(['nom_organizacao' => 'Unidade Mãe', 'sgl_organizacao' => 'MAE']);
    $filha = Organization::create(['nom_organizacao' => 'Unidade Filha', 'sgl_organizacao' => 'FIL', 'rel_cod_organizacao' => $mae->cod_organizacao]);

    $pei = PEI::create(['dsc_pei' => 'Ciclo', 'num_ano_inicio_pei' => (int) date('Y'), 'num_ano_fim_pei' => (int) date('Y') + 3]);

    $admin = User::factory()->create(['ativo' => true]);
    $admin->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $mae->cod_organizacao]);
    $admin->organizacoes()->syncWithoutDetaching([$mae->cod_organizacao]);
    $admin->unsetRelation('perfisAcesso');

    $pessoaFilha = User::factory()->create(['ativo' => true, 'name' => 'Pessoa Só da Filha']);
    $pessoaFilha->organizacoes()->syncWithoutDetaching([$filha->cod_organizacao]);

    Session::put('organizacao_selecionada_id', $mae->cod_organizacao);
    Session::put('pei_selecionado_id', $pei->cod_pei);

    $risco = fn (Organization $org, string $titulo, int $p, int $i, User $resp) => Risco::create([
        'cod_pei' => $pei->cod_pei, 'cod_organizacao' => $org->cod_organizacao,
        'dsc_titulo' => $titulo, 'txt_descricao' => "Descrição de {$titulo}", 'dsc_categoria' => 'Operacional',
        'dsc_status' => 'Identificado', 'num_probabilidade' => $p, 'num_impacto' => $i,
        'cod_responsavel_monitoramento' => $resp->getKey(),
    ]);

    return [
        'admin' => $admin, 'mae' => $mae, 'filha' => $filha, 'pei' => $pei, 'pessoaFilha' => $pessoaFilha,
        'riscoMae' => $risco($mae, 'Risco da mãe', 3, 5, $admin),
        'riscoFilha' => $risco($filha, 'Risco da filha', 2, 2, $pessoaFilha),
    ];
}

// ── Mitigação e ocorrência ────────────────────────────────────────────────

test('mitigação com o custo apagado salva com custo nulo, sem erro', function () {
    $c = cenarioRevisaoRiscos();

    Livewire::actingAs($c['admin'])
        ->test(GerenciarMitigacoes::class, ['riscoId' => $c['riscoMae']->cod_risco])
        ->call('create')
        ->set('form.txt_descricao', 'Revisar contratos.')
        ->set('form.cod_responsavel', $c['admin']->getKey())
        ->set('form.dte_prazo', now()->addMonth()->format('Y-m-d'))
        ->set('form.vlr_custo_estimado', '')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $m = RiscoMitigacao::where('cod_risco', $c['riscoMae']->cod_risco)->sole();
    expect($m->vlr_custo_estimado)->toBeNull();
});

test('mitigação incompleta mostra a mensagem de cada campo na tela', function () {
    $c = cenarioRevisaoRiscos();

    Livewire::actingAs($c['admin'])
        ->test(GerenciarMitigacoes::class, ['riscoId' => $c['riscoMae']->cod_risco])
        ->call('create')
        ->set('form.txt_descricao', 'Só a descrição.')
        ->call('save')
        ->assertHasErrors(['form.cod_responsavel', 'form.dte_prazo'])
        ->assertSee('Escolha o responsável.')
        ->assertSee('Informe o prazo.');
});

test('mitigação com responsável de fora da unidade mostra o motivo na tela', function () {
    $c = cenarioRevisaoRiscos();

    Livewire::actingAs($c['admin'])
        ->test(GerenciarMitigacoes::class, ['riscoId' => $c['riscoMae']->cod_risco])
        ->call('create')
        ->set('form.txt_descricao', 'Ação')
        ->set('form.cod_responsavel', $c['pessoaFilha']->getKey())
        ->set('form.dte_prazo', now()->addMonth()->format('Y-m-d'))
        ->call('save')
        ->assertHasErrors(['form.cod_responsavel'])
        ->assertSee('Escolha um responsável da unidade do risco.');
});

test('ocorrência sem descrição mostra a mensagem na tela', function () {
    $c = cenarioRevisaoRiscos();

    Livewire::actingAs($c['admin'])
        ->test(RegistrarOcorrencias::class, ['riscoId' => $c['riscoMae']->cod_risco])
        ->call('create')
        ->call('save')
        ->assertHasErrors(['form.txt_descricao'])
        ->assertSee('Descreva o que aconteceu.');
});

test('nenhuma exclusão por wire:click depende de onclick="return confirm" (Cancelar não impedia)', function () {
    // return false no onclick só faz preventDefault; o wire:click roda mesmo assim.
    // A confirmação certa é wire:confirm.
    $pendentes = [];

    $achados = [];
    $arquivos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));
    foreach ($arquivos as $arquivo) {
        if ($arquivo->isDir() || ! str_ends_with($arquivo->getFilename(), '.blade.php')) {
            continue;
        }
        $relativo = str_replace('\\', '/', substr($arquivo->getPathname(), strlen(resource_path('views')) + 1));
        if (in_array($relativo, $pendentes, true)) {
            continue;
        }
        // onclick="return confirm(" com wire:click no mesmo elemento (o atributo
        // do wire:click tem "->" dentro, então não dá para delimitar o tag por ">":
        // olha-se o trecho desde o início do elemento).
        $conteudo = file_get_contents($arquivo->getPathname());
        $offset = 0;
        while (($pos = strpos($conteudo, 'onclick="return confirm(', $offset)) !== false) {
            $inicio = strrpos(substr($conteudo, 0, $pos), '<');
            if (str_contains(substr($conteudo, $inicio, $pos - $inicio), 'wire:click')) {
                $achados[] = $relativo;
                break;
            }
            $offset = $pos + 1;
        }
    }

    expect($achados)->toBe([]);
});

// ── Excel ─────────────────────────────────────────────────────────────────

test('o Excel de riscos traz título e descrição reais e a mesma régua de criticidade das telas', function () {
    $c = cenarioRevisaoRiscos();
    Excel::fake();

    $this->actingAs($c['admin'])
        ->get(route('relatorios.riscos.excel', ['organizacao_id' => $c['mae']->cod_organizacao]))
        ->assertOk();

    Excel::assertDownloaded('Riscos_MAE.xlsx', function (RiscosExport $export) use ($c) {
        $linhas = $export->collection()->map(fn ($r) => $export->map($r))->all();
        $linhaMae = collect($linhas)->firstWhere(0, 'Risco da mãe');

        // 3 × 5 = 15: "Alto" na tela (Crítico só a partir de 16).
        return $linhaMae !== null
            && $linhaMae[1] === 'Descrição de Risco da mãe'
            && $linhaMae[4] === 15
            && $linhaMae[5] === $c['riscoMae']->fresh()->getNivelRiscoLabel()
            && $linhaMae[5] === 'Alto';
    });
});

// ── Matriz × lista ─────────────────────────────────────────────────────────

test('com a unidade-mãe selecionada, a matriz mostra os mesmos riscos da lista (inclui a filha)', function () {
    $c = cenarioRevisaoRiscos();

    $lista = Livewire::actingAs($c['admin'])->test(ListarRiscos::class)->viewData('riscos');
    $matriz = Livewire::actingAs($c['admin'])->test(MatrizRiscos::class)->get('matriz');
    $naMatriz = collect($matriz)->flatten(2)->pluck('cod_risco')->sort()->values()->all();

    expect($naMatriz)->toBe(collect($lista->items())->pluck('cod_risco')->sort()->values()->all())
        ->and($naMatriz)->toContain($c['riscoFilha']->cod_risco);
});

test('o link do risco na matriz abre a lista já filtrada', function () {
    $c = cenarioRevisaoRiscos();

    Livewire::actingAs($c['admin'])->test(MatrizRiscos::class)
        ->assertSeeHtml(e(route('riscos.index', ['search' => 'Risco da filha'])));

    $lista = Livewire::withQueryParams(['search' => 'Risco da filha'])
        ->actingAs($c['admin'])
        ->test(ListarRiscos::class)
        ->assertSet('search', 'Risco da filha')
        ->viewData('riscos');

    expect(collect($lista->items())->pluck('dsc_titulo')->all())->toBe(['Risco da filha']);
});

// ── Responsável de risco de unidade subordinada ───────────────────────────

test('editar risco da unidade filha oferece as pessoas da filha e aceita trocar o responsável', function () {
    $c = cenarioRevisaoRiscos();
    $outra = User::factory()->create(['ativo' => true, 'name' => 'Outra da Filha']);
    $outra->organizacoes()->syncWithoutDetaching([$c['filha']->cod_organizacao]);

    $tela = Livewire::actingAs($c['admin'])->test(ListarRiscos::class)
        ->call('edit', $c['riscoFilha']->cod_risco);

    expect(collect($tela->get('usuarios'))->pluck('id')->all())
        ->toContain($c['pessoaFilha']->getKey(), $outra->getKey());

    $tela->set('form.cod_responsavel_monitoramento', $outra->getKey())
        ->call('save')
        ->assertHasNoErrors();

    expect($c['riscoFilha']->fresh()->cod_responsavel_monitoramento)->toBe($outra->getKey());
});

test('depois de editar risco da filha, "Novo risco" volta a oferecer as pessoas da unidade selecionada', function () {
    $c = cenarioRevisaoRiscos();

    $tela = Livewire::actingAs($c['admin'])->test(ListarRiscos::class)
        ->call('edit', $c['riscoFilha']->cod_risco)
        ->call('create');

    expect(collect($tela->get('usuarios'))->pluck('id')->all())
        ->toContain($c['admin']->getKey())
        ->not->toContain($c['pessoaFilha']->getKey());
});

// ── Pré-visualização do nível ─────────────────────────────────────────────

test('a pré-visualização do nível usa a mesma régua da matriz (5 = Médio, amarelo)', function () {
    $c = cenarioRevisaoRiscos();

    Livewire::actingAs($c['admin'])->test(ListarRiscos::class)
        ->call('create')
        ->set('form.num_probabilidade', 5)
        ->set('form.num_impacto', 1)
        ->assertSeeHtml('color: #eab308')
        ->assertSee('Médio');

    expect(Risco::corDoNivel(5))->toBe('#eab308')
        ->and(Risco::corDoNivel(15))->toBe('#f97316')
        ->and(Risco::rotuloDoNivel(15))->toBe('Alto')
        ->and(Risco::rotuloDoNivel(16))->toBe('Crítico');
});

// ── Código R-nn ───────────────────────────────────────────────────────────

test('o código do risco não é reaproveitado depois de uma exclusão', function () {
    $c = cenarioRevisaoRiscos();
    $ultimo = $c['riscoFilha'];
    $codigoExcluido = $ultimo->num_codigo_risco;
    $ultimo->delete();

    $novo = Risco::create([
        'cod_pei' => $c['pei']->cod_pei, 'cod_organizacao' => $c['mae']->cod_organizacao,
        'dsc_titulo' => 'Novo', 'txt_descricao' => 'Novo risco', 'dsc_categoria' => 'Operacional', 'dsc_status' => 'Identificado',
        'num_probabilidade' => 1, 'num_impacto' => 1, 'cod_responsavel_monitoramento' => $c['admin']->getKey(),
    ]);

    expect($novo->num_codigo_risco)->toBeGreaterThan($codigoExcluido);
});
