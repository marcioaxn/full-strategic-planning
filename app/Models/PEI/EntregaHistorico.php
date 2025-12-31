<?php

namespace App\Models\PEI;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model de Histórico de Entrega.
 * 
 * Registra todas as alterações feitas nas entregas para auditoria
 * e funcionalidade de "histórico de versões".
 */
class EntregaHistorico extends Model
{
    use HasFactory, HasUuids;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_entrega_historico';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_historico';

    /**
     * Tipo da chave primária
     */
    protected $keyType = 'string';

    /**
     * Chave primária não é auto-incremental
     */
    public $incrementing = false;

    /**
     * Apenas created_at é usado
     */
    public $timestamps = false;

    /**
     * Atributos mass assignable
     */
    protected $fillable = [
        'cod_entrega',
        'cod_usuario',
        'dsc_acao',
        'dsc_campo',
        'json_valor_antigo',
        'json_valor_novo',
        'dsc_descricao',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'json_valor_antigo' => 'array',
        'json_valor_novo' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Tipos de ações disponíveis
     */
    public const ACOES = [
        'created' => ['icon' => 'plus-circle', 'color' => 'success', 'label' => 'Criado'],
        'updated' => ['icon' => 'pencil', 'color' => 'primary', 'label' => 'Atualizado'],
        'deleted' => ['icon' => 'trash', 'color' => 'danger', 'label' => 'Excluído'],
        'restored' => ['icon' => 'arrow-counterclockwise', 'color' => 'info', 'label' => 'Restaurado'],
        'status_changed' => ['icon' => 'arrow-repeat', 'color' => 'warning', 'label' => 'Status alterado'],
        'comment_added' => ['icon' => 'chat', 'color' => 'secondary', 'label' => 'Comentário adicionado'],
        'attachment_added' => ['icon' => 'paperclip', 'color' => 'secondary', 'label' => 'Anexo adicionado'],
        'label_added' => ['icon' => 'tag', 'color' => 'info', 'label' => 'Label adicionada'],
        'label_removed' => ['icon' => 'tag', 'color' => 'warning', 'label' => 'Label removida'],
        'assigned' => ['icon' => 'person-plus', 'color' => 'info', 'label' => 'Atribuído'],
        'unassigned' => ['icon' => 'person-dash', 'color' => 'warning', 'label' => 'Desatribuído'],
    ];

    // ========================================
    // RELACIONAMENTOS
    // ========================================

    /**
     * Relacionamento: Entrega
     */
    public function entrega(): BelongsTo
    {
        return $this->belongsTo(Entrega::class, 'cod_entrega', 'cod_entrega');
    }

    /**
     * Relacionamento: Usuário que fez a ação
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario', 'id');
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    /**
     * Retorna informações da ação
     */
    public function getAcaoInfo(): array
    {
        return self::ACOES[$this->dsc_acao] ?? [
            'icon' => 'circle',
            'color' => 'secondary',
            'label' => ucfirst($this->dsc_acao),
        ];
    }

    /**
     * Retorna o valor antigo formatado
     */
    public function getValorAntigo()
    {
        return $this->json_valor_antigo['value'] ?? null;
    }

    /**
     * Retorna o valor novo formatado
     */
    public function getValorNovo()
    {
        return $this->json_valor_novo['value'] ?? null;
    }

    /**
     * Gera descrição legível da alteração
     */
    public function getDescricaoLegivel(): string
    {
        if ($this->dsc_descricao) {
            return $this->dsc_descricao;
        }

        $acaoInfo = $this->getAcaoInfo();
        $usuario = $this->usuario?->name ?? 'Sistema';

        if ($this->dsc_campo) {
            $campoLabel = $this->getCampoLabel();
            return "{$usuario} alterou {$campoLabel}";
        }

        return "{$usuario}: {$acaoInfo['label']}";
    }

    /**
     * Retorna label amigável do campo
     */
    protected function getCampoLabel(): string
    {
        return match ($this->dsc_campo) {
            'dsc_entrega' => 'descrição',
            'bln_status' => 'status',
            'cod_prioridade' => 'prioridade',
            'cod_responsavel' => 'responsável',
            'dte_prazo' => 'prazo',
            'num_ordem' => 'ordem',
            'bln_arquivado' => 'arquivamento',
            default => str_replace(['dsc_', 'cod_', 'bln_', 'dte_', 'num_'], '', $this->dsc_campo),
        };
    }

    /**
     * Retorna tempo relativo (há X minutos/horas/dias)
     */
    public function getTempoRelativo(): string
    {
        return $this->created_at->diffForHumans();
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope: Por ação
     */
    public function scopePorAcao($query, string $acao)
    {
        return $query->where('dsc_acao', $acao);
    }

    /**
     * Scope: Por usuário
     */
    public function scopePorUsuario($query, int $userId)
    {
        return $query->where('cod_usuario', $userId);
    }

    /**
     * Scope: Recentes primeiro
     */
    public function scopeRecentes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
