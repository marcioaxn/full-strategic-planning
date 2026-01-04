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

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public $planoId;

    // Campos do Formulário
    public $dsc_plano_de_acao;
    public $cod_objetivo;
    public $cod_tipo_execucao;
    public $dte_inicio;
    public $dte_fim;
    public $vlr_orcamento_previsto = 0;
    public $bln_status = 'Não Iniciado';
    public $cod_ppa;
    public $cod_loa;

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
        'peiSelecionado' => 'atualizarPEI'
    ];

    public function mount()
    {
        $this->carregarPEI();
        $this->atualizarOrganizacao(Session::get('organizacao_selecionada_id'));
        $this->tiposExecucao = TipoExecucao::orderBy('dsc_tipo_execucao')->get();
        $this->filtroAno = now()->year;
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
        $this->resetPage();
        $this->carregarObjetivos();
    }

    public function carregarObjetivos()
    {
        $this->grausSatisfacao = \App\Models\StrategicPlanning\GrauSatisfacao::orderBy('vlr_minimo')->get();

        if ($this->peiAtivo) {
            $this->objetivos = Objetivo::whereHas('perspectiva', function($query) {
                $query->where('cod_pei', $this->peiAtivo->cod_pei);
            })->with('perspectiva')->orderBy('nom_objetivo')->get();
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
        
        if (!$this->organizacaoId) {
            $this->dispatch('notify', message: 'Selecione uma organização no menu superior.', style: 'warning');
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
        $this->cod_objetivo = $plano->cod_objetivo;
        $this->cod_tipo_execucao = $plano->cod_tipo_execucao;
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
        $this->validate([
            'dsc_plano_de_acao' => 'required|string|max:255',
            'cod_objetivo' => 'required|exists:pei.tab_objetivo,cod_objetivo',
            'cod_tipo_execucao' => 'required|exists:pei.tab_tipo_execucao,cod_tipo_execucao',
            'dte_inicio' => 'required|date',
            'dte_fim' => 'required|date|after_or_equal:dte_inicio',
            'vlr_orcamento_previsto' => 'nullable|numeric|min:0',
        ]);

        $data = [
            'dsc_plano_de_acao' => $this->dsc_plano_de_acao,
            'cod_objetivo' => $this->cod_objetivo,
            'cod_tipo_execucao' => $this->cod_tipo_execucao,
            'dte_inicio' => $this->dte_inicio,
            'dte_fim' => $this->dte_fim,
            'vlr_orcamento_previsto' => $this->vlr_orcamento_previsto,
            'bln_status' => $this->bln_status,
            'cod_ppa' => $this->cod_ppa,
            'cod_loa' => $this->cod_loa,
            'cod_organizacao' => $this->organizacaoId,
        ];

        if ($this->planoId) {
            PlanoDeAcao::findOrFail($this->planoId)->update($data);
            $msg = 'Plano de ação atualizado com sucesso!';
        } else {
            PlanoDeAcao::create($data);
            $msg = 'Plano de ação criado com sucesso!';
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('message', $msg);
        session()->flash('style', 'success');
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
        $this->cod_objetivo = '';
        $this->cod_tipo_execucao = '';
        $this->dte_inicio = null;
        $this->dte_fim = null;
        $this->vlr_orcamento_previsto = 0;
        $this->bln_status = 'Não Iniciado';
        $this->cod_ppa = '';
        $this->cod_loa = '';
    }

    public function render()
    {
        $query = PlanoDeAcao::query()
            ->with(['objetivo', 'tipoExecucao', 'organizacao']);

        if ($this->filtroObjetivo) {
            $query->where('cod_objetivo', $this->filtroObjetivo);
        } elseif ($this->organizacaoId) {
            $query->where('cod_organizacao', $this->organizacaoId);
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
