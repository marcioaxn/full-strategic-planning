<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\Entrega;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FixPlanosEntregasDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seae:fix-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Correção de datas nulas em Planos e Entregas respeitando vigência do PEI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando correção de datas...');
        
        $anoAtual = now()->year;

        DB::transaction(function() use ($anoAtual) {
            
            // 1. Corrigir Planos de Ação
            $planosSemData = PlanoDeAcao::whereNull('dte_inicio')
                ->orWhereNull('dte_fim')
                ->with(['objetivo.perspectiva.pei']) // Eager loading da hierarquia
                ->get();

            $bar = $this->output->createProgressBar(count($planosSemData));
            $this->info("\nCorrigindo " . count($planosSemData) . " Planos de Ação...");

            foreach ($planosSemData as $plano) {
                $pei = $plano->objetivo->perspectiva->pei ?? null;

                if (!$pei) {
                    $this->warn("\nPlano {$plano->cod_plano_de_acao} sem PEI vinculado. Pulando.");
                    continue;
                }

                $anoInicioPei = $pei->num_ano_inicio_pei;
                $anoFimPei = $pei->num_ano_fim_pei;

                // Definir ano de referência (preferência pelo ano atual se dentro do ciclo)
                $anoReferencia = $anoAtual;
                if ($anoAtual < $anoInicioPei) $anoReferencia = $anoInicioPei;
                if ($anoAtual > $anoFimPei) $anoReferencia = $anoFimPei;

                // Definir vigência para o ano de referência inteiro
                $plano->dte_inicio = Carbon::create($anoReferencia, 1, 1)->startOfDay();
                $plano->dte_fim = Carbon::create($anoReferencia, 12, 31)->endOfDay();
                
                // Salvar sem disparar eventos se possível, ou salvar normal
                $plano->saveQuietly();
                $bar->advance();
            }
            $bar->finish();

            // 2. Corrigir Entregas
            $entregasSemPrazo = Entrega::whereNull('dte_prazo')
                ->with('planoDeAcao')
                ->get();

            $this->info("\n\nCorrigindo " . count($entregasSemPrazo) . " Entregas...");
            $bar2 = $this->output->createProgressBar(count($entregasSemPrazo));

            foreach ($entregasSemPrazo as $entrega) {
                $plano = $entrega->planoDeAcao;

                if (!$plano || !$plano->dte_fim) {
                    // Se o plano ainda não tem data (caso raro se passo 1 rodou), pular
                    continue; 
                }

                // Definir prazo como a data fim do plano (limite máximo permitido)
                // Ou poderíamos usar o fim do ano de referência do plano
                $entrega->dte_prazo = $plano->dte_fim;
                $entrega->saveQuietly();
                
                $bar2->advance();
            }
            $bar2->finish();
        });

        $this->info("\n\nCorreção concluída com sucesso!");
    }
}
