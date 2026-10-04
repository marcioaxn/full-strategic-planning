<?php

namespace App\Livewire\RiskManagement;

use App\Models\Organization;
use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\AI\AiServiceFactory;
use App\Services\NotificationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ListarRiscos extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $search = '';

    public $filtroNivel = '';

    public $filtroCategoria = '';

    #[Locked]
    public $organizacaoId;

    #[Locked]
    public $peiAtivo;

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public bool $showSuccessModal = false;

    public bool $showErrorModal = false;

    public string $successMessage = '';

    public string $errorMessage = '';

    public string $createdRiscoName = '';

    // Só o servidor define (edit/confirmDelete, ambos autorizados).
    #[Locked]
    public $riscoId;

    #[Locked]
    public bool $aiEnabled = false;

    public $aiSuggestion = '';

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
        'objetivos_vinculados' => [],
        'dsc_estrategia_resposta' => '',
        'txt_justificativa_estrategia' => '',
        'dte_proxima_revisao' => '',
    ];

    // Listas auxiliares
    public $categoriasOptions = ['Estratégico', 'Operacional', 'Financeiro', 'Reputacional', 'Legal/Conformidade'];

    public $statusOptions = ['Identificado', 'Em Monitoramento', 'Mitigado', 'Encerrado'];

    public array $estrategiasOptions = [];

    public $objetivos = [];

    public $usuarios = [];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
    ];

    public function mount()
    {
        $this->authorize('modulo.acessar', 'riscos');

        $this->aiEnabled = SystemSetting::getValue('ai_enabled', true);
        $this->estrategiasOptions = Risco::ESTRATEGIAS_RESPOSTA;
        $this->carregarPEI();
        // Organização da sessão só vale se estiver no escopo do usuário.
        $this->atualizarOrganizacao(Auth::user()->organizacaoSelecionadaId());
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->successMessage = '';
        $this->createdRiscoName = '';
    }

    public function closeErrorModal()
    {
        $this->showErrorModal = false;
        $this->errorMessage = '';
    }

    public function pedirAjudaIA()
    {
        $this->authorize('create', [Risco::class, $this->organizacaoId]);

        if (! $this->aiEnabled) {
            return;
        }

        $aiService = AiServiceFactory::make();
        if (! $aiService) {
            return;
        }

        $objetivosList = collect($this->objetivos)->pluck('nom_objetivo')->take(5)->implode(', ');

        $this->aiSuggestion = 'Pensando...';

        $prompt = "Com base nos seguintes Objetivos Estratégicos da organização: '{$objetivosList}', sugira 3 riscos potenciais que podem impedir o alcance dessas metas. 
        Para cada risco informe: Título, Categoria (Estratégico, Operacional, Financeiro, Reputacional), Descrição curta e uma sugestão de Medida de Mitigação.
        Responda OBRIGATORIAMENTE em formato JSON puro, contendo um array de objetos com os campos 'titulo', 'categoria', 'descricao' e 'mitigacao'.";

        $response = $aiService->suggest($prompt);
        $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

        if (is_array($decoded)) {
            $this->aiSuggestion = $decoded;
        } else {
            $this->aiSuggestion = null;
            session()->flash('error', 'Falha ao processar sugestões de risco. Tente novamente.');
        }
    }

    public function aplicarSugestao($titulo, $categoria, $descricao)
    {
        $this->resetForm();
        $this->form['dsc_titulo'] = $titulo;
        $this->form['dsc_categoria'] = $categoria;
        $this->form['txt_descricao'] = $descricao;
        $this->form['cod_responsavel_monitoramento'] = Auth::id();

        $this->showModal = true;
        $this->aiSuggestion = '';
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarListasAuxiliares();
        $this->resetPage();
    }

    private function carregarPEI()
    {
        $peiId = Session::get('pei_selecionado_id');

        if ($peiId) {
            $this->peiAtivo = PEI::find($peiId);
        }

        if (! $this->peiAtivo) {
            $this->peiAtivo = PEI::ativos()->first();
        }
    }

    public function atualizarOrganizacao($id)
    {
        // Método público (e ouvinte de evento): o ID vem do cliente.
        abort_unless(! $id || Auth::user()?->podeAcessarOrganizacao($id), 403);

        // Sem Super Admin, "nenhuma organização" não é "todas".
        if (! $id && ! Auth::user()->isSuperAdmin()) {
            $id = Auth::user()->organizacaoSelecionadaId();
        }

        $this->organizacaoId = $id;
        $this->resetPage();
        $this->carregarListasAuxiliares();
    }

    public function carregarListasAuxiliares()
    {
        if ($this->peiAtivo) {
            $objetivosBrutos = Objetivo::with('perspectiva')
                ->whereHas('perspectiva', function ($query) {
                    $query->where('cod_pei', $this->peiAtivo->cod_pei);
                })
                ->get();

            $this->objetivos = $objetivosBrutos->groupBy(function ($item) {
                return $item->perspectiva->dsc_perspectiva ?? 'Outras Dimensões';
            })->toArray();
        }

        if ($this->organizacaoId) {
            $this->usuarios = User::whereHas('organizacoes', function ($q) {
                $q->where('tab_organizacoes.cod_organizacao', $this->organizacaoId);
            })->orderBy('name')->get();
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->authorize('create', [Risco::class, $this->organizacaoId]);
        $this->resetForm();
        if (! $this->organizacaoId) {
            $this->dispatch('notify', message: 'Selecione uma organização.', style: 'warning');

            return;
        }
        $this->showModal = true;
    }

    public function edit($id)
    {
        $risco = Risco::with('objetivos')->findOrFail($id);
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
            'objetivos_vinculados' => $risco->objetivos->pluck('cod_objetivo')->toArray(),
            'dsc_estrategia_resposta' => $risco->dsc_estrategia_resposta ?? '',
            'txt_justificativa_estrategia' => $risco->txt_justificativa_estrategia ?? '',
            'dte_proxima_revisao' => $risco->dte_proxima_revisao?->format('Y-m-d') ?? '',
        ];

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'form.dsc_titulo' => 'required|string|max:255',
            // Lista fechada no servidor (as listas da tela são propriedades públicas).
            // "Jurídico" e "Monitorado" são valores legados ainda gravados no banco.
            'form.dsc_categoria' => 'required|in:Estratégico,Operacional,Financeiro,Reputacional,Legal/Conformidade,Jurídico',
            'form.dsc_status' => 'required|in:Identificado,Em Monitoramento,Monitorado,Mitigado,Encerrado',
            'form.num_probabilidade' => 'required|integer|min:1|max:5',
            'form.num_impacto' => 'required|integer|min:1|max:5',
            // ATENÇÃO: nunca prefixar o schema aqui. O Laravel lê o ponto na regra
            // exists/unique como NOME DE CONEXÃO, não como schema — e a tela quebra ao
            // salvar com "Database connection [...] not configured". O search_path da
            // conexão pgsql resolve a tabela. Ver documentacao/melhorias/11-*.md
            'form.cod_responsavel_monitoramento' => 'required|exists:users,id',
            'form.dsc_estrategia_resposta' => 'nullable|in:Mitigar,Evitar,Transferir,Aceitar',
            'form.txt_justificativa_estrategia' => 'required_if:form.dsc_estrategia_resposta,Aceitar|nullable|string|max:2000',
            'form.dte_proxima_revisao' => 'nullable|date',
        ], [
            'form.txt_justificativa_estrategia.required_if' => 'Ao aceitar o risco, é obrigatório justificar a decisão.',
        ]);

        // Autoriza ANTES do try: dentro dele a negação virava "erro técnico".
        $riscoExistente = $this->riscoId ? Risco::findOrFail($this->riscoId) : null;
        $riscoExistente
            ? $this->authorize('update', $riscoExistente)
            : $this->authorize('create', [Risco::class, $this->organizacaoId]);

        // Responsável e objetivos vêm do navegador: o responsável precisa ser da
        // unidade do risco e os objetivos, do ciclo do risco.
        $orgDoRisco = $riscoExistente?->cod_organizacao ?? $this->organizacaoId;
        $responsavelValido = User::where('id', $this->form['cod_responsavel_monitoramento'])
            ->where(fn ($q) => $q->whereHas('organizacoes', fn ($o) => $o->where('tab_organizacoes.cod_organizacao', $orgDoRisco))
                ->orWhereHas('perfisAcesso', fn ($p) => $p->where('rel_users_tab_organizacoes_tab_perfil_acesso.cod_organizacao', $orgDoRisco)))
            ->exists();
        if (! $responsavelValido) {
            $this->addError('form.cod_responsavel_monitoramento', 'Escolha um responsável da unidade do risco.');

            return;
        }
        $peiDoRisco = $riscoExistente?->cod_pei ?? $this->peiAtivo?->cod_pei;
        $objetivos = array_values(array_filter((array) $this->form['objetivos_vinculados']));
        if ($objetivos && Objetivo::whereIn('cod_objetivo', $objetivos)->whereHas('perspectiva', fn ($q) => $q->where('cod_pei', $peiDoRisco))->count() !== count(array_unique($objetivos))) {
            $this->addError('form.objetivos_vinculados', 'Há objetivo de outro ciclo na seleção.');

            return;
        }

        try {
            // Só os campos do formulário. 🔴 $this->form inteiro ia para o update():
            // o array público aceita chave nova vinda do navegador, e o fillable tem
            // cod_organizacao/cod_pei — o risco era transferido para outra unidade.
            $data = Arr::only($this->form, [
                'dsc_titulo', 'txt_descricao', 'dsc_categoria', 'num_probabilidade', 'num_impacto',
                'txt_causas', 'txt_consequencias', 'cod_responsavel_monitoramento', 'dsc_status',
                'dsc_estrategia_resposta', 'txt_justificativa_estrategia', 'dte_proxima_revisao',
            ]);

            // Campo de data não preenchido chega do formulário como string
            // vazia, e o PostgreSQL recusa "" em coluna date
            // ("invalid input syntax for type date"). O risco simplesmente não
            // salvava quando a próxima revisão ficava em branco — que é o caso
            // comum, já que o campo é opcional.
            foreach (['dte_proxima_revisao', 'dsc_estrategia_resposta', 'cod_responsavel_monitoramento'] as $campo) {
                if (($data[$campo] ?? null) === '') {
                    $data[$campo] = null;
                }
            }

            if ($riscoExistente) {
                $risco = $riscoExistente;
                $risco->update($data);
                $this->successMessage = 'As definições do risco foram atualizadas com sucesso e a matriz já reflete a nova avaliação.';
            } else {
                if (! $this->peiAtivo || ! $this->organizacaoId) {
                    $this->errorMessage = 'Selecione um Ciclo PEI e uma organização antes de registrar um risco.';
                    $this->showErrorModal = true;

                    return;
                }
                $data['cod_pei'] = $this->peiAtivo->cod_pei;
                $data['cod_organizacao'] = $this->organizacaoId;
                $risco = Risco::create($data);
                $this->successMessage = 'O novo risco foi identificado e registrado. Agora você pode prosseguir com os planos de mitigação.';
            }

            // Sincronizar objetivos
            $risco->objetivos()->sync($this->form['objetivos_vinculados']);

            $this->createdRiscoName = $this->form['dsc_titulo'];
            $this->showModal = false;
            $this->showSuccessModal = true;

        } catch (\Exception $e) {
            Log::error('Erro ao salvar risco', [
                'mensagem' => $e->getMessage(),
                'arquivo' => $e->getFile().':'.$e->getLine(),
            ]);
            $this->errorMessage = 'Não foi possível processar o registro do risco. Por favor, revise as informações e tente novamente.';
            $this->showErrorModal = true;
        }
    }

    public function confirmDelete($id)
    {
        $this->authorize('delete', Risco::findOrFail($id));
        $this->riscoId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $risco = Risco::findOrFail($this->riscoId);
        $this->authorize('delete', $risco);
        $risco->delete();
        $this->showDeleteModal = false;

        $alert = NotificationService::sendMentorAlert(
            'Risco Removido',
            'O item foi excluído do mapeamento de riscos.',
            'bi-trash',
            'warning'
        );

        $this->dispatch('mentor-notification', ...$alert);
    }

    public function resetForm()
    {
        $this->riscoId = null;
        $this->form = [
            'dsc_titulo' => '',
            'txt_descricao' => '',
            'dsc_categoria' => 'Operacional',
            'num_probabilidade' => 3,
            'num_impacto' => 3,
            'txt_causas' => '',
            'txt_consequencias' => '',
            'cod_responsavel_monitoramento' => '',
            'dsc_status' => 'Identificado',
            'objetivos_vinculados' => [],
            'dsc_estrategia_resposta' => '',
            'txt_justificativa_estrategia' => '',
            'dte_proxima_revisao' => '',
        ];
    }

    public function render()
    {
        $query = Risco::query()->with(['responsavel', 'objetivos']);

        // A unidade selecionada e as subordinadas, dentro do que o usuário
        // alcança. 🔴 Sem organização, a lista trazia os riscos (causas,
        // responsáveis) de TODAS as unidades a qualquer usuário.
        $usuario = Auth::user();
        if ($this->organizacaoId) {
            $orgIds = Organization::descendentesEProprio($this->organizacaoId);
            if (! $usuario->isSuperAdmin()) {
                $orgIds = array_values(array_intersect($orgIds, $usuario->organizacaoIdsPermitidas()->all()));
            }
            $query->whereIn('cod_organizacao', $orgIds);
        } else {
            $usuario->aplicarEscopoOrganizacional($query);
        }

        // Só o ciclo selecionado no topo: a lista misturava riscos de todos os ciclos.
        if ($this->peiAtivo) {
            $query->where('cod_pei', $this->peiAtivo->cod_pei);
        }

        if ($this->search) {
            $query->where('dsc_titulo', 'ilike', '%'.$this->search.'%');
        }

        if ($this->filtroCategoria) {
            $query->where('dsc_categoria', $this->filtroCategoria);
        }

        if ($this->filtroNivel) {
            if ($this->filtroNivel === 'Critico') {
                $query->criticos();
            } elseif ($this->filtroNivel === 'Baixo') {
                $query->where('num_nivel_risco', '<', 5);
            }
            // ... outros filtros
        }

        return view('livewire.risco.listar-riscos', [
            'riscos' => $query->orderBy('num_nivel_risco', 'desc')->paginate(10),
        ]);
    }
}
