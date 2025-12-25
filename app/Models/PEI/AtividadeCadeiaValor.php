<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AtividadeCadeiaValor extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'pei.tab_atividade_cadeia_valor';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_atividade_cadeia_valor';

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
        'cod_pei',
        'cod_perspectiva',
        'dsc_atividade',
    ];

    /**
     * Relacionamento: PEI
     */
    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

    /**
     * Relacionamento: Perspectiva BSC
     */
    public function perspectiva(): BelongsTo
    {
        return $this->belongsTo(Perspectiva::class, 'cod_perspectiva', 'cod_perspectiva');
    }

    /**
     * Relacionamento: Processos da Atividade
     */
    public function processos(): HasMany
    {
        return $this->hasMany(ProcessoAtividadeCadeiaValor::class, 'cod_atividade_cadeia_valor', 'cod_atividade_cadeia_valor');
    }

    /**
     * Scopes
     */

    /**
     * Scope: Por PEI
     */
    public function scopePorPei($query, string $codPei)
    {
        return $query->where('cod_pei', $codPei);
    }

    /**
     * Scope: Por perspectiva
     */
    public function scopePorPerspectiva($query, string $codPerspectiva)
    {
        return $query->where('cod_perspectiva', $codPerspectiva);
    }
}
