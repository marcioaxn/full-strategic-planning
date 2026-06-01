<?php

namespace Database\Seeders;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\TemaNorteador;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class MIDRStrategicSeeder extends Seeder
{
    public function run(): void
    {
        $pei = PEI::ativos()->first();
        $midr = Organization::where('sgl_organizacao', 'MIDR')->first();

        if (!$pei || !$midr) return;

        // 1. Perspectivas (Extraídas ipsis litteris do Mapa Estratégico Oficial)
        $perspectivasData = [
            ['dsc' => 'Resultado Integrado', 'nivel' => 1],
            ['dsc' => 'Políticas Públicas', 'nivel' => 2],
            ['dsc' => 'Parceria e Fomento', 'nivel' => 3],
            ['dsc' => 'Governança e Gestão Corporativa', 'nivel' => 4],
        ];

        $perspModels = [];
        foreach ($perspectivasData as $p) {
            $perspModels[] = Perspectiva::updateOrCreate(
                ['dsc_perspectiva' => $p['dsc'], 'cod_pei' => $pei->cod_pei],
                [
                    'num_nivel_hierarquico_apresentacao' => $p['nivel'],
                    'num_peso_indicadores' => 80,
                    'num_peso_planos' => 20,
                ]
            );
        }

        // 2. Temas Norteadores
        $temas = [
            'Redução das Desigualdades Regionais',
            'Segurança Hídrica e Mudança do Clima',
            'Gestão de Riscos de Desastres',
            'Desenvolvimento Produtivo e Inovação',
        ];

        foreach ($temas as $t) {
            TemaNorteador::updateOrCreate(
                ['nom_tema_norteador' => $t, 'cod_pei' => $pei->cod_pei, 'cod_organizacao' => $midr->cod_organizacao]
            );
        }

        // 3. Objetivos Estratégicos (Extraídos ipsis litteris do Mapa Estratégico Oficial)
        $objetivos = [
            // Resultado Integrado
            ['nom' => 'Efetivar o desenvolvimento socioeconômico e a redução das desigualdades regionais', 'persp' => $perspModels[0]],
            
            // Políticas Públicas
            ['nom' => 'Ampliar a capacidade dos municípios para a gestão dos riscos de desastres, com investimentos em prevenção, mitigação, preparação, integração das políticas públicas e capacitação dos atores do Sistema Nacional de Proteção e Defesa Civil', 'persp' => $perspModels[1]],
            ['nom' => 'Otimizar o apoio federal nas ações de resposta e recuperação pós desastre', 'persp' => $perspModels[1]],
            ['nom' => 'Aperfeiçoar as estratégias e instrumentos de planejamento multiescalar e transversal para o desenvolvimento regional e ordenamento territorial, com melhoria da governança e transparência', 'persp' => $perspModels[1]],
            ['nom' => 'Assegurar o desenvolvimento produtivo inovador, inclusivo e sustentável prioritariamente nos territórios elegíveis da Política Nacional de Desenvolvimento Regional', 'persp' => $perspModels[1]],
            ['nom' => 'Preservar, conservar e recuperar bacias hidrográficas, especialmente aquelas em situação de vulnerabilidade', 'persp' => $perspModels[1]],
            ['nom' => 'Ampliar a área e a produtividade da agricultura irrigada para o desenvolvimento regional, observando o uso racional dos recursos naturais', 'persp' => $perspModels[1]],
            ['nom' => 'Ampliar a segurança hídrica e a resiliência à mudança do clima por meio da implantação, recuperação e manutenção da infraestrutura hídrica, em bases sustentáveis, especialmente nas regiões em situação crítica', 'persp' => $perspModels[1]],
            ['nom' => 'Aprimorar os usos múltiplos da água e os serviços hídricos, observando a eficiência e a sustentabilidade, ampliando o conhecimento sobre recursos hídricos, minimizando os riscos e as ocorrências de conflitos', 'persp' => $perspModels[1]],

            // Parceria e Fomento
            ['nom' => 'Aprimorar a aplicação e o acesso aos instrumentos de fomento ao desenvolvimento regional', 'persp' => $perspModels[2]],
            ['nom' => 'Ampliar investimento privado para implementação de infraestrutura e prestação de serviços com foco no desenvolvimento regional, por meio de parcerias privadas', 'persp' => $perspModels[2]],

            // Governança e Gestão Corporativa
            ['nom' => 'Consolidar um modelo de governança e gestão estratégica pautado pela integração, inovação e participação social, com foco em resultados', 'persp' => $perspModels[3]],
            ['nom' => 'Alcançar alto nível de qualidade, de inovação e de segurança das soluções de TIC, providas de forma tempestiva e alinhadas às prioridades organizacionais', 'persp' => $perspModels[3]],
            ['nom' => 'Promover a excelência em contratações públicas e gestão de serviços internos', 'persp' => $perspModels[3]],
            ['nom' => 'Garantir a comunicação e a interação do MIDR com os públicos interno e externo, priorizando o uso de ferramentas digitais de comunicação e de linguagem acessível e inclusiva', 'persp' => $perspModels[3]],
            ['nom' => 'Efetivar a plena execução orçamentária das ações finalísticas', 'persp' => $perspModels[3]],
            ['nom' => 'Promover o desenvolvimento de competências e a valorização da força de trabalho, com foco no desempenho institucional e na melhoria do clima organizacional', 'persp' => $perspModels[3]],
        ];

        foreach ($objetivos as $obj) {
            Objetivo::updateOrCreate(
                ['nom_objetivo' => $obj['nom'], 'cod_perspectiva' => $obj['persp']->cod_perspectiva],
                [
                    'dsc_objetivo' => $obj['nom'],
                    'num_nivel_hierarquico_apresentacao' => 1,
                ]
            );
        }
    }
}