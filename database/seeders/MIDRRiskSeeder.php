<?php

namespace Database\Seeders;

use App\Models\RiskManagement\Risco;
use App\Models\RiskManagement\RiscoMitigacao;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class MIDRRiskSeeder extends Seeder
{
    public function run(): void
    {
        $pei = PEI::ativos()->first();
        $organizacoes = Organization::all();
        $usuarios = User::all();
        $objetivos = Objetivo::all();

        if (!$pei || $organizacoes->isEmpty() || $objetivos->isEmpty()) return;

        $dadosRisco = [
            ['tit' => 'Inadimplência de Repasses FNE', 'cat' => 'Financeiro', 'prob' => 3, 'imp' => 4],
            ['tit' => 'Atraso na Execução do PISF', 'cat' => 'Operacional', 'prob' => 2, 'imp' => 5],
            ['tit' => 'Eventos Climáticos Extremos (Secas)', 'cat' => 'Estratégico', 'prob' => 4, 'imp' => 5],
            ['tit' => 'Insegurança Jurídica em Obras Civis', 'cat' => 'Jurídico', 'prob' => 2, 'imp' => 4],
            ['tit' => 'Falha no Monitoramento de Barragens', 'cat' => 'Operacional', 'prob' => 1, 'imp' => 5],
        ];

        $codigo = 1;
        foreach ($organizacoes as $org) {
            foreach ($dadosRisco as $d) {
                $risco = Risco::create([
                    'cod_pei' => $pei->cod_pei,
                    'cod_organizacao' => $org->cod_organizacao,
                    'num_codigo_risco' => $codigo++,
                    'dsc_titulo' => $d['tit'],
                    'txt_descricao' => 'Análise de risco estratégica detalhada para a unidade ' . $org->sgl_organizacao,
                    'dsc_categoria' => $d['cat'],
                    'dsc_status' => 'Monitorado',
                    'num_probabilidade' => $d['prob'],
                    'num_impacto' => $d['imp'],
                    'num_nivel_risco' => $d['prob'] * $d['imp'],
                    'cod_responsavel_monitoramento' => $usuarios->random()->id,
                ]);

                // Vincular a 1 ou 2 objetivos aleatórios
                $risco->objetivos()->attach($objetivos->random(rand(1, 2))->pluck('cod_objetivo'));

                // Criar Mitigação
                RiscoMitigacao::create([
                    'cod_risco' => $risco->cod_risco,
                    'dsc_tipo_mitigacao' => 'Reduzir',
                    'txt_acao_mitigacao' => 'Fortalecer os mecanismos de controle interno e governança da unidade.',
                    'cod_responsavel' => $usuarios->random()->id,
                    'dte_prazo' => now()->addMonths(6),
                    'dsc_status' => 'Em Andamento'
                ]);
            }
        }
    }
}
