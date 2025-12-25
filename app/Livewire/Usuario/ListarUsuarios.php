<?php

namespace App\Livewire\Usuario;

use App\Models\User;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class ListarUsuarios extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    public string $search = '';
    
    // Filtros
    public string $filtroAtivo = 'todos'; // todos, ativos, inativos
    
    public array $form = [
        'name' => '',
        'email' => '',
        'password' => '', // Opcional na edição
        'ativo' => true,
        'trocarsenha' => 0,
        'vinculos' => [], // Array de ['org_id' => ..., 'perfil_id' => ...]
    ];

    // Dados auxiliares para o modal
    public $vinculoTemporario = [
        'org_id' => '',
        'perfil_id' => ''
    ];

    public bool $showFormModal = false;
    public bool $showDeleteModal = false;

    public ?User $editing = null;

    public ?string $flashMessage = null;
    public string $flashStyle = 'success';

    protected string $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected function rules(): array
    {
        $rules = [
            'form.name' => ['required', 'string', 'max:255'],
            'form.email' => ['required', 'email', 'max:255', 'unique:users,email' . ($this->editing ? ',' . $this->editing->id : '')],
            'form.ativo' => ['boolean'],
            'form.trocarsenha' => ['integer', 'in:0,1,2'],
            'form.vinculos' => ['array'],
        ];

        if (!$this->editing) {
            $rules['form.password'] = ['required', 'string', 'min:8'];
        } else {
            $rules['form.password'] = ['nullable', 'string', 'min:8'];
        }

        return $rules;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filtroAtivo = 'todos';
        $this->resetPage();
    }

    // Propriedades Computadas para Selects
    public function getOrganizacoesOptionsProperty()
    {
        return Organization::orderBy('sgl_organizacao')->get()->map(function($o) {
            return ['id' => $o->cod_organizacao, 'label' => $o->sgl_organizacao . ' - ' . $o->nom_organizacao];
        });
    }

    public function getPerfisOptionsProperty()
    {
        return PerfilAcesso::orderBy('dsc_perfil')->get()->map(function($p) {
            return ['id' => $p->cod_perfil, 'label' => $p->dsc_perfil];
        });
    }

    protected function baseQuery(): Builder
    {
        $query = User::query()->with(['organizacoes', 'perfisAcesso']);
        $search = trim($this->search);

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($this->filtroAtivo === 'ativos') {
            $query->where('ativo', true);
        } elseif ($this->filtroAtivo === 'inativos') {
            $query->where('ativo', false);
        }

        return $query;
    }

    protected function paginatedUsuarios(): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->orderBy('name')
            ->paginate(10);
    }

    public function create(): void
    {
        $this->authorize('create', User::class);
        $this->resetForm();
        $this->showFormModal = true;
        $this->resetValidation();
    }

    public function edit(string $id): void
    {
        $this->editing = User::findOrFail($id);
        $this->authorize('update', $this->editing);
        
        // Carregar vínculos existentes
        $vinculos = [];
        // Precisamos iterar sobre a pivot table. 
        // Como o relacionamento é BelongsToMany, podemos acessar via pivot
        foreach ($this->editing->perfisAcesso as $perfil) {
            $vinculos[] = [
                'org_id' => $perfil->pivot->cod_organizacao,
                'perfil_id' => $perfil->cod_perfil,
                // Adicionamos labels para exibição na lista
                'org_label' => Organization::find($perfil->pivot->cod_organizacao)?->sgl_organizacao ?? 'N/A',
                'perfil_label' => $perfil->dsc_perfil
            ];
        }

        $this->form = [
            'name' => $this->editing->name,
            'email' => $this->editing->email,
            'password' => '', // Não carrega senha
            'ativo' => (bool)$this->editing->ativo,
            'trocarsenha' => (int)$this->editing->trocarsenha,
            'vinculos' => $vinculos,
        ];
        
        $this->showFormModal = true;
        $this->resetValidation();
    }

    public function adicionarVinculo()
    {
        $this->validate([
            'vinculoTemporario.org_id' => 'required',
            'vinculoTemporario.perfil_id' => 'required',
        ]);

        // Verificar duplicatas
        foreach ($this->form['vinculos'] as $v) {
            if ($v['org_id'] == $this->vinculoTemporario['org_id'] && $v['perfil_id'] == $this->vinculoTemporario['perfil_id']) {
                $this->addError('vinculoTemporario', 'Este vínculo já existe.');
                return;
            }
        }

        // Buscar labels para exibição
        $org = Organization::find($this->vinculoTemporario['org_id']);
        $perfil = PerfilAcesso::find($this->vinculoTemporario['perfil_id']);

        $this->form['vinculos'][] = [
            'org_id' => $this->vinculoTemporario['org_id'],
            'perfil_id' => $this->vinculoTemporario['perfil_id'],
            'org_label' => $org->sgl_organizacao,
            'perfil_label' => $perfil->dsc_perfil
        ];

        // Limpar temporário
        $this->vinculoTemporario = ['org_id' => '', 'perfil_id' => ''];
    }

    public function removerVinculo($index)
    {
        unset($this->form['vinculos'][$index]);
        $this->form['vinculos'] = array_values($this->form['vinculos']); // Reindexar
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate();
        
        DB::transaction(function () {
            $data = [
                'name' => $this->form['name'],
                'email' => $this->form['email'],
                'ativo' => $this->form['ativo'],
                'trocarsenha' => $this->form['trocarsenha'],
            ];

            if (!empty($this->form['password'])) {
                $data['password'] = Hash::make($this->form['password']);
            }

            if ($this->editing) {
                $this->authorize('update', $this->editing);
                $this->editing->update($data);
                $user = $this->editing;
                $message = __('Usuário atualizado com sucesso.');
            } else {
                $this->authorize('create', User::class);
                $user = User::create($data);
                $message = __('Usuário criado com sucesso.');
            }

            // Atualizar Vínculos (Detach All + Attach All)
            // Cuidado: detach() sem argumentos remove tudo da pivot table user_perfil?
            // Sim, $user->perfisAcesso()->detach() remove todos os perfis desse usuário.
            // Mas precisamos garantir que removemos os registros corretos da tabela 'rel_users_tab_organizacoes_tab_perfil_acesso'
            
            // Vamos fazer via DB para garantir
            DB::table('rel_users_tab_organizacoes_tab_perfil_acesso')
                ->where('user_id', $user->id)
                ->delete();

            foreach ($this->form['vinculos'] as $vinculo) {
                DB::table('rel_users_tab_organizacoes_tab_perfil_acesso')->insert([
                    'id' => Str::uuid(),
                    'user_id' => $user->id,
                    'cod_organizacao' => $vinculo['org_id'],
                    'cod_perfil' => $vinculo['perfil_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Também atualizar a tabela simples 'rel_users_tab_organizacoes' para compatibilidade?
            // O User.php tem relacionamento organizacoes() via 'rel_users_tab_organizacoes'.
            // Vamos manter sincronizado.
            $user->organizacoes()->sync(collect($this->form['vinculos'])->pluck('org_id')->unique());

            $this->notify($message);
        });

        $this->closeFormModal();
        $this->resetPage();
    }

    public function confirmDelete(string $id): void
    {
        $this->editing = User::findOrFail($id);
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
            $this->editing->delete(); // Soft delete se configurado, ou delete normal
            // User não tem SoftDeletes por padrão no Laravel, mas vamos checar o model.
            // O model User não tem `use SoftDeletes` no arquivo que li anteriormente.
            // Então é delete permanente.
            
            $this->notify(__('Usuário excluído com sucesso.'), 'warning');
        }

        $this->cancelDelete();
        $this->resetForm();
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.usuario.listar-usuarios', [
            'usuarios' => $this->paginatedUsuarios(),
        ]);
    }

    protected function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'email' => '',
            'password' => '',
            'ativo' => true,
            'trocarsenha' => 0,
            'vinculos' => [],
        ];
        
        $this->vinculoTemporario = ['org_id' => '', 'perfil_id' => ''];
        $this->editing = null;
    }

    protected function notify(string $message, string $style = 'success'): void
    {
        $this->flashMessage = $message;
        $this->flashStyle = $style;
    }
}
