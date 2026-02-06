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
    public $peiAtivo;

    // IA e Mentor
    public bool $aiEnabled = false;
    public $aiSuggestion = '';
    public $smartFeedback = '';

    // Modais
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showMetasModal = false;
    public bool $showLinhaBaseModal = false;
    
    public $indicadorId;
    public $indicadorSelecionado;

    // Success Modal Properties
    public bool $showSuccessModal = false;
    public bool $showErrorModal = false;
    public string $successMessage = '';
    public string $errorMessage = '';
    public string $createdIndicadorName = '';

    // Form Indicador
    public $form = [
        'nom_indicador' => '',
        'dsc_indicador' => '',
        'dsc_tipo' => 'Objetivo',
        'dsc_calculation_type' => 'manual',
        'cod_objetivo' => '',
        'cod_plano_de_acao' => '',
        'txt_observacao' => '',
        'dsc_meta' => '',
        'dsc_unidade_medida' => 'Percentual (%)',
        'dsc_polaridade' => 'Positiva',
        'num_peso' => 1,
        'bln_acumulado' => 'Não',
        'dsc_formula' => '',
        'dsc_fonte' => '',
        'dsc_periodo_medicao' => 'Mensal',
        'dsc_referencial_comparativo' => '',
        'dsc_atributos' => '',
        'organizacoes_ids' => [], // IDs para multivinculação
    ];

    // Form Metas/Linha Base
    public $metaAno;
    public $metaValor;
    public $linhaBaseAno;
    public $linhaBaseValor;

    // Listas Auxiliares
    public $objetivosAgrupados = [];
    public $planosAgrupados = [];
    public $organizacoesOptions = [];
    public $unidadesMedida = [];
    public $polaridades = [];
    public $calculationTypes = [];
    public $periodosOptions = ['Mensal', 'Bimestral', 'Trimestral', 'Semestral', 'Anual'];
    public $grausSatisfacao = [];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI'
    ];

    public function mount()
    {
        $this->organizacaoId = Session::get('organizacao_selecionada_id');
        $this->peiAtivo = PEI::find(Session::get('pei_selecionado_id')) ?? PEI::ativos()->first();
        
        $this->carregarListasAuxiliares();
        
        // Verifica se a IA está habilitada nas configurações do sistema
        try {
            $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', false);
        } catch (\Exception $e) {
            $this->aiEnabled = false;
        }
    }

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;
        $this->resetPage();
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarListasAuxiliares();
        $this->resetPage();
    }

    public function carregarListasAuxiliares()
    {
        $this->grausSatisfacao = \App\Models\StrategicPlanning\GrauSatisfacao::orderBy('vlr_minimo')->get();
        $this->unidadesMedida = Indicador::UNIDADES_MEDIDA;
        $this->polaridades = Indicador::POLARIDADES;
        $this->calculationTypes = Indicador::CALCULATION_TYPES;
        $this->organizacoesOptions = Organization::getTreeForSelector();

        if ($this->peiAtivo) {
            // Agrupar objetivos por perspectiva para o select
            $objetivos = Objetivo::whereHas('perspectiva', function($q) {
                $q->where('cod_pei', $this->peiAtivo->cod_pei);
            })->with('perspectiva')->get();

            $this->objetivosAgrupados = $objetivos->groupBy(function($obj) {
                return $obj->perspectiva->dsc_perspectiva;
            })->map(function($group) {
                return $group->map(function($obj) {
                    return [
                        'cod_objetivo' => $obj->cod_objetivo,
                        'nom_objetivo' => $obj->nom_objetivo
                    ];
                });
            })->toArray();

            // Agrupar planos por objetivo
            $planos = PlanoDeAcao::whereHas('objetivo.perspectiva', function($q) {
                $q->where('cod_pei', $this->peiAtivo->cod_pei);
            })->with('objetivo')->get();

            $this->planosAgrupados = $planos->groupBy(function($plano) {
                return $plano->objetivo->nom_objetivo ?? 'Sem Objetivo';
            })->map(function($group) {
                return $group->map(function($plano) {
                    return [
                        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
                        'dsc_plano_de_acao' => $plano->dsc_plano_de_acao
                    ];
                });
            })->toArray();
        }
    }

    public function create(\App\Services\PeiGuidanceService $service)
    {
        // ... (guidance check)
        $this->authorize('create', Indicador::class);
        $this->resetForm();
        if ($this->organizacaoId) {
            $this->form['organizacoes_ids'] = [$this->organizacaoId];
        }
        $this->showModal = true;
    }

    public function edit($id)
    {
        $indicador = Indicador::with('organizacoes')->findOrFail($id);
        $this->authorize('update', $indicador);
        $this->indicadorId = $id;
        $this->form = [
            'nom_indicador' => $indicador->nom_indicador,
            'dsc_indicador' => $indicador->dsc_indicador,
            'dsc_tipo' => $indicador->dsc_tipo ?? ($indicador->cod_plano_de_acao ? 'Plano' : 'Objetivo'),
            'dsc_calculation_type' => $indicador->dsc_calculation_type ?? 'manual',
            'cod_objetivo' => $indicador->cod_objetivo,
            'cod_plano_de_acao' => $indicador->cod_plano_de_acao,
            'txt_observacao' => $indicador->txt_observacao,
            'dsc_meta' => $indicador->dsc_meta,
            'dsc_unidade_medida' => $indicador->dsc_unidade_medida,
            'dsc_polaridade' => $indicador->dsc_polaridade ?? 'Positiva',
            'num_peso' => $indicador->num_peso,
            'bln_acumulado' => $indicador->bln_acumulado,
            'dsc_formula' => $indicador->dsc_formula,
            'dsc_fonte' => $indicador->dsc_fonte,
            'dsc_periodo_medicao' => $indicador->dsc_periodo_medicao,
            'dsc_referencial_comparativo' => $indicador->dsc_referencial_comparativo,
            'dsc_atributos' => $indicador->dsc_atributos,
            'organizacoes_ids' => $indicador->organizacoes->pluck('cod_organizacao')->toArray(),
        ];
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'form.nom_indicador' => 'required|string|max:255',
            'form.dsc_tipo' => 'required',
            'form.dsc_unidade_medida' => 'required',
            'form.organizacoes_ids' => 'required|array|min:1',
        ]);

        try {
            $data = $this->form;
            $orgIds = $data['organizacoes_ids'];
            unset($data['organizacoes_ids']);

            if ($data['dsc_tipo'] === 'Objetivo') { 
                $data['cod_plano_de_acao'] = null; 
            } else { 
                $data['cod_objetivo'] = null; 
            }

            if ($this->indicadorId) {
                $indicador = Indicador::findOrFail($this->indicadorId);
                $this->authorize('update', $indicador);
                $indicador->update($data);
                $indicador->organizacoes()->sync($orgIds);
                $this->successMessage = "As configurações do indicador foram atualizadas com sucesso e as organizações vinculadas já refletem as mudanças.";
            } else {
                $this->authorize('create', Indicador::class);
                $indicador = Indicador::create($data);
                $indicador->organizacoes()->sync($orgIds);
                $this->successMessage = "O novo indicador foi registrado com sucesso e vinculado às unidades organizacionais selecionadas.";
            }

            $this->createdIndicadorName = $this->form['nom_indicador'];
            $this->showModal = false;
            $this->showSuccessModal = true;

        } catch (\Exception $e) {
            $this->errorMessage = "Não foi possível processar o registro do indicador. Por favor, revise as informações e tente novamente.";
            $this->showErrorModal = true;
        }
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
        
        $this->dispatch('mentor-notification', 
            title: 'Indicador Removido',
            message: 'O KPI foi excluído com sucesso.',
            icon: 'bi-trash',
            type: 'warning'
        );
    }

    public function resetForm()
    {
        $this->indicadorId = null;
        $this->form = [
            'nom_indicador' => '', 'dsc_indicador' => '', 'dsc_tipo' => 'Objetivo',
            'dsc_calculation_type' => 'manual',
            'cod_objetivo' => '', 'cod_plano_de_acao' => '', 'txt_observacao' => '',
            'dsc_meta' => '', 'dsc_unidade_medida' => 'Percentual (%)', 'dsc_polaridade' => 'Positiva', 'num_peso' => 1, 'bln_acumulado' => 'Não',
            'dsc_formula' => '', 'dsc_fonte' => '', 'dsc_periodo_medicao' => 'Mensal',
            'dsc_referencial_comparativo' => '', 'dsc_atributos' => '',
        ];
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->createdIndicadorName = '';
        $this->resetForm();
    }

    public function closeErrorModal()
    {
        $this->showErrorModal = false;
    }

    public function pedirAjudaIA()
    {
        // Simulação de IA por enquanto ou integração real se houver service
        $this->aiSuggestion = [
            ['nome' => 'Índice de Eficiência Operacional', 'descricao' => 'Mede a relação entre recursos utilizados e resultados alcançados.', 'unidade' => 'Percentual (%)', 'formula' => '(Resultados / Recursos) * 100'],
            ['nome' => 'Taxa de Cumprimento de Prazos', 'descricao' => 'Percentual de entregas realizadas dentro do cronograma previsto.', 'unidade' => 'Percentual (%)', 'formula' => '(Entregas no Prazo / Total de Entregas) * 100'],
        ];
    }

    public function aplicarSugestao($nome, $desc, $unidade, $formula)
    {
        $this->resetForm();
        $this->form['nom_indicador'] = $nome;
        $this->form['dsc_indicador'] = $desc;
        $this->form['dsc_unidade_medida'] = $unidade;
        $this->form['dsc_formula'] = $formula;
        if ($this->organizacaoId) {
            $this->form['organizacoes_ids'] = [$this->organizacaoId];
        }
        $this->aiSuggestion = '';
        $this->showModal = true;
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
            // Filtro por organização considerando multivinculação
            $query->where(function($q) {
                $q->whereHas('organizacoes', function($sub) {
                    $sub->where('tab_organizacoes.cod_organizacao', $this->organizacaoId);
                })->orWhereHas('planoDeAcao', function($sub) {
                    $sub->whereHas('organizacoes', function($subOrg) {
                        $subOrg->where('tab_organizacoes.cod_organizacao', $this->organizacaoId);
                    });
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
