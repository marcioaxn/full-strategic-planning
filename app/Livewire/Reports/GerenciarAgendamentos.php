<?php

namespace App\Livewire\Reports;

use App\Models\Reports\RelatorioAgendado;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class GerenciarAgendamentos extends Component
{
    use WithPagination;

    protected $listeners = ['agendamentoCriado' => '$refresh'];

    public function toggleStatus($id)
    {
        $agendamento = RelatorioAgendado::where('user_id', Auth::id())->findOrFail($id);
        $agendamento->bln_ativo = !$agendamento->bln_ativo;
        $agendamento->save();
        
        $status = $agendamento->bln_ativo ? 'ativado' : 'desativado';
        $this->dispatch('notify', ['type' => 'success', 'message' => "Agendamento {$status} com sucesso."]);
    }

    public function delete($id)
    {
        $agendamento = RelatorioAgendado::where('user_id', Auth::id())->findOrFail($id);
        $agendamento->delete();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Agendamento removido.']);
    }

    public function render()
    {
        $agendamentos = RelatorioAgendado::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('livewire.reports.gerenciar-agendamentos', [
            'agendamentos' => $agendamentos
        ]);
    }
}