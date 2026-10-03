<?php

namespace App\Livewire\RiskManagement;

use App\Models\RiskManagement\Risco;
use App\Models\RiskManagement\RiscoMitigacao;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class GerenciarMitigacoes extends Component
{
    use AuthorizesRequests;

    public $risco;

    public $mitigacoes = [];

    public $usuarios = [];

    public bool $showModal = false;

    // Só o servidor define (edit); o navegador não pode apontar para registro de outro risco.
    #[Locked]
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

        $this->usuarios = User::whereHas('organizacoes', function ($q) {
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
        $this->authorize('update', $this->risco);
        $m = $this->mitigacaoDoRisco($id);

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

        // O responsável vem do navegador: só pessoa da unidade do risco (a
        // mesma lista que a tela oferece).
        // (Consulta ao banco, não à propriedade $usuarios, que o navegador altera.)
        $daUnidade = User::where('id', $this->form['cod_responsavel'])
            ->whereHas('organizacoes', fn ($q) => $q->where('tab_organizacoes.cod_organizacao', $this->risco->cod_organizacao))
            ->exists();
        if (! $daUnidade) {
            $this->addError('form.cod_responsavel', 'Escolha um responsável da unidade do risco.');

            return;
        }

        $data = $this->form;
        $data['cod_risco'] = $this->risco->cod_risco;

        // Edição: o registro tem de ser deste risco — senão o updateOrCreate abaixo
        // o transferiria para cá, trocando o cod_risco.
        if ($this->mitigacaoId) {
            $this->mitigacaoDoRisco($this->mitigacaoId);
        }

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
        $this->mitigacaoDoRisco($id)->delete();
        $this->carregarDados();
    }

    /**
     * Registro pelo id vindo do navegador, restrito ao risco da tela.
     */
    private function mitigacaoDoRisco($id): RiscoMitigacao
    {
        return RiscoMitigacao::where('cod_risco', $this->risco->cod_risco)->findOrFail($id);
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
