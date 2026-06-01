<?php

namespace App\Models\StrategicPlanning;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntegracaoInstrumento extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'strategic_planning.tab_integracao_instrumentos';

    protected $primaryKey = 'cod_integracao';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_pei',
        'dsc_instrumento',
        'dsc_tipo_instrumento',
        'txt_pontos_atencao',
        'txt_tarefas',
        'dsc_intensidade',
        'num_ordem',
    ];

    protected $casts = [
        'num_ordem' => 'integer',
    ];

    // A Agenda 2030/ODS deixou de ser um "tipo de instrumento" genérico:
    // agora tem aba dedicada com vínculo estruturado (rel_pei_ods).
    public const TIPOS = ['PPA', 'LOA', 'Plano Setorial', 'Outro'];

    public const INTENSIDADES = ['Alta', 'Media', 'Baixa'];

    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }
}
