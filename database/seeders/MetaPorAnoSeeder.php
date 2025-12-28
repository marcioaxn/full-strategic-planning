<?php

namespace Database\Seeders;

use App\Models\PEI\MetaPorAno;
use App\Models\PEI\Indicador;
use App\Models\PEI\LinhaBaseIndicador;
use App\Models\PEI\PEI;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetaPorAnoSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::ativos()->first();
        if (!$peiAtivo) {
            $this->command->warn('Nenhum PEI ativo encontrado.');
            return;
        }

        $indicadores = Indicador::with('linhaBase')->whereHas('objetivoEstrategico.perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->orWhereHas('planoDeAcao.objetivoEstrategico.perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->get();

        if ($indicadores->isEmpty()) {
            $this->command->warn('Nenhum indicador encontrado.');
            return;
        }

        // Limpar metas existentes
        DB::table('tab_meta_por_ano')
            ->whereIn('cod_indicador', $indicadores->pluck('cod_indicador'))
            ->delete();

        $this->command->info('Criando Metas Anuais dos Indicadores...');

        $metas = [];
        $anoInicio = (int)$peiAtivo->num_ano_inicio_vigencia;
        $anoFim = (int)$peiAtivo->num_ano_fim_vigencia;

        if (!$anoInicio || $anoInicio < 2000) {
            $anoInicio = now()->year;
        }
        if (!$anoFim || $anoFim < $anoInicio) {
            $anoFim = $anoInicio;
        }

        foreach ($indicadores as $indicador) {
            $linhaBase = $indicador->linhaBase->first();
            $valorBase = $linhaBase ? (float)$linhaBase->num_linha_base : rand(50, 70);

            // Crescimento incremental ano a ano (5% a 15% ao ano)
            $taxaCrescimento = rand(105, 115) / 100;

            for ($ano = $anoInicio; $ano <= $anoFim; $ano++) {
                $anosDecorridos = $ano - $anoInicio + 1;
                $metaAno = $valorBase * pow($taxaCrescimento, $anosDecorridos);

                // Limitar metas de percentual a 100
                if (str_contains($indicador->dsc_unidade_medida, '%')) {
                    $metaAno = min($metaAno, 100);
                }

                $metas[] = [
                    'cod_indicador' => $indicador->cod_indicador,
                    'num_ano' => $ano,
                    'meta' => round($metaAno, 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Inserir em lotes
        foreach (array_chunk($metas, 100) as $chunk) {
            MetaPorAno::insert($chunk);
        }

        $this->command->info('✓ ' . count($metas) . ' Metas Anuais criadas com sucesso!');
    }
}
