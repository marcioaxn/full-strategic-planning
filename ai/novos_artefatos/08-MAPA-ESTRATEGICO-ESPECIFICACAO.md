# MAPA ESTRATÉGICO - ESPECIFICAÇÃO TÉCNICA COMPLETA

**Versão:** 2.0
**Data:** 23 de Dezembro de 2025
**Stack:** Laravel 12 + Livewire 3 + Bootstrap 5 + Chart.js
**Status:** Pronto para implementação

---

## 📋 VISÃO GERAL

O **Mapa Estratégico** é o componente central do sistema, apresentando uma visualização dinâmica e interativa do planejamento estratégico da organização. Ele é **construído dinamicamente** conforme o usuário preenche os dados (missão, visão, valores, perspectivas, objetivos) e utiliza coloração baseada no grau de satisfação para indicar o nível de atingimento.

### Características Principais

✅ **100% Dinâmico** - Montado em tempo real a partir dos dados preenchidos pelo usuário
✅ **Coloração por Desempenho** - Cores extraídas de `pei.tab_grau_satisfacao`
✅ **Visualização Chart.js** - Gráficos de rosca (doughnut) e barras horizontais
✅ **UI Moderna** - Baseada 100% no starter kit atual (Bootstrap 5)
✅ **Responsivo** - Adaptável para desktop, tablet e mobile
✅ **Interativo** - Filtros por organização, PEI e período

---

## 🎨 DESIGN E UX/UI

### Baseado no Starter Kit Atual

O componente utiliza **exclusivamente** os padrões visuais do starter kit:
- `resources/views/livewire/leads-table.blade.php` - Cards modernos, filtros, empty states
- `resources/views/dashboard.blade.php` - Stat cards, layouts, gradientes

**Classes CSS utilizadas do starter kit:**
- `.card-modern` - Cards com sombras suaves
- `.gradient-theme-header` - Headers com gradiente
- `.stat-card`, `.stat-card-primary`, `.stat-card-success` - Cards de estatísticas
- `.stat-card-icon`, `.stat-card-content`, `.stat-card-label` - Componentes dos stat cards
- `.badge`, `.badge-success`, `.badge-warning` - Badges coloridos

### Paleta de Cores (Grau de Satisfação)

```php
// Baseado em pei.tab_grau_satisfacao
// Cores alinhadas com o starter kit Bootstrap 5

private function determinarCor($percentual)
{
    if ($percentual >= 80) {
        return '#65a30d'; // Verde (Bootstrap success)
    } elseif ($percentual >= 60) {
        return '#eab308'; // Amarelo (Bootstrap warning)
    } else {
        return '#dc2626'; // Vermelho (Bootstrap danger)
    }
}

private function determinarBadgeClass($percentual)
{
    if ($percentual >= 80) {
        return 'badge-success';
    } elseif ($percentual >= 60) {
        return 'badge-warning';
    } else {
        return 'badge-danger';
    }
}
```

---

## 🏗️ ESTRUTURA DO COMPONENTE LIVEWIRE

### Arquivo: `app/Http/Livewire/MapaEstrategico/ShowDashboard.php`

