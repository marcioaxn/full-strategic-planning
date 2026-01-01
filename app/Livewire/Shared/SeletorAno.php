<?php

namespace App\Livewire\Shared;

use App\Models\PEI\PEI;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class SeletorAno extends Component
{
    public $anos = [];
    public $anoSelecionado;

    public function mount()
    {
        $this->carregarAnos();

        // Tenta pegar o ano da sessão, senão usa o ano atual
        $this->anoSelecionado = Session::get('ano_selecionado');

        if (!$this->anoSelecionado) {
            $this->anoSelecionado = date('Y');
            // Se o ano atual não estiver no range dos PEIs, pega o maior ano disponível
            if (!in_array($this->anoSelecionado, $this->anos) && !empty($this->anos)) {
                $this->anoSelecionado = $this->anos[0];
            }
            $this->definirSessao($this->anoSelecionado);
        }
    }

    public function carregarAnos()
    {
        // Busca o range de todos os PEIs
        $range = PEI::selectRaw('MIN(num_ano_inicio_pei) as min_ano, MAX(num_ano_fim_pei) as max_ano')->first();

        if ($range && $range->min_ano && $range->max_ano) {
            $this->anos = range($range->max_ano, $range->min_ano); // Ordem decrescente
        } else {
            // Fallback caso não existam PEIs
            $atual = date('Y');
            $this->anos = [$atual, $atual - 1, $atual - 2];
        }
    }

    private function definirSessao($ano)
    {
        Session::put('ano_selecionado', $ano);
    }

    public function selecionar($ano)
    {
        $this->anoSelecionado = $ano;
        $this->definirSessao($ano);

        // Dispara evento global para que outros componentes se atualizem
        $this->dispatch('anoSelecionado', ano: $ano);

        // Refresh da página para aplicar em todo o sistema
        return $this->redirect(request()->header('Referer'), navigate: true);
    }

    public function render()
    {
        return view('livewire.shared.seletor-ano');
    }
}