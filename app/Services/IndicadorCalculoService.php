<?php

namespace App\Services;

use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicAlert;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\Perspectiva;
use App\Support\CalculoPolaridade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Service responsável pelo cálculo automático de indicadores
 * baseado no progresso ponderado das entregas de uma Iniciativa.
 *
 * ## Fórmula de Cálculo
 *
 * ```
 *                     Σ (Peso_i × Progresso_i)
 * Progresso (%) = ─────────────────────────────── × 100
 *                          Σ Peso_i
 * ```
 *
 * Onde:
 * - Progresso_i = valor entre 0.0 e 1.0 (decimal)
 * - Peso_i = peso da entrega (0-100)
 *
 * ## Mapeamento de Status
 *
 * | Status        | Decimal | Descrição                    |
 * |---------------|---------|------------------------------|
 * | Concluído     | 1.0     | 100% completo                |
 * | Em Andamento  | 0.5     | 50% progresso estimado       |
 * | Suspenso      | 0.25    | 25% (trabalho parcial feito) |
 * | Não Iniciado  | 0.0     | Ainda não começou            |
 * | Cancelado     | N/A     | Excluído do cálculo          |
 *
 * @author SEAE Strategic Planning Team
 *
 * @since 2026-02
 *
 * @version 2.0 - Refatoração com clareza matemática
 */
class IndicadorCalculoService
{
    /**
     * Mapeamento de status para fração decimal (0.0 a 1.0).
     *
     * IMPORTANTE: Usar escala decimal para evitar multiplicações
     * duplicadas e manter clareza matemática.
     */
    const STATUS_DECIMAL = [
        'Concluído' => 1.0,
        'Em Andamento' => 0.5,
        'Suspenso' => 0.25,
        'Não Iniciado' => 0.0,
        // Cancelado não está aqui - é excluído do cálculo
    ];

    /**
     * Mapeamento legado para percentual (mantido para compatibilidade).
     *
     * @deprecated Use STATUS_DECIMAL para novos cálculos
     */
    const STATUS_PERCENTUAL = [
        'Concluído' => 100,
        'Em Andamento' => 50,
        'Suspenso' => 25,
        'Não Iniciado' => 0,
        'Cancelado' => 0,
    ];

    /**
     * Calcula o progresso ponderado de uma Iniciativa.
     *
     * ## Lógica de Cálculo
     *
     * 1. Busca entregas válidas (não deletadas, não arquivadas, não canceladas)
     * 2. Se há pesos definidos: usa média ponderada
     * 3. Se não há pesos: usa média simples
     * 4. Para entregas com sub-entregas: calcula recursivamente
     *
     * @param  PlanoDeAcao  $plano  A iniciativa a calcular
     * @param  bool  $apenasRaiz  Se true, considera apenas entregas raiz (sem pai)
     * @return float Percentual de progresso (0.0 a 100.0)
     */
    public function calcularProgressoPlano(PlanoDeAcao $plano, bool $apenasRaiz = true): float
    {
        return $this->progressoDaIniciativa($plano, $apenasRaiz) ?? 0.0;
    }

    /**
     * Progresso da iniciativa (0 a 100) pela regra única; NULL quando não há
     * entrega a medir — "sem entregas", nunca 0%.
     */
    public function progressoDaIniciativa(PlanoDeAcao $plano, bool $apenasRaiz = true): ?float
    {
        return $this->progressoDasEntregas($this->entregasQueContam($plano->entregas()->get(), null, $apenasRaiz));
    }

    /**
     * 🔴 A REGRA ÚNICA das entregas que contam no progresso (22/11/2026).
     *
     * Havia cinco contas diferentes para a mesma coisa (iniciativa, ano, objetivo,
     * perspectiva e Mapa Estratégico), e a mesma iniciativa saía com quatro números
     * diferentes. Agora todas passam por aqui:
     * - só a entrega raiz conta — a sub-entrega entra no progresso da entrega pai;
     * - a arquivada e a cancelada ficam fora (a excluída já sai pelo SoftDeletes);
     * - com $ano, só as de prazo naquele ano.
     */
    public function entregasQueContam(Collection $entregas, ?int $ano = null, bool $apenasRaiz = true): Collection
    {
        return $entregas->filter(fn (Entrega $e): bool => (! $apenasRaiz || $e->cod_entrega_pai === null)
            && ! $e->bln_arquivado
            && $e->bln_status !== 'Cancelado'
            && ($ano === null || ($e->dte_prazo && (int) $e->dte_prazo->year === $ano)))
            ->values();
    }

