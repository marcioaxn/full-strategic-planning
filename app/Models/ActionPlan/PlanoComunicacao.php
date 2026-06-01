<?php

namespace App\Models\ActionPlan;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanoComunicacao extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'action_plan.tab_plano_comunicacao';

    protected $primaryKey = 'cod_comunicacao';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_plano_de_acao',
        'nom_publico_alvo',
        'dsc_mensagem_chave',
        'dsc_canal',
        'dsc_frequencia',
        'nom_responsavel',
        'num_ordem',
    ];

    protected $casts = ['num_ordem' => 'integer'];

    public const CANAIS = [
        'E-mail', 'Reunião presencial', 'Videoconferência',
        'Relatório', 'Apresentação', 'Mensagem instantânea',
        'Portal/Intranet', 'Ofício', 'Outro',
    ];

    public const FREQUENCIAS = [
        'Semanal', 'Quinzenal', 'Mensal',
        'Bimestral', 'Trimestral', 'Sob demanda', 'Única vez',
    ];

    public function plano(): BelongsTo
    {
        return $this->belongsTo(PlanoDeAcao::class, 'cod_plano_de_acao', 'cod_plano_de_acao');
    }
}
