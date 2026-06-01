<?php

namespace App\Models\ActionPlan;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LicaoAprendida extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'action_plan.tab_licoes_aprendidas';

    protected $primaryKey = 'cod_licao';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_plano_de_acao',
        'dsc_categoria',
        'dsc_tipo',
        'txt_descricao',
        'txt_recomendacao',
        'num_ordem',
    ];

    protected $casts = ['num_ordem' => 'integer'];

    public const TIPOS = [
        'Aprendizado'  => ['icon' => 'lightbulb-fill', 'color' => 'success'],
        'Problema'     => ['icon' => 'exclamation-triangle-fill', 'color' => 'danger'],
        'Melhoria'     => ['icon' => 'arrow-up-circle-fill', 'color' => 'primary'],
        'Boas Práticas'=> ['icon' => 'star-fill', 'color' => 'warning'],
    ];

    public const CATEGORIAS = [
        'Geral', 'Planejamento', 'Execução', 'Comunicação',
        'Riscos', 'Equipe', 'Orçamento', 'Prazo',
    ];

    public function plano(): BelongsTo
    {
        return $this->belongsTo(PlanoDeAcao::class, 'cod_plano_de_acao', 'cod_plano_de_acao');
    }
}
