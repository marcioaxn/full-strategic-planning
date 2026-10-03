<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Services\StrategicPlanning\CopiarPeiService;
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

    // "Salvar como"
    public bool $showSalvarComoModal = false;

    public ?string $copiaOrigemId = null;

    public string $copiaOrigemNome = '';

    public $copia_dsc_pei = '';

    public $copia_num_ano_inicio_pei;

    public $copia_num_ano_fim_pei;

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroStatus' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->garantirSuperAdmin();
        $this->num_ano_inicio_pei = now()->year;
        $this->num_ano_fim_pei = now()->year + 4;

        // "Editar PEI" no detalhe do ciclo chega aqui com ?editar={cod_pei}
        // e já abre o modal de edição daquele ciclo.
        $editar = request()->query('editar');
        if (is_string($editar) && PEI::whereKey($editar)->exists()) {
            $this->edit($editar);
        }
    }

    /**
     * Cada método público é um endpoint: a checagem do mount() não basta.
     */
    private function garantirSuperAdmin(): void
    {
        if (! auth()->user()?->isSuperAdmin()) {
            abort(403, 'Apenas Super Administradores podem gerenciar PEIs.');
        }
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
        $this->garantirSuperAdmin();

        $pei = PEI::findOrFail($id);
        $this->peiId = $id;
        $this->dsc_pei = $pei->dsc_pei;
        $this->num_ano_inicio_pei = $pei->num_ano_inicio_pei;
        $this->num_ano_fim_pei = $pei->num_ano_fim_pei;
        $this->showModal = true;
    }

    public function save()
    {
        $this->garantirSuperAdmin();

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

    public function abrirSalvarComo($id)
    {
        $this->garantirSuperAdmin();

        $pei = PEI::findOrFail($id);
        $this->resetValidation();
        $this->copiaOrigemId = $pei->cod_pei;
        $this->copiaOrigemNome = $pei->dsc_pei;
        $this->copia_dsc_pei = '';
        $this->copia_num_ano_inicio_pei = $pei->num_ano_inicio_pei;
        $this->copia_num_ano_fim_pei = $pei->num_ano_fim_pei;
        $this->showSalvarComoModal = true;
    }

    /**
     * Cria um PEI novo com tudo o que está preso ao de origem. O período pode
     * ser o mesmo; a descrição, não — é ela que distingue os dois ciclos nas
     * listas e nos relatórios.
     */
    public function salvarComo(CopiarPeiService $copiador)
    {
        $this->garantirSuperAdmin();

        $this->copia_dsc_pei = trim((string) $this->copia_dsc_pei);
        $this->validate([
            'copia_dsc_pei' => [
                'required', 'string', 'max:255',
                function (string $atributo, $valor, \Closure $falhar) {
                    $existe = PEI::whereRaw('lower(trim(dsc_pei)) = ?', [mb_strtolower($valor, 'UTF-8')])->exists();
                    if ($existe) {
                        $falhar('Já existe um PEI com esta descrição. Use uma descrição diferente.');
                    }
                },
            ],
            'copia_num_ano_inicio_pei' => 'required|integer|min:2000|max:2100',
            'copia_num_ano_fim_pei' => 'required|integer|min:2000|max:2100|gte:copia_num_ano_inicio_pei',
        ], [
            'copia_dsc_pei.required' => 'Informe a descrição do novo PEI.',
            'copia_num_ano_fim_pei.gte' => 'O ano de término deve ser maior ou igual ao ano de início.',
        ]);

        $origem = PEI::findOrFail($this->copiaOrigemId);

        try {
            $copiador->copiar($origem, $this->copia_dsc_pei, (int) $this->copia_num_ano_inicio_pei, (int) $this->copia_num_ano_fim_pei);
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Não foi possível copiar o PEI. Nada foi gravado: o ciclo de origem continua como estava. Tente novamente; se persistir, acione o suporte.';
            $this->showErrorModal = true;

            return;
        }

        $this->createdPeiName = $this->copia_dsc_pei;
        $this->successMessage = "Cópia de \"{$origem->dsc_pei}\" criada com identidade, perspectivas, objetivos, iniciativas, entregas, indicadores, riscos e demais registros do ciclo. Selecione o novo PEI no topo da tela para trabalhar nele.";
        $this->showSalvarComoModal = false;
        $this->copiaOrigemId = null;
        $this->copia_dsc_pei = '';
        $this->showSuccessModal = true;
    }

    public function confirmDelete($id)
    {
        $this->garantirSuperAdmin();

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
        $this->garantirSuperAdmin();

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
