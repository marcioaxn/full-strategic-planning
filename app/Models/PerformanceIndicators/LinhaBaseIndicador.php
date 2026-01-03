<?php

namespace App\Models\PerformanceIndicators;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LinhaBaseIndicador extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_linha_base_indicador';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_linha_base';

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
        'num_linha_base',
        'num_ano',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'num_linha_base' => 'decimal:2',
        'num_ano' => 'integer',
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
}