```php
<?php

namespace App\Http\Livewire\MapaEstrategico;

use Livewire\Component;
use App\Models\Pei;
use App\Models\Organization;
use App\Models\MissaoVisaoValores;
use App\Models\Perspectiva;
use App\Models\ObjetivoEstrategico;
use App\Models\Indicador;
use App\Models\EvolucaoIndicador;
use App\Models\PlanoDeAcao;
use App\Models\Entregas;
use Illuminate\Support\Facades\DB;

class ShowDashboard extends Component
{
    // === PROPRIEDADES PÚBLICAS ===

    public $pei_cod;                    // PEI selecionado
    public $cod_organizacao;            // Organização selecionada
    public $ano_base;                   // Ano de referência
    public $mes_referencia;             // Mês de referência (1-12)

    // Dados carregados
    public $pei;
    public $organizacao;
    public $missaoVisaoValores;
    public $perspectivas;
    public $resultadosGerais = [];
    public $dadosGraficoDoughnut = [];
    public $dadosGraficoBarras = [];

    // === LISTENERS ===

    protected $listeners = [
        'refreshMapa' => '$refresh',
        'organizacaoAlterada' => 'atualizarOrganizacao',
    ];

    // === REGRAS DE VALIDAÇÃO ===

    protected $rules = [
        'pei_cod' => 'required|uuid|exists:pei.tab_pei,cod_pei',
        'cod_organizacao' => 'required|uuid|exists:tab_organizacoes,cod_organizacao',
        'ano_base' => 'required|integer|min:2000|max:2100',
        'mes_referencia' => 'required|integer|min:1|max:12',
    ];

    // === MOUNT ===

    public function mount()
    {
        // Valores padrão
        $this->ano_base = now()->year;
        $this->mes_referencia = now()->month;

        // Buscar PEI vigente
        $this->pei = Pei::vigente()->first();

        if ($this->pei) {
            $this->pei_cod = $this->pei->cod_pei;
        }

        // Buscar organização do usuário
        $this->organizacao = auth()->user()->organizacaoPrincipal();

        if ($this->organizacao) {
            $this->cod_organizacao = $this->organizacao->cod_organizacao;
        }

        $this->carregarDados();
    }

    // === MÉTODOS PRINCIPAIS ===

    public function carregarDados()
    {
        if (!$this->pei_cod || !$this->cod_organizacao) {
            return;
        }

        // 1. Carregar Missão, Visão e Valores
        $this->carregarIdentidadeOrganizacional();

        // 2. Carregar Perspectivas e Objetivos (DINÂMICO - apenas se existirem)
        $this->carregarPerspectivasComObjetivos();

        // 3. Calcular Resultados Gerais
        $this->calcularResultadosGerais();

        // 4. Preparar Dados para Gráficos Chart.js
        $this->prepararDadosGraficos();
    }

    private function carregarIdentidadeOrganizacional()
    {
        $this->missaoVisaoValores = MissaoVisaoValores::where('cod_pei', $this->pei_cod)
            ->where('cod_organizacao', $this->cod_organizacao)
            ->with('valores')
            ->first();
    }

    private function carregarPerspectivasComObjetivos()
    {
        // MONTAGEM DINÂMICA: Só carrega perspectivas que foram cadastradas
        $this->perspectivas = Perspectiva::where('cod_pei', $this->pei_cod)
            ->orderBy('num_nivel_hierarquico_apresentacao')
            ->with([
                'objetivosEstrategicos' => function($query) {
                    $query->where('cod_organizacao', $this->cod_organizacao)
                          ->orderBy('num_nivel_hierarquico_apresentacao');
                },
                'objetivosEstrategicos.indicadores.evolucoesIndicador' => function($query) {
                    $query->where('ano_evolucao_indicador', $this->ano_base)
                          ->where('mes_evolucao_indicador', '<=', $this->mes_referencia);
                },
                'objetivosEstrategicos.planosAcao.entregas' => function($query) {
                    $query->whereYear('dte_entrega', $this->ano_base)
                          ->whereMonth('dte_entrega', '<=', $this->mes_referencia);
                }
            ])
            ->get();

        // Calcular desempenho de cada objetivo (APENAS SE TIVER DADOS)
        foreach ($this->perspectivas as $perspectiva) {
            foreach ($perspectiva->objetivosEstrategicos as $objetivo) {
                $objetivo->performance = $this->calcularDesempenhoObjetivo($objetivo);
            }
        }
    }

    private function calcularDesempenhoObjetivo(ObjetivoEstrategico $objetivo)
    {
        $percentualIndicadores = 0;
        $percentualEntregas = 0;
        $totalIndicadores = $objetivo->indicadores->count();
        $totalPlanosAcao = $objetivo->planosAcao->count();

        // 1. Calcular desempenho dos indicadores (SE EXISTIREM)
        if ($totalIndicadores > 0) {
            $somaPercentuais = 0;

            foreach ($objetivo->indicadores as $indicador) {
                $evolucaoRecente = $indicador->evolucoesIndicador->last();

                if ($evolucaoRecente && $indicador->vlr_meta_ano > 0) {
                    $percentualAtingimento = $this->calcularPercentualAtingimento(
                        $evolucaoRecente->vlr_realizado,
                        $indicador->vlr_meta_ano,
                        $indicador->bln_maior_melhor
                    );

                    $somaPercentuais += $percentualAtingimento;
                }
            }

            $percentualIndicadores = $totalIndicadores > 0 ? $somaPercentuais / $totalIndicadores : 0;
        }

        // 2. Calcular desempenho das entregas (SE EXISTIREM)
        if ($totalPlanosAcao > 0) {
            $somaPercentuais = 0;

            foreach ($objetivo->planosAcao as $plano) {
                $totalEntregas = $plano->entregas->count();
                $entregasConcluidas = $plano->entregas->where('vlr_percentual_execucao', 100)->count();

                if ($totalEntregas > 0) {
                    $somaPercentuais += ($entregasConcluidas / $totalEntregas) * 100;
                }
            }

            $percentualEntregas = $totalPlanosAcao > 0 ? $somaPercentuais / $totalPlanosAcao : 0;
        }

        // 3. Calcular média ponderada (60% indicadores, 40% entregas)
        $percentualFinal = 0;

        if ($totalIndicadores > 0 && $totalPlanosAcao > 0) {
            $percentualFinal = ($percentualIndicadores * 0.6) + ($percentualEntregas * 0.4);
        } elseif ($totalIndicadores > 0) {
            $percentualFinal = $percentualIndicadores;
        } elseif ($totalPlanosAcao > 0) {
            $percentualFinal = $percentualEntregas;
        }

        // 4. Retornar dados calculados
        return [
            'percentual' => round($percentualFinal, 1),
            'cor' => $this->determinarCor($percentualFinal),
            'badgeClass' => $this->determinarBadgeClass($percentualFinal),
            'label' => $this->determinarLabel($percentualFinal),
            'total_indicadores' => $totalIndicadores,
            'total_planos' => $totalPlanosAcao,
        ];
    }

    private function calcularPercentualAtingimento($vlrRealizado, $vlrMeta, $blnMaiorMelhor)
    {
        if ($vlrMeta == 0) {
            return 0;
        }

        $percentual = ($vlrRealizado / $vlrMeta) * 100;

        // Se menor é melhor, inverter lógica
        if (!$blnMaiorMelhor) {
            $percentual = 200 - $percentual;
        }

        return max(0, min(100, $percentual)); // Limitar entre 0 e 100
    }

    private function determinarCor($percentual)
    {
        if ($percentual >= 80) {
            return '#65a30d'; // Verde
        } elseif ($percentual >= 60) {
            return '#eab308'; // Amarelo
        } else {
            return '#dc2626'; // Vermelho
        }
    }

    private function determinarBadgeClass($percentual)
    {
        if ($percentual >= 80) {
            return 'bg-success';
        } elseif ($percentual >= 60) {
            return 'bg-warning';
        } else {
            return 'bg-danger';
        }
    }

    private function determinarLabel($percentual)
    {
        if ($percentual >= 80) {
            return 'Ótimo';
        } elseif ($percentual >= 60) {
            return 'Atenção';
        } else {
            return 'Crítico';
        }
    }

    private function calcularResultadosGerais()
    {
        $totalObjetivos = 0;
        $somaPercentuais = 0;

        foreach ($this->perspectivas as $perspectiva) {
            foreach ($perspectiva->objetivosEstrategicos as $objetivo) {
                $totalObjetivos++;
                $somaPercentuais += $objetivo->performance['percentual'];
            }
        }

        $percentualGeral = $totalObjetivos > 0 ? $somaPercentuais / $totalObjetivos : 0;

        $this->resultadosGerais = [
            'percentual_geral' => round($percentualGeral, 1),
            'cor_geral' => $this->determinarCor($percentualGeral),
            'badge_class_geral' => $this->determinarBadgeClass($percentualGeral),
            'label_geral' => $this->determinarLabel($percentualGeral),
            'total_objetivos' => $totalObjetivos,
            'total_perspectivas' => $this->perspectivas->count(),
        ];
    }

    private function prepararDadosGraficos()
    {
        // Gráfico Doughnut (Resultado Geral - Chart.js)
        $this->dadosGraficoDoughnut = [
            'labels' => ['Atingido', 'Restante'],
            'datasets' => [[
                'data' => [
                    $this->resultadosGerais['percentual_geral'],
                    100 - $this->resultadosGerais['percentual_geral']
                ],
                'backgroundColor' => [
                    $this->resultadosGerais['cor_geral'],
                    '#e5e7eb' // Cinza claro
                ],
                'borderWidth' => 0,
            ]]
        ];

        // Gráfico de Barras Horizontais (Perspectivas - Chart.js)
        $labels = [];
        $data = [];
        $backgroundColor = [];

        foreach ($this->perspectivas as $perspectiva) {
            $totalObjetivos = $perspectiva->objetivosEstrategicos->count();

            if ($totalObjetivos > 0) {
                $somaPercentuais = 0;

                foreach ($perspectiva->objetivosEstrategicos as $objetivo) {
                    $somaPercentuais += $objetivo->performance['percentual'];
                }

                $percentualPerspectiva = $somaPercentuais / $totalObjetivos;

                $labels[] = $perspectiva->dsc_perspectiva;
                $data[] = round($percentualPerspectiva, 1);
                $backgroundColor[] = $this->determinarCor($percentualPerspectiva);
            }
        }

        $this->dadosGraficoBarras = [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Desempenho (%)',
                'data' => $data,
                'backgroundColor' => $backgroundColor,
                'borderWidth' => 0,
                'barThickness' => 40,
            ]]
        ];
    }

    // === AÇÕES DO USUÁRIO ===

    public function atualizarOrganizacao($codOrganizacao)
    {
        $this->cod_organizacao = $codOrganizacao;
        $this->organizacao = Organization::find($codOrganizacao);
        $this->carregarDados();
    }

    public function atualizarPei()
    {
        $this->validate(['pei_cod' => 'required|uuid|exists:pei.tab_pei,cod_pei']);
        $this->pei = Pei::find($this->pei_cod);
        $this->carregarDados();
    }

    public function atualizarPeriodo()
    {
        $this->validate([
            'ano_base' => 'required|integer|min:2000|max:2100',
            'mes_referencia' => 'required|integer|min:1|max:12',
        ]);

        $this->carregarDados();
    }

    // === RENDER ===

    public function render()
    {
        return view('livewire.mapa-estrategico.show-dashboard');
    }
}
```