    /**
     * Peso efetivo: o peso informado; sem peso, 1.
     *
     * Sem nenhum peso, isso é a média simples; com pesos, a entrega sem peso
     * continua contando (pouco) em vez de sumir do cálculo — concluí-la nunca
     * deixa o progresso parado. validarPesosPlano() avisa o peso que falta.
     */
    public static function pesoEfetivo(Entrega $entrega): float
    {
        $peso = (float) $entrega->num_peso;

        return $peso > 0 ? $peso : 1.0;
    }

    /**
     * Soma ponderada (Σ peso × fração) e soma dos pesos de entregas que já contam.
     *
     * @return array{0: float, 1: float}
     */
    public function acumularEntregas(Collection $entregasQueContam): array
    {
        $soma = 0.0;
        $pesos = 0.0;

        foreach ($entregasQueContam as $entrega) {
            $peso = self::pesoEfetivo($entrega);
            $soma += $peso * $this->progressoDaEntrega($entrega);
            $pesos += $peso;
        }

        return [$soma, $pesos];
    }

    /** Progresso (0 a 100, duas casas) de entregas que já contam; NULL sem nada a medir. */
    public function progressoDasEntregas(Collection $entregasQueContam): ?float
    {
        [$soma, $pesos] = $this->acumularEntregas($entregasQueContam);

        return $pesos > 0 ? round($soma / $pesos * 100, 2) : null;
    }

    /** Fração (0,0 a 1,0) de uma entrega: as sub-entregas que contam, ou a situação dela. */
    public function progressoDaEntrega(Entrega $entrega): float
    {
        return $this->getProgressoEntrega($entrega);
    }

    /**
     * Obtém o progresso de uma entrega como fração decimal (0.0 a 1.0).
     *
     * Se a entrega tem sub-entregas, calcula recursivamente.
     * Caso contrário, mapeia o status para decimal.
     *
     * @param  Entrega  $entrega  A entrega
     * @return float Progresso decimal (0.0 a 1.0)
     */
    protected function getProgressoEntrega(Entrega $entrega): float
    {
        // Se tem sub-entregas, calcular recursivamente
        if ($entrega->hasSubEntregas()) {
            return $this->calcularProgressoSubEntregas($entrega);
        }

        // Mapear status para decimal
        return self::STATUS_DECIMAL[$entrega->bln_status] ?? 0.0;
    }

    /**
     * Calcula o progresso de uma entrega pai baseado em suas sub-entregas.
     *
     * @param  Entrega  $entrega  A entrega pai
     * @return float Progresso decimal (0.0 a 1.0)
     */
    protected function calcularProgressoSubEntregas(Entrega $entrega): float
    {
        // A mesma regra das entregas raiz: arquivada e cancelada fora, peso efetivo.
        [$soma, $pesos] = $this->acumularEntregas($this->entregasQueContam($entrega->subEntregas()->get(), null, false));

        // Sem sub-entrega que conte, vale a situação da própria entrega.
        // Retorna fração (0,0 a 1,0), não percentual.
        return $pesos > 0 ? $soma / $pesos : (self::STATUS_DECIMAL[$entrega->bln_status] ?? 0.0);
    }

    /**
     * Obtém entregas válidas de um plano (não deletadas, não arquivadas).
     *
     * @param  PlanoDeAcao  $plano  A iniciativa
     * @param  bool  $apenasRaiz  Se true, retorna apenas entregas raiz
     */
    protected function getEntregasValidas(PlanoDeAcao $plano, bool $apenasRaiz = true): Collection
    {
        $query = $plano->entregas()
            ->where('bln_arquivado', false)
            ->whereNull('deleted_at');

        if ($apenasRaiz) {
            $query->whereNull('cod_entrega_pai');
        }

        return $query->get();
    }

