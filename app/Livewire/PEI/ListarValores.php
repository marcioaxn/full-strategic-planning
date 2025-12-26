<?php

namespace App\Livewire\PEI;

use App\Models\PEI\Valor;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class ListarValores extends Component
{
    use AuthorizesRequests;

    public $organizacaoId;
    public $organizacaoNome;
    
    public $valores = [];
    
    public bool $showModal = false;
    public $valorId;
    public $nom_valor;
    public $dsc_valor;

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao'
    ];

    public function mount()
    {
        $this->atualizarOrganizacao(session('organizacao_selecionada_id'));
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
        $this->valores = Valor::where('cod_organizacao', $this->organizacaoId)
            ->orderBy('nom_valor')
            ->get();
    }

    public function create()
    {
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
                // 'cod_pei' => ...
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