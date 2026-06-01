<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\InauguraPei;
use App\Models\StrategicPlanning\IntegracaoInstrumento;
use App\Models\StrategicPlanning\CalendarioEventoPei;
use App\Models\StrategicPlanning\ParteInteressada;
use App\Models\StrategicPlanning\CenarioProspectivo;
use App\Models\StrategicPlanning\Rae;
use App\Models\StrategicPlanning\AtividadeCadeiaValor;
use App\Models\StrategicPlanning\ProcessoAtividadeCadeiaValor;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\LicaoAprendida;
use App\Models\ActionPlan\Raci;
use App\Models\ActionPlan\PlanoComunicacao;
use App\Models\Organization;
use App\Models\User;
use App\Models\Agenda2030\ODS;

/**
 * Popula os módulos GPPEI adicionados após o núcleo (Agenda 2030, Inaugurar e
 * Integrar, Cadeia de Valor, Partes Interessadas, Cenários, RAE, Lições, RACI,
 * Modelo Lógico e Plano de Comunicação), reaproveitando o ambiente MIDR
 * (PEI, organização, objetivos, planos, entregas e usuários) já criado.
 *
 * Idempotente: re-execução não duplica registros. Deve rodar APÓS os seeders
 * MIDR* e o OdsSeeder.
 */
class MIDRModulosGppeiSeeder extends Seeder
{
    public function run(): void
    {
        $pei = PEI::ativos()->first() ?? PEI::first();
        if (!$pei) {
            $this->command?->warn('Nenhum PEI encontrado. Rode os seeders MIDR primeiro.');
            return;
        }

        $org = Organization::where('sgl_organizacao', 'MIDR')->first() ?? Organization::first();
        $codOrg = $org?->cod_organizacao;

        // Garante os 17+1 ODS de referência
        if (ODS::count() === 0) {
            $this->call(OdsSeeder::class);
        }

        $this->seedAgenda2030($pei);
        $this->seedInaugurarIntegrar($pei);
        $this->seedCadeiaValor($pei);
        $this->seedPartesInteressadas($pei);
        $this->seedCenarios($pei, $codOrg);
        $this->seedRae($pei, $codOrg);
        $this->seedExecucaoPlanos();   // Modelo Lógico, RACI, Comunicação, Lições

        $this->command?->info('Módulos GPPEI (novos) populados com sucesso.');
    }

    /* ─────────────── Agenda 2030 (ODS) ─────────────── */
    private function seedAgenda2030(PEI $pei): void
    {
        // Aderência institucional do PEI (rel_pei_ods)
        $aderencia = [
            1  => ['txt_contribuicao' => 'Programas de desenvolvimento regional e redução de pobreza.', 'dsc_intensidade' => 'Alta'],
            8  => ['txt_contribuicao' => 'Geração de emprego e crescimento econômico regional.',       'dsc_intensidade' => 'Alta'],
            9  => ['txt_contribuicao' => 'Infraestrutura e inovação para o desenvolvimento territorial.','dsc_intensidade' => 'Alta'],
            10 => ['txt_contribuicao' => 'Redução das desigualdades regionais.',                        'dsc_intensidade' => 'Alta'],
            11 => ['txt_contribuicao' => 'Cidades e comunidades sustentáveis.',                         'dsc_intensidade' => 'Média'],
            13 => ['txt_contribuicao' => 'Ações de defesa civil e adaptação climática.',                'dsc_intensidade' => 'Média'],
            16 => ['txt_contribuicao' => 'Fortalecimento institucional e governança.',                  'dsc_intensidade' => 'Média'],
            17 => ['txt_contribuicao' => 'Parcerias federativas e meios de implementação.',             'dsc_intensidade' => 'Alta'],
        ];
        try {
            $pei->ods()->syncWithoutDetaching($aderencia);
        } catch (\Throwable $e) {
            $this->command?->warn('Aderência PEI↔ODS não aplicada: '.$e->getMessage());
        }

        // Vínculo granular Objetivo↔ODS — distribui ODS variados pelos objetivos
        $pools = [[1, 10], [8, 9], [11, 13], [16, 17], [3, 4], [6, 11], [9, 17], [10, 16]];
        $objetivos = Objetivo::whereHas('perspectiva', fn($q) => $q->where('cod_pei', $pei->cod_pei))->get();
        foreach ($objetivos as $i => $obj) {
            $par = $pools[$i % count($pools)];
            $sync = [];
            foreach ($par as $num) {
                $sync[$num] = ['txt_contribuicao' => 'Este objetivo contribui diretamente para o ODS '.$num.'.'];
            }
            try {
                $obj->ods()->syncWithoutDetaching($sync);
            } catch (\Throwable $e) {
                // segue
            }
        }
    }