---

## 🎨 BLADE VIEW (UI do Starter Kit)

### Arquivo: `resources/views/livewire/mapa-estrategico/show-dashboard.blade.php`

```blade
<div class="container-fluid py-4">
    {{-- Header com filtros (usando gradient-theme-header do starter kit) --}}
    <div class="card-modern mb-4">
        <div class="gradient-theme-header">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="mb-0 text-white">
                        <i class="bi bi-diagram-3 me-2"></i>
                        Mapa Estratégico
                    </h2>
                    @if($pei)
                        <p class="mb-0 mt-2 opacity-90 text-white">
                            {{ $pei->dsc_pei }} ({{ $pei->num_ano_inicio_pei }} - {{ $pei->num_ano_fim_pei }})
                        </p>
                    @endif
                </div>

                <div class="col-lg-6">
                    <div class="row g-3">
                        {{-- Seletor de PEI --}}
                        <div class="col-md-6">
                            <select wire:model="pei_cod" wire:change="atualizarPei" class="form-select">
                                <option value="">Selecione um PEI</option>
                                @foreach(\App\Models\Pei::orderBy('num_ano_inicio_pei', 'desc')->get() as $peiItem)
                                    <option value="{{ $peiItem->cod_pei }}">
                                        {{ $peiItem->dsc_pei }} ({{ $peiItem->num_ano_inicio_pei }}-{{ $peiItem->num_ano_fim_pei }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Seletor de Organização --}}
                        <div class="col-md-6">
                            <select wire:model="cod_organizacao" wire:change="$refresh" class="form-select">
                                @foreach(auth()->user()->organizacoes as $org)
                                    <option value="{{ $org->cod_organizacao }}">{{ $org->sgl_organizacao }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3">
                {{-- Seletor de Ano --}}
                <div class="col-md-6">
                    <label class="form-label">Ano de Referência</label>
                    <select wire:model="ano_base" wire:change="atualizarPeriodo" class="form-select">
                        @for($ano = now()->year; $ano >= 2020; $ano--)
                            <option value="{{ $ano }}">{{ $ano }}</option>
                        @endfor
                    </select>
                </div>

                {{-- Seletor de Mês --}}
                <div class="col-md-6">
                    <label class="form-label">Mês de Referência</label>
                    <select wire:model="mes_referencia" wire:change="atualizarPeriodo" class="form-select">
                        @foreach(['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'] as $index => $mes)
                            <option value="{{ $index + 1 }}">{{ $mes }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Resultado Geral (Stat Cards do starter kit) --}}
    @if($resultadosGerais)
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card stat-card-primary">
                <div class="stat-card-icon">
                    <i class="bi bi-speedometer2"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-header">
                        <span class="stat-card-label">Desempenho Geral</span>
                        <span class="badge {{ $resultadosGerais['badge_class_geral'] }}">
                            {{ $resultadosGerais['label_geral'] }}
                        </span>
                    </div>
                    <div class="stat-card-value">
                        <span class="stat-number">{{ $resultadosGerais['percentual_geral'] }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card stat-card-success">
                <div class="stat-card-icon">
                    <i class="bi bi-bullseye"></i>
                </div>
                <div class="stat-card-content">
                    <span class="stat-card-label">Objetivos Estratégicos</span>
                    <div class="stat-card-value">
                        <span class="stat-number">{{ $resultadosGerais['total_objetivos'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card stat-card-info">
                <div class="stat-card-icon">
                    <i class="bi bi-layers"></i>
                </div>
                <div class="stat-card-content">
                    <span class="stat-card-label">Perspectivas</span>
                    <div class="stat-card-value">
                        <span class="stat-number">{{ $resultadosGerais['total_perspectivas'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card stat-card-warning">
                <div class="stat-card-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="stat-card-content">
                    <span class="stat-card-label">Período</span>
                    <div class="stat-card-value">
                        <span class="stat-number">{{ $mes_referencia }}/{{ $ano_base }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Gráficos Chart.js --}}
    @if($perspectivas->count() > 0)
    <div class="row g-4 mb-4">
        {{-- Gráfico Doughnut --}}
        <div class="col-lg-4">
            <div class="card-modern">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart me-2"></i>
                        Resultado Acumulado
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartDoughnutGeral"></canvas>
                    <div class="text-center mt-3">
                        <h3 class="mb-0">{{ $resultadosGerais['percentual_geral'] }}%</h3>
                        <p class="text-muted mb-0">Desempenho Geral</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfico de Barras --}}
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart me-2"></i>
                        Desempenho por Perspectiva
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartBarrasPerspectivas"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Missão, Visão e Valores (se existirem - DINÂMICO) --}}
    @if($missaoVisaoValores)
    <div class="card-modern mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body text-white">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-flag-fill fs-2 me-3"></i>
                        <div>
                            <h5 class="mb-2">Missão</h5>
                            <p class="mb-0 opacity-90">{{ $missaoVisaoValores->txt_missao }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-eye-fill fs-2 me-3"></i>
                        <div>
                            <h5 class="mb-2">Visão</h5>
                            <p class="mb-0 opacity-90">{{ $missaoVisaoValores->txt_visao }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-heart-fill fs-2 me-3"></i>
                        <div>
                            <h5 class="mb-2">Valores</h5>
                            <ul class="list-unstyled mb-0">
                                @foreach($missaoVisaoValores->valores as $valor)
                                    <li class="mb-1 opacity-90">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        {{ $valor->dsc_valor }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Perspectivas e Objetivos (MONTAGEM DINÂMICA - só aparece o que foi preenchido) --}}
    @if($perspectivas->count() > 0)
        @foreach($perspectivas as $perspectiva)
        <div class="card-modern mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="mb-1">
                            <i class="bi bi-diagram-3 me-2 text-primary"></i>
                            {{ $perspectiva->dsc_perspectiva }}
                        </h4>
                        <p class="text-muted mb-0">
                            {{ $perspectiva->objetivosEstrategicos->count() }} objetivo(s) estratégico(s)
                        </p>
                    </div>
                </div>

                @if($perspectiva->objetivosEstrategicos->count() > 0)
                <div class="d-flex flex-wrap gap-2">
                    @foreach($perspectiva->objetivosEstrategicos as $objetivo)
                        <span class="badge rounded-pill {{ $objetivo->performance['badgeClass'] }} fs-6 px-3 py-2"
                              data-bs-toggle="tooltip"
                              data-bs-placement="top"
                              title="Desempenho: {{ $objetivo->performance['percentual'] }}% | {{ $objetivo->performance['total_indicadores'] }} indicador(es) | {{ $objetivo->performance['total_planos'] }} plano(s)">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                            {{ $objetivo->num_objetivo_estrategico }}. {{ $objetivo->dsc_objetivo_estrategico }}
                            <span class="ms-2 badge bg-white text-dark">{{ $objetivo->performance['percentual'] }}%</span>
                        </span>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mb-0 mt-2">Nenhum objetivo cadastrado para esta perspectiva.</p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    @else
        {{-- Empty State (do starter kit) --}}
        <div class="card-modern">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                <h5 class="text-muted mb-2">Nenhum dado cadastrado</h5>
                <p class="text-muted mb-0">
                    O Mapa Estratégico é construído dinamicamente.<br>
                    Comece preenchendo: PEI, Missão/Visão, Perspectivas e Objetivos Estratégicos.
                </p>
                <a href="{{ route('pei.create') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle me-2"></i>
                    Cadastrar PEI
                </a>
            </div>
        </div>
    @endif
</div>

{{-- Scripts Chart.js --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuração global Chart.js
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';

    // Gráfico Doughnut
    const ctxDoughnut = document.getElementById('chartDoughnutGeral');
    if (ctxDoughnut) {
        const chartDoughnut = new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: @json($dadosGraficoDoughnut),
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                },
                cutout: '70%',
            }
        });
    }

    // Gráfico de Barras Horizontais
    const ctxBarras = document.getElementById('chartBarrasPerspectivas');
    if (ctxBarras) {
        const chartBarras = new Chart(ctxBarras, {
            type: 'bar',
            data: @json($dadosGraficoBarras),
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Desempenho: ' + context.parsed.x + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: '#e5e7eb'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Inicializar tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Atualizar gráficos quando Livewire recarregar
Livewire.on('refreshMapa', () => {
    location.reload();
});
</script>
@endpush
```

