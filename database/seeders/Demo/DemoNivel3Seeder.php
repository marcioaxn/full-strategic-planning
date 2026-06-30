<?php

namespace Database\Seeders\Demo;

use App\Models\Organization;
use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\AnaliseAmbiental;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\Valor;
use Illuminate\Database\Seeder;

/**
 * Nível 3 — Demo completa (95% automático, passeio guiado).
 *
 * Popula tudo: PEI, identidade, perspectivas, graus, objetivos, PESTEL, riscos.
 * O apresentador faz apenas o passeio guiado pela interface sem precisar digitar nada.
 */
class DemoNivel3Seeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::whereNull('rel_cod_organizacao')
            ->orWhereColumn('rel_cod_organizacao', 'cod_organizacao')
            ->first();

        if (! $org) {
            $this->command->error('Nenhuma organização raiz encontrada. Verifique o cadastro de organizações.');
            return;
        }

        // ─────────────────────────────────────────────
        // 1. PEI
        // ─────────────────────────────────────────────
        $pei = PEI::create([
            'dsc_pei'            => 'PEI 2025–2028 — Planejamento Estratégico Integrado',
            'num_ano_inicio_pei' => 2025,
            'num_ano_fim_pei'    => 2028,
        ]);

        // ─────────────────────────────────────────────
        // 2. Identidade Estratégica
        // ─────────────────────────────────────────────
        MissaoVisaoValores::create([
            'cod_pei'         => $pei->cod_pei,
            'cod_organizacao' => $org->cod_organizacao,
            'dsc_negocio'     => 'Formulação, coordenação e monitoramento de políticas públicas de desenvolvimento regional e urbano.',
            'dsc_missao'      => 'Formular, coordenar e monitorar as políticas públicas de desenvolvimento regional e urbano, promovendo a redução das desigualdades territoriais e a melhoria da qualidade de vida da população brasileira.',
            'dsc_visao'       => 'Ser reconhecido como referência nacional em governança territorial e gestão estratégica, contribuindo para um Brasil mais equilibrado, sustentável e inovador até 2028.',
        ]);

        // ─────────────────────────────────────────────
        // 3. Valores
        // ─────────────────────────────────────────────
        $valores = [
            ['nom_valor' => 'Excelência',    'dsc_valor' => 'Comprometimento com a qualidade, a eficiência e a melhoria contínua dos serviços e processos institucionais.'],
            ['nom_valor' => 'Transparência', 'dsc_valor' => 'Clareza e abertura nas ações, decisões e resultados, promovendo o controle social e a confiança pública.'],
            ['nom_valor' => 'Colaboração',   'dsc_valor' => 'Atuação integrada entre equipes, parceiros e cidadãos, valorizando a diversidade e a inteligência coletiva.'],
        ];

        foreach ($valores as $v) {
            Valor::create([
                'cod_pei'         => $pei->cod_pei,
                'cod_organizacao' => $org->cod_organizacao,
                'nom_valor'       => $v['nom_valor'],
                'dsc_valor'       => $v['dsc_valor'],
            ]);
        }

        // ─────────────────────────────────────────────
        // 4. Perspectivas BSC
        // ─────────────────────────────────────────────
        $perspectivas = [
            ['dsc_perspectiva' => 'Resultados para a Sociedade', 'num_nivel' => 1, 'peso_ind' => 60, 'peso_planos' => 40],
            ['dsc_perspectiva' => 'Processos Internos',           'num_nivel' => 2, 'peso_ind' => 60, 'peso_planos' => 40],
            ['dsc_perspectiva' => 'Aprendizado e Crescimento',    'num_nivel' => 3, 'peso_ind' => 50, 'peso_planos' => 50],
            ['dsc_perspectiva' => 'Recursos e Infraestrutura',    'num_nivel' => 4, 'peso_ind' => 50, 'peso_planos' => 50],
        ];

        $perspCriadas = [];
        foreach ($perspectivas as $p) {
            $perspCriadas[$p['dsc_perspectiva']] = Perspectiva::create([
                'cod_pei'                            => $pei->cod_pei,
                'dsc_perspectiva'                    => $p['dsc_perspectiva'],
                'num_nivel_hierarquico_apresentacao' => $p['num_nivel'],
                'num_peso_indicadores'               => $p['peso_ind'],
                'num_peso_planos'                    => $p['peso_planos'],
            ]);
        }

        // ─────────────────────────────────────────────
        // 5. Graus de Satisfação (farois do Mapa)
        // ─────────────────────────────────────────────
        $graus = [
            ['dsc_grau_satisfacao' => 'Abaixo do Esperado', 'vlr_minimo' => 0,     'vlr_maximo' => 50,   'cor' => '#dc2626'],
            ['dsc_grau_satisfacao' => 'Em Desenvolvimento', 'vlr_minimo' => 50.01, 'vlr_maximo' => 79.99,'cor' => '#f59e0b'],
            ['dsc_grau_satisfacao' => 'Satisfatório',       'vlr_minimo' => 80,    'vlr_maximo' => 100,  'cor' => '#16a34a'],
        ];

        foreach ($graus as $g) {
            GrauSatisfacao::create([
                'cod_pei'             => $pei->cod_pei,
                'dsc_grau_satisfacao' => $g['dsc_grau_satisfacao'],
                'vlr_minimo'          => $g['vlr_minimo'],
                'vlr_maximo'          => $g['vlr_maximo'],
                'cor'                 => $g['cor'],
            ]);
        }

        // ─────────────────────────────────────────────
        // 6. Objetivos Estratégicos (2 por perspectiva)
        // ─────────────────────────────────────────────
        $objetivosConfig = [
            'Resultados para a Sociedade' => [
                ['nom_objetivo' => 'Ampliar a entrega de valor à sociedade',    'dsc_objetivo' => 'Aumentar o alcance e o impacto das políticas públicas desenvolvidas pelo ministério, priorizando as populações em situação de vulnerabilidade.'],
                ['nom_objetivo' => 'Fortalecer a transparência institucional',  'dsc_objetivo' => 'Garantir o acesso da sociedade às informações sobre ações, resultados e uso de recursos públicos, em conformidade com a Lei de Acesso à Informação.'],
            ],
            'Processos Internos' => [
                ['nom_objetivo' => 'Otimizar os processos finalísticos',        'dsc_objetivo' => 'Revisar, simplificar e automatizar os processos-chave para reduzir o tempo de entrega e aumentar a qualidade dos serviços prestados.'],
                ['nom_objetivo' => 'Garantir a qualidade dos serviços prestados','dsc_objetivo' => 'Implementar sistemas de monitoramento e avaliação contínua da qualidade, com base em indicadores de satisfação do usuário e conformidade normativa.'],
            ],
            'Aprendizado e Crescimento' => [
                ['nom_objetivo' => 'Desenvolver as competências da equipe',     'dsc_objetivo' => 'Estruturar um programa de capacitação contínua alinhado às necessidades estratégicas do ministério e às novas demandas da gestão pública.'],
                ['nom_objetivo' => 'Fomentar a cultura de inovação',            'dsc_objetivo' => 'Criar um ambiente institucional que valorize a experimentação, a aprendizagem com erros e a adoção de soluções criativas para os desafios públicos.'],
            ],
            'Recursos e Infraestrutura' => [
                ['nom_objetivo' => 'Assegurar os recursos orçamentários',       'dsc_objetivo' => 'Garantir o planejamento financeiro adequado para a execução das ações estratégicas, com foco na eficiência do gasto e na captação de recursos complementares.'],
                ['nom_objetivo' => 'Modernizar a infraestrutura tecnológica',   'dsc_objetivo' => 'Investir na atualização dos sistemas de informação, plataformas digitais e segurança da informação para suportar a transformação digital do ministério.'],
            ],
        ];

        $objetivosCriados = [];
        foreach ($objetivosConfig as $nomePerspectiva => $objetivos) {
            $perspectiva = $perspCriadas[$nomePerspectiva];
            foreach ($objetivos as $ordem => $obj) {
                $objetivo = Objetivo::create([
                    'nom_objetivo'                       => $obj['nom_objetivo'],
                    'dsc_objetivo'                       => $obj['dsc_objetivo'],
                    'num_nivel_hierarquico_apresentacao' => $ordem + 1,
                    'cod_perspectiva'                    => $perspectiva->cod_perspectiva,
                ]);
                $objetivosCriados[] = $objetivo;
            }
        }

        // ─────────────────────────────────────────────
        // 7. PESTEL (1 fator por dimensão)
        // ─────────────────────────────────────────────
        $fatoresPestel = [
            [
                'dsc_categoria' => AnaliseAmbiental::PESTEL_POLITICO,
                'dsc_item'      => 'Mudanças na agenda de governo',
                'txt_observacao'=> 'Transições de governo e alterações na prioridade política podem impactar a continuidade dos programas e a alocação de recursos.',
                'num_impacto'   => 4,
                'num_gravidade' => 4,
                'num_urgencia'  => 3,
                'num_tendencia' => 3,
            ],
            [
                'dsc_categoria' => AnaliseAmbiental::PESTEL_ECONOMICO,
                'dsc_item'      => 'Restrições orçamentárias',
                'txt_observacao'=> 'O cenário fiscal restritivo exige maior eficiência na aplicação dos recursos e pode limitar a execução de novos projetos estratégicos.',
                'num_impacto'   => 5,
                'num_gravidade' => 5,
                'num_urgencia'  => 4,
                'num_tendencia' => 4,
            ],
            [
                'dsc_categoria' => AnaliseAmbiental::PESTEL_SOCIAL,
                'dsc_item'      => 'Demandas crescentes da sociedade',
                'txt_observacao'=> 'A população exige cada vez mais serviços públicos de qualidade, com rapidez, transparência e foco em resultados concretos.',
                'num_impacto'   => 4,
                'num_gravidade' => 3,
                'num_urgencia'  => 4,
                'num_tendencia' => 5,
            ],
            [
                'dsc_categoria' => AnaliseAmbiental::PESTEL_TECNOLOGICO,
                'dsc_item'      => 'Transformação digital acelerada',
                'txt_observacao'=> 'A digitalização dos serviços públicos e o uso de dados e IA representam oportunidades para aumentar a eficiência e melhorar a experiência do cidadão.',
                'num_impacto'   => 5,
                'num_gravidade' => 4,
                'num_urgencia'  => 4,
                'num_tendencia' => 5,
            ],
            [
                'dsc_categoria' => AnaliseAmbiental::PESTEL_AMBIENTAL,
                'dsc_item'      => 'Compromissos de sustentabilidade',
                'txt_observacao'=> 'As metas climáticas e os Objetivos de Desenvolvimento Sustentável impõem critérios ambientais às políticas públicas e ao planejamento institucional.',
                'num_impacto'   => 3,
                'num_gravidade' => 3,
                'num_urgencia'  => 3,
                'num_tendencia' => 4,
            ],
            [
                'dsc_categoria' => AnaliseAmbiental::PESTEL_LEGAL,
                'dsc_item'      => 'Novas normativas de governança pública',
                'txt_observacao'=> 'A evolução do marco regulatório de governança, controle interno e transparência exige adaptação contínua dos processos e sistemas institucionais.',
                'num_impacto'   => 4,
                'num_gravidade' => 3,
                'num_urgencia'  => 3,
                'num_tendencia' => 3,
            ],
        ];

        foreach ($fatoresPestel as $ordem => $fator) {
            AnaliseAmbiental::create([
                'cod_pei'          => $pei->cod_pei,
                'cod_organizacao'  => $org->cod_organizacao,
                'dsc_tipo_analise' => AnaliseAmbiental::TIPO_PESTEL,
                'dsc_categoria'    => $fator['dsc_categoria'],
                'dsc_item'         => $fator['dsc_item'],
                'txt_observacao'   => $fator['txt_observacao'],
                'num_impacto'      => $fator['num_impacto'],
                'num_gravidade'    => $fator['num_gravidade'],
                'num_urgencia'     => $fator['num_urgencia'],
                'num_tendencia'    => $fator['num_tendencia'],
                'num_ordem'        => $ordem + 1,
            ]);
        }

        // ─────────────────────────────────────────────
        // 8. Riscos (4 com níveis diferentes — para a Matriz)
        // ─────────────────────────────────────────────
        $riscos = [
            [
                'dsc_titulo'               => 'Contingenciamento orçamentário',
                'txt_descricao'            => 'Corte de recursos previstos no orçamento anual, inviabilizando a execução de ações e projetos estratégicos prioritários.',
                'dsc_categoria'            => 'Financeiro',
                'dsc_status'              => 'Ativo',
                'num_probabilidade'        => 4,
                'num_impacto'             => 5,
                'txt_causas'              => 'Deterioração do cenário fiscal, revisões da LDO/LOA, priorização de outras áreas pelo governo federal.',
                'txt_consequencias'        => 'Paralisação de contratos, descontinuidade de programas e perda de credibilidade institucional.',
                'dsc_estrategia_resposta'  => 'Mitigar',
                'txt_justificativa_estrategia' => 'Diversificação de fontes de financiamento e priorização das ações de maior impacto estratégico.',
            ],
            [
                'dsc_titulo'               => 'Rotatividade de pessoal-chave',
                'txt_descricao'            => 'Saída de servidores e colaboradores com conhecimento crítico para a execução das ações estratégicas.',
                'dsc_categoria'            => 'Pessoas',
                'dsc_status'              => 'Ativo',
                'num_probabilidade'        => 3,
                'num_impacto'             => 4,
                'txt_causas'              => 'Aposentadorias, transferências, disputas salariais com o mercado e ausência de plano de sucessão.',
                'txt_consequencias'        => 'Perda de memória institucional, atrasos em projetos e aumento do tempo de aprendizagem da equipe substituta.',
                'dsc_estrategia_resposta'  => 'Mitigar',
                'txt_justificativa_estrategia' => 'Implementação de plano de sucessão e gestão do conhecimento institucional.',
            ],
            [
                'dsc_titulo'               => 'Falha em sistema de tecnologia da informação',
                'txt_descricao'            => 'Indisponibilidade ou comprometimento de sistemas críticos de TI que suportam os processos finalísticos.',
                'dsc_categoria'            => 'Tecnológico',
                'dsc_status'              => 'Monitorado',
                'num_probabilidade'        => 2,
                'num_impacto'             => 3,
                'txt_causas'              => 'Infraestrutura defasada, vulnerabilidades de segurança, falta de redundância e backups inadequados.',
                'txt_consequencias'        => 'Interrupção na prestação de serviços, exposição de dados e perda de produtividade operacional.',
                'dsc_estrategia_resposta'  => 'Transferir',
                'txt_justificativa_estrategia' => 'Contratação de serviços de TI com SLA e cobertura de contingência contratual.',
            ],
            [
                'dsc_titulo'               => 'Mudança de prioridade política',
                'txt_descricao'            => 'Reorientação da agenda governamental que impacte o alinhamento estratégico e a continuidade dos programas.',
                'dsc_categoria'            => 'Político',
                'dsc_status'              => 'Monitorado',
                'num_probabilidade'        => 2,
                'num_impacto'             => 2,
                'txt_causas'              => 'Eleições, reformas ministeriais, mudança de titulares e reordenamento das prioridades de governo.',
                'txt_consequencias'        => 'Necessidade de revisão do PEI, reprogramação de ações e possível descontinuidade de projetos em andamento.',
                'dsc_estrategia_resposta'  => 'Aceitar',
                'txt_justificativa_estrategia' => 'Risco inerente ao ambiente político. Monitoramento contínuo com revisão do PEI quando necessário.',
            ],
        ];

        foreach ($riscos as $r) {
            Risco::create([
                'cod_pei'                      => $pei->cod_pei,
                'cod_organizacao'              => $org->cod_organizacao,
                'dsc_titulo'                   => $r['dsc_titulo'],
                'txt_descricao'                => $r['txt_descricao'],
                'dsc_categoria'                => $r['dsc_categoria'],
                'dsc_status'                   => $r['dsc_status'],
                'num_probabilidade'            => $r['num_probabilidade'],
                'num_impacto'                  => $r['num_impacto'],
                'txt_causas'                   => $r['txt_causas'],
                'txt_consequencias'            => $r['txt_consequencias'],
                'dsc_estrategia_resposta'      => $r['dsc_estrategia_resposta'],
                'txt_justificativa_estrategia' => $r['txt_justificativa_estrategia'],
            ]);
        }
    }
}
