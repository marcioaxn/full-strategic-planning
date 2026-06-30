<?php

namespace App\Livewire\Organization;

use App\Models\Organization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class ListarOrganizacoes extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    public string $search = '';
    
    public array $form = [
        'sgl_organizacao' => '',
        'nom_organizacao' => '',
        'rel_cod_organizacao' => '',
    ];

    public bool $showFormModal = false;
    public bool $showDeleteModal = false;

    public ?Organization $editing = null;

    public ?string $flashMessage = null;
    public string $flashStyle = 'success';

    public bool $aiEnabled = false;
    public $aiSuggestion = '';

    // Propriedades de feedback premium
    public bool $showSuccessModal = false;
    public bool $showErrorModal = false;
    public string $successMessage = '';
    public string $errorMessage = '';
    public string $createdOrgName = '';

    public function mount()
    {
        $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', true);
    }

    public function pedirAjudaIA()
    {
        if (!$this->aiEnabled) return;
        
        if (empty($this->form['nom_organizacao'])) {
             session()->flash('error', 'Digite o nome da organização primeiro.');
             return;
        }

        try {
            $aiService = \App\Services\AI\AiServiceFactory::make();
            if (!$aiService) return;

            $this->aiSuggestion = 'Pensando...';
            
            $prompt = "Sugira uma sigla curta e impactante e 3 possíveis subunidades (filiais ou departamentos) para a organização: '{$this->form['nom_organizacao']}'.
            Responda OBRIGATORIAMENTE em formato JSON puro, contendo os campos 'sigla' (string) e 'subunidades' (array de strings).";
            
            $response = $aiService->suggest($prompt);
            $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

            if (is_array($decoded)) {
                $this->aiSuggestion = $decoded;
            } else {
                throw new \Exception('Falha ao decodificar');
            }
        } catch (\Exception $e) {
            Log::error('Erro IA Org: ' . $e->getMessage());
            $this->aiSuggestion = null;
            session()->flash('error', 'Não foi possível gerar sugestões.');
        }
    }

    public function aplicarSugestaoSigla($sigla)
    {
        $this->form['sgl_organizacao'] = $sigla;
        $this->aiSuggestion['sigla_aplicada'] = true;
    }

    protected string $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'organizacaoSelecionada' => '$refresh'
    ];

    protected function rules(): array
    {
        return [
            'form.sgl_organizacao' => ['required', 'string', 'max:20'],
            'form.nom_organizacao' => ['required', 'string', 'max:255'],
            'form.rel_cod_organizacao' => ['nullable', 'exists:tab_organizacoes,cod_organizacao'],
        ];
    }

    public function updatingSearch(string $value): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function getOrganizacoesPaiProperty()
    {
        return Organization::getTreeForSelector($this->editing?->cod_organizacao);
    }

    protected function baseQuery(): Builder
    {
        $query = Organization::query()->with('pai');
        $search = trim($this->search);

        if ($search !== '') {
            $this->applySearchFilter($query, $search);
            return $query->orderBy('nom_organizacao');
        }

        return $query->orderBy('nom_organizacao');
    }

    protected function paginatedOrganizacoes(): LengthAwarePaginator
    {
        return $this->baseQuery()->paginate(50);
    }

    /**
     * Retorna todas as organizações em ordem hierárquica (DFS: raiz → filhos → netos).
     * Pré-computa o nível em cada model para evitar N+1 no template.
     */
    protected function buildHierarchicalList(): Collection
    {
        $all = Organization::with('pai')->get()->keyBy('cod_organizacao');

        $roots = $all->filter(fn ($org) => $org->isRaiz())->sortBy('nom_organizacao');

        $result = collect();
        foreach ($roots as $root) {
            $this->appendHierarchically($result, $root, $all, 0);
        }

        return $result;
    }

    protected function appendHierarchically(Collection $list, Organization $org, Collection $all, int $level): void
    {
        $org->nivel_hierarquico_calculado = $level;
        $list->push($org);

        $children = $all
            ->filter(fn ($o) => $o->rel_cod_organizacao === $org->cod_organizacao && $o->cod_organizacao !== $org->cod_organizacao)
            ->sortBy('nom_organizacao');

        foreach ($children as $child) {
            $this->appendHierarchically($list, $child, $all, $level + 1);
        }
    }

    public function create(): void
    {
        $this->authorize('create', Organization::class);
        $this->resetForm();
        $this->showFormModal = true;
        $this->resetValidation();
    }

    public function edit(string $id): void
    {
        $this->editing = Organization::findOrFail($id);
        $this->authorize('update', $this->editing);
        
        $this->form = [
            'sgl_organizacao' => $this->editing->sgl_organizacao,
            'nom_organizacao' => $this->editing->nom_organizacao,
            'rel_cod_organizacao' => $this->editing->rel_cod_organizacao,
        ];
        
        $this->showFormModal = true;
        $this->resetValidation();
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function save(): void
    {
        try {
            $data = $this->validate()['form'];

            if ($this->editing) {
                $this->authorize('update', $this->editing);
                $this->editing->update($data);
                $this->successMessage = __('Unidade organizacional atualizada com sucesso.');
                $this->createdOrgName = $this->editing->nom_organizacao;
            } else {
                $this->authorize('create', Organization::class);
                $org = Organization::create($data);
                
                if (empty($data['rel_cod_organizacao'])) {
                    $org->rel_cod_organizacao = $org->cod_organizacao;
                    $org->save();
                }
                
                $this->successMessage = __('Nova unidade cadastrada e integrada à hierarquia.');
                $this->createdOrgName = $org->nom_organizacao;
            }

            $this->showFormModal = false;
            $this->showSuccessModal = true;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->showErrorModal = true;
        }
    }

    public function closeSuccessModal() { $this->showSuccessModal = false; $this->resetPage(); }
    public function closeErrorModal() { $this->showErrorModal = false; }

    public function confirmDelete(string $id): void
    {
        $this->editing = Organization::findOrFail($id);
        $this->authorize('delete', $this->editing);
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->editing = null;
    }

    public function delete(): void
    {
        if ($this->editing) {
            $this->authorize('delete', $this->editing);
            $this->editing->delete();
            $this->notify(__('Organização excluída com sucesso.'), 'warning');
        }

        $this->cancelDelete();
        $this->resetForm();
        $this->resetPage();
    }

    public function render()
    {
        $search = trim($this->search);

        if ($search !== '') {
            $organizacoes = $this->paginatedOrganizacoes();
            $isPaginated  = true;
        } else {
            $organizacoes = $this->buildHierarchicalList();
            $isPaginated  = false;
        }

        return view('livewire.organizacao.listar-organizacoes', [
            'organizacoes' => $organizacoes,
            'isPaginated'  => $isPaginated,
        ]);
    }

    protected function resetForm(): void
    {
        $this->form = [
            'sgl_organizacao' => '',
            'nom_organizacao' => '',
            'rel_cod_organizacao' => '',
        ];

        $this->editing = null;
        $this->aiSuggestion = '';
    }

    protected function notify(string $message, string $style = 'success'): void
    {
        $this->flashMessage = $message;
        $this->flashStyle = $style;
    }

    protected function applySearchFilter(Builder $query, string $search): void
    {
        $search = trim($search);

        if ($search === '') {
            return;
        }

        $columns = ['nom_organizacao', 'sgl_organizacao'];
        $driver = $query->getModel()->getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $like = '%' . $search . '%';

            $query->where(function (Builder $subQuery) use ($columns, $like) {
                foreach ($columns as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $subQuery->{$method}($column, 'ilike', $like);
                }
            });

            return;
        }
        
        $query->where(function ($q) use ($search) {
             $q->where('nom_organizacao', 'like', "%{$search}%")
               ->orWhere('sgl_organizacao', 'like', "%{$search}%");
        });
    }
}