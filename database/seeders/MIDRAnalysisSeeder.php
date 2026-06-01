<?php

namespace Database\Seeders;

use App\Models\StrategicPlanning\AnaliseAmbiental;
use App\Models\StrategicPlanning\PEI;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class MIDRAnalysisSeeder extends Seeder
{
    public function run(): void
    {
        $pei = PEI::ativos()->first();
        $organizacoes = Organization::all();

        if (!$pei || $organizacoes->isEmpty()) return;

        foreach ($organizacoes as $org) {
            // --- MATRIZ SWOT COMPLETA PARA CADA ÓRGÃO ---
            $swot = [
                ['tipo' => 'Força', 'dsc' => 'Corpo técnico altamente qualificado em sua área de atuação.'],
                ['tipo' => 'Força', 'dsc' => 'Domínio de tecnologias de monitoramento e gestão setorial.'],
                ['tipo' => 'Força', 'dsc' => 'Capacidade de articulação com entes federados e parceiros.'],
                ['tipo' => 'Fraqueza', 'dsc' => 'Dependência de recursos orçamentários sujeitos a contingenciamento.'],
                ['tipo' => 'Fraqueza', 'dsc' => 'Necessidade de modernização de sistemas legados de TI.'],
                ['tipo' => 'Oportunidade', 'dsc' => 'Expansão de parcerias internacionais para sustentabilidade.'],
                ['tipo' => 'Oportunidade', 'dsc' => 'Fortalecimento da governança regional via consórcios.'],
                ['tipo' => 'Ameaça', 'dsc' => 'Intensificação de eventos climáticos extremos impactando as metas.'],
                ['tipo' => 'Ameaça', 'dsc' => 'Insegurança jurídica em processos de contratação de grande porte.'],
            ];

            foreach ($swot as $idx => $item) {
                AnaliseAmbiental::create([
                    'cod_pei' => $pei->cod_pei,
                    'cod_organizacao' => $org->cod_organizacao,
                    'dsc_tipo_analise' => 'SWOT',
                    'dsc_categoria' => $item['tipo'],
                    'dsc_item' => $item['dsc'],
                    'num_impacto' => rand(3, 5),
                    'num_ordem' => $idx,
                ]);
            }

            // --- MATRIZ PESTEL COMPLETA PARA CADA ÓRGÃO ---
            $pestel = [
                ['cat' => 'Político', 'dsc' => 'Alinhamento com a agenda nacional de desenvolvimento.'],
                ['cat' => 'Econômico', 'dsc' => 'Variação cambial afetando o custo de insumos importados.'],
                ['cat' => 'Social', 'dsc' => 'Foco na redução de desigualdades socioeconômicas locais.'],
                ['cat' => 'Tecnológico', 'dsc' => 'Adoção de inteligência artificial no monitoramento de riscos.'],
                ['cat' => 'Ambiental', 'dsc' => 'Pressão por metas de descarbonização e reuso de água.'],
                ['cat' => 'Legal', 'dsc' => 'Adequação ao novo arcabouço fiscal e normas regulatórias.'],
            ];

            foreach ($pestel as $idx => $item) {
                AnaliseAmbiental::create([
                    'cod_pei' => $pei->cod_pei,
                    'cod_organizacao' => $org->cod_organizacao,
                    'dsc_tipo_analise' => 'PESTEL',
                    'dsc_categoria' => $item['cat'],
                    'dsc_item' => $item['dsc'],
                    'num_impacto' => rand(3, 5),
                    'num_ordem' => $idx,
                ]);
            }
        }
    }
}
