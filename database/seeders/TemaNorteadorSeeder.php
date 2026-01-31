<?php

namespace Database\Seeders;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\TemaNorteador;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class TemaNorteadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $peiAtivo = PEI::ativos()->first();
        $organizacoes = Organization::all();

        if (!$peiAtivo || $organizacoes->isEmpty()) {
            return;
        }

        $exemplos = [
            'Aprimorar a governança institucional e a gestão de riscos',
            'Fortalecer a transformação digital e a inovação nos processos',
            'Otimizar a alocação de recursos humanos e financeiros',
            'Expandir a transparência e a participação social',
            'Garantir a sustentabilidade e a eficiência operacional',
        ];

        foreach ($organizacoes as $org) {
            // Criar 2 temas norteadores para cada organização no PEI ativo
            foreach (array_rand(array_flip($exemplos), 2) as $nome) {
                TemaNorteador::create([
                    'nom_tema_norteador' => $nome,
                    'cod_pei' => $peiAtivo->cod_pei,
                    'cod_organizacao' => $org->cod_organizacao,
                ]);
            }
        }
    }
}