<?php

namespace App\Livewire\Deliverables;

use Livewire\Component;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\EntregaLabel;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class DeliverablesBoard extends Component
{
    use AuthorizesRequests, WithFileUploads;

    // ========================================
    // PROPRIEDADES PÚBLICAS
    // ========================================

    /** @var PlanoDeAcao Plano de ação atual */
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
    public ?string $entregaDetalheId = null;

    // ========================================
    // PROPRIEDADES DO MODAL DE CRIAÇÃO RÁPIDA
    // ========================================

    public bool $showQuickAdd = false;
    public string $quickAddStatus = 'Não Iniciado';
    public string $quickAddTitulo = '';

    // ========================================
    // PROPRIEDADES DO MODAL DE EDIÇÃO
    // ========================================

    public bool $showEditModal = false;
    public ?string $editEntregaId = null;
    public string $editTitulo = '';
    public string $editStatus = 'Não Iniciado';
    public string $editPrioridade = 'media';
    public ?string $editPrazo = null;
    public array $editResponsaveis = []; // Mudado para array
    public string $editTipo = 'task';

    // ========================================
    // PROPRIEDADES DO MODAL DE LABELS
    // ========================================

    public bool $showLabelsModal = false;
    public ?string $labelsEntregaId = null;
    public string $novaLabelNome = '';
    public string $novaLabelCor = '#1B408E';

    public bool $showDeleteModal = false;
    public ?string $entregaParaExcluirId = null;
    public bool $isPermanentDelete = false;

    // Success Modal Properties
    public bool $showSuccessModal = false;
    public string $createdDeliverableName = '';

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
        // Tenta obter o ID do plano da URL, ou do primeiro plano disponível da organização selecionada
        $idParaCarregar = $planoId;

        if (!$idParaCarregar) {
            $orgId = session('organizacao_selecionada_id');
            if ($orgId) {
                $idParaCarregar = PlanoDeAcao::where('cod_organizacao', $orgId)->orderBy('created_at', 'desc')->first()?->cod_plano_de_acao;
            }
        }

        if ($idParaCarregar) {
            $this->plano = PlanoDeAcao::with(['tipoExecucao', 'organizacao', 'objetivo.perspectiva'])->findOrFail($idParaCarregar);
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
            // Se nenhum plano for encontrado, tentamos pegar qualquer um que o usuário tenha acesso para não mostrar tela vazia
            $primeiroDisponivel = PlanoDeAcao::first();
            if ($primeiroDisponivel) {
                $this->plano = $primeiroDisponivel;
                $this->calcularProgresso();
            } else {
                $this->plano = new PlanoDeAcao();
            }
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
        $orgId = session('organizacao_selecionada_id');

        // 1. Carrega Perspectivas do PEI
        $this->perspectivasDisponiveis = \App\Models\StrategicPlanning\Perspectiva::where('cod_pei', $peiId)
            ->orderBy('num_nivel_hierarquico_apresentacao')
            ->get();

        // 2. Carrega Objetivos (Filtrados por Perspectiva se houver)
        $this->objetivosDisponiveis = \App\Models\StrategicPlanning\Objetivo::query()
            ->whereHas('perspectiva', fn($q) => $q->where('cod_pei', $peiId))
            ->when($this->perspectivaId, fn($q) => $q->where('cod_perspectiva', $this->perspectivaId))
            ->orderBy('nom_objetivo')
            ->get();

        // 3. Carrega Planos (Filtrados por Objetivo se houver, ou apenas pela Org)
        $this->planosDisponiveis = PlanoDeAcao::query()
            ->where('cod_organizacao', $orgId)
            ->when($this->objetivoId, fn($q) => $q->where('cod_objetivo', $this->objetivoId))
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
            return redirect()->route('planos.entregas', $id);
        }
    }

    public function render()
    {
        if (!$this->plano || !$this->plano->cod_plano_de_acao) {
            return view('livewire.entregas.notion-board-vazio');
        }

        return view('livewire.entregas.notion-board', [
            'entregas' => $this->getEntregas(),
            'entregasPorStatus' => $this->getEntregasPorStatus(),
            'labels' => $this->getLabels(),
            'usuarios' => $this->getUsuarios(),
            'entregaDetalhe' => $this->entregaDetalheId ? Entrega::with(['responsavel', 'responsaveis', 'labels', 'comentarios.usuario', 'anexos', 'historico.usuario', 'subEntregas'])->find($this->entregaDetalheId) : null,
        ]);
    }

    // ========================================
    // QUERIES
    // ========================================

    protected function getEntregas()
    {
        $query = Entrega::with(['responsavel', 'responsaveis', 'labels', 'subEntregas'])
            ->where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->raiz(); // Apenas entregas sem pai

        // Filtros
        if (!$this->mostrarLixeira) {
            $query->whereNull('deleted_at');
            
            if (!$this->mostrarArquivados) {
                $query->ativas();
            }
        } else {
            $query->deletadasRecentemente();
        }

        if ($this->filtroStatus) {
            $query->porStatus($this->filtroStatus);
        }

        if ($this->filtroPrioridade) {
            $query->porPrioridade($this->filtroPrioridade);
        }

        if ($this->filtroResponsavel) {
            $query->porResponsavel((int) $this->filtroResponsavel);
        }

        if ($this->busca) {
            $query->where('dsc_entrega', 'ilike', "%{$this->busca}%");
        }

        return $query->ordenado()->get();
    }

    protected function getEntregasPorStatus(): array
    {
        $entregas = $this->getEntregas();
        
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
        return User::orderBy('name')->get(['id', 'name', 'email']);
    }

    protected function calcularProgresso(): void
    {
        $total = Entrega::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->ativas()
            ->tarefas()
            ->count();

        if ($total === 0) {
            $this->progresso = 0;
            return;
        }

        $concluidas = Entrega::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->ativas()
            ->tarefas()
            ->concluidas()
            ->count();

        $this->progresso = ($concluidas / $total) * 100;
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
        $this->mostrarArquivados = !$this->mostrarArquivados;
        $this->mostrarLixeira = false;
    }

    public function toggleLixeira(): void
    {
        $this->mostrarLixeira = !$this->mostrarLixeira;
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
        $inicio = \Carbon\Carbon::parse($this->timelineInicio);
        $fim = \Carbon\Carbon::parse($this->timelineFim);
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
        $inicio = \Carbon\Carbon::parse($this->timelineInicio);
        $fim = \Carbon\Carbon::parse($this->timelineFim);
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
        $inicio = \Carbon\Carbon::parse($this->timelineInicio);
        $fim = \Carbon\Carbon::parse($this->timelineFim);
        $duracao = $inicio->diffInDays($fim);

        if ($duracao > 7) {
            // Reduz o período em 25%
            $reducao = (int) ceil($duracao * 0.25);
            $this->timelineInicio = $inicio->addDays((int)($reducao / 2))->format('Y-m-d');
            $this->timelineFim = $fim->subDays((int)($reducao / 2))->format('Y-m-d');
            $this->timelineZoom = 'dia';
        }
    }

    /**
     * Diminui o zoom (mais dias visíveis)
     */
    public function timelineZoomOut(): void
    {
        $inicio = \Carbon\Carbon::parse($this->timelineInicio);
        $fim = \Carbon\Carbon::parse($this->timelineFim);
        $duracao = $inicio->diffInDays($fim);

        if ($duracao < 120) {
            // Aumenta o período em 50%
            $aumento = (int) ceil($duracao * 0.5);
            $this->timelineInicio = $inicio->subDays((int)($aumento / 2))->format('Y-m-d');
            $this->timelineFim = $fim->addDays((int)($aumento / 2))->format('Y-m-d');
            $this->timelineZoom = $duracao > 30 ? 'mes' : 'semana';
        }
    }

    /**
     * Define período específico para a timeline
     */
    public function timelineDefinirPeriodo(string $inicio, string $fim): void
    {
        $this->timelineInicio = $inicio;
        $this->timelineFim = $fim;
    }

    /**
     * Atualiza o prazo de uma entrega via drag & drop no Gantt
     */
    public function atualizarPrazoEntrega(string $entregaId, string $novoPrazo): void
    {
        $entrega = Entrega::findOrFail($entregaId);
        $this->authorize('update', $this->plano);

        $entrega->update([
            'dte_prazo' => $novoPrazo
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Prazo atualizado com sucesso!'
        ]);
    }

    // ========================================
    // AÇÕES DE CRIAÇÃO RÁPIDA
    // ========================================

    public function openQuickAdd(string $status = 'Não Iniciado'): void
    {
        $this->authorize('update', $this->plano);
        $this->quickAddStatus = $status;
        $this->quickAddTitulo = '';
        $this->showQuickAdd = true;
    }

    public function closeQuickAdd(): void
    {
        $this->showQuickAdd = false;
        $this->quickAddTitulo = '';
    }

    public function criarRapido(): void
    {
        $this->authorize('update', $this->plano);

        $this->validate([
            'quickAddTitulo' => 'required|string|min:3|max:500',
            'quickAddStatus' => 'required|in:' . implode(',', Entrega::STATUS_OPTIONS),
        ]);

        // Calcular próxima ordem
        $maxOrdem = Entrega::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->max('num_ordem') ?? 0;

        Entrega::create([
            'cod_plano_de_acao' => $this->plano->cod_plano_de_acao,
            'dsc_entrega' => $this->quickAddTitulo,
            'bln_status' => $this->quickAddStatus,
            'dsc_tipo' => 'task',
            'cod_prioridade' => 'media',
            'num_ordem' => $maxOrdem + 1,
            'num_nivel_hierarquico_apresentacao' => $maxOrdem + 1,
            'dsc_periodo_medicao' => '',
        ]);

        $this->closeQuickAdd();
        $this->calcularProgresso();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Entrega criada com sucesso!'
        ]);
    }

    // ========================================
    // AÇÕES DE EDIÇÃO
    // ========================================

    public function openEditModal(?string $entregaId = null): void
    {
        $this->authorize('update', $this->plano);

        if ($entregaId) {
            $entrega = Entrega::with('responsaveis')->findOrFail($entregaId);
            $this->editEntregaId = $entregaId;
            $this->editTitulo = $entrega->dsc_entrega;
            $this->editStatus = $entrega->bln_status;
            $this->editPrioridade = $entrega->cod_prioridade;
            $this->editPrazo = $entrega->dte_prazo?->format('Y-m-d');
            $this->editResponsaveis = $entrega->responsaveis->pluck('id')->toArray();
            $this->editTipo = $entrega->dsc_tipo;
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
    }

    public function salvarEntrega(): void
    {
        $this->authorize('update', $this->plano);

        $this->validate([
            'editTitulo' => 'required|string|min:3|max:500',
            'editStatus' => 'required|in:' . implode(',', Entrega::STATUS_OPTIONS),
            'editPrioridade' => 'required|in:' . implode(',', array_keys(Entrega::PRIORIDADE_OPTIONS)),
            'editPrazo' => 'nullable|date',
            'editResponsaveis' => 'nullable|array',
            'editResponsaveis.*' => 'exists:users,id',
            'editTipo' => 'required|in:' . implode(',', array_keys(Entrega::TIPO_OPTIONS)),
        ]);

        $dados = [
            'dsc_entrega' => $this->editTitulo,
            'bln_status' => $this->editStatus,
            'cod_prioridade' => $this->editPrioridade,
            'dte_prazo' => $this->editPrazo,
            'cod_responsavel' => !empty($this->editResponsaveis) ? $this->editResponsaveis[0] : null, // Mantém compatibilidade com o primeiro
            'dsc_tipo' => $this->editTipo,
        ];

        if ($this->editEntregaId) {
            $entrega = Entrega::findOrFail($this->editEntregaId);
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
        $this->entregaDetalheId = $entregaId;
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

    public function atualizarTitulo(string $entregaId, string $titulo): void
    {
        $this->authorize('update', $this->plano);

        if (strlen($titulo) < 3) {
            return;
        }

        Entrega::where('cod_entrega', $entregaId)->update([
            'dsc_entrega' => $titulo,
        ]);
    }

    public function atualizarStatus(string $entregaId, string $status): void
    {
        $this->authorize('update', $this->plano);

        if (!in_array($status, Entrega::STATUS_OPTIONS)) {
            return;
        }

        Entrega::where('cod_entrega', $entregaId)->update([
            'bln_status' => $status,
        ]);

        $this->calcularProgresso();
    }

    public function atualizarPrioridade(string $entregaId, string $prioridade): void
    {
        $this->authorize('update', $this->plano);

        if (!array_key_exists($prioridade, Entrega::PRIORIDADE_OPTIONS)) {
            return;
        }

        Entrega::where('cod_entrega', $entregaId)->update([
            'cod_prioridade' => $prioridade,
        ]);
    }

    public function atualizarResponsaveis(string $entregaId, array $userIds): void
    {
        $this->authorize('update', $this->plano);

        $entrega = Entrega::findOrFail($entregaId);
        $entrega->responsaveis()->sync($userIds);
        
        // Atualiza a coluna legada com o primeiro da lista (para compatibilidade de relatórios antigos)
        $entrega->update(['cod_responsavel' => !empty($userIds) ? $userIds[0] : null]);
    }

    public function atualizarPrazo(string $entregaId, ?string $prazo): void
    {
        $this->authorize('update', $this->plano);

        Entrega::where('cod_entrega', $entregaId)->update([
            'dte_prazo' => $prazo ?: null,
        ]);
    }

    // ========================================
    // AÇÕES DE DRAG-AND-DROP
    // ========================================

    #[On('reordenar-entregas')]
    public function reordenarEntregas(array $ordem): void
    {
        $this->authorize('update', $this->plano);

        foreach ($ordem as $index => $entregaId) {
            Entrega::where('cod_entrega', $entregaId)->update([
                'num_ordem' => $index + 1,
            ]);
        }
    }

    #[On('mover-para-status')]
    public function moverParaStatus(string $entregaId, string $novoStatus, int $novaPosicao): void
    {
        $this->authorize('update', $this->plano);

        if (!in_array($novoStatus, Entrega::STATUS_OPTIONS)) {
            return;
        }

        Entrega::where('cod_entrega', $entregaId)->update([
            'bln_status' => $novoStatus,
            'num_ordem' => $novaPosicao,
        ]);

        $this->calcularProgresso();
        $this->dispatch('re-init-sortable');
    }

    // ========================================
    // AÇÕES DE EXCLUSÃO
    // ========================================

    public function arquivar(string $entregaId): void
    {
        $this->authorize('update', $this->plano);

        Entrega::where('cod_entrega', $entregaId)->update([
            'bln_arquivado' => true,
        ]);

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Entrega arquivada. Ative "Mostrar arquivados" para visualizar.'
        ]);
    }

    public function desarquivar(string $entregaId): void
    {
        $this->authorize('update', $this->plano);

        Entrega::where('cod_entrega', $entregaId)->update([
            'bln_arquivado' => false,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Entrega restaurada do arquivo.'
        ]);
    }

    public function confirmDeleteEntrega(string $entregaId, bool $isPermanent = false): void
    {
        $this->authorize('update', $this->plano);
        $this->entregaParaExcluirId = $entregaId;
        $this->isPermanentDelete = $isPermanent;
        $this->showDeleteModal = true;
    }

    public function excluir(): void
    {
        $this->authorize('update', $this->plano);

        if ($this->entregaParaExcluirId) {
            if ($this->isPermanentDelete) {
                Entrega::withTrashed()->where('cod_entrega', $this->entregaParaExcluirId)->forceDelete();
                $title = 'Exclusão Permanente';
                $message = 'A entrega foi removida definitivamente.';
            } else {
                Entrega::where('cod_entrega', $this->entregaParaExcluirId)->delete();
                $title = 'Entrega Removida';
                $message = 'A entrega foi movida para a lixeira.';
            }
            $this->entregaParaExcluirId = null;
        }

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
        $this->authorize('update', $this->plano);

        Entrega::withTrashed()->where('cod_entrega', $entregaId)->restore();

        $this->calcularProgresso();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Entrega restaurada com sucesso!'
        ]);
    }

    public function excluirPermanente(string $entregaId): void
    {
        $this->authorize('update', $this->plano);

        Entrega::withTrashed()->where('cod_entrega', $entregaId)->forceDelete();

        $this->dispatch('notify', [
            'type' => 'danger',
            'message' => 'Entrega excluída permanentemente.'
        ]);
    }

    // ========================================
    // AÇÕES DE LABELS
    // ========================================

    public function openLabelsModal(string $entregaId): void
    {
        $this->labelsEntregaId = $entregaId;
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

        $entrega = Entrega::findOrFail($entregaId);
        $entrega->labels()->toggle($labelId);
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
            'num_ordem' => EntregaLabel::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)->count() + 1
        ]);

        $this->novaLabelNome = '';
        $this->novaLabelCor = '#1B408E';
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Label criada com sucesso!'
        ]);
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

        $entrega = Entrega::findOrFail($entregaId);
        $entrega->comentarios()->create([
            'cod_usuario' => Auth::id(),
            'dsc_comentario' => $conteudo,
            'cod_comentario_pai' => $comentarioPaiId
        ]);

        $this->respondendoComentarioId = null;
        $entrega->registrarHistorico('comment_added');
    }

    public function excluirComentario(string $comentarioId): void
    {
        $this->authorize('update', $this->plano);

        \App\Models\ActionPlan\EntregaComentario::where('cod_comentario', $comentarioId)
            ->where('cod_usuario', Auth::id())
            ->delete();
    }

    // ========================================
    // AÇÕES DE ANEXOS
    // ========================================

    public function updatedAnexosUpload(): void
    {
        $this->validate([
            'anexosUpload.*' => 'required|file|max:10240', // Max 10MB por arquivo
        ]);

        if (!$this->entregaDetalheId) return;

        foreach ($this->anexosUpload as $file) {
            $nomeOriginal = $file->getClientOriginalName();
            $path = $file->store('entregas/anexos', 'public');

            \App\Models\ActionPlan\EntregaAnexo::create([
                'cod_entrega' => $this->entregaDetalheId,
                'cod_usuario' => Auth::id(),
                'dsc_nome_arquivo' => $nomeOriginal,
                'dsc_caminho' => $path,
                'dsc_mime_type' => $file->getMimeType(),
                'num_tamanho_bytes' => $file->getSize(),
            ]);
        }

        $this->anexosUpload = []; // Limpa o input
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Arquivo(s) anexado(s) com sucesso!'
        ]);
    }

    public function excluirAnexo(string $anexoId): void
    {
        $this->authorize('update', $this->plano);

        $anexo = \App\Models\ActionPlan\EntregaAnexo::findOrFail($anexoId);
        
        // Remove arquivo físico se desejar (opcional dependendo da política de backup)
        // Storage::disk('public')->delete($anexo->dsc_caminho);
        
        $anexo->delete();

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Anexo removido.'
        ]);
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
