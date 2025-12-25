<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrauSatisfacao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'pei.tab_grau_satisfcao';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_grau_satisfcao';

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
        'dsc_grau_satisfcao',
        'cor',
        'vlr_minimo',
        'vlr_maximo',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'vlr_minimo' => 'decimal:2',
        'vlr_maximo' => 'decimal:2',
    ];

    /**
     * Métodos auxiliares
     */

    /**
     * Obter grau de satisfação por percentual
     */
    public static function porPercentual(float $percentual): ?self
    {
        return static::where('vlr_minimo', '<=', $percentual)
                     ->where('vlr_maximo', '>=', $percentual)
                     ->first();
    }

    /**
     * Scopes
     */

    /**
     * Scope: Ordenar por valor mínimo
     */
    public function scopeOrdenadoPorValor($query)
    {
        return $query->orderBy('vlr_minimo');
    }
}
