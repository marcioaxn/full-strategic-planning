<?php

namespace Database\Seeders;

use App\Models\PEI\PlanoDeAcao;
use App\Models\PEI\Entrega;
use Illuminate\Database\Seeder;

class EntregaSeeder extends Seeder
{
    public function run(): void
    {
        $planos = PlanoDeAcao::with('objetivo.perspectiva')->get();

        foreach ($planos as $plano) {
            // Criar 3 a 5 entregas por plano
            $qtd = rand(3, 5);
            for ($i = 1; $i <= $qtd; $i++) {
                Entrega::create([
                    'cod_plano_de_acao' => $plano->cod_plano_de_acao,
                    'dsc_entrega' => "Entrega $i do plano " . $plano->dsc_plano_de_acao,
                    'dsc_periodo_medicao' => 'Mensal',
                    'bln_status' => rand(0, 1) ? 'Concluído' : 'Em Andamento',
                    'num_nivel_hierarquico_apresentacao' => $i
                ]);
            }
        }
    }
}