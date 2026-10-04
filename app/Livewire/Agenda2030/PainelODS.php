<?php

namespace App\Livewire\Agenda2030;

use App\Models\Agenda2030\ODS;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\IntegracaoInstrumento;
use App\Models\StrategicPlanning\PEI;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

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
        $this->authorize('modulo.acessar', 'planejamento-estrategico');

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
        $totalVinculos = 0;
        $odsDeclarados = [];
        $declaradosSemObjetivo = collect();
        $detalhe = null;

        if ($this->peiAtivo) {
            $codPei = $this->peiAtivo->cod_pei;

            // Todos os ODS do catálogo, com os objetivos do PEI ativo vinculados a cada um
            $todosOds = ODS::ordenado()
                ->with(['objetivos' => function ($q) use ($codPei) {
                    $q->whereHas('perspectiva', fn ($qp) => $qp->where('cod_pei', $codPei))
                        ->with(['perspectiva', 'indicadores']);
                }])
                ->get();

            $odsCobertos = $todosOds->filter(fn ($o) => $o->objetivos->isNotEmpty());

            // Um objetivo pode contribuir para até 3 ODS: somar por ODS contaria
            // o mesmo objetivo várias vezes. Objetivos são contados uma vez só;
            // vínculos (pares objetivo × ODS) são informados à parte.
            $totalVinculos = $odsCobertos->sum(fn ($o) => $o->objetivos->count());
            $totalObjetivosVinculados = $odsCobertos
                ->flatMap(fn ($o) => $o->objetivos->pluck('cod_objetivo'))
                ->unique()
                ->count();

            // Aderência declarada na etapa "Inaugurar e Integrar" (rel_pei_ods)
            $declarados = $this->peiAtivo->ods()->get();
            $odsDeclarados = $declarados
                ->mapWithKeys(fn ($o) => [(int) $o->num_ods => IntegracaoInstrumento::rotuloIntensidade($o->pivot->dsc_intensidade)])
                ->all();
            $declaradosSemObjetivo = $todosOds
                ->filter(fn ($o) => isset($odsDeclarados[(int) $o->num_ods]) && $o->objetivos->isEmpty())
                ->values();

            // Detalhamento do ODS selecionado
            if ($this->odsAtivo) {
                $alvo = $todosOds->firstWhere('num_ods', $this->odsAtivo);
                if ($alvo) {
                    $detalhe = [
                        'ods' => $alvo,
                        'declarado' => $odsDeclarados[(int) $alvo->num_ods] ?? null,
                        'contribuicao_declarada' => $declarados->firstWhere('num_ods', $alvo->num_ods)?->pivot->txt_contribuicao,
                        'objetivos' => $alvo->objetivos->map(function ($obj) {
                            // Sem indicador não há medição: mostrar 0% seria
                            // afirmar um desempenho péssimo que ninguém mediu.
                            $temIndicador = $obj->indicadores->isNotEmpty()
                                || Indicador::whereHas('planoDeAcao', fn ($q) => $q->where('cod_objetivo', $obj->cod_objetivo))->exists();

                            // Com indicador mas sem medição no ano: também NULL.
                            $atingimento = $temIndicador ? $obj->calcularAtingimentoConsolidado($this->ano) : null;

                            return [
                                'nome' => $obj->nom_objetivo,
                                'cod' => $obj->cod_objetivo,
                                'perspectiva' => $obj->perspectiva?->dsc_perspectiva ?? '—',
                                'qtd_kpis' => $obj->indicadores->count(),
                                'atingimento' => $atingimento === null ? null : round($atingimento, 1),
                                'tem_indicador' => $temIndicador,
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
            'todosOds' => $todosOds,
            'odsCobertos' => $odsCobertos,
            'qtdCobertos' => $odsCobertos->count(),
            'totalObjetivosVinculados' => $totalObjetivosVinculados,
            'totalVinculos' => $totalVinculos,
            'odsDeclarados' => $odsDeclarados,
            'declaradosSemObjetivo' => $declaradosSemObjetivo,
            'detalhe' => $detalhe,
        ]);
    }
}
