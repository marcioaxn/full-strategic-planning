<?php

namespace App\Models\StrategicPlanning;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuturoAlmejado extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'strategic_planning.tab_futuro_almejado_objetivo';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_futuro_almejado';

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
        'cod_objetivo',
        'dsc_situacao_atual',
        'dsc_futuro_almejado',
        'dsc_indicador_referencia',
        'vlr_referencia_meta',
        'dte_horizonte',
    ];

    protected $casts = [
        'vlr_referencia_meta' => 'decimal:4',
        'dte_horizonte'       => 'date',
    ];

    /**
     * Relacionamento: Objetivo
     */
    public function objetivo(): BelongsTo
    {
        return $this->belongsTo(Objetivo::class, 'cod_objetivo', 'cod_objetivo');
    }
}
