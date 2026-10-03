<?php

namespace App\Livewire\Organization;

use App\Models\Organization;
use App\Models\SystemSetting;
use App\Services\AI\AiServiceFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ListarOrganizacoes extends Component
{
    use AuthorizesRequests;
    use WithPagination;

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
        $this->authorize('viewAny', Organization::class);

        $this->aiEnabled = SystemSetting::getValue('ai_enabled', true);

        // "Editar" no detalhe da organização chega com ?editar={cod} e já abre o
        // modal. O botão de lá não fazia nada.
        $editar = request()->query('editar');
        if (is_string($editar) && ($alvo = Organization::find($editar)) && auth()->user()->can('update', $alvo)) {
            $this->edit($editar);
        }
    }

    public function pedirAjudaIA()
    {
        if (! $this->aiEnabled) {
            return;
        }

        if (empty($this->form['nom_organizacao'])) {
            session()->flash('error', 'Digite o nome da organização primeiro.');

            return;
        }

        try {
            $aiService = AiServiceFactory::make();
            if (! $aiService) {
                return;
            }

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
            Log::error('Erro IA Org: '.$e->getMessage());
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
        'organizacaoSelecionada' => '$refresh',
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
        $arvore = Organization::getTreeForSelector($this->editing?->cod_organizacao);

        // Quem não é Super Admin só pendura a unidade em outra do próprio escopo.
        if (! auth()->user()->isSuperAdmin()) {
            $permitidas = auth()->user()->organizacaoIdsPermitidas()->all();
            $arvore = array_values(array_filter($arvore, fn ($o) => in_array($o['id'], $permitidas, true)));
        }

        return $arvore;
    }

    /** Unidades que o usuário enxerga (Super Admin: null = todas). */
    protected function escopo(): ?array
    {
        $user = auth()->user();

        return $user->isSuperAdmin() ? null : $user->organizacaoIdsPermitidas()->all();
    }

    protected function baseQuery(): Builder
    {
        $query = Organization::query()->with('pai');

        if (($escopo = $this->escopo()) !== null) {
            $query->whereIn('cod_organizacao', $escopo);
        }
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

        // Fora do Super Admin, a árvore começa nas unidades do escopo cuja
        // superior não está no escopo (a "raiz" de quem consulta).
        if (($escopo = $this->escopo()) !== null) {
            $all = $all->only($escopo);
            $roots = $all->filter(fn ($org) => $org->isRaiz() || ! $all->has($org->rel_cod_organizacao))
                ->sortBy('nom_organizacao');
        } else {
            $roots = $all->filter(fn ($org) => $org->isRaiz())->sortBy('nom_organizacao');
        }

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

            // "Nenhuma (unidade raiz)" chega do seletor como texto vazio. Ia
            // assim para a coluna UUID e o cadastro da unidade raiz — o primeiro
            // passo de todo cliente novo — quebrava com erro de SQL na tela.
            // Raiz, na base, é a unidade que aponta para si mesma.
            $semSuperior = ($data['rel_cod_organizacao'] ?? '') === '';
            $data['rel_cod_organizacao'] = $semSuperior ? null : $data['rel_cod_organizacao'];

            if ($this->editing) {
                $this->authorize('update', $this->editing);
                $this->validarNovaSuperior($this->editing, $data['rel_cod_organizacao']);

                if ($semSuperior) {
                    // Só o Super Admin transforma uma unidade em raiz.
                    if (! auth()->user()?->isSuperAdmin()) {
                        throw ValidationException::withMessages([
                            'form.rel_cod_organizacao' => 'Somente o Super Administrador define uma unidade raiz. Escolha a unidade superior.',
                        ]);
                    }
                    $data['rel_cod_organizacao'] = $this->editing->cod_organizacao;
                }

                $this->editing->update($data);
                $this->successMessage = __('Unidade organizacional atualizada com sucesso.');
                $this->createdOrgName = $this->editing->nom_organizacao;
            } else {
                $this->authorize('create', Organization::class);
                $org = Organization::create($data);

                if ($semSuperior) {
                    $org->rel_cod_organizacao = $org->cod_organizacao;
                    $org->save();
                }

                $this->successMessage = __('Nova unidade cadastrada e integrada à hierarquia.');
                $this->createdOrgName = $org->nom_organizacao;
            }

            $this->showFormModal = false;
            $this->showSuccessModal = true;
            $this->resetForm();
        } catch (ValidationException $e) {
            // Erro de preenchimento aparece no próprio campo, não num modal genérico.
            throw $e;
        } catch (\Exception $e) {
            // Sem isto, a causa real desaparece: o cliente recebe uma
            // orientação genérica e não sobra rastro nenhum para investigar.
            report($e);

            // A mensagem técnica (SQL, nome de tabela, host do banco) vai para o
            // log; o usuário recebe o que pode fazer a respeito.
            $this->errorMessage = 'Não foi possível salvar a unidade. Confira os campos e tente de novo; se persistir, informe o suporte.';
            $this->showErrorModal = true;
        }
    }

    /**
     * 🔴 A regra só exigia que a superior existisse. Definir como superior a
     * própria unidade ou uma subordinada a ela criava um ciclo na árvore: a
     * unidade sumia da listagem e o Dashboard entrava em recursão infinita.
     * O seletor da tela já escondia essas opções — mas o valor vem do navegador.
     */
    protected function validarNovaSuperior(Organization $org, ?string $novaSuperior): void
    {
        if (! $novaSuperior || $novaSuperior === $org->rel_cod_organizacao) {
            return;
        }

        if (in_array($novaSuperior, Organization::descendentesEProprio($org->cod_organizacao), true)) {
            throw ValidationException::withMessages([
                'form.rel_cod_organizacao' => 'A unidade superior não pode ser a própria unidade nem uma subordinada a ela.',
            ]);
        }

        if (($escopo = $this->escopo()) !== null && ! in_array($novaSuperior, $escopo, true)) {
            throw ValidationException::withMessages([
                'form.rel_cod_organizacao' => 'Escolha como superior uma unidade que você administra.',
            ]);
        }
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->resetPage();
    }

    public function closeErrorModal()
    {
        $this->showErrorModal = false;
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
        $search = trim($this->search);

        if ($search !== '') {
            $organizacoes = $this->paginatedOrganizacoes();
            $isPaginated = true;
        } else {
            $organizacoes = $this->buildHierarchicalList();
            $isPaginated = false;
        }

        return view('livewire.organizacao.listar-organizacoes', [
            'organizacoes' => $organizacoes,
            'isPaginated' => $isPaginated,
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
            $like = '%'.$search.'%';

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
