<?php

namespace App\Livewire\PlanoAcao;

use App\Models\PEI\PlanoDeAcao;
use App\Models\PEI\TipoExecucao;
use App\Models\PEI\ObjetivoEstrategico;
use App\Models\PEI\PEI;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Eloquent\Builder;
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
    
    // Filtro global de organização
    public $organizacaoId;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public $planoId;

    // Campos do Formulário
    public $dsc_plano_de_acao;
    public $cod_tipo_execucao;
    public $cod_objetivo_estrategico;
    public $dte_inicio;
    public $dte_fim;
    public $vlr_orcamento_previsto;
    public $bln_status = 'Não Iniciado';
    public $cod_ppa;
    public $cod_loa;

    // Dados para Dropdowns
    public $tiposExecucao = [];
    public $objetivos = [];
    public $statusOptions = ['Não Iniciado', 'Em Andamento', 'Concluído', 'Suspenso', 'Cancelado'];

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroStatus' => ['except' => ''],
        'filtroTipo' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao'
    ];

    public function mount()
    {
        $this->atualizarOrganizacao(Session::get('organizacao_selecionada_id'));
        $this->tiposExecucao = TipoExecucao::orderBy('dsc_tipo_execucao')->get();
        $this->filtroAno = now()->year;
    }

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;
        $this->resetPage();
        $this->carregarObjetivos();
    }

    public function carregarObjetivos()
    {
        // Carrega objetivos do PEI ativo
        $peiAtivo = PEI::ativos()->first();
        if ($peiAtivo) {
            $this->objetivos = ObjetivoEstrategico::where('cod_pei', $peiAtivo->cod_pei) // Ajustado para usar cod_pei direto do model
                ->with('perspectiva')
                ->get()
                ->sortBy(['perspectiva.num_nivel_hierarquico_apresentacao', 'num_nivel_hierarquico_apresentacao']);
        } else {
            $this->objetivos = [];
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->authorize('create', PlanoDeAcao::class);
        $this->resetForm();
        
        // Se houver uma organização selecionada, define no form (embora não esteja explicitamente no form visual, será salvo no backend)
        // Se não houver, o usuário precisará selecionar (mas por enquanto estamos assumindo o contexto global)
        if (!$this->organizacaoId) {
            $this->dispatch('notify', message: 'Selecione uma organização no menu superior para criar um plano.', style: 'warning');
            return;
        }

        $this->showModal = true;
    }

    public function edit($id)
    {
        $plano = PlanoDeAcao::findOrFail($id);
        $this->authorize('update', $plano);

        $this->planoId = $id;
        $this->dsc_plano_de_acao = $plano->dsc_plano_de_acao;
        $this->cod_tipo_execucao = $plano->cod_tipo_execucao;
        $this->cod_objetivo_estrategico = $plano->cod_objetivo_estrategico;
        $this->dte_inicio = $plano->dte_inicio?->format('Y-m-d');
        $this->dte_fim = $plano->dte_fim?->format('Y-m-d');
        $this->vlr_orcamento_previsto = $plano->vlr_orcamento_previsto;
        $this->bln_status = $plano->bln_status;
        $this->cod_ppa = $plano->cod_ppa;
        $this->cod_loa = $plano->cod_loa;

        $this->showModal = true;
    }

    public function save()
    {
        $rules = [
            'dsc_plano_de_acao' => 'required|string|max:500',
            'cod_tipo_execucao' => 'required|exists:pei.tab_tipo_execucao,cod_tipo_execucao',
            'cod_objetivo_estrategico' => 'required|exists:pei.tab_objetivo_estrategico,cod_objetivo_estrategico',
            'dte_inicio' => 'required|date',
            'dte_fim' => 'required|date|after_or_equal:dte_inicio',
            'vlr_orcamento_previsto' => 'nullable|numeric|min:0',
            'bln_status' => 'required|in:' . implode(',', $this->statusOptions),
            'cod_ppa' => 'nullable|string|max:20',
            'cod_loa' => 'nullable|string|max:20',
        ];

        $this->validate($rules);

        if (!$this->organizacaoId && !$this->planoId) {
             $this->dispatch('notify', message: 'Erro: Organização não identificada.', style: 'danger');
             return;
        }

        $data = [
            'dsc_plano_de_acao' => $this->dsc_plano_de_acao,
            'cod_tipo_execucao' => $this->cod_tipo_execucao,
            'cod_objetivo_estrategico' => $this->cod_objetivo_estrategico,
            'dte_inicio' => $this->dte_inicio,
            'dte_fim' => $this->dte_fim,
            'vlr_orcamento_previsto' => $this->vlr_orcamento_previsto ?? 0,
            'bln_status' => $this->bln_status,
            'cod_ppa' => $this->cod_ppa,
            'cod_loa' => $this->cod_loa,
        ];

        if ($this->planoId) {
            $plano = PlanoDeAcao::findOrFail($this->planoId);
            $this->authorize('update', $plano);
            $plano->update($data);
            $message = 'Plano atualizado com sucesso!';
        } else {
            $this->authorize('create', PlanoDeAcao::class);
            $data['cod_organizacao'] = $this->organizacaoId;
            // Nível hierárquico padrão = 1 (pode ser ajustado futuramente)
            $data['num_nivel_hierarquico_apresentacao'] = 1;
            
            PlanoDeAcao::create($data);
            $message = 'Plano criado com sucesso!';
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('message', $message);
        session()->flash('style', 'success');
    }

    public function confirmDelete($id)
    {
        $plano = PlanoDeAcao::findOrFail($id);
        $this->authorize('delete', $plano);
        $this->planoId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->planoId) {
            $plano = PlanoDeAcao::findOrFail($this->planoId);
            $this->authorize('delete', $plano);
            $plano->delete();
            
            session()->flash('message', 'Plano excluído com sucesso!');
            session()->flash('style', 'success');
        }
        $this->showDeleteModal = false;
        $this->planoId = null;
    }

    public function resetForm()
    {
        $this->planoId = null;
        $this->dsc_plano_de_acao = '';
        $this->cod_tipo_execucao = '';
        $this->cod_objetivo_estrategico = '';
        $this->dte_inicio = '';
        $this->dte_fim = '';
        $this->vlr_orcamento_previsto = '';
        $this->bln_status = 'Não Iniciado';
        $this->cod_ppa = '';
        $this->cod_loa = '';
    }

    public function render()
    {
        $query = PlanoDeAcao::query()
            ->with(['objetivoEstrategico', 'tipoExecucao', 'organizacao'])
            ->orderBy('dte_inicio', 'desc');

        // Filtro por Organização
        if ($this->organizacaoId) {
            // Se tem organização selecionada, mostra planos dela
            // Aqui poderíamos incluir filhas também, mas planos geralmente são específicos da unidade
            $query->where('cod_organizacao', $this->organizacaoId);
        }

        // Busca textual
        if ($this->search) {
            $query->where('dsc_plano_de_acao', 'ilike', '%' . $this->search . '%');
        }

        // Filtros
        if ($this->filtroStatus) {
            $query->where('bln_status', $this->filtroStatus);
        }

        if ($this->filtroTipo) {
            $query->where('cod_tipo_execucao', $this->filtroTipo);
        }
        
        if ($this->filtroAno) {
            $query->whereYear('dte_inicio', '<=', $this->filtroAno)
                  ->whereYear('dte_fim', '>=', $this->filtroAno);
        }

        return view('livewire.plano-acao.listar-planos', [
            'planos' => $query->paginate(10)
        ]);
    }
}