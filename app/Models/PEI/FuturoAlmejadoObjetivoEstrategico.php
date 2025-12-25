<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuturoAlmejadoObjetivoEstrategico extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'pei.tab_futuro_almejado_objetivo_estrategico';

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
        'dsc_futuro_almejado',
        'cod_objetivo_estrategico',
    ];

    /**
     * Relacionamento: Objetivo Estratégico
     */
    public function objetivoEstrategico(): BelongsTo
    {
        return $this->belongsTo(ObjetivoEstrategico::class, 'cod_objetivo_estrategico', 'cod_objetivo_estrategico');
    }
}
