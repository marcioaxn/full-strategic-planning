<?php

namespace App\Models\ActionPlan;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model de Label/Tag para Entregas.
 */
class EntregaLabel extends Model
{
    use HasFactory, HasUuids;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_entrega_labels';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_label';

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
        'dsc_label',
        'dsc_cor',
        'dsc_icone',
        'num_ordem',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'num_ordem' => 'integer',
    ];

    /**
     * Cores predefinidas para sugestão
     */
    public const CORES_PREDEFINIDAS = [
        '#6366f1', // Indigo
        '#8b5cf6', // Violet
        '#ec4899', // Pink
        '#ef4444', // Red
        '#f97316', // Orange
        '#eab308', // Yellow
        '#22c55e', // Green
        '#14b8a6', // Teal
        '#06b6d4', // Cyan
        '#3b82f6', // Blue
        '#64748b', // Slate
    ];

    // ========================================
    // RELACIONAMENTOS
    // ========================================

    /**
     * Relacionamento: Plano de Ação
     */
    public function planoDeAcao(): BelongsTo
    {
        return $this->belongsTo(PlanoDeAcao::class, 'cod_plano_de_acao', 'cod_plano_de_acao');
    }

    /**
     * Relacionamento: Entregas (N:N)
     */
    public function entregas(): BelongsToMany
    {
        return $this->belongsToMany(
            Entrega::class,
            'rel_entrega_labels',
            'cod_label',
            'cod_entrega'
        );
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    /**
     * Retorna a cor em formato RGB
     */
    public function getCorRgb(): array
    {
        $hex = ltrim($this->dsc_cor, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Retorna se a cor é escura (para decidir cor do texto)
     */
    public function isCorEscura(): bool
    {
        $rgb = $this->getCorRgb();
        // Fórmula de luminância relativa
        $luminancia = ($rgb['r'] * 0.299 + $rgb['g'] * 0.587 + $rgb['b'] * 0.114) / 255;
        return $luminancia < 0.5;
    }

    /**
     * Retorna a cor do texto apropriada
     */
    public function getCorTexto(): string
    {
        return $this->isCorEscura() ? '#ffffff' : '#1f2937';
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope: Ordenado
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('num_ordem');
    }
}
