<?php

namespace App\Livewire\Shared;

use App\Models\StrategicPlanning\PEI;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class SeletorPEI extends Component
{
    public $peis;
    public $selecionadoId;

    public function mount()
    {
        $this->carregarPEIs();
        $this->selecionadoId = Session::get('pei_selecionado_id');

        if (!$this->selecionadoId && $this->peis->isNotEmpty()) {
            $peiAtivo = $this->peis->first(fn($p) => $p->isAtivo()) ?? $this->peis->first();
            $this->definirSessao($peiAtivo);
        }
    }

    public function carregarPEIs()
    {
        $this->peis = PEI::orderBy('num_ano_inicio_pei', 'desc')->get();
    }

    private function definirSessao(PEI $pei)
    {
        $this->selecionadoId = $pei->cod_pei;
        Session::put('pei_selecionado_id', $pei->cod_pei);
        Session::put('pei_selecionado_dsc', $pei->dsc_pei);
        Session::put('pei_selecionado_periodo', $pei->num_ano_inicio_pei . '-' . $pei->num_ano_fim_pei);
    }

    public function selecionar($id)
    {
        $pei = PEI::find($id);

        if ($pei) {
            $this->definirSessao($pei);

            // --- INTELIGÊNCIA: Sincronizar com o Ano ---
            $anoAtualSessao = (int) Session::get('ano_selecionado', date('Y'));
            
            // Se o ano atual da sessão não está no range do novo PEI
            if ($anoAtualSessao < $pei->num_ano_inicio_pei || $anoAtualSessao > $pei->num_ano_fim_pei) {
                // Tenta colocar no ano vigente se ele estiver no PEI, senão vai para o ano de início do PEI
                $anoVigente = (int) date('Y');
                if ($anoVigente >= $pei->num_ano_inicio_pei && $anoVigente <= $pei->num_ano_fim_pei) {
                    Session::put('ano_selecionado', $anoVigente);
                } else {
                    Session::put('ano_selecionado', $pei->num_ano_inicio_pei);
                }
            }

            $this->dispatch('peiSelecionado', id: $id);
            $url = request()->header('Referer') ?? route('dashboard');
            return $this->redirect($url, navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.shared.seletor-pei');
    }
}