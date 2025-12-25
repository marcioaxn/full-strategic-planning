<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetaPorAno extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'pei.tab_meta_por_ano';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_meta_por_ano';

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
        'cod_indicador',
        'num_ano',
        'meta',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'num_ano' => 'integer',
        'meta' => 'decimal:2',
    ];

    /**
     * Relacionamento: Indicador
     */
    public function indicador(): BelongsTo
    {
        return $this->belongsTo(Indicador::class, 'cod_indicador', 'cod_indicador');
    }

    /**
     * Scopes
     */

    /**
     * Scope: Por ano
     */
    public function scopePorAno($query, int $ano)
    {
        return $query->where('num_ano', $ano);
    }

    /**
     * Scope: Ano atual
     */
    public function scopeAnoAtual($query)
    {
        return $query->where('num_ano', now()->year);
    }
}
