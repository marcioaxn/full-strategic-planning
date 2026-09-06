<?php

/*
 * Regressão do achado de segurança nº 6 (.claude/seguranca-em-aberto.md).
 *
 * A listagem do histórico filtrava por usuário, mas o método download() não
 * verificava nada. Todo método público de componente Livewire é invocável
 * direto pelo navegador, sem passar pela listagem — então qualquer usuário
 * autenticado baixava o relatório de qualquer outro, contornando de uma vez o
 * RBAC, o recorte organizacional e a capacidade "exportar".
 */

use App\Livewire\Reports\HistoricoRelatorios;
use App\Livewire\Reports\ListarRelatorios;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\Reports\RelatorioGerado;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function orgDosRelatorios(): Organization
{
    return Organization::firstOrCreate(
        ['sgl_organizacao' => 'ORGREL'],
        ['nom_organizacao' => 'Org Relatórios', 'cod_organizacao_pai' => null]
    );
}

function usuarioComRelatorios(string $perfil = PerfilAcesso::ADMIN_UNIDADE): User
{
    $org = orgDosRelatorios();

    $user = User::factory()->create();
    $user->perfisAcesso()->attach($perfil, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');
    $user->organizacoes()->syncWithoutDetaching([$org->cod_organizacao]);

    Session::put('organizacao_selecionada_id', $org->cod_organizacao);

    return $user;
}

function relatorioDe(User $dono, string $caminho = 'relatorios/2026/09/teste.pdf', bool $gravarArquivo = true): RelatorioGerado
{
    if ($gravarArquivo) {
        Storage::disk('relatorios')->put($caminho, '%PDF-1.4 conteudo de teste');
    }

    return RelatorioGerado::create([
        'user_id' => $dono->getKey(),
        'dsc_tipo_relatorio' => 'integrado',
        'dsc_caminho_arquivo' => $caminho,
        'dsc_formato' => 'pdf',
        'txt_filtros_aplicados' => ['ano' => 2026],
        'num_tamanho_bytes' => 26,
    ]);
}

beforeEach(function () {
    Storage::fake('relatorios');
});

test('o dono baixa o próprio relatório', function () {
    $dono = usuarioComRelatorios();
    $relatorio = relatorioDe($dono);

    Livewire::actingAs($dono)
        ->test(HistoricoRelatorios::class)
        ->call('download', $relatorio->cod_relatorio_gerado)
        ->assertOk();
});

test('outro usuário NÃO baixa relatório alheio pelo histórico', function () {
    $dono = usuarioComRelatorios();
    $relatorio = relatorioDe($dono);

    // Mesmo perfil, mesma organização: o que separa é a titularidade.
    $intruso = usuarioComRelatorios();

    Livewire::actingAs($intruso)
        ->test(HistoricoRelatorios::class)
        ->call('download', $relatorio->cod_relatorio_gerado)
        ->assertForbidden();
});

test('outro usuário NÃO baixa relatório alheio pela tela de relatórios', function () {
    // O mesmo download existia duplicado nos dois componentes. Se alguém
    // reintroduzir a versão sem verificação em um deles, este teste falha.
    $dono = usuarioComRelatorios();
    $relatorio = relatorioDe($dono);

    $intruso = usuarioComRelatorios();

    Livewire::actingAs($intruso)
        ->test(ListarRelatorios::class)
        ->call('download', $relatorio->cod_relatorio_gerado)
        ->assertForbidden();
});

test('super admin baixa relatório de qualquer usuário', function () {
    $dono = usuarioComRelatorios();
    $relatorio = relatorioDe($dono);

    $superAdmin = usuarioComRelatorios(PerfilAcesso::SUPER_ADMIN);

    Livewire::actingAs($superAdmin)
        ->test(HistoricoRelatorios::class)
        ->call('download', $relatorio->cod_relatorio_gerado)
        ->assertOk();
});

test('caminho com travessia de diretório é recusado antes de tocar o disco', function () {
    $dono = usuarioComRelatorios();
    // Não grava o arquivo: o próprio Flysystem recusaria o caminho. O que se
    // testa aqui é a normalização do componente, que roda ANTES do Storage.
    $relatorio = relatorioDe($dono, 'relatorios/../../../.env', gravarArquivo: false);

    Livewire::actingAs($dono)
        ->test(HistoricoRelatorios::class)
        ->call('download', $relatorio->cod_relatorio_gerado)
        ->assertOk()
        ->assertDispatched('notify');
});

test('caminho absoluto é recusado', function () {
    $dono = usuarioComRelatorios();
    $relatorio = relatorioDe($dono, 'C:/Windows/System32/drivers/etc/hosts', gravarArquivo: false);

    Livewire::actingAs($dono)
        ->test(HistoricoRelatorios::class)
        ->call('download', $relatorio->cod_relatorio_gerado)
        ->assertOk()
        ->assertDispatched('notify');
});

test('o disco de relatórios é privado e fora do webroot', function () {
    // Trava a segunda metade do achado: os arquivos ficavam num disco marcado
    // como público, servidos pelo Apache em /storage sem passar por Laravel.
    $disco = config('filesystems.disks.relatorios');

    expect($disco)->not->toBeNull()
        ->and($disco['visibility'] ?? null)->toBe('private')
        ->and($disco['url'] ?? null)->toBeNull()
        ->and(str_replace('\\', '/', $disco['root']))->not->toContain('app/public');
});

test('gerar um relatório pela tela REGISTRA no histórico', function () {
    // A causa da tela de Histórico estar vazia: RelatorioGerado era instanciado
    // em UM lugar do projeto — o comando de agendamento, que depende de uma
    // Tarefa Agendada do sistema operacional que ninguém configurou. O que o
    // cliente baixa clicando na tela não era registrado em lugar nenhum.
    $user = usuarioComRelatorios(PerfilAcesso::SUPER_ADMIN);
    $org = orgDosRelatorios();

    $antes = RelatorioGerado::where('user_id', $user->getKey())->count();

    $this->actingAs($user)->get(route('relatorios.riscos.pdf', ['organizacao_id' => $org->cod_organizacao]));

    expect(RelatorioGerado::where('user_id', $user->getKey())->count())->toBe($antes + 1);
});

test('o registro guarda o tipo em linguagem de gente, não nome de método', function () {
    $user = usuarioComRelatorios(PerfilAcesso::SUPER_ADMIN);
    $org = orgDosRelatorios();

    $this->actingAs($user)->get(route('relatorios.riscos.pdf', ['organizacao_id' => $org->cod_organizacao]));

    $registro = RelatorioGerado::where('user_id', $user->getKey())->latest()->first();

    expect($registro->dsc_tipo_relatorio)->toBe('Gestão de Riscos');
});
