<?php

namespace App\Livewire\Shared;

use App\Models\PEI\PEI;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class SeletorPEI extends Component
{
    public $peis;
    public $selecionadoId;

    public function mount()
    {
        $this->carregarPEIs();

        // Inicializar com a sessão ou com o primeiro PEI ativo
        $this->selecionadoId = Session::get('pei_selecionado_id');

        if (!$this->selecionadoId && $this->peis->isNotEmpty()) {
            // Tenta pegar o primeiro PEI ativo, senão pega o primeiro da lista
            $peiAtivo = $this->peis->first(fn($p) => $p->isAtivo()) ?? $this->peis->first();
            $this->selecionar($peiAtivo->cod_pei);
        }
    }

    public function carregarPEIs()
    {
        // Carrega todos os PEIs ordenados por ano de início (mais recente primeiro)
        $this->peis = PEI::orderBy('num_ano_inicio_pei', 'desc')->get();
    }

    public function selecionar($id)
    {
        $pei = PEI::find($id);

        if ($pei) {
            $this->selecionadoId = $id;
            Session::put('pei_selecionado_id', $id);
            Session::put('pei_selecionado_dsc', $pei->dsc_pei);
            Session::put('pei_selecionado_periodo', $pei->num_ano_inicio_pei . '-' . $pei->num_ano_fim_pei);

            // Dispara evento global para outros componentes que podem estar ouvindo
            $this->dispatch('peiSelecionado', id: $id);

            // Refresh da página de forma compatível com wire:navigate
            // Se estivermos em uma URL que depende do PEI, o redirect garante a atualização dos dados
            return $this->redirect(request()->header('Referer'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.shared.seletor-pei');
    }
}