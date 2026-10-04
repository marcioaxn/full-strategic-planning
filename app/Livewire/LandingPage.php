<?php

namespace App\Livewire;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\Organization;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\SystemSetting;
use App\Services\IndicadorCalculoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class LandingPage extends Component
{
    public bool $temDados = false;

    public $pei = null;

    public $organizacao = null;

    public $identidade = null;

    public $perspectivas = null;

    public array $stats = [];

    public $grausSatisfacao = null;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect(route('dashboard'), navigate: true);

            return;
        }

        $this->carregarDados();
    }

    private function carregarDados(): void
    {
        try {
            // Cache leve de 5 min para não onerar a DB em cada visita
            $cache = Cache::remember('lp_dados_publicos', 300, function () {
                $pei = PEI::ativos()->first();

                if (! $pei) {
                    return ['temDados' => false];
                }

                $graus = GrauSatisfacao::doPei($pei->cod_pei)->get();
                $anoAtual = (int) date('Y');

                // 🔴 A cor sai da MESMA função que pinta o Mapa Estratégico e a
                // página do objetivo. A versão anterior repetia a busca de faixa
                // aqui e devolvia cinza para todo valor fora das faixas — um
                // indicador a 125% da meta, que superou a melhor faixa, saía
                // cinza no portal e vermelho no mapa. O mesmo número, dois
                // juízos, na mesma plataforma.
                $calcularCor = fn (float $pct): string => GrauSatisfacao::corDe($pct, $pei->cod_pei, $anoAtual);
                $calculo = app(IndicadorCalculoService::class);

                $org = Organization::whereColumn('cod_organizacao', 'rel_cod_organizacao')->first()
                           ?? Organization::first();
                // A missão exibida é a da unidade exibida (a raiz). Sem o filtro, saía a
                // primeira identidade do ciclo — de qualquer unidade — sob o nome da raiz.
                $identidade = MissaoVisaoValores::where('cod_pei', $pei->cod_pei)
                    ->when($org, fn ($q) => $q->where('cod_organizacao', $org->cod_organizacao))
                    ->first();

                // Perspectivas com objetivos (desc: nível mais alto no topo)
                $perspectivas = Perspectiva::where('cod_pei', $pei->cod_pei)
                    ->with(['objetivos' => fn ($q) => $q->withCount(['indicadores', 'planosAcao'])])
                    ->orderBy('num_nivel_hierarquico_apresentacao', 'desc')
                    ->get()
                    ->map(function ($p) use ($anoAtual, $calcularCor, $calculo) {
                        // Pré-calcula o atingimento de cada objetivo UMA vez e anexa ao objeto
                        // (evita N+1 na renderização do Mapa Estratégico e do Panorama).
                        $p->objetivos->each(function ($o) use ($anoAtual, $calcularCor, $calculo) {
                            $o->lp_atingimento = round($calculo->calcularAtingimentoObjetivo($o, $anoAtual), 1);
                            $o->lp_cor = $calcularCor($o->lp_atingimento);
                        });

                        /*
                         * 🔴 `->filter()` SEM CALLBACK DESCARTA O ZERO.
                         *
                         * A média da perspectiva ignorava todo objetivo medido em
                         * 0% — o resultado ruim sumia da conta e a perspectiva
                         * subia sozinha. Num Portal da Transparência isso não é
                         * arredondamento: é esconder o que foi mal.
                         *
                         * A distinção correta não é "zero ou não", é "medido ou
                         * não": objetivo SEM indicador e SEM iniciativa não tem o
                         * que medir e fica fora da média (entrar como 0 inventaria
                         * um resultado ruim que ninguém apurou). Objetivo com o que
                         * medir entra, valha 0 ou 125.
                         */
                        $mensuraveis = $p->objetivos->filter(
                            fn ($o) => $o->indicadores_count > 0 || $o->planos_acao_count > 0
                        );

                        $p->qtd_mensuravel = $mensuraveis->count();
                        $p->tem_medicao = $p->qtd_mensuravel > 0;
                        // Mesmo cálculo do Dashboard, do Mapa e dos relatórios
                        // (indicadores e iniciativas, com os pesos da perspectiva):
                        // o portal não pode publicar um número que a área interna desmente.
                        $p->atingimento_medio = $p->tem_medicao
                            ? round($calculo->calcularAtingimentoPerspectiva($p, $anoAtual), 1)
                            : 0;
                        $p->cor_atingimento = $p->tem_medicao ? $calcularCor($p->atingimento_medio) : '#6b7280';
                        $p->objetivos_abaixo = $mensuraveis->filter(fn ($o) => $o->lp_atingimento < 50)->count();

                        return $p;
                    });

                /*
                 * 🔴 O NÚMERO NÃO BATIA COM O PRÓPRIO RÓTULO.
                 *
                 * A média global descartava toda perspectiva em 0% e depois o
                 * portal anunciava "Média consolidada de N perspectivas BSC",
                 * com N contando TODAS. Com uma perspectiva em 0% e outra em
                 * 125%, a tela estampava 125% e dizia que era a média de duas.
                 *
                 * Agora entram as perspectivas que têm o que medir — e o rótulo
                 * informa quantas foram, que é o que torna o número rastreável.
                 */
                $comMedicao = $perspectivas->filter(fn ($p) => $p->tem_medicao);
                $globalAt = $comMedicao->count() > 0 ? round($comMedicao->avg('atingimento_medio'), 1) : 0;

                // Contagens (lightweight)
                $totalObjs = $perspectivas->sum(fn ($p) => $p->objetivos->count());

                $totalInds = Indicador::whereHas(
                    'objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $pei->cod_pei)
                )->count();

                $totalPlanos = PlanoDeAcao::whereHas(
                    'objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $pei->cod_pei)
                )->count();

                // Informação de gestão de riscos em página PÚBLICA é decisão do
                // órgão, não do produto. Nasce DESLIGADA: um cliente pode querer
                // publicar o mapa estratégico e não os riscos.
                // Ver documentacao/melhorias/07-mapa-estrategico-publico.md
                $publicaRiscos = (bool) SystemSetting::getValue('transparencia_exibe_riscos_agregado', false);

                $riscosCrit = $publicaRiscos
                    ? Risco::where('cod_pei', $pei->cod_pei)->where('num_nivel_risco', '>=', 16)->count()
                    : null;

                // Houve ALGUM lançamento de evolução neste ciclo?
                //
                // Sem isso, o portal estampava "0% de atingimento" e "0 planos"
                // durante todo o preenchimento. O número é verdadeiro e a leitura
                // que ele induz é falsa: num Portal da Transparência, 0% não diz
                // "ciclo em preenchimento" — diz "este órgão não executou nada".
                $temMedicao = EvolucaoIndicador::whereHas(
                    'indicador.objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $pei->cod_pei)
                )->exists();

                return [
                    'temDados' => true,
                    'pei' => $pei,
                    'organizacao' => $org,
                    'identidade' => $identidade,
                    'perspectivas' => $perspectivas,
                    'graus' => $graus,
                    'stats' => [
                        'atingimentoGlobal' => $globalAt,
                        'corGlobal' => $comMedicao->count() > 0 ? $calcularCor($globalAt) : '#6b7280',
                        'perspectivas' => $perspectivas->count(),
                        // Quantas perspectivas ENTRARAM na média — é este o
                        // número que o rótulo tem de citar, não o total.
                        'perspectivasNaMedia' => $comMedicao->count(),
                        'temRegua' => GrauSatisfacao::temRegua($pei->cod_pei, $anoAtual),
                        'objetivos' => $totalObjs,
                        'indicadores' => $totalInds,
                        'planos' => $totalPlanos,
                        'riscosCriticos' => $riscosCrit,
                        'publicaRiscos' => $publicaRiscos,
                        'temMedicao' => $temMedicao,
                    ],
                ];
            });

            if ($cache['temDados']) {
                $this->temDados = true;
                $this->pei = $cache['pei'];
                $this->organizacao = $cache['organizacao'];
                $this->identidade = $cache['identidade'];
                $this->perspectivas = $cache['perspectivas'];
                $this->grausSatisfacao = $cache['graus'];
                $this->stats = $cache['stats'];
            }

        } catch (\Throwable $e) {
            // Sem report(), uma falha aqui deixa o portal público em branco e
            // não sobra rastro nenhum para investigar.
            report($e);

            $this->temDados = false;
        }
    }

    public function render()
    {
        return view('livewire.landing-page');
    }
}