    /* ─────────────── Módulo 01 — Inaugurar e Integrar ─────────────── */
    private function seedInaugurarIntegrar(PEI $pei): void
    {
        InauguraPei::firstOrCreate(
            ['cod_pei' => $pei->cod_pei],
            [
                'txt_equipe'          => 'Comitê de Planejamento Estratégico, Assessoria de Planejamento (ASPLAN) e representantes das Secretarias finalísticas.',
                'txt_diretrizes'      => 'Alinhamento ao PPA 2024-2027, foco em resultados e participação das unidades regionais.',
                'txt_metodologia'     => 'GPPEI/MGI 2025 combinada ao Balanced Scorecard (BSC), com workshops presenciais e validação pela Alta Direção.',
                'txt_observacoes'     => 'Processo conduzido em 4 oficinas temáticas ao longo do 1º semestre.',
                'dte_inicio_processo' => now()->subMonths(6)->toDateString(),
                'dte_fim_previsto'    => now()->addMonths(2)->toDateString(),
                'bln_aprovado'        => true,
            ]
        );

        $instrumentos = [
            ['dsc_instrumento' => 'PPA 2024-2027',      'dsc_tipo_instrumento' => 'PPA',            'dsc_intensidade' => 'Alta',  'txt_pontos_atencao' => 'Vincular objetivos aos programas finalísticos do PPA.', 'txt_tarefas' => 'Mapear marcadores ODS por programa.', 'num_ordem' => 1],
            ['dsc_instrumento' => 'LOA 2026',           'dsc_tipo_instrumento' => 'LOA',            'dsc_intensidade' => 'Média', 'txt_pontos_atencao' => 'Compatibilizar metas com a dotação orçamentária anual.', 'txt_tarefas' => 'Conferir ações orçamentárias por plano.', 'num_ordem' => 2],
            ['dsc_instrumento' => 'Plano Nacional de Desenvolvimento Regional', 'dsc_tipo_instrumento' => 'Plano Setorial', 'dsc_intensidade' => 'Alta', 'txt_pontos_atencao' => 'Coerência com a PNDR.', 'txt_tarefas' => 'Alinhar indicadores regionais.', 'num_ordem' => 3],
        ];
        foreach ($instrumentos as $ins) {
            IntegracaoInstrumento::firstOrCreate(
                ['cod_pei' => $pei->cod_pei, 'dsc_instrumento' => $ins['dsc_instrumento']],
                array_merge($ins, ['cod_pei' => $pei->cod_pei])
            );
        }

        $eventos = [
            ['dsc_titulo' => 'Oficina de Diagnóstico Estratégico',  'dsc_tipo_evento' => 'Workshop', 'dias' => -150, 'realizado' => true,  'obj' => 'Levantar forças, fraquezas e cenários.'],
            ['dsc_titulo' => 'Validação da Identidade Estratégica', 'dsc_tipo_evento' => 'Reunião',  'dias' => -120, 'realizado' => true,  'obj' => 'Aprovar missão, visão e valores.'],
            ['dsc_titulo' => 'Pactuação de Indicadores e Metas',    'dsc_tipo_evento' => 'Workshop', 'dias' => -60,  'realizado' => true,  'obj' => 'Definir KPIs e metas anuais.'],
            ['dsc_titulo' => 'RAE — 1ª Reunião de Avaliação',       'dsc_tipo_evento' => 'Reunião',  'dias' => 30,   'realizado' => false, 'obj' => 'Avaliar o progresso do ciclo.'],
        ];
        foreach ($eventos as $ev) {
            CalendarioEventoPei::firstOrCreate(
                ['cod_pei' => $pei->cod_pei, 'dsc_titulo' => $ev['dsc_titulo']],
                [
                    'cod_pei'           => $pei->cod_pei,
                    'dsc_objetivo'      => $ev['obj'],
                    'dte_evento'        => now()->addDays($ev['dias'])->toDateString(),
                    'dsc_participantes' => 'Alta Direção, ASPLAN e gestores das unidades.',
                    'dsc_tipo_evento'   => $ev['dsc_tipo_evento'],
                    'bln_realizado'     => $ev['realizado'],
                ]
            );
        }
    }

