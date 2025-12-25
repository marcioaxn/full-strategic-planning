<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ObjetivoEstrategico extends Model implements Auditable
{
    use HasFactory, HasUuids, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'pei.tab_objetivo_estrategico';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_objetivo_estrategico';

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
        'nom_objetivo_estrategico',
        'dsc_objetivo_estrategico',
        'num_nivel_hierarquico_apresentacao',
        'cod_perspectiva',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'num_nivel_hierarquico_apresentacao' => 'integer',
    ];

    /**
     * Relacionamento: Perspectiva BSC
     */
    public function perspectiva(): BelongsTo
    {
        return $this->belongsTo(Perspectiva::class, 'cod_perspectiva', 'cod_perspectiva');
    }

    /**
     * Relacionamento: Planos de Ação
     */
    public function planosAcao(): HasMany
    {
        return $this->hasMany(PlanoDeAcao::class, 'cod_objetivo_estrategico', 'cod_objetivo_estrategico');
    }

    /**
     * Relacionamento: Indicadores
     */
    public function indicadores(): HasMany
    {
        return $this->hasMany(Indicador::class, 'cod_objetivo_estrategico', 'cod_objetivo_estrategico');
    }

    /**
     * Relacionamento: Futuro Almejado
     */
    public function futuroAlmejado(): HasMany
    {
        return $this->hasMany(FuturoAlmejadoObjetivoEstrategico::class, 'cod_objetivo_estrategico', 'cod_objetivo_estrategico');
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

    /**
     * Scope: Por perspectiva
     */
    public function scopePorPerspectiva($query, string $codPerspectiva)
    {
        return $query->where('cod_perspectiva', $codPerspectiva);
    }
}
