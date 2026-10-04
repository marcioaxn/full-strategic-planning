<?php

namespace App\Livewire\Deliverables;

use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\EntregaAnexo;
use App\Models\ActionPlan\EntregaComentario;
use App\Models\ActionPlan\EntregaLabel;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\Organization;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\User;
use App\Services\IndicadorCalculoService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class DeliverablesBoard extends Component
{
    use AuthorizesRequests, WithFileUploads;

    // ========================================
    // PROPRIEDADES PÚBLICAS
    // ========================================

    /** @var PlanoDeAcao Iniciativa atual */
    public PlanoDeAcao $plano;

    /** @var string View atual: kanban, lista, timeline, calendario */
    #[Url]
    public string $view = 'kanban';

    /** @var string Filtro de status */
    #[Url]
    public string $filtroStatus = '';

    /** @var string Filtro de prioridade */
    #[Url]
    public string $filtroPrioridade = '';

    /** @var string Filtro de responsável */
    #[Url]
    public string $filtroResponsavel = '';

    /** @var string Busca por texto */
    #[Url]
    public string $busca = '';

    /** @var bool Mostrar arquivados */
    public bool $mostrarArquivados = false;

    /** @var bool Mostrar lixeira (deletados) */
    public bool $mostrarLixeira = false;

    /** @var float Progresso geral do plano */
    public float $progresso = 0;

    /** @var array Lista de planos para o seletor */
    public $planosDisponiveis = [];

    /** @var string IDs para navegação estratégica */
    public $perspectivaId = '';

    public $objetivoId = '';

    /** @var array Listas para os seletores */
    public $perspectivasDisponiveis = [];

    public $objetivosDisponiveis = [];

    // ========================================
    // PROPRIEDADES DO MODAL DE DETALHES
    // ========================================

    public bool $showDetails = false;

    // IDs de entrega abaixo só o servidor define, sempre conferidos contra o plano da tela.
    #[Locked]
    public ?string $entregaDetalheId = null;

    // ========================================
    // PROPRIEDADES DO MODAL DE CRIAÇÃO RÁPIDA
    // ========================================

    public bool $showQuickAdd = false;

    public string $quickAddStatus = 'Não Iniciado';

    public string $quickAddTitulo = '';

    /** Prazo vindo do clique num dia do calendário (Y-m-d); só openQuickAdd define. */
    #[Locked]
    public ?string $quickAddPrazo = null;

    // ========================================
    // PROPRIEDADES DO MODAL DE EDIÇÃO
    // ========================================

    public bool $showEditModal = false;

    #[Locked]
    public ?string $editEntregaId = null;

    public string $editTitulo = '';

    public string $editStatus = 'Não Iniciado';

    public string $editPrioridade = 'media';

    public ?string $editPrazo = null;

    public array $editResponsaveis = [];

    public string $editTipo = 'task';

    public array $edit5w2h = ['what' => '', 'why' => '', 'who' => '', 'where' => '', 'when' => '', 'how' => '', 'howmuch' => ''];

    // ========================================
    // PROPRIEDADES DO MODAL DE LABELS
    // ========================================

    public bool $showLabelsModal = false;

    #[Locked]
    public ?string $labelsEntregaId = null;

    public string $novaLabelNome = '';

    public string $novaLabelCor = '#1B408E';

    public bool $showDeleteModal = false;

    #[Locked]
    public ?string $entregaParaExcluirId = null;

    public bool $isPermanentDelete = false;

    // Success Modal Properties
    public bool $showSuccessModal = false;

    public string $createdDeliverableName = '';

    public bool $entregaFoiEditada = false;

    /** @var string|null ID do comentário que está sendo respondido */
    public ?string $respondendoComentarioId = null;

    /** @var array Upload de arquivos */
    public $anexosUpload = [];

    // ========================================
    // PROPRIEDADES DO CALENDÁRIO
    // ========================================

    /** @var int Mês atual do calendário (1-12) */
    public int $calendarioMes;

    /** @var int Ano atual do calendário */
    public int $calendarioAno;

    // ========================================
    // PROPRIEDADES DO GANTT/TIMELINE
    // ========================================

    /** @var string Data de início da timeline (Y-m-d) */
    public string $timelineInicio;

    /** @var string Data de fim da timeline (Y-m-d) */
    public string $timelineFim;

    /** @var string Nível de zoom: dia, semana, mes */
    public string $timelineZoom = 'semana';

    // ========================================
    // CICLO DE VIDA
    // ========================================

    public function mount(?string $planoId = null): void
    {
        // Entrega só existe dentro de uma iniciativa (regra do BSC). 🔴 Sem iniciativa
        // na URL, o quadro escolhia SOZINHO a iniciativa mais recente da unidade: a
        // pessoa cadastrava entrega numa iniciativa que não escolheu, sem perceber.
        // Agora, sem iniciativa, a tela pede a escolha (render → seletor).
        if ($planoId) {
            $this->plano = PlanoDeAcao::with(['tipoExecucao', 'organizacao', 'objetivo.perspectiva'])->findOrFail($planoId);
            $this->authorize('view', $this->plano);
            $this->calcularProgresso();

            // Sincroniza os IDs de navegação com o plano atual
            if ($this->plano->objetivo) {
                $this->objetivoId = $this->plano->cod_objetivo;
                if ($this->plano->objetivo->perspectiva) {
                    $this->perspectivaId = $this->plano->objetivo->cod_perspectiva;
                }
            }
        } else {
            $this->authorize('modulo.acessar', 'entregas');
            $this->plano = new PlanoDeAcao;
        }

        // Inicializa calendário no mês atual
        $this->calendarioMes = (int) now()->format('m');
        $this->calendarioAno = (int) now()->format('Y');

        // Inicializa timeline (4 semanas: 1 semana atrás + 3 semanas à frente)
        $this->timelineInicio = now()->subWeek()->startOfWeek()->format('Y-m-d');
        $this->timelineFim = now()->addWeeks(3)->endOfWeek()->format('Y-m-d');

        $this->carregarListasEstrategicas();
    }

    public function carregarListasEstrategicas()
    {
        $peiId = session('pei_selecionado_id');
        $usuario = Auth::user();
        $orgId = $usuario?->organizacaoSelecionadaId();

        // A unidade selecionada e as subordinadas, dentro do que o usuário alcança.
        $orgIds = $orgId ? Organization::descendentesEProprio($orgId) : [];
        if ($usuario && ! $usuario->isSuperAdmin()) {
            $orgIds = array_values(array_intersect($orgIds, $usuario->organizacaoIdsPermitidas()->all()));
        }

        // 1. Carrega Perspectivas do PEI
        $this->perspectivasDisponiveis = Perspectiva::where('cod_pei', $peiId)
            ->orderBy('num_nivel_hierarquico_apresentacao')
            ->get();

        // 2. Carrega Objetivos (Filtrados por Perspectiva se houver)
        $this->objetivosDisponiveis = Objetivo::query()
            ->whereHas('perspectiva', fn ($q) => $q->where('cod_pei', $peiId))
            ->when($this->perspectivaId, fn ($q) => $q->where('cod_perspectiva', $this->perspectivaId))
            ->orderBy('nom_objetivo')
            ->get();

        // 3. Carrega Planos (Filtrados por Objetivo se houver, ou apenas pela Org).
        // Só as do ciclo selecionado no topo (como a lista de Iniciativas); a
        // iniciativa aberta fica sempre na lista, para o seletor mostrá-la.
        $planoAtualId = $this->plano?->cod_plano_de_acao;
        $this->planosDisponiveis = PlanoDeAcao::query()
            ->whereIn('cod_organizacao', $orgIds)
            ->where(function ($q) use ($peiId, $planoAtualId) {
                $q->whereHas('objetivo.perspectiva', fn ($p) => $p->where('cod_pei', $peiId));
                if ($planoAtualId) {
                    $q->orWhere('cod_plano_de_acao', $planoAtualId);
                }
            })
            ->when($this->objetivoId, fn ($q) => $q->where('cod_objetivo', $this->objetivoId))
            ->orderBy('dsc_plano_de_acao')
            ->get();
    }

    /**
     * Hooks para quando o usuário muda os seletores na UI
     */
    public function updatedPerspectivaId()
    {
        $this->objetivoId = ''; // Reseta objetivo ao mudar perspectiva
        $this->carregarListasEstrategicas();
    }

    public function updatedObjetivoId()
    {
        $this->carregarListasEstrategicas();

        // Se após filtrar os planos houver apenas um, ou se o usuário selecionou um objetivo,
        // ele pode querer pular direto para o primeiro plano disponível
        if ($this->planosDisponiveis->count() === 1) {
            return redirect()->route('planos.entregas', $this->planosDisponiveis->first()->cod_plano_de_acao);
        }
    }

    public function mudarPlano($id)
    {
        if ($id) {
            $this->authorize('view', PlanoDeAcao::findOrFail($id));

            return redirect()->route('planos.entregas', $id);
        }
    }

    public function render()
    {
        if (! $this->plano || ! $this->plano->cod_plano_de_acao) {
            // Sem iniciativa escolhida: lista as iniciativas do ciclo e da unidade
            // (e subordinadas) que a pessoa pode ver, para ela escolher onde trabalhar.
            $usuario = Auth::user();
            $orgId = $usuario?->organizacaoSelecionadaId();
            $orgIds = $orgId ? Organization::descendentesEProprio($orgId) : [];
            if ($usuario && ! $usuario->isSuperAdmin()) {
                $orgIds = array_values(array_intersect($orgIds, $usuario->organizacaoIdsPermitidas()->all()));
            }

            $iniciativas = PlanoDeAcao::query()
                ->with(['objetivo', 'organizacao'])
                ->withCount('entregas')
                ->when(! ($usuario?->isSuperAdmin() && ! $orgId), fn ($q) => $q->whereIn('cod_organizacao', $orgIds))
                ->whereHas('objetivo.perspectiva', fn ($q) => $q->where('cod_pei', session('pei_selecionado_id')))
                ->orderBy('dsc_plano_de_acao')
                ->get()
                ->filter(fn (PlanoDeAcao $p) => Gate::allows('view', $p))
                ->groupBy(fn (PlanoDeAcao $p) => $p->objetivo?->nom_objetivo ?? 'Sem objetivo');

            return view('livewire.entregas.notion-board-vazio', ['iniciativasPorObjetivo' => $iniciativas]);
        }

        $entregas = $this->getEntregas();

        return view('livewire.entregas.notion-board', [
            'entregas' => $entregas,
            'entregasPorStatus' => $this->agruparPorStatus($entregas),
            'labels' => $this->getLabels(),
            'usuarios' => $this->getUsuarios(),
            // Uma checagem por render: a Lista e o kanban escondem os controles
            // de escrita de quem só consulta (antes, o clique dava 403).
            'podeEditar' => (bool) Auth::user()?->can('update', $this->plano),
            'entregaDetalhe' => $this->entregaDetalheId ? $this->entregasDoPlano()->withTrashed()->with(['responsavel', 'responsaveis', 'labels', 'comentarios.usuario', 'comentarios.respostas.usuario', 'anexos', 'historico.usuario', 'subEntregas'])->find($this->entregaDetalheId) : null,
        ]);
    }

    // ========================================
    // QUERIES
    // ========================================

    protected function getEntregas()
    {
        $query = Entrega::with(['responsavel', 'responsaveis', 'labels', 'subEntregas'])
            ->withCount(['comentarios', 'anexos'])
            ->where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->raiz(); // Apenas entregas sem pai

        // Filtros
        if (! $this->mostrarLixeira) {
            if (! $this->mostrarArquivados) {
                $query->ativas();
            }
        } else {
            // Toda a lixeira: não há rotina que esvazie, e o que está lá
            // continua restaurável (antes, após 24h sumia sem poder voltar).
            $query->onlyTrashed();
        }

        if ($this->filtroStatus) {
            $query->porStatus($this->filtroStatus);
        }

        if ($this->filtroPrioridade) {
            $query->porPrioridade($this->filtroPrioridade);
        }

        // users.id é uuid: o valor vem da URL (#[Url]) e de qualquer $set do
        // navegador. Inválido, o filtro cai (antes: (int) do uuid → 404/500).
        if ($this->filtroResponsavel && ! Str::isUuid($this->filtroResponsavel)) {
            $this->filtroResponsavel = '';
        }

        if ($this->filtroResponsavel) {
            $query->porResponsavel($this->filtroResponsavel);
        }

        if ($this->busca) {
            $query->where('dsc_entrega', 'ilike', "%{$this->busca}%");
        }

        return $query->ordenado()->get();
    }

    /**
     * Entregas do plano da tela. Todo id de entrega que chega do navegador passa por aqui:
     * sem o recorte, quem edita um plano alteraria ou apagaria entrega de qualquer outro.
     */
    protected function entregasDoPlano()
    {
        return Entrega::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao);
    }

    /** Agrupa a mesma consulta do render (antes, getEntregas() rodava duas vezes por render). */
    protected function agruparPorStatus($entregas): array
    {
        $resultado = [];
        foreach (Entrega::STATUS_OPTIONS as $status) {
            $resultado[$status] = $entregas->where('bln_status', $status)->values();
        }

        return $resultado;
    }

    protected function getLabels()
    {
        return EntregaLabel::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->ordenado()
            ->get();
    }

    protected function getUsuarios()
    {
        return $this->consultaUsuariosDaIniciativa()->orderBy('name')->get(['users.id', 'users.name', 'users.email']);
    }

    /**
     * Quem pode ser responsável por entrega: as pessoas das unidades da
     * iniciativa. Antes a lista trazia TODOS os usuários do sistema (nome e
     * e-mail) para quem abrisse o quadro, e aceitava qualquer id.
     */
    protected function consultaUsuariosDaIniciativa()
    {
        $orgs = $this->plano->organizacoes()->pluck('tab_organizacoes.cod_organizacao')
            ->push($this->plano->cod_organizacao)->filter()->unique()->all();

        return User::query()->where(function ($q) use ($orgs) {
            $q->whereHas('organizacoes', fn ($o) => $o->whereIn('tab_organizacoes.cod_organizacao', $orgs))
                ->orWhereHas('perfisAcesso', fn ($p) => $p->whereIn('rel_users_tab_organizacoes_tab_perfil_acesso.cod_organizacao', $orgs));
        });
    }

    /** @param  array<int, mixed>  $ids */
    private function garantirResponsaveisDaIniciativa(array $ids): void
    {
        $ids = array_values(array_unique(array_filter($ids)));

        abort_unless(
            $ids === [] || $this->consultaUsuariosDaIniciativa()->whereIn('users.id', $ids)->count() === count($ids),
            422,
            'Responsável fora das unidades da iniciativa.'
        );
    }

    /** Excluir (lixeira ou definitivo) e restaurar: capacidade própria — o Gestor Substituto não exclui. */
    private function autorizarExclusao(string $entregaId): void
    {
        $this->authorize('delete', $this->entregasDoPlano()->withTrashed()->findOrFail($entregaId));
    }

    /**
     * Uma só regra de progresso para o quadro e o detalhe da iniciativa: a do
     * IndicadorCalculoService (Cancelado fora do denominador, Em Andamento 0,5).
     * Antes o quadro tinha conta própria e mostrava 33,3% onde o detalhe dava 75,0%.
     */
    protected function calcularProgresso(): void
    {
        $this->progresso = app(IndicadorCalculoService::class)->calcularProgressoPlano($this->plano);
    }

    /** Entrega deste plano pelo id vindo do navegador (sem as da lixeira). */
    private function entregaDoPlano(string $entregaId): Entrega
    {
        return $this->entregasDoPlano()->findOrFail($entregaId);
    }

    /**
     * Prazo de entrega fica dentro do período da iniciativa.
     *
     * @return string|null mensagem para a pessoa, ou null se o prazo serve
     */
    private function problemaNoPrazo(?string $prazo): ?string
    {
        if (! $prazo) {
            return null;
        }

        try {
            $data = Carbon::parse($prazo)->startOfDay();
        } catch (\Throwable) {
            return 'Informe uma data válida para o prazo.';
        }

        $inicio = $this->plano->dte_inicio?->copy()->startOfDay();
        $fim = $this->plano->dte_fim?->copy()->startOfDay();

        if (($inicio && $data->lt($inicio)) || ($fim && $data->gt($fim))) {
            return sprintf(
                'O prazo precisa estar dentro do período da iniciativa (%s a %s).',
                $inicio?->format('d/m/Y') ?? '—',
                $fim?->format('d/m/Y') ?? '—'
            );
        }

        return null;
    }

    /** Regra de validação Laravel para o prazo (mesma checagem de problemaNoPrazo). */
    private function regraPrazoNaIniciativa(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail) {
            if ($mensagem = $this->problemaNoPrazo($value)) {
                $fail($mensagem);
            }
        };
    }

    /**
     * Grava a ordem de exibição de um conjunto de entregas sem mexer nas
     * demais: as entregas recebem, na ordem pedida, as mesmas posições
     * (num_ordem) que já ocupavam. Antes a coluna recebia 1..n e a posição
     * colidia com a ordenação global — o card "pulava" de lugar.
     *
     * @param  array<int, string>  $ids
     */
    private function gravarOrdem(array $ids): void
    {
        $ids = array_values(array_unique(array_filter($ids, 'is_string')));
        if ($ids === []) {
            return;
        }

        $atuais = $this->entregasDoPlano()->whereIn('cod_entrega', $ids)->pluck('num_ordem', 'cod_entrega');
        $ids = array_values(array_filter($ids, fn ($id) => $atuais->has($id)));

        $posicoes = $atuais->values()->map(fn ($n) => (int) $n)->sort()->values()->all();

        // Posições repetidas (dado antigo) não garantem a ordem: renumera a partir da menor.
        if (count(array_unique($posicoes)) !== count($posicoes)) {
            $base = $posicoes[0] ?? 1;
            $posicoes = range($base, $base + count($posicoes) - 1);
        }

        // Só num_ordem, em lote: a reordenação não é alteração de conteúdo
        // e não entra no histórico.
        foreach ($ids as $indice => $id) {
            $this->entregasDoPlano()->where('cod_entrega', $id)->update(['num_ordem' => $posicoes[$indice]]);
        }
    }

    // ========================================
    // AÇÕES DE VIEW
    // ========================================

    public function setView(string $view): void
    {
        if (in_array($view, ['kanban', 'lista', 'timeline', 'calendario'])) {
            $this->view = $view;
        }
    }

    public function limparFiltros(): void
    {
        $this->filtroStatus = '';
        $this->filtroPrioridade = '';
        $this->filtroResponsavel = '';
        $this->busca = '';
        $this->mostrarArquivados = false;
        $this->mostrarLixeira = false;
    }

    public function toggleArquivados(): void
    {
        $this->mostrarArquivados = ! $this->mostrarArquivados;
        $this->mostrarLixeira = false;
    }

    public function toggleLixeira(): void
    {
        $this->mostrarLixeira = ! $this->mostrarLixeira;
        $this->mostrarArquivados = false;
    }

    // ========================================
    // AÇÕES DO CALENDÁRIO
    // ========================================

    /**
     * Navega para o mês anterior
     */
    public function calendarioAnterior(): void
    {
        $this->calendarioMes--;
        if ($this->calendarioMes < 1) {
            $this->calendarioMes = 12;
            $this->calendarioAno--;
        }
    }

    /**
     * Navega para o próximo mês
     */
    public function calendarioProximo(): void
    {
        $this->calendarioMes++;
        if ($this->calendarioMes > 12) {
            $this->calendarioMes = 1;
            $this->calendarioAno++;
        }
    }

    /**
     * Volta para o mês atual
     */
    public function calendarioHoje(): void
    {
        $this->calendarioMes = (int) now()->format('m');
        $this->calendarioAno = (int) now()->format('Y');
    }

    /**
     * Navega para um mês/ano específico
     */
    public function calendarioIrPara(int $mes, int $ano): void
    {
        $this->calendarioMes = max(1, min(12, $mes));
        $this->calendarioAno = $ano;
    }

    // ========================================
    // AÇÕES DO GANTT/TIMELINE
    // ========================================

    /**
     * Navega para o período anterior
     */
    public function timelineAnterior(): void
    {
        $inicio = Carbon::parse($this->timelineInicio);
        $fim = Carbon::parse($this->timelineFim);
        $duracao = $inicio->diffInDays($fim);

        // Move metade do período para trás
        $deslocamento = (int) ceil($duracao / 2);
        $this->timelineInicio = $inicio->subDays($deslocamento)->format('Y-m-d');
        $this->timelineFim = $fim->subDays($deslocamento)->format('Y-m-d');
    }

    /**
     * Navega para o próximo período
     */
    public function timelineProximo(): void
    {
        $inicio = Carbon::parse($this->timelineInicio);
        $fim = Carbon::parse($this->timelineFim);
        $duracao = $inicio->diffInDays($fim);

        // Move metade do período para frente
        $deslocamento = (int) ceil($duracao / 2);
        $this->timelineInicio = $inicio->addDays($deslocamento)->format('Y-m-d');
        $this->timelineFim = $fim->addDays($deslocamento)->format('Y-m-d');
    }

    /**
     * Centraliza na data atual
     */
    public function timelineHoje(): void
    {
        $this->timelineInicio = now()->subWeek()->startOfWeek()->format('Y-m-d');
        $this->timelineFim = now()->addWeeks(3)->endOfWeek()->format('Y-m-d');
    }

    /**
     * Aumenta o zoom (menos dias visíveis)
     */
    public function timelineZoomIn(): void
    {
        $inicio = Carbon::parse($this->timelineInicio);
        $fim = Carbon::parse($this->timelineFim);
        $duracao = $inicio->diffInDays($fim);

        if ($duracao > 7) {
            // Reduz o período em 25%
            $reducao = (int) ceil($duracao * 0.25);
            $this->timelineInicio = $inicio->addDays((int) ($reducao / 2))->format('Y-m-d');
            $this->timelineFim = $fim->subDays((int) ($reducao / 2))->format('Y-m-d');
            $this->timelineZoom = 'dia';
        }
    }

    /**
     * Diminui o zoom (mais dias visíveis)
     */
    public function timelineZoomOut(): void
    {
        $inicio = Carbon::parse($this->timelineInicio);
        $fim = Carbon::parse($this->timelineFim);
        $duracao = $inicio->diffInDays($fim);

        if ($duracao < 120) {
            // Aumenta o período em 50%
            $aumento = (int) ceil($duracao * 0.5);
            $this->timelineInicio = $inicio->subDays((int) ($aumento / 2))->format('Y-m-d');
            $this->timelineFim = $fim->addDays((int) ($aumento / 2))->format('Y-m-d');
            $this->timelineZoom = $duracao > 30 ? 'mes' : 'semana';
        }
    }

    // ========================================
    // AÇÕES DE CRIAÇÃO RÁPIDA
    // ========================================

    /** O calendário passa o dia clicado: a entrega nasce com aquele prazo. */
    public function openQuickAdd(string $status = 'Não Iniciado', ?string $prazo = null): void
    {
        $this->authorize('create', [Entrega::class, $this->plano]);
        $this->quickAddStatus = $status;
        $this->quickAddTitulo = '';
        $this->quickAddPrazo = $prazo && preg_match('/^\d{4}-\d{2}-\d{2}$/', $prazo) && checkdate((int) substr($prazo, 5, 2), (int) substr($prazo, 8, 2), (int) substr($prazo, 0, 4))
            ? $prazo
            : null;
        $this->resetErrorBag();
        $this->showQuickAdd = true;
    }

    public function closeQuickAdd(): void
    {
        $this->showQuickAdd = false;
        $this->quickAddTitulo = '';
        $this->quickAddPrazo = null;
    }

    /** Entrega só existe dentro de uma iniciativa: sem ela, nada é gravado. */
    private function exigirIniciativa(): void
    {
        abort_unless($this->plano?->cod_plano_de_acao, 422, 'Escolha a iniciativa antes de cadastrar a entrega.');
    }

    public function criarRapido(): void
    {
        $this->exigirIniciativa();
        $this->authorize('create', [Entrega::class, $this->plano]);

        $this->validate([
            'quickAddTitulo' => 'required|string|min:3|max:500',
            'quickAddStatus' => 'required|in:'.implode(',', Entrega::STATUS_OPTIONS),
            'quickAddPrazo' => ['nullable', 'date', $this->regraPrazoNaIniciativa()],
        ]);

        // Calcular próxima ordem
        $maxOrdem = Entrega::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->max('num_ordem') ?? 0;

        Entrega::create([
            'cod_plano_de_acao' => $this->plano->cod_plano_de_acao,
            'dsc_entrega' => $this->quickAddTitulo,
            'bln_status' => $this->quickAddStatus,
            'dte_prazo' => $this->quickAddPrazo,
            'dsc_tipo' => 'task',
            'cod_prioridade' => 'media',
            'num_ordem' => $maxOrdem + 1,
            'num_nivel_hierarquico_apresentacao' => $maxOrdem + 1,
            'dsc_periodo_medicao' => '',
        ]);

        $this->closeQuickAdd();
        $this->calcularProgresso();

        $this->dispatch('notify', message: 'Entrega criada com sucesso!', style: 'success');
    }

    // ========================================
    // AÇÕES DE EDIÇÃO
    // ========================================

    public function openEditModal(?string $entregaId = null): void
    {
        $entregaId
            ? $this->authorize('update', $this->entregasDoPlano()->findOrFail($entregaId))
            : $this->authorize('create', [Entrega::class, $this->plano]);

        if ($entregaId) {
            $entrega = $this->entregasDoPlano()->with('responsaveis')->findOrFail($entregaId);
            $this->editEntregaId = $entregaId;
            $this->editTitulo = $entrega->dsc_entrega;
            $this->editStatus = $entrega->bln_status;
            $this->editPrioridade = $entrega->cod_prioridade;
            $this->editPrazo = $entrega->dte_prazo?->format('Y-m-d');
            $this->editResponsaveis = $entrega->responsaveis->pluck('id')->toArray();
            $this->editTipo = $entrega->dsc_tipo;
            $props = $entrega->json_propriedades ?? [];
            $this->edit5w2h = array_merge(['what' => '', 'why' => '', 'who' => '', 'where' => '', 'when' => '', 'how' => '', 'howmuch' => ''], $props['5w2h'] ?? []);
        } else {
            $this->resetEditForm();
        }

        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->resetEditForm();
    }

    protected function resetEditForm(): void
    {
        $this->editEntregaId = null;
        $this->editTitulo = '';
        $this->editStatus = 'Não Iniciado';
        $this->editPrioridade = 'media';
        $this->editPrazo = null;
        $this->editResponsaveis = [];
        $this->editTipo = 'task';
        $this->edit5w2h = ['what' => '', 'why' => '', 'who' => '', 'where' => '', 'when' => '', 'how' => '', 'howmuch' => ''];
    }

    public function salvarEntrega(): void
    {
        $this->exigirIniciativa();

        $this->editEntregaId
            ? $this->authorize('update', $this->entregasDoPlano()->findOrFail($this->editEntregaId))
            : $this->authorize('create', [Entrega::class, $this->plano]);

        $this->validate([
            'editTitulo' => 'required|string|min:3|max:500',
            'editStatus' => 'required|in:'.implode(',', Entrega::STATUS_OPTIONS),
            'editPrioridade' => 'required|in:'.implode(',', array_keys(Entrega::PRIORIDADE_OPTIONS)),
            'editPrazo' => ['nullable', 'date', $this->regraPrazoNaIniciativa()],
            'editResponsaveis' => 'nullable|array',
            'editResponsaveis.*' => 'exists:users,id',
            'editTipo' => 'required|in:'.implode(',', array_keys(Entrega::TIPO_OPTIONS)),
            'edit5w2h' => 'array',
            'edit5w2h.*' => 'nullable|string|max:1000',
        ], [
            'edit5w2h.*.max' => 'Cada campo do 5W2H aceita até 1.000 caracteres.',
        ]);

        $this->garantirResponsaveisDaIniciativa((array) $this->editResponsaveis);

        // Só as sete chaves do 5W2H (o array vem do navegador).
        $w5h2 = array_intersect_key($this->edit5w2h, array_flip(['what', 'why', 'who', 'where', 'when', 'how', 'howmuch']));
        $propsExistentes = [];
        if ($this->editEntregaId) {
            $propsExistentes = $this->entregasDoPlano()->findOrFail($this->editEntregaId)->json_propriedades ?? [];
        }
        // Os sete campos limpos apagam o 5W2H (antes o antigo ficava gravado).
        $novasProps = $propsExistentes;
        if (array_filter($w5h2)) {
            $novasProps['5w2h'] = $w5h2;
        } else {
            unset($novasProps['5w2h']);
        }

        $dados = [
            'dsc_entrega' => $this->editTitulo,
            'bln_status' => $this->editStatus,
            'cod_prioridade' => $this->editPrioridade,
            'dte_prazo' => $this->editPrazo,
            'cod_responsavel' => ! empty($this->editResponsaveis) ? $this->editResponsaveis[0] : null,
            'dsc_tipo' => $this->editTipo,
            'json_propriedades' => $novasProps ?: null,
        ];

        if ($this->editEntregaId) {
            $entrega = $this->entregasDoPlano()->findOrFail($this->editEntregaId);
            $entrega->update($dados);
            $entrega->responsaveis()->sync($this->editResponsaveis);
            $message = 'Entrega atualizada com sucesso!';
        } else {
            $maxOrdem = Entrega::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
                ->max('num_ordem') ?? 0;

            $entrega = Entrega::create(array_merge($dados, [
                'cod_plano_de_acao' => $this->plano->cod_plano_de_acao,
                'num_ordem' => $maxOrdem + 1,
                'num_nivel_hierarquico_apresentacao' => $maxOrdem + 1,
                'dsc_periodo_medicao' => '',
            ]));

            $entrega->responsaveis()->sync($this->editResponsaveis);
            $message = 'Entrega criada com sucesso!';
        }

        $this->createdDeliverableName = $this->editTitulo;
        // O modal dizia "Entrega Registrada!" também na edição.
        $this->entregaFoiEditada = $message === 'Entrega atualizada com sucesso!';
        $this->closeEditModal();
        $this->calcularProgresso();
        $this->dispatch('re-init-sortable');

        // Disparar modal de sucesso
        $this->showSuccessModal = true;
    }

    // ========================================
    // AÇÕES DE DETALHES
    // ========================================

    public function openDetails(string $entregaId): void
    {
        $this->authorize('view', $this->plano);

        // withTrashed: na Lixeira o card também abre o detalhe (antes dava 404).
        $this->entregaDetalheId = $this->entregasDoPlano()->withTrashed()->findOrFail($entregaId)->cod_entrega;
        $this->showDetails = true;
    }

    public function closeDetails(): void
    {
        $this->showDetails = false;
        $this->entregaDetalheId = null;
    }

    // ========================================
    // AÇÕES INLINE (edição direta)
    // ========================================

    // 🔴 Toda escrita abaixo passa pelo Model ($entrega->update()/delete()...):
    // é o que dispara o histórico da entrega e o EntregaObserver (recalcula o
    // indicador automático da iniciativa). Com Query Builder, nada disso rodava.

    public function atualizarTitulo(string $entregaId, string $titulo): void
    {
        $this->authorize('update', $this->plano);

        $titulo = trim($titulo);
        if (mb_strlen($titulo) < 3 || mb_strlen($titulo) > 500) {
            $this->dispatch('notify', message: 'O título precisa ter entre 3 e 500 caracteres.', style: 'warning');

            return;
        }

        $this->entregaDoPlano($entregaId)->update(['dsc_entrega' => $titulo]);
    }

    public function atualizarStatus(string $entregaId, string $status): void
    {
        $this->authorize('update', $this->plano);

        if (! in_array($status, Entrega::STATUS_OPTIONS)) {
            return;
        }

        $this->entregaDoPlano($entregaId)->update(['bln_status' => $status]);

        $this->calcularProgresso();
    }

    public function atualizarPrioridade(string $entregaId, string $prioridade): void
    {
        $this->authorize('update', $this->plano);

        if (! array_key_exists($prioridade, Entrega::PRIORIDADE_OPTIONS)) {
            return;
        }

        $this->entregaDoPlano($entregaId)->update(['cod_prioridade' => $prioridade]);
    }

    public function atualizarResponsaveis(string $entregaId, array $userIds): void
    {
        $this->authorize('update', $this->plano);

        $userIds = array_values(array_unique(array_filter($userIds, 'is_string')));
        $this->garantirResponsaveisDaIniciativa($userIds);
        $entrega = $this->entregaDoPlano($entregaId);
        $entrega->responsaveis()->sync($userIds);

        // Atualiza a coluna legada com o primeiro da lista (para compatibilidade de relatórios antigos)
        $entrega->update(['cod_responsavel' => $userIds[0] ?? null]);
    }

    public function atualizarPrazo(string $entregaId, ?string $prazo): void
    {
        $this->authorize('update', $this->plano);

        $entrega = $this->entregaDoPlano($entregaId);

        if ($problema = $this->problemaNoPrazo($prazo)) {
            $this->dispatch('notify', message: $problema, style: 'warning');

            return;
        }

        $entrega->update(['dte_prazo' => $prazo ?: null]);
    }

    // ========================================
    // AÇÕES DE DRAG-AND-DROP
    // ========================================

    #[On('reordenar-entregas')]
    public function reordenarEntregas(array $ordem): void
    {
        $this->authorize('update', $this->plano);

        $this->gravarOrdem($ordem);
    }

    /**
     * Soltar um card no kanban: o status muda pelo Model (histórico + indicador)
     * e a coluna de destino guarda a ordem em que a pessoa deixou os cards.
     *
     * @param  array<int, string>  $ordemColuna  ids dos cards da coluna de destino, de cima para baixo
     */
    #[On('mover-para-status')]
    public function moverParaStatus(string $entregaId, string $novoStatus, array $ordemColuna = []): void
    {
        $this->authorize('update', $this->plano);

        if (! in_array($novoStatus, Entrega::STATUS_OPTIONS)) {
            return;
        }

        $this->entregaDoPlano($entregaId)->update(['bln_status' => $novoStatus]);
        $this->gravarOrdem($ordemColuna);

        $this->calcularProgresso();
        $this->dispatch('re-init-sortable');
    }

    // ========================================
    // AÇÕES DE EXCLUSÃO
    // ========================================

    public function arquivar(string $entregaId): void
    {
        $this->authorize('update', $this->plano);

        $this->entregaDoPlano($entregaId)->update(['bln_arquivado' => true]);
        $this->calcularProgresso();

        $this->dispatch('notify', message: 'Entrega arquivada. Ative "Mostrar arquivados" para visualizar.', style: 'info');
    }

    public function desarquivar(string $entregaId): void
    {
        $this->authorize('update', $this->plano);

        $this->entregaDoPlano($entregaId)->update(['bln_arquivado' => false]);
        $this->calcularProgresso();

        $this->dispatch('notify', message: 'Entrega restaurada do arquivo.', style: 'success');
    }

    public function confirmDeleteEntrega(string $entregaId, bool $isPermanent = false): void
    {
        $this->autorizarExclusao($entregaId);
        $this->entregaParaExcluirId = $this->entregasDoPlano()->withTrashed()->findOrFail($entregaId)->cod_entrega;
        $this->isPermanentDelete = $isPermanent;
        $this->showDeleteModal = true;
    }

    public function excluir(): void
    {

        // Sem entrega escolhida não há o que excluir. Antes seguia adiante e
        // quebrava com "Undefined variable $title" (erro 500 na tela).
        if (! $this->entregaParaExcluirId) {
            $this->showDeleteModal = false;

            return;
        }

        $this->autorizarExclusao($this->entregaParaExcluirId);

        if ($this->isPermanentDelete) {
            // Pelo Model: o forceDelete apaga também os arquivos dos anexos (Entrega::booted).
            $this->entregasDoPlano()->withTrashed()->findOrFail($this->entregaParaExcluirId)->forceDelete();
            $title = 'Exclusão Permanente';
            $message = 'A entrega e os arquivos anexados foram removidos definitivamente.';
        } else {
            $this->entregaDoPlano($this->entregaParaExcluirId)->delete();
            $title = 'Entrega Removida';
            $message = 'A entrega foi movida para a lixeira, de onde pode ser restaurada.';
        }
        $this->entregaParaExcluirId = null;

        $this->showDeleteModal = false;
        $this->closeDetails();
        $this->calcularProgresso();

        $this->dispatch('mentor-notification',
            title: $title,
            message: $message,
            icon: 'bi-trash',
            type: 'warning'
        );
    }

    public function restaurar(string $entregaId): void
    {
        $this->autorizarExclusao($entregaId);

        $this->entregasDoPlano()->onlyTrashed()->findOrFail($entregaId)->restore();

        $this->calcularProgresso();

        $this->dispatch('notify', message: 'Entrega restaurada com sucesso!', style: 'success');
    }

    // ========================================
    // AÇÕES DE LABELS
    // ========================================

    public function openLabelsModal(string $entregaId): void
    {
        $this->authorize('update', $this->plano);

        $this->labelsEntregaId = $this->entregasDoPlano()->findOrFail($entregaId)->cod_entrega;
        $this->showLabelsModal = true;
    }

    public function closeLabelsModal(): void
    {
        $this->showLabelsModal = false;
        $this->labelsEntregaId = null;
    }

    public function toggleLabel(string $entregaId, string $labelId): void
    {
        $this->authorize('update', $this->plano);

        $entrega = $this->entregasDoPlano()->findOrFail($entregaId);

        // A label também vem do navegador: só as deste plano.
        $label = EntregaLabel::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)->findOrFail($labelId);
        $entrega->labels()->toggle($label->getKey());
    }

    public function criarLabel(): void
    {
        $this->authorize('update', $this->plano);

        $this->validate([
            'novaLabelNome' => 'required|string|max:50',
            'novaLabelCor' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        EntregaLabel::create([
            'cod_plano_de_acao' => $this->plano->cod_plano_de_acao,
            'dsc_label' => $this->novaLabelNome,
            'dsc_cor' => $this->novaLabelCor,
            'num_ordem' => EntregaLabel::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)->count() + 1,
        ]);

        $this->novaLabelNome = '';
        $this->novaLabelCor = '#1B408E';

        $this->dispatch('notify', message: 'Label criada com sucesso!', style: 'success');
    }

    // ========================================
    // AÇÕES DE COMENTÁRIOS
    // ========================================

    public function setRespondendo(?string $comentarioId): void
    {
        $this->respondendoComentarioId = $comentarioId;
    }

    #[On('adicionar-comentario')]
    public function adicionarComentario($entregaId, $conteudo = null, $comentarioPaiId = null): void
    {
        // Robustez: Se vier como array (payload do evento não desempacotado), extrair valores
        if (is_array($entregaId)) {
            $data = $entregaId;
            $entregaId = $data['entregaId'] ?? null;
            $conteudo = $data['conteudo'] ?? null;
            $comentarioPaiId = $data['comentarioPaiId'] ?? null;
        }

        $this->authorize('update', $this->plano);

        if (empty($entregaId) || empty($conteudo)) {
            return;
        }

        $entrega = $this->entregasDoPlano()->findOrFail($entregaId);

        // Resposta só a comentário da mesma entrega.
        if ($comentarioPaiId && ! $entrega->comentarios()->where('cod_comentario', $comentarioPaiId)->exists()) {
            $comentarioPaiId = null;
        }

        $entrega->comentarios()->create([
            'cod_usuario' => Auth::id(),
            'dsc_comentario' => $conteudo,
            'cod_comentario_pai' => $comentarioPaiId,
        ]);

        $this->respondendoComentarioId = null;
        $entrega->registrarHistorico('comment_added');
    }

    public function excluirComentario(string $comentarioId): void
    {
        $this->authorize('update', $this->plano);

        // Só comentário próprio E de entrega deste plano: a autorização acima é
        // deste plano, não da iniciativa do comentário.
        $comentario = EntregaComentario::where('cod_comentario', $comentarioId)
            ->where('cod_usuario', Auth::id())
            ->whereIn('cod_entrega', $this->entregasDoPlano()->withTrashed()->select('cod_entrega'))
            ->first();

        if (! $comentario) {
            return;
        }

        // Comentário com respostas fica: excluí-lo escondia junto as respostas
        // de outras pessoas (a conversa só lista as respostas de um pai visível).
        if ($comentario->respostas()->exists()) {
            $this->dispatch('notify', message: 'Este comentário já tem respostas e por isso não pode ser excluído.', style: 'warning');

            return;
        }

        $comentario->delete();
    }

    // ========================================
    // AÇÕES DE ANEXOS
    // ========================================

    public function updatedAnexosUpload(): void
    {
        $this->authorize('update', $this->plano);

        $this->validate([
            // Só formatos de documento e imagem: um .html/.svg rodaria script se o
            // navegador o abrisse na origem do sistema.
            'anexosUpload.*' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,png,jpg,jpeg,gif,txt,csv,zip', // Max 10MB por arquivo
        ]);

        if (! $this->entregaDetalheId || ! $this->entregasDoPlano()->where('cod_entrega', $this->entregaDetalheId)->exists()) {
            return;
        }

        foreach ($this->anexosUpload as $file) {
            // dsc_nome_arquivo é varchar(255): nome maior era erro 500. Encurta
            // o nome e mantém a extensão.
            $nomeOriginal = $file->getClientOriginalName();
            if (mb_strlen($nomeOriginal) > 255) {
                $extensao = mb_substr((string) pathinfo($nomeOriginal, PATHINFO_EXTENSION), 0, 20);
                $nomeOriginal = mb_substr(pathinfo($nomeOriginal, PATHINFO_FILENAME), 0, 250 - mb_strlen($extensao)).($extensao !== '' ? '.'.$extensao : '');
            }
            $path = $file->store(EntregaAnexo::PASTA, EntregaAnexo::DISCO);

            EntregaAnexo::create([
                'cod_entrega' => $this->entregaDetalheId,
                'cod_usuario' => Auth::id(),
                'dsc_nome_arquivo' => $nomeOriginal,
                'dsc_caminho' => $path,
                'dsc_mime_type' => $file->getMimeType(),
                'num_tamanho_bytes' => $file->getSize(),
            ]);
        }

        $this->anexosUpload = []; // Limpa o input

        $this->dispatch('notify', message: 'Arquivo(s) anexado(s) com sucesso!', style: 'success');
    }

    public function excluirAnexo(string $anexoId): void
    {
        $this->authorize('update', $this->plano);

        // Anexo só de entrega deste plano (inclusive entrega na lixeira).
        $anexo = EntregaAnexo::whereIn('cod_entrega', $this->entregasDoPlano()->withTrashed()->select('cod_entrega'))
            ->findOrFail($anexoId);

        // A tela promete "excluir permanentemente": sai o arquivo físico e o registro.
        $anexo->apagarArquivo();
        $anexo->forceDelete();

        $this->dispatch('notify', message: 'Anexo removido.', style: 'info');
    }

    // ========================================
    // POLLING PARA ATUALIZAÇÃO EM TEMPO REAL
    // ========================================

    /**
     * Método chamado pelo wire:poll para atualizar a view
     * O Livewire executa este método e automaticamente re-renderiza o componente,
     * buscando os dados atualizados do banco no método render().
     */
    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->createdDeliverableName = '';
    }

    public function poll(): void
    {
        // Apenas para manter o polling ativo e forçar o re-render
        $this->calcularProgresso();
    }
}
