<?php

namespace App\Models\StrategicPlanning;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InauguraPei extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'strategic_planning.tab_inaugurar_pei';

    protected $primaryKey = 'cod_inaugurar';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_pei',
        'txt_equipe',
        'txt_diretrizes',
        'txt_metodologia',
        'txt_observacoes',
        'dte_inicio_processo',
        'dte_fim_previsto',
        'bln_aprovado',
    ];

    protected $casts = [
        'dte_inicio_processo' => 'date',
        'dte_fim_previsto'    => 'date',
        'bln_aprovado'        => 'boolean',
    ];

    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }
}
