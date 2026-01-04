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

        if ($perspectivasCount == 0) {
            $phases['perspectivas']['status'] = 'active';
            return $this->buildResponse($phases, 'perspectivas', 20, $pei, 
                'Crie as Perspectivas do BSC (ex: Financeira, Clientes, Processos e Aprendizado).', 
                'pei.perspectivas', 'Criar Perspectivas');
        }

        // Se tem pelo menos 1, marcamos como completa mas com mensagem de orientação se < 4
        $phases['perspectivas']['status'] = 'completed';
        $perspectivaWarning = $perspectivasCount < 4 ? " (Recomendamos 4 pilares, você tem {$perspectivasCount})" : "";

        // --- PHASE 4: Objetivos Estratégicos ---
        $perspectivaIds = $pei->perspectivas()->pluck('cod_perspectiva');
        $objetivosCount = Objetivo::whereIn('cod_perspectiva', $perspectivaIds)->count();
        $phases['objetivos']['count'] = $objetivosCount;

        if ($objetivosCount == 0) {
            $phases['objetivos']['status'] = 'active';
            return $this->buildResponse($phases, 'objetivos', 40, $pei, 
                "Perspectiva registrada!{$perspectivaWarning} Agora, defina os Objetivos Estratégicos.", 
                'objetivos.index', 'Criar Objetivos');
        }

        // Verifica se TODAS as perspectivas têm pelo menos um objetivo (Ideal)
        $perspectivasComObjetivo = Objetivo::whereIn('cod_perspectiva', $perspectivaIds)
            ->distinct('cod_perspectiva')
            ->count('cod_perspectiva');

        $phases['objetivos']['status'] = 'completed';
        $objetivoWarning = $perspectivasComObjetivo < $perspectivasCount ? " (Algumas perspectivas ainda estão sem objetivos)" : "";

        // --- PHASE 5: Grau de Satisfação (NEW) ---
        $grausCount = \App\Models\StrategicPlanning\GrauSatisfacao::count();
        $phases['graus']['count'] = $grausCount;

        if ($grausCount == 0) {
            $phases['graus']['status'] = 'active';
            return $this->buildResponse($phases, 'graus', 50, $pei, 
                "Objetivos salvos!{$objetivoWarning} Agora, defina as cores e níveis do Grau de Satisfação.", 
                'graus-satisfacao.index', 'Configurar Níveis');
        }

        $phases['graus']['status'] = 'completed';

        // --- PHASE 6: Indicadores (KPIs) ---
        $objetivoIds = Objetivo::whereIn('cod_perspectiva', $perspectivaIds)->pluck('cod_objetivo');
        $indicadoresCount = Indicador::whereIn('cod_objetivo', $objetivoIds)->count();
        $phases['indicadores']['count'] = $indicadoresCount;

        if ($indicadoresCount == 0) {
            $phases['indicadores']['status'] = 'active';
            return $this->buildResponse($phases, 'indicadores', 65, $pei, 
                "Níveis de satisfação configurados! O próximo passo é criar Indicadores para medi-los.", 
                'indicadores.index', 'Criar Indicadores');
        }

        // Critério: Pelo menos um indicador para cada objetivo (Ideal)
        $objetivosComIndicador = Indicador::whereIn('cod_objetivo', $objetivoIds)
            ->distinct('cod_objetivo')
            ->count('cod_objetivo');

        $phases['indicadores']['status'] = 'completed';
        $indicadorWarning = $objetivosComIndicador < $objetivosCount ? " (Faltam indicadores para alguns objetivos)" : "";

        // --- PHASE 7: Planos de Ação ---
        $planosCount = PlanoDeAcao::whereIn('cod_objetivo', $objetivoIds)->count();
        $phases['planos']['count'] = $planosCount;

        if ($planosCount == 0) {
            $phases['planos']['status'] = 'active';
            return $this->buildResponse($phases, 'planos', 85, $pei, 
                "Indicadores registrados!{$indicadorWarning} Agora, crie Planos de Ação para tirar a estratégia do papel.", 
                'planos.index', 'Criar Planos');
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
            'pei_id' => $pei->cod_pei,
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
            'pei_id' => $pei->cod_pei,
            'pei_cycle' => $pei->num_ano_inicio_pei . '-' . $pei->num_ano_fim_pei,
            'message' => $msg,
            'action_route' => $route,
            'action_label' => $label,
            'next_step' => $this->getNextStepInfo($currentPhaseKey)
        ];
    }

    private function getNextStepInfo(string $currentPhase): ?array
    {
        $order = ['ciclo', 'identidade', 'perspectivas', 'objetivos', 'graus', 'indicadores', 'planos', 'monitoramento'];
        $index = array_search($currentPhase, $order);
        
        if ($index !== false && isset($order[$index + 1])) {
            $nextKey = $order[$index + 1];
            $phases = $this->getEmptyPhasesStructure();
            
            // Fallback for monitoramento which isn't in empty structure
            $name = $phases[$nextKey]['name'] ?? 'Monitoramento';
            
            return [
                'key' => $nextKey,
                'name' => $name
            ];
        }
        
        return null;
    }

    private function getEmptyPhasesStructure(): array
    {
        return [
            'ciclo' => ['name' => 'Ciclo PEI', 'status' => 'locked', 'icon' => 'calendar-range'],
            'identidade' => ['name' => 'Identidade', 'status' => 'locked', 'icon' => 'fingerprint'],
            'perspectivas' => ['name' => 'Perspectivas', 'status' => 'locked', 'icon' => 'layers'],
            'objetivos' => ['name' => 'Objetivos', 'status' => 'locked', 'icon' => 'bullseye'],
            'graus' => ['name' => 'Grau de Satisfação', 'status' => 'locked', 'icon' => 'palette'],
            'indicadores' => ['name' => 'Indicadores', 'status' => 'locked', 'icon' => 'graph-up-arrow'],
            'planos' => ['name' => 'Planos de Ação', 'status' => 'locked', 'icon' => 'kanban'],
        ];
    }
}
