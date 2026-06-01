<?php

namespace App\Livewire\Deliverables;

use App\Models\ActionPlan\Entrega;
use App\Models\StrategicPlanning\PEI;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class MinhasEntregas extends Component
{
    public string $filtroStatus = '';
    public string $filtroPrioridade = '';
    public string $busca = '';

    public function render()
    {
        $userId = Auth::id();
        $peiId  = Session::get('pei_selecionado_id');

        $query = Entrega::whereHas('responsaveis', fn($q) => $q->where('users.id', $userId))
            ->where('bln_status', '!=', 'Concluído')
            ->where('bln_arquivado', false)
            ->whereNull('deleted_at')
            ->with(['planoDeAcao.objetivo.perspectiva', 'responsaveis']);

        if ($peiId) {
            $query->whereHas('planoDeAcao.objetivo.perspectiva', fn($q) => $q->where('cod_pei', $peiId));
        }

        if ($this->filtroStatus) {
            $query->where('bln_status', $this->filtroStatus);
        }

        if ($this->filtroPrioridade) {
            $query->where('cod_prioridade', $this->filtroPrioridade);
        }

        if ($this->busca) {
            $query->where('dsc_entrega', 'ilike', '%'.$this->busca.'%');
        }

        $entregas = $query->orderBy('dte_prazo')->get()->groupBy(fn($e) => $e->planoDeAcao?->cod_plano_de_acao);

        return view('livewire.entregas.minhas-entregas', [
            'entregasAgrupadas' => $entregas,
            'statusOptions'     => Entrega::STATUS_OPTIONS,
            'prioridades'       => Entrega::PRIORIDADE_OPTIONS,
            'totalPendente'     => $entregas->flatten()->count(),
            'totalAtrasadas'    => $entregas->flatten()->filter(fn($e) => $e->dte_prazo && $e->dte_prazo->isPast())->count(),
        ]);
    }
}
