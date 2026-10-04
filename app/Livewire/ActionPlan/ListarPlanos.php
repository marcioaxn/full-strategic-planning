<?php

namespace App\Livewire\ActionPlan;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Organization;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\SystemSetting;
use App\Services\AI\AiServiceFactory;
use App\Services\PeiGuidanceService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

// Sem #[Layout] fixo: o layout é escolhido no render(), porque esta tela
// também é servida ao visitante pelo Mapa Estratégico público.
class ListarPlanos extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $search = '';

    public $filtroStatus = '';

    public $filtroTipo = '';

    public $filtroAno = '';

    public $filtroObjetivo = '';

    // Só o servidor define (atualizarOrganizacao confere o escopo): sem a
    // trava, um $set do navegador listava iniciativas de qualquer unidade.
    #[Locked]
    public $organizacaoId;

    public $organizacaoNome;

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    /** O que vai junto com a iniciativa (PlanoDeAcao::booted), para o modal dizer a verdade. */
    #[Locked]
    public array $impactoExclusao = [];

    // Só o servidor define (edit/confirmDelete, ambos autorizados).
    #[Locked]
    public $planoId;

    // Campos do Formulário
    public $dsc_plano_de_acao;

    public $txt_detalhamento;

    public $cod_objetivo;

    public $cod_tipo_execucao;

    public $dte_inicio;

    public $dte_fim;

    public $vlr_orcamento_previsto = 0;

    public $bln_status = 'Não Iniciado';

    public $cod_ppa;

    public $cod_loa;

    public array $modelo_logico = ['insumos' => '', 'atividades' => '', 'resultados' => '', 'impacto' => '', 'pressupostos' => ''];

    public $organizacoes_ids = []; // Suporte a multivinculação

    public $organizacoesOptions = []; // Lista em árvore

    #[Locked]
    public bool $aiEnabled = false;

    public $aiSuggestion = '';

    // Success Modal Properties
    public bool $showSuccessModal = false;

    public $createdPlanName = '';

    public $createdPlanType = '';

    // Listas auxiliares
    public $objetivos = [];

    public $tiposExecucao = [];

    /** Lista fixa do servidor: a validação não lê a propriedade pública (o navegador poderia trocá-la). */
    public const STATUS_OPCOES = ['Não Iniciado', 'Em Andamento', 'Concluído', 'Atrasado', 'Suspenso', 'Cancelado'];

    public $statusOptions = self::STATUS_OPCOES;

    public $grausSatisfacao = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroStatus' => ['except' => ''],
        'filtroTipo' => ['except' => ''],
        'filtroAno' => ['except' => ''],
        'filtroObjetivo' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public $peiAtivo;

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
        'anoSelecionado' => 'atualizarAno',
    ];

    public $objetivoContexto = null; // Propriedade para armazenar o objetivo carregado

    public function mount()
    {
        $this->aiEnabled = SystemSetting::getValue('ai_enabled', true);

        // Sanitizar filtroObjetivo vindo da URL
        if ($this->filtroObjetivo && ! preg_match('/^[0-9a-fA-F-]{36}$/', $this->filtroObjetivo)) {
            $this->filtroObjetivo = '';
        }

        // Se houver filtroObjetivo válido, carregar o contexto
        if ($this->filtroObjetivo) {
            $this->objetivoContexto = Objetivo::with(['perspectiva.pei', 'indicadores.evolucoes', 'indicadores.metasPorAno', 'planosAcao.entregas'])
                ->find($this->filtroObjetivo);

            // Se não encontrou, limpa o filtro para evitar problemas
            if (! $this->objetivoContexto) {
                $this->filtroObjetivo = '';
            }
        }

        $this->carregarPEI();
        // Logado: a organização vem validada contra o escopo (nunca a sessão
        // crua, que podia apontar para a raiz e dar 403 a quem não é dela).
        // Visitante da Transparência: mantém a seleção pública da sessão.
        $this->atualizarOrganizacao(Auth::check()
            ? Auth::user()->organizacaoSelecionadaId()
            : Session::get('organizacao_selecionada_id'));
        $this->tiposExecucao = TipoExecucao::where('cod_tipo_execucao', '!=', 'ecef6a50-c010-4cda-afc3-cbda245b55b0')
            ->orderBy('dsc_tipo_execucao')
            ->get();
        // O ano que veio no endereço (link compartilhado, voltar do navegador)
        // vale; só sem ele cai no ano de referência do topo.
        if ($this->filtroAno === '' || $this->filtroAno === null) {
            $this->filtroAno = Session::get('ano_selecionado', now()->year);
        }
        $this->organizacoesOptions = Organization::getTreeForSelector();
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->createdPlanName = '';
        $this->createdPlanType = '';
    }

    public function pedirAjudaIA()
    {
        // Tela também pública: visitante não aciona a IA (serviço pago).
        abort_unless(Auth::check(), 403);

        if (! $this->aiEnabled) {
            return;
        }

        if (! $this->cod_objetivo) {
            session()->flash('error', 'Selecione um objetivo no formulário primeiro.');

            return;
        }

        try {
            $aiService = AiServiceFactory::make();
            if (! $aiService) {
                return;
            }

            $objetivo = Objetivo::find($this->cod_objetivo);
            if (! $objetivo) {
                session()->flash('error', 'Objetivo não encontrado.');

                return;
            }

            $this->aiSuggestion = 'Pensando...';

            $prompt = "Sugira 3 iniciativas (iniciativas) para alcançar o objetivo estratégico: '{$objetivo->nom_objetivo}'.
            Leve em conta que a organização é: {$this->organizacaoNome}.
            Responda OBRIGATORIAMENTE em formato JSON puro, contendo um array de objetos com os campos 'nome' e 'justificativa'.
            O campo 'justificativa' deve ser detalhado e explicar como o plano ajuda a alcançar o objetivo.";

            $response = $aiService->suggest($prompt);

            if (str_contains($response, 'Erro na IA:') || str_contains($response, 'Falha técnica')) {
                $this->aiSuggestion = null;
                session()->flash('error', $response);

                return;
            }

            $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

            if (is_array($decoded)) {
                $this->aiSuggestion = $decoded;
            } else {
                throw new \Exception('Falha ao decodificar resposta da IA.');
            }
        } catch (\Exception $e) {
            \Log::error('Erro IA Planos: '.$e->getMessage());
            $this->aiSuggestion = null;
            session()->flash('error', 'Não foi possível gerar sugestões no momento.');
        }
    }

    public function aplicarSugestao($nome, $justificativa = null)
    {
        $this->dsc_plano_de_acao = $nome;
        if ($justificativa) {
            $this->txt_detalhamento = $justificativa;
        }
        $this->aiSuggestion = '';
    }

    public function atualizarAno($ano)
    {
        $this->filtroAno = $ano;
        $this->resetPage();
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarObjetivos();
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
        // Método público (e ouvinte de evento): o ID vem do cliente. Logado, só
        // dentro do próprio escopo; o visitante da área pública só consulta.
        abort_unless(! $id || ! Auth::check() || Auth::user()->podeAcessarOrganizacao($id), 403);

        $this->organizacaoId = $id;
        $this->organizacaoNome = $id ? Organization::find($id)?->nom_organizacao : null;
        $this->resetPage();
        $this->carregarObjetivos();
    }

    public function carregarObjetivos()
    {
        $this->grausSatisfacao = GrauSatisfacao::doPei($this->peiAtivo?->cod_pei)->get();

        if ($this->peiAtivo) {
            // Carrega objetivos e agrupa por nome da perspectiva, convertendo para array para estabilidade do Livewire
            $this->objetivos = Objetivo::whereHas('perspectiva', function ($query) {
                $query->where('cod_pei', $this->peiAtivo->cod_pei);
            })->with('perspectiva')->orderBy('nom_objetivo')->get()
                ->groupBy('perspectiva.dsc_perspectiva')
                ->toArray();
        }
    }

    public function create()
    {
        // Autorização ANTES do pré-requisito: ver comentário equivalente em
        // ListarIndicadores::create().
        $this->authorize('modulo.criar', 'planos-de-acao');

        $bloqueio = app(PeiGuidanceService::class)
            ->verificarPreRequisitos('planos', $this->peiAtivo?->cod_pei ?? null);
        if ($bloqueio) {
            $this->dispatch('notify', message: $bloqueio['mensagem'], style: 'warning');

            return;
        }

        try {
            // Iniciativa nasce pelas mãos do Administrador da unidade, que
            // designa os gestores depois (Gestor não cria iniciativa).
            $this->authorize('create', [PlanoDeAcao::class, $this->organizacaoId]);
        } catch (AuthorizationException $e) {
            // Sem isto, a causa real desaparece: o cliente recebe uma
            // orientação genérica e não sobra rastro nenhum para investigar.
            report($e);

            $this->dispatch('notify', message: 'Você não tem permissão para criar planos.', style: 'danger');

            return;
        }

        $this->resetForm();

        if (! $this->organizacaoId) {
            $this->dispatch('notify', message: 'Selecione uma organização no menu superior.', style: 'warning');

            return;
        }

        $this->showModal = true;
    }

    public function edit($id)
    {
        $plano = PlanoDeAcao::with('organizacoes')->findOrFail($id);
        $this->authorize('update', $plano);

        $this->planoId = $id;
        $this->dsc_plano_de_acao = $plano->dsc_plano_de_acao;
        $this->txt_detalhamento = $plano->txt_detalhamento;
        $this->cod_objetivo = $plano->cod_objetivo;
        $this->cod_tipo_execucao = $plano->cod_tipo_execucao;
        $this->dte_inicio = $plano->dte_inicio?->format('Y-m-d');
        $this->dte_fim = $plano->dte_fim?->format('Y-m-d');
        $this->vlr_orcamento_previsto = $plano->vlr_orcamento_previsto;
        $this->bln_status = $plano->bln_status;
        $this->cod_ppa = $plano->cod_ppa;
        $this->cod_loa = $plano->cod_loa;
        $this->organizacoes_ids = $plano->organizacoes->pluck('cod_organizacao')->toArray();
        $this->modelo_logico = array_merge(['insumos' => '', 'atividades' => '', 'resultados' => '', 'impacto' => '', 'pressupostos' => ''], $plano->json_modelo_logico ?? []);

        $this->showModal = true;
    }

    public function save()
    {
        // 🔴 Esta tela é servida também na área pública de transparência, e
        // /livewire/update não passa pelo middleware `transparencia`: sem esta
        // guarda, um visitante anônimo criava ou alterava iniciativas.
        $planoExistente = $this->planoId ? PlanoDeAcao::with('organizacoes')->findOrFail($this->planoId) : null;
        abort_unless(Auth::check(), 403);

        $usuario = Auth::user();
        $orgsInformadas = array_values(array_unique(array_filter((array) $this->organizacoes_ids, 'is_string')));
        $principal = $this->unidadePrincipal($orgsInformadas, $planoExistente);

        if ($planoExistente) {
            $this->authorize('update', $planoExistente);

            // O Gestor edita a SUA iniciativa, mas não a transfere de unidade:
            // cada unidade acrescentada exige ser Administrador dela.
            $atuais = $planoExistente->organizacoes->pluck('cod_organizacao')->all();
            // Unidade acrescentada OU retirada, e troca da unidade principal (que
            // vira cod_organizacao e decide a Policy): só quem administra.
            // 🔴 Antes só a acrescentada era conferida — o Gestor reordenava ou
            // reduzia a lista e tirava a iniciativa do Administrador original.
            $alteradas = array_merge(array_diff($orgsInformadas, $atuais), array_diff($atuais, $orgsInformadas));
            if ($principal !== $planoExistente->cod_organizacao) {
                $alteradas[] = $planoExistente->cod_organizacao;
                $alteradas[] = $principal;
            }
            foreach (array_unique(array_filter($alteradas)) as $codOrg) {
                abort_unless($usuario->isSuperAdmin() || $usuario->ehAdministradorEm($codOrg), 403);
            }
        } else {
            // As organizações vêm do cliente: em cada uma, quem grava precisa
            // poder CRIAR iniciativa — não basta enxergá-la.
            foreach ($orgsInformadas as $codOrg) {
                abort_unless($usuario->podeAcessarOrganizacao($codOrg), 403);
                $this->authorize('create', [PlanoDeAcao::class, $codOrg]);
            }
        }

        $messages = [
            'dsc_plano_de_acao.required' => 'A descrição do plano é obrigatória.',
            'cod_objetivo.required' => 'Vincule o plano a um objetivo estratégico.',
            'cod_tipo_execucao.required' => 'Defina o tipo de execução (Projeto, Atividade, etc).',
            'dte_inicio.required' => 'A data de início é obrigatória.',
            'dte_fim.required' => 'A data de término é obrigatória.',
            'dte_fim.after_or_equal' => 'A data final não pode ser anterior à data inicial.',
        ];

        $this->validate([
            'dsc_plano_de_acao' => 'required|string|max:255',
            'txt_detalhamento' => 'nullable|string',
            'cod_objetivo' => 'required|exists:tab_objetivo,cod_objetivo',
            'cod_tipo_execucao' => 'required|exists:tab_tipo_execucao,cod_tipo_execucao',
            'dte_inicio' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    // Validar contra PEI associado ao objetivo selecionado
                    if ($this->cod_objetivo) {
                        $objetivo = Objetivo::find($this->cod_objetivo);
                        $pei = $objetivo?->perspectiva?->pei;

                        if ($pei) {
                            $anoInicio = Carbon::create($pei->num_ano_inicio_pei, 1, 1)->startOfDay();
                            $dataInput = Carbon::parse($value);

                            if ($dataInput->lt($anoInicio)) {
                                $fail("A data de início deve ser igual ou posterior ao início do PEI ({$pei->num_ano_inicio_pei}).");
                            }
                        }
                    }
                },
            ],
            'dte_fim' => [
                'required',
                'date',
                'after_or_equal:dte_inicio',
                function ($attribute, $value, $fail) {
                    if ($this->cod_objetivo) {
                        $objetivo = Objetivo::find($this->cod_objetivo);
                        $pei = $objetivo?->perspectiva?->pei;

                        if ($pei) {
                            $anoFim = Carbon::create($pei->num_ano_fim_pei, 12, 31)->endOfDay();
                            $dataInput = Carbon::parse($value);

                            if ($dataInput->gt($anoFim)) {
                                $fail("A data final deve ser igual ou anterior ao fim do PEI ({$pei->num_ano_fim_pei}).");
                            }
                        }
                    }
                },
            ],
            'vlr_orcamento_previsto' => 'nullable|numeric|min:0',
            // varchar(191) no banco: sem o limite, texto maior era erro 500.
            'cod_ppa' => 'nullable|string|max:191',
            'cod_loa' => 'nullable|string|max:191',
            'bln_status' => 'nullable|in:'.implode(',', self::STATUS_OPCOES),
            'modelo_logico' => 'array',
            'modelo_logico.*' => 'nullable|string|max:5000',
            'organizacoes_ids' => 'required|array|min:1',
        ], $messages + [
            'cod_ppa.max' => 'O código do PPA aceita até 191 caracteres.',
            'cod_loa.max' => 'O código da LOA aceita até 191 caracteres.',
            'dsc_plano_de_acao.max' => 'A descrição da iniciativa aceita até 255 caracteres.',
            'modelo_logico.*.max' => 'Cada campo do modelo lógico aceita até 5.000 caracteres.',
        ]);

        $data = [
            'dsc_plano_de_acao' => $this->dsc_plano_de_acao,
            'txt_detalhamento' => $this->txt_detalhamento,
            'cod_objetivo' => $this->cod_objetivo,
            'cod_tipo_execucao' => $this->cod_tipo_execucao,
            'dte_inicio' => $this->dte_inicio,
            'dte_fim' => $this->dte_fim,
            'vlr_orcamento_previsto' => $this->vlr_orcamento_previsto,
            'bln_status' => $this->bln_status,
            'cod_ppa' => $this->cod_ppa,
            'cod_loa' => $this->cod_loa,
            'cod_organizacao' => $principal,
            'num_nivel_hierarquico_apresentacao' => 3,
            'json_modelo_logico' => array_filter($this->modelo_logico) ?: null,
        ];

        if ($planoExistente) {
            $plano = $planoExistente;
            $plano->update($data);
            $plano->organizacoes()->sync($orgsInformadas);
        } else {
            $plano = PlanoDeAcao::create($data);
            $plano->organizacoes()->sync($orgsInformadas);
        }

        // Capture details for success modal before resetting
        $tipo = TipoExecucao::find($this->cod_tipo_execucao)?->dsc_tipo_execucao ?? 'Item';
        $this->createdPlanType = $tipo;
        $this->createdPlanName = $this->dsc_plano_de_acao;

        $this->showModal = false;
        $this->resetForm();

        // Show success modal instead of flash message
        $this->showSuccessModal = true;
    }

    /**
     * Unidade principal (cod_organizacao, que decide a Policy) sem depender da
     * ordem em que as unidades foram marcadas. Antes era a 1ª da lista: marcar
     * em outra ordem trocava a principal em silêncio (ou dava 403 ao Gestor).
     * Edição: mantém a já gravada enquanto ela continuar marcada. Nova (ou a
     * gravada foi desmarcada): a unidade selecionada no topo, se marcada;
     * senão, a primeira marcada.
     *
     * @param  array<int, string>  $orgsInformadas
     */
    private function unidadePrincipal(array $orgsInformadas, ?PlanoDeAcao $planoExistente): ?string
    {
        if ($planoExistente && in_array($planoExistente->cod_organizacao, $orgsInformadas, true)) {
            return $planoExistente->cod_organizacao;
        }

        if ($this->organizacaoId && in_array($this->organizacaoId, $orgsInformadas, true)) {
            return $this->organizacaoId;
        }

        return $orgsInformadas[0] ?? null;
    }

    public function confirmDelete($id)
    {
        $plano = PlanoDeAcao::findOrFail($id);
        $this->authorize('delete', $plano);
        $this->planoId = $id;
        $this->impactoExclusao = [
            'entregas' => $plano->entregas()->count(),
            'indicadores' => $plano->indicadores()->count(),
            'gestores' => DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')
                ->where('cod_plano_de_acao', $plano->cod_plano_de_acao)
                ->whereNull('deleted_at')
                ->count(),
        ];
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $plano = PlanoDeAcao::findOrFail($this->planoId);
        $this->authorize('delete', $plano);

        $plano->delete();
        $this->showDeleteModal = false;

        $this->dispatch('mentor-notification',
            title: 'Iniciativa excluída',
            message: 'A iniciativa e o que dependia dela saíram das telas. Os registros ficam guardados e só o suporte técnico os recupera.',
            icon: 'bi-trash',
            type: 'warning'
        );
    }

    public function resetForm()
    {
        $this->planoId = null;
        $this->dsc_plano_de_acao = '';
        $this->txt_detalhamento = '';
        $this->cod_objetivo = '';
        $this->cod_tipo_execucao = '';
        $this->dte_inicio = null;
        $this->dte_fim = null;
        $this->vlr_orcamento_previsto = 0;
        $this->bln_status = 'Não Iniciado';
        $this->cod_ppa = '';
        $this->cod_loa = '';
        $this->modelo_logico = ['insumos' => '', 'atividades' => '', 'resultados' => '', 'impacto' => '', 'pressupostos' => ''];
        $this->organizacoes_ids = $this->organizacaoId ? [$this->organizacaoId] : [];
        $this->aiSuggestion = '';
    }

    public function render()
    {
        $query = PlanoDeAcao::query()
            ->with(['objetivo', 'tipoExecucao', 'organizacoes', 'indicadoresVinculados'])
            ->where('cod_tipo_execucao', '!=', 'ecef6a50-c010-4cda-afc3-cbda245b55b0');

        // O filtro de objetivo SE SOMA ao de unidade (antes o substituía e listava
        // as iniciativas de todas as unidades daquele objetivo).
        if ($this->filtroObjetivo) {
            $query->where('cod_objetivo', $this->filtroObjetivo);
        }

        if ($this->organizacaoId) {
            // A unidade selecionada e as subordinadas — limitadas ao que o
            // usuário de fato alcança (o Administrador e a Consulta alcançam as
            // subordinadas; o Gestor, só a própria unidade).
            $orgIds = Organization::descendentesEProprio($this->organizacaoId);
            $usuario = auth()->user();
            if ($usuario && ! $usuario->isSuperAdmin()) {
                $orgIds = array_values(array_intersect($orgIds, $usuario->organizacaoIdsPermitidas()->all()));
            } elseif (! $usuario) {
                $orgIds = [$this->organizacaoId];
            }

            $query->whereHas('organizacoes', function ($sub) use ($orgIds) {
                $sub->whereIn('tab_organizacoes.cod_organizacao', $orgIds);
            });
        }

        // Só o ciclo selecionado no topo, como em Indicadores. Sem isto, com dois
        // PEIs na mesma unidade (ex.: um "Salvar como"), as iniciativas dos dois
        // apareciam misturadas.
        if ($this->peiAtivo && ! $this->filtroObjetivo) {
            $codPei = $this->peiAtivo->cod_pei;
            $query->whereHas('objetivo.perspectiva', fn ($p) => $p->where('cod_pei', $codPei));
        }

        if ($this->search) {
            $query->where('dsc_plano_de_acao', 'ilike', '%'.$this->search.'%');
        }

        if ($this->filtroStatus) {
            $query->where('bln_status', $this->filtroStatus);
        }

        if ($this->filtroTipo) {
            $query->where('cod_tipo_execucao', $this->filtroTipo);
        }

        // Iniciativa VIGENTE no ano: começa até 31/12 e termina a partir de
        // 01/01. Antes só entrava se começasse ou terminasse no ano — a
        // plurianual (2026–2028) sumia no filtro 2027.
        if ($this->filtroAno && ctype_digit((string) $this->filtroAno)) {
            $ano = (int) $this->filtroAno;
            $query->whereDate('dte_inicio', '<=', "{$ano}-12-31")
                ->whereDate('dte_fim', '>=', "{$ano}-01-01");
        }

        // Layout dinâmico: o visitante chega aqui pelo Mapa Estratégico público
        // e não tem menu autenticado. Mesmo critério do MapaEstrategico.
        return view('livewire.plano-acao.listar-planos', [
            'planos' => $query->orderBy('dte_fim')->paginate(10),
        ])
            ->layout(Auth::check() ? 'layouts.app' : 'layouts.public');
    }
}
