<?php

/*
 * As DUAS populações de cliente, que é o problema que originou a demanda.
 *
 * O vocabulário era semeado dentro do up() de uma migration. Migration já
 * aplicada nunca roda de novo — então cliente que clonava hoje recebia a opção
 * nova, e cliente instalado no ano passado nunca recebia, mesmo rodando
 * `migrate`. Um teste que só exercitasse a instalação nova passaria verde com
 * o defeito inteiro de pé.
 */

use App\Models\ActionPlan\TipoExecucao;
use App\Services\Seeding\VocabularioControlado;
use Database\Seeders\TipoExecucaoSeeder;
use Illuminate\Support\Facades\DB;

const TABELA_TIPOS = 'action_plan.tab_tipo_execucao';

function limparTipos(): void
{
    DB::table(TABELA_TIPOS)->delete();
}

/** Reproduz a base de um cliente instalado antes da mudança: os três tipos antigos. */
function baseDeClienteAntigo(): void
{
    limparTipos();

    DB::table(TABELA_TIPOS)->insert([
        ['cod_tipo_execucao' => TipoExecucao::ACAO, 'dsc_tipo_execucao' => 'Ação', 'created_at' => now(), 'updated_at' => now()],
        ['cod_tipo_execucao' => TipoExecucao::INICIATIVA, 'dsc_tipo_execucao' => 'Iniciativa', 'created_at' => now(), 'updated_at' => now()],
        ['cod_tipo_execucao' => TipoExecucao::PROJETO, 'dsc_tipo_execucao' => 'Projeto', 'created_at' => now(), 'updated_at' => now()],
    ]);
}

test('instalação NOVA: tabela vazia recebe exatamente Ação e Projeto', function () {
    limparTipos();

    (new TipoExecucaoSeeder)->run();

    $rotulos = TipoExecucao::orderBy('dsc_tipo_execucao')->pluck('dsc_tipo_execucao')->all();

    expect($rotulos)->toBe(['Ação', 'Projeto']);
});

test('instalação ANTIGA: recebe o que falta e nada é apagado', function () {
    baseDeClienteAntigo();
    $antes = DB::table(TABELA_TIPOS)->count();

    (new TipoExecucaoSeeder)->run();

    // Nenhuma linha foi removida fisicamente: Iniciativa foi só aposentada.
    expect(DB::table(TABELA_TIPOS)->count())->toBe($antes)
        ->and(TipoExecucao::orderBy('dsc_tipo_execucao')->pluck('dsc_tipo_execucao')->all())
        ->toBe(['Ação', 'Projeto'])
        ->and(TipoExecucao::withTrashed()->find(TipoExecucao::INICIATIVA))->not->toBeNull();
});

test('segunda execução não muda nada', function () {
    limparTipos();
    (new TipoExecucaoSeeder)->run();

    $snapshot = DB::table(TABELA_TIPOS)->orderBy('cod_tipo_execucao')->get()->toJson();

    (new TipoExecucaoSeeder)->run();
    (new TipoExecucaoSeeder)->run();

    expect(DB::table(TABELA_TIPOS)->orderBy('cod_tipo_execucao')->get()->toJson())
        ->toBe($snapshot);
});

test('customização do cliente é preservada', function () {
    limparTipos();
    DB::table(TABELA_TIPOS)->insert([
        'cod_tipo_execucao' => TipoExecucao::ACAO,
        // O cliente renomeou. Isso é decisão dele.
        'dsc_tipo_execucao' => 'Ação Corretiva',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    (new TipoExecucaoSeeder)->run();

    expect(TipoExecucao::find(TipoExecucao::ACAO)->dsc_tipo_execucao)->toBe('Ação Corretiva')
        ->and(TipoExecucao::find(TipoExecucao::PROJETO))->not->toBeNull();
});

test('aposentadoria é RECUSADA quando há Iniciativa vinculada ao tipo', function () {
    baseDeClienteAntigo();

    $vocabulario = new VocabularioControlado;
    $vocabulario->aposentar(
        TABELA_TIPOS,
        'cod_tipo_execucao',
        [TipoExecucao::INICIATIVA],
        dependencias: [
            // Aponta para a própria tabela de tipos como se fosse a dependente,
            // só para provar a contagem sem depender do schema de plano de ação.
            ['tabela' => TABELA_TIPOS, 'coluna' => 'cod_tipo_execucao'],
        ]
    );

    $relatorio = $vocabulario->relatorio();

    expect($relatorio['recusados'])->toHaveCount(1)
        ->and(TipoExecucao::find(TipoExecucao::INICIATIVA))->not->toBeNull();
});

test('nenhuma query do seeder usa a cláusula de upsert do PostgreSQL 9.5+', function () {
    // A asserção é sobre o SQL EMITIDO, não sobre o resultado: é o artefato
    // real que roda na instalação do cliente.
    limparTipos();

    $sqls = [];
    DB::listen(function ($query) use (&$sqls) {
        $sqls[] = $query->sql;
    });

    (new TipoExecucaoSeeder)->run();
    baseDeClienteAntigo();
    (new TipoExecucaoSeeder)->run();

    $proibido = 'on '.'conflict';
    $violacoes = array_values(array_filter(
        $sqls,
        fn ($sql) => str_contains(strtolower($sql), $proibido)
    ));

    expect($violacoes)->toBe([]);
});

test('o serviço recusa tabela sem schema qualificado', function () {
    (new VocabularioControlado)->sincronizar('tab_tipo_execucao', 'cod_tipo_execucao', []);
})->throws(InvalidArgumentException::class);

test('o serviço recusa item sem chave primária fixa', function () {
    (new VocabularioControlado)->sincronizar(
        TABELA_TIPOS,
        'cod_tipo_execucao',
        [['dsc_tipo_execucao' => 'Sem UUID']]
    );
})->throws(InvalidArgumentException::class);
