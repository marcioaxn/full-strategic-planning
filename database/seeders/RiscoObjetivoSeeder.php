<?php

namespace Database\Seeders;

use App\Models\Risco;
use App\Models\PEI\ObjetivoEstrategico;
use App\Models\PEI\PEI;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiscoObjetivoSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::ativos()->first();
        if (!$peiAtivo) {
            $this->command->warn('Nenhum PEI ativo encontrado.');
            return;
        }

        $riscos = Risco::where('cod_pei', $peiAtivo->cod_pei)->get();
        $objetivos = ObjetivoEstrategico::whereHas('perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->get();

        if ($riscos->isEmpty() || $objetivos->isEmpty()) {
            $this->command->warn('Riscos ou Objetivos não encontrados. Execute RiscoSeeder primeiro.');
            return;
        }

        // Limpar relacionamentos existentes
        DB::table('tab_risco_objetivo')
            ->whereIn('cod_risco', $riscos->pluck('cod_risco'))
            ->delete();

        $this->command->info('Vinculando Riscos aos Objetivos Estratégicos...');

        $vinculos = [];

        // Cada risco pode afetar 1-3 objetivos
        foreach ($riscos as $risco) {
            $numObjetivos = rand(1, 3);
            $objetivosSelecionados = $objetivos->random(min($numObjetivos, $objetivos->count()));

            foreach ($objetivosSelecionados as $objetivo) {
                $vinculos[] = [
                    'cod_risco' => $risco->cod_risco,
                    'cod_objetivo_estrategico' => $objetivo->cod_objetivo_estrategico,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Remover duplicatas
        $vinculos = collect($vinculos)->unique(function ($item) {
            return $item['cod_risco'] . '-' . $item['cod_objetivo_estrategico'];
        })->values()->all();

        // Inserir em lotes
        foreach (array_chunk($vinculos, 100) as $chunk) {
            DB::table('tab_risco_objetivo')->insert($chunk);
        }

        $this->command->info('✓ ' . count($vinculos) . ' vínculos Risco-Objetivo criados com sucesso!');
    }
}
