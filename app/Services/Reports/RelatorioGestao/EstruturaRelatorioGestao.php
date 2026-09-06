<?php

namespace App\Services\Reports\RelatorioGestao;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\Organization;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\AnaliseAmbiental;
use App\Models\StrategicPlanning\AtividadeCadeiaValor;
use App\Models\StrategicPlanning\IdentidadeEstrategica;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\TemaNorteador;
use App\Models\StrategicPlanning\Valor;
use App\Models\SystemSetting;
use App\Support\UnidadeMedida;
use Illuminate\Support\Collection;

/**
 * Monta o Relatório de Gestão como DADO, não como HTML.
 *
 * Existe uma estrutura só, consumida por dois renderizadores (PDF e DOCX).
 * Sem isso, os dois formatos divergiriam na primeira manutenção — e o cliente
 * receberia um PDF e um Word que não dizem a mesma coisa.
 *
 * REFERÊNCIA
 * O modelo é o Relatório de Gestão 2025 da Presidência da República
 * (documentacao/relatorios/RelatriodeGesto2025PReVPR31mar.pdf, 198 páginas).
 * A numeração das seções, os títulos e os cabeçalhos de tabela abaixo seguem
 * o documento oficial — inclusive "Iniciativas", que é o termo que ele usa nas
 * tabelas 2.2.1 (p. 28) e 2.2.2 (p. 34-37).
 *
 * 🔴 O QUE O SISTEMA PREENCHE, E O QUE NÃO
 * Das 5 partes do modelo, este sistema é a fonte de:
 *   1.6 Cadeia de Valor · 1.8 Ambiente Externo · 2.1 Estratégia ·
 *   2.2 Resultados Alcançados · 4.1 Gestão de Riscos
 *
 * O restante vem de Tesouro Gerencial, SIAFI, SIAPE e Comprasnet — sistemas
 * de outra natureza, aos quais este produto não tem acesso. Por isso existem
 * DUAS variantes:
 *
 *   RÉPLICA — o esqueleto completo do modelo. As seções de fonte externa
 *             aparecem marcadas, dizendo QUAL sistema as alimenta. Serve como
 *             rascunho para a unidade completar no Word.
 *
 *   AUTORAL — só o que tem conteúdo. Nenhuma seção vazia, nenhuma marcação.
 *             É o documento pronto para apresentar.
 */
class EstruturaRelatorioGestao
{
    public const VARIANTE_REPLICA = 'replica';

    public const VARIANTE_AUTORAL = 'autoral';

    /** Origem de cada seção que este sistema NÃO alimenta. */
    private const FONTES_EXTERNAS = [
        '1.2' => 'Cadastro institucional da unidade (CNPJ, natureza jurídica, endereço)',
        '1.3' => 'Normativos internos do órgão',
        '1.4' => 'Comitês e colegiados de governança',
        '1.5' => 'Orçamento (SIOP), força de trabalho (SIAPE) e investimentos em TIC',
        '1.7' => 'SIOP — Sistema Integrado de Planejamento e Orçamento',
        '3.1' => 'Tesouro Gerencial',
        '3.2' => 'Sistema de Custos',
        '3.3' => 'SIAPE',
        '3.4' => 'Comprasnet / SIASG',
        '3.5' => 'Sistema de patrimônio',
        '3.6' => 'Plano Diretor de TIC',
        '3.7' => 'Relatório de sustentabilidade',
        '3.8' => 'Coordenação de visitação',
        '3.9' => 'Texto autoral da alta direção',
        '4.2' => 'Programa de integridade',
        '4.3' => 'Comissão de ética',
        '4.4' => 'Ouvidoria',
        '4.5' => 'Canais de atendimento',
        '4.6' => 'Corregedoria',
        '4.7' => 'Auditoria interna',
        '5.1' => 'Tesouro Gerencial / SIAFI',
        '5.2' => 'Tesouro Gerencial / SIAFI',
        '5.3' => 'Tesouro Gerencial / SIAFI',
        '5.4' => 'Tesouro Gerencial / SIAFI',
        '5.5' => 'Tesouro Gerencial / SIAFI',
        '5.6' => 'Tesouro Gerencial / SIAFI',
        '5.7' => 'Tesouro Gerencial / SIAFI',
        '5.8' => 'Tesouro Gerencial / SIAFI',
        '5.9' => 'Tesouro Gerencial / SIAFI',
        '5.10' => 'Tesouro Gerencial / SIAFI',
        '5.11' => 'Tesouro Gerencial / SIAFI',
        '5.12' => 'Tesouro Gerencial / SIAFI',
        '5.13' => 'Setorial contábil',
        '5.14' => 'Setorial contábil',
    ];

