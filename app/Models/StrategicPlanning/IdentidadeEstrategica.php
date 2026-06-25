<?php

namespace App\Models\StrategicPlanning;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class IdentidadeEstrategica extends Model implements Auditable
{
    use HasFactory, HasUuids, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'strategic_planning.tab_missao_visao_valores';

    protected $primaryKey = 'cod_missao_visao_valores';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'dsc_negocio',
        'dsc_missao',
        'dsc_visao',
        'cod_pei',
        'cod_organizacao',
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
