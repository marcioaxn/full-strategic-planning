<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Organization;
use App\Models\PEI\ObjetivoEstrategico;
use App\Models\PEI\PlanoDeAcao;
use App\Models\PEI\Indicador;
use App\Models\Risco;
use Livewire\Attributes\Layout;

class Index extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.dashboard.index', [
            'totalOrganizacoes' => Organization::count(),
            'totalObjetivos' => ObjetivoEstrategico::count(),
            'totalPlanos' => PlanoDeAcao::count(),
            'totalIndicadores' => Indicador::count(),
            'planosAtrasados' => PlanoDeAcao::where('dsc_status', '!=', 'Concluído')
                                    ->where('dte_fim', '<', now())
                                    ->count(),
            // Placeholder: Indicadores sem lançamento requereria verificar a tabela de evolução
            'indicadoresSemLancamento' => 0, 
            'riscosCriticos' => Risco::whereRaw('(num_probabilidade * num_impacto) >= 15')->count(),
        ]);
    }
}