---

## 📊 TABELAS DO BANCO UTILIZADAS

| Tabela | Uso | Campos Principais |
|--------|-----|-------------------|
| `pei.tab_pei` | Ciclo de planejamento | `cod_pei`, `dsc_pei`, `num_ano_inicio_pei`, `num_ano_fim_pei` |
| `tab_organizacoes` | Unidades organizacionais | `cod_organizacao`, `sgl_organizacao`, `nom_organizacao` |
| `pei.tab_missao_visao_valores` | Identidade organizacional | `txt_missao`, `txt_visao` |
| `pei.tab_valores` | Valores da organização | `dsc_valor` |
| `pei.tab_perspectiva` | Perspectivas BSC | `dsc_perspectiva`, `num_nivel_hierarquico_apresentacao` |
| `pei.tab_objetivo_estrategico` | Objetivos estratégicos | `num_objetivo_estrategico`, `dsc_objetivo_estrategico` |
| `pei.tab_indicador` | Indicadores de desempenho | `vlr_meta_ano`, `bln_maior_melhor` |
| `pei.tab_evolucao_indicador` | Evolução mensal | `vlr_previsto`, `vlr_realizado`, `ano_evolucao_indicador`, `mes_evolucao_indicador` |
| `pei.tab_plano_de_acao` | Planos de ação | `dsc_plano_acao` |
| `pei.tab_entregas` | Entregas dos planos | `vlr_percentual_execucao`, `dte_entrega` |
| `pei.tab_grau_satisfacao` | Cores do semáforo | `dsc_cor_semaforo`, `vlr_minimo`, `vlr_maximo` |

