<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entrega extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'pei.tab_entregas';

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
        'dsc_entrega',
        'bln_status',
        'dsc_periodo_medicao',
        'num_nivel_hierarquico_apresentacao',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'num_nivel_hierarquico_apresentacao' => 'integer',
    ];

    /**
     * Relacionamento: Plano de Ação
     */
    public function planoDeAcao(): BelongsTo
    {
        return $this->belongsTo(PlanoDeAcao::class, 'cod_plano_de_acao', 'cod_plano_de_acao');
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Verifica se entrega está concluída
     */
    public function isConcluida(): bool
    {
        return $this->bln_status === 'Concluído';
    }

    /**
     * Scopes
     */

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
     * Scope: Ordenar por nível hierárquico
     */
    public function scopeOrdenadoPorNivel($query)
    {
        return $query->orderBy('num_nivel_hierarquico_apresentacao');
    }
}
