<?php

/**
 * Conferência (somente leitura) do banco de demonstração: contagem por tabela
 * e farol de cada indicador no ano corrente.
 *
 * Uso: $env:DB_DATABASE='pei_manual'; $env:DB_PORT='5434'; php documentacao/manual/gerador/contar-demonstracao.php
 */

use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\GrauSatisfacao;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

chdir(__DIR__.'/../../..');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (DB::connection()->getDatabaseName() !== 'pei_manual') {
    fwrite(STDERR, 'ABORTADO: conexão não aponta para pei_manual.'.PHP_EOL);
    exit(1);
}

$tabelas = DB::select("select table_schema as s, table_name as t from information_schema.tables
    where table_type = 'BASE TABLE' and table_schema in ('pei','strategic_planning','action_plan','performance_indicators','risk_management','organization')
    and table_name not in ('migrations','cache','cache_locks','jobs','job_batches','failed_jobs','password_reset_tokens','personal_access_tokens','sessions')
    order by 1, 2");
foreach ($tabelas as $tab) {
    $nome = $tab->s.'.'.$tab->t;
    $total = DB::table($nome)->count();
    $temSoft = DB::selectOne('select 1 as x from information_schema.columns where table_schema = ? and table_name = ? and column_name = ?', [$tab->s, $tab->t, 'deleted_at']);
    $ativos = $temSoft ? DB::table($nome)->whereNull('deleted_at')->count() : $total;
    printf("%-60s %6d%s\n", $nome, $total, $ativos !== $total ? "  (ativos: {$ativos})" : '');
}

$ano = (int) now()->year;
echo PHP_EOL."Farol dos indicadores em {$ano}:".PHP_EOL;
foreach (Indicador::with(['objetivo.perspectiva', 'planoDeAcao.objetivo.perspectiva'])->orderBy('nom_indicador')->get() as $ind) {
    $pei = $ind->codPeiDoCiclo();
    $at = $ind->atingimentoMedido($ano);
    $faixa = $at === null ? 'sem medição' : (GrauSatisfacao::rotuloDe((float) $at, $pei, $ano) ?? '?');
    printf("  %-62s %8s  %s\n", mb_strimwidth($ind->nom_indicador, 0, 62), $at === null ? '—' : number_format($at, 1, ',', '.').'%', $faixa);
}
