<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\TemaNorteador;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharPei extends Component
{
    public $pei;

    public $estatisticas = [];

    public function mount($id)
    {
        $this->pei = PEI::with(['identidadeEstrategica', 'valores'])->findOrFail($id);
        $this->carregarEstatisticas();
    }

    public function carregarEstatisticas()
    {
        // IDs das perspectivas deste PEI
        $perspectivasIds = Perspectiva::where('cod_pei', $this->pei->cod_pei)->pluck('cod_perspectiva');

        // Contagem de Objetivos BSC (vinculados a perspectivas)
        $objetivosBscCount = Objetivo::whereIn('cod_perspectiva', $perspectivasIds)->count();

        // Contagem de Temas Norteadores (vinculados diretamente ao PEI)
        $temasNorteadoresCount = TemaNorteador::where('cod_pei', $this->pei->cod_pei)->count();

        $this->estatisticas = [
            'qtd_perspectivas' => $perspectivasIds->count(),
            'qtd_objetivos_bsc' => $objetivosBscCount,
            'qtd_temas_norteadores' => $temasNorteadoresCount,
            'qtd_valores' => $this->pei->valores->count(),
        ];
    }

    /**
     * Abre uma tela de gestão já com ESTE ciclo selecionado.
     *
     * As telas de perspectivas, objetivos, valores etc. trabalham sobre o ciclo
     * da sessão (seletor do topo). Um link direto, a partir do detalhe de outro
     * ciclo, abria a tela no ciclo errado — e o usuário editava o que não via.
     */
    public function abrirNoCiclo(string $rota)
    {
        $permitidas = ['pei.index', 'pei.valores', 'pei.perspectivas', 'objetivos.index', 'indicadores.index', 'pei.swot'];
        if (! in_array($rota, $permitidas, true)) {
            abort(404);
        }

        Session::put('pei_selecionado_id', $this->pei->cod_pei);
        Session::put('pei_selecionado_dsc', $this->pei->dsc_pei);
        Session::put('pei_selecionado_periodo', $this->pei->num_ano_inicio_pei.'-'.$this->pei->num_ano_fim_pei);

        // Mesmo critério do seletor do topo: o ano de referência precisa
        // pertencer ao ciclo, senão as telas abrem vazias.
        $ano = (int) Session::get('ano_selecionado', date('Y'));
        if ($ano < $this->pei->num_ano_inicio_pei || $ano > $this->pei->num_ano_fim_pei) {
            $vigente = (int) date('Y');
            Session::put('ano_selecionado', ($vigente >= $this->pei->num_ano_inicio_pei && $vigente <= $this->pei->num_ano_fim_pei)
                ? $vigente
                : $this->pei->num_ano_inicio_pei);
        }

        return $this->redirect(route($rota));
    }

    public function render()
    {
        return view('livewire.p-e-i.detalhar-pei');
    }
}
