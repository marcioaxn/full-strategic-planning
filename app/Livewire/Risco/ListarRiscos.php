<?php

namespace App\Livewire\Risco;

use App\Models\Risco;
use App\Models\PEI\PEI;
use App\Models\PEI\ObjetivoEstrategico;
use App\Models\Organization;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class ListarRiscos extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    public $search = '';
    public $filtroNivel = '';
    public $filtroCategoria = '';
    public $organizacaoId;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public $riscoId;

    // Campos do Formulário
    public $form = [
        'dsc_titulo' => '',
        'txt_descricao' => '',
        'dsc_categoria' => 'Operacional',
        'num_probabilidade' => 3,
        'num_impacto' => 3,
        'txt_causas' => '',
        'txt_consequencias' => '',
        'cod_responsavel_monitoramento' => '',
        'dsc_status' => 'Identificado',
        'objetivos_vinculados' => [], // IDs dos objetivos
    ];

    // Listas auxiliares
    public $categoriasOptions = ['Estratégico', 'Operacional', 'Financeiro', 'Reputacional', 'Legal/Conformidade'];
    public $statusOptions = ['Identificado', 'Em Monitoramento', 'Mitigado', 'Encerrado'];
    public $objetivos = [];
    public $usuarios = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroNivel' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao'
    ];

    public function mount()
    {
        $this->atualizarOrganizacao(Session::get('organizacao_selecionada_id'));
    }

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;
        $this->resetPage();
        $this->carregarListasAuxiliares();
    }

    public function carregarListasAuxiliares()
    {
        $peiAtivo = PEI::ativos()->first();
        if ($peiAtivo) {
            $this->objetivos = ObjetivoEstrategico::whereHas('perspectiva', function($query) use ($peiAtivo) {
                $query->where('cod_pei', $peiAtivo->cod_pei);
            })->orderBy('nom_objetivo_estrategico')->get();
        }

        if ($this->organizacaoId) {
            $this->usuarios = User::whereHas('organizacoes', function($q) {
                $q->where('tab_organizacoes.cod_organizacao', $this->organizacaoId);
            })->orderBy('name')->get();
        }
    }

    public function updatingSearch() { $this->resetPage(); }

    public function create()
    {
        $this->authorize('create', Risco::class);
        $this->resetForm();
        if (!$this->organizacaoId) {
            $this->dispatch('notify', message: 'Selecione uma organização.', style: 'warning');
            return;
        }
        $this->showModal = true;
    }

    public function edit($id)
    {
        $risco = Risco::with('objetivosEstrategicos')->findOrFail($id);
        $this->authorize('update', $risco);

        $this->riscoId = $id;
        $this->form = [
            'dsc_titulo' => $risco->dsc_titulo,
            'txt_descricao' => $risco->txt_descricao,
            'dsc_categoria' => $risco->dsc_categoria,
            'num_probabilidade' => $risco->num_probabilidade,
            'num_impacto' => $risco->num_impacto,
            'txt_causas' => $risco->txt_causas,
            'txt_consequencias' => $risco->txt_consequencias,
            'cod_responsavel_monitoramento' => $risco->cod_responsavel_monitoramento,
            'dsc_status' => $risco->dsc_status,
            'objetivos_vinculados' => $risco->objetivosEstrategicos->pluck('cod_objetivo_estrategico')->toArray(),
        ];

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'form.dsc_titulo' => 'required|string|max:255',
            'form.dsc_categoria' => 'required',
            'form.num_probabilidade' => 'required|integer|min:1|max:5',
            'form.num_impacto' => 'required|integer|min:1|max:5',
            'form.cod_responsavel_monitoramento' => 'required|exists:users,id',
        ]);

        $peiAtivo = PEI::ativos()->first();
        $data = $this->form;
        unset($data['objetivos_vinculados']);

        if ($this->riscoId) {
            $risco = Risco::findOrFail($this->riscoId);
            $this->authorize('update', $risco);
            $risco->update($data);
        } else {
            $this->authorize('create', Risco::class);
            $data['cod_pei'] = $peiAtivo->cod_pei;
            $data['cod_organizacao'] = $this->organizacaoId;
            $risco = Risco::create($data);
        }

        // Sincronizar objetivos
        $risco->objetivosEstrategicos()->sync($this->form['objetivos_vinculados']);

        $this->showModal = false;
        session()->flash('status', 'Risco salvo com sucesso!');
    }

    public function confirmDelete($id)
    {
        $this->riscoId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $risco = Risco::findOrFail($this->riscoId);
        $this->authorize('delete', $risco);
        $risco->delete();
        $this->showDeleteModal = false;
        session()->flash('status', 'Risco excluído!');
    }

    public function resetForm()
    {
        $this->riscoId = null;
        $this->form = [
            'dsc_titulo' => '', 'txt_descricao' => '', 'dsc_categoria' => 'Operacional',
            'num_probabilidade' => 3, 'num_impacto' => 3, 'txt_causas' => '',
            'txt_consequencias' => '', 'cod_responsavel_monitoramento' => '',
            'dsc_status' => 'Identificado', 'objetivos_vinculados' => [],
        ];
    }

    public function render()
    {
        $query = Risco::query()->with(['responsavel', 'objetivosEstrategicos']);

        if ($this->organizacaoId) {
            $query->where('cod_organizacao', $this->organizacaoId);
        }

        if ($this->search) {
            $query->where('dsc_titulo', 'ilike', '%' . $this->search . '%');
        }

        if ($this->filtroCategoria) {
            $query->where('dsc_categoria', $this->filtroCategoria);
        }

        if ($this->filtroNivel) {
            if ($this->filtroNivel === 'Critico') $query->criticos();
            elseif ($this->filtroNivel === 'Baixo') $query->where('num_nivel_risco', '<', 5);
            // ... outros filtros
        }

        return view('livewire.risco.listar-riscos', [
            'riscos' => $query->orderBy('num_nivel_risco', 'desc')->paginate(10)
        ]);
    }
}