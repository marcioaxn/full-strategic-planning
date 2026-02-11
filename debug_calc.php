<?php
$ano = 2026;
// Encontrar a perspectiva pelo nome exato (pode haver espaços ou caracteres invisíveis, usar like ou first)
$persp = \App\Models\StrategicPlanning\Perspectiva::where('dsc_perspectiva', 'ILIKE', '%Processos Internos e Governança%')->first();

if (!$persp) {
    echo "ERRO: Perspectiva 'Processos Internos e Governança' não encontrada.\n";
    return;
}

echo "=== DIAGNÓSTICO DE CÁLCULO ===\n";
echo "Perspectiva: {$persp->dsc_perspectiva} (ID: {$persp->cod_perspectiva})\n";
echo "Configuração de Pesos: Indicadores={$persp->num_peso_indicadores}%, Planos={$persp->num_peso_planos}%\n";

$service = app(\App\Services\IndicadorCalculoService::class);
$global = $service->calcularAtingimentoPerspectiva($persp, $ano);

echo ">> Atingimento TOTAL Calculado: {$global}%\n\n";

echo "--- Análise Detalhada ---\n";

$somaAtingInd = 0;
$totalInd = 0;
$somaProgressoPlan = 0;
$somaPesoPlan = 0;

foreach($persp->objetivos as $obj) {
    echo "\nObjetivo: {$obj->dsc_objetivo} (ID: {$obj->cod_objetivo})\n";
    
    // 1. Indicadores
    foreach($obj->indicadores as $ind) {
        $val = $ind->calcularAtingimento($ano);
        echo "   [KPI] {$ind->dsc_indicador} (ID: {$ind->cod_indicador}): {$val}%\n";
        $somaAtingInd += $val;
        $totalInd++;
    }

    // 2. Planos
    foreach($obj->planosAcao as $plano) {
        // Lógica de filtro do Service
        $entregasAno = $plano->entregas->filter(function($entrega) use ($ano) {
            return $entrega->dte_prazo && 
                   $entrega->dte_prazo->year == $ano &&
                   $entrega->bln_status !== 'Cancelado' &&
                   $entrega->cod_entrega_pai === null;
        });

        if ($entregasAno->isEmpty()) {
            echo "   [PLANO] {$plano->dsc_plano_de_acao} (ID: {$plano->cod_plano_de_acao}): Sem entregas em $ano (Ignorado)\n";
            continue;
        }

        echo "   [PLANO] {$plano->dsc_plano_de_acao} (ID: {$plano->cod_plano_de_acao}):\n";
        
        $progressoPlano = 0;
        $pesoPlanoacum = 0;
        
        foreach ($entregasAno as $entrega) {
            $statusDecimal = match($entrega->bln_status) {
                'Concluído' => 1.0, 
                'Em Andamento' => 0.5, 
                'Suspenso' => 0.25, 
                default => 0.0
            };
            $peso = $entrega->num_peso > 0 ? $entrega->num_peso : 1;
            
            $contrib = $peso * $statusDecimal;
            echo "      -> Entrega '{$entrega->dsc_entrega}' ({$entrega->bln_status}): Peso $peso * Status $statusDecimal = $contrib\n";
            
            $somaProgressoPlan += $contrib; // Acumulador GLOBAL da Perspectiva
            $somaPesoPlan += $peso; // Acumulador GLOBAL
            
            $progressoPlano += $contrib;
            $pesoPlanoacum += $peso;
        }
        $mediaPlanoLocal = $pesoPlanoacum > 0 ? ($progressoPlano / $pesoPlanoacum) * 100 : 0;
        echo "      => Progresso do Plano: " . number_format($mediaPlanoLocal, 2) . "%\n";
    }
}

echo "\n--- Resumo Final ---\n";
$mediaInd = $totalInd > 0 ? ($somaAtingInd / $totalInd) : 0;
echo "Média Indicadores: $mediaInd% (Total Index: $totalInd)\n";

$mediaPlan = $somaPesoPlan > 0 ? ($somaProgressoPlan / $somaPesoPlan) * 100 : 0;
echo "Média Planos (Ponderada Global): $mediaPlan% (Soma Pesos: $somaPesoPlan)\n";

$final = (($mediaInd * $persp->num_peso_indicadores) + ($mediaPlan * $persp->num_peso_planos)) / ($persp->num_peso_indicadores + $persp->num_peso_planos);
echo "Cálculo Final: (($mediaInd * {$persp->num_peso_indicadores}) + ($mediaPlan * {$persp->num_peso_planos})) / 100 = $final\n";