    /* ─────────────── Cadeia de Valor ─────────────── */
    private function seedCadeiaValor(PEI $pei): void
    {
        $perspectivas = Perspectiva::where('cod_pei', $pei->cod_pei)->ordenadoPorNivel()->get();
        if ($perspectivas->isEmpty()) {
            $this->command?->warn('Sem perspectivas — Cadeia de Valor não populada.');
            return;
        }

        $atividades = [
            ['dsc_atividade' => 'Desenvolvimento Regional Sustentável', 'dsc_tipo' => 'Finalística', 'num_ordem' => 1, 'proc' => ['Demandas regionais', 'Formulação e execução de políticas e programas', 'Territórios desenvolvidos']],
            ['dsc_atividade' => 'Gestão de Riscos e Desastres',          'dsc_tipo' => 'Finalística', 'num_ordem' => 2, 'proc' => ['Alertas e ocorrências', 'Coordenação da defesa civil', 'Resposta e reconstrução']],
            ['dsc_atividade' => 'Gestão de Recursos Hídricos',           'dsc_tipo' => 'Finalística', 'num_ordem' => 3, 'proc' => ['Demandas hídricas', 'Infraestrutura e segurança hídrica', 'Acesso à água']],
            ['dsc_atividade' => 'Gestão de Pessoas',                     'dsc_tipo' => 'Suporte',     'num_ordem' => 1, 'proc' => ['Necessidade de capacitação', 'Desenvolvimento de competências', 'Servidores capacitados']],
            ['dsc_atividade' => 'Tecnologia da Informação',              'dsc_tipo' => 'Suporte',     'num_ordem' => 2, 'proc' => ['Demandas de TI', 'Provisão de sistemas e infraestrutura', 'Serviços digitais disponíveis']],
        ];
        foreach ($atividades as $i => $a) {
            $perspectiva = $perspectivas[$i % $perspectivas->count()];
            $atividade = AtividadeCadeiaValor::firstOrCreate(
                ['cod_pei' => $pei->cod_pei, 'dsc_atividade' => $a['dsc_atividade']],
                ['cod_pei' => $pei->cod_pei, 'cod_perspectiva' => $perspectiva->cod_perspectiva, 'dsc_tipo' => $a['dsc_tipo'], 'num_ordem' => $a['num_ordem']]
            );
            ProcessoAtividadeCadeiaValor::firstOrCreate(
                ['cod_atividade_cadeia_valor' => $atividade->getKey(), 'dsc_transformacao' => $a['proc'][1]],
                [
                    'cod_atividade_cadeia_valor' => $atividade->getKey(),
                    'dsc_entrada'       => $a['proc'][0],
                    'dsc_transformacao' => $a['proc'][1],
                    'dsc_saida'         => $a['proc'][2],
                ]
            );
        }
    }

    /* ─────────────── Partes Interessadas ─────────────── */
    private function seedPartesInteressadas(PEI $pei): void
    {
        $partes = [
            ['nom_parte' => 'Governadores e Estados',        'dsc_tipo' => 'Governo',          'num_interesse' => 5, 'num_influencia' => 5, 'eng' => 'Pactuação federativa e comitês regionais.'],
            ['nom_parte' => 'Cidadãos das regiões atendidas','dsc_tipo' => 'Sociedade Civil',  'num_interesse' => 5, 'num_influencia' => 2, 'eng' => 'Canais de transparência e ouvidoria.'],
            ['nom_parte' => 'Congresso Nacional',            'dsc_tipo' => 'Governo',          'num_interesse' => 4, 'num_influencia' => 5, 'eng' => 'Prestação de contas e articulação legislativa.'],
            ['nom_parte' => 'Órgãos de Controle (TCU/CGU)',  'dsc_tipo' => 'Controle',         'num_interesse' => 3, 'num_influencia' => 4, 'eng' => 'Conformidade e atendimento a recomendações.'],
            ['nom_parte' => 'Municípios',                    'dsc_tipo' => 'Parceiro',         'num_interesse' => 5, 'num_influencia' => 3, 'eng' => 'Convênios e assistência técnica.'],
        ];
        foreach ($partes as $i => $p) {
            ParteInteressada::firstOrCreate(
                ['cod_pei' => $pei->cod_pei, 'nom_parte' => $p['nom_parte']],
                [
                    'cod_pei'                     => $pei->cod_pei,
                    'dsc_tipo'                    => $p['dsc_tipo'],
                    'num_interesse'               => $p['num_interesse'],
                    'num_influencia'              => $p['num_influencia'],
                    'txt_estrategia_engajamento'  => $p['eng'],
                    'num_ordem'                   => $i + 1,
                ]
            );
        }
    }

