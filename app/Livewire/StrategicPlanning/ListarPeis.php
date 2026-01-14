<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\PEI;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class ListarPeis extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    public $search = '';
    public $filtroStatus = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public $peiId;
    public $impactoExclusao = [];

    // Campos do Formulário
    public $dsc_pei = '';
    public $num_ano_inicio_pei;
    public $num_ano_fim_pei;

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroStatus' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Apenas Super Administradores podem gerenciar PEIs.');
        }
        $this->num_ano_inicio_pei = now()->year;
        $this->num_ano_fim_pei = now()->year + 4;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filtroStatus = '';
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $pei = PEI::findOrFail($id);
        $this->peiId = $id;
        $this->dsc_pei = $pei->dsc_pei;
        $this->num_ano_inicio_pei = $pei->num_ano_inicio_pei;
        $this->num_ano_fim_pei = $pei->num_ano_fim_pei;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'dsc_pei' => 'required|string|max:255',
            'num_ano_inicio_pei' => 'required|integer|min:2000|max:2100',
            'num_ano_fim_pei' => 'required|integer|min:2000|max:2100|gte:num_ano_inicio_pei',
        ], [
            'num_ano_fim_pei.gte' => 'O ano de término deve ser maior ou igual ao ano de início.',
        ]);

        $data = [
            'dsc_pei' => $this->dsc_pei,
            'num_ano_inicio_pei' => $this->num_ano_inicio_pei,
            'num_ano_fim_pei' => $this->num_ano_fim_pei,
        ];

        if ($this->peiId) {
            PEI::findOrFail($this->peiId)->update($data);
            $message = 'PEI atualizado com sucesso!';
        } else {
            PEI::create($data);
            $message = 'PEI criado com sucesso!';
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('status', $message);
    }

    public function confirmDelete($id)
    {
        $this->peiId = $id;
        $pei = PEI::withCount('perspectivas')->findOrFail($id);
        
        $perspIds = \App\Models\StrategicPlanning\Perspectiva::where('cod_pei', $id)->pluck('cod_perspectiva');
        $objCount = \App\Models\StrategicPlanning\Objetivo::whereIn('cod_perspectiva', $perspIds)->count();
        $indCount = \App\Models\PerformanceIndicators\Indicador::whereHas('objetivo', function($q) use ($perspIds) {
            $q->whereIn('cod_perspectiva', $perspIds);
        })->count();
        $planCount = \App\Models\ActionPlan\PlanoDeAcao::whereHas('objetivo', function($q) use ($perspIds) {
            $q->whereIn('cod_perspectiva', $perspIds);
        })->count();

        $this->impactoExclusao = [
            'perspectivas' => $pei->perspectivas_count,
            'objetivos' => $objCount,
            'indicadores' => $indCount,
            'planos' => $planCount,
        ];

        $this->showDeleteModal = true;
    }

    public function delete()
    {
        PEI::findOrFail($this->peiId)->delete();
        $this->showDeleteModal = false;
        $this->peiId = null;
        session()->flash('status', 'PEI excluído com sucesso!');
    }

    public function resetForm()
    {
        $this->peiId = null;
        $this->dsc_pei = '';
        $this->num_ano_inicio_pei = now()->year;
        $this->num_ano_fim_pei = now()->year + 4;
    }

    public function render()
    {
        $query = PEI::query()->withCount('perspectivas');

        if ($this->search) {
            $query->where('dsc_pei', 'ilike', '%' . $this->search . '%');
        }

        if ($this->filtroStatus === 'ativo') {
            $query->ativos();
        } elseif ($this->filtroStatus === 'futuro') {
            $query->futuros();
        } elseif ($this->filtroStatus === 'passado') {
            $query->passados();
        }

        return view('livewire.p-e-i.listar-peis', [
            'peis' => $query->orderBy('num_ano_inicio_pei', 'desc')->paginate(10)
        ]);
    }
}
