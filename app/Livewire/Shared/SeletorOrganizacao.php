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

        // Inicializar com a sessão (validada contra o escopo) ou com a primeira da lista
        $this->selecionadaId = auth()->check()
            ? auth()->user()->organizacaoSelecionadaId()
            : Session::get('organizacao_selecionada_id');

        // Nome e sigla exibidos no topo acompanham a organização validada (se a
        // sessão trazia uma unidade fora do escopo, a tela não pode exibi-la).
        if ($this->selecionadaId && auth()->check()) {
            $org = Organization::find($this->selecionadaId);
            Session::put('organizacao_selecionada_nom', $org?->nom_organizacao);
            Session::put('organizacao_selecionada_sgl', $org?->sgl_organizacao);
        }

        if (! $this->selecionadaId && $this->organizacoes->isNotEmpty()) {
            $first = $this->organizacoes->first();
            $id = is_array($first) ? $first['id'] : $first->cod_organizacao;
            $this->atualizarSessao($id);
        }
    }

    public function carregarOrganizacoes()
    {
        $user = auth()->user();

        if ($user && $user->isSuperAdmin()) {
            // Admin vê toda a árvore hierárquica
            $this->organizacoes = collect(Organization::getTreeForSelector());
        } elseif ($user) {
            // Usuário comum vê as unidades do seu escopo — as vinculadas e, onde é
            // Administrador ou Consulta, também as subordinadas — na ordem da árvore.
            $permitidas = $user->organizacaoIdsPermitidas()->all();
            $this->organizacoes = collect(Organization::getTreeForSelector())
                ->filter(fn ($o) => in_array($o['id'], $permitidas, true))
                ->values();
        } else {
            // Acesso público: árvore completa
            $this->organizacoes = collect(Organization::getTreeForSelector());
        }
    }

    public function selecionar($id)
    {
        if ($this->atualizarSessao($id)) {
            // Para garantir que o Roll-up e outros filtros globais sejam aplicados instantaneamente
            return redirect(request()->header('Referer'));
        }
    }

    private function atualizarSessao($id)
    {
        $org = Organization::find($id);

        if (! $org) {
            return false;
        }

        // Usuário autenticado não-admin só pode selecionar organizações do
        // seu próprio escopo — evita que qualquer usuário logado assuma,
        // via manipulação direta da ação Livewire, o contexto de uma
        // organização à qual não pertence (toda a cadeia de componentes do
        // sistema confia neste valor de sessão para decisões de acesso).
        $user = auth()->user();
        if ($user && ! $user->podeAcessarOrganizacao($id)) {
            return false;
        }

        if ($org) {
            $this->selecionadaId = $id;
            Session::put('organizacao_selecionada_id', $id);
            Session::put('organizacao_selecionada_nom', $org->nom_organizacao);
            Session::put('organizacao_selecionada_sgl', $org->sgl_organizacao);

            $this->dispatch('organizacaoSelecionada', id: $id);

            return true;
        }

        return false;
    }

    public function render()
    {
        return view('livewire.shared.seletor-organizacao');
    }
}