---

## 🔄 FLUXO DE MONTAGEM DINÂMICA

```
1. USUÁRIO ACESSA MAPA ESTRATÉGICO
   └─> Sistema verifica se há dados cadastrados

2. SE NÃO HÁ DADOS
   └─> Mostra empty state com instruções
   └─> Botão "Cadastrar PEI" para começar

3. SE HÁ DADOS
   ├─> Carrega PEI vigente
   ├─> Carrega organização do usuário
   └─> Usuário pode filtrar por: PEI, Organização, Ano, Mês

4. SISTEMA MONTA DINAMICAMENTE
   ├─> Se existe Missão/Visão/Valores → Exibe seção roxa
   ├─> Se existem Perspectivas → Carrega e lista
   │   └─> Para cada Perspectiva:
   │       ├─> Se existem Objetivos → Carrega e calcula desempenho
   │       └─> Se NÃO existem → Mostra "Nenhum objetivo cadastrado"
   └─> Se existem dados suficientes → Renderiza gráficos Chart.js

5. CÁLCULO DE DESEMPENHO (APENAS SE TIVER DADOS)
   ├─> Para cada Indicador:
   │   └─> % Atingimento = (Realizado / Meta) * 100
   ├─> Para cada Plano de Ação:
   │   └─> % Execução = (Entregas 100% / Total) * 100
   └─> Para cada Objetivo:
       └─> Performance = (60% Indicadores) + (40% Entregas)

6. COLORAÇÃO DINÂMICA
   ├─> >= 80% = Verde (#65a30d) + Badge success
   ├─> >= 60% = Amarelo (#eab308) + Badge warning
   └─> < 60% = Vermelho (#dc2626) + Badge danger
```

