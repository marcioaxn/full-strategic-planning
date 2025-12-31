<?php

namespace App\Models\PEI;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ObjetivoEstrategico extends Model implements Auditable
{
    use HasFactory, HasUuids, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_objetivo_estrategico';

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
        'cod_pei',
        'cod_organizacao',
    ];

    /**
     * Relacionamento: Ciclo PEI
     */
    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

    /**
     * Relacionamento: Organização
     */
    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'cod_organizacao', 'cod_organizacao');
    }
}
