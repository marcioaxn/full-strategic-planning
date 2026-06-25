<?php

namespace App\Models\StrategicPlanning;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rae extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'strategic_planning.tab_rae';

    protected $primaryKey = 'cod_rae';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_pei',
        'cod_organizacao',
        'dte_referencia',
        'dte_reuniao',
        'txt_destaques_positivos',
        'txt_problemas_identificados',
        'txt_encaminhamentos',
        'json_participantes',
        'num_progresso_geral',
        'dsc_tipo_reuniao',
    ];

    protected $casts = [
        'dte_referencia'   => 'date',
        'dte_reuniao'      => 'date',
        'json_participantes' => 'array',
        'num_progresso_geral' => 'decimal:2',
    ];

    public const TIPOS_REUNIAO = ['RAE', 'Revisão Semestral', 'Revisão Anual', 'Reunião de Monitoramento'];

    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'cod_organizacao', 'cod_organizacao');
    }

    public function encaminhamentos(): HasMany
    {
        return $this->hasMany(RaeEncaminhamento::class, 'cod_rae', 'cod_rae')
            ->orderBy('dte_prazo')
            ->orderBy('created_at');
    }

    public function encaminhamentosPendentes(): HasMany
    {
        return $this->hasMany(RaeEncaminhamento::class, 'cod_rae', 'cod_rae')
            ->whereIn('dsc_status', ['Pendente', 'Em Execução']);
    }
}
