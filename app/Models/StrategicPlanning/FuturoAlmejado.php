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
    protected $table = 'tab_futuro_almejado_objetivo';

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
        'cod_objetivo',
    ];

    /**
     * Relacionamento: Objetivo
     */
    public function objetivo(): BelongsTo
    {
        return $this->belongsTo(Objetivo::class, 'cod_objetivo', 'cod_objetivo');
    }
}