    /**
     * Atualiza a evolução de um indicador calculado automaticamente.
     *
     * Cria ou atualiza o registro de evolução para o mês/ano atual
     * com o valor realizado baseado no progresso do plano.
     *
     * @param  Indicador  $indicador  O indicador a atualizar
     * @return EvolucaoIndicador|null A evolução criada/atualizada ou null se manual
     */
    public function atualizarIndicadorAutomatico(Indicador $indicador): ?EvolucaoIndicador
    {
        // Verificar se é indicador de cálculo automático
        if ($indicador->dsc_calculation_type !== 'action_plan') {
            return null;
        }

        // Verificar se tem plano vinculado
        if (! $indicador->cod_plano_de_acao || ! $indicador->planoDeAcao) {
            Log::warning("Indicador {$indicador->cod_indicador} configurado para cálculo automático mas sem plano vinculado.");

            return null;
        }

        // Calcular progresso do plano. Sem entrega a medir não há medição: gravar 0
        // pintava o farol de vermelho por algo que ninguém mediu (22/11/2026).
        $progresso = $this->progressoDaIniciativa($indicador->planoDeAcao);

        if ($progresso === null) {
            Log::info("Indicador {$indicador->cod_indicador} sem medição: a iniciativa ainda não tem entrega a medir.");

            return null;
        }

        // Obter ano e mês atual
        $ano = now()->year;
        $mes = now()->month;

        // Buscar ou criar evolução para o período atual
        $evolucao = EvolucaoIndicador::updateOrCreate(
            [
                'cod_indicador' => $indicador->cod_indicador,
                'num_ano' => $ano,
                'num_mes' => $mes,
            ],
            [
                'vlr_realizado' => $progresso,
                'vlr_previsto' => 100, // Meta padrão: 100% de conclusão
                // (Havia aqui 'txt_observacao', coluna que tab_evolucao_indicador
                // não tem e que não está no $fillable: era descartada em silêncio.)
            ]
        );

        Log::info("Indicador {$indicador->nom_indicador} atualizado automaticamente: {$progresso}% ({$mes}/{$ano})");

        return $evolucao;
    }