---

## ✅ CHECKLIST DE IMPLEMENTAÇÃO

### Fase 1: Estrutura Base
- [ ] Criar Model `Pei` com scope `vigente()`
- [ ] Criar Model `MissaoVisaoValores` com relacionamento `valores()`
- [ ] Criar Model `Perspectiva` com relacionamento `objetivosEstrategicos()`
- [ ] Criar Model `ObjetivoEstrategico` com relacionamentos `indicadores()` e `planosAcao()`
- [ ] Criar componente Livewire `MapaEstrategico/ShowDashboard`

### Fase 2: Lógica de Negócio
- [ ] Implementar `calcularDesempenhoObjetivo()` (apenas se tiver dados)
- [ ] Implementar `calcularPercentualAtingimento()`
- [ ] Implementar `determinarCor()` e `determinarBadgeClass()`
- [ ] Implementar `calcularResultadosGerais()`
- [ ] Implementar `prepararDadosGraficos()` para Chart.js

### Fase 3: Interface (UI do Starter Kit)
- [ ] Criar Blade view usando `.card-modern` e `.gradient-theme-header`
- [ ] Criar seção de stat cards (`.stat-card`)
- [ ] Criar seção de identidade organizacional (gradiente roxo)
- [ ] Criar cards de perspectivas
- [ ] Criar badges de objetivos com tooltips
- [ ] Implementar empty state quando não houver dados

