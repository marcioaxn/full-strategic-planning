<?php

namespace App\Livewire\Agenda2030;

use App\Models\Agenda2030\ODS;
use App\Models\StrategicPlanning\PEI;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class PainelODS extends Component
{
    public $peiAtivo;
    public ?int $odsAtivo = null;   // ODS selecionado para exibir o detalhamento
    public int $ano;

    protected $listeners = [
        'peiSelecionado' => 'atualizarPEI',
        'anoSelecionado' => 'atualizarAno',
    ];

    public function mount(): void
    {
        $this->ano = (int) Session::get('ano_selecionado', now()->year);
        $this->carregarPEI();
    }

    public function atualizarPEI($id): void
    {
        $this->peiAtivo = PEI::find($id);
        $this->odsAtivo = null;
    }

    public function atualizarAno($ano): void
    {
        $this->ano = (int) $ano;
    }

    private function carregarPEI(): void
    {
        $peiId = Session::get('pei_selecionado_id');
        $this->peiAtivo = $peiId ? PEI::find($peiId) : PEI::ativos()->first();
    }

    /**
     * Seleciona (ou desmarca) um ODS para ver o detalhamento.
     */
    public function selecionarOds(int $num): void
    {
        $this->odsAtivo = ($this->odsAtivo === $num) ? null : $num;
    }

    public function render()
    {
        $odsCobertos = collect();
        $totalObjetivosVinculados = 0;
        $detalhe = null;

        if ($this->peiAtivo) {
            $codPei = $this->peiAtivo->cod_pei;

            // Os 17 ODS com os objetivos vinculados que pertençam ao PEI ativo
            $todosOds = ODS::ordenado()
                ->with(['objetivos' => function ($q) use ($codPei) {
                    $q->whereHas('perspectiva', fn ($qp) => $qp->where('cod_pei', $codPei))
                      ->with(['perspectiva', 'indicadores']);
                }])
                ->get();

            $odsCobertos = $todosOds->filter(fn ($o) => $o->objetivos->isNotEmpty());
            $totalObjetivosVinculados = $odsCobertos->sum(fn ($o) => $o->objetivos->count());

            // Detalhamento do ODS selecionado
            if ($this->odsAtivo) {
                $alvo = $todosOds->firstWhere('num_ods', $this->odsAtivo);
                if ($alvo) {
                    $detalhe = [
                        'ods'       => $alvo,
                        'objetivos' => $alvo->objetivos->map(function ($obj) {
                            return [
                                'nome'         => $obj->nom_objetivo,
                                'cod'          => $obj->cod_objetivo,
                                'perspectiva'  => $obj->perspectiva?->dsc_perspectiva ?? '—',
                                'qtd_kpis'     => $obj->indicadores->count(),
                                'atingimento'  => round($obj->calcularAtingimentoConsolidado($this->ano), 1),
                                'contribuicao' => $obj->pivot->txt_contribuicao ?? null,
                            ];
                        })->values()->all(),
                    ];
                }
            }
        } else {
            $todosOds = ODS::ordenado()->get();
        }

        return view('livewire.agenda2030.painel-o-d-s', [
            'todosOds'                 => $todosOds,
            'odsCobertos'              => $odsCobertos,
            'qtdCobertos'              => $odsCobertos->count(),
            'totalObjetivosVinculados' => $totalObjetivosVinculados,
            'detalhe'                  => $detalhe,
        ]);
    }
}
