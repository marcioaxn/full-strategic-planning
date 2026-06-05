<?php

namespace App\Livewire\UserManagement;

use App\Models\User;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Notifications\WelcomeSetPasswordNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;
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
        'password_confirmation' => '',
        'password' => '', // Opcional na edição
        'ativo' => true,
        'trocarsenha' => 0,
        'vinculos' => [], // Array de ['org_id' => ..., 'perfil_id' => ...]
    ];

    // Modo de criacao do acesso inicial no cadastro administrativo.
    public string $modoSenhaInicial = 'enviar_link';

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
    public bool $showTransactionModal = false;
    public string $transactionTitle = '';
    public string $transactionMessage = '';
    public string $transactionStyle = 'success';

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
        $rules = [
            'form.name' => ['required', 'string', 'max:255'],
            'form.email' => ['required', 'email', 'max:255', 'unique:users,email' . ($this->editing ? ',' . $this->editing->id : '')],
            'form.ativo' => ['boolean'],
            'form.trocarsenha' => ['integer', 'in:0,1,2'],
            'form.vinculos' => ['array'],
            'modoSenhaInicial' => ['required', 'in:enviar_link,senha_manual'],
        ];

        if (!$this->editing) {
            // No modo por link, a senha nunca e informada durante o cadastro.
            if ($this->modoSenhaInicial === 'senha_manual') {
                $rules['form.password'] = array_merge(['required'], $this->strongPasswordRules());
                $rules['form.password_confirmation'] = ['required', 'string'];
            }
        } else {
            $passwordInformada = trim((string) ($this->form['password'] ?? '')) !== ''
                || trim((string) ($this->form['password_confirmation'] ?? '')) !== '';

            if ($passwordInformada) {
                $rules['form.password'] = array_merge(['required'], $this->strongPasswordRules());
                $rules['form.password_confirmation'] = ['required', 'string'];
            }
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'form.name.required' => 'Informe o nome completo do usuario.',
            'form.name.max' => 'O nome deve ter no maximo 255 caracteres.',
            'form.email.required' => 'Informe o e-mail institucional do usuario.',
            'form.email.email' => 'Informe um e-mail valido para o usuario.',
            'form.email.unique' => 'Ja existe um usuario cadastrado com este e-mail.',
            'form.password.required' => 'Defina a senha inicial do usuario.',
            'form.password.confirmed' => 'A confirmacao da senha nao confere.',
            'form.password.min' => 'A senha deve ter no minimo 8 caracteres.',
            'form.password.regex' => 'A senha deve conter letra maiuscula, letra minuscula, numero e caractere especial.',
            'form.password_confirmation.required' => 'Confirme a senha inicial do usuario.',
            'form.trocarsenha.in' => 'Selecione uma opcao valida para a troca de senha.',
            'modoSenhaInicial.in' => 'Selecione uma forma valida de definicao da senha inicial.',
            'vinculoTemporario.org_id.required' => 'Selecione a organizacao do vinculo.',
            'vinculoTemporario.perfil_id.required' => 'Selecione o perfil de acesso do vinculo.',
        ];
    }

    public function updated(string $propertyName): void
    {
        if ($propertyName === 'modoSenhaInicial' && $this->modoSenhaInicial === 'enviar_link') {
            $this->form['password'] = '';
            $this->form['password_confirmation'] = '';
            $this->resetValidation(['form.password', 'form.password_confirmation']);
        }

        if (! array_key_exists($propertyName, $this->rules())) {
            return;
        }

        $this->validateOnly($propertyName);
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

        // Filtro por Organização Selecionada Globalmente
        $orgId = session('organizacao_selecionada_id');
        if ($orgId) {
            $query->whereHas('organizacoes', function ($q) use ($orgId) {
                $q->where('tab_organizacoes.cod_organizacao', $orgId);
            });
        }

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
            'password_confirmation' => '',
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
        try {
            $this->validate();

            $isNovoUsuario = !$this->editing;

            DB::transaction(function () use ($isNovoUsuario) {
            $data = [
                'name' => $this->form['name'],
                'email' => $this->form['email'],
                'ativo' => $this->form['ativo'],
                'trocarsenha' => $this->form['trocarsenha'],
            ];

            // Preparar acesso inicial conforme o ritual escolhido.
            if ($isNovoUsuario && $this->modoSenhaInicial === 'enviar_link') {
                $data['password'] = Hash::make(Str::random(80));
                $data['trocarsenha'] = 1;
            } elseif (!empty($this->form['password'])) {
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

                if ($this->modoSenhaInicial === 'enviar_link') {
                    $token = Password::broker()->createToken($user);
                    Notification::sendNow($user, new WelcomeSetPasswordNotification($token));
                    $message .= ' Boas-vindas com link para cadastro de senha enviadas por e-mail.';
                } else {
                    $message .= ' Senha inicial definida pelo gestor.';
                }
            }

            // Atualizar Vínculos (Detach All + Attach All)
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

            // Manter sincronizada a tabela simples para compatibilidade
            $user->organizacoes()->sync(collect($this->form['vinculos'])->pluck('org_id')->unique());

            $this->notify($message);
        });

            $this->closeFormModal();
            $this->resetPage();
        } catch (ValidationException $exception) {
            $this->notify(
                'O cadastro nao foi concluido. Revise os campos destacados e tente novamente.',
                'danger',
                'Cadastro nao concluido'
            );

            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            $this->notify(
                'Nao foi possivel concluir o cadastro. Nenhuma confirmacao de sucesso foi emitida. Verifique a configuracao de e-mail, os dados informados e tente novamente.',
                'danger',
                'Falha na transacao'
            );
        }
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
            'password_confirmation' => '',
            'ativo' => true,
            'trocarsenha' => 0,
            'vinculos' => [],
        ];

        $this->vinculoTemporario = ['org_id' => '', 'perfil_id' => ''];
        $this->modoSenhaInicial = 'enviar_link';
        $this->editing = null;
    }

    protected function notify(string $message, string $style = 'success', ?string $title = null): void
    {
        $this->flashMessage = $message;
        $this->flashStyle = $style;
        $this->transactionMessage = $message;
        $this->transactionStyle = $style;
        $this->transactionTitle = $title ?? ($style === 'success' ? 'Transacao concluida' : 'Aviso da transacao');
        $this->showTransactionModal = true;
    }

    protected function strongPasswordRules(): array
    {
        return [
            'string',
            'confirmed',
            'min:8',
            'regex:/[a-z]/',
            'regex:/[A-Z]/',
            'regex:/[0-9]/',
            'regex:/[^A-Za-z0-9]/',
        ];
    }
}