    public function __construct(
        private readonly string $variante = self::VARIANTE_AUTORAL
    ) {}

    /**
     * @return array{
     *   capa: array, organizacao: Organization|null, pei: PEI|null,
     *   ano: int, variante: string, capitulos: array<int, array>
     * }
     */
    public function montar(?string $organizacaoId, int $ano): array
    {
        $organizacao = $organizacaoId ? Organization::find($organizacaoId) : null;
        $pei = $this->cicloDoAno($ano);

        $capitulos = [
            $this->capitulo1($pei, $organizacao),
            $this->capitulo2($pei, $organizacao, $ano),
            $this->capitulo3(),
            $this->capitulo4($pei, $organizacao),
            $this->capitulo5(),
        ];

        // Na variante autoral, seção sem conteúdo simplesmente não existe.
        if ($this->variante === self::VARIANTE_AUTORAL) {
            $capitulos = $this->podar($capitulos);
        }

        return [
            'capa' => [
                'orgao' => $organizacao?->nom_organizacao ?? 'Organização',
                'sigla' => $organizacao?->sgl_organizacao ?? '',
                'titulo' => 'Relatório de Gestão',
                'ano' => $ano,
                'ciclo' => $pei?->dsc_pei,
                'emitido_em' => now()->format('d/m/Y'),

                /*
                 * Ativos visuais da CAPA e do rodapé — de cada cliente, nunca
                 * do modelo. O Relatório de Gestão da Presidência abre com uma
                 * foto do Palácio do Planalto e fecha com as redes sociais da
                 * PR; reproduzir isso no relatório de outro órgão seria
                 * falsificação institucional. O layout se copia; a identidade
                 * não. Sem imagem configurada, a capa sai no fundo sólido
                 * institucional.
                 */
                'site' => (string) SystemSetting::getValue('orgao_site', ''),
                'imagem' => $this->imagemDeCapa(),
                'credito_imagem' => (string) SystemSetting::getValue('relatorio_gestao_capa_credito', ''),
            ],
            'organizacao' => $organizacao,
            'pei' => $pei,
            'ano' => $ano,
            'variante' => $this->variante,
            'capitulos' => array_values($capitulos),
        ];
    }

    /**
     * Caminho absoluto da imagem de capa, se o órgão configurou uma.
     *
     * Devolve null — e não o caminho — quando o arquivo não existe mais: o
     * DomPDF desenharia um retângulo quebrado no lugar, e o cliente receberia
     * uma capa defeituosa sem entender por quê.
     */
    private function imagemDeCapa(): ?string
    {
        $relativo = (string) SystemSetting::getValue('relatorio_gestao_capa', '');

        if ($relativo === '') {
            return null;
        }

        // Confinado ao disco público do próprio sistema: nome de arquivo vindo
        // de configuração não pode virar leitura de caminho arbitrário.
        $base = realpath(storage_path('app/public'));
        $caminho = realpath(storage_path('app/public/'.$relativo));

        if (! $base || ! $caminho || ! str_starts_with($caminho, $base)) {
            return null;
        }

        return is_file($caminho) ? $caminho : null;
    }

    // ------------------------------------------------------------- capítulos

    private function capitulo1(?PEI $pei, ?Organization $org): array
    {
        return [
            'numero' => '1',
            'titulo' => 'Visão Geral Organizacional e Ambiente Externo',
            'secoes' => [
                $this->secao('1.1', 'Estrutura Organizacional', 'organograma', [
                    'unidades' => $org
                        ? Organization::where('rel_cod_organizacao', $org->cod_organizacao)
                            ->orderBy('nom_organizacao')->get()
                        : collect(),
                    'raiz' => $org,
                ]),
                $this->secaoExterna('1.2', 'Identificação da(s) Unidade(s)'),
                $this->secaoExterna('1.3', 'Principais Normas'),
                $this->secaoExterna('1.4', 'Estrutura de Governança'),
                $this->secaoExterna('1.5', 'Modelo de Negócios'),
                $this->secao('1.6', 'Cadeia de Valor', 'cadeia-valor', [
                    'grupos' => $this->cadeiaDeValor($pei),
                ]),
                $this->secaoExterna('1.7', 'Programas e ações orçamentárias'),
                $this->secao('1.8', 'Ambiente Externo', 'ambiente-externo', [
                    'swot' => $this->analiseAmbiental($pei, $org, 'SWOT'),
                    'pestel' => $this->analiseAmbiental($pei, $org, 'PESTEL'),
                ]),
            ],
        ];
    }

