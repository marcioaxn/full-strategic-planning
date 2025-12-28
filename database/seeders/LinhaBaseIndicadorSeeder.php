<?php

namespace Database\Seeders;

use App\Models\PEI\LinhaBaseIndicador;
use App\Models\PEI\Indicador;
use App\Models\PEI\PEI;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LinhaBaseIndicadorSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::ativos()->first();
        if (!$peiAtivo) {
            $this->command->warn('Nenhum PEI ativo encontrado.');
            return;
        }

        $indicadores = Indicador::whereHas('objetivoEstrategico.perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->orWhereHas('planoDeAcao.objetivoEstrategico.perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->get();

        if ($indicadores->isEmpty()) {
            $this->command->warn('Nenhum indicador encontrado. Execute IndicadorSeeder primeiro.');
            return;
        }

        // Limpar linhas de base existentes
        DB::table('tab_linha_base_indicador')
            ->whereIn('cod_indicador', $indicadores->pluck('cod_indicador'))
            ->delete();

        $this->command->info('Criando Linhas de Base dos Indicadores...');

        $linhasBase = [];
        $anoInicio = (int)$peiAtivo->num_ano_inicio_vigencia;
        if (!$anoInicio || $anoInicio < 2000) {
            $anoInicio = now()->year;
        }

        foreach ($indicadores as $indicador) {
            // Linha de base no ano anterior ao início do PEI
            $linhasBase[] = [
                'cod_indicador' => $indicador->cod_indicador,
                'num_linha_base' => rand(10, 80) / 10, // Entre 1.0 e 8.0
                'num_ano' => $anoInicio - 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Inserir em lotes
        foreach (array_chunk($linhasBase, 100) as $chunk) {
            LinhaBaseIndicador::insert($chunk);
        }

        $this->command->info('✓ ' . count($linhasBase) . ' Linhas de Base criadas com sucesso!');
    }
}
