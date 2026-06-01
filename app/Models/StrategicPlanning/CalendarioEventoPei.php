<?php

namespace App\Models\StrategicPlanning;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarioEventoPei extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'strategic_planning.tab_calendario_eventos_pei';

    protected $primaryKey = 'cod_evento';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_pei',
        'dsc_titulo',
        'dsc_objetivo',
        'dte_evento',
        'dsc_participantes',
        'dsc_tipo_evento',
        'bln_realizado',
    ];

    protected $casts = [
        'dte_evento'    => 'date',
        'bln_realizado' => 'boolean',
    ];

    public const TIPOS_EVENTO = ['Reunião', 'Workshop', 'Oficina', 'Apresentação', 'Capacitação', 'Outro'];

    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }
}