    private function capitulo2(?PEI $pei, ?Organization $org, int $ano): array
    {
        return [
            'numero' => '2',
            'titulo' => 'Resultados e Desempenho da Gestão',
            'secoes' => [
                $this->secao('2.1', 'Estratégia', 'estrategia', $this->estrategia($pei, $org)),
                $this->secao('2.2', 'Resultados Alcançados', 'resultados', [
                    'tabela_2_2_1' => $this->tabelaPlanejamentoIntegrado($pei, $org),
                    'tabela_2_2_2' => $this->tabelaResultados($pei, $org, $ano),
                ]),
                $this->secao('2.3', 'Grandes Números', 'grandes-numeros', [
                    'numeros' => $this->grandesNumeros($pei, $org, $ano),
                ]),
            ],
        ];
    }

    private function capitulo3(): array
    {
        return [
            'numero' => '3',
            'titulo' => 'Resultados e Desempenho da Gestão Administrativa',
            'secoes' => [
                $this->secaoExterna('3.1', 'Gestão Orçamentária e Financeira'),
                $this->secaoExterna('3.2', 'Gestão de Custos'),
                $this->secaoExterna('3.3', 'Gestão de Pessoas'),
                $this->secaoExterna('3.4', 'Gestão de Licitações e Contratos'),
                $this->secaoExterna('3.5', 'Gestão Patrimonial e Infraestrutura'),
                $this->secaoExterna('3.6', 'Gestão da Tecnologia da Informação'),
                $this->secaoExterna('3.7', 'Sustentabilidade Ambiental'),
                $this->secaoExterna('3.8', 'Visitação Pública'),
                $this->secaoExterna('3.9', 'Oportunidades e Perspectivas'),
            ],
        ];
    }

    private function capitulo4(?PEI $pei, ?Organization $org): array
    {
        return [
            'numero' => '4',
            'titulo' => 'Riscos, Integridade, Ética, Transparência, Correição e Controle Interno',
            'secoes' => [
                $this->secao('4.1', 'Gestão de Riscos', 'riscos', [
                    'riscos' => $this->riscos($pei, $org),
                ]),
                $this->secaoExterna('4.2', 'Gestão da Integridade'),
                $this->secaoExterna('4.3', 'Gestão da Ética'),
                $this->secaoExterna('4.4', 'Ouvidoria e Transparência'),
                $this->secaoExterna('4.5', 'Canais de Informação à Sociedade'),
                $this->secaoExterna('4.6', 'Correição'),
                $this->secaoExterna('4.7', 'Auditoria'),
            ],
        ];
    }

    private function capitulo5(): array
    {
        return [
            'numero' => '5',
            'titulo' => 'Informações Orçamentárias, Financeiras e Contábeis',
            'secoes' => [
                $this->secaoExterna('5.1', 'Base das Demonstrações Contábeis'),
                $this->secaoExterna('5.2', 'Balanço Patrimonial'),
                $this->secaoExterna('5.3', 'Ativos e Passivos Financeiros e Permanentes'),
                $this->secaoExterna('5.4', 'Contas de Compensação'),
                $this->secaoExterna('5.5', 'Superávit/Déficit Financeiro'),
                $this->secaoExterna('5.6', 'Demonstração das Variações Patrimoniais'),
                $this->secaoExterna('5.7', 'Balanço Orçamentário'),
                $this->secaoExterna('5.8', 'Execução de Restos a Pagar Não Processados'),
                $this->secaoExterna('5.9', 'RAP Processados e Não Processados Liquidados'),
                $this->secaoExterna('5.10', 'Balanço Financeiro'),
                $this->secaoExterna('5.11', 'Resultado Financeiro do Exercício'),
                $this->secaoExterna('5.12', 'Demonstração dos Fluxos de Caixa'),
                $this->secaoExterna('5.13', 'Notas Explicativas'),
                $this->secaoExterna('5.14', 'Esclarecimentos Adicionais'),
            ],
        ];
    }

    // ------------------------------------------------------------- conteúdos

