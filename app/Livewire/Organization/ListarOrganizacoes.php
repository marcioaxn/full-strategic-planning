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

    // ... (pedirAjudaIA e aplicarSugestaoSigla mantidos)

    public function getOrganizacoesPaiProperty()
    {
        return Organization::getTreeForSelector($this->editing?->cod_organizacao);
    }

    protected function baseQuery(): Builder
    {
        $query = Organization::query()->with('pai');
        $search = trim($this->search);

        // Se estiver pesquisando, usamos a busca plana normal
        if ($search !== '') {
            $this->applySearchFilter($query, $search);
            return $query->orderBy('nom_organizacao');
        }

        // Se não estiver pesquisando, mostramos a árvore ordenada
        // Nota: Para paginação com árvore é complexo, então usaremos ordenação por hierarquia simulada
        return $query->orderByRaw("(CASE WHEN cod_organizacao = rel_cod_organizacao THEN '0' ELSE '1' END), nom_organizacao");
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

    // ... (restante dos métodos)

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