    /* ─────────────── Cenários Prospectivos ─────────────── */
    private function seedCenarios(PEI $pei, ?string $codOrg): void
    {
        $cenarios = [
            ['nom_cenario' => 'Expansão do Investimento Regional', 'dsc_tipo' => 'Otimista',   'prob' => 30, 'imp' => 5, 'desc' => 'Aumento sustentado de recursos e parcerias.', 'imp_txt' => 'Aceleração das entregas e ampliação de cobertura.', 'resp' => 'Escalar programas de maior impacto.'],
            ['nom_cenario' => 'Continuidade Orçamentária',         'dsc_tipo' => 'Tendencial', 'prob' => 50, 'imp' => 3, 'desc' => 'Manutenção do patamar atual de recursos.', 'imp_txt' => 'Execução conforme planejado, sem grandes saltos.', 'resp' => 'Priorizar eficiência e foco.'],
            ['nom_cenario' => 'Restrição Fiscal Severa',           'dsc_tipo' => 'Pessimista', 'prob' => 20, 'imp' => 5, 'desc' => 'Contingenciamento e cortes orçamentários.', 'imp_txt' => 'Atrasos e repriorização de planos.', 'resp' => 'Proteger entregas críticas e buscar parcerias.'],
        ];
        foreach ($cenarios as $i => $c) {
            CenarioProspectivo::firstOrCreate(
                ['cod_pei' => $pei->cod_pei, 'nom_cenario' => $c['nom_cenario']],
                [
                    'cod_pei'                 => $pei->cod_pei,
                    'cod_organizacao'         => $codOrg,
                    'dsc_tipo'                => $c['dsc_tipo'],
                    'dsc_descricao'           => $c['desc'],
                    'txt_implicacoes'         => $c['imp_txt'],
                    'txt_resposta_estrategica'=> $c['resp'],
                    'num_probabilidade'       => $c['prob'],
                    'num_impacto'             => $c['imp'],
                    'num_ordem'               => $i + 1,
                ]
            );
        }
    }

    /* ─────────────── RAE — Revisão e Avaliação da Estratégia ─────────────── */
    private function seedRae(PEI $pei, ?string $codOrg): void
    {
        $raes = [
            ['ref' => now()->subMonths(6), 'reuniao' => now()->subMonths(6)->addDays(5), 'tipo' => 'Ordinária', 'prog' => 42.0,
             'pos' => 'Identidade aprovada e indicadores pactuados no prazo.',
             'prob' => 'Atraso na contratação de alguns planos por restrição orçamentária.',
             'enc'  => 'Repriorizar entregas do 2º semestre e reforçar monitoramento mensal.'],
            ['ref' => now()->subMonths(3), 'reuniao' => now()->subMonths(3)->addDays(5), 'tipo' => 'Ordinária', 'prog' => 63.5,
             'pos' => 'Avanço consistente nas perspectivas de processos e sociedade.',
             'prob' => 'Dois riscos críticos exigem plano de mitigação imediato.',
             'enc'  => 'Acelerar mitigações e revisar metas dos KPIs em alerta.'],
        ];
        foreach ($raes as $r) {
            Rae::firstOrCreate(
                ['cod_pei' => $pei->cod_pei, 'dte_referencia' => $r['ref']->toDateString()],
                [
                    'cod_pei'                    => $pei->cod_pei,
                    'cod_organizacao'            => $codOrg,
                    'dte_reuniao'                => $r['reuniao']->toDateString(),
                    'dsc_tipo_reuniao'           => $r['tipo'],
                    'txt_destaques_positivos'    => $r['pos'],
                    'txt_problemas_identificados'=> $r['prob'],
                    'txt_encaminhamentos'        => $r['enc'],
                    'json_participantes'         => ['Alta Direção', 'ASPLAN', 'Gestores de Plano'],
                    'num_progresso_geral'        => $r['prog'],
                ]
            );
        }
    }

