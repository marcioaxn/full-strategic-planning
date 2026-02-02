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
            $first = $this->organizacoes->first();
            $id = is_array($first) ? $first['id'] : $first->cod_organizacao;
            $this->selecionar($id);
        }
    }

    public function carregarOrganizacoes()
    {
        $user = auth()->user();

        if ($user && $user->isSuperAdmin()) {
            // Admin vê toda a árvore hierárquica
            $this->organizacoes = collect(Organization::getTreeForSelector());
        } elseif ($user) {
            // Usuário comum vê apenas suas organizações vinculadas (mas ainda em formato compatível)
            $this->organizacoes = $user->organizacoes()
                ->orderBy('sgl_organizacao')
                ->get()
                ->map(fn($org) => [
                    'id' => $org->cod_organizacao,
                    'label' => $org->sgl_organizacao . ' - ' . $org->nom_organizacao
                ]);
        } else {
            // Acesso público: árvore completa
            $this->organizacoes = collect(Organization::getTreeForSelector());
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
            
            $this->dispatch('organizacaoSelecionada', id: $id);
            
            // Para garantir que o Roll-up e outros filtros globais sejam aplicados instantaneamente
            return redirect(request()->header('Referer'));
        }
    }

    public function render()
    {
        return view('livewire.shared.seletor-organizacao');
    }
}