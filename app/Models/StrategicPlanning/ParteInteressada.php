<?php

namespace App\Models\StrategicPlanning;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParteInteressada extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'strategic_planning.tab_partes_interessadas';

    protected $primaryKey = 'cod_parte';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_pei',
        'nom_parte',
        'dsc_tipo',
        'num_interesse',
        'num_influencia',
        'txt_estrategia_engajamento',
        'num_ordem',
    ];

    protected $casts = [
        'num_interesse'  => 'integer',
        'num_influencia' => 'integer',
        'num_ordem'      => 'integer',
    ];

    public const TIPOS = ['Interno', 'Externo'];

    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

    public function getQuadrante(): string
    {
        $alto = fn(int $v) => $v >= 3;
        return match (true) {
            $alto($this->num_influencia) && $alto($this->num_interesse) => 'Gerencie de Perto',
            $alto($this->num_influencia)                                  => 'Mantenha Satisfeito',
            $alto($this->num_interesse)                                   => 'Mantenha Informado',
            default                                                        => 'Monitore',
        };
    }
}