### Fase 4: Gráficos Chart.js
- [ ] Incluir CDN do Chart.js 4.x
- [ ] Implementar gráfico doughnut (desempenho geral)
- [ ] Implementar gráfico de barras horizontais (perspectivas)
- [ ] Configurar cores dinâmicas baseadas em desempenho
- [ ] Configurar formatação de valores em percentual

### Fase 5: Interatividade
- [ ] Implementar seletor de PEI
- [ ] Implementar seletor de organização
- [ ] Implementar seletor de período (ano/mês)
- [ ] Implementar tooltips Bootstrap 5
- [ ] Implementar reload de gráficos via Livewire

### Fase 6: Testes
- [ ] Testar com banco VAZIO (deve mostrar empty state)
- [ ] Testar com apenas PEI cadastrado
- [ ] Testar com PEI + Missão/Visão/Valores
- [ ] Testar com dados completos (perspectivas + objetivos)
- [ ] Testar cálculo de performance com diferentes cenários
- [ ] Testar responsividade (mobile, tablet, desktop)

### Fase 7: Refinamentos
- [ ] Otimizar queries (Eager Loading)
- [ ] Adicionar loading states do Livewire
- [ ] Revisar acessibilidade (WCAG)
- [ ] Documentar métodos complexos

---

## 🚀 COMANDOS ÚTEIS

