<?php

namespace App\Models\StrategicPlanning;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstrategiaTows extends Model
{
    use SoftDeletes;

    protected $table = 'strategic_planning.tab_estrategia_tows';

    protected $primaryKey = 'cod_estrategia';

    protected $keyType = 'string';

    public $incrementing = false;

    const TIPOS = [
        'SO' => ['label' => 'SO — Força + Oportunidade',  'desc' => 'Usar forças para aproveitar oportunidades',      'cor' => 'success'],
        'WO' => ['label' => 'WO — Fraqueza + Oportunidade','desc' => 'Superar fraquezas aproveitando oportunidades',   'cor' => 'primary'],
        'ST' => ['label' => 'ST — Força + Ameaça',         'desc' => 'Usar forças para neutralizar ameaças',           'cor' => 'warning'],
        'WT' => ['label' => 'WT — Fraqueza + Ameaça',      'desc' => 'Minimizar fraquezas e evitar ameaças',           'cor' => 'danger'],
    ];

    protected $fillable = [
        'cod_pei',
        'cod_organizacao',
        'dsc_tipo',
        'dsc_estrategia',
        'txt_fundamentacao',
        'cod_objetivo_vinculado',
    ];

    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'cod_organizacao', 'cod_organizacao');
    }

    public function objetivo(): BelongsTo
    {
        return $this->belongsTo(Objetivo::class, 'cod_objetivo_vinculado', 'cod_objetivo');
    }
}