    private function estrategia(?PEI $pei, ?Organization $org): array
    {
        if (! $pei) {
            return ['identidade' => null, 'valores' => collect(), 'temas' => collect(), 'grupos' => []];
        }

        $identidade = IdentidadeEstrategica::where('cod_pei', $pei->cod_pei)
            ->when($org, fn ($q) => $q->where('cod_organizacao', $org->cod_organizacao))
            ->first();

        // O modelo separa OBJETIVOS FINALÍSTICOS (borda vermelha, p. 27) de
        // SUPORTE (borda preta). Aqui isso vem da posição da perspectiva no
        // mapa: as de cima entregam resultado; as da base sustentam.
        $perspectivas = Perspectiva::where('cod_pei', $pei->cod_pei)
            ->with(['objetivos' => fn ($q) => $q->raiz()->ordenadoPorNivel()])
            ->orderBy('num_nivel_hierarquico_apresentacao', 'desc')
            ->get();

        $total = $perspectivas->count();
        $corte = (int) ceil($total / 2);

        $finalisticos = collect();
        $suporte = collect();

        foreach ($perspectivas->values() as $i => $p) {
            $destino = $i < $corte ? $finalisticos : $suporte;
            foreach ($p->objetivos as $objetivo) {
                $destino->push($objetivo);
            }
        }

        return [
            'identidade' => $identidade,
            'valores' => Valor::where('cod_pei', $pei->cod_pei)
                ->when($org, fn ($q) => $q->where('cod_organizacao', $org->cod_organizacao))
                ->orderBy('nom_valor')->get(),
            'temas' => TemaNorteador::where('cod_pei', $pei->cod_pei)
                ->when($org, fn ($q) => $q->where('cod_organizacao', $org->cod_organizacao))
                ->get(),
            'perspectivas' => $perspectivas,
            'objetivos_finalisticos' => $finalisticos,
            'objetivos_suporte' => $suporte,
        ];
    }

    /** Tabela 2.2.1 do modelo (p. 28). */
    private function tabelaPlanejamentoIntegrado(?PEI $pei, ?Organization $org): array
    {
        if (! $pei) {
            return [];
        }

        $linhas = [];
        $i = 0;

        foreach ($this->objetivosDoCiclo($pei) as $objetivo) {
            $iniciativas = PlanoDeAcao::where('cod_objetivo', $objetivo->cod_objetivo)
                ->when($org, fn ($q) => $q->where('cod_organizacao', $org->cod_organizacao))
                ->orderBy('dsc_plano_de_acao')
                ->get();

            $linhas[] = [
                'identificador' => ++$i,
                'objetivo' => $objetivo->nom_objetivo,
                'descricao' => $objetivo->dsc_objetivo,
                'iniciativas' => $iniciativas->pluck('dsc_plano_de_acao')->all(),
            ];
        }

        return $linhas;
    }

    /** Tabela 2.2.2 do modelo (p. 34-37): Objetivo · Iniciativas · Resultados. */
    private function tabelaResultados(?PEI $pei, ?Organization $org, int $ano): array
    {
        if (! $pei) {
            return [];
        }

        $linhas = [];

        foreach ($this->objetivosDoCiclo($pei) as $objetivo) {
            $iniciativas = PlanoDeAcao::where('cod_objetivo', $objetivo->cod_objetivo)
                ->when($org, fn ($q) => $q->where('cod_organizacao', $org->cod_organizacao))
                ->orderBy('dsc_plano_de_acao')
                ->get();

            $indicadores = Indicador::where('cod_objetivo', $objetivo->cod_objetivo)->get();

            $resultados = $indicadores->map(function (Indicador $ind) use ($ano) {
                $atingimento = $ind->calcularAtingimento($ano);

                return [
                    'indicador' => $ind->nom_indicador,
                    'unidade' => $ind->dsc_unidade_medida,
                    'atingimento' => round($atingimento, 1),
                    'texto' => sprintf(
                        '%s: %s de atingimento',
                        $ind->nom_indicador,
                        UnidadeMedida::formatar($atingimento, 'Percentual (%)')
                    ),
                ];
            })->all();

            $linhas[] = [
                'objetivo' => $objetivo->nom_objetivo,
                'iniciativas' => $iniciativas->pluck('dsc_plano_de_acao')->all(),
                'resultados' => $resultados,
            ];
        }

        return $linhas;
    }

