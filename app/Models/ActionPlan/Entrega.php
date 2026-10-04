<?php

namespace App\Models\ActionPlan;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Model de Entrega com suporte a funcionalidades estilo Notion.
 *
 * Suporta hierarquia (sub-entregas), tipos de bloco, prioridades,
 * labels, comentários, anexos e histórico de alterações.
 */
class Entrega extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'action_plan.tab_entregas';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_entrega';

    /**
     * Tipo da chave primária
     */
    protected $keyType = 'string';

    /**
     * Chave primária não é auto-incremental
     */
    public $incrementing = false;

    /**
     * Atributos mass assignable
     */
    protected $fillable = [
        'cod_plano_de_acao',
        'cod_entrega_pai',
        'dsc_entrega',
        'dsc_tipo',
        'json_propriedades',
        'bln_status',
        'dsc_periodo_medicao',
        'dte_prazo',
        'cod_responsavel',
        'cod_prioridade',
        'num_ordem',
        'num_peso',
        'num_nivel_hierarquico_apresentacao',
        'bln_arquivado',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'num_nivel_hierarquico_apresentacao' => 'integer',
        'num_ordem' => 'integer',
        'num_peso' => 'decimal:2',
        'bln_arquivado' => 'boolean',
        'json_propriedades' => 'array',
        'dte_prazo' => 'date',
    ];

    /**
     * Anexos lidos no forceDeleting, para apagar os arquivos no forceDeleted.
     * Propriedade declarada: não é atributo do Model (não vai ao banco).
     *
     * @var Collection<int, EntregaAnexo>|null
     */
    protected $anexosParaApagar = null;

    /**
     * Opções de status disponíveis
     */
    public const STATUS_OPTIONS = [
        'Não Iniciado',
        'Em Andamento',
        'Concluído',
        'Cancelado',
        'Suspenso',
    ];

    /**
     * Opções de prioridade disponíveis
     */
    public const PRIORIDADE_OPTIONS = [
        'baixa' => ['label' => 'Baixa', 'color' => '#e3e2e0', 'icon' => 'dash'],
        'media' => ['label' => 'Média', 'color' => '#fdecc8', 'icon' => 'dash-lg'],
        'alta' => ['label' => 'Alta', 'color' => '#ffe2dd', 'icon' => 'exclamation'],
        'urgente' => ['label' => 'Urgente', 'color' => '#e03e3e', 'icon' => 'exclamation-circle-fill'],
    ];

    /**
     * Tipos de bloco disponíveis
     */
    public const TIPO_OPTIONS = [
        'task' => ['label' => 'Tarefa', 'icon' => 'check2-square'],
        'heading' => ['label' => 'Cabeçalho', 'icon' => 'type-h1'],
        'text' => ['label' => 'Texto', 'icon' => 'text-paragraph'],
        'divider' => ['label' => 'Divisor', 'icon' => 'dash-lg'],
        'checklist' => ['label' => 'Checklist', 'icon' => 'list-check'],
    ];

    /**
     * Cores de status para UI
     */
    public const STATUS_COLORS = [
        'Não Iniciado' => '#e3e2e0',
        'Em Andamento' => '#fdecc8',
        'Concluído' => '#dbeddb',
        'Cancelado' => '#ffe2dd',
        'Suspenso' => '#d3e5ef',
    ];

    // ========================================
    // RELACIONAMENTOS
    // ========================================

    /**
     * Relacionamento: Iniciativa
     */
    public function planoDeAcao(): BelongsTo
    {
        return $this->belongsTo(PlanoDeAcao::class, 'cod_plano_de_acao', 'cod_plano_de_acao');
    }

    /**
     * Relacionamento: Entrega pai (hierarquia)
     */
    public function entregaPai(): BelongsTo
    {
        return $this->belongsTo(Entrega::class, 'cod_entrega_pai', 'cod_entrega');
    }

    /**
     * Relacionamento: Sub-entregas (filhos)
     */
    public function subEntregas(): HasMany
    {
        return $this->hasMany(Entrega::class, 'cod_entrega_pai', 'cod_entrega')
            ->orderBy('num_ordem');
    }

    /**
     * Relacionamento: Responsável (User) - Legado
     */
    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_responsavel', 'id');
    }

    /**
     * Relacionamento: Múltiplos responsáveis (Estilo Notion)
     */
    public function responsaveis(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'rel_entrega_users_responsaveis',
            'cod_entrega',
            'cod_usuario',
            'cod_entrega',
            'id'
        )->withTimestamps();
    }

    /**
     * Relacionamento: Comentários raiz (sem pai)
     */
    public function comentarios(): HasMany
    {
        return $this->hasMany(EntregaComentario::class, 'cod_entrega', 'cod_entrega')
            ->whereNull('cod_comentario_pai')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Relacionamento: Labels (N:N)
     */
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(
            EntregaLabel::class,
            'rel_entrega_labels',
            'cod_entrega',
            'cod_label'
        );
    }

    /**
     * Relacionamento: Anexos
     */
    public function anexos(): HasMany
    {
        return $this->hasMany(EntregaAnexo::class, 'cod_entrega', 'cod_entrega');
    }

    /**
     * Relacionamento: Histórico
     */
    public function historico(): HasMany
    {
        return $this->hasMany(EntregaHistorico::class, 'cod_entrega', 'cod_entrega')
            ->orderBy('created_at', 'desc');
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    /**
     * Verifica se entrega está concluída
     */
    public function isConcluida(): bool
    {
        return $this->bln_status === 'Concluído';
    }

    /**
     * Status em que a entrega não conta como atrasada: já terminou (Concluído),
     * foi abandonada (Cancelado) ou está parada por decisão (Suspenso).
     */
    public const STATUS_SEM_ATRASO = ['Concluído', 'Cancelado', 'Suspenso'];

    /**
     * Atrasada: prazo ANTES de hoje e status em aberto. O prazo de hoje ainda
     * está no prazo. Critério único do quadro, do calendário, do Gantt e de
     * Minhas Entregas (antes: prazo de hoje já era atraso e Cancelada contava).
     */
    public function isAtrasada(): bool
    {
        if (! $this->dte_prazo || in_array($this->bln_status, self::STATUS_SEM_ATRASO, true)) {
            return false;
        }

        return $this->dte_prazo->copy()->startOfDay()->lt(today());
    }

    /**
     * Verifica se é uma sub-entrega
     */
    public function isSubEntrega(): bool
    {
        return ! is_null($this->cod_entrega_pai);
    }

    /**
     * Verifica se tem sub-entregas
     */
    public function hasSubEntregas(): bool
    {
        return $this->subEntregas()->count() > 0;
    }

    /**
     * Retorna a cor do status
     */
    public function getStatusColor(): string
    {
        return self::STATUS_COLORS[$this->bln_status] ?? '#e3e2e0';
    }

    /**
     * Retorna informações da prioridade
     */
    public function getPrioridadeInfo(): array
    {
        return self::PRIORIDADE_OPTIONS[$this->cod_prioridade] ?? self::PRIORIDADE_OPTIONS['media'];
    }

    /**
     * Retorna informações do tipo
     */
    public function getTipoInfo(): array
    {
        return self::TIPO_OPTIONS[$this->dsc_tipo] ?? self::TIPO_OPTIONS['task'];
    }

    /**
     * Retorna propriedade do JSON
     */
    public function getProp(string $key, $default = null)
    {
        return $this->json_propriedades[$key] ?? $default;
    }

    /**
     * Define propriedade no JSON
     */
    public function setProp(string $key, $value): self
    {
        $props = $this->json_propriedades ?? [];
        $props[$key] = $value;
        $this->json_propriedades = $props;

        return $this;
    }

    /**
     * Calcula progresso baseado em sub-entregas
     */
    public function calcularProgressoSubEntregas(): float
    {
        $total = $this->subEntregas()->count();
        if ($total === 0) {
            return $this->isConcluida() ? 100 : 0;
        }

        $concluidas = $this->subEntregas()->where('bln_status', 'Concluído')->count();

        return ($concluidas / $total) * 100;
    }

    /**
     * Registra ação no histórico
     */
    public function registrarHistorico(string $acao, ?string $campo = null, $valorAntigo = null, $valorNovo = null, ?string $descricao = null): void
    {
        $this->historico()->create([
            'cod_usuario' => Auth::id(),
            'dsc_acao' => $acao,
            'dsc_campo' => $campo,
            'json_valor_antigo' => $valorAntigo ? ['value' => $valorAntigo] : null,
            'json_valor_novo' => $valorNovo ? ['value' => $valorNovo] : null,
            'dsc_descricao' => $descricao,
        ]);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope: Por status
     */
    public function scopePorStatus($query, string $status)
    {
        return $query->where('bln_status', $status);
    }

    /**
     * Scope: Entregas concluídas
     */
    public function scopeConcluidas($query)
    {
        return $query->where('bln_status', 'Concluído');
    }

    /**
     * Scope: Entregas pendentes
     */
    public function scopePendentes($query)
    {
        return $query->where('bln_status', '!=', 'Concluído');
    }

    /**
     * Scope: Ordenar por nível hierárquico (legado)
     */
    public function scopeOrdenadoPorNivel($query)
    {
        return $query->orderBy('num_nivel_hierarquico_apresentacao');
    }

    /**
     * Scope: Ordenar por ordem (novo)
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('num_ordem');
    }

    /**
     * Scope: Apenas entregas raiz (sem pai)
     */
    public function scopeRaiz($query)
    {
        return $query->whereNull('cod_entrega_pai');
    }

    /**
     * Scope: Não arquivadas
     */
    public function scopeAtivas($query)
    {
        return $query->where('bln_arquivado', false);
    }

    /**
     * Scope: Arquivadas
     */
    public function scopeArquivadas($query)
    {
        return $query->where('bln_arquivado', true);
    }

    /**
     * Scope: Por prioridade
     */
    public function scopePorPrioridade($query, string $prioridade)
    {
        return $query->where('cod_prioridade', $prioridade);
    }

    /**
     * Scope: Por responsável — a relação de responsáveis (a mesma do modal de
     * edição), não a coluna legada cod_responsavel. users.id é uuid.
     */
    public function scopePorResponsavel($query, string $userId)
    {
        return $query->whereHas('responsaveis', fn ($q) => $q->where('users.id', $userId));
    }

    /**
     * Scope: Atrasadas (mesmo critério de isAtrasada()).
     */
    public function scopeAtrasadas($query)
    {
        return $query->whereNotNull('dte_prazo')
            ->whereDate('dte_prazo', '<', today())
            ->whereNotIn('bln_status', self::STATUS_SEM_ATRASO);
    }

    /**
     * Scope: Do tipo tarefa
     */
    public function scopeTarefas($query)
    {
        return $query->where('dsc_tipo', 'task');
    }

    // ========================================
    // BOOT
    // ========================================

    protected static function boot()
    {
        parent::boot();

        // Ao criar, registrar no histórico
        static::created(function ($entrega) {
            $entrega->registrarHistorico('created', null, null, null, 'Entrega criada');
        });

        // Ao atualizar, registrar alterações no histórico
        static::updating(function ($entrega) {
            $original = $entrega->getOriginal();
            $changes = $entrega->getDirty();

            foreach ($changes as $campo => $valorNovo) {
                if (! in_array($campo, ['updated_at', 'json_propriedades'])) {
                    $entrega->registrarHistorico(
                        'updated',
                        $campo,
                        $original[$campo] ?? null,
                        $valorNovo
                    );
                }
            }
        });

        // Ao deletar, registrar no histórico (só a ida para a lixeira: na
        // exclusão definitiva o histórico vai junto, por cascata).
        static::deleting(function ($entrega) {
            if ($entrega->isForceDeleting()) {
                return;
            }

            $entrega->registrarHistorico('deleted', null, null, null, 'Entrega movida para lixeira');
        });

        // Exclusão definitiva: os registros dos anexos saem por cascata no banco,
        // mas os arquivos ficavam no disco. Guarda os anexos antes e apaga os
        // arquivos só depois que o banco confirmou a exclusão.
        static::forceDeleting(function ($entrega) {
            $entrega->anexosParaApagar = $entrega->anexos()->withTrashed()->get();
        });

        static::forceDeleted(function ($entrega) {
            foreach ($entrega->anexosParaApagar ?? [] as $anexo) {
                $anexo->apagarArquivo();
            }
        });

        // Ao restaurar, registrar no histórico
        static::restored(function ($entrega) {
            $entrega->registrarHistorico('restored', null, null, null, 'Entrega restaurada da lixeira');
        });
    }
}
