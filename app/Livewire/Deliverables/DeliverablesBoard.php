<?php

namespace App\Livewire\Deliverables;

use Livewire\Component;
use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;

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

    /** @var string|null ID do comentário que está sendo respondido */
    public ?string $respondendoComentarioId = null;

    /** @var array Upload de arquivos */
    public $anexosUpload = [];

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
                $idParaCarregar = PlanoDeAcao::where('cod_organizacao', $orgId)->first()?->cod_plano_de_acao;
            }
        }

        if ($idParaCarregar) {
            $this->plano = PlanoDeAcao::with('tipoExecucao', 'organizacao')->findOrFail($idParaCarregar);
            $this->authorize('view', $this->plano);
            $this->calcularProgresso();
        } else {
            // Se nenhum plano for encontrado, deixamos $this->plano nulo e tratamos na view
            $this->plano = new PlanoDeAcao(); // Objeto vazio para evitar erro de tipo na propriedade tipada
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

        $this->closeEditModal();
        $this->calcularProgresso();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message
        ]);
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

    public function excluir(string $entregaId): void
    {
        $this->authorize('update', $this->plano);

        Entrega::where('cod_entrega', $entregaId)->delete();

        $this->closeDetails();
        $this->calcularProgresso();

        $this->dispatch('notify', [
            'type' => 'warning',
            'message' => 'Entrega movida para lixeira. Será excluída permanentemente em 24 horas.'
        ]);
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
    public function adicionarComentario(string $entregaId, string $conteudo, ?string $comentarioPaiId = null): void
    {
        $this->authorize('update', $this->plano);

        if (strlen($conteudo) < 1) {
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

        \App\Models\PEI\EntregaComentario::where('cod_comentario', $comentarioId)
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

            \App\Models\PEI\EntregaAnexo::create([
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

        $anexo = \App\Models\PEI\EntregaAnexo::findOrFail($anexoId);
        
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
    public function poll(): void
    {
        // Apenas para manter o polling ativo e forçar o re-render
        $this->calcularProgresso();
    }
}
