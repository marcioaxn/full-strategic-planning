<?php

namespace App\Livewire\Reports;

use App\Models\Reports\RelatorioAgendado;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class AgendarRelatorio extends Component
{
    public $tipoRelatorio;
    public $frequencia = 'mensal';
    public $filtros = [];
    public $dataInicio;

    public $showModal = false;

    protected $listeners = ['abrirAgendamento' => 'carregar'];

    public function carregar($tipo, $filtros)
    {
        $this->tipoRelatorio = $tipo;
        $this->filtros = $filtros;
        $this->dataInicio = now()->addDay()->format('Y-m-d H:i');
        $this->showModal = true;
    }

    public function salvar()
    {
        $this->validate([
            'frequencia' => 'required|in:diario,semanal,mensal',
            'dataInicio' => 'required|date|after:now',
        ]);

        RelatorioAgendado::create([
            'user_id' => Auth::id(),
            'dsc_tipo_relatorio' => $this->tipoRelatorio,
            'dsc_frequencia' => $this->frequencia,
            'txt_filtros' => $this->filtros,
            'dte_proxima_execucao' => $this->dataInicio,
            'bln_ativo' => true,
        ]);

        $this->showModal = false;
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Relatório agendado com sucesso!'
        ]);
    }

    public function render()
    {
        return view('livewire.reports.agendar-relatorio');
    }
}