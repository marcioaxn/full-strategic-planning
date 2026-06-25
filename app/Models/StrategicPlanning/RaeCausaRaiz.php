<?php

namespace App\Models\StrategicPlanning;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaeCausaRaiz extends Model
{
    protected $table = 'strategic_planning.tab_rae_causa_raiz';

    protected $primaryKey = 'cod_causa';

    protected $keyType = 'string';

    public $incrementing = false;

    const CATEGORIAS_ISHIKAWA = [
        'Método',
        'Máquina',
        'Mão de Obra',
        'Material',
        'Medida',
        'Meio Ambiente',
    ];

    protected $fillable = [
        'cod_rae',
        'dsc_problema',
        'json_cinco_porques',
        'dsc_causa_raiz',
        'dsc_categoria_ishikawa',
        'cod_encaminhamento_vinculado',
    ];

    protected $casts = [
        'json_cinco_porques' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (! $model->cod_causa) {
                $model->cod_causa = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function rae(): BelongsTo
    {
        return $this->belongsTo(Rae::class, 'cod_rae', 'cod_rae');
    }

    public function encaminhamento(): BelongsTo
    {
        return $this->belongsTo(RaeEncaminhamento::class, 'cod_encaminhamento_vinculado', 'cod_encaminhamento');
    }
}
