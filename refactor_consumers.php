<?php

$replacements = [
    // === 1. Performance Indicators (Prioridade: Específicos do PEI que saíram de lá) ===
    'App\Models\PEI\Indicador' => 'App\Models\PerformanceIndicators\Indicador',
    'App\Models\PEI\MetaPorAno' => 'App\Models\PerformanceIndicators\MetaPorAno',
    'App\Models\PEI\EvolucaoIndicador' => 'App\Models\PerformanceIndicators\EvolucaoIndicador',
    'App\Models\PEI\LinhaBaseIndicador' => 'App\Models\PerformanceIndicators\LinhaBaseIndicador',
    'App\Livewire\Indicador' => 'App\Livewire\PerformanceIndicators',

    // === 2. Action Plan (Específicos do PEI que saíram de lá) ===
    'App\Models\PEI\PlanoDeAcao' => 'App\Models\ActionPlan\PlanoDeAcao',
    'App\Models\PEI\Entrega' => 'App\Models\ActionPlan\Entrega', // Pega Entrega, EntregaAnexo, etc se prefixo bater
    'App\Models\PEI\TipoExecucao' => 'App\Models\ActionPlan\TipoExecucao',
    'App\Models\Acao' => 'App\Models\ActionPlan\Acao',
    'App\Livewire\PlanoAcao' => 'App\Livewire\ActionPlan',
    // Correção do Board renomeado
    'App\Livewire\Entregas\NotionBoard' => 'App\Livewire\Deliverables\DeliverablesBoard', 
    'App\Livewire\Entregas' => 'App\Livewire\Deliverables',

    // === 3. Risk Management ===
    'App\Models\Risco' => 'App\Models\RiskManagement\Risco', // Pega Risco, RiscoMitigacao...
    'App\Livewire\Risco' => 'App\Livewire\RiskManagement',

    // === 4. Strategic Planning (O que sobrou do PEI vai para cá) ===
    'App\Models\PEI' => 'App\Models\StrategicPlanning',
    'App\Livewire\PEI' => 'App\Livewire\StrategicPlanning',

    // === 5. Organization ===
    'App\Livewire\Organizacao' => 'App\Livewire\Organization',
    
    // === 6. User Management ===
    'App\Livewire\Usuario' => 'App\Livewire\UserManagement',

    // === 7. Reports ===
    'App\Livewire\Relatorio' => 'App\Livewire\Reports',
    'App\Http\Controllers\RelatorioController' => 'App\Http\Controllers\Reports\RelatorioController',
];

$directories = [
    __DIR__ . '/app',
    __DIR__ . '/resources/views',
    __DIR__ . '/database/seeders',
    __DIR__ . '/database/factories',
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($directories as $dir) {
    if (!is_dir($dir)) continue; 
    
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    
    foreach ($files as $file) {
        if ($file->isDir()) continue;
        // Processar PHP e Blade
        if (!in_array($file->getExtension(), ['php', 'blade.php'])) continue;
        
        // Ignorar o próprio script e pastas de vendor/storage se caírem aqui
        if (strpos($file->getPathname(), 'vendor') !== false) continue;
        if (strpos($file->getPathname(), 'storage') !== false) continue;
        if ($file->getFilename() === 'refactor_consumers.php') continue;

        $content = file_get_contents($file->getPathname());
        $originalContent = $content;

        foreach ($replacements as $old => $new) {
            // Substituição simples de string (escapando backslashes para regex se necessário, 
            // mas str_replace é mais seguro para namespaces literais)
            $content = str_replace($old, $new, $content);
            
            // Também verificar versão com barras invertidas duplas (comum em strings json ou regex dentro do código)
            $oldEscaped = str_replace('\\', '\\\\', $old); // Escaping for regex pattern
            $newEscaped = str_replace('\\', '\\\\', $new); // Escaping for replacement string
            $content = str_replace($oldEscaped, $newEscaped, $content);
        }

        if ($content !== $originalContent) {
            file_put_contents($file->getPathname(), $content);
            echo "Updated: " . $file->getPathname() . "\n";
        }
    }
}