    /** Seções 2.3 a 2.5 do modelo: os números de destaque do exercício. */
    private function grandesNumeros(?PEI $pei, ?Organization $org, int $ano): array
    {
        if (! $pei) {
            return [];
        }

        $objetivos = $this->objetivosDoCiclo($pei);

        $iniciativas = PlanoDeAcao::whereIn('cod_objetivo', $objetivos->pluck('cod_objetivo'))
            ->when($org, fn ($q) => $q->where('cod_organizacao', $org->cod_organizacao))
            ->get();

        $orcamento = (float) $iniciativas->sum('vlr_orcamento_previsto');

        $numeros = [
            ['rotulo' => 'Objetivos estratégicos', 'valor' => (string) $objetivos->count()],
            ['rotulo' => 'Iniciativas no ciclo', 'valor' => (string) $iniciativas->count()],
            ['rotulo' => 'Indicadores monitorados', 'valor' => (string) Indicador::whereIn('cod_objetivo', $objetivos->pluck('cod_objetivo'))->count()],
        ];

        if ($orcamento > 0) {
            $numeros[] = [
                'rotulo' => 'Orçamento previsto nas iniciativas',
                'valor' => UnidadeMedida::formatar($orcamento, 'Monetário (R$)'),
            ];
        }

        return $numeros;
    }

    private function cadeiaDeValor(?PEI $pei): array
    {
        if (! $pei) {
            return [];
        }

        $atividades = AtividadeCadeiaValor::where('cod_pei', $pei->cod_pei)
            ->with('processos')
            ->orderBy('num_ordem')
            ->get()
            ->groupBy('dsc_tipo');

        $grupos = [];

        foreach (AtividadeCadeiaValor::TIPOS as $tipo) {
            $itens = $atividades->get($tipo, collect());

            if ($itens->isNotEmpty()) {
                $grupos[] = ['tipo' => $tipo, 'itens' => $itens];
            }
        }

        return $grupos;
    }

    private function analiseAmbiental(?PEI $pei, ?Organization $org, string $tipo)
    {
        if (! $pei) {
            return collect();
        }

        return AnaliseAmbiental::where('cod_pei', $pei->cod_pei)
            ->when($org, fn ($q) => $q->where('cod_organizacao', $org->cod_organizacao))
            ->where('dsc_tipo_analise', $tipo)
            ->orderBy('dsc_categoria')
            ->orderBy('num_ordem')
            ->get()
            ->groupBy('dsc_categoria');
    }

    private function riscos(?PEI $pei, ?Organization $org)
    {
        if (! $pei) {
            return collect();
        }

        return Risco::where('cod_pei', $pei->cod_pei)
            ->when($org, fn ($q) => $q->where('cod_organizacao', $org->cod_organizacao))
            ->orderByDesc('num_nivel_risco')
            ->get();
    }

    // --------------------------------------------------------------- apoio

    private function objetivosDoCiclo(PEI $pei)
    {
        return Perspectiva::where('cod_pei', $pei->cod_pei)
            ->with(['objetivos' => fn ($q) => $q->raiz()->ordenadoPorNivel()])
            ->orderBy('num_nivel_hierarquico_apresentacao', 'desc')
            ->get()
            ->flatMap(fn ($p) => $p->objetivos);
    }

    /** O ciclo vigente no ano relatado — não simplesmente "o ativo". */
    private function cicloDoAno(int $ano): ?PEI
    {
        return PEI::where('num_ano_inicio_pei', '<=', $ano)
            ->where('num_ano_fim_pei', '>=', $ano)
            ->first()
            ?? PEI::ativos()->first();
    }

    private function secao(string $numero, string $titulo, string $tipo, array $dados): array
    {
        return [
            'numero' => $numero,
            'titulo' => $titulo,
            'tipo' => $tipo,
            'externa' => false,
            'dados' => $dados,
            'tem_conteudo' => $this->temConteudo($dados),
        ];
    }

    private function secaoExterna(string $numero, string $titulo): array
    {
        return [
            'numero' => $numero,
            'titulo' => $titulo,
            'tipo' => 'externa',
            'externa' => true,
            'fonte' => self::FONTES_EXTERNAS[$numero] ?? 'Fonte externa a este sistema',
            'dados' => [],
            'tem_conteudo' => false,
        ];
    }

    private function temConteudo(array $dados): bool
    {
        foreach ($dados as $valor) {
            if ($valor instanceof Collection) {
                if ($valor->isNotEmpty()) {
                    return true;
                }

                continue;
            }

            if (is_array($valor) && $valor !== []) {
                return true;
            }

            if (is_object($valor) || (is_string($valor) && trim($valor) !== '')) {
                return true;
            }
        }

        return false;
    }

    /** Na variante autoral, some tudo que não tem o que dizer. */
    private function podar(array $capitulos): array
    {
        $resultado = [];

        foreach ($capitulos as $capitulo) {
            $secoes = array_values(array_filter(
                $capitulo['secoes'],
                fn (array $s) => $s['tem_conteudo']
            ));

            if ($secoes !== []) {
                $capitulo['secoes'] = $secoes;
                $resultado[] = $capitulo;
            }
        }

        return $resultado;
    }
}
