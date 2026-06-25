<?php

namespace App\Models\StrategicPlanning;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RaeEncaminhamento extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'strategic_planning.tab_rae_encaminhamento';

    protected $primaryKey = 'cod_encaminhamento';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_rae',
        'cod_responsavel',
        'cod_plano_vinculado',
        'dsc_tipo',
        'txt_descricao',
        'dsc_status',
        'dte_prazo',
    ];

    protected $casts = [
        'dte_prazo' => 'date',
    ];

    public const TIPOS = ['Novo Plano', 'Revisão de Meta', 'Revisão de Objetivo', 'Revisão de Risco', 'Outro'];

    public const STATUS = ['Pendente', 'Em Execução', 'Concluído'];

    public function rae(): BelongsTo
    {
        return $this->belongsTo(Rae::class, 'cod_rae', 'cod_rae');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_responsavel', 'id');
    }

    public function estaAtrasado(): bool
    {
        return $this->dsc_status !== 'Concluído'
            && $this->dte_prazo !== null
            && $this->dte_prazo->isPast();
    }
}
