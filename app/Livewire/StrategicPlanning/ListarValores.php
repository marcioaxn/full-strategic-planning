<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\PEI\PEI;
use App\Models\PEI\Valor;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class ListarValores extends Component
{
    use AuthorizesRequests;

    public $organizacaoId;
    public $organizacaoNome;
    public $peiAtivo;

    public $valores = [];

    public bool $showModal = false;
    public $valorId;
    public $nom_valor;
    public $dsc_valor;

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI'
    ];

    public function mount()
    {
        $this->carregarPEI();
        $this->atualizarOrganizacao(Session::get('organizacao_selecionada_id'));
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarValores();
    }

    private function carregarPEI()
    {
        $peiId = Session::get('pei_selecionado_id');

        if ($peiId) {
            $this->peiAtivo = PEI::find($peiId);
        }

        if (!$this->peiAtivo) {
            $this->peiAtivo = PEI::ativos()->first();
        }
    }

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;

        if ($id) {
            $org = Organization::find($id);
            $this->organizacaoNome = $org->nom_organizacao;
            $this->carregarValores();
        } else {
            $this->valores = [];
            $this->organizacaoNome = '';
        }
    }

    public function carregarValores()
    {
        if (!$this->peiAtivo || !$this->organizacaoId) {
            $this->valores = [];
            return;
        }

        $this->valores = Valor::where('cod_organizacao', $this->organizacaoId)
            ->where('cod_pei', $this->peiAtivo->cod_pei)
            ->orderBy('nom_valor')
            ->get();
    }

    public function create()
    {
        if (!$this->peiAtivo) {
            session()->flash('error', 'Não há um Ciclo PEI selecionado.');
            return;
        }
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $valor = Valor::findOrFail($id);
        $this->valorId = $id;
        $this->nom_valor = $valor->nom_valor;
        $this->dsc_valor = $valor->dsc_valor;
        $this->showModal = true;
    }

    public function save()
    {
        if (!$this->peiAtivo) {
            session()->flash('error', 'Não é possível salvar sem um Ciclo PEI selecionado.');
            return;
        }

        $this->validate([
            'nom_valor' => 'required|string|max:255',
            'dsc_valor' => 'nullable|string|max:1000',
        ]);

        Valor::updateOrCreate(
            ['cod_valor' => $this->valorId],
            [
                'nom_valor' => $this->nom_valor,
                'dsc_valor' => $this->dsc_valor,
                'cod_organizacao' => $this->organizacaoId,
                'cod_pei' => $this->peiAtivo->cod_pei,
            ]
        );

        $this->showModal = false;
        $this->carregarValores();
        session()->flash('status', 'Valor salvo com sucesso!');
    }

    public function delete($id)
    {
        Valor::findOrFail($id)->delete();
        $this->carregarValores();
        session()->flash('status', 'Valor excluído com sucesso!');
    }

    public function resetForm()
    {
        $this->valorId = null;
        $this->nom_valor = '';
        $this->dsc_valor = '';
    }

    public function render()
    {
        return view('livewire.p-e-i.listar-valores');
    }
}