    /**
     * Atualiza todos os indicadores automáticos de uma iniciativa.
     *
     * Chamado quando uma entrega do plano é alterada (created, updated, deleted).
     *
     * @param  PlanoDeAcao  $plano  A iniciativa modificada
     * @return int Número de indicadores atualizados
     */
    public function atualizarIndicadoresDoPlano(PlanoDeAcao $plano): int
    {
        $indicadores = Indicador::where('cod_plano_de_acao', $plano->cod_plano_de_acao)
            ->where('dsc_calculation_type', 'action_plan')
            ->get();

        $count = 0;
        foreach ($indicadores as $indicador) {
            if ($this->atualizarIndicadorAutomatico($indicador)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Valida se os pesos das entregas de um plano somam 100.
     *
     * @param  PlanoDeAcao  $plano  A iniciativa
     * @return array ['valid' => bool, 'total' => float, 'message' => string]
     */
    public function validarPesosPlano(PlanoDeAcao $plano): array
    {
        $entregas = $plano->entregas()
            ->whereNull('cod_entrega_pai')
            ->where('bln_arquivado', false)
            ->whereNull('deleted_at')
            ->where('bln_status', '!=', 'Cancelado')
            ->get();

        if ($entregas->isEmpty()) {
            return [
                'valid' => true,
                'total' => 0,
                'message' => 'Nenhuma entrega cadastrada.',
            ];
        }

        $totalPesos = (float) $entregas->sum('num_peso');
        $semPeso = $entregas->filter(fn (Entrega $e): bool => (float) $e->num_peso <= 0)->count();

        // Tolerância de 0.01 para erros de arredondamento
        $somaCem = abs($totalPesos - 100) < 0.01;

        // Com pesos em uso, a entrega sem peso conta com peso 1 (pesoEfetivo): a
        // soma pode dar 100 e o cálculo ainda não ser o que a pessoa distribuiu.
        $semPesoEmUso = $semPeso > 0 && $totalPesos > 0;
        $isValid = $somaCem && ! $semPesoEmUso;

        $formatado = number_format($totalPesos, 2, ',', '.');
        $formatado = str_ends_with($formatado, ',00') ? substr($formatado, 0, -3) : $formatado;

        $mensagem = match (true) {
            $isValid => 'Pesos corretamente distribuídos (100%).',
            $somaCem => "A soma dos pesos é 100%, mas {$semPeso} ".($semPeso === 1 ? 'entrega está sem peso e conta' : 'entregas estão sem peso e contam')
                .' com peso 1. Dê um peso a '.($semPeso === 1 ? 'ela' : 'elas').' ou redistribua os pesos.',
            default => "A soma dos pesos é {$formatado}%. Ajuste para totalizar 100%.",
        };

        return [
            'valid' => $isValid,
            'total' => round($totalPesos, 2),
            'message' => $mensagem,
        ];
    }

    /**
     * Redistribui pesos igualitários entre entregas de um plano.
     *
     * @param  PlanoDeAcao  $plano  A iniciativa
     * @return int Número de entregas atualizadas
     */
    public function redistribuirPesosIguais(PlanoDeAcao $plano): int
    {
        $entregas = $plano->entregas()
            ->whereNull('cod_entrega_pai')
            ->where('bln_arquivado', false)
            ->whereNull('deleted_at')
            ->where('bln_status', '!=', 'Cancelado')
            ->get();

        if ($entregas->isEmpty()) {
            return 0;
        }

        $pesoIgual = round(100 / $entregas->count(), 2);

        // Ajuste para garantir que soma = 100
        $ajuste = 100 - ($pesoIgual * $entregas->count());

        foreach ($entregas as $index => $entrega) {
            $peso = $pesoIgual;
            // Adicionar ajuste na última entrega
            if ($index === $entregas->count() - 1) {
                $peso += $ajuste;
            }
            $entrega->update(['num_peso' => round($peso, 2)]);
        }

        return $entregas->count();
    }

    /**
     * Retorna estatísticas do plano para exibição na UI.
     *
     * @param  PlanoDeAcao  $plano  A iniciativa
     * @return array Estatísticas detalhadas
     */
    public function getEstatisticasPlano(PlanoDeAcao $plano): array
    {
        $entregas = $plano->entregas()
            ->whereNull('cod_entrega_pai')
            ->where('bln_arquivado', false)
            ->whereNull('deleted_at')
            ->get();

        $porStatus = $entregas->groupBy('bln_status')->map->count();

        return [
            'total_entregas' => $entregas->count(),
            'por_status' => $porStatus->toArray(),
            'progresso_simples' => $plano->calcularProgressoEntregas(),
            'progresso_ponderado' => $this->calcularProgressoPlano($plano),
            'validacao_pesos' => $this->validarPesosPlano($plano),
        ];
    }

    /**
     * Simula o cálculo para debug/preview.
     *
     * @param  PlanoDeAcao  $plano  A iniciativa
     * @return array Detalhes do cálculo passo a passo
     */
    public function simularCalculo(PlanoDeAcao $plano): array
    {
        $entregas = $this->getEntregasValidas($plano);
        $detalhes = [];
        $soma = 0;
        $divisor = 0;

        foreach ($entregas as $entrega) {
            if ($entrega->bln_status === 'Cancelado') {
                $detalhes[] = [
                    'entrega' => $entrega->dsc_entrega,
                    'peso' => $entrega->num_peso,
                    'status' => $entrega->bln_status,
                    'progresso' => 'N/A (excluído)',
                    'contribuicao' => 0,
                ];

                continue;
            }

            $progresso = $this->getProgressoEntrega($entrega);
            $peso = self::pesoEfetivo($entrega);
            $contribuicao = $peso * $progresso;

            $detalhes[] = [
                'entrega' => $entrega->dsc_entrega,
                'peso' => $peso,
                'status' => $entrega->bln_status,
                'progresso' => round($progresso * 100, 1).'%',
                'contribuicao' => round($contribuicao, 2),
            ];

            $soma += $contribuicao;
            $divisor += $peso;
        }

        return [
            'detalhes' => $detalhes,
            'soma_contribuicoes' => round($soma, 2),
            'soma_pesos' => round($divisor, 2),
            'resultado' => $divisor > 0 ? round(($soma / $divisor) * 100, 2) : 0,
        ];
    }

    /**
     * Calcula o progresso de um plano considerando APENAS entregas com prazo no ano especificado.
     *
     * @param  PlanoDeAcao  $plano  A iniciativa
     * @param  int  $ano  O ano de referência
     * @return array ['progresso' => float, 'total_entregas' => int, 'detalhes' => array]
     */
    public function calcularProgressoPlanoNoAno(PlanoDeAcao $plano, int $ano): array
    {
        // 1. Filtrar entregas do ano (usando a relação já carregada se possível, ou query)
        // Preferir filter na coleção se já estiver carregada para evitar N+1 em loops
        $entregasAno = $this->entregasQueContam($plano->entregas, $ano);

        if ($entregasAno->isEmpty()) {
            return [
                'progresso' => 0.0,
                'total_entregas' => 0,
                'detalhes' => [],
                'status_calculado' => 'Sem Entregas',
            ];
        }

        // 2. Calcular o progresso pela regra única (peso efetivo, sub-entregas no pai)
        $progressoGeral = $this->progressoDasEntregas($entregasAno) ?? 0.0;
        $detalhes = $entregasAno->map(fn (Entrega $entrega): array => [
            'entrega' => $entrega->dsc_entrega,
            'prazo' => $entrega->dte_prazo->format('d/m/Y'),
            'status' => $entrega->bln_status,
            'peso' => self::pesoEfetivo($entrega),
            'progresso_item' => $this->getProgressoEntrega($entrega),
        ])->all();

        return [
            'progresso' => $progressoGeral,
            'total_entregas' => $entregasAno->count(),
            'detalhes' => $detalhes,
            'status_calculado' => $progressoGeral >= 100 ? 'Concluído' : ($progressoGeral > 0 ? 'Em Andamento' : 'Não Iniciado'),
        ];
    }

    /**
     * Calcula o atingimento global de uma perspectiva para um determinado ano.
     * Segue EXATAMENTE a lógica do Mapa Estratégico para garantir Single Source of Truth.
     *
     * NULL = sem medição: nenhum indicador medido e nenhuma iniciativa com
     * entrega no ano (ou só o componente que a perspectiva pesa com 0).
     *
     * @return float|null Percentual (0-100+), ou NULL sem medição
     */
    public function calcularAtingimentoPerspectiva(Perspectiva $perspectiva, int $ano): ?float
    {
        $pesoInd = $perspectiva->num_peso_indicadores ?? 100;
        $pesoPlan = $perspectiva->num_peso_planos ?? 0;

        // 1. Média dos indicadores MEDIDOS (sem medição e informativo ficam fora)
        $somaAtingInd = 0;
        $totalInd = 0;

        foreach ($perspectiva->objetivos as $obj) {
            foreach ($obj->indicadores as $ind) {
                $ating = $ind->atingimentoParaMedia($ano);
                if ($ating === null) {
                    continue;
                }
                $somaAtingInd += $ating;
                $totalInd++;
            }
        }

        $mediaIndicadores = $totalInd > 0 ? ($somaAtingInd / $totalInd) : null;

        // 2. Calcular Média Planos (Ponderada Globalmente)
        $somaProgressoPlan = 0;
        $somaPesoPlan = 0;

        foreach ($perspectiva->objetivos as $obj) {
            foreach ($obj->planosAcao as $plano) {
                // As entregas do ano pela regra única (entregasQueContam).
                [$soma, $pesos] = $this->acumularEntregas($this->entregasQueContam($plano->entregas, $ano));
                $somaProgressoPlan += $soma;
                $somaPesoPlan += $pesos;
            }
        }

        $mediaPlanos = $somaPesoPlan > 0 ? ($somaProgressoPlan / $somaPesoPlan) * 100 : null;

        // 3. Cálculo Final Híbrido (componente sem dado sai; pesos se renormalizam)
        $atingimentoFinal = self::combinarComponentes($mediaIndicadores, $pesoInd, $mediaPlanos, $pesoPlan);

        return $atingimentoFinal === null ? null : round($atingimentoFinal, 1);
    }

    /**
     * O cálculo híbrido indicadores × iniciativas, honesto com o que falta.
     *
     * 🔴 Componente SEM DADO não é componente em 0%. Antes, uma perspectiva
     * 50/50 com a iniciativa toda entregue e o indicador ainda sem medição
     * saía com 50% — a metade "indicadores" entrava como zero. Agora o
     * componente ausente (NULL) fica de fora e os pesos dos presentes se
     * renormalizam. Nenhum presente — ou só presentes com peso 0 — devolve
     * NULL: "sem medição", nunca 0% pintado de crítico.
     *
     * Uma regra só, usada pelo Mapa Estratégico e por este serviço.
     */
    public static function combinarComponentes(?float $mediaIndicadores, float|int $pesoIndicadores, ?float $mediaPlanos, float|int $pesoPlanos): ?float
    {
        $soma = 0.0;
        $pesos = 0.0;

        foreach ([[$mediaIndicadores, $pesoIndicadores], [$mediaPlanos, $pesoPlanos]] as [$media, $peso]) {
            if ($media === null || $peso <= 0) {
                continue;
            }
            $soma += $media * $peso;
            $pesos += $peso;
        }

        return $pesos > 0 ? $soma / $pesos : null;
    }

    /**
     * Calcula o atingimento de um Objetivo específico para um determinado ano.
     * Segue a mesma lógica híbrida da Perspectiva (Indicadores + Planos), usando os pesos da Perspectiva pai.
     *
     * @return float|null Percentual (0-100+), ou NULL sem medição
     */
    public function calcularAtingimentoObjetivo(Objetivo $objetivo, int $ano): ?float
    {
        // Pesos vêm da Perspectiva Pai
        $perspectiva = $objetivo->perspectiva;
        $pesoInd = $perspectiva->num_peso_indicadores ?? 100;
        $pesoPlan = $perspectiva->num_peso_planos ?? 0;

        // 1. Média dos indicadores MEDIDOS do objetivo
        $somaAtingInd = 0;
        $totalInd = 0;

        foreach ($objetivo->indicadores as $ind) {
            $ating = $ind->atingimentoParaMedia($ano);
            if ($ating === null) {
                continue;
            }
            $somaAtingInd += $ating;
            $totalInd++;
        }

        $mediaIndicadores = $totalInd > 0 ? ($somaAtingInd / $totalInd) : null;

        // 2. Calcular Média Planos do Objetivo (Ponderada)
        $somaProgressoPlan = 0;
        $somaPesoPlan = 0;

        foreach ($objetivo->planosAcao as $plano) {
            // As entregas do ano pela regra única (entregasQueContam).
            [$soma, $pesos] = $this->acumularEntregas($this->entregasQueContam($plano->entregas, $ano));
            $somaProgressoPlan += $soma;
            $somaPesoPlan += $pesos;
        }

        $mediaPlanos = $somaPesoPlan > 0 ? ($somaProgressoPlan / $somaPesoPlan) * 100 : null;

        // 3. Cálculo Final Híbrido (componente sem dado sai; pesos se renormalizam)
        $atingimentoFinal = self::combinarComponentes($mediaIndicadores, $pesoInd, $mediaPlanos, $pesoPlan);

        return $atingimentoFinal === null ? null : round($atingimentoFinal, 1);
    }

    /**
     * Calcula o Índice de Qualidade de Gestão (IQG) do PEI — índice único de desempenho.
     *
     * Média ponderada dos atingimentos das perspectivas que têm medição no
     * período (calcularAtingimentoPerspectiva() diferente de NULL).
     *
     * @return array{valor: float, tendencia: string, perspectivas: array, grau: mixed}
     */
    public function calcularIQG(string $codPei, int $ano, ?int $mes = null): array
    {
        $mes = $mes ?? now()->month;

        $perspectivas = Perspectiva::where('cod_pei', $codPei)
            ->with(['objetivos.indicadores.evolucoes', 'objetivos.planosAcao.entregas'])
            ->orderBy('num_nivel_hierarquico_apresentacao')
            ->get();

        $somaAtingimento = 0.0;
        $somaPeso = 0;
        $detalhes = [];

        foreach ($perspectivas as $perspectiva) {
            // Só entra a perspectiva COM MEDIÇÃO. O filtro antigo ("tem alguma
            // evolução no ano") deixava entrar a que só tinha previsto lançado
            // — e ela pesava no índice como 0%.
            $atingimento = $this->calcularAtingimentoPerspectiva($perspectiva, $ano);

            if ($atingimento === null) {
                continue;
            }

            $peso = max(1, $perspectiva->num_nivel_hierarquico_apresentacao ?? 1);

            $somaAtingimento += $atingimento * $peso;
            $somaPeso += $peso;

            $detalhes[] = [
                'perspectiva' => $perspectiva->dsc_perspectiva,
                'atingimento' => round($atingimento, 1),
                'peso' => $peso,
            ];
        }

        $valor = $somaPeso > 0 ? round($somaAtingimento / $somaPeso, 1) : 0.0;

        // Mesma regra usada por todo o sistema, num lugar só: a faixa vem do
        // ciclo E do ano, com a faixa específica do ano tendo precedência
        // sobre a geral do ciclo.
        $grau = GrauSatisfacao::faixaDe($valor, $codPei, $ano);

        return [
            'valor' => $valor,
            'perspectivas' => $detalhes,
            'grau' => $grau,
            'tem_dados' => $somaPeso > 0,
        ];
    }

    /**
     * Dispara alerta de StrategicAlert se a tendência do indicador for desfavorável.
     * Evita duplicidade verificando se já existe alerta não lido nas últimas 24h.
     */
    public function verificarAlertaTendencia(Indicador $indicador): void
    {
        $tendencia = $this->calcularTendencia($indicador->cod_indicador);

        if ($tendencia['favoravel'] !== false || count($tendencia['pontos']) < 2) {
            return;
        }

        $jaAlertado = StrategicAlert::where('title', 'like', '%'.$indicador->nom_indicador.'%')
            ->whereNull('read_at')
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();

        if ($jaAlertado) {
            return;
        }

        $userId = auth()->id();
        if (! $userId) {
            return;
        }

        StrategicAlert::create([
            'user_id' => $userId,
            // Unidade validada contra o escopo, nunca a sessão crua.
            'cod_organizacao' => Auth::user()?->organizacaoSelecionadaId(),
            'title' => 'Tendência Desfavorável: '.$indicador->nom_indicador,
            'message' => 'O indicador apresenta tendência '.strtolower($tendencia['direcao'])
                .' ('.($tendencia['variacao_pct'] >= 0 ? '+' : '').$tendencia['variacao_pct'].'% por período), '
                .'desfavorável para a polaridade '.($indicador->dsc_polaridade ?? 'Positiva').'. Avalie ação corretiva.',
            'icon' => 'bi-graph-down-arrow',
            'type' => 'danger',
        ]);
    }

    /**
     * Regressão linear simples sobre os últimos N valores realizados.
     * Retorna direção (Crescente/Decrescente/Estável), se é favorável e variação percentual.
     */
    public function calcularTendencia(string $codIndicador, int $ultimosMeses = 3): array
    {
        $neutro = ['direcao' => 'Estável', 'favoravel' => null, 'variacao_pct' => 0.0, 'pontos' => []];

        $indicador = Indicador::with(['evolucoes'])->find($codIndicador);
        if (! $indicador) {
            return $neutro;
        }

        $evolucoes = $indicador->evolucoes()
            ->whereNotNull('vlr_realizado')
            ->orderBy('num_ano')
            ->orderBy('num_mes')
            ->get(['num_ano', 'num_mes', 'vlr_realizado'])
            ->slice(-$ultimosMeses);

        if ($evolucoes->count() < 2) {
            return array_merge($neutro, ['pontos' => $evolucoes->pluck('vlr_realizado')->toArray()]);
        }

        $n = $evolucoes->count();
        $xs = range(1, $n);
        $ys = $evolucoes->pluck('vlr_realizado')->map(fn ($v) => (float) $v)->toArray();
        $meanX = array_sum($xs) / $n;
        $meanY = array_sum($ys) / $n;

        $num = 0.0;
        $den = 0.0;
        foreach ($xs as $i => $x) {
            $num += ($x - $meanX) * ($ys[$i] - $meanY);
            $den += ($x - $meanX) ** 2;
        }

        $slope = $den != 0 ? $num / $den : 0.0;

        // threshold relativo: 1% do valor médio para evitar falsos positivos em séries estáveis
        $threshold = $meanY != 0 ? abs($meanY) * 0.01 : 0.01;

        $direcao = match (true) {
            $slope > $threshold => 'Crescente',
            $slope < -$threshold => 'Decrescente',
            default => 'Estável',
        };

        $variacaoPct = $meanY != 0 ? round(($slope / abs($meanY)) * 100, 1) : 0.0;

        // Favorável depende da polaridade
        // Normalizada: o rótulo longo ("Negativa (Quanto menor, melhor)"), que
        // vem do legado e de importação, caía no default e ficava sem juízo.
        $favoravel = match (CalculoPolaridade::normalizar($indicador->dsc_polaridade)) {
            CalculoPolaridade::POSITIVA => $direcao === 'Crescente',
            CalculoPolaridade::NEGATIVA => $direcao === 'Decrescente',
            CalculoPolaridade::ESTABILIDADE => $direcao === 'Estável',
            default => null,
        };

        return [
            'direcao' => $direcao,
            'favoravel' => $favoravel,
            'variacao_pct' => $variacaoPct,
            'pontos' => $ys,
        ];
    }
}
