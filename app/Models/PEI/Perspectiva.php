<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Perspectiva extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'pei.tab_perspectiva';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_perspectiva';

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
        'dsc_perspectiva',
        'num_nivel_hierarquico_apresentacao',
        'cod_pei',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'num_nivel_hierarquico_apresentacao' => 'integer',
    ];

    /**
     * Relacionamento: PEI
     */
    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

    /**
     * Relacionamento: Objetivos Estratégicos
     */
    public function objetivos(): HasMany
    {
        return $this->hasMany(ObjetivoEstrategico::class, 'cod_perspectiva', 'cod_perspectiva');
    }

    /**
     * Relacionamento: Atividades da Cadeia de Valor
     */
    public function atividades(): HasMany
    {
        return $this->hasMany(AtividadeCadeiaValor::class, 'cod_perspectiva', 'cod_perspectiva');
    }

    /**
     * Scopes
     */

    /**
     * Scope: Ordenar por nível hierárquico
     */
    public function scopeOrdenadoPorNivel($query)
    {
        return $query->orderBy('num_nivel_hierarquico_apresentacao');
    }
}
