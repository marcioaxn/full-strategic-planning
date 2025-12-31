<?php

namespace Database\Seeders;

use App\Models\PEI\PEI;
use App\Models\PEI\Objetivo;
use App\Models\PEI\PlanoDeAcao;
use App\Models\PEI\TipoExecucao;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class PlanoAcaoSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::first();
        if (!$peiAtivo) return;

        $tipos = TipoExecucao::all();
        $organizacoes = Organization::all();
        
        if ($tipos->isEmpty() || $organizacoes->isEmpty()) return;

        $objetivos = Objetivo::whereHas('perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->get();

        foreach ($objetivos as $objetivo) {
            // Criar 1 a 2 planos por objetivo
            $qtd = rand(1, 2);
            for ($i = 1; $i <= $qtd; $i++) {
                PlanoDeAcao::create([
                    'cod_objetivo' => $objetivo->cod_objetivo,
                    'cod_tipo_execucao' => $tipos->random()->cod_tipo_execucao,
                    'cod_organizacao' => $organizacoes->random()->cod_organizacao,
                    'dsc_plano_de_acao' => "Plano de Ação $i para: " . $objetivo->nom_objetivo,
                    'dte_inicio' => now()->startOfYear(),
                    'dte_fim' => now()->endOfYear(),
                    'vlr_orcamento_previsto' => rand(10000, 500000),
                    'bln_status' => 'Em Andamento',
                    'num_nivel_hierarquico_apresentacao' => $i
                ]);
            }
        }
    }
}