<?php

namespace App\Models\StrategicPlanning;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CenarioProspectivo extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'strategic_planning.tab_cenarios_prospectivos';

    protected $primaryKey = 'cod_cenario';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_pei',
        'cod_organizacao',
        'nom_cenario',
        'dsc_tipo',
        'dsc_descricao',
        'txt_implicacoes',
        'txt_resposta_estrategica',
        'num_probabilidade',
        'num_impacto',
        'num_ordem',
    ];

    protected $casts = [
        'num_probabilidade' => 'integer',
        'num_impacto'       => 'integer',
        'num_ordem'         => 'integer',
    ];

    public const TIPOS = [
        'Otimista'    => ['icon' => 'sun-fill', 'color' => 'success'],
        'Tendencial'  => ['icon' => 'arrow-right-circle-fill', 'color' => 'primary'],
        'Pessimista'  => ['icon' => 'cloud-rain-fill', 'color' => 'danger'],
    ];

    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'cod_organizacao', 'cod_organizacao');
    }
}
