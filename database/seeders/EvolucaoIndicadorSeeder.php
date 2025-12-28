<?php

namespace Database\Seeders;

use App\Models\PEI\EvolucaoIndicador;
use App\Models\PEI\Indicador;
use App\Models\PEI\MetaPorAno;
use App\Models\PEI\PEI;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvolucaoIndicadorSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::ativos()->first();
        if (!$peiAtivo) {
            $this->command->warn('Nenhum PEI ativo encontrado.');
            return;
        }

        $indicadores = Indicador::with('metasPorAno')->whereHas('objetivoEstrategico.perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->orWhereHas('planoDeAcao.objetivoEstrategico.perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->get();

        if ($indicadores->isEmpty()) {
            $this->command->warn('Nenhum indicador encontrado.');
            return;
        }

        // Limpar evoluções existentes
        DB::table('tab_evolucao_indicador')
            ->whereIn('cod_indicador', $indicadores->pluck('cod_indicador'))
            ->delete();

        $this->command->info('Criando Evoluções Mensais dos Indicadores...');

        $evolucoes = [];
        $anoInicio = (int)$peiAtivo->num_ano_inicio_vigencia;
        if (!$anoInicio || $anoInicio < 2000) {
            $anoInicio = now()->year;
        }
        $anoAtual = now()->year;
        $mesAtual = now()->month;

        foreach ($indicadores as $indicador) {
            // Criar evoluções desde o início do PEI até o mês atual
            for ($ano = $anoInicio; $ano <= $anoAtual; $ano++) {
                $metaAno = $indicador->metasPorAno->where('num_ano', $ano)->first();
                if (!$metaAno) continue;

                $metaMensal = (float)$metaAno->meta;
                $ultimoMes = ($ano == $anoAtual) ? $mesAtual : 12;

                for ($mes = 1; $mes <= $ultimoMes; $mes++) {
                    // Determinar período de medição
                    $periodoMedicao = $indicador->dsc_periodo_medicao ?? 'Mensal';

                    // Se não é o mês de medição, pular (simplificado para trimestral/semestral)
                    if ($periodoMedicao === 'Trimestral' && $mes % 3 !== 0) continue;
                    if ($periodoMedicao === 'Semestral' && $mes % 6 !== 0) continue;
                    if ($periodoMedicao === 'Anual' && $mes !== 12) continue;

                    // Simular evolução realista
                    // Meta mensal = meta anual / 12 (simplificado)
                    $vlrPrevisto = round($metaMensal / 12, 2);

                    // Realizado varia entre 70% e 110% do previsto
                    $variacao = rand(70, 110) / 100;
                    $vlrRealizado = round($vlrPrevisto * $variacao, 2);

                    // Evoluções mais antigas têm maior probabilidade de atualização
                    $probabilidadeAtualizacao = ($ano < $anoAtual || $mes < $mesAtual) ? 95 : 60;
                    $blnAtualizado = (rand(1, 100) <= $probabilidadeAtualizacao) ? 'Sim' : 'Não';

                    $avaliacoes = [
                        'Meta atingida conforme planejamento',
                        'Resultado acima do esperado, reflexo das ações implementadas',
                        'Desempenho dentro da normalidade',
                        'Leve desvio identificado, ações corretivas em andamento',
                        'Resultado excelente, superando expectativas',
                        'Performance estável em relação ao período anterior',
                        'Necessário reforço nas ações para atingir meta',
                        'Evolução positiva em relação ao mês anterior',
                    ];

                    $dataEvolucao = now()->year($ano)->month($mes);

                    $evolucoes[] = [
                        'cod_indicador' => $indicador->cod_indicador,
                        'num_ano' => $ano,
                        'num_mes' => $mes,
                        'vlr_previsto' => $vlrPrevisto,
                        'vlr_realizado' => $vlrRealizado,
                        'txt_avaliacao' => $blnAtualizado === 'Sim' ? $avaliacoes[array_rand($avaliacoes)] : null,
                        'bln_atualizado' => $blnAtualizado,
                        'created_at' => $dataEvolucao,
                        'updated_at' => $dataEvolucao,
                    ];
                }
            }
        }

        // Inserir em lotes
        foreach (array_chunk($evolucoes, 200) as $chunk) {
            EvolucaoIndicador::insert($chunk);
        }

        $this->command->info('✓ ' . count($evolucoes) . ' Evoluções de Indicadores criadas com sucesso!');
    }
}
