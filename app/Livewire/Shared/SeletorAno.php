<?php

namespace App\Livewire\Shared;

use App\Models\PEI\PEI;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class SeletorAno extends Component
{
    public $anosAgrupados = [];
    public $anoSelecionado;
    public $peiSelecionadoId;

    protected $listeners = [
        'peiSelecionado' => 'atualizarPeiId'
    ];

    public function mount()
    {
        $this->peiSelecionadoId = Session::get('pei_selecionado_id');
        $this->anoSelecionado = Session::get('ano_selecionado', date('Y'));
        $this->carregarAnos();
    }

    public function atualizarPeiId($id)
    {
        $this->peiSelecionadoId = $id;
    }

    public function carregarAnos()
    {
        // Busca todos os PEIs para agrupar os anos
        $peis = PEI::orderBy('num_ano_inicio_pei', 'desc')->get();
        $this->anosAgrupados = [];

        foreach ($peis as $pei) {
            $this->anosAgrupados[] = [
                'pei_id' => $pei->cod_pei,
                'label' => $pei->dsc_pei . " ({$pei->num_ano_inicio_pei}-{$pei->num_ano_fim_pei})",
                'anos' => range($pei->num_ano_fim_pei, $pei->num_ano_inicio_pei)
            ];
        }

        if (empty($this->anosAgrupados)) {
            $atual = date('Y');
            $this->anosAgrupados[] = [
                'label' => 'Anos Disponíveis',
                'anos' => [$atual, $atual - 1, $atual - 2]
            ];
        }
    }

    public function selecionar($ano)
    {
        $this->anoSelecionado = $ano;
        Session::put('ano_selecionado', $ano);

        // --- INTELIGÊNCIA: Sincronizar com o PEI ---
        // Verifica se o ano selecionado pertence ao PEI atual
        $currentPei = PEI::find($this->peiSelecionadoId);
        
        if (!$currentPei || $ano < $currentPei->num_ano_inicio_pei || $ano > $currentPei->num_ano_fim_pei) {
            // Se não pertence, busca o PEI que abrange este ano
            $novoPei = PEI::where('num_ano_inicio_pei', '<=', $ano)
                          ->where('num_ano_fim_pei', '>=', $ano)
                          ->first();
            
            if ($novoPei) {
                $this->trocarPeiSilencioso($novoPei);
            }
        }

        $this->dispatch('anoSelecionado', ano: $ano);
        return $this->redirect(request()->header('Referer'), navigate: true);
    }

    private function trocarPeiSilencioso(PEI $pei)
    {
        Session::put('pei_selecionado_id', $pei->cod_pei);
        Session::put('pei_selecionado_dsc', $pei->dsc_pei);
        Session::put('pei_selecionado_periodo', $pei->num_ano_inicio_pei . '-' . $pei->num_ano_fim_pei);
        $this->dispatch('peiSelecionado', id: $pei->cod_pei);
    }

    public function render()
    {
        return view('livewire.shared.seletor-ano');
    }
}
