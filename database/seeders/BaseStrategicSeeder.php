<?php

namespace Database\Seeders;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\Valor;
use App\Models\Organization;
use App\Models\TabStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BaseStrategicSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Obter Organização Central (criada na migration)
        $org = Organization::where('sgl_organizacao', 'MIDR')->first();
        if (!$org) {
            $org = Organization::create([
                'cod_organizacao' => '3834910f-66f7-46d8-9104-2904d59e1241',
                'sgl_organizacao' => 'MIDR',
                'nom_organizacao' => 'Ministério da Integração e do Desenvolvimento Regional',
                'rel_cod_organizacao' => '3834910f-66f7-46d8-9104-2904d59e1241',
            ]);
        }

        // 1. Criar Ciclo PEI (2024-2027)
        $pei = PEI::updateOrCreate(
            ['dsc_pei' => 'Ciclo Estratégico 2024-2027'],
            [
                'num_ano_inicio_pei' => 2024,
                'num_ano_fim_pei' => 2027,
            ]
        );

        // 2. Identidade Estratégica
        MissaoVisaoValores::updateOrCreate(
            ['cod_pei' => $pei->cod_pei, 'cod_organizacao' => $org->cod_organizacao],
            [
                'dsc_missao' => 'Promover a excelência na gestão pública e resultados para a sociedade.',
                'dsc_visao' => 'Ser referência nacional em inovação e eficiência institucional até 2027.',
            ]
        );

        // 3. Valores
        $valores = [
            ['nom' => 'Ética', 'dsc' => 'Atuar com integridade e retidão.'],
            ['nom' => 'Transparência', 'dsc' => 'Garantir o acesso à informação.'],
            ['nom' => 'Inovação', 'dsc' => 'Buscar soluções criativas e eficientes.'],
            ['nom' => 'Foco no Cidadão', 'dsc' => 'Priorizar as necessidades da sociedade.'],
        ];

        foreach ($valores as $v) {
            Valor::updateOrCreate(
                ['nom_valor' => $v['nom'], 'cod_pei' => $pei->cod_pei, 'cod_organizacao' => $org->cod_organizacao],
                ['dsc_valor' => $v['dsc']]
            );
        }

        // 4. Graus de Satisfação (Faróis)
        $graus = [
            ['vlr_minimo' => 0, 'vlr_maximo' => 50, 'dsc_grau_satisfacao' => 'Crítico', 'cor' => '#dc3545'],
            ['vlr_minimo' => 50.01, 'vlr_maximo' => 75, 'dsc_grau_satisfacao' => 'Atenção', 'cor' => '#ffc107'],
            ['vlr_minimo' => 75.01, 'vlr_maximo' => 90, 'dsc_grau_satisfacao' => 'Satisfatório', 'cor' => '#198754'],
            ['vlr_minimo' => 90.01, 'vlr_maximo' => 200, 'dsc_grau_satisfacao' => 'Excelente', 'cor' => '#0dcaf0'],
        ];

        foreach ($graus as $grau) {
            GrauSatisfacao::updateOrCreate(
                ['dsc_grau_satisfacao' => $grau['dsc_grau_satisfacao'], 'cod_pei' => $pei->cod_pei],
                [
                    'vlr_minimo' => $grau['vlr_minimo'],
                    'vlr_maximo' => $grau['vlr_maximo'],
                    'cor' => $grau['cor'],
                    'num_ano' => 2024,
                ]
            );
        }

        // 5. Status de Planos/Entregas
        $statuses = [
            'Não Iniciado',
            'Em Andamento',
            'Concluído',
            'Suspenso',
            'Cancelado',
            'Atrasado'
        ];

        foreach ($statuses as $status) {
            TabStatus::updateOrCreate(['dsc_status' => $status]);
        }

        // 6. Perspectivas BSC
        $perspectivasData = [
            ['dsc' => 'Sociedade', 'nivel' => 1],
            ['dsc' => 'Processos Internos', 'nivel' => 2],
            ['dsc' => 'Aprendizado e Crescimento', 'nivel' => 3],
            ['dsc' => 'Recursos e Infraestrutura', 'nivel' => 4],
        ];

        $perspectivas = [];
        foreach ($perspectivasData as $data) {
            $perspectivas[] = Perspectiva::updateOrCreate(
                ['dsc_perspectiva' => $data['dsc'], 'cod_pei' => $pei->cod_pei],
                [
                    'num_nivel_hierarquico_apresentacao' => $data['nivel'],
                    'num_peso_indicadores' => 70,
                    'num_peso_planos' => 30,
                ]
            );
        }

        // 7. Objetivos Estratégicos (Exemplos)
        $objetivosData = [
            ['nom' => 'Ampliar a satisfação do cidadão', 'persp' => $perspectivas[0]],
            ['nom' => 'Otimizar processos finalísticos', 'persp' => $perspectivas[1]],
            ['nom' => 'Fortalecer a cultura de inovação', 'persp' => $perspectivas[2]],
            ['nom' => 'Garantir a sustentabilidade financeira', 'persp' => $perspectivas[3]],
        ];

        foreach ($objetivosData as $data) {
            Objetivo::updateOrCreate(
                ['nom_objetivo' => $data['nom'], 'cod_perspectiva' => $data['persp']->cod_perspectiva],
                [
                    'dsc_objetivo' => 'Descrição detalhada do objetivo: ' . $data['nom'],
                    'num_nivel_hierarquico_apresentacao' => 1,
                ]
            );
        }
    }
}
