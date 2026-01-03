<?php

namespace App\Livewire\Organization;

use App\Models\Organization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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
    
    // Filtro para mostrar/esconder hierarquia visual (opcional futuramente)
    // Por enquanto, listagem plana com coluna de "Pai"

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

    // Retorna lista de organizações para o select de "Pai"
    public function getOrganizacoesPaiProperty()
    {
        return Organization::orderBy('nom_organizacao')
            ->get()
            ->map(function ($org) {
                return [
                    'id' => $org->cod_organizacao,
                    'label' => $org->sgl_organizacao . ' - ' . $org->nom_organizacao
                ];
            });
    }

    protected function baseQuery(): Builder
    {
        $query = Organization::query()->with('pai'); // Eager loading
        $search = trim($this->search);

        // Filtro por Organização Selecionada Globalmente
        $orgId = session('organizacao_selecionada_id');
        if ($orgId) {
            // Se houver uma selecionada, mostramos ela e suas filhas (hierarquia descendente)
            $query->where(function($q) use ($orgId) {
                $q->where('cod_organizacao', $orgId)
                  ->orWhere('rel_cod_organizacao', $orgId);
            });
            // Nota: Para hierarquia profunda, precisaríamos de uma query recursiva ou carregar todos e filtrar.
            // Por enquanto, mostramos a selecionada e suas filhas diretas.
        }

        if ($search !== '') {
            $this->applySearchFilter($query, $search);
        }

        return $query;
    }

    protected function paginatedOrganizacoes(): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->orderBy('sgl_organizacao')
            ->paginate(10);
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
        $data = $this->validate()['form'];

        // Se pai não definido, define como null (ou auto-referência se for raiz, depende da regra)
        // Por padrão no form, vazio = null.
        // Se a regra for "Raiz aponta para si mesma", precisamos ajustar. 
        // Lendo o Model, isRaiz() checa se cod == rel_cod. 
        // Vamos assumir null ou valor selecionado. Se for raiz, o usuário deve selecionar a si mesma ou deixamos null e tratamos no backend?
        // Vou manter simples: se null, é raiz (mas ajustando para o padrão do banco se necessário).
        // Update: O model diz `scopeRaiz` whereColumn. 
        // Vamos permitir null no form e se for null, no create, define como o próprio ID (padrão raiz) ou null?
        // O model Organization tem `isRaiz` checking `cod_organizacao === rel_cod_organizacao`.
        // Então ao criar, se rel_cod_organizacao for null, deve ser igual ao ID gerado.
        
        if ($this->editing) {
            $this->authorize('update', $this->editing);
            
            // Evitar ciclo: não pode ser pai de si mesmo diretamente (simplificado)
            if ($data['rel_cod_organizacao'] == $this->editing->cod_organizacao) {
                // É raiz
            }
            
            $this->editing->update($data);
            $message = __('Organização atualizada com sucesso.');
        } else {
            $this->authorize('create', Organization::class);
            $org = Organization::create($data);
            
            // Se não veio pai, define como raiz (pai = ele mesmo)
            if (empty($data['rel_cod_organizacao'])) {
                $org->rel_cod_organizacao = $org->cod_organizacao;
                $org->save();
            }
            
            $message = __('Organização criada com sucesso.');
        }

        $this->notify($message);
        $this->closeFormModal();
        $this->resetPage();
    }

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
        return view('livewire.organizacao.listar-organizacoes', [
            'organizacoes' => $this->paginatedOrganizacoes(),
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
        
        // Fallback
        $query->where(function ($q) use ($search) {
             $q->where('nom_organizacao', 'like', "%{$search}%")
               ->orWhere('sgl_organizacao', 'like', "%{$search}%");
        });
    }
}
