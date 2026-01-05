<?php

namespace App\Models\StrategicPlanning;

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
    protected $table = 'tab_grau_satisfacao';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_grau_satisfacao';

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
        'num_ano',
        'dsc_grau_satisfacao',
        'cor',
        'vlr_minimo',
        'vlr_maximo',
    ];

    /**
     * Relacionamento: PEI
     */
    public function pei(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

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
     * Obter grau de satisfação por percentual seguindo as regras de maturidade:
     * 1. Busca por PEI e Ano específico.
     * 2. Fallback: Busca por PEI (geral do ciclo).
     * 3. Fallback: Busca Global (cod_pei nulo).
     */
    public static function porPercentual(float $percentual, ?string $peiId = null, ?int $ano = null): ?self
    {
        // Se não passar PEI/Ano, tenta pegar da sessão
        $peiId = $peiId ?? session('pei_selecionado_id');
        $ano = $ano ?? session('ano_selecionado');

        // Tentar Nível 1: Específico por Ano dentro do PEI (Maturidade)
        if ($peiId && $ano) {
            $grau = static::where('cod_pei', $peiId)
                         ->where('num_ano', $ano)
                         ->where('vlr_minimo', '<=', $percentual)
                         ->where('vlr_maximo', '>=', $percentual)
                         ->first();
            if ($grau) return $grau;
        }

        // Tentar Nível 2: Padrão do PEI (Geral do Ciclo)
        if ($peiId) {
            $grau = static::where('cod_pei', $peiId)
                         ->whereNull('num_ano')
                         ->where('vlr_minimo', '<=', $percentual)
                         ->where('vlr_maximo', '>=', $percentual)
                         ->first();
            if ($grau) return $grau;
        }

        // Nível 3: Fallback Global (Legado ou Padrão de Sistema)
        return static::whereNull('cod_pei')
                     ->whereNull('num_ano')
                     ->where('vlr_minimo', '<=', $percentual)
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
