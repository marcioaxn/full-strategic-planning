<?php

namespace App\Livewire\Deliverables;

use App\Models\ActionPlan\Entrega;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MinhasEntregas extends Component
{
    public string $filtroStatus = '';

    public string $filtroPrioridade = '';

    public string $busca = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Entrega::class);
    }

    public function render()
    {
        $userId = Auth::id();
        $peiId = Session::get('pei_selecionado_id');

        $query = Entrega::whereHas('responsaveis', fn ($q) => $q->where('users.id', $userId))
            ->where('bln_arquivado', false)
            ->with(['planoDeAcao.objetivo.perspectiva', 'responsaveis']);

        if ($peiId) {
            $query->whereHas('planoDeAcao.objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $peiId));
        }

        // Sem filtro, a tela é a fila de trabalho (sem as concluídas). Com o
        // filtro "Concluído", mostra as concluídas — antes vinha sempre vazio,
        // porque a exclusão das concluídas valia também com o filtro.
        if ($this->filtroStatus) {
            $query->where('bln_status', $this->filtroStatus);
        } else {
            $query->where('bln_status', '!=', 'Concluído');
        }

        if ($this->filtroPrioridade) {
            $query->where('cod_prioridade', $this->filtroPrioridade);
        }

        if ($this->busca) {
            $query->where('dsc_entrega', 'ilike', '%'.$this->busca.'%');
        }

        $entregas = $query->orderBy('dte_prazo')->get()->groupBy(fn ($e) => $e->planoDeAcao?->cod_plano_de_acao);

        return view('livewire.entregas.minhas-entregas', [
            'entregasAgrupadas' => $entregas,
            'statusOptions' => Entrega::STATUS_OPTIONS,
            'prioridades' => Entrega::PRIORIDADE_OPTIONS,
            'totalPendente' => $entregas->flatten()->reject(fn (Entrega $e) => $e->isConcluida())->count(),
            // Mesmo critério do quadro (Entrega::isAtrasada).
            'totalAtrasadas' => $entregas->flatten()->filter(fn (Entrega $e) => $e->isAtrasada())->count(),
        ]);
    }
}
