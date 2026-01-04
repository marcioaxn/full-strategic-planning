<?php

namespace App\Livewire\Shared;

use App\Models\Organization;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class SeletorOrganizacao extends Component
{
    public $organizacoes;
    public $selecionadaId;

    public function mount()
    {
        $this->carregarOrganizacoes();
        
        // Inicializar com a sessão ou com a primeira da lista
        $this->selecionadaId = Session::get('organizacao_selecionada_id');

        if (!$this->selecionadaId && $this->organizacoes->isNotEmpty()) {
            $this->selecionar($this->organizacoes->first()->cod_organizacao);
        }
    }

    public function carregarOrganizacoes()
    {
        $user = auth()->user();

        if ($user && $user->isSuperAdmin()) {
            $this->organizacoes = Organization::orderBy('sgl_organizacao')->get();
        } elseif ($user) {
            $this->organizacoes = $user->organizacoes()->orderBy('sgl_organizacao')->get();
        } else {
            // Guest access: show all organizations for public map
            $this->organizacoes = Organization::orderBy('sgl_organizacao')->get();
        }
    }

    public function selecionar($id)
    {
        $org = Organization::find($id);
        
        if ($org) {
            $this->selecionadaId = $id;
            Session::put('organizacao_selecionada_id', $id);
            Session::put('organizacao_selecionada_nom', $org->nom_organizacao);
            Session::put('organizacao_selecionada_sgl', $org->sgl_organizacao);
            
            // Emitir evento global para que outros componentes se atualizem
            $this->dispatch('organizacaoSelecionada', id: $id);
            
            // Opcional: recarregar a página para garantir que tudo seja filtrado
            // return redirect(request()->header('Referer'));
        }
    }

    public function render()
    {
        return view('livewire.shared.seletor-organizacao');
    }
}