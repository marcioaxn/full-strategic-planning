<?php

namespace App\Livewire\RiskManagement;

use App\Models\Risco;
use App\Models\RiscoOcorrencia;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class RegistrarOcorrencias extends Component
{
    use AuthorizesRequests;

    public $risco;
    public $ocorrencias = [];

    public bool $showModal = false;
    public $ocorrenciaId;

    // Form Ocorrência
    public $form = [
        'dte_ocorrencia' => '',
        'txt_descricao' => '',
        'num_impacto_real' => 3,
        'txt_acoes_tomadas' => '',
        'txt_licoes_aprendidas' => '',
    ];

    public function mount($riscoId)
    {
        $this->risco = Risco::findOrFail($riscoId);
        $this->authorize('view', $this->risco);
        $this->carregarDados();
    }

    public function carregarDados()
    {
        $this->ocorrencias = RiscoOcorrencia::where('cod_risco', $this->risco->cod_risco)
            ->orderBy('dte_ocorrencia', 'desc')
            ->get();
    }

    public function create()
    {
        $this->authorize('update', $this->risco);
        $this->resetForm();
        $this->form['dte_ocorrencia'] = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function edit($id)
    {
        $o = RiscoOcorrencia::findOrFail($id);
        $this->authorize('update', $this->risco);

        $this->ocorrenciaId = $id;
        $this->form = [
            'dte_ocorrencia' => $o->dte_ocorrencia?->format('Y-m-d'),
            'txt_descricao' => $o->txt_descricao,
            'num_impacto_real' => $o->num_impacto_real,
            'txt_acoes_tomadas' => $o->txt_acoes_tomadas,
            'txt_licoes_aprendidas' => $o->txt_licoes_aprendidas,
        ];

        $this->showModal = true;
    }

    public function save()
    {
        $this->authorize('update', $this->risco);

        $this->validate([
            'form.dte_ocorrencia' => 'required|date',
            'form.txt_descricao' => 'required|string|max:1000',
            'form.num_impacto_real' => 'required|integer|min:1|max:5',
        ]);

        $data = $this->form;
        $data['cod_risco'] = $this->risco->cod_risco;

        RiscoOcorrencia::updateOrCreate(
            ['cod_ocorrencia' => $this->ocorrenciaId],
            $data
        );

        $this->showModal = false;
        $this->carregarDados();
        session()->flash('status', 'Ocorrência registrada com sucesso!');
    }

    public function delete($id)
    {
        $this->authorize('update', $this->risco);
        RiscoOcorrencia::findOrFail($id)->delete();
        $this->carregarDados();
    }

    public function resetForm()
    {
        $this->ocorrenciaId = null;
        $this->form = [
            'dte_ocorrencia' => '',
            'txt_descricao' => '',
            'num_impacto_real' => 3,
            'txt_acoes_tomadas' => '',
            'txt_licoes_aprendidas' => '',
        ];
    }

    public function render()
    {
        return view('livewire.risco.registrar-ocorrencias');
    }
}
