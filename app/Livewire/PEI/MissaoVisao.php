<?php

namespace App\Livewire\PEI;

use App\Models\PEI\MissaoVisaoValores;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class MissaoVisao extends Component
{
    use AuthorizesRequests;

    public $missao = '';
    public $visao = '';
    public $organizacaoId;
    public $organizacaoNome;
    
    public bool $isEditing = false;

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
            $this->carregarDados();
        } else {
            $this->missao = '';
            $this->visao = '';
            $this->organizacaoNome = '';
        }
        
        $this->isEditing = false;
    }

    public function carregarDados()
    {
        $dados = MissaoVisaoValores::where('cod_organizacao', $this->organizacaoId)
            ->first();

        if ($dados) {
            $this->missao = $dados->dsc_missao;
            $this->visao = $dados->dsc_visao;
        } else {
            $this->missao = '';
            $this->visao = '';
        }
    }

    public function habilitarEdicao()
    {
        // $this->authorize('update-identity', Organization::find($this->organizacaoId));
        $this->isEditing = true;
    }

    public function cancelar()
    {
        $this->carregarDados();
        $this->isEditing = false;
    }

    public function salvar()
    {
        $this->validate([
            'missao' => 'nullable|string|max:2000',
            'visao' => 'nullable|string|max:2000',
        ]);

        $dados = MissaoVisaoValores::updateOrCreate(
            ['cod_organizacao' => $this->organizacaoId],
            [
                'dsc_missao' => $this->missao,
                'dsc_visao' => $this->visao,
                // 'cod_pei' => ... (precisaremos do PEI ativo futuramente)
            ]
        );

        $this->isEditing = false;
        session()->flash('status', 'Identidade estratégica atualizada com sucesso!');
    }

    public function render()
    {
        return view('livewire.p-e-i.missao-visao');
    }
}