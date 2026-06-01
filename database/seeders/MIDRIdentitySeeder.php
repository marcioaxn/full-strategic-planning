<?php

namespace Database\Seeders;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Valor;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\TabStatus;
use App\Models\ActionPlan\TipoExecucao;
use Illuminate\Database\Seeder;

class MIDRIdentitySeeder extends Seeder
{
    public function run(): void
    {
        $midr = \App\Models\Organization::where('sgl_organizacao', 'MIDR')->first();

        // 1. Ciclo PEI 2023-2027
        $pei = PEI::updateOrCreate(
            ['dsc_pei' => 'Plano Estratégico Institucional (PEI MIDR) 2023-2027'],
            [
                'num_ano_inicio_pei' => 2023,
                'num_ano_fim_pei' => 2027,
            ]
        );

        // 2. Identidade Estratégica (Extraído Oficialmente da Imagem do Mapa Estratégico)
        MissaoVisaoValores::updateOrCreate(
            ['cod_pei' => $pei->cod_pei, 'cod_organizacao' => $midr->cod_organizacao],
            [
                'dsc_missao' => 'Promover o desenvolvimento sustentável e a integração nacional, visando à redução das desigualdades regionais e à melhoria da qualidade de vida da população',
                'dsc_visao' => 'Ser reconhecido por assegurar proteção e defesa civil, água para todos e desenvolvimento regional integrado e sustentável',
            ]
        );

        // 3. Valores (Extraídos Oficialmente da Imagem do Mapa Estratégico)
        $valores = [
            ['nom' => 'Foco no Cidadão', 'dsc' => 'Atuação orientada para as necessidades e expectativas da sociedade.'],
            ['nom' => 'Inovação', 'dsc' => 'Busca contínua por novas e melhores formas de gerar valor público.'],
            ['nom' => 'Integridade e Transparência', 'dsc' => 'Conduta ética, proba e com ampla publicidade das ações.'],
            ['nom' => 'Integração e Sustentabilidade', 'dsc' => 'Articulação de políticas e atores garantindo o equilíbrio econômico, social e ambiental.'],
            ['nom' => 'Valorização das Potencialidades Locais', 'dsc' => 'Reconhecimento e fomento das vocações e recursos próprios de cada território.'],
        ];

        foreach ($valores as $v) {
            Valor::updateOrCreate(
                ['nom_valor' => $v['nom'], 'cod_pei' => $pei->cod_pei, 'cod_organizacao' => $midr->cod_organizacao],
                ['dsc_valor' => $v['dsc']]
            );
        }

        // 4. Faróis (Graus de Satisfação)
        $graus = [
            ['min' => 0, 'max' => 50, 'dsc' => 'Crítico', 'cor' => '#dc3545'],
            ['min' => 50.01, 'max' => 75, 'dsc' => 'Atenção', 'cor' => '#ffc107'],
            ['min' => 75.01, 'max' => 90, 'dsc' => 'Satisfatório', 'cor' => '#198754'],
            ['min' => 90.01, 'max' => 200, 'dsc' => 'Excelente', 'cor' => '#0dcaf0'],
        ];

        foreach ($graus as $g) {
            GrauSatisfacao::updateOrCreate(
                ['dsc_grau_satisfacao' => $g['dsc'], 'cod_pei' => $pei->cod_pei],
                [
                    'vlr_minimo' => $g['min'],
                    'vlr_maximo' => $g['max'],
                    'cor' => $g['cor'],
                    'num_ano' => 2026,
                ]
            );
        }

        // 5. Status Gerais
        $statuses = ['Não Iniciado', 'Em Andamento', 'Concluído', 'Suspenso', 'Cancelado', 'Atrasado'];
        foreach ($statuses as $s) {
            TabStatus::updateOrCreate(['dsc_status' => $s]);
        }

        // 6. Tipos de Execução
        $tipos = ['Ação', 'Iniciativa', 'Projeto', 'Processo'];
        foreach ($tipos as $t) {
            TipoExecucao::updateOrCreate(['dsc_tipo_execucao' => $t]);
        }
    }
}