<?php

namespace App\Services;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\ActionPlan\PlanoDeAcao;

class PeiGuidanceService
{
    /**
     * Analyze the completeness of the current or specified PEI.
     *
     * @param string|null $peiId Optional PEI ID. If null, tries to find an active one.
     * @return array
     */
    public function analyzeCompleteness(?string $peiId = null): array
    {
        // 1. Prioritize passed ID, then Session, then First Active
        $peiId = $peiId ?? session('pei_selecionado_id');

        $pei = $peiId 
            ? PEI::with('identidadeEstrategica')->find($peiId) 
            : PEI::ativos()->with('identidadeEstrategica')->first();

        if (!$pei) {
            // Check if there are ANY PEIs (future/past)
            $anyPei = PEI::exists();
            
            return [
                'status' => 'critical',
                'current_phase' => 'ciclo',
                'progress' => 0,
                'phases' => $this->getEmptyPhasesStructure(),
                'message' => $anyPei 
                    ? 'Nenhum Planejamento Estratégico (PEI) vigente encontrado. Ative ou crie um novo ciclo.' 
                    : 'Bem-vindo! Vamos começar definindo o ciclo do seu Planejamento Estratégico (PEI).',
                'action_route' => 'pei.ciclos',
                'action_label' => 'Definir Ciclo PEI'
            ];
        }

        // Initialize Phases Structure
        $phases = $this->getEmptyPhasesStructure();
        
        // --- PHASE 1: Ciclo PEI (Always valid if we have a $pei object) ---
        $phases['ciclo']['status'] = 'completed';
        $phases['ciclo']['label'] = 'Ciclo ' . $pei->num_ano_inicio_pei . '-' . $pei->num_ano_fim_pei;
        
        // --- PHASE 2: Identidade (Missão, Visão, Valores) ---
        $identidade = $pei->identidadeEstrategica->first();
        $hasIdentity = $identidade && 
                       strlen(trim($identidade->dsc_missao ?? '')) > 10 && 
                       strlen(trim($identidade->dsc_visao ?? '')) > 10; 
        
        if ($hasIdentity) {
            $phases['identidade']['status'] = 'completed';
        } else {
            return $this->buildResponse($phases, 'identidade', 20, $pei, 
                'Defina a identidade da sua organização: Missão, Visão e Valores.', 
                'pei.index', 'Definir Identidade');
        }

        // --- PHASE 3: Perspectivas BSC ---
        $perspectivasCount = $pei->perspectivas()->count();
        $phases['perspectivas']['count'] = $perspectivasCount;

        if ($perspectivasCount >= 4) { // BSC usually has 4
            $phases['perspectivas']['status'] = 'completed';
        } elseif ($perspectivasCount > 0) {
            $phases['perspectivas']['status'] = 'in_progress'; // Partial
            return $this->buildResponse($phases, 'perspectivas', 35, $pei, 
                'O BSC padrão sugere 4 perspectivas. Você tem ' . $perspectivasCount . '.', 
                'pei.perspectivas', 'Completar Perspectivas');
        } else {
            return $this->buildResponse($phases, 'perspectivas', 30, $pei, 
                'Crie as Perspectivas do BSC (Financeira, Clientes, Processos, Aprendizado).', 
                'pei.perspectivas', 'Criar Perspectivas');
        }

        // --- PHASE 4: Objetivos Estratégicos ---
        // Objectives are linked to Perspectives.
        $perspectivaIds = $pei->perspectivas()->pluck('cod_perspectiva');
        $objetivosCount = Objetivo::whereIn('cod_perspectiva', $perspectivaIds)->count();
        $phases['objetivos']['count'] = $objetivosCount;

        if ($objetivosCount > 0) {
            // Check if ALL perspectives have at least one objective (Ideal scenario)
            $perspectivasWithObj = Objetivo::whereIn('cod_perspectiva', $perspectivaIds)
                ->distinct('cod_perspectiva')
                ->count('cod_perspectiva');
            
            if ($perspectivasWithObj == $perspectivasCount) {
                 $phases['objetivos']['status'] = 'completed';
            } else {
                $phases['objetivos']['status'] = 'in_progress';
                return $this->buildResponse($phases, 'objetivos', 50, $pei, 
                    'Algumas perspectivas ainda não possuem objetivos definidos.', 
                    'pei.objetivos', 'Revisar Objetivos');
            }
        } else {
            return $this->buildResponse($phases, 'objetivos', 40, $pei, 
                'Defina os Objetivos Estratégicos para cada perspectiva.', 
                'pei.objetivos', 'Criar Objetivos');
        }

        // --- PHASE 5: Indicadores (KPIs) ---
        // Indicators are linked to Objectives.
        // We need to check if objectives have indicators.
        $objetivoIds = Objetivo::whereIn('cod_perspectiva', $perspectivaIds)->pluck('cod_objetivo');
        $indicadoresCount = Indicador::whereIn('cod_objetivo', $objetivoIds)->count();
        $phases['indicadores']['count'] = $indicadoresCount;

        if ($indicadoresCount > 0) {
             // Ideally every objective should have an indicator
             $objetivosWithInd = Indicador::whereIn('cod_objetivo', $objetivoIds)
                ->distinct('cod_objetivo')
                ->count('cod_objetivo');
            
             if ($objetivosWithInd >= $objetivosCount) {
                $phases['indicadores']['status'] = 'completed';
             } else {
                 // Not blocking, but warning
                 $phases['indicadores']['status'] = 'in_progress';
                 // We let it pass to Planos de Ação but keep status as in_progress
             }
        } else {
            return $this->buildResponse($phases, 'indicadores', 60, $pei, 
                'Defina Indicadores (KPIs) para mensurar seus objetivos.', 
                'indicadores.index', 'Criar Indicadores');
        }

        // --- PHASE 6: Planos de Ação ---
        // Action Plans linked to Objectives
        $planosCount = PlanoDeAcao::whereIn('cod_objetivo', $objetivoIds)->count();
        $phases['planos']['count'] = $planosCount;

        if ($planosCount > 0) {
            $phases['planos']['status'] = 'completed';
        } else {
            return $this->buildResponse($phases, 'planos', 80, $pei, 
                'Crie Planos de Ação para tirar a estratégia do papel.', 
                'planos.index', 'Criar Planos');
        }

        // --- ALL COMPLETED ---
        return [
            'status' => 'success',
            'current_phase' => 'monitoramento',
            'progress' => 100,
            'phases' => $phases,
            'message' => 'Parabéns! Seu Planejamento Estratégico está estruturado. Agora é hora de monitorar.',
            'action_route' => 'dashboard',
            'action_label' => 'Ir para Dashboard'
        ];
    }

