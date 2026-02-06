<?php

namespace App\Services;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\Entrega;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use Illuminate\Support\Facades\Log;

/**
 * Service responsável pelo cálculo automático de indicadores
 * baseado no progresso ponderado das entregas de um Plano de Ação.
 * 
 * Fórmula de Cálculo:
 * Progresso = Σ (Peso da Entrega × Status da Entrega) / Soma Total dos Pesos
 * 
 * Status mapeados para percentual:
 * - Concluído: 100%
 * - Em Andamento: 50%
 * - Suspenso: 25%
 * - Não Iniciado: 0%
 * - Cancelado: 0% (não contabiliza)
 * 
 * @author SEAE Strategic Planning Team
 * @since 2026-02
 */
class IndicadorCalculoService
{
    /**
     * Mapeamento de status para percentual de conclusão.
     * Permite customização futura via configuração.
     */
    const STATUS_PERCENTUAL = [
        'Concluído'     => 100,
        'Em Andamento'  => 50,
        'Suspenso'      => 25,
        'Não Iniciado'  => 0,
        'Cancelado'     => 0,  // Cancelado não contribui para o cálculo
    ];

    /**
     * Calcula o progresso ponderado de um Plano de Ação.
     * 
     * @param PlanoDeAcao $plano O plano de ação a calcular
     * @param bool $apenasRaiz Se true, considera apenas entregas raiz (sem pai)
     * @return float Percentual de progresso (0-100)
     */
    public function calcularProgressoPlano(PlanoDeAcao $plano, bool $apenasRaiz = true): float
    {
        // Buscar entregas ativas (não deletadas, não arquivadas)
        $query = $plano->entregas()
            ->where('bln_arquivado', false)
            ->whereNull('deleted_at');

        if ($apenasRaiz) {
            $query->whereNull('cod_entrega_pai');
        }

        $entregas = $query->get();

        if ($entregas->isEmpty()) {
            return 0;
        }

        // Verificar se plano usa pesos ou não
        $somaPesos = $entregas->sum('num_peso');
        $usaPesos = $somaPesos > 0;

        $progressoPonderado = 0;
        $divisor = 0;

        foreach ($entregas as $entrega) {
            // Ignorar entregas canceladas no cálculo
            if ($entrega->bln_status === 'Cancelado') {
                continue;
            }

            // Obter percentual do status
            $percentualStatus = self::STATUS_PERCENTUAL[$entrega->bln_status] ?? 0;

            // Se a entrega tem sub-entregas, calcular progresso delas recursivamente
            if ($entrega->hasSubEntregas()) {
                $percentualStatus = $this->calcularProgressoEntregaComFilhos($entrega);
            }

            if ($usaPesos && $entrega->num_peso > 0) {
                // Cálculo ponderado
                $progressoPonderado += ($entrega->num_peso * $percentualStatus);
                $divisor += $entrega->num_peso;
            } else {
                // Peso igual (fallback)
                $progressoPonderado += $percentualStatus;
                $divisor += 100; // Considera peso 100 para cada entrega
            }
        }

        if ($divisor === 0) {
            return 0;
        }

        return round(($progressoPonderado / $divisor) * 100, 2);
    }

    /**
     * Calcula o progresso de uma entrega considerando suas sub-entregas.
     * 
     * @param Entrega $entrega A entrega pai
     * @return float Percentual de progresso (0-100)
     */
    protected function calcularProgressoEntregaComFilhos(Entrega $entrega): float
    {
        $subEntregas = $entrega->subEntregas()
            ->where('bln_arquivado', false)
            ->whereNull('deleted_at')
            ->where('bln_status', '!=', 'Cancelado')
            ->get();

        if ($subEntregas->isEmpty()) {
            return self::STATUS_PERCENTUAL[$entrega->bln_status] ?? 0;
        }

        $somaPesos = $subEntregas->sum('num_peso');
        $usaPesos = $somaPesos > 0;

        $progressoPonderado = 0;
        $divisor = 0;

        foreach ($subEntregas as $sub) {
            $percentualStatus = self::STATUS_PERCENTUAL[$sub->bln_status] ?? 0;

            // Recursão para sub-sub-entregas
            if ($sub->hasSubEntregas()) {
                $percentualStatus = $this->calcularProgressoEntregaComFilhos($sub);
            }

            if ($usaPesos && $sub->num_peso > 0) {
                $progressoPonderado += ($sub->num_peso * $percentualStatus);
                $divisor += $sub->num_peso;
            } else {
                $progressoPonderado += $percentualStatus;
                $divisor += 100;
            }
        }

        if ($divisor === 0) {
            return 0;
        }

        return round(($progressoPonderado / $divisor) * 100, 2);
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
}
