<?php

namespace App\Observers;

use App\Models\ActionPlan\Entrega;
use App\Services\IndicadorCalculoService;
use Illuminate\Support\Facades\Log;

/**
 * Observer para atualização automática de indicadores
 * quando entregas são modificadas.
 * 
 * Sempre que uma entrega é criada, atualizada ou deletada,
 * os indicadores do tipo 'action_plan' vinculados ao plano
 * são recalculados automaticamente.
 * 
 * @author SEAE Strategic Planning Team
 * @since 2026-02
 */
class EntregaObserver
{
    protected IndicadorCalculoService $calculoService;

    public function __construct(IndicadorCalculoService $calculoService)
    {
        $this->calculoService = $calculoService;
    }

    /**
     * Handle the Entrega "created" event.
     */
    public function created(Entrega $entrega): void
    {
        $this->recalcularIndicadores($entrega);
    }

    /**
     * Handle the Entrega "updated" event.
     * 
     * Só recalcula se campos relevantes foram alterados:
     * - bln_status (status da entrega)
     * - num_peso (peso da entrega)
     * - cod_entrega_pai (hierarquia)
     * - bln_arquivado (visibilidade)
     */
    public function updated(Entrega $entrega): void
    {
        $camposRelevantes = ['bln_status', 'num_peso', 'cod_entrega_pai', 'bln_arquivado'];
        
        // Verificar se algum campo relevante foi alterado
        $foiAlterado = false;
        foreach ($camposRelevantes as $campo) {
            if ($entrega->wasChanged($campo)) {
                $foiAlterado = true;
                break;
            }
        }

        if ($foiAlterado) {
            $this->recalcularIndicadores($entrega);
        }
    }

    /**
     * Handle the Entrega "deleted" event (soft delete).
     */
    public function deleted(Entrega $entrega): void
    {
        $this->recalcularIndicadores($entrega);
    }

    /**
     * Handle the Entrega "restored" event.
     */
    public function restored(Entrega $entrega): void
    {
        $this->recalcularIndicadores($entrega);
    }

    /**
     * Recalcula os indicadores automáticos do plano da entrega.
     */
    protected function recalcularIndicadores(Entrega $entrega): void
    {
        try {
            // Verificar se tem plano vinculado
            if (!$entrega->cod_plano_de_acao) {
                return;
            }

            // Carregar plano se necessário
            $plano = $entrega->planoDeAcao;
            if (!$plano) {
                return;
            }

            // Atualizar indicadores do plano
            $count = $this->calculoService->atualizarIndicadoresDoPlano($plano);

            if ($count > 0) {
                Log::info("EntregaObserver: {$count} indicador(es) recalculado(s) para o plano '{$plano->dsc_plano_de_acao}'");
            }
        } catch (\Exception $e) {
            // Log do erro mas não interrompe a operação
            Log::error("EntregaObserver: Erro ao recalcular indicadores - " . $e->getMessage());
        }
    }
}
