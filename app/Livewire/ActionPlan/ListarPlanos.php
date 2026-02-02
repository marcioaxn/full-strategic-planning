<?php

namespace App\Livewire\ActionPlan;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class ListarPlanos extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    public $search = '';
    public $filtroStatus = '';
    public $filtroTipo = '';
    public $filtroAno = '';
    public $filtroObjetivo = '';
    public $organizacaoId;
    public $organizacaoNome;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public $planoId;

    // Campos do Formulário
    public $dsc_plano_de_acao;
    public $txt_detalhamento;
    public $cod_objetivo;
    public $cod_tipo_execucao;
    public $dte_inicio;
    public $dte_fim;
    public $vlr_orcamento_previsto = 0;
    public $bln_status = 'Não Iniciado';
    public $cod_ppa;
    public $cod_loa;

    public $organizacoes_ids = []; // Suporte a multivinculação
    public $organizacoesOptions = []; // Lista em árvore

    public bool $aiEnabled = false;
    public $aiSuggestion = '';

    // Success Modal Properties
    public bool $showSuccessModal = false;
    public $createdPlanName = '';
    public $createdPlanType = '';

    // Listas auxiliares
    public $objetivos = [];
    public $tiposExecucao = [];
    public $statusOptions = ['Não Iniciado', 'Em Andamento', 'Concluído', 'Atrasado', 'Suspenso', 'Cancelado'];
    public $grausSatisfacao = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroStatus' => ['except' => ''],
        'filtroTipo' => ['except' => ''],
        'filtroAno' => ['except' => ''],
        'filtroObjetivo' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public $peiAtivo;

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
        'anoSelecionado' => 'atualizarAno'
    ];

    public function mount()
    {
        $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', true);
        $this->carregarPEI();
        $this->atualizarOrganizacao(Session::get('organizacao_selecionada_id'));
        $this->tiposExecucao = TipoExecucao::orderBy('dsc_tipo_execucao')->get();
        $this->filtroAno = Session::get('ano_selecionado', now()->year);
        $this->organizacoesOptions = Organization::getTreeForSelector();
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->createdPlanName = '';
        $this->createdPlanType = '';
    }

    public function pedirAjudaIA()
    {
        if (!$this->aiEnabled) return;
        
        if (!$this->cod_objetivo) {
             session()->flash('error', 'Selecione um objetivo no formulário primeiro.');
             return;
        }

        try {
            $aiService = \App\Services\AI\AiServiceFactory::make();
            if (!$aiService) return;

            $objetivo = Objetivo::find($this->cod_objetivo);
            if (!$objetivo) {
                session()->flash('error', 'Objetivo não encontrado.');
                return;
            }

            $this->aiSuggestion = 'Pensando...';
            
            $prompt = "Sugira 3 planos de ação (iniciativas) para alcançar o objetivo estratégico: '{$objetivo->nom_objetivo}'.
            Leve em conta que a organização é: {$this->organizacaoNome}.
            Responda OBRIGATORIAMENTE em formato JSON puro, contendo um array de objetos com os campos 'nome' e 'justificativa'.
            O campo 'justificativa' deve ser detalhado e explicar como o plano ajuda a alcançar o objetivo.";
            
            $response = $aiService->suggest($prompt);
            $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

            if (is_array($decoded)) {
                $this->aiSuggestion = $decoded;
            } else {
                throw new \Exception('Falha ao decodificar resposta da IA.');
            }
        } catch (\Exception $e) {
            \Log::error('Erro IA Planos: ' . $e->getMessage());
            $this->aiSuggestion = null;
            session()->flash('error', 'Não foi possível gerar sugestões no momento.');
        }
    }

    public function aplicarSugestao($nome, $justificativa = null)
    {
        $this->dsc_plano_de_acao = $nome;
        if ($justificativa) {
            $this->txt_detalhamento = $justificativa;
        }
        $this->aiSuggestion = '';
    }

    public function atualizarAno($ano)
    {
        $this->filtroAno = $ano;
        $this->resetPage();
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarObjetivos();
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
        $this->organizacaoNome = $id ? Organization::find($id)?->nom_organizacao : null;
        $this->resetPage();
        $this->carregarObjetivos();
    }

    public function carregarObjetivos()
    {
        $this->grausSatisfacao = \App\Models\StrategicPlanning\GrauSatisfacao::orderBy('vlr_minimo')->get();

        if ($this->peiAtivo) {
            // Carrega objetivos e agrupa por nome da perspectiva, convertendo para array para estabilidade do Livewire
            $this->objetivos = Objetivo::whereHas('perspectiva', function($query) {
                $query->where('cod_pei', $this->peiAtivo->cod_pei);
            })->with('perspectiva')->orderBy('nom_objetivo')->get()
            ->groupBy('perspectiva.dsc_perspectiva')
            ->toArray();
        }
    }

    public function create()
    {
        try {
            $this->authorize('create', PlanoDeAcao::class);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->dispatch('notify', message: 'Você não tem permissão para criar planos.', style: 'danger');
            return;
        }

        $this->resetForm();
        
        if (!$this->organizacaoId) {
            $this->dispatch('notify', message: 'Selecione uma organização no menu superior.', style: 'warning');
            return;
        }

        $this->showModal = true;
    }

    public function edit($id)
    {
        $plano = PlanoDeAcao::with('organizacoes')->findOrFail($id);
        $this->authorize('update', $plano);

        $this->planoId = $id;
        $this->dsc_plano_de_acao = $plano->dsc_plano_de_acao;
        $this->txt_detalhamento = $plano->txt_detalhamento;
        $this->cod_objetivo = $plano->cod_objetivo;
        $this->cod_tipo_execucao = $plano->cod_tipo_execucao;
        $this->dte_inicio = $plano->dte_inicio?->format('Y-m-d');
        $this->dte_fim = $plano->dte_fim?->format('Y-m-d');
        $this->vlr_orcamento_previsto = $plano->vlr_orcamento_previsto;
        $this->bln_status = $plano->bln_status;
        $this->cod_ppa = $plano->cod_ppa;
        $this->cod_loa = $plano->cod_loa;
        $this->organizacoes_ids = $plano->organizacoes->pluck('cod_organizacao')->toArray();

        $this->showModal = true;
    }

    public function save()
    {
        $messages = [
            'dsc_plano_de_acao.required' => 'A descrição do plano é obrigatória.',
            'cod_objetivo.required' => 'Vincule o plano a um objetivo estratégico.',
            'cod_tipo_execucao.required' => 'Defina o tipo de execução (Projeto, Atividade, etc).',
            'dte_inicio.required' => 'A data de início é obrigatória.',
            'dte_fim.required' => 'A data de término é obrigatória.',
            'dte_fim.after_or_equal' => 'A data final não pode ser anterior à data inicial.',
        ];

        $this->validate([
            'dsc_plano_de_acao' => 'required|string|max:255',
            'txt_detalhamento' => 'nullable|string',
            'cod_objetivo' => 'required|exists:tab_objetivo,cod_objetivo',
            'cod_tipo_execucao' => 'required|exists:tab_tipo_execucao,cod_tipo_execucao',
            'dte_inicio' => 'required|date',
            'dte_fim' => 'required|date|after_or_equal:dte_inicio',
            'vlr_orcamento_previsto' => 'nullable|numeric|min:0',
            'organizacoes_ids' => 'required|array|min:1',
        ], $messages);


        $data = [
            'dsc_plano_de_acao' => $this->dsc_plano_de_acao,
            'txt_detalhamento' => $this->txt_detalhamento,
            'cod_objetivo' => $this->cod_objetivo,
            'cod_tipo_execucao' => $this->cod_tipo_execucao,
            'dte_inicio' => $this->dte_inicio,
            'dte_fim' => $this->dte_fim,
            'vlr_orcamento_previsto' => $this->vlr_orcamento_previsto,
            'bln_status' => $this->bln_status,
            'cod_ppa' => $this->cod_ppa,
            'cod_loa' => $this->cod_loa,
            'cod_organizacao' => $this->organizacoes_ids[0], // Mantém compatibilidade legada
            'num_nivel_hierarquico_apresentacao' => 3, // Padrão: 3 (Nível Operacional/Ação)
        ];

        if ($this->planoId) {
            $plano = PlanoDeAcao::findOrFail($this->planoId);
            $plano->update($data);
            $plano->organizacoes()->sync($this->organizacoes_ids);
        } else {
            $plano = PlanoDeAcao::create($data);
            $plano->organizacoes()->sync($this->organizacoes_ids);
        }

        // Capture details for success modal before resetting
        $tipo = TipoExecucao::find($this->cod_tipo_execucao)->dsc_tipo_execucao ?? 'Item';
        $this->createdPlanType = $tipo;
        $this->createdPlanName = $this->dsc_plano_de_acao;
        
        $this->showModal = false;
        $this->resetForm();
        
        // Show success modal instead of flash message
        $this->showSuccessModal = true;
    }

    public function confirmDelete($id)
    {
        $this->planoId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $plano = PlanoDeAcao::findOrFail($this->planoId);
        $this->authorize('delete', $plano);
        
        $plano->delete();
        $this->showDeleteModal = false;
        
        $this->dispatch('mentor-notification', 
            title: 'Plano Removido',
            message: 'O plano de ação foi excluído do sistema.',
            icon: 'bi-trash',
            type: 'warning'
        );
    }

    public function resetForm()
    {
        $this->planoId = null;
        $this->dsc_plano_de_acao = '';
        $this->txt_detalhamento = '';
        $this->cod_objetivo = '';
        $this->cod_tipo_execucao = '';
        $this->dte_inicio = null;
        $this->dte_fim = null;
        $this->vlr_orcamento_previsto = 0;
        $this->bln_status = 'Não Iniciado';
        $this->cod_ppa = '';
        $this->cod_loa = '';
        $this->organizacoes_ids = $this->organizacaoId ? [$this->organizacaoId] : [];
        $this->aiSuggestion = '';
    }

    public function render()
    {
        $query = PlanoDeAcao::query()
            ->with(['objetivo', 'tipoExecucao', 'organizacoes']);

        if ($this->filtroObjetivo) {
            $query->where('cod_objetivo', $this->filtroObjetivo);
        } elseif ($this->organizacaoId) {
            // Filtro por organização considerando multivinculação
            $query->whereHas('organizacoes', function($sub) {
                $sub->where('tab_organizacoes.cod_organizacao', $this->organizacaoId);
            });
        }

        if ($this->search) {
            $query->where('dsc_plano_de_acao', 'ilike', '%' . $this->search . '%');
        }

        if ($this->filtroStatus) {
            $query->where('bln_status', $this->filtroStatus);
        }

        if ($this->filtroTipo) {
            $query->where('cod_tipo_execucao', $this->filtroTipo);
        }

        if ($this->filtroAno) {
            $query->where(function($q) {
                $q->whereYear('dte_inicio', $this->filtroAno)
                  ->orWhereYear('dte_fim', $this->filtroAno);
            });
        }

        return view('livewire.plano-acao.listar-planos', [
            'planos' => $query->orderBy('dte_fim')->paginate(10)
        ]);
    }
}