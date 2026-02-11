<?php

namespace App\Services;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\Entrega;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Service responsável pelo cálculo automático de indicadores
 * baseado no progresso ponderado das entregas de um Plano de Ação.
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
 * @since 2026-02
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
        'Concluído'     => 1.0,
        'Em Andamento'  => 0.5,
        'Suspenso'      => 0.25,
        'Não Iniciado'  => 0.0,
        // Cancelado não está aqui - é excluído do cálculo
    ];

    /**
     * Mapeamento legado para percentual (mantido para compatibilidade).
     * @deprecated Use STATUS_DECIMAL para novos cálculos
     */
    const STATUS_PERCENTUAL = [
        'Concluído'     => 100,
        'Em Andamento'  => 50,
        'Suspenso'      => 25,
        'Não Iniciado'  => 0,
        'Cancelado'     => 0,
    ];

    /**
     * Calcula o progresso ponderado de um Plano de Ação.
     * 
     * ## Lógica de Cálculo
     * 
     * 1. Busca entregas válidas (não deletadas, não arquivadas, não canceladas)
     * 2. Se há pesos definidos: usa média ponderada
     * 3. Se não há pesos: usa média simples
     * 4. Para entregas com sub-entregas: calcula recursivamente
     * 
     * @param PlanoDeAcao $plano O plano de ação a calcular
     * @param bool $apenasRaiz Se true, considera apenas entregas raiz (sem pai)
     * @return float Percentual de progresso (0.0 a 100.0)
     */
    public function calcularProgressoPlano(PlanoDeAcao $plano, bool $apenasRaiz = true): float
    {
        $entregas = $this->getEntregasValidas($plano, $apenasRaiz);

        if ($entregas->isEmpty()) {
            return 0.0;
        }

        // Filtrar entregas canceladas (não participam do cálculo)
        $entregasAtivas = $entregas->filter(
            fn(Entrega $e) => $e->bln_status !== 'Cancelado'
        );

        if ($entregasAtivas->isEmpty()) {
            return 0.0;
        }

        // Verificar se plano usa pesos
        $somaPesos = $entregasAtivas->sum('num_peso');
        
        // Se nenhum peso definido, usar média simples
        if ($somaPesos <= 0) {
            return $this->calcularMediaSimples($entregasAtivas);
        }

        // Cálculo ponderado: Σ(peso × progresso) / Σ(peso) × 100
        return $this->calcularMediaPonderada($entregasAtivas, $somaPesos);
    }

    /**
     * Calcula média simples quando não há pesos definidos.
     * 
     * Fórmula: (Σ Progresso_i) / N × 100
     * 
     * @param Collection $entregas Entregas para calcular
     * @return float Percentual de progresso (0.0 a 100.0)
     */
    protected function calcularMediaSimples(Collection $entregas): float
    {
        if ($entregas->isEmpty()) {
            return 0.0;
        }

        $somaProgresso = 0.0;

        foreach ($entregas as $entrega) {
            $somaProgresso += $this->getProgressoEntrega($entrega);
        }

        // Média simples × 100 para converter decimal para percentual
        $media = $somaProgresso / $entregas->count();
        
        return round($media * 100, 2);
    }

    /**
     * Calcula média ponderada com base nos pesos das entregas.
     * 
     * Fórmula: Σ(peso × progresso) / Σ(peso) × 100
     * 
     * @param Collection $entregas Entregas para calcular
     * @param float $somaPesos Soma total dos pesos
     * @return float Percentual de progresso (0.0 a 100.0)
     */
    protected function calcularMediaPonderada(Collection $entregas, float $somaPesos): float
    {
        if ($somaPesos <= 0) {
            return 0.0;
        }

        $progressoAcumulado = 0.0;

        foreach ($entregas as $entrega) {
            // Ignorar entregas sem peso no cálculo ponderado
            if ($entrega->num_peso <= 0) {
                continue;
            }

            $progressoEntrega = $this->getProgressoEntrega($entrega); // 0.0 a 1.0
            $progressoAcumulado += $entrega->num_peso * $progressoEntrega;
        }

        // Resultado: (peso × decimal) / somaPesos × 100
        // Exemplo: (40 × 1.0 + 30 × 0.5) / 100 × 100 = 55%
        return round(($progressoAcumulado / $somaPesos) * 100, 2);
    }

    /**
     * Obtém o progresso de uma entrega como fração decimal (0.0 a 1.0).
     * 
     * Se a entrega tem sub-entregas, calcula recursivamente.
     * Caso contrário, mapeia o status para decimal.
     * 
     * @param Entrega $entrega A entrega
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
     * @param Entrega $entrega A entrega pai
     * @return float Progresso decimal (0.0 a 1.0)
     */
    protected function calcularProgressoSubEntregas(Entrega $entrega): float
    {
        $subEntregas = $entrega->subEntregas()
            ->where('bln_arquivado', false)
            ->whereNull('deleted_at')
            ->where('bln_status', '!=', 'Cancelado')
            ->get();

        if ($subEntregas->isEmpty()) {
            // Se não tem sub-entregas válidas, usar status da própria entrega
            return self::STATUS_DECIMAL[$entrega->bln_status] ?? 0.0;
        }

        $somaPesos = $subEntregas->sum('num_peso');

        // Se sub-entregas não têm pesos, usar média simples
        if ($somaPesos <= 0) {
            $somaProgresso = 0.0;
            foreach ($subEntregas as $sub) {
                $somaProgresso += $this->getProgressoEntrega($sub);
            }
            return $somaProgresso / $subEntregas->count();
        }

        // Cálculo ponderado das sub-entregas
        $progressoAcumulado = 0.0;
        foreach ($subEntregas as $sub) {
            if ($sub->num_peso > 0) {
                $progressoAcumulado += $sub->num_peso * $this->getProgressoEntrega($sub);
            }
        }

        // Retorna decimal (0.0 a 1.0), não percentual
        return $progressoAcumulado / $somaPesos;
    }

    /**
     * Obtém entregas válidas de um plano (não deletadas, não arquivadas).
     * 
     * @param PlanoDeAcao $plano O plano de ação
     * @param bool $apenasRaiz Se true, retorna apenas entregas raiz
     * @return Collection
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
     * @param Indicador $indicador O indicador a atualizar
     * @return EvolucaoIndicador|null A evolução criada/atualizada ou null se manual
     */
    public function atualizarIndicadorAutomatico(Indicador $indicador): ?EvolucaoIndicador
    {
        // Verificar se é indicador de cálculo automático
        if ($indicador->dsc_calculation_type !== 'action_plan') {
            return null;
        }

        // Verificar se tem plano vinculado
        if (!$indicador->cod_plano_de_acao || !$indicador->planoDeAcao) {
            Log::warning("Indicador {$indicador->cod_indicador} configurado para cálculo automático mas sem plano vinculado.");
            return null;
        }

        // Calcular progresso do plano
        $progresso = $this->calcularProgressoPlano($indicador->planoDeAcao);

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
                'txt_observacao' => 'Valor calculado automaticamente com base no progresso das entregas ponderadas do plano de ação.',
            ]
        );

        Log::info("Indicador {$indicador->nom_indicador} atualizado automaticamente: {$progresso}% ({$mes}/{$ano})");

        return $evolucao;
    }

    /**
     * Atualiza todos os indicadores automáticos de um plano de ação.
     * 
     * Chamado quando uma entrega do plano é alterada (created, updated, deleted).
     * 
     * @param PlanoDeAcao $plano O plano de ação modificado
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
     * @param PlanoDeAcao $plano O plano de ação
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

        $totalPesos = $entregas->sum('num_peso');

        // Tolerância de 0.01 para erros de arredondamento
        $isValid = abs($totalPesos - 100) < 0.01;

        return [
            'valid' => $isValid,
            'total' => round($totalPesos, 2),
            'message' => $isValid 
                ? 'Pesos corretamente distribuídos (100%).' 
                : "A soma dos pesos é {$totalPesos}%. Ajuste para totalizar 100%.",
        ];
    }

    /**
     * Redistribui pesos igualitários entre entregas de um plano.
     * 
     * @param PlanoDeAcao $plano O plano de ação
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
     * @param PlanoDeAcao $plano O plano de ação
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
     * @param PlanoDeAcao $plano O plano de ação
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
            $peso = $entrega->num_peso > 0 ? $entrega->num_peso : 0;
            $contribuicao = $peso * $progresso;

            $detalhes[] = [
                'entrega' => $entrega->dsc_entrega,
                'peso' => $peso,
                'status' => $entrega->bln_status,
                'progresso' => round($progresso * 100, 1) . '%',
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
     * @param PlanoDeAcao $plano O plano de ação
     * @param int $ano O ano de referência
     * @return array ['progresso' => float, 'total_entregas' => int, 'detalhes' => array]
     */
    public function calcularProgressoPlanoNoAno(PlanoDeAcao $plano, int $ano): array
    {
        // 1. Filtrar entregas do ano (usando a relação já carregada se possível, ou query)
        // Preferir filter na coleção se já estiver carregada para evitar N+1 em loops
        $entregasAno = $plano->entregas->filter(function($entrega) use ($ano) {
            return $entrega->dte_prazo && 
                   $entrega->dte_prazo->year == $ano &&
                   $entrega->bln_status !== 'Cancelado' &&
                   $entrega->cod_entrega_pai === null; // Apenas raiz
        });

        if ($entregasAno->isEmpty()) {
            return [
                'progresso' => 0.0,
                'total_entregas' => 0,
                'detalhes' => [],
                'status_calculado' => 'Sem Entregas'
            ];
        }

        // 2. Calcular Progresso (Ponderado ou Simples)
        $somaPesos = $entregasAno->sum('num_peso');
        $progressoGeral = 0.0;
        $detalhes = [];

        if ($somaPesos > 0) {
            // Média Ponderada
            $progressoAcumulado = 0.0;
            foreach ($entregasAno as $entrega) {
                if ($entrega->num_peso <= 0) continue;
                $progressoUnitario = $this->getProgressoEntrega($entrega);
                $progressoAcumulado += $entrega->num_peso * $progressoUnitario;
                
                $detalhes[] = [
                    'entrega' => $entrega->dsc_entrega,
                    'prazo' => $entrega->dte_prazo->format('d/m/Y'),
                    'status' => $entrega->bln_status,
                    'peso' => $entrega->num_peso,
                    'progresso_item' => $progressoUnitario
                ];
            }
            $progressoGeral = round(($progressoAcumulado / $somaPesos) * 100, 2);
        } else {
            // Média Simples
            $somaProgresso = 0.0;
            foreach ($entregasAno as $entrega) {
                $progressoUnitario = $this->getProgressoEntrega($entrega);
                $somaProgresso += $progressoUnitario;

                $detalhes[] = [
                    'entrega' => $entrega->dsc_entrega,
                    'prazo' => $entrega->dte_prazo->format('d/m/Y'),
                    'status' => $entrega->bln_status,
                    'peso' => 0, // Peso zero ou indefinido
                    'progresso_item' => $progressoUnitario
                ];
            }
            $progressoGeral = round(($somaProgresso / $entregasAno->count()) * 100, 2);
        }

        return [
            'progresso' => $progressoGeral,
            'total_entregas' => $entregasAno->count(),
            'detalhes' => $detalhes,
            'status_calculado' => $progressoGeral >= 100 ? 'Concluído' : ($progressoGeral > 0 ? 'Em Andamento' : 'Não Iniciado')
        ];
    }

    /**
     * Calcula o atingimento global de uma perspectiva para um determinado ano.
     * Segue EXATAMENTE a lógica do Mapa Estratégico para garantir Single Source of Truth.
     * 
     * @param \App\Models\StrategicPlanning\Perspectiva $perspectiva
     * @param int $ano
     * @return float Percentual (0-100)
     */
    public function calcularAtingimentoPerspectiva(\App\Models\StrategicPlanning\Perspectiva $perspectiva, int $ano): float
    {
        $pesoInd = $perspectiva->num_peso_indicadores ?? 100;
        $pesoPlan = $perspectiva->num_peso_planos ?? 0;

        // 1. Calcular Média Indicadores
        $somaAtingInd = 0;
        $totalInd = 0;
        
        foreach ($perspectiva->objetivos as $obj) {
            foreach ($obj->indicadores as $ind) {
                // Assume que o model Indicador tem este método. 
                // Se não tiver, o Mapa Estratégico quebraria, então DEVE ter.
                $ating = $ind->calcularAtingimento($ano); 
                $somaAtingInd += $ating;
                $totalInd++;
            }
        }
        
        $mediaIndicadores = $totalInd > 0 ? ($somaAtingInd / $totalInd) : 0;

        // 2. Calcular Média Planos (Ponderada Globalmente)
        $somaProgressoPlan = 0;
        $somaPesoPlan = 0;
        
        foreach ($perspectiva->objetivos as $obj) {
            foreach ($obj->planosAcao as $plano) {
                // Filtrar entregas do ano
                $entregasAno = $plano->entregas->filter(function($entrega) use ($ano) {
                    return $entrega->dte_prazo && 
                           $entrega->dte_prazo->year == $ano &&
                           $entrega->bln_status !== 'Cancelado' &&
                           $entrega->cod_entrega_pai === null;
                });

                if ($entregasAno->isEmpty()) continue;

                foreach ($entregasAno as $entrega) {
                    $statusDecimal = match($entrega->bln_status) {
                        'Concluído' => 1.0, 
                        'Em Andamento' => 0.5, 
                        'Suspenso' => 0.25, 
                        default => 0.0
                    };
                    $peso = $entrega->num_peso > 0 ? $entrega->num_peso : 1;
                    
                    $somaProgressoPlan += ($peso * $statusDecimal);
                    $somaPesoPlan += $peso;
                }
            }
        }
        
        $mediaPlanos = $somaPesoPlan > 0 ? ($somaProgressoPlan / $somaPesoPlan) * 100 : 0;

        // 3. Cálculo Final Híbrido
        $atingimentoFinal = 0;
        $somaPesosConfig = $pesoInd + $pesoPlan;

        if ($somaPesosConfig > 0) {
            $atingimentoFinal = (($mediaIndicadores * $pesoInd) + ($mediaPlanos * $pesoPlan)) / $somaPesosConfig;
        }
        
        return round($atingimentoFinal, 1);
    }

    /**
     * Calcula o atingimento de um Objetivo específico para um determinado ano.
     * Segue a mesma lógica híbrida da Perspectiva (Indicadores + Planos), usando os pesos da Perspectiva pai.
     * 
     * @param \App\Models\StrategicPlanning\Objetivo $objetivo
     * @param int $ano
     * @return float Percentual (0-100)
     */
    public function calcularAtingimentoObjetivo(\App\Models\StrategicPlanning\Objetivo $objetivo, int $ano): float
    {
        // Pesos vêm da Perspectiva Pai
        $perspectiva = $objetivo->perspectiva;
        $pesoInd = $perspectiva->num_peso_indicadores ?? 100;
        $pesoPlan = $perspectiva->num_peso_planos ?? 0;

        // 1. Calcular Média Indicadores do Objetivo
        $somaAtingInd = 0;
        $totalInd = 0;
        
        foreach ($objetivo->indicadores as $ind) {
            $ating = $ind->calcularAtingimento($ano); 
            $somaAtingInd += $ating;
            $totalInd++;
        }
        
        $mediaIndicadores = $totalInd > 0 ? ($somaAtingInd / $totalInd) : 0;

        // 2. Calcular Média Planos do Objetivo (Ponderada)
        $somaProgressoPlan = 0;
        $somaPesoPlan = 0;
        
        foreach ($objetivo->planosAcao as $plano) {
            // Filtrar entregas do ano
            $entregasAno = $plano->entregas->filter(function($entrega) use ($ano) {
                return $entrega->dte_prazo && 
                       $entrega->dte_prazo->year == $ano &&
                       $entrega->bln_status !== 'Cancelado' &&
                       $entrega->cod_entrega_pai === null;
            });

            if ($entregasAno->isEmpty()) continue;

            foreach ($entregasAno as $entrega) {
                $statusDecimal = match($entrega->bln_status) {
                    'Concluído' => 1.0, 
                    'Em Andamento' => 0.5, 
                    'Suspenso' => 0.25, 
                    default => 0.0
                };
                $peso = $entrega->num_peso > 0 ? $entrega->num_peso : 1;
                
                $somaProgressoPlan += ($peso * $statusDecimal);
                $somaPesoPlan += $peso;
            }
        }
        
        $mediaPlanos = $somaPesoPlan > 0 ? ($somaProgressoPlan / $somaPesoPlan) * 100 : 0;

        // 3. Cálculo Final Híbrido
        $atingimentoFinal = 0;
        $somaPesosConfig = $pesoInd + $pesoPlan;

        if ($somaPesosConfig > 0) {
            $atingimentoFinal = (($mediaIndicadores * $pesoInd) + ($mediaPlanos * $pesoPlan)) / $somaPesosConfig;
        }
        
        return round($atingimentoFinal, 1);
    }
}
