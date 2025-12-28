<?php

namespace App\Livewire\Risco;

use App\Models\Risco;
use App\Models\RiscoMitigacao;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class GerenciarMitigacoes extends Component
{
    use AuthorizesRequests;

    public $risco;
    public $mitigacoes = [];
    public $usuarios = [];

    public bool $showModal = false;
    public $mitigacaoId;

    // Form Mitigação
    public $form = [
        'dsc_tipo' => 'Prevenção',
        'txt_descricao' => '',
        'cod_responsavel' => '',
        'dte_prazo' => '',
        'dsc_status' => 'A Fazer',
        'vlr_custo_estimado' => 0,
    ];

    public function mount($riscoId)
    {
        $this->risco = Risco::findOrFail($riscoId);
        $this->authorize('view', $this->risco);
        $this->carregarDados();
    }

    public function carregarDados()
    {
        $this->mitigacoes = RiscoMitigacao::where('cod_risco', $this->risco->cod_risco)
            ->orderBy('dte_prazo')
            ->get();

        $this->usuarios = User::whereHas('organizacoes', function($q) {
            $q->where('tab_organizacoes.cod_organizacao', $this->risco->cod_organizacao);
        })->orderBy('name')->get();
    }

    public function create()
    {
        $this->authorize('update', $this->risco);
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $m = RiscoMitigacao::findOrFail($id);
        $this->authorize('update', $this->risco);

        $this->mitigacaoId = $id;
        $this->form = [
            'dsc_tipo' => $m->dsc_tipo,
            'txt_descricao' => $m->txt_descricao,
            'cod_responsavel' => $m->cod_responsavel,
            'dte_prazo' => $m->dte_prazo?->format('Y-m-d'),
            'dsc_status' => $m->dsc_status,
            'vlr_custo_estimado' => $m->vlr_custo_estimado,
        ];

        $this->showModal = true;
    }

    public function save()
    {
        $this->authorize('update', $this->risco);

        $this->validate([
            'form.dsc_tipo' => 'required',
            'form.txt_descricao' => 'required|string|max:1000',
            'form.cod_responsavel' => 'required|exists:users,id',
            'form.dte_prazo' => 'required|date',
        ]);

        $data = $this->form;
        $data['cod_risco'] = $this->risco->cod_risco;

        RiscoMitigacao::updateOrCreate(
            ['cod_mitigacao' => $this->mitigacaoId],
            $data
        );

        $this->showModal = false;
        $this->carregarDados();
        session()->flash('status', 'Plano de mitigação salvo!');
    }

    public function delete($id)
    {
        $this->authorize('update', $this->risco);
        RiscoMitigacao::findOrFail($id)->delete();
        $this->carregarDados();
    }

    public function resetForm()
    {
        $this->mitigacaoId = null;
        $this->form = [
            'dsc_tipo' => 'Prevenção',
            'txt_descricao' => '',
            'cod_responsavel' => '',
            'dte_prazo' => '',
            'dsc_status' => 'A Fazer',
            'vlr_custo_estimado' => 0,
        ];
    }

    public function render()
    {
        return view('livewire.risco.gerenciar-mitigacoes');
    }
}