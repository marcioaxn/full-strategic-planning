<?php

namespace App\Livewire\PerformanceIndicators;

use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\PerformanceIndicators\LinhaBaseIndicador;
use App\Models\PerformanceIndicators\MetaPorAno;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class ListarIndicadores extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    public $search = '';
    public $filtroVinculo = '';
    public $filtroObjetivo = '';
    public $organizacaoId;

    // Modais
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showMetasModal = false;
    public bool $showLinhaBaseModal = false;
    
    public $indicadorId;
    public $indicadorSelecionado;

    // Form Indicador
    public $form = [
        'nom_indicador' => '',
        'dsc_indicador' => '',
        'dsc_tipo' => 'Objetivo',
        'cod_objetivo' => '',
        'cod_plano_de_acao' => '',
        'txt_observacao' => '',
        'dsc_meta' => '',
        'dsc_unidade_medida' => '',
        'num_peso' => 1,
        'bln_acumulado' => 'Não',
        'dsc_formula' => '',
        'dsc_fonte' => '',
        'dsc_periodo_medicao' => 'Mensal',
        'dsc_referencial_comparativo' => '',
        'dsc_atributos' => '',
    ];

    // Form Metas/Linha Base
    public $metaAno;
    public $metaValor;
    public $linhaBaseAno;
    public $linhaBaseValor;

    // Listas Auxiliares
    public $objetivos = [];
    public $planos = [];
    public $periodosOptions = ['Mensal', 'Bimestral', 'Trimestral', 'Semestral', 'Anual'];
    public $grausSatisfacao = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroVinculo' => ['except' => ''],
        'filtroObjetivo' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public $peiAtivo;
    public bool $aiEnabled = false;
    public $aiSuggestion = '';

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI'
    ];

    public function pedirAjudaIA()
    {
        if (!$this->aiEnabled) return;

        if (!$this->filtroObjetivo) {
             session()->flash('error', 'Selecione um Objetivo no filtro primeiro para eu saber o que medir.');
             return;
        }

        $aiService = \App\Services\AI\AiServiceFactory::make();
        if (!$aiService) return;

        $objetivo = Objetivo::find($this->filtroObjetivo);
        
        $this->aiSuggestion = 'Pensando...';

        $prompt = "Sugira 3 indicadores (KPIs) para o objetivo: '{$objetivo->nom_objetivo}'. 
        Descrição do objetivo: '{$objetivo->dsc_objetivo}'.
        Responda OBRIGATORIAMENTE em formato JSON puro, contendo um array de objetos com os campos 'nome', 'descricao', 'unidade' e 'formula'.";
        
        $response = $aiService->suggest($prompt);
        $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

        if (is_array($decoded)) {
            $this->aiSuggestion = $decoded;
        } else {
            $this->aiSuggestion = null;
            session()->flash('error', 'Falha ao processar sugestões. Tente novamente.');
        }
    }

    public function aplicarSugestao($nome, $descricao, $unidade, $formula)
    {
        $this->resetForm();
        $this->form['nom_indicador'] = $nome;
        $this->form['dsc_indicador'] = $descricao;
        $this->form['dsc_unidade_medida'] = $unidade; 
        $this->form['dsc_formula'] = $formula;
        $this->form['cod_objetivo'] = $this->filtroObjetivo;
        $this->form['dsc_tipo'] = 'Objetivo';
        
        $this->save();
        
        // Remove o item da lista de sugestões
        if (is_array($this->aiSuggestion)) {
            $this->aiSuggestion = array_filter($this->aiSuggestion, function($item) use ($nome) {
                return $item['nome'] !== $nome;
            });
            
            if (empty($this->aiSuggestion)) $this->aiSuggestion = '';
        }
    }

    public function mount()
    {
        $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', true);
        $this->carregarPEI();
        $this->atualizarOrganizacao(Session::get('organizacao_selecionada_id'));
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarListasAuxiliares();
        $this->resetPage();
    }

    private function carregarPEI()
    {
        $peiId = Session::get('pei_selecionado_id');

        if ($peiId) {
            $this->peiAtivo = PEI::find($peiId);
        }

        if (!$this->peiAtivo) {
            $this->peiAtivo = PEI::ativos()->first();
        }
    }

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;
        $this->resetPage();
        $this->carregarListasAuxiliares();
    }

    public function carregarListasAuxiliares()
    {
        $this->grausSatisfacao = \App\Models\StrategicPlanning\GrauSatisfacao::orderBy('vlr_minimo')->get();

        if ($this->peiAtivo) {
            $this->objetivos = Objetivo::whereHas('perspectiva', function($query) {
                $query->where('cod_pei', $this->peiAtivo->cod_pei);
            })->orderBy('nom_objetivo')->get();
        }

        if ($this->organizacaoId) {
            $this->planos = PlanoDeAcao::where('cod_organizacao', $this->organizacaoId)->orderBy('dsc_plano_de_acao')->get();
        }
    }

    public function create(\App\Services\PeiGuidanceService $service)
    {
        $guidance = $service->analyzeCompleteness($this->peiAtivo?->cod_pei);
        
        // If we are in any phase BEFORE indicators, redirect and alert
        $phasesBefore = ['ciclo', 'identidade', 'perspectivas', 'objetivos'];
        if ($guidance['status'] === 'warning' && in_array($guidance['current_phase'], $phasesBefore)) {
             session()->flash('error', $guidance['message']);
             return redirect()->route($guidance['action_route']);
        }

        $this->authorize('create', Indicador::class);
        $this->resetForm();
        if (!$this->organizacaoId) {
            $this->dispatch('notify', message: 'Selecione uma organização.', style: 'warning');
            return;
        }
        $this->showModal = true;
    }

    public function edit($id)
    {
        $indicador = Indicador::findOrFail($id);
        $this->authorize('update', $indicador);
        $this->indicadorId = $id;
        $this->form = [
            'nom_indicador' => $indicador->nom_indicador,
            'dsc_indicador' => $indicador->dsc_indicador,
            'dsc_tipo' => $indicador->dsc_tipo ?? ($indicador->cod_plano_de_acao ? 'Plano' : 'Objetivo'),
            'cod_objetivo' => $indicador->cod_objetivo,
            'cod_plano_de_acao' => $indicador->cod_plano_de_acao,
            'txt_observacao' => $indicador->txt_observacao,
            'dsc_meta' => $indicador->dsc_meta,
            'dsc_unidade_medida' => $indicador->dsc_unidade_medida,
            'num_peso' => $indicador->num_peso,
            'bln_acumulado' => $indicador->bln_acumulado,
            'dsc_formula' => $indicador->dsc_formula,
            'dsc_fonte' => $indicador->dsc_fonte,
            'dsc_periodo_medicao' => $indicador->dsc_periodo_medicao,
            'dsc_referencial_comparativo' => $indicador->dsc_referencial_comparativo,
            'dsc_atributos' => $indicador->dsc_atributos,
        ];
        $this->showModal = true;
    }

    public function save()
    {
        $service = app(\App\Services\PeiGuidanceService::class);
        $before = $service->analyzeCompleteness($this->peiAtivo?->cod_pei);

        $this->validate([
            'form.nom_indicador' => 'required|string|max:255',
            'form.dsc_tipo' => 'required',
            'form.dsc_unidade_medida' => 'required',
        ]);

        $data = $this->form;
        if ($data['dsc_tipo'] === 'Objetivo') { $data['cod_plano_de_acao'] = null; } 
        else { $data['cod_objetivo'] = null; }

        if ($this->indicadorId) {
            $indicador = Indicador::findOrFail($this->indicadorId);
            $this->authorize('update', $indicador);
            $indicador->update($data);
        } else {
            $this->authorize('create', Indicador::class);
            $indicador = Indicador::create($data);
            if ($this->organizacaoId) { $indicador->organizacoes()->attach($this->organizacaoId); }
        }

        $after = $service->analyzeCompleteness($this->peiAtivo?->cod_pei);

        $this->dispatch('mentor-notification', 
            title: $this->indicadorId ? 'KPI Atualizado!' : 'KPI Registrado!',
            message: $after['message'],
            icon: 'bi-graph-up-arrow'
        );

        $this->showModal = false;
        session()->flash('status', 'Sucesso!');
    }

    // --- Gestão de Metas ---
    public function abrirMetas($id)
    {
        $this->indicadorSelecionado = Indicador::with('metasPorAno')->findOrFail($id);
        $this->authorize('update', $this->indicadorSelecionado);
        $this->metaAno = now()->year;
        $this->metaValor = '';
        $this->showMetasModal = true;
    }

    public function salvarMeta()
    {
        $this->validate([
            'metaAno' => 'required|integer|min:2000|max:2100',
            'metaValor' => 'required|numeric',
        ]);

        MetaPorAno::updateOrCreate(
            ['cod_indicador' => $this->indicadorSelecionado->cod_indicador, 'num_ano' => $this->metaAno],
            ['meta' => $this->metaValor]
        );

        $this->abrirMetas($this->indicadorSelecionado->cod_indicador); // Refresh
        $this->dispatch('notify', message: 'Meta salva!', style: 'success');
    }

    public function excluirMeta($id)
    {
        MetaPorAno::findOrFail($id)->delete();
        $this->abrirMetas($this->indicadorSelecionado->cod_indicador);
    }

    // --- Gestão de Linha de Base ---
    public function abrirLinhaBase($id)
    {
        $this->indicadorSelecionado = Indicador::with('linhaBase')->findOrFail($id);
        $this->authorize('update', $this->indicadorSelecionado);
        $this->linhaBaseAno = now()->year - 1;
        $this->linhaBaseValor = '';
        $this->showLinhaBaseModal = true;
    }

    public function salvarLinhaBase()
    {
        $this->validate([
            'linhaBaseAno' => 'required|integer|min:2000|max:2100',
            'linhaBaseValor' => 'required|numeric',
        ]);

        LinhaBaseIndicador::updateOrCreate(
            ['cod_indicador' => $this->indicadorSelecionado->cod_indicador, 'num_ano' => $this->linhaBaseAno],
            ['num_linha_base' => $this->linhaBaseValor]
        );

        $this->abrirLinhaBase($this->indicadorSelecionado->cod_indicador);
        $this->dispatch('notify', message: 'Linha de base salva!', style: 'success');
    }

    public function excluirLinhaBase($id)
    {
        LinhaBaseIndicador::findOrFail($id)->delete();
        $this->abrirLinhaBase($this->indicadorSelecionado->cod_indicador);
    }

    public function confirmDelete($id)
    {
        $this->indicadorId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        Indicador::findOrFail($this->indicadorId)->delete();
        $this->showDeleteModal = false;
    }

    public function resetForm()
    {
        $this->indicadorId = null;
        $this->form = [
            'nom_indicador' => '', 'dsc_indicador' => '', 'dsc_tipo' => 'Objetivo',
            'cod_objetivo' => '', 'cod_plano_de_acao' => '', 'txt_observacao' => '',
            'dsc_meta' => '', 'dsc_unidade_medida' => '', 'num_peso' => 1, 'bln_acumulado' => 'Não',
            'dsc_formula' => '', 'dsc_fonte' => '', 'dsc_periodo_medicao' => 'Mensal',
            'dsc_referencial_comparativo' => '', 'dsc_atributos' => '',
        ];
    }

    public function render()
    {
        $query = Indicador::query()->with(['objetivo', 'planoDeAcao', 'evolucoes', 'metasPorAno']);

        // Se há filtro por objetivo específico, prioriza esse filtro
        if ($this->filtroObjetivo) {
            // Busca indicadores diretamente vinculados ao objetivo
            // OU vinculados a planos de ação desse objetivo
            $query->where(function($q) {
                $q->where('cod_objetivo', $this->filtroObjetivo)
                  ->orWhereHas('planoDeAcao', function($sub) {
                      $sub->where('cod_objetivo', $this->filtroObjetivo);
                  });
            });
        } elseif ($this->organizacaoId) {
            // Filtro padrão por organização (quando não há filtro por objetivo)
            $query->where(function($q) {
                $q->whereHas('organizacoes', function($sub) {
                    $sub->where('tab_organizacoes.cod_organizacao', $this->organizacaoId);
                })->orWhereHas('planoDeAcao', function($sub) {
                    $sub->where('cod_organizacao', $this->organizacaoId);
                });
            });
        }

        if ($this->search) {
            $query->where('nom_indicador', 'ilike', '%' . $this->search . '%');
        }

        if ($this->filtroVinculo === 'Objetivo') {
            $query->deObjetivo();
        } elseif ($this->filtroVinculo === 'Plano') {
            $query->dePlano();
        }

        return view('livewire.indicador.listar-indicadores', [
            'indicadores' => $query->paginate(10)
        ]);
    }
}
