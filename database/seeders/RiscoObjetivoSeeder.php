<?php

namespace Database\Seeders;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\RiskManagement\Risco;
use Illuminate\Database\Seeder;

class RiscoObjetivoSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::where('bln_ativo', true)->first();
        if (!$peiAtivo) return;

        $objetivos = Objetivo::whereHas('perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->get();

        $riscos = Risco::where('cod_pei', $peiAtivo->cod_pei)->get();

        if ($objetivos->isEmpty() || $riscos->isEmpty()) return;

        foreach ($riscos as $risco) {
            // Vincular cada risco a 1 ou 2 objetivos aleatórios
            $vincular = $objetivos->random(rand(1, 2));
            $risco->objetivos()->sync($vincular->pluck('cod_objetivo'));
        }
    }
}