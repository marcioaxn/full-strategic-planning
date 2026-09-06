<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ListarPeis extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $search = '';

    public $filtroStatus = '';

    // Modais e Feedback
    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public bool $showSuccessModal = false;

    public bool $showErrorModal = false;

    public string $successMessage = '';

    public string $errorMessage = '';

    public string $createdPeiName = '';

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
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Apenas Super Administradores podem gerenciar PEIs.');
        }
        $this->num_ano_inicio_pei = now()->year;
        $this->num_ano_fim_pei = now()->year + 4;
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->successMessage = '';
        $this->createdPeiName = '';
    }

    public function closeErrorModal()
    {
        $this->showErrorModal = false;
        $this->errorMessage = '';
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

        try {
            $data = [
                'dsc_pei' => $this->dsc_pei,
                'num_ano_inicio_pei' => $this->num_ano_inicio_pei,
                'num_ano_fim_pei' => $this->num_ano_fim_pei,
            ];

            if ($this->peiId) {
                PEI::findOrFail($this->peiId)->update($data);
                $this->successMessage = 'O ciclo de planejamento estratégico foi atualizado com sucesso e todos os vínculos foram preservados.';
            } else {
                PEI::create($data);
                $this->successMessage = 'O novo ciclo de planejamento estratégico foi registrado. Agora você pode prosseguir com a definição da Identidade e Perspectivas.';
            }

            $this->createdPeiName = $this->dsc_pei;
            $this->showModal = false;
            $this->resetForm();
            $this->showSuccessModal = true;

        } catch (\Exception $e) {
            // Sem isto, a causa real desaparece: o cliente recebe uma
            // orientação genérica e não sobra rastro nenhum para investigar.
            report($e);

            $this->errorMessage = 'Ocorreu um erro técnico ao processar o registro do PEI. Por favor, tente novamente.';
            $this->showErrorModal = true;
        }
    }

    public function confirmDelete($id)
    {
        $this->peiId = $id;
        $pei = PEI::withCount('perspectivas')->findOrFail($id);

        $perspIds = Perspectiva::where('cod_pei', $id)->pluck('cod_perspectiva');
        $objCount = Objetivo::whereIn('cod_perspectiva', $perspIds)->count();
        $indCount = Indicador::whereHas('objetivo', function ($q) use ($perspIds) {
            $q->whereIn('cod_perspectiva', $perspIds);
        })->count();
        $planCount = PlanoDeAcao::whereHas('objetivo', function ($q) use ($perspIds) {
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
            $query->where('dsc_pei', 'ilike', '%'.$this->search.'%');
        }

        if ($this->filtroStatus === 'ativo') {
            $query->ativos();
        } elseif ($this->filtroStatus === 'futuro') {
            $query->futuros();
        } elseif ($this->filtroStatus === 'passado') {
            $query->passados();
        }

        return view('livewire.p-e-i.listar-peis', [
            'peis' => $query->orderBy('num_ano_inicio_pei', 'desc')->paginate(10),
        ]);
    }
}