```bash
# Criar componente Livewire
php artisan make:livewire MapaEstrategico/ShowDashboard

# Criar Models (se ainda não existirem)
php artisan make:model Pei
php artisan make:model MissaoVisaoValores
php artisan make:model Perspectiva
php artisan make:model ObjetivoEstrategico

# Adicionar rota
# routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/mapa-estrategico', \App\Http\Livewire\MapaEstrategico\ShowDashboard::class)
        ->name('mapa-estrategico');
});

# Limpar cache
php artisan optimize:clear
```

---

## 📝 DIFERENÇAS DO PROJETO ANTIGO

### ✅ O que APROVEITAMOS (apenas a lógica):
- Conceito de montagem dinâmica conforme preenchimento
- Lógica de cálculo de desempenho por objetivo
- Sistema de coloração baseado em percentual

### ❌ O que NÃO usamos:
- ApexCharts → Substituído por **Chart.js**
- CSS customizado antigo → Usando **Bootstrap 5 do starter kit**
- Estrutura de código antiga → Novo código Laravel 12

---

## 🎯 OBSERVAÇÕES FINAIS

### Montagem Dinâmica
O componente é 100% dinâmico:
- Se não há dados → Mostra empty state
- Se há apenas PEI → Mostra filtros
- Se há Missão/Visão → Exibe seção roxa
- Se há Perspectivas → Lista dinamicamente
- Se há Objetivos → Calcula e exibe desempenho
- Gráficos só aparecem se houver dados suficientes

### UI do Starter Kit
Todas as classes CSS são do starter kit atual:
- `.card-modern` para cards
- `.gradient-theme-header` para headers
- `.stat-card-*` para estatísticas
- Badges Bootstrap 5 padrão
- Empty states do starter kit

### Chart.js
Biblioteca escolhida:
- Versão: 4.4.0
- CDN oficial
- Gráficos: Doughnut + Bar (horizontal)
- Cores dinâmicas por desempenho

---

**Desenvolvido para Claude Code (Anthropic)**
*Versão 2.0 - 23 de Dezembro de 2025*