    private function buildResponse(array $phases, string $currentPhaseKey, int $progress, $pei, string $msg, string $route, string $label): array
    {
        // Mark current phase as active/in_progress if not already
        if ($phases[$currentPhaseKey]['status'] === 'locked') {
            $phases[$currentPhaseKey]['status'] = 'active'; 
        }

        return [
            'status' => 'warning', // In progress
            'current_phase' => $currentPhaseKey,
            'progress' => $progress,
            'phases' => $phases,
            'pei_cycle' => $pei->num_ano_inicio_pei . '-' . $pei->num_ano_fim_pei,
            'message' => $msg,
            'action_route' => $route,
            'action_label' => $label
        ];
    }

    private function getEmptyPhasesStructure(): array
    {
        return [
            'ciclo' => ['name' => 'Ciclo PEI', 'status' => 'locked', 'icon' => 'calendar-range'],
            'identidade' => ['name' => 'Identidade', 'status' => 'locked', 'icon' => 'fingerprint'],
            'perspectivas' => ['name' => 'Perspectivas', 'status' => 'locked', 'icon' => 'layers'],
            'objetivos' => ['name' => 'Objetivos', 'status' => 'locked', 'icon' => 'bullseye'],
            'indicadores' => ['name' => 'Indicadores', 'status' => 'locked', 'icon' => 'graph-up-arrow'],
            'planos' => ['name' => 'Planos de Ação', 'status' => 'locked', 'icon' => 'kanban'],
        ];
    }
}
