<?php

namespace App\Console\Commands;

use App\Models\ActionPlan\TipoExecucao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Move Iniciativas de um tipo de execução para outro.
 *
 * Separado do seeder de propósito: remanejar registro do cliente é DECISÃO,
 * não efeito colateral de `php artisan db:seed`. Quem roda precisa ver o
 * número antes e depois, e confirmar.
 *
 * Simulação é o padrão: sem --aplicar, nada é escrito.
 */
class RemanejarTipoIniciativa extends Command
{
    protected $signature = 'iniciativas:remanejar-tipo
                            {--de= : rótulo do tipo de origem (ex.: "Iniciativa")}
                            {--para= : rótulo do tipo de destino (ex.: "Ação")}
                            {--aplicar : escreve de fato; sem esta opção apenas simula}';

    protected $description = 'Remaneja Iniciativas de um tipo de execução para outro (simula por padrão)';

    public function handle(): int
    {
        $de = $this->option('de');
        $para = $this->option('para');

        if (! $de || ! $para) {
            $this->error('Informe --de e --para.');
            $this->line('Ex.: <comment>php artisan iniciativas:remanejar-tipo --de="Iniciativa" --para="Ação"</comment>');

            return self::FAILURE;
        }

        $origem = TipoExecucao::withTrashed()->where('dsc_tipo_execucao', $de)->first();
        $destino = TipoExecucao::where('dsc_tipo_execucao', $para)->first();

        if (! $origem) {
            $this->error("Tipo de origem \"{$de}\" não encontrado.");

            return self::FAILURE;
        }

        if (! $destino) {
            $this->error("Tipo de destino \"{$para}\" não encontrado ou está aposentado.");
            $this->line('Rode antes: <comment>php artisan db:seed --class=TipoExecucaoSeeder</comment>');

            return self::FAILURE;
        }

        if ($origem->cod_tipo_execucao === $destino->cod_tipo_execucao) {
            $this->error('Origem e destino são o mesmo tipo.');

            return self::FAILURE;
        }

        $afetados = DB::table('action_plan.tab_plano_de_acao')
            ->where('cod_tipo_execucao', $origem->cod_tipo_execucao)
            ->whereNull('deleted_at')
            ->count();

        $totalAntes = DB::table('action_plan.tab_plano_de_acao')->whereNull('deleted_at')->count();

        $this->newLine();
        $this->line("  Origem:  <comment>{$de}</comment>  ({$origem->cod_tipo_execucao})");
        $this->line("  Destino: <comment>{$para}</comment>  ({$destino->cod_tipo_execucao})");
        $this->line("  Iniciativas a remanejar: <comment>{$afetados}</comment>");
        $this->line("  Total de Iniciativas na base: <comment>{$totalAntes}</comment>");
        $this->newLine();

        if ($afetados === 0) {
            $this->info('Nada a remanejar.');

            return self::SUCCESS;
        }

        if (! $this->option('aplicar')) {
            $this->warn('SIMULAÇÃO — nada foi escrito.');
            $this->line('Para aplicar, repita o comando com <comment>--aplicar</comment>.');

            return self::SUCCESS;
        }

        if ($this->input->isInteractive()
            && ! $this->confirm("Remanejar {$afetados} Iniciativa(s) de \"{$de}\" para \"{$para}\"?")) {
            $this->info('Cancelado.');

            return self::SUCCESS;
        }

        $movidos = DB::table('action_plan.tab_plano_de_acao')
            ->where('cod_tipo_execucao', $origem->cod_tipo_execucao)
            ->whereNull('deleted_at')
            ->update([
                'cod_tipo_execucao' => $destino->cod_tipo_execucao,
                'updated_at' => now(),
            ]);

        $totalDepois = DB::table('action_plan.tab_plano_de_acao')->whereNull('deleted_at')->count();

        $this->info("Remanejadas: {$movidos}");

        // O total tem de ser idêntico. Se mudou, alguma linha se perdeu — e a
        // tela pode até parecer certa.
        if ($totalDepois !== $totalAntes) {
            $this->error(
                "ATENÇÃO: o total de Iniciativas mudou de {$totalAntes} para {$totalDepois}. ".
                'Investigue antes de prosseguir.'
            );

            return self::FAILURE;
        }

        $this->line("  Total conferido: {$totalDepois} (inalterado)");

        $restantes = DB::table('action_plan.tab_plano_de_acao')
            ->where('cod_tipo_execucao', $origem->cod_tipo_execucao)
            ->whereNull('deleted_at')
            ->count();

        if ($restantes === 0) {
            $this->newLine();
            $this->line('Agora é possível aposentar o tipo de origem:');
            $this->line('  <comment>php artisan db:seed --class=TipoExecucaoSeeder</comment>');
        }

        return self::SUCCESS;
    }
}
