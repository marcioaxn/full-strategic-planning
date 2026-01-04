<?php

namespace App\Livewire\Dashboard;

use App\Services\PeiGuidanceService;
use Livewire\Component;

class PeiChecklist extends Component
{
    public $guidance;
    public bool $aiEnabled = false;

    protected $listeners = [
        'peiSelecionado' => 'refreshGuidance'
    ];

    public function mount(PeiGuidanceService $service)
    {
        $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', true);
        $this->guidance = $service->analyzeCompleteness();
    }

    public function refreshGuidance($id = null, PeiGuidanceService $service = null)
    {
        $service = $service ?? app(PeiGuidanceService::class);
        $this->guidance = $service->analyzeCompleteness($id);
    }

    public function render()
    {
        return view('livewire.dashboard.pei-checklist');
    }
}
