<?php

namespace App\Livewire\Dashboard;

use App\Services\PeiGuidanceService;
use Livewire\Component;

class PeiChecklist extends Component
{
    public $guidance;
    public bool $aiEnabled = false;
    public bool $isDismissed = false;

    protected $listeners = [
        'peiSelecionado' => 'refreshGuidance'
    ];

    public function mount(PeiGuidanceService $service)
    {
        $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', true);
        $this->guidance = $service->analyzeCompleteness();
        
        // Verifica se o usuário já arquivou este mentor para o PEI atual
        $peiId = $this->guidance['pei_id'] ?? 'default';
        $this->isDismissed = session("mentor_dismissed_{$peiId}", false);
    }

    public function dismiss()
    {
        $peiId = $this->guidance['pei_id'] ?? 'default';
        session(["mentor_dismissed_{$peiId}" => true]);
        $this->isDismissed = true;
    }

    public function refreshGuidance($id = null, PeiGuidanceService $service = null)
    {
        $service = $service ?? app(PeiGuidanceService::class);
        $this->guidance = $service->analyzeCompleteness($id);
        
        // Ao trocar de PEI, verifica novamente se está arquivado
        $peiId = $id ?? ($this->guidance['pei_id'] ?? 'default');
        $this->isDismissed = session("mentor_dismissed_{$peiId}", false);
        
        // Se o progresso não for 100%, ele deve aparecer de novo
        if ($this->guidance['progress'] < 100) {
            $this->isDismissed = false;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.pei-checklist');
    }
}