    /* ─────────────── Execução: Modelo Lógico, RACI, Comunicação, Lições ─────────────── */
    private function seedExecucaoPlanos(): void
    {
        $usuarios = User::take(6)->get();
        $planos = PlanoDeAcao::with('entregas')->take(6)->get();
        // tab_raci.dsc_papel é char(1): armazena a inicial R/A/C/I
        $papeis = ['R', 'A', 'C', 'I'];

        foreach ($planos as $idx => $plano) {
            // Modelo Lógico
            if (empty($plano->json_modelo_logico)) {
                $plano->update(['json_modelo_logico' => [
                    'insumos'    => 'Orçamento, equipe técnica e parcerias institucionais.',
                    'atividades' => 'Execução das entregas planejadas e articulação com as unidades.',
                    'produtos'   => 'Entregas concluídas e marcos atingidos.',
                    'resultados' => 'Melhoria dos indicadores do objetivo vinculado.',
                    'impacto'    => 'Contribuição ao desenvolvimento regional sustentável.',
                ]]);
            }

            // RACI (por plano, ciclando usuários e a primeira entrega quando houver)
            if ($usuarios->isNotEmpty()) {
                $entregaId = $plano->entregas->first()?->getKey();
                foreach ($papeis as $p => $papel) {
                    $user = $usuarios[($idx + $p) % $usuarios->count()];
                    Raci::firstOrCreate(
                        ['cod_plano_de_acao' => $plano->cod_plano_de_acao, 'user_id' => $user->id, 'dsc_papel' => $papel],
                        ['cod_entrega' => $entregaId]
                    );
                }
            }

            // Plano de Comunicação
            $coms = [
                ['pub' => 'Alta Direção',        'msg' => 'Status executivo do plano e principais marcos.', 'canal' => 'Reunião',        'freq' => 'Mensal'],
                ['pub' => 'Equipe Executora',    'msg' => 'Orientações operacionais e prazos das entregas.', 'canal' => 'E-mail',         'freq' => 'Quinzenal'],
                ['pub' => 'Sociedade / Cidadão', 'msg' => 'Resultados e benefícios entregues à população.',  'canal' => 'Portal/Imprensa','freq' => 'Trimestral'],
            ];
            foreach ($coms as $o => $c) {
                PlanoComunicacao::firstOrCreate(
                    ['cod_plano_de_acao' => $plano->cod_plano_de_acao, 'nom_publico_alvo' => $c['pub']],
                    [
                        'dsc_mensagem_chave' => $c['msg'],
                        'dsc_canal'          => $c['canal'],
                        'dsc_frequencia'     => $c['freq'],
                        'nom_responsavel'    => 'Assessoria de Comunicação',
                        'num_ordem'          => $o + 1,
                    ]
                );
            }

            // Lições Aprendidas
            $licoes = [
                ['cat' => 'Gestão',     'tipo' => 'Aprendizado',    'desc' => 'O monitoramento mensal antecipou desvios e permitiu correção rápida.', 'rec' => 'Manter ritmo mensal de acompanhamento.'],
                ['cat' => 'Comunicação','tipo' => 'Boas Práticas',  'desc' => 'O envolvimento precoce das unidades regionais melhorou a adesão.',       'rec' => 'Engajar stakeholders desde o início.'],
            ];
            foreach ($licoes as $l => $lic) {
                LicaoAprendida::firstOrCreate(
                    ['cod_plano_de_acao' => $plano->cod_plano_de_acao, 'txt_descricao' => $lic['desc']],
                    [
                        'dsc_categoria'   => $lic['cat'],
                        'dsc_tipo'        => $lic['tipo'],
                        'txt_recomendacao'=> $lic['rec'],
                        'num_ordem'       => $l + 1,
                    ]
                );
            }
        }
    }
}
